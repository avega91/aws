<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file AssetController.php
 *     Management of actions for main page
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class AssetController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = false;
        $this->autoRender = false; // no view to render
        $this->_method = strtoupper($this->request->method());

        $this->uses[] = "FolderApp";
        $this->uses[] = "AssetMetadata";
    }

    /**
     * Get Assets for one BS or save new
     *
     * @param [int] $buoySystem
     * @return void
     */
    public function buoySystemAssets($buoySystem) {
        $response = null;
        $status_code = 403;
        if(is_numeric($buoySystem)){
            switch($this->_method) {
               case ApiRequest::IsPost: //Save new row
                $response = $this->saveAssetForBuoySystem($buoySystem);
                $status_code = !isset($response['error']) ? 200 : $status_code;
               break;
               default:
                    $response = array('error' => 'operation not allowed');
                break;
            }
            $status_code = 200;
        }else{
            $response = array('error' => 'wrong buoy system id');
        }

        $response['success'] = true;

        $this->response->type('json');
        $this->response->statusCode($status_code);
        $json = json_encode($this->Core->cleanJson($response));
        $this->response->body($json);
    }

    /**
     * Get some Asset metadata for one BS or update asset
     *
     * @param [int] $buoySystem
     * @param [int] $asset_metadata_id
     * @return void
     */
    public function buoySystemAsset($buoySystem, $asset_metadata_id) {
        $response = null;
        $status_code = 403;
        if(is_numeric($buoySystem)){
            switch($this->_method) {
               case ApiRequest::IsPost: //Save new row
                $response = $this->saveAssetForBuoySystem($buoySystem, $asset_metadata_id);
                $status_code = !isset($response['error']) ? 200 : $status_code;
               break;
               default:
                    $response = array('error' => 'operation not allowed');
                break;
            }
            $status_code = 200;
        }else{
            $response = array('error' => 'wrong client id');
        }

        $response['success'] = true;

        $this->response->type('json');
        $this->response->statusCode($status_code);
        $json = json_encode($this->Core->cleanJson($response));
        $this->response->body($json);
    }

    private function saveAssetForBuoySystem($buoySystemId, $assetId = 0){
        $responseOperation = [];
        $assetsData = $this->request->data;
        if(is_null($assetsData)){// it comes of api request
            $assetsData = file_get_contents('php://input');
            $assetsData = json_decode($assetsData, true);
        }

        if(is_null($assetsData) || empty($assetsData)){
            $responseOperation = ['error' => "Empty data to save"];
        } else {
            Configure::load('folder_names');
            $folderSettings = Configure :: read('Folders');
            Configure::load('metadata_fields');
            $fieldsMetadata = Configure::read('Metadata')['fields']['asset'];
            
            //Get data of request
            $data = $assetsData['data'];
            $overwrite = $assetsData['overwrite'];

            $assetsBuoySystem = $this->AssetMetadata->find('all',[
                'fields' => ['unique_id_tag', 'id'],
                'conditions' => [
                    'Asset.buoy_system_id' => $buoySystemId,
                ],
                'contain' => ['Asset']
            ]);
            $assetsBuoySystem = !empty($assetsBuoySystem) ? Set::extract('/AssetMetadata/.', $assetsBuoySystem) : [];
            //Modificamos el unique id de los Assets guardados para poder hacer un search case insensitive
            array_walk_recursive($assetsBuoySystem,function (&$item, $key){
                $item = $key === 'unique_id_tag' ? strtolower($item) : $item;
            });


            $savedAssetMetadata = [];
            $assetWithError = [];
            try {
                foreach($data AS $bsRow){
                    $bsId = null;
                    $assetUniqueId = $bsRow[1];
                    $assetName = '';
                    if($assetId <= 0){//Viene de una importacion excel
                        $assetName = $bsRow[0];
                        unset($bsRow[0]);
                    }else{
                        $keysPacket = array_keys($bsRow);
                        for($i=0; $i<end($keysPacket);$i++){
                            if(!isset($bsRow[$i])){
                                $bsRow[$i] = '';
                            }
                        }
                        ksort($bsRow);
                    }
                    

                    $metadataAsset = array_combine($fieldsMetadata, $bsRow);
                    $metadataAsset = ['AssetMetadata'=> $metadataAsset];

                    $metadataAsset['AssetMetadata']['delivery_date'] = str_replace(['(',')'],['',''],$metadataAsset['AssetMetadata']['delivery_date']);
                    $metadataAsset['AssetMetadata']['end_life'] = str_replace(['(',')'],['',''],$metadataAsset['AssetMetadata']['end_life']);
                    
                    //SAVE OR GET THE ASSET
                    $key = $assetId > 0 ? $assetId : array_search(strtolower($assetUniqueId), array_column($assetsBuoySystem, 'unique_id_tag'));
                    if($key !== false){ //Asset metadata already exists cheking unique id tag or param assetId
                        $assetIdRow = $assetId > 0 ? $assetId : $assetsBuoySystem[$key]['id'];
                        if($overwrite == 1){//@todo cuando es un save desde app, checar que el unique id no se repita
                            $metadataAsset['AssetMetadata']['id'] = $assetIdRow;
                            $this->AssetMetadata->save($metadataAsset); //overwrite data
                        }else{
                            //Keep Data
                        }
                    }else{ //Bs not exist, create
                        $assetsBSWithName = $this->FolderApp->find('first', [
                            'fields' => ['id', 'name', 'AssetMetadata.id'],
                            'conditions'=>[
                                'AssetMetadata.id' => null,
                                'FolderApp.deleted' => 0,
                                'FolderApp.is_asset_folder' => 1,
                                'FolderApp.buoy_system_id' => $buoySystemId,
                                'LOWER(FolderApp.name)' => strtolower($assetName),
                            ],
                            'joins' => [
                                array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                            ]
                        ]);

                        //Si existe el nodo, y no tiene info asociada, se le ponen los metadatos
                        if(!empty($assetsBSWithName)){
                            $this->AssetMetadata->create();
                            $metadataAsset['AssetMetadata']['folder_app_id'] = $assetsBSWithName['FolderApp']['id'];
                            if($this->AssetMetadata->save($metadataAsset)){
                                $metadataId = $this->AssetMetadata->getInsertID();
                                $metadataAsset['AssetMetadata']['id'] = $metadataId;
                                $savedAssetMetadata[] = $metadataAsset;
                            }
                        } else {//no existe el node, buscar donde va el asset
                            $assetsBSWithName = $this->FolderApp->find('first', [
                                'fields' => ['id', 'client_id', 'folder_id', 'buoy_system_id', 'type'],
                                'conditions'=>[
                                    'FolderApp.deleted' => 0,
                                    'FolderApp.is_asset_folder' => 1,
                                    'FolderApp.buoy_system_id' => $buoySystemId,
                                    'LOWER(FolderApp.name)' => strtolower($assetName),
                                ]
                            ]);

                            //var_dump($assetsBSWithName);

                            if(!empty($assetsBSWithName)){
                                //Buscar todos los assets del tipo, para checar que no haya un nodo ej. Valves-1 que necesite informacion
                                $assetsBSWithType = $this->FolderApp->find('first', [
                                    'fields' => ['id', 'folder_id', 'buoy_system_id', 'type', 'name', 'AssetMetadata.id'],
                                    'conditions'=>[
                                        'AssetMetadata.id' => null,
                                        'FolderApp.deleted' => 0,
                                        'FolderApp.is_asset_folder' => 1,
                                        'FolderApp.buoy_system_id' => $buoySystemId,
                                        'FolderApp.type' => $assetsBSWithName['FolderApp']['type'],
                                    ],
                                    'joins' => [
                                        array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                                    ]
                                ]);
                                if(!empty($assetsBSWithType)){//Ya existe un nodo del tipo Asset-1 o Asset-2, que necesita metadatos
                                    $this->AssetMetadata->create();
                                    $metadataAsset['AssetMetadata']['folder_app_id'] = $assetsBSWithType['FolderApp']['id'];
                                    if($this->AssetMetadata->save($metadataAsset)){
                                        $metadataId = $this->AssetMetadata->getInsertID();
                                        $metadataAsset['AssetMetadata']['id'] = $metadataId;
                                        $savedAssetMetadata[] = $metadataAsset;
                                    }
                                }else{//Create the new node (Asset & metadata asset)
                                    $alreadySavedFolderType = $this->FolderApp->find('all', [
                                        'fields' => ['name'],
                                        'conditions'=>[
                                            'FolderApp.deleted' => 0,
                                            'FolderApp.is_asset_folder' => 1,
                                            'FolderApp.buoy_system_id' => $buoySystemId,
                                            'FolderApp.type' => $assetsBSWithName['FolderApp']['type'],
                                            ]
                                        ]
                                    );
                                    $coincidences = count($alreadySavedFolderType);
                                    $prefixName = $coincidences > 0 ? "-$coincidences" : '';

                                    $this->FolderApp->create();
                                    $assetBSData = ['FolderApp' => [
                                        'name' => $assetName.$prefixName,
                                        'client_id' => $assetsBSWithName['FolderApp']['client_id'],
                                        'folder_id' => $assetsBSWithName['FolderApp']['folder_id'],
                                        'buoy_system_id' => $assetsBSWithName['FolderApp']['buoy_system_id'],
                                        'type' => $assetsBSWithName['FolderApp']['type'],
                                        'is_asset_folder' => true,
                                        'allow_assets' => false,
                                        'updated_at' => date('Y-m-d H:i:s')
                                        ]
                                    ];

                                    //Guardamos el asset folder
                                    if($this->FolderApp->save($assetBSData)){
                                        $assetFolderIdRow = $this->FolderApp->getInsertID();

                                        //creamos sus GHMC
                                        $buoyFolderAssetsSaved = $this->FolderApp->findByIdAndDeleted($assetFolderIdRow, 0);
                                        $buoyFolderAssetsSaved['FolderApp']['type'] = 'sheet_folder';
                                        $this->Core->createGenericFoldersInAssetsFolder($buoyFolderAssetsSaved, $folderSettings, $assetsBSWithName['FolderApp']['buoy_system_id']);

                                        //Guardamos los metadatos del asset
                                        $this->AssetMetadata->create();
                                        $metadataAsset['AssetMetadata']['folder_app_id'] = $assetFolderIdRow;
                                        if($this->AssetMetadata->save($metadataAsset)){
                                            $metadataId = $this->AssetMetadata->getInsertID();
                                            $metadataAsset['AssetMetadata']['id'] = $metadataId;
                                            $savedAssetMetadata[] = $metadataAsset;
                                        }else{//No se guardo bien el metadato, quitar el asset folder
                                            $assetWithError[] = $assetFolderIdRow;
                                        }
                                    }
                                }
                            }else{
                                //El tipo de asset segun su nombre no existe, no hay manera de saber que asset es, no se hace nada
                            }
                        }
                    }
                }

                $responseOperation['msg'] = $buoySystemId > 0 ? __('The metadata was saved successfully.',true) : __('The metadata was uploaded successfully.',true);

            }catch (Exception $e) {
                //rollback saved data
                if(!empty($savedAssetMetadata)){
                    foreach($savedAssetMetadata AS $savedMDBS){
                        $this->AssetMetadata->delete($savedMDBS['AssetMetadata']['id']);
                    }
                }

                if(!empty($assetWithError)){
                    foreach($assetWithError AS $assetFolderId) {
                        $this->FolderApp->delete($assetFolderId);
                    }
                }
                $responseOperation = ['error' => $e->getMessage()];
            }
        }
        return $responseOperation;
    }

}
