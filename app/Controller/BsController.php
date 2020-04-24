<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file BsController.php
 *     Management of actions for main page
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class BsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = false;
        $this->autoRender = false; // no view to render
        $this->_method = strtoupper($this->request->method());

        $this->uses[] = "FolderApp";
        $this->uses[] = "BsMetadata";
    }

    /**
     * Get Bss for one client or save new
     *
     * @param [int] $client
     * @return void
     */
    public function clientBuoySystems($client) {
        $response = null;
        $status_code = 403;
        if(is_numeric($client)){
            switch($this->_method) {
               case ApiRequest::IsPost: //Save new row
                $response = $this->saveBuoySystemForClient($client);
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

    /**
     * Get some Bs for one client or update bs
     *
     * @param [int] $client
     * @param [int] $buoy_system_id
     * @return void
     */
    public function clientBuoySystem($client, $buoy_system_id) {
        $response = null;
        $status_code = 403;
        if(is_numeric($client)){
            switch($this->_method) {
               case ApiRequest::IsPost: //Save new row
                $response = $this->saveBuoySystemForClient($client, $buoy_system_id);
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

    private function saveBuoySystemForClient($clientId, $buoySystemId = 0){
        $responseOperation = [];
        $bsData = $this->request->data;
        if(is_null($bsData)){// it comes of api request
            $bsData = file_get_contents('php://input');
            $bsData = json_decode($bsData, true);
        }

        if(is_null($bsData) || empty($bsData)){
            $responseOperation = ['error' => "Empty data to save"];
        } else {
            Configure::load('metadata_fields');
            $fieldsMetadata = Configure::read('Metadata')['fields']['bs'];
            
            //Get data of request
            $data = $bsData['data'];
            $overwrite = $bsData['overwrite'];
            //Get buoy systems of client
            $buoySystemsClientDB = $this->FolderApp->find('all', [
                'fields' => ['FolderApp.name', 'FolderApp.id', 'BsMetadata.id'],
                'conditions'=>[
                    'FolderApp.deleted' => 0,
                    'FolderApp.type' => 'buoy_system',
                    'FolderApp.client_id' => $clientId
                    ]
                ]
            );

            //Le agregamos el id de su metadato para checar si tiene metadatos asociados
            if(!empty($buoySystemsClientDB)){
                $buoySystemsClientDB = array_map(function ($item){
                    return ['FolderApp' => [
                        'name' => $item['FolderApp']['name'],
                        'id' => $item['FolderApp']['id'],
                        'metadata_id' => $item['BsMetadata']['id']
                        ]];
                }, $buoySystemsClientDB);
            }

            $buoySystemsClient = !empty($buoySystemsClientDB) ? Set::extract('/FolderApp/.', $buoySystemsClientDB) : [];
            //Modificamos el titulo de los BS guardados para poder hacer un search case insensitive
            array_walk_recursive($buoySystemsClient,function (&$item, $key){
                $item = $key === 'name' ? strtolower($item) : $item;
            });

            $savedBsMetadata = [];
            $bsWithError = [];
            try {
                foreach($data AS $bsRow){
                    $bsId = null;
                    $bsName = $bsRow[0];
                    //Set data of metadata
                    unset($bsRow[0]);

                    $metadataBs = array_combine($fieldsMetadata, $bsRow);
                    $metadataBs = ['BsMetadata'=> $metadataBs];
                    
                    if(isset($metadataBs['BsMetadata']['revision_date'])) {
                        $metadataBs['BsMetadata']['revision_date'] = str_replace('/','-',$metadataBs['BsMetadata']['revision_date']);
                        $metadataBs['BsMetadata']['revision_date'] = date("Y-m-d", strtotime($metadataBs['BsMetadata']['revision_date']));
                    }
                    
                    //SAVE OR GET THE BUOY SYSTEM
                    $key = $buoySystemId > 0 ? $buoySystemId : array_search(strtolower($bsName), array_column($buoySystemsClient, 'name'));
                    if($key !== false){ //Buoy system already exists
                        $bsId = $buoySystemId > 0 ? $buoySystemId : $buoySystemsClient[$key]['id'];
                        $metadataBsInBd = $this->BsMetadata->findByFolderAppId($bsId);
                        //var_dump($metadataBsInBd);die;
                        if(!empty($metadataBsInBd)){//Si ya existe la info, solo tomar el id para sobreesribir con la info recibida
                            if($overwrite == 1){
                                $metadataBs['BsMetadata']['id'] = $metadataBsInBd['BsMetadata']['id'];
                                $this->BsMetadata->save($metadataBs); //overwrite data
                            }
                        }else{//si no, crear el bsMetadata para el bs
                            $this->BsMetadata->create();
                            $metadataBs['BsMetadata']['folder_app_id'] = $bsId;
                            if($this->BsMetadata->save($metadataBs)){
                                $metadataId = $this->BsMetadata->getInsertID();
                                $metadataBs['BsMetadata']['id'] = $metadataId;
                                $savedBsMetadata[] = $metadataBs;
                            }
                        }
                    }else{ //Bs not exist, create
                        $buoySystemData = ['FolderApp' => [
                                'name' => $bsName,
                                'client_id' => $clientId,
                                'folder_id' => 0,
                                'type' => 'buoy_system',
                                'allow_assets' => false,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]
                        ];
                        //model data is reset and ready to accept new data
                        $this->FolderApp->create();
                        $this->FolderApp->save($buoySystemData);
                        $bsId = $this->FolderApp->getInsertID();

                        $this->BsMetadata->create();
                        $metadataBs['BsMetadata']['folder_app_id'] = $bsId;
                        if($this->BsMetadata->save($metadataBs)){
                            $metadataId = $this->BsMetadata->getInsertID();
                            $metadataBs['BsMetadata']['id'] = $metadataId;
                            $savedBsMetadata[] = $metadataBs;
                        }else{//No se guardo bien el metadato, quitar el bs
                            $bsWithError[] = $bsId;
                        }
                    }
                }

                $responseOperation['msg'] = $buoySystemId > 0 ? __('The metadata was saved successfully.',true) : __('The metadata was uploaded successfully.',true);

            }catch (Exception $e) {
                //rollback saved data
                if(!empty($savedBsMetadata)){
                    foreach($savedBsMetadata AS $savedMDBS){
                        $this->BsMetadata->delete($savedMDBS['BsMetadata']['id']);
                    }
                }

                if(!empty($bsWithError)){
                    foreach($bsWithError AS $buoySystemId) {
                        $this->FolderApp->delete($buoySystemId);
                    }
                }
                $responseOperation = ['error' => $e->getMessage()];
            }
        }
        return $responseOperation;
    }

}
