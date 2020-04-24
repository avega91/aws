<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file BuoySystemsController.php
 *     Management of actions for conveyors
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::import('Vendor', 'VideoEncoder', array('file' => 'VideoEncoder/VideoEncoder.php'));
App::import('Vendor', 'Dompdf', array('file' => 'Dompdf/dompdf_config.inc.php'));
App::import('Vendor', 'PhpExcel', array('file' => 'PhpExcel/PHPExcel.php'));
App::uses('HttpSocket', 'Network/Http');
App::uses('IMarket', 'Model');
//App::import('Core', 'ConnectionManager');

class BuoySystemsController extends AppController {
    public $components = ['Datatable'];

    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'UsConveyor';
        $this->uses[] = 'CompanyArea';
        $this->uses[] = 'CompanySubarea';
        $this->uses[] = "RecommendedBelt";

        $this->uses[] = "FolderApp";
        $this->uses[] = "FileFolder";
        $this->uses[] = "AssetMetadata";


        if (!$this->Session->check(Statistic::GO_CONVEYORS)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_CONVEYORS);
            $this->Session->write(Statistic::GO_CONVEYORS, Statistic::GO_CONVEYORS);
        }

    }

    public function refreshBuoys() {
        $this->layout = false;
        $query = $sort = '';
        $activeTab = 'admin';
        $rows = $desde = 0;
        $clientId = isset($this->request->query['cid']) ? $this->request->query['cid'] : 0;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $sort = $params[1];
                $query = isset($params[2]) ? $params[2] : '';
                $query = $query == '-' ? '' : $query;

                //$rows = isset($params[3]) && $params[3] > 0 ? $params[3] : 0;
                $rows = isset($params[3]) ? $params[3] : 0;
                $desde = isset($params[4]) && $rows > 0 ? $params[4] : 0;
            }
        }

        //var_dump($params);
        //echo '<br><br><br><br><br>';
        $sorts = [
            'title' => 'FolderApp.name ASC',
            'update' => 'FolderApp.updated_at DESC',
            'status' => 'FolderApp.status ASC'
        ];

        $filter_companies = $this->Session->read(parent::ASSOC_COMPANIES);
        $filter_companies = explode(",", $filter_companies);
        $buoySystems = $this->FolderApp->find('all', [
            'conditions'=>[
                'FolderApp.name LIKE' => "%$query%",
                'FolderApp.client_id' => $filter_companies,
                'FolderApp.folder_id' => 0, // Buscar solo los root folder
                'FolderApp.deleted' => 0,
            ],
            'order' => [$sorts[$sort]],
            ]
        );
        
        //$log = $this->FolderApp->getDataSource()->getLog(false, false);
        //var_dump($log['log'][40]['query']);
        //$conveyors = $this->Core->getConveyorsUsingFilters($filter_companies, $query, $sort, $rows, $desde);

        if($clientId>0){
            $company = $this->Empresa->findById($clientId);
            $this->set('nameCompany', $company['Empresa']['name']);
        }

        $this->set('buoy_systems', $buoySystems);
        $this->set('offset', $desde);
        $this->set('clientId', $clientId);
    }


  
    /**
     * Vista de un folder de imodco
     *
     * @return void
     */
    public function viewBuoyFolder() {
        $this->set('options_toolbar', 'items-folder');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $error = true;
        $isBuoySystem = false;
        if (!empty($params) && count($params) == 2) {
            $decodedItemParams = $this->Core->decodePairParams($params);
            if ($decodedItemParams['isOk']) {
                $item_received = $decodedItemParams['item_id'];
                //Obtenemos el folder actual
                $buoyFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                $secureFolderParams = $this->Core->encodeParams($item_received);

                if (!empty($buoyFolder)) {
                    //Solo folders de tipo buoy systems
                    //Configure::load('folder_names');->Este archivo no crea loas assets folders
                    Configure::load('folder_names_full_struct');
                    $folderSettings = Configure :: read('Folders');
                    if($buoyFolder['FolderApp']['folder_id']<=0){ //Si es un folder de tipo BS y aun no se ha generado su estructura global
                        //Obtenemos si el folder BS tiene hijos
                        $childsBS = $this->FolderApp->findByFolderIdAndDeleted($item_received, 0);
                        if(empty($childsBS)){//Si no tiene hijos, crear la estructura por primera vez
                            //Llamar a funcion que crea toda la estructura de folders, solo una vez
                            $buoySystemId = $item_received;  
                            $this->Core->createFullImodcoTreeFolders($buoyFolder, $folderSettings, $buoySystemId);
                        }else {
                            $this->Core->updateBSIDChildFolders($buoyFolder, $item_received);
                        }
                    
                        $isBuoySystem = true;
                    }

                    //Checamos si tiene hijos el folder, SI NO TIENE, es un folder G,H,M,C
                    $folderChilds = $this->FolderApp->find('all', [
                        'fields' => ['type', 'id', 'name', 'is_file_folder'],
                        'conditions'=>[
                            'FolderApp.client_id' => $buoyFolder['Client']['id'], //el id del client
                            'FolderApp.folder_id' => $item_received,// el id del folderactual
                            'FolderApp.deleted' => 0,
                            ]
                        ]
                    );

                    $secureFolderParams = $this->Core->encodeParams($item_received);
                    $is_folder_sheet = false;

                    /* @todo, checar si es asset folder, y no tienen hijos, crear sus GHMC
                    if(empty($folderChilds) && $buoyFolder['FolderApp']['is_asset_folder']){ 
                    }*/


                    //EL FOLDER DE ARCHIVOS
                    if(empty($folderChilds) && !$buoyFolder['FolderApp']['allow_assets']){ //Si no tiene hijos y no permite assets, es folder de archivos
                        $this->set('options_toolbar', '');
                        $is_folder_sheet = true;
                        $this->openCssToInclude[] = 'plugins/Assets/datatables/dataTables.bootstrap';
                        $this->openCssToInclude[] = 'plugins/Assets/datatables/extensions/Responsive/css/dataTables.responsive';
                        $this->openCssToInclude[] = 'plugins/Assets/datatables/extensions/Buttons/css/buttons.dataTables.min';
                        $this->openCssToInclude[] = 'plugins/Assets/datatables/extensions/TableTools/css/dataTables.tableTools';
                        $this->openCssToInclude[] = 'plugins/Assets/css/jquery-uploadfile/uploadfile';

                        $this->openJsToInclude[] = 'plugins/Assets/datatables/jquery.dataTables.1.10.12.min';
                        $this->openJsToInclude[] = 'plugins/Assets/datatables/dataTables.bootstrap.min';
                        $this->openJsToInclude[] = 'plugins/Assets/datatables/extensions/Responsive/js/dataTables.responsive.min';
                        $this->openJsToInclude[] = 'plugins/Assets/datatables/extensions/Buttons/js/dataTables.buttons.min';
                        $this->openJsToInclude[] = 'plugins/Assets/datatables/extensions/Buttons/js/buttons.print.min';
                        $this->openJsToInclude[] = 'plugins/Assets/datatables/sorting/date-es';
                        $this->openJsToInclude[] = 'plugins/Assets/js/jquery-uploadfile/jquery.uploadfile.min';


                        $canDownloadItem = isset($this->credentials['permissions'][IElement::Is_File]) && in_array('download', $this->credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
                        $canDeleteItem = isset($this->credentials['permissions'][IElement::Is_File]) && in_array('delete', $this->credentials['permissions'][IElement::Is_File]['allows']) ? true : false;

                        $this->setJsVar('canDownloadItem', $canDownloadItem);
                        $this->setJsVar('canDeleteItem', $canDeleteItem);

                        $addFileUrl = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'addFileConveyor', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                        $this->set('add_file_url', $addFileUrl);
                        $this->setJsVar('refreshArchivesDatatableAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'getArchivesDatatable', $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                        $this->setJsVar('uploadFilesAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'saveFileToFolder', $secureFolderParams['item_id'], $secureFolderParams['digest'], $this->credentials['id'])));
                        $this->setJsVar('deleteFilesAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'bulkDeleteFiles', $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                        $this->setJsVar('downloadFilesAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'bulkDownloadFiles', $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                    }else if($buoyFolder['FolderApp']['allow_assets']){//Es folder de assets
                        $childAlreadySaved = [];
                        if(!empty($folderChilds)){
                            $folderChilds = Set::extract('/FolderApp/.', $folderChilds);
                            foreach($folderChilds AS $folderChild){
                                $childAlreadySaved[$folderChild['type']] = $folderChild['name'];
                            }
                        }

                        $assetsFolders = [];
                        $rootNodes = $folderSettings['buoy_system']['nodes'];
                        asort($rootNodes);
                        foreach($rootNodes AS $node_id => $node){
                            $childAlreadySaved = []; //Hack para que siempre se muestren todos
                            $nodesForNode = $this->Core->getAssetsFoldersForNode($node_id, $folderSettings['assets_folder']);
                            $available_nodes = array_diff($nodesForNode, $childAlreadySaved);
                            asort($available_nodes);
                            //$available_nodes = $nodesForNode;
                            //var_dump($available_nodes);
                            //echo '<br><br>';
                            $assetsFolders[$node_id] = ['name'=>$node, 'nodes' => $available_nodes];
                        }
                        //var_dump($assetsFolders);

                        //$available_asset_folders = $folderSettings['assets_folder'][$buoyFolder['FolderApp']['type']]['nodes'];
                        //$available_asset_folders = array_diff($available_asset_folders, $childAlreadySaved);
                        //asort($available_asset_folders);
                        $this->set('secure_params', $params);

                        $this->set('available_asset_folders',$assetsFolders);
                        $this->setJsVar('refreshItemsFolderAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'refreshItemsFolder', $this->usercode, $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                    }else{
                        $allowFileFolders = !empty($folderChilds) && $folderChilds[0]['FolderApp']['is_file_folder'];
                        if($allowFileFolders){
                            $addFileFolderUrl = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'addItemFolder', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                            $this->set('addFileFolderUrl', $addFileFolderUrl);
                            //REEPLICAR FOLDER EN TODOS LOS Dir
                            //$this->Core->createCustomFileFoldersIn($buoyFolder, $folderChilds);
                        }
                        $this->setJsVar('refreshItemsFolderAx', $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'refreshItemsFolder', $this->usercode, $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                    }

                    $this->set('is_folder_sheet',$is_folder_sheet);

                    //Get parents of current folder
                    $folderBreadcrum = $this->Core->getParentsOfFolderId($item_received);
                    if(empty($folderBreadcrum) || $folderBreadcrum[0]['FolderApp']['type']!=='buoy_system'){
                        setcookie('conti_notification', __('The folder was deleted previously.', true), time() + (86400 * 30), "/"); // 86400 = 1 day
                        $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                    }
                    $this->set('folderBreadcrum', $folderBreadcrum);

                    //Obtenemos los comentarios
                    $comments_item = $this->Comment->getCommentsItemByType($item_received, 'folder_app');

                    $this->set('comments_item', $comments_item);

                    $error = false;

                    $this->set('is_folder', true);
                    $this->set('buoy_data', $buoyFolder['FolderApp']);
                    $this->set('client_info', $buoyFolder['Client']);

                    $secureClientConveyorParams = $this->Core->encodeParams($buoyFolder['Client']['id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $this->set('company', $buoyFolder['Client']);
                    $this->set('isBuoySystem', $isBuoySystem);

                    $this->set('secureFolder',$secureFolderParams);
                
                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Conveyors/item_view';
                    $this->set('jsToInclude', $this->jsToInclude);
                
                }else{
                    $folder = $this->FolderApp->findById($item_received);
                    $item_type = '';
                    if(!empty($folder)){
                        $item_type = $folder['FolderApp']['type'] === 'buoy_system' ? 'buoy system' : 'folder';
                    }else{
                        $item_type = 'folder';
                    }
                    setcookie('conti_notification', __('The '.$item_type .' was deleted previously.', true), time() + (86400 * 30), "/"); // 86400 = 1 day
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            }
        }

        if($error){
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    
    
    public function getArchivesDatatable() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $folderId = 0;
        if (!empty($params) && count($params) == 2) {
            $decodedItemParams = $this->Core->decodePairParams($params);
            $folderId = $decodedItemParams['item_id'];
        }

        $request = $this->request->data; //get data

        $canEditItem = isset($this->credentials['permissions'][IElement::Is_File]) && in_array('edit', $this->credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $canDownloadItem = isset($this->credentials['permissions'][IElement::Is_File]) && in_array('download', $this->credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $canDeleteItem = isset($this->credentials['permissions'][IElement::Is_File]) && in_array('delete', $this->credentials['permissions'][IElement::Is_File]['allows']) ? true : false;

        // Array of database columns which should be read and sent back to DataTables.
        // The `db` parameter represents the column name in the database, while the `dt`
        // parameter represents the DataTables column identifier. In this case object
        // parameter names
        $columns = array(
            array(
                'db'        => 'id',
                'dt'        => 'action_row',
                'formatter' => function( $d, $row ) {
                    $rowId = $row[7];// es la columna que indica el js (la def del datatable)
                    $secureArchiveParams = $this->Core->encodeParams($rowId);
                    return '<input class="checkbox-row" type="checkbox" data-signature="'.$secureArchiveParams['item_id'].'" data-digest="'.$secureArchiveParams['digest'].'"/>';
                }
            ),
            array(
                'db'        => 'is_favorite',
                'dt'        => 'favorite',
                'formatter' => function( $d, $row ) {
                    $rowId = $row[7];// es la columna que indica el js (la def del datatable)
                    $secureArchiveParams = $this->Core->encodeParams($rowId);
                    $urlSetFavorite = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'setFavoriteFile', $secureArchiveParams['item_id'], $secureArchiveParams['digest'],$d));
                    $is_favorite = $d == 1 ? 'is-favorite' : '';
                    return '<span class="favorite-row '.$is_favorite.'" data-url="'.$urlSetFavorite.'">&nbsp;</span>';
                }
            ),
            array(
                'db'        => 'name',
                'dt'        => 'doc_name',
                'formatter' => function( $d, $row ) use ($canEditItem){
                    $rowId = $row[7];// es la columna que indica el js (la def del datatable)
                    $extension = $row[3];
                    $secureArchiveParams = $this->Core->encodeParams($rowId);
                    $urlEditFileName = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'updateDocname', $secureArchiveParams['item_id'], $secureArchiveParams['digest']));
                    $editionCtrl = $canEditItem ? '<input class="text-filename" type="text" value="'.$d.'" data-update="'.$urlEditFileName.'"/>' : '';
                    $urlDownloadFile = in_array($extension, ['jpg', 'jpeg', 'gif', 'png', 'pdf']) ? $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'preview', $secureArchiveParams['item_id'], $secureArchiveParams['digest'])) : '#';
                    $classLink = !in_array($extension, ['jpg', 'jpeg', 'gif', 'png', 'pdf']) ? 'no-link' : '';
                    return '<a class="label-filename '.$classLink.'" target="_blank" href="'.$urlDownloadFile.'">'.$d.'</a>'.$editionCtrl;
                }
            ),
            array( 'db' => 'extension', 'dt' => 'type' ),
            array(
                'db'        => 'size',
                'dt'        => 'size',
                'formatter' => function( $d, $row ) {
                    return number_format($d / 1000, 2) .' Kb';
                }
            ),
            array(
                'db'        => 'upload_date',
                'dt'        => 'uploaded',
                'formatter' => function( $d, $row ) {
                    return date( 'h:i a - M d, y', strtotime($d));
                }
            ),
            array( 'db' => 'user_name', 'dt' => 'user_upload' ),
            array(
                'db'        => 'id',
                'dt'        => 'actions',
                'formatter' => function( $d, $row ) use ($canDownloadItem, $canDeleteItem){
                    $confirmationMsg = __('Are you sure you want to delete this item?', true);
                    $secureArchiveParams = $this->Core->encodeParams($d);
                    $urlDownloadFile = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'download', $secureArchiveParams['item_id'], $secureArchiveParams['digest']));
                    $urlDeleteFile = $this->_html->url(array('controller' => 'BuoySystems', 'action' => 'deleteFile',$secureArchiveParams['item_id'], $secureArchiveParams['digest']));
                    
                    $linkDownload = $canDownloadItem ? '<a class="actions-row download download-file-link tooltiped" title="Download" href="'.$urlDownloadFile.'"></a>' : '';
                    $linkDelete = $canDeleteItem ? '<a class="actions-row delete delete-file-link tooltiped" title="Delete" rel="'.$urlDeleteFile.'" conf-msg="'.$confirmationMsg.'"></a>' : '';
                    return $linkDownload.$linkDelete;
                    //return '<span class="actions-row download delete-file-link tooltiped" title="Download" rel="'.$urlDownloadFile.'" conf-msg="'.$confirmationMsg.'">&nbsp;</span>';
                }
            ),
        );
        // DB table to use
        $table = 'archives';
        // Table's primary key
        $primaryKey = 'id';

        $connection = $this->Archive->getDataSource()->getConnection();
        //Aplicar orden favoritos
        array_unshift($request['order'], ['column'=>1, 'dir'=>'desc']);
        $request['columns'][1]['orderable'] = true;

        //var_dump($request);
        $bindings = array();

        $limit = $this->Datatable->limit( $request, $columns );
        $order = $this->Datatable->order( $request, $columns );
        $where = $this->Datatable->filter( $request, $columns, $bindings );
        
        //Filter by current folder
        $where = $where == '' ? "WHERE folder_id = '$folderId' AND deleted = 0" : $where . " AND folder_id = '$folderId' AND deleted = 0";
        $filterFolder = "WHERE folder_id = '$folderId' AND deleted = 0";

        //Get rows
        $query = "
        SELECT `".implode("`, `", $this->Datatable->pluck($columns, 'db'))."`
        FROM `$table`
        $where
        $order
        $limit";

         // Main query to actually get the data
        $data = $this->Datatable->sql_exec( $connection, $bindings, $query);
        $rows = $this->Datatable->data_output($columns, $data);

        //Total filtered rows
        $resFilterLength = $this->Datatable->sql_exec( $connection, $bindings,
            "SELECT COUNT(`{$primaryKey}`) AS tot_filtered
            FROM `$table`
            $where"
        );
        $recordsFiltered = $resFilterLength[0][0];

        //Total rows in table
        $resTotalLength = $this->Datatable->sql_exec( $connection,
			"SELECT COUNT(`{$primaryKey}`) AS tot_rows
             FROM   `$table`
             $filterFolder
             "
        );
		$recordsTotal = $resTotalLength[0][0];
        //$recordsTotal = $this->Archive->find('count');

        $data = [
            "draw" => isset ( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data" => $rows,
        ];
        echo json_encode($data);
    }


    public function refreshItemsFolder() {
        $query = $sort = '';
        $folderItems = $transportador = array();
        $itemId = 0;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) >= 3) {
            if ($params[0] == $this->usercode) {
                $sort = $params[3];
                $query = isset($params[4]) ? $params[4] : '';

                $decodedFolderParams = $this->Core->decodePairParams($params, 1);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $itemId = $folder_received;
                    //$currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);
                    $currentFolder = $this->FolderApp->find('first', [
                        'fields' => ['FolderApp.*', 'BsMetadata.*', 'Client.id', 'Client.name', 'Client.parent', 'AssetMetadata.id','AssetMetadata.unique_id_tag', 'AssetMetadata.created_at'],
                        'conditions'=>[
                            'FolderApp.id' => $folder_received,
                            'FolderApp.deleted' => 0,
                        ],
                        'joins' => [
                            array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                        ]
                    ]);

                    if (!empty($currentFolder)) {
                        Configure::load('folder_names');
                        $folderSettings = Configure :: read('Folders');

                        //Obtener los hijos del folder actual del cliente actual
                        $sort_direction = $sort == 'updated_at' ? 'DESC' : 'ASC';
                        $folderItems = $this->FolderApp->find('all', [
                            'fields' => ['FolderApp.*', 'BsMetadata.*', 'Client.id', 'Client.name', 'Client.parent', 'AssetMetadata.unique_id_tag'],
                            'order' => [ "FolderApp.$sort" => $sort_direction],
                            'conditions'=>[
                                'FolderApp.folder_id' => $folder_received,// el id del folderactual
                                'FolderApp.deleted' => 0,
                            ],
                            'joins' => [
                                array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                            ]
                        ]);

                        //Get parents of current folder
                        $folderBreadcrum = $this->Core->getParentsOfFolderId($folder_received);
                        //Asignar los colores individualmente a los folder raiz
                        if(count($folderBreadcrum)<=1){
                            foreach($folderItems AS $index => $folder){
                                $typeFolder = $folder['FolderApp']['type'];
                                if(isset($folderSettings[$typeFolder]) && isset($folderSettings[$typeFolder]['color'])){
                                    //var_dump($folderSettings[$typeFolder]['color']);
                                    $folderItems[$index]['FolderApp']['color'] = $folderSettings[$typeFolder]['color'];
                                }
                            }
                        }else{//Tiene mas de un elemento el folder, no es raiz
                            $typeFolder = $folderBreadcrum[1]['FolderApp']['type'];
                            $folderParent = $folderSettings[$typeFolder];
                            $colorFolder = $folderParent['color'];
                            foreach($folderItems AS $index => $folder){
                                $folderItems[$index]['FolderApp']['color'] = $colorFolder;
                            }
                        }
                        
                        //$transportador = $this->Conveyor->findById($folder['Bucket']['parent_conveyor']);
                        //$transportador = $transportador['Conveyor'];

                    }
                    //var_dump($folder);
                    $this->set('folder', $currentFolder);
                }
            }
        }

        $this->set('item_id', $itemId);
        //$this->set('conveyor', $transportador);
        $this->set('conveyor_items', $folderItems);
    }


    public function addItemFolder() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $type_item = 'file_folder';
                    
                    $response['success'] = true;
                    $this->set('type_item', $type_item);
                    $this->set('conveyor_id', $folder_received);
                    $this->set('secure_params', $params);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Crea un folder de asset, los que se generan desde el dropdown
     *
     * @return void
     */
    public function saveItemFolder() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data 
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($currentFolder)) {
                        $currentFolder = $currentFolder['FolderApp'];
                        if ($data['folderName'] != '' && $data['folderType'] != '') {
                            $itemSaved = 0;

                            $alreadySavedFolderType = $this->FolderApp->find('all', [
                                'fields' => ['name'],
                                'conditions'=>[
                                    'FolderApp.deleted' => 0,
                                    'FolderApp.folder_id' => $currentFolder['id'],
                                    'FolderApp.type' => $data['folderType'],
                                    'FolderApp.client_id' => $currentFolder['client_id']
                                    ]
                                ]
                            );
                            $coincidences = count($alreadySavedFolderType);
                            /*$buoySystemsClient = !empty($buoySystemsClient) ? Set::extract('/FolderApp/.', $buoySystemsClient) : [];
                            $buoySystemsClient = array_map('strtolower', array_column($buoySystemsClient, 'name'));
                            $coincidences = count(array_keys($buoySystemsClient, strtolower($data['folderName'])));*/
                            $prefixName = $coincidences > 0 ? "-$coincidences" : '';
                            
                            $this->FolderApp->save(array(
                                'name' => $data['folderName'].$prefixName,
                                'folder_id' => $currentFolder['id'],
                                'client_id' => $currentFolder['client_id'],
                                'buoy_system_id' => $currentFolder['buoy_system_id'],
                                'type' => $data['folderType'],
                                'allow_assets' => false,
                                'is_asset_folder' => true,
                                'updated_at' => date('Y-m-d H:i:s')
                            ));

                            Configure::load('folder_names');
                            $folderSettings = Configure :: read('Folders');
                            $folderSavedId = $this->FolderApp->getInsertID();
                            $buoyFolderAssets = $this->FolderApp->findByIdAndDeleted($folderSavedId, 0);
                            $buoyFolderAssets['FolderApp']['type'] = 'sheet_folder';
                            $this->Core->createGenericFoldersInAssetsFolder($buoyFolderAssets, $folderSettings, $currentFolder['buoy_system_id']);

                             // Guardamos la notificacion
                             $this->Notifications->itemSaved(Item::FOLDER, $folderSavedId, $currentFolder);

                            $response['msg'] = __('El folder fue agregado exitosamente', true);
                            $response['success'] = true;
                        } else {
                            $response['msg'] = __('Proporcione todos los campos requeridos', true);
                        }
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Crea un folder tipo container de files como GHMC
     *
     * @return void
     */
    public function saveItemFileFolder() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data 
            parse_str($formdata['formdata'], $data);
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($currentFolder)) {
                        $currentFolder = $currentFolder['FolderApp'];
                        if ($data['item_name'] != '' && $data['item_type'] != '') {

                            //Buscamos file folders del cliente
                            $fileFoldersClient = $this->FileFolder->findAllByClientIdAndDeleted($currentFolder['client_id'], 0);
                            $fileFoldersClient = !empty($fileFoldersClient) ? Set::extract('/FileFolder/.', $fileFoldersClient) : [];
                            $fileFoldersClient = []; //No reestringir duplicados
                            //var_dump($fileFoldersClient);
                            $nameFolderLowerCase = strtolower($data['item_name']);
                            if(!in_array($nameFolderLowerCase, array_column($fileFoldersClient, 'lower_name'))){
                                $itemSaved = 0;
                                $this->FolderApp->save(array(
                                    'name' => $data['item_name'],
                                    'folder_id' => $currentFolder['id'],
                                    'client_id' => $currentFolder['client_id'],
                                    'buoy_system_id' => $currentFolder['buoy_system_id'],
                                    'type' => $data['item_type'],
                                    'allow_assets' => false,
                                    'is_file_folder' => true,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ));
                                $folderSavedId = $this->FolderApp->getInsertID();

                                //Save log file folder
                                $this->FileFolder->save([
                                    'name' => $data['item_name'],
                                    'lower_name' => $nameFolderLowerCase,
                                    'client_id' => $currentFolder['client_id'],
                                ]);

                                // Guardamos la notificacion
                                $this->Notifications->itemSaved(Item::FOLDER_FILE, $folderSavedId, $currentFolder);

                                $response['msg'] = __('El folder fue agregado exitosamente', true);
                                $response['success'] = true;
                            }else{
                                $response['msg'] = __('Folder name already exists', true);
                            }
                        
                        } else {
                            $response['msg'] = __('Proporcione todos los campos requeridos', true);
                        }
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * add action for add new conveyor
     */
    public function add() {


        $userProperties = $this->Core->getRegionCountryAndMarketForUserLogged();
        $perfiles_transportador = $this->PerfilesTransportadores->find('all');
        //$dist_companies = $this->Empresa->findByTypeWithCorporate('distributor');
        $params = $this->request->data; //get data
        $dist_companies = [];
        if (isset($params['did']) && $params['did'] > 0) {
            $dist_companies = $this->Empresa->findById($params['did']);
            $dist_companies = array($dist_companies);
        } else {

            $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region, 0, $userProperties['region'],$userProperties['country'],$userProperties['market']);
            $manager_corporate = Configure::read('manager_corporate');
            if (!is_null($manager_corporate)) {
                if ($this->credentials['role_company'] == UsuariosEmpresa::IS_DIST) {//Si es un manager dis
                    $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', '', $manager_corporate);
                } else {//es manager cli
                    $dist_companies = $this->Empresa->findByIdWithCorporate($this->credentials['parent']);
                    $dist_companies = array($dist_companies);
                }
            }
        }

        $sharedDealers = $this->Core->getSharedDealersSalesperson();
        if(!empty($sharedDealers)){
            $dist_companies = array_merge($dist_companies, $sharedDealers);
        }

        $this->set('distribuidores', $dist_companies);
        $this->set('perfiles', $perfiles_transportador);

        //Obtenemos el market del usuario logueado
        $market_company = $this->credentials["company_market_id"];

        //Si estamos en la vista de cliente, obtenemos su mercado para desplegar el form segun su mercado
        $refer_url = $this->referer('/', true);
        $parse_url_params = Router::parse($refer_url);
        if(isset($parse_url_params["pass"]) && count($parse_url_params["pass"])==2){
            $clientParams = $parse_url_params["pass"];
            $decodedClientParams = $this->Core->decodePairParams($clientParams);
            if ($decodedClientParams['isOk']) {
                $company_received = $decodedClientParams['item_id'];
                $this->Empresa->recursive = 1;
                $company = $this->Empresa->findById($company_received);
                if(!empty($company)){
                    $market_company = !is_null($company["Empresa"]["i_market_id"]) ? $company["Empresa"]["i_market_id"] : $market_company;
                }
            }
        }
    }

    public function save() {

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            if ($data['no_transportador'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['code'] = 0; //Indice de la pestania en activar
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            } else {
                $client_id = $data['client_txt'];
                if ($client_id > 0) {

                    $this->Empresa->recursive = 0;
                    $clientCompany = $this->Empresa->findById($client_id, ['i_country_id']);
                    $country_id = $clientCompany['Empresa']['i_country_id'];

                    $buoySystemData = ['FolderApp' => [
                            'name' => $data['no_transportador'],
                            'client_id' => $client_id,
                            'folder_id' => 0,
                            'type' => 'buoy_system',
                            'allow_assets' => false,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    ];


                    if ($this->FolderApp->save($buoySystemData)) {
                        $buoySystemId = $this->FolderApp->getInsertID();
                        $this->FolderApp->id = $buoySystemId;
                        $cover_img = '';
                        if ($data['path_logo_transportador'] != '') {
                            $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $buoySystemId, $data['path_logo_transportador']);
                            $dataImage = file_get_contents($cover_img);
                            $this->FolderApp->saveField('image', $dataImage);
                        }
                        $this->FolderApp->saveField('cover_img', $cover_img);
        

                        $response['success'] = true;
                        $response['conveyor_number'] = $data['no_transportador'];
                        $response['msg'] = __('The Buoy system %s was saved successfully.', [$data['no_transportador']]);

                         // Guardamos la notificacion
                         $this->Notifications->buoySystemSaved($buoySystemId);


                    } else {
                        $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                    }


                } else {
                    $response['msg'] = __('Favor de proporcionar el cliente', true);
                }
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }


    public function updateMetadataAsset() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                $ancla = $this->request->data;
                $ancla = !empty($ancla) ? $ancla['ancla'] : 0; //for activate tab
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    
                    Configure::load('metadata_fields');
                    $fullFieldsMetadata = Configure::read('Metadata')['fields']['full_assets'];
                    $fieldsMetadata = Configure::read('Metadata')['fields']['asset'];
                    $fieldsMetadataToHide = Configure::read('Metadata')['fields_hide'];
                    
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0, ['type']);
                    $fieldsMetadata[] = 'id';
                    $fieldsMetadata[] = 'created_at';
                    if(!empty($currentFolder) && isset($fieldsMetadataToHide[$currentFolder['FolderApp']['type']])){
                        $fieldsMetadataToHide = $fieldsMetadataToHide[$currentFolder['FolderApp']['type']];
                        $fieldsMetadata = array_values(array_diff($fieldsMetadata, $fieldsMetadataToHide));
                    }
                    
                    $fieldsAssetQuery = array_map(function ($field) {
                        return "AssetMetadata.$field";
                    }, $fieldsMetadata);
                    $fieldsAssetQuery = implode(',',$fieldsAssetQuery);

                    $assetFolder = $this->FolderApp->find('first', [
                        'fields' => ['FolderApp.id','FolderApp.name', 'Client.id', 'Client.name', 'Client.parent', $fieldsAssetQuery],
                        'conditions'=>[
                            'FolderApp.id' => $item_received,
                            'FolderApp.deleted' => 0,
                        ],
                        'joins' => [
                            array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                        ]
                    ]);
                    if (!empty($assetFolder)) {

                        $response['success'] = true;
                        $this->set('assetFolder', $assetFolder);
                        $this->set('metadataFields', $fullFieldsMetadata);
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processUpdateMetadataAsset() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '');
            $processedData = false;
            if (!empty($params) && count($params) == 2) {
                $decodedBuoyParams = $this->Core->decodePairParams($params);
                if ($decodedBuoyParams['isOk']) {
                    $item_received = $decodedBuoyParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                    if (!empty($currentFolder)) {
                        $client_received = $currentFolder['Client']['id'];
                        $assetId = $data['assetId'];
                        $metadataValues = $data['Metadata'];
                        ksort($metadataValues);
                        $metadataValues = [$metadataValues];
                        $request = "/buoysystems/$item_received/assets/$assetId";

                        $request = new CakeRequest($request);
                        $request->data('data',$metadataValues);
                        $response = new CakeResponse();
                        $d = new Dispatcher();
                        $d->dispatch(
                            $request,
                            $response
                        );
                        $processedData = true;
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            if (!$processedData) {
                echo json_encode($response);
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function updateMetadata() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                $ancla = $this->request->data;
                $ancla = !empty($ancla) ? $ancla['ancla'] : 0; //for activate tab
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                    if (!empty($currentFolder)) {

                        $response['success'] = true;
                        $this->set('folderApp', $currentFolder);

                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processUpdateMetadata() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '');
            $processedData = false;
            if (!empty($params) && count($params) == 2) {
                $decodedBuoyParams = $this->Core->decodePairParams($params);
                if ($decodedBuoyParams['isOk']) {
                    $buoy_received = $decodedBuoyParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($buoy_received, 0);
                    if (!empty($currentFolder)) {
                        $client_received = $currentFolder['Client']['id'];
                        $metadataValues = $data['Metadata'];
                        ksort($metadataValues);
                        $metadataValues = [$metadataValues];
                        $request = "/clients/$client_received/buoysystems/$buoy_received";

                        $request = new CakeRequest($request);
                        $request->data('data',$metadataValues);
                        $response = new CakeResponse();
                        $d = new Dispatcher();
                        $d->dispatch(
                            $request,
                            $response
                        );
                        $processedData = true;
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            if (!$processedData) {
                echo json_encode($response);
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function update() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                $ancla = $this->request->data;
                $ancla = !empty($ancla) ? $ancla['ancla'] : 0; //for activate tab
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                    if (!empty($currentFolder)) {

                        $response['success'] = true;
                        $this->set('folderApp', $currentFolder);

                        $path_imagen_portada = $currentFolder['FolderApp']['cover_img'];

                        $perfiles_transportador = $this->PerfilesTransportadores->find('all');
                        //$dist_companies = $this->Empresa->findByTypeWithCorporate('distributor');

                        //Fix load distributors for managers
                        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region);
                        $manager_corporate = Configure::read('manager_corporate');
                        if (!is_null($manager_corporate)) {
                            if ($this->credentials['role_company'] == UsuariosEmpresa::IS_DIST) {//Si es un manager dis
                                $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', '', $manager_corporate);
                            } else {//es manager cli
                                $dist_companies = $this->Empresa->findByIdWithCorporate($this->credentials['parent']);
                                $dist_companies = array($dist_companies);
                            }
                        }
                        //$dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region);

                        $sharedDealers = $this->Core->getSharedDealersSalesperson();
                        if(!empty($sharedDealers)){
                            $dist_companies = array_merge($dist_companies, $sharedDealers);
                        }

                        $this->set('path_imagen_portada', $path_imagen_portada);
                        $this->set('distribuidores', $dist_companies);
                        $this->set('perfiles', $perfiles_transportador);

                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processUpdate() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedBuoyParams = $this->Core->decodePairParams($params);
                if ($decodedBuoyParams['isOk']) {
                    $buoy_received = $decodedBuoyParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($buoy_received, 0);
                    if (!empty($currentFolder)) {
                        if ($data['no_transportador'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                        } else {
                            $client_id = $data['client_txt'];
                            if ($client_id > 0) {

                                $currentFolder['FolderApp']['name'] = $data['no_transportador'];
                                $currentFolder['FolderApp']['updated_at'] = date('y-m-d h:i:s');

                                if ($this->FolderApp->save($currentFolder)) {
                                    $cover_img = $currentFolder['FolderApp']['cover_img'];
                                    if ($data['path_logo_transportador'] != '') {
                                        $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $buoy_received, $data['path_logo_transportador']);
                                        $image = file_get_contents($cover_img);
                                        $currentFolder['FolderApp']['image'] = $image;
                                    }

                                    $currentFolder['FolderApp']['cover_img'] = $cover_img;
                                    $this->FolderApp->save($currentFolder);

                                    $response['conveyor_number'] = $data['no_transportador'];
                                    $response['success'] = true;
                                    $response['msg'] = __('The Buoy system %s was updated successfully.', [$data['no_transportador']]);

                                } else {
                                    $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                                }
                            } else {
                                $response['msg'] = __('Favor de proporcionar el cliente', true);
                            }
                        }
                    } else {
                        $response['msg'] = __('El transportador que intenta actualizar no existe', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function remove() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                    if (!empty($currentFolder)) {

                        $this->FolderApp->id = $item_received;
                        $this->FolderApp->saveField('deleted', true);

                        $parentFolder = $this->FolderApp->findById($currentFolder['FolderApp']['folder_id']);

                        //Eliminamos del catalogo de file folders del cliente, fisicamente
                        if($currentFolder['FolderApp']['is_file_folder']){
                            $nameFolderLowerCase = strtolower($currentFolder['FolderApp']['name']);
                            $fileFolder = $this->FileFolder->findByLowerName($nameFolderLowerCase);
                            if(!empty($fileFolder)){
                                $this->FileFolder->delete($fileFolder['FileFolder']['id']);
                            }
                        }

                        switch ($currentFolder['FolderApp']['type']) {
                            case 'buoy_system':
                                $this->Notifications->buoySystemDeleted($item_received);
                                //$this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, Item::CONVEYOR, $item[$typeItem]['numero']);
                            break;
                        }

                        if($currentFolder['FolderApp']['type'] === 'buoy_system'){

                        } else if($currentFolder['FolderApp']['is_asset_folder']){
                            $this->Notifications->itemDeleted(Item::FOLDER, $item_received, $parentFolder['FolderApp']);
                        }else{ //is file folder
                            $this->Notifications->itemDeleted(Item::FOLDER_FILE, $item_received, $parentFolder['FolderApp']);
                        }
                        
                        $response['msg'] = __('El elemento fue eliminado exitosamente', true);
                        $response['success'] = true;

                    } else {
                        $response['msg'] = __('Error, el elemento a eliminar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function changeStatus() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedBuoyParams = $this->Core->decodePairParams($params);
                if ($decodedBuoyParams['isOk']) {
                    $buoy_received = $decodedBuoyParams['item_id'];
                    $this->FolderApp->id = $buoy_received;
                    $this->FolderApp->saveField('status', $data['new_status']);

                    $response['success'] = true;
                    $response['conveyor_number'] = $this->FolderApp->field('name');
                    $response['msg'] = __('The buoy system status was updated successfully.', true);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function addFileConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];

                    $response['success'] = true;
                    $this->set('conveyor_id', $conveyor_received);
                    $this->set('secure_params', $params);
                    $this->Session->delete('UserIdData');
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function setFavoriteFile(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedArchiveParams = $this->Core->decodePairParams($params);
                if ($decodedArchiveParams['isOk']) {
                    $file_received = $decodedArchiveParams['item_id'];
                    $is_favorite = $params[2] == 1 ? 0 : 1;
                    $this->Archive->id = $file_received;
                    $this->Archive->saveField('is_favorite', $is_favorite);//update current copies
                    $response['msg'] = __('La informacion se proceso correctamente', true);
                    $response['success'] = true;
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function bulkDownloadFiles() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) { 
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);

                    if(!empty($currentFolder)){
                        //$folderBreadcrum = $this->Core->getParentsOfFolderId($folder_received);
                        $filesDownload = $this->request->data; //get data
                        $filesDownload = $filesDownload['fileItems'];
                        if(!empty($filesDownload)){

                            /*$file_zip = tempnam("tmp", "zip");
                            $zip = new ZipArchive();
                            if ($zip->open($file_zip, ZipArchive::CREATE) === TRUE) {
                                foreach ($reports AS $report) {
                                    $report = $report['Reporte'];
                                    $zip->addFile($report['file'], $report['nombre'] . '.pdf');
                                }
                                $zip->close();
    
    
                                $file_name = $conveyor['Conveyor']['numero'];
                                header("Content-Type: application/zip");
                                header("Content-Length: " . filesize($file_zip));
                                header("Content-Disposition: attachment; filename=\"$file_name.zip\"");
                                readfile($file_zip);
                                unlink($file_zip);
                            } else {
                                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                            }*/

                            $fileIds = [];
                            foreach($filesDownload AS $fileData){
                                $fileData = [$fileData['signature'],$fileData['digest']];
                                $decodedFileData = $this->Core->decodePairParams($fileData);
                                if ($decodedFileData['isOk']) {
                                    $fileIds[] = $decodedFileData['item_id'];
                                }
                            }
                            
                            $relative_path = 'uploads/tmpfiles/files'.uniqid().'.zip';
                            $path_to_zip_file = _ABSOLUTE_PATH.$relative_path;
                            //$file_zip = tempnam("tmp", "zip");
                            $zip = new ZipArchive();
                            if ($zip->open($path_to_zip_file, ZipArchive::CREATE) === TRUE) {
                                $files = $this->Archive->find('all', [
                                    'conditions' => [ 'id' => $fileIds]
                                ]);
    
                                if(!empty($files)){
                                    $files = Set::extract('/Archive/.', $files);
                                    $tmpFiles = [];
                                    $filesAdded = [];
                                    //Add files to zip
                                    foreach($files AS $file){
                                        $fileName = $file['name'].'-'.uniqid().'.'.$file['extension'];
                                        $path_to_save_file = _ABSOLUTE_PATH.'uploads/tmpfiles/'.$fileName;
                                        $tmpFiles[] = $path_to_save_file;
                                        file_put_contents($path_to_save_file, $file['file']);
                                        $zip->addFile($path_to_save_file, $fileName);
                                    }
                                    $zip->close();
                                    //Remove tmp files
                                    if(!empty($tmpFiles)){
                                        foreach($tmpFiles AS $tmpFile){
                                            @unlink($tmpFile);
                                        }
                                    }
                                    $response['zip'] = $this->site.$relative_path;
                                    $response['msg'] = __('La informacion se proceso correctamente', true);
                                    $response['success'] = true;
                                }else{
                                    $response['msg'] = __('Files not founded', true);
                                }
                            }else{
                                $response['msg'] = __('Error on create zip', true);
                            }
                        }else{
                            $response['msg'] = __('There are not files to delete', true);
                        }
                    }else{
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function preview(){
        $this->layout = false;
        $this->autoRender = false;
        $error = true;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) >= 2) {
            $decodedArchiveParams = $this->Core->decodePairParams($params);
            if ($decodedArchiveParams['isOk']) {
                $file_received = $decodedArchiveParams['item_id'];
                $file = $this->Archive->findByIdAndDeleted($file_received, 0);
                if(!empty($file)){
                    $file = $file['Archive'];
                    $type = $file['extension'];
                    switch($type){
                        case 'pdf':case 'PDF':
                            header('Content-type: application/pdf');
                            echo $file['file'];
                            exit();
                            //echo '<object data="data:application/pdf;base64,'.base64_encode($file['file']).'" type="application/pdf" style="height:97%;width:100%"></object>';
                            $error = false;
                            break;
                        case 'jpg': case 'jpeg': case 'png': case 'gif':
                            echo '<img src="data:image/'.$type.';base64,'.base64_encode( $file['file'] ).'"/>';
                            $error = false;
                        break;
                        default:
                            $error = true;
                        break;
                    }
                }
            }
        }

        if($error) {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function download(){
        $this->layout = false;
        $this->autoRender = false;
        $error = true;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) >= 2) {
            $decodedArchiveParams = $this->Core->decodePairParams($params);
            if ($decodedArchiveParams['isOk']) {
                $file_received = $decodedArchiveParams['item_id'];
                $file = $this->Archive->findByIdAndDeleted($file_received, 0);
                if(!empty($file)){
                    $file = $file['Archive'];
                    $type = $file['extension'];
                    switch($type){
                        
                        case 'pdf':case 'PDF':
                            header('Content-type: application/pdf');
                        break;
                        case 'doc':case 'DOC':
                            header('Content-type: application/msword'); 
                        break;
                        case 'docx':case 'DOCX':
                            header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                        break;
                        case 'xls':case 'XLS':
                            header('Content-type: application/vnd.ms-excel'); 
                        break;
                        case 'xlsx':case 'XLSX':
                            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        break;
                        case 'ppt':case 'PPT':
                            header('Content-type: application/vnd.ms-powerpoint'); 
                        break;
                        case 'pptx':case 'PPTX':
                            header('Content-type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
                        break;
                        default:
                            header('Content-type: application/octet-stream');
                        break;
                    }
                    $size = $file['size'];
                    $nameFile = $file['name'].'.'.$type;
                    $filedata = $file['file'];
                    header("Content-length: ".strlen($filedata));
                    header("Content-Disposition: attachment; filename=".$nameFile);
                    header("Content-Description: Imodco File Data");
                    echo $filedata;
                    $error = false;
                }
            }
        }

        if($error) {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function bulkDeleteFiles() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) { 
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);

                    if(!empty($currentFolder)){
                        $folderBreadcrum = $this->Core->getParentsOfFolderId($folder_received);
                        $filesDelete = $this->request->data; //get data
                        $filesDelete = $filesDelete['filesDelete'];
                        if(!empty($filesDelete)){
                            $fileId = 0;
                            foreach($filesDelete AS $fileData){
                                $fileData = [$fileData['signature'],$fileData['digest']];
                                $decodedFileData = $this->Core->decodePairParams($fileData);
                                if ($decodedFileData['isOk']) {
                                    $fileId = $decodedFileData['item_id'];
                                    $this->Archive->id = $fileId;
                                    $this->Archive->saveField('deleted', true);
                                }
                            }

                            if($fileId > 0){
                                $response['msg'] = __('La informacion se proceso correctamente', true);
                                $response['success'] = true;

                                $archive = $this->Archive->findById($fileId);
                                $buoySystem = $this->FolderApp->findById($archive['Archive']['buoy_system_id']);
                                //Save log
                                $this->Notifications->itemDeleted(Item::FILE, $archive['Archive']['folder_id'], $buoySystem['FolderApp']);
                            }
                        }else{
                            $response['msg'] = __('There are not files to delete', true);
                        }
                    }else{
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function deleteFile(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedArchiveParams = $this->Core->decodePairParams($params);
                if ($decodedArchiveParams['isOk']) {
                    $file_received = $decodedArchiveParams['item_id'];
                    $this->Archive->id = $file_received;
                    $this->Archive->saveField('deleted', true);//update current copies
                    $response['msg'] = __('La informacion se proceso correctamente', true);
                    $response['success'] = true;

                    $archive = $this->Archive->findById($file_received);
                    $buoySystem = $this->FolderApp->findById($archive['Archive']['buoy_system_id']);
                    //Save log
                    $this->Notifications->itemDeleted(Item::FILE, $archive['Archive']['folder_id'], $buoySystem['FolderApp']);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function updateDocname(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedArchiveParams = $this->Core->decodePairParams($params);
                if ($decodedArchiveParams['isOk']) {
                    $file_received = $decodedArchiveParams['item_id'];
                    $this->Archive->id = $file_received;
                    $this->Archive->saveField('name', $formdata['docname']);
                    $response['msg'] = __('La informacion se proceso correctamente', true);
                    $response['success'] = true;
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    

    public function saveFileToFolder() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {

            
            // Obtener el id y name del user
            $userId = $this->credentials['id'];
            $userName = $this->credentials['name'];
            if(is_null($userId)){
                $userId = $params[2];
                $this->UsuariosEmpresa->recursive = 0;
                $usuario = $this->UsuariosEmpresa->findById($userId, ['id', 'name']);
                if(!empty($usuario)){
                    $userId = $usuario['UsuariosEmpresa']['id'];
                    $userName = $usuario['UsuariosEmpresa']['name'];
                }
            }

            //$formdata = $this->request->data; //get data
            //parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0);

                    if(!empty($currentFolder)){
                        $folderBreadcrum = $this->Core->getParentsOfFolderId($folder_received);
                        $buoySystemFolder = $folderBreadcrum[0]['FolderApp']; 

                        //Subimos a un directorio temporal
                        $output_dir = _ABSOLUTE_PATH."uploads/tmp/";

                        $ret = array();
                        $error =$_FILES["imodco_files"]["error"];

                        if(!is_array($_FILES["imodco_files"]["name"])){ //Single file
                            $path = $_FILES["imodco_files"]["name"];
                            $ext = pathinfo($path, PATHINFO_EXTENSION);
                            $fileName = str_replace($ext, '', $this->Core->sanitize($path)).'.'.$ext;
                            move_uploaded_file($_FILES["imodco_files"]["tmp_name"],$output_dir.$fileName);
                            $ret[]= $fileName;
                        }
                        else{
                            $fileCount = count($_FILES["imodco_files"]["name"]);
                            for($i=0; $i < $fileCount; $i++){
                                $path = $_FILES["imodco_files"]["name"][$i];
                                $ext = pathinfo($path, PATHINFO_EXTENSION);
                                $fileName = str_replace($ext, '', $this->Core->sanitize($path)).'.'.$ext;
                                move_uploaded_file($_FILES["imodco_files"]["tmp_name"][$i],$output_dir.$fileName);
                                $ret[]= $fileName;
                            }
                        }
                        
                        //$userId = null;

                        //En $ret tenemos los archivos, ahora hay que moverlos al lugar correcto
                        foreach($ret AS $uploaded_file){
                            $path_file = $output_dir.$uploaded_file;
                            $buoy_file = new File($path_file);
                            if ($buoy_file->exists()) {
                                $diferencial = '_' . time();
                                $sha_company = sha1($buoySystemFolder['client_id']);
                                $sha_buoy_system = sha1($buoySystemFolder['id']);
    
//                                 new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
//                                 new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _BUOYS_FOLDER, true); //true para crearlo sino existe el folder
//                                 new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _BUOYS_FOLDER . '/' . $sha_buoy_system, true); //true para crearlo sino existe el folder
//                                 new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _BUOYS_FOLDER . '/' . $sha_buoy_system . '/' . _FILES_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
                                $dest_path_file = _COMPANY_DATA . $sha_company . '/' . _BUOYS_FOLDER . '/' . $sha_buoy_system . '/' . _FILES_FOLDER;
    
                                $dest_name_file = $buoy_file->name() . $diferencial;
                                $name_file = $dest_name_file . '.' . $buoy_file->ext();     
                                
                                $nameFileNoExt = $buoy_file->name();
                                $sizeFile = $buoy_file->size();
                                $extension = $buoy_file->ext();

                                $dest_path_file = $dest_path_file . '/' . $name_file;
                                $buoy_file->copy($dest_path_file, true);
                                $file_data = file_get_contents($dest_path_file);
                                
                                //Guardar los archivos en S3
                                $this->Aws->putObjectOnS3($dest_path_file, $path_file);
                                
                                
                                //End guardar

                                //$file_report = $dest_path_file;
                                $buoy_file->delete(); //Eliminamos el archivo anterior  
                                
                                $archive = ['Archive' => [
                                        'name' => $nameFileNoExt,
                                        'extension' => $extension,
                                        'size' => $sizeFile,
                                        'path' => $dest_path_file,
                                        'file' => $file_data,
                                        'buoy_system_id' => $buoySystemFolder['id'],
                                        'folder_id' => $folder_received,
                                        'user_id' => $userId,
                                        'user_name' => $userName,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]
                                ];

                                $this->Archive->create();
                                $this->Archive->save($archive);
                            }
                        }

                        // Guardamos la notificacion
                        $this->Notifications->itemSaved(Item::FILE, $folder_received, $buoySystemFolder);
                        echo json_encode($ret);

                    }else{
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function importAssetsFromXls() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedClientParams = $this->Core->decodePairParams($params);
                if ($decodedClientParams['isOk']) {
                    $client_received = $decodedClientParams['item_id'];
                    $response['success'] = true;
                    $this->set('client_id', $client_received);
                    $this->set('secure_params', $params);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processImportAssetsExcel() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            $overwrite = $formdata['overwrite'];
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form
            $response = array('success' => false, 'msg' => '');
            $processedData = false;
            if (!empty($params) && count($params) == 2) {
                $decodedFolderParams = $this->Core->decodePairParams($params);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    //Get the bs
                    $buoySystem = $this->FolderApp->findByIdAndDeleted($folder_received, 0);
                    //Obtenemos todos los asset del cliente con informacion asociada

                    //Filter/filtrar in model relation/relacion
                    //Obtenemos todos los assets folder que ya tengan informacion asociada del buoy system id 
                    $assetsBuoySystem = $this->AssetMetadata->find('all',[
                        'fields' => ['unique_id_tag'],
                        'conditions' => [
                            'Asset.buoy_system_id' => $buoySystem['FolderApp']['id'],
                        ],
                        'contain' => ['Asset']
                    ]);
                    $assetsBuoySystem = !empty($assetsBuoySystem) ? Set::extract('/AssetMetadata/.', $assetsBuoySystem) : [];
                    $assetsBuoySystem = array_map('strtolower', array_column($assetsBuoySystem, 'unique_id_tag'));
                   
                    $fileData = [];
                    $filename = $data['item_name'];
                    $filepath = $data['path_item'];
                    $type = PHPExcel_IOFactory::identify($filepath);
                    $objReader = PHPExcel_IOFactory::createReader($type);

                    $objPHPExcel = $objReader->load($filepath);
                    $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
                    foreach($rowIterator as $row){
                        $cellIterator = $row->getCellIterator();
                        foreach ($cellIterator as $cell) {
                            // $fileData[$row->getRowIndex()][$cell->getColumn()] = $cell->getCalculatedValue();
                            $cellValue= $cell->getCalculatedValue();
                            if(PHPExcel_Shared_Date::isDateTime($cell)) {
                                $cellValue = date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($cellValue));
                                $cellValue = date("d/m/Y", strtotime($cellValue . ' +1 day'));
                            }
                            $fileData[$row->getRowIndex()][] = $cellValue;
                        }
                    }

                    $assetUniqueIdsFile = [];
                    $assetsMetadata = [];
                    for($i=1;$i<count($fileData[1]);$i++){//Iteramos sobre las columnas
                        $assetData = [];
                        $emptyValues = 0;
                        foreach($fileData AS $rowIndex => $row){ //iteramos sobre cada una de las filas
                            $cellValue = $row[$i];
                            $assetData[] = $cellValue;
                            $emptyValues = is_null($cellValue) || $cellValue === 'x' ? $emptyValues + 1 : $emptyValues;
                        }

                        $assetUniqueIdsFile[] = strtolower($assetData[1]);//para comparar los names
                        array_push($assetsMetadata, $assetData );
                        if((count($assetData) == $emptyValues) || ($emptyValues+1 == count($assetData))){//Si todos los valores del array son nulos
                            array_pop($assetsMetadata);
                            array_pop($assetUniqueIdsFile);
                        }
                    }

                    $coincidences = array_intersect($assetUniqueIdsFile, $assetsBuoySystem);
                    if(empty($coincidences) || $overwrite >= 0){ //if bss not exist
                        $request = "/buoysystems/$folder_received/assets";
                
                        $request = new CakeRequest($request);
                        $request->data('data',$assetsMetadata);
                        $response = new CakeResponse();
                        $d = new Dispatcher();
                        $d->dispatch(
                            $request,
                            $response
                        );
                        $processedData = true;
                    }else{
                        //Obtener los nombres de assets duplicados
                        $duplicatedAssets = $this->AssetMetadata->find('all',[
                            'fields' => ['Asset.name', 'AssetMetadata.unique_id_tag'],
                            'conditions' => [
                                'AssetMetadata.unique_id_tag' => $coincidences,
                                'Asset.buoy_system_id' => $buoySystem['FolderApp']['id'],
                            ],
                            'contain' => ['Asset']
                        ]);
                        $uniqueIds = array_map(function($duplicatedAsset){ return $duplicatedAsset['AssetMetadata']['unique_id_tag']; }, $duplicatedAssets);

                        $response['duplicated'] = true;
                        $response['msg'] = __('The assets with unique id tags [<b>%s</b>], already contains metadata. <br> Do you want to overwrite it?', implode(', ', $uniqueIds));
                    }
                    
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            if (!$processedData) {
                echo json_encode($response);
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }







    /**
     * action for conveyor view
     */
    public function View() {

        $this->set('title_for_layout', 'Hose');
        $this->set('options_toolbar', 'items-conveyors');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = null;

                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);

                if (!empty($conveyor)) {
                    //$conveyorItems = $this->Conveyor->getItemsConveyor($conveyor_received);
                    $isUSConveyor = $conveyor["Conveyor"]["is_us_conveyor"];
                    if($isUSConveyor){
                        $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                    }

                    $full_conveyor = $conveyor;
                    $transportador = $conveyor['Conveyor'];
                    $empresa = $conveyor['Empresa'];
                    $distribuidor = $conveyor['Distribuidor'];
                    $ultrasonic = $conveyor['Ultrasonic'];

                    $companyRegion = $this->Empresa->find('first',['recursive'=>0, 'fields'=>['region'],'conditions'=>['Empresa.id'=>$empresa['id']]]);

                    $perfil_transportador = $this->PerfilesTransportadores->findById($transportador['perfil']);

                    $has_failed_date = false;
                    if($transportador["is_us_conveyor"]){
                        $has_failed_date = $full_conveyor["TabInstalledBelt"]["installation_date"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["installation_date"]!='' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                    }else{
                        $has_failed_date = $full_conveyor["Conveyor"]["banda_fecha_instalacion"]!='0000-00-00' && $full_conveyor["Conveyor"]["banda_fecha_instalacion"]!='' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                    }

                    $this->set('has_failed_date', $has_failed_date);

                    $this->set('log_rows', array());
                    $this->set('recommended_info_assoc', array());
                    $this->set('perfil_transportador', $perfil_transportador['PerfilesTransportadores']);
                    $this->set('conveyor', $transportador);
                    $this->set('ultrasonic', $ultrasonic);
                    $this->set('company', $empresa);
                    $this->set('companyRegion', $companyRegion['Empresa']);
                    $this->set('dealer', $distribuidor);

                    //$this->set('conveyor_items', $conveyorItems);
                    $response['success'] = true;

                    $this->setJsVar('uploadNicEditReportAx', $this->_html->url(array('controller' => 'Uploader', 'action' => 'uploadNicEditReport')));

                    $secureClientConveyorParams = $this->Core->encodeParams($transportador['id_company']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));

                    $secureConveyorParams = $this->Core->encodeParams($conveyor_received);
                    $urlQrCodeConveyor = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'conveyorQr', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
                    $urlReportsConveyor = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'reportingHistory', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
                    $urlRemoveItem = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'remove', Item::CONVEYOR, $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
                    $urlEditItem = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'update', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));

                    $reports = $isUSConveyor ? $this->UsConveyor->getReportsConveyor($conveyor_received) : $this->Conveyor->getReportsConveyor($conveyor_received);
                    $urlDownloadReportsConveyor = !empty($reports) ? $this->_html->url(array('controller' => 'Conveyors', 'action' => 'downloadReportingHistory', $secureConveyorParams['item_id'], $secureConveyorParams['digest'])) : '';
                    $urlDownloadFullReportConveyor = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'downloadFullReport', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));

                    $this->setJsVar('filterAreasData', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'getFilterAreasSubareas',$secureConveyorParams['item_id'], $secureConveyorParams['digest'])));
                    $this->setJsVar('setAreaSubAreaUrl', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'setFiltersAreaToConveyor',$secureConveyorParams['item_id'], $secureConveyorParams['digest'])));
                    $this->setJsVar('refreshItemsConveyorAx', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'refreshItemsConveyor', $this->usercode, $secureConveyorParams['item_id'], $secureConveyorParams['digest'])));


                    $this->setJsVar('secureConveyor', $secureConveyorParams);
                    $this->set('urlQrCodeConveyor', $urlQrCodeConveyor);
                    $this->set('urlreportingHistoryConveyor', $urlReportsConveyor);
                    $this->set('urlDownloadReportingHistoryConveyor', $urlDownloadReportsConveyor);
                    $this->set('urlFullReportConveyor', $urlDownloadFullReportConveyor);

                    $this->set('urlRemoveItem', $urlRemoveItem);
                    $this->set('urlReturnRemove', $this->referer());
                    $this->set('urlEditItem', $urlEditItem);
                    $this->set('assocDealerConveyor', $distribuidor['id']);
                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $this->set('assocClientConveyor', $transportador['id_company']);


                    $this->set('sePuedeCalcularVidaEstimada', !$isUSConveyor ? $this->Core->sePuedeCalcularVidaEstimada($full_conveyor): false);
                    $this->set('sePuedeCalcularBandaRecomendada', !$isUSConveyor ? $this->Core->sePuedeCalcularBandaRecomendada($full_conveyor): false);
                    $this->set('isUsConveyor', $isUSConveyor);

                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';


                    $this->openCssToInclude[] = 'plugins/Assets/css/lightbox/jquery.lightbox';
                    $this->openJsToInclude[] = 'plugins/Assets/js/lightbox/jquery.lightbox.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ajaxQ/ajaxq';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Conveyors/conveyor_view';
                    $this->set('jsToInclude', $this->jsToInclude);

                    $this->Core->setTutorialSectionViewed(7);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    

    /**
     * dashboard action for dashboard view
     */
    public function dashboard() {
        $this->set('options_toolbar', 'search-conveyors');


        $query = $sort = '';
        $activeTab = 'admin';
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $sort = $params[1];
                $query = isset($params[2]) ? $params[2] : '';
            }
        }

        $filter_companies = $this->Core->getCompaniesFilterAccordingUserLogged();
        $this->Session->write(parent::ASSOC_COMPANIES, $filter_companies);

        $conveyors = $this->Core->getConveyorsBasicFieldsUsingFilters($filter_companies, $query, $sort);
        $this->openJsToInclude[] = 'plugins/Assets/js/ajaxQ/ajaxq';


        $this->jsToInclude[] = 'application/Conveyors/dashboard';
        $this->jsToInclude[] = 'scrolling';
        $this->set('jsToInclude', $this->jsToInclude);


        $autocompleteConveyors = $this->Core->initAutocompleteConveyors($conveyors);
        $this->setJsVar('autocompleteConveyors', $autocompleteConveyors);
        $this->setJsVar('totConveyors', count($conveyors));

        $empresaUser = $this->credentials['id_empresa'];
        $secureCompanyParams = $this->Core->encodeParams($empresaUser);
        $this->set('secureClient',$secureCompanyParams);

        if($this->credentials['role'] === UsuariosEmpresa::IS_CLIENT){
            $empresaCliente = $this->credentials['id_empresa'];
            $company = $this->Empresa->findByIdWithCorporate($empresaCliente);

            $companyRelations = $this->Empresa->findById($empresaCliente, ['Empresa.id']);
            $this->set('areas', $companyRelations['Areas']);
            $this->set('subareas', $companyRelations['Subareas']);

            //GetDealer
            $companyDealer = $this->Empresa->findByIdWithCorporate($company['Empresa']['parent']);
            $userDealer = $this->UsuariosEmpresa->find("first",array(
                'conditions' => array('id_empresa' => $company['Empresa']['parent'])
            ));
            $userDealer = !empty($userDealer) ? $userDealer["UsuariosEmpresa"] : $userDealer;

            //GetAdminRegion
            $empresaAdmin = $this->Empresa->find("first", array(
                'conditions' => array('region' => $companyDealer["Empresa"]["region"], "type" => "admin")
            ));
            $userAdmin = $this->UsuariosEmpresa->find("first",array(
                'conditions' => array('id_empresa' => $empresaAdmin["Empresa"]['id'])
            ));
            $userAdmin = !empty($userAdmin) ? $userAdmin["UsuariosEmpresa"] : $userAdmin;

            $empresa = $company['Empresa'];
            $corporativo = $company['Corporativo'];
            $this->set('empresa', $empresa);

            $this->set('empresa_dealer', $companyDealer);
            $this->set('usuario_dealer', $userDealer);
            $this->set('usuario_admin', $userAdmin);

            $this->set('corporativo', $corporativo);
            $this->set('distribuidor', $company['Distribuidor']);

            $this->openCssToInclude[] = 'plugins/Assets/css/multiple-select/multiple-select';
            $this->set('openCssToInclude', $this->openCssToInclude);
            $this->cssToInclude[] = 'reset';
            $this->set('cssToInclude', $this->cssToInclude);

            $this->openJsToInclude[] = 'plugins/Assets/js/multiple-select/multiple-select';

        }

        $this->set('openJsToInclude', $this->openJsToInclude);

        //$this->setJsVar('conveyorsDataReload', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'dashboard', $this->usercode)));
        //$this->set('conveyors', $conveyors);

        $this->Core->setTutorialSectionViewed(5);
    }


    


    

    public function copy() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {
                        $current_copies = $item["Conveyor"]["copies"];

                        $numberCopy =  str_pad(($current_copies+1), 2, "0", STR_PAD_LEFT);
                        $numberCopy = $item["Conveyor"]["numero"]." (copy $numberCopy)";
                        unset($item["Conveyor"]["id"]);//remove index of id
                        $item["Conveyor"]["numero"] = $numberCopy;

                        if ($this->Conveyor->save($item)) {
                            $conveyor_id = $this->Conveyor->getInsertID();


                            if($item["Conveyor"]["is_us_conveyor"]){
                                $this->uses[] = 'UsTabConveyor';
                                $this->uses[] = 'UsTabIdler';
                                $this->uses[] = 'UsTabInstalledBelt';
                                $this->uses[] = 'UsTabMaterial';
                                $this->uses[] = 'UsTabPulley';
                                $this->uses[] = 'UsTabRemark';
                                $this->uses[] = 'UsTabTransitionZone';
                                $this->uses[] = 'UsTabWearLife';

                                $installed_belt_tab = $this->UsTabInstalledBelt->findByConveyorId($item_received);
                                $material_tab = $this->UsTabMaterial->findByConveyorId($item_received);
                                $wear_life_tab = $this->UsTabWearLife->findByConveyorId($item_received);
                                $conveyor_tab = $this->UsTabConveyor->findByConveyorId($item_received);
                                $idlers_tab = $this->UsTabIdler->findByConveyorId($item_received);
                                $pulleys_tab = $this->UsTabPulley->findByConveyorId($item_received);
                                $transition_zone_tab = $this->UsTabTransitionZone->findByConveyorId($item_received);
                                $remarks_tab = $this->UsTabRemark->findByConveyorId($item_received);

                                $installed_belt_tab["UsTabInstalledBelt"]["conveyor_id"] = $conveyor_id;
                                $material_tab["UsTabMaterial"]["conveyor_id"] = $conveyor_id;
                                $wear_life_tab["UsTabWearLife"]["conveyor_id"] = $conveyor_id;
                                $conveyor_tab["UsTabConveyor"]["conveyor_id"] = $conveyor_id;
                                $idlers_tab["UsTabIdler"]["conveyor_id"] = $conveyor_id;
                                $pulleys_tab["UsTabPulley"]["conveyor_id"] = $conveyor_id;
                                $transition_zone_tab["UsTabTransitionZone"]["conveyor_id"] = $conveyor_id;
                                $remarks_tab["UsTabRemark"]["conveyor_id"] = $conveyor_id;

                                unset($installed_belt_tab["UsTabInstalledBelt"]["id"],$material_tab["UsTabMaterial"]["id"],
                                    $wear_life_tab["UsTabWearLife"]["id"],$conveyor_tab["UsTabConveyor"]["id"],
                                    $idlers_tab["UsTabIdler"]["id"],$pulleys_tab["UsTabPulley"]["id"],
                                    $transition_zone_tab["UsTabTransitionZone"]["id"],$remarks_tab["UsTabRemark"]["id"]);//remove index of id

                                $this->UsTabInstalledBelt->save($installed_belt_tab);
                                $this->UsTabMaterial->save($material_tab);
                                $this->UsTabWearLife->save($wear_life_tab);
                                $this->UsTabConveyor->save($conveyor_tab);
                                $this->UsTabIdler->save($idlers_tab);
                                $this->UsTabPulley->save($pulleys_tab);
                                $this->UsTabTransitionZone->save($transition_zone_tab);
                                $this->UsTabRemark->save($remarks_tab);

                            }else{//Is Mx
                                $installed_belt_tab = $this->TabInstalledBelt->findByConveyorId($item_received);
                                $conveyor_tab = $this->TabConveyor->findByConveyorId($item_received);
                                $idlers_tab = $this->TabIdler->findByConveyorId($item_received);
                                $pulleys_tab = $this->TabPulley->findByConveyorId($item_received);
                                $transition_zone_tab = $this->TabTransitionZone->findByConveyorId($item_received);
                                $remarks_tab = $this->TabRemark->findByConveyorId($item_received);

                                $installed_belt_tab["TabInstalledBelt"]["conveyor_id"] = $conveyor_id;
                                $conveyor_tab["TabConveyor"]["conveyor_id"] = $conveyor_id;
                                $idlers_tab["TabIdler"]["conveyor_id"] = $conveyor_id;
                                $pulleys_tab["TabPulley"]["conveyor_id"] = $conveyor_id;
                                $transition_zone_tab["TabTransitionZone"]["conveyor_id"] = $conveyor_id;
                                $remarks_tab["TabRemark"]["conveyor_id"] = $conveyor_id;

                                unset($installed_belt_tab["TabInstalledBelt"]["id"],$conveyor_tab["TabConveyor"]["id"],
                                    $idlers_tab["TabIdler"]["id"],$pulleys_tab["TabPulley"]["id"],
                                    $transition_zone_tab["TabTransitionZone"]["id"],$remarks_tab["TabRemark"]["id"]);//remove index of id

                                $this->TabInstalledBelt->save($installed_belt_tab);
                                $this->TabConveyor->save($conveyor_tab);
                                $this->TabIdler->save($idlers_tab);
                                $this->TabPulley->save($pulleys_tab);
                                $this->TabTransitionZone->save($transition_zone_tab);
                                $this->TabRemark->save($remarks_tab);
                            }

                            //Update data of conveyor copied
                            $this->$typeItem->id = $item_received;
                            $this->$typeItem->saveField('copies', $current_copies+1);//update current copies

                            $response['msg'] = __('La informacion se proceso correctamente', true);
                            $response['success'] = true;
                        }else{
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                        }

                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    

    public function removeItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {

                        $this->$typeItem->id = $item_received;
                        $this->$typeItem->saveField('eliminada', true);

                        if($typeItem != Item::CONVEYOR){
                            $conveyor = $this->Conveyor->findById($item[$typeItem]['parent_conveyor']);
                            $conveyor = $conveyor['Conveyor'];
                        }


                        $response['msg'] = __('El elemento fue eliminado exitosamente', true);
                        $response['success'] = true;


                        switch ($typeItem) {
                            case Item::CONVEYOR:
                                $this->Notifications->conveyorDeleted($item_received);


                                $this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, Item::CONVEYOR, $item[$typeItem]['numero']);
                                break;
                            case Item::IMAGE:case Item::VIDEO:case Item::FOLDER: case Item::NOTE:
                                $this->Notifications->itemDeleted($typeItem, $item_received, $conveyor);

                                $this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, $typeItem, $item[$typeItem]['nombre']);
                                break;
                            case Item::FILE:
                                $this->Notifications->itemDeleted($typeItem, $item_received, $conveyor);
                            break;
                            case Item::REPORT:
                                $this->Notifications->reportDeleted($item_received);
                                $this->Notifications->itemDeleted($typeItem, $item_received, $conveyor);


                                $this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, Item::REPORT, $item[$typeItem]['nombre']);
                                break;
                        }

                    } else {
                        $response['msg'] = __('Error, el elemento a eliminar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    
    public function editAssetFolder() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    //$item = $this->FolderApp->findByIdAndDeleted($item_received, 0);

                    $item = $this->FolderApp->find('first', [
                        'fields' => ['FolderApp.id','FolderApp.name', 'Client.id', 'Client.name', 'Client.parent', 'AssetMetadata.id', 'AssetMetadata.unique_id_tag'],
                        'conditions'=>[
                            'FolderApp.id' => $item_received,
                            'FolderApp.deleted' => 0,
                        ],
                        'joins' => [
                            array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                        ]
                    ]);
                    
                    if (!empty($item)) {
                        $response['success'] = true;
                        $this->set('item_id', $item_received);
                        $this->set('item', $item);
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processEditAssetFolder() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->FolderApp->findByIdAndDeleted($item_received, 0);
                    if (!empty($item)) {
                        if ($data['item_name'] != '') {
                            if($data['assetId']==""){//no tiene asset asociado, se le crea
                                $this->AssetMetadata->save([
                                    'AssetMetadata' => [
                                        'folder_app_id' => $item_received,
                                        'unique_id_tag' => $data['unique_id']
                                    ]
                                ]);
                            }else{
                                $this->AssetMetadata->id = $data['assetId'];
                                $this->AssetMetadata->saveField('unique_id_tag', $data['unique_id']);
                            }
                            
                            $this->FolderApp->id = $item_received;
                            $this->FolderApp->saveField('name', $data['item_name']);
                            //$this->Secure->saveBrowsingData(BrowsingLog::ACTUALIZACION, $typeItem, $item[$typeItem]['nombre']);

                            $response['success'] = true;
                            $response['msg'] = __('El elemento fue editado exitosamente', true);
                        } else {
                            $response['msg'] = __('Proporcione todos los campos requeridos', true);
                        }
                    } else {
                        $response['msg'] = __('Error, el elemento a editar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function editItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {
                        $response['success'] = true;
                        $this->set('type_item', $typeItem);
                        $this->set('item_id', $item_received);
                        $this->set('item', $item);
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processEditItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {
                        if ($data['item_name'] != '' && $data['item_description'] != '') {
                            $conveyor = $this->Conveyor->findById($item[$typeItem]['parent_conveyor']);
                            $conveyor = $conveyor['Conveyor'];
                                    
                            $this->$typeItem->id = $item_received;
                            $this->$typeItem->saveField('nombre', $data['item_name']);
                            switch ($typeItem) {
                                case Item::IMAGE: case Item::VIDEO://nombre y descripcion
                                    $this->$typeItem->saveField('descripcion', $data['item_description']);
                                    break;
                                case Item::NOTE://nombre y contenido
                                    $this->$typeItem->saveField('contenido', $data['item_description']);
                                    break;
                            }
                            
                            $this->Notifications->itemEdited($typeItem, $item_received, $conveyor);

                            /*
                             * Guardamos log de navegacion 
                             */
                            $this->Secure->saveBrowsingData(BrowsingLog::ACTUALIZACION, $typeItem, $item[$typeItem]['nombre']);

                            $response['success'] = true;
                            $response['msg'] = __('El elemento fue editado exitosamente', true);
                        } else {
                            $response['msg'] = __('Proporcione todos los campos requeridos', true);
                        }
                    } else {
                        $response['msg'] = __('Error, el elemento a editar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function toggleItemSmartview() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'class' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {
                        $class = $item[$typeItem]['in_smartview'] == 0 ? 'active-on-smartview' : 'inactive-on-smartview';
                        $item[$typeItem]['in_smartview'] = $item[$typeItem]['in_smartview'] == 0 ? 1 : 0;
                        $response['class'] = $class;
                        if ($this->$typeItem->save($item)) {
                            $response['success'] = true;
                        }
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function togglePrivateFolder() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'class' => '');
            if (!empty($params) && count($params) == 3) {
                $typeItem = $params[0];
                $decodedItemsParams = $this->Core->decodePairParams($params, 1);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->$typeItem->findById($item_received);
                    if (!empty($item)) {
                        $class = $item[$typeItem]['is_private'] == 0 ? 'active' : '';
                        $item[$typeItem]['is_private'] = $item[$typeItem]['is_private'] == 0 ? 1 : 0;
                        $response['class'] = $class;
                        if ($this->$typeItem->save($item)) {
                            $response['success'] = true;
                        }
                    } else {
                        $response['msg'] = __('Error, el elemento no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function refreshItemsConveyor() {
        $query = $sort = '';
        $conveyorItems = $transportador = $full_conveyor = array();
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) >= 3) {
            if ($params[0] == $this->usercode) {
                $sort = $params[3];
                $query = isset($params[4]) ? $params[4] : '';

                $decodedConveyorParams = $this->Core->decodePairParams($params, 1);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);

                    if (!empty($conveyor)) {
                        $isUSConveyor = $conveyor["Conveyor"]["is_us_conveyor"];
                        if($isUSConveyor){
                            $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                        }

                        $full_conveyor = $conveyor;
                        $transportador = $conveyor['Conveyor'];
                        $conveyorItems = $this->Conveyor->getItemsConveyor($conveyor_received, $query, $sort);
                        $empresa = $conveyor['Empresa'];
                        $distribuidor = $conveyor['Distribuidor'];
                        $ultrasonic = $conveyor['Ultrasonic'];
                        $ultrasonic_readings = $conveyor['UltrasonicReading'];
                        //$perfil_transportador = $this->PerfilesTransportadores->findById($transportador['perfil']);
                        //$this->set('perfil_transportador', $perfil_transportador['PerfilesTransportadores']);
                        $this->set('company', $empresa);
                        $this->set('ultrasonic', $ultrasonic);
                        $this->set('ultrasonic_readings', $ultrasonic_readings);
                        $this->set('dealer', $distribuidor);
                        $has_failed_date = false;
                        $has_failed_date = false;
                        if($transportador["is_us_conveyor"]){
                            $has_failed_date = $full_conveyor["TabInstalledBelt"]["installation_date"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["installation_date"]!='' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                        }else{
                            $has_failed_date = $full_conveyor["Conveyor"]["banda_fecha_instalacion"]!='0000-00-00' && $full_conveyor["Conveyor"]["banda_fecha_instalacion"]!='' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $full_conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                        }
                        $this->set('has_failed_date', $has_failed_date);
                    }
                }
            }
        }

        $this->set('conveyor', $transportador);
        $this->set('full_conveyor', $full_conveyor);
        $this->set('conveyor_items', $conveyorItems);
    }

    

    public function ultrasonic() {

        $this->set('title_for_layout', 'Ultrasonic');
        $this->set('options_toolbar', 'ultrasonic-section');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];

                //If not assoc ultrasonic, assoc
                $this->Core->addUltrasonicAssocIfNotHave($conveyor_received);

                $ultrasonic = $this->Ultrasonic->find('first', array(
                    'conditions' => array('Ultrasonic.conveyor_id' => $conveyor_received),
                    'fields' => array('Ultrasonic.*', 'Conveyor.id', 'Conveyor.numero', 'Conveyor.banda_marca', 'Conveyor.banda_fecha_instalacion', 'Empresa.id', 'Empresa.name'),
                    'joins' => array(
                        array('table' => 'conveyors', 'type' => 'INNER', 'alias' => 'Conveyor', 'conditions' => array('Ultrasonic.conveyor_id = Conveyor.id')),
                        array('table' => 'empresas', 'type' => 'INNER', 'alias' => 'Empresa', 'conditions' => array('Conveyor.id_company = Empresa.id'))
                    )
                        )
                );

                /* Si hay ultrasonic * */
                if (!empty($ultrasonic)) {
                    Configure::load('settings');
                    $this->set('units_conveyor', Configure :: read('Settings.units_conveyor'));

                    if($ultrasonic['Ultrasonic']['other_width']==''){
                        $width_typed = $ultrasonic['Ultrasonic']['ultrasonic_width'];
                        $widths = [1, 0, 0, 0, 0, 0, $width_typed-1];
                        for($i=1;$i<count($widths)-1;$i++){
                            $widths[$i] = (($width_typed-2)/6) * $i;
                            $widths[$i] = number_format($widths[$i],0,'','');
                        }
                        $this->Ultrasonic->id = $ultrasonic['Ultrasonic']['id'];
                        $this->Ultrasonic->saveField('other_width', implode(',',$widths));
                        $ultrasonic_row = $this->Ultrasonic->findById($ultrasonic['Ultrasonic']['id']);
                        $ultrasonic_row = $ultrasonic_row['Ultrasonic'];
                    }else{
                        $ultrasonic_row = $ultrasonic['Ultrasonic'];
                    }



                    $transportador = $ultrasonic['Conveyor'];
                    $empresa = $ultrasonic['Empresa'];
                    $id_ultrasonic = $ultrasonic_row['id'];

                    /*                     * Get readings for ultrasonic * */
                    $ultrasonic_readings = $this->UltrasonicReading->find('all', array(
                        'conditions' => array('UltrasonicReading.ultrasonic_id' => $id_ultrasonic, 'UltrasonicReading.deleted' => 0),
                        'order' => array('reading_date' => 'ASC')
                    ));
                    //$ultrasonic_widths = $this->Core->getUltrasonicWidths();
                    //$ultrasonic_widths_metric = $this->Core->getUltrasonicWidthsMetric();

                    $ultrasonic_widths = Configure::read('ConveyorUS')['installed_belt']['widths']['imperial'];
                    array_shift($ultrasonic_widths);
                    $ultrasonic_widths_metric = Configure::read('ConveyorUS')['installed_belt']['widths']['metric'];
                    array_shift($ultrasonic_widths_metric);

                    $ultrasonic_compounds = $this->Core->getCompoundMatrixValues();

                    $secureUltrasonicParams = $this->Core->encodeParams($id_ultrasonic);
                    $secureConveyorParams = $this->Core->encodeParams($transportador['id']);
                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);

                    $abrasionLifeData = [];
                    $urlDownloadUltrasonicData = '';
                    $plot_data = $this->Core->getUltrasonicPlotData($ultrasonic_readings, $ultrasonic_row, $transportador);
                    /*echo '<pre>';
                    var_dump($plot_data);
                    echo '</pre>';*/
                    if (!empty($ultrasonic_readings)) {//Si tiene lecturas
                        $urlDownloadUltrasonicData = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'downloadUltrasonicConveyorData', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
                        //Calcular abrasion life
                        $abrasionLifeData = $this->Core->calcAbrasionLife($conveyor_received);
                    }

                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);

                    $this->setJsVar('otherWidthNeeded', __('Favor de proporcionar todos los campos requeridos', true));
                    $this->setJsVar('abrasionLifeData', $abrasionLifeData);
                    $this->setJsVar('compoundNames', $ultrasonic_compounds);
                    $this->setJsVar('updateUltrasonicDataAx', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'updateUltrasonicConveyorData', $secureUltrasonicParams['item_id'], $secureUltrasonicParams['digest'])));
                    $this->setJsVar('chartData', $plot_data);
                    $this->setJsVar('chartTitle', __('COVER WEAR MEASUREMENTS', true));
                    $this->setJsVar('vAxisLabel', $ultrasonic_row["units"]=='imperial' ? __('label_pulgadas_plot', true):__('label_pulgadas_plot_metric', true));

                    $this->openJsToInclude[] = 'plugins/Assets/js/inputmask/jquery.inputmask.bundle';
                    $this->jsToInclude[] = 'application/Conveyors/ultrasonic';

                    $this->set('jsToInclude', $this->jsToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->set('ultrasonic', $ultrasonic_row);
                    $this->set('ultrasonic_widths', $ultrasonic_widths);
                    $this->set('ultrasonic_widths_metric', $ultrasonic_widths_metric);
                    $this->set('ultrasonic_compounds', $ultrasonic_compounds);

                    $this->set('conveyor', $transportador);
                    $this->set('ultrasonic_data', $ultrasonic_readings);
                    $this->set('urlDownloadUltrasonicData', $urlDownloadUltrasonicData);
                    $this->set('company', $empresa);

                    $this->set('abrasionLifeData',$abrasionLifeData);
                    $this->Core->setTutorialSectionViewed(12);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ultrasonicData() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedUltrasonicParams = $this->Core->decodePairParams($params);

                //Obtenemos los datos del ultrasonic
                if ($decodedUltrasonicParams['isOk']) {//Existe el ultrasonic
                    $ultrasonic_received = $decodedUltrasonicParams['item_id'];
                    $ultrasonic = $this->Ultrasonic->findById($ultrasonic_received);

                    $ultrasonic_widths = $this->Core->getUltrasonicWidths();
                    $ultrasonic_widths_metric = $this->Core->getUltrasonicWidthsMetric();

                    if (!empty($ultrasonic)) {

                        $ultrasonicReading = array();
                        //SI estamos actualizando una lectura, cargar los datos de la lectura
                        if (isset($params[2])) {
                            $decodedUltrasonicReadingParams = $this->Core->decodePairParams($params, 2);
                            if ($decodedUltrasonicReadingParams['isOk']) {
                                $ultrasonicReading = $this->UltrasonicReading->findById($decodedUltrasonicReadingParams['item_id']);
                                $ultrasonicReading = $this->Converter->process_convertion_ultrasonic($ultrasonicReading, $language=null, $ultrasonic);
                            }
                        }

                        $this->set('ultrasonic', $ultrasonic);
                        $this->set('ultrasonic_widths', $ultrasonic_widths);
                        $this->set('ultrasonic_widths_metric', $ultrasonic_widths_metric);
                        $this->set('ultrasonicData', $ultrasonicReading);
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Error al consultar datos', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    
    public function downloadNoAuthUltrasonic() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $error = true;
        if (!empty($params) && count($params)==4) {
            $decodedUltrasonicParams = $this->Core->decodePairParams($params);
            $decodedUserParams = $this->Core->decodePairParams($params,2);            
            if ($decodedUltrasonicParams['isOk'] && $decodedUserParams['isOk']) {
                $ultrasonic_received = $decodedUltrasonicParams['item_id'];
                
                $ultrasonic = $this->Ultrasonic->find('first', array(
                    'conditions' => array('Ultrasonic.id' => $ultrasonic_received),
                    'fields' => array('Ultrasonic.*', 'Conveyor.id', 'Conveyor.numero', 'Conveyor.banda_marca', 'Conveyor.banda_fecha_instalacion', 'Empresa.id', 'Empresa.name'),
                    'joins' => array(
                        array('table' => 'conveyors', 'type' => 'INNER', 'alias' => 'Conveyor', 'conditions' => array('Ultrasonic.conveyor_id = Conveyor.id')),
                        array('table' => 'empresas', 'type' => 'INNER', 'alias' => 'Empresa', 'conditions' => array('Conveyor.id_company = Empresa.id'))
                    )
                        )
                );

                /* Si hay ultrasonic * */
                if (!empty($ultrasonic)) {
                    $ultrasonic_row = $ultrasonic['Ultrasonic'];
                    $transportador = $ultrasonic['Conveyor'];
                    $empresa = $ultrasonic['Empresa'];
                    $id_ultrasonic = $ultrasonic_row['id'];

                    /*                     * Get readings for ultrasonic * */
                    $ultrasonic_readings = $this->UltrasonicReading->find('all', array(
                        'conditions' => array('UltrasonicReading.ultrasonic_id' => $id_ultrasonic, 'UltrasonicReading.deleted' => 0),
                        'order' => array('reading_date' => 'ASC')
                    ));

                    if (!empty($ultrasonic_readings)) {
                        $error = false;

                        $plot_data = $this->Core->getUltrasonicPlotData($ultrasonic_readings, $ultrasonic_row, $transportador);
                        //$specifications = $this->Core->getSpecificationsUltrasonic($ultrasonic_row, $transportador);
                        $specifications = array();
                        $readings = $this->Core->getUltrasonicDatesAndMeasured($ultrasonic_readings, $ultrasonic_row, $transportador);
                        $statistic_projection_date = $this->Core->getUltrasonicStatisticProjectionsData($ultrasonic_readings, $ultrasonic_row, $transportador);
                        $banda_marca = $ultrasonic_row['conveyor_brand_ultra'] == '' ? $transportador['banda_marca'] : $ultrasonic_row['conveyor_brand_ultra'];

                        $titleChart = $empresa['name'] . "\n" . $transportador['numero'] . "\n" . $banda_marca . "\n" . __('COVER WEAR MEASUREMENTS', true);
                        //$options = array('title' => __('CONTINENTAL CONTITECH SELECT ULTRASONIC GAUGE REPORT', true), 'chartTitle' => $titleChart);
                        $options = array('title' => '', 'chartTitle' => $titleChart);
                        
                        $data = array('plotData' => $plot_data, 'options' => $options, 'tables' => array($specifications, $readings, $statistic_projection_date));
                        $response = $this->CustomSocket->send('https://tools.contiplus.net/ExcelMaker/createChart', $data);
                        $name_file = $this->Core->sanitize('Ultrasonic-C' . $transportador['numero']) . '.xlsx';
                        $file = _PATH_GENERIC_TMP_FILES.$name_file;
                        file_put_contents($file, $response);
                        $this->response->file($file, array(
                            'download' => true,
                            'name' => $name_file
                        ));
                        $this->response->header('Content-Disposition', 'inline;filename=' . $name_file);
                        return $this->response;
                    }
                }
            }
        }
        if ($error) {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadUltrasonicConveyorData() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $error = true;
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];

                $ultrasonic = $this->Ultrasonic->find('first', array(
                    'conditions' => array('Ultrasonic.conveyor_id' => $conveyor_received),
                    'fields' => array('Ultrasonic.*', 'Conveyor.id', 'Conveyor.numero', 'Conveyor.banda_marca', 'Conveyor.banda_fecha_instalacion', 'Empresa.id', 'Empresa.name'),
                    'joins' => array(
                        array('table' => 'conveyors', 'type' => 'INNER', 'alias' => 'Conveyor', 'conditions' => array('Ultrasonic.conveyor_id = Conveyor.id')),
                        array('table' => 'empresas', 'type' => 'INNER', 'alias' => 'Empresa', 'conditions' => array('Conveyor.id_company = Empresa.id'))
                    )
                        )
                );

                /* Si hay ultrasonic * */
                if (!empty($ultrasonic)) {
                    $ultrasonic_row = $ultrasonic['Ultrasonic'];
                    $transportador = $ultrasonic['Conveyor'];
                    $empresa = $ultrasonic['Empresa'];
                    $id_ultrasonic = $ultrasonic_row['id'];

                    /*                     * Get readings for ultrasonic * */
                    $ultrasonic_readings = $this->UltrasonicReading->find('all', array(
                        'conditions' => array('UltrasonicReading.ultrasonic_id' => $id_ultrasonic, 'UltrasonicReading.deleted' => 0),
                        'order' => array('reading_date' => 'ASC')
                    ));

                    if (!empty($ultrasonic_readings)) {
                        $error = false;

                        $plot_data = $this->Core->getUltrasonicPlotData($ultrasonic_readings, $ultrasonic_row, $transportador);
                        //$specifications = $this->Core->getSpecificationsUltrasonic($ultrasonic_row, $transportador);
                        $specifications = array();
                        $readings = $this->Core->getUltrasonicDatesAndMeasured($ultrasonic_readings, $ultrasonic_row, $transportador);                        
                        $statistic_projection_date = $this->Core->getUltrasonicStatisticProjectionsData($ultrasonic_readings, $ultrasonic_row, $transportador);                        
                        $banda_marca = $ultrasonic_row['conveyor_brand_ultra'] == '' ? $transportador['banda_marca'] : $ultrasonic_row['conveyor_brand_ultra'];

                        $titleChart = $empresa['name'] . "\n" . $transportador['numero'] . "\n" . $banda_marca . "\n" . __('COVER WEAR MEASUREMENTS', true);
                        //$options = array('title' => __('CONTINENTAL CONTITECH SELECT ULTRASONIC GAUGE REPORT', true), 'chartTitle' => $titleChart);
                        $options = array('title' => '', 'chartTitle' => $titleChart);

                        $data = array('plotData' => $plot_data, 'options' => $options, 'tables' => array($specifications, $readings, $statistic_projection_date));
                        $response = $this->CustomSocket->send('https://tools.contiplus.net/ExcelMaker/createChart', $data);
                        $name_file = $this->Core->sanitize('Ultrasonic-C' . $transportador['numero']) . '.xlsx';
                        $file = _PATH_GENERIC_TMP_FILES.$name_file;
                        file_put_contents($file, $response);

                        $this->response->file($file, array(
                            'download' => true,
                            'name' => $name_file
                        ));
                        $this->response->header('Content-Disposition', 'inline;filename=' . $name_file);
                        return $this->response;
                    }
                }
            }
        }
        if ($error) {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function saveUltrasonicData() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUltrasonicParams = $this->Core->decodePairParams($params);
                if ($decodedUltrasonicParams['isOk']) {
                    $ultrasonic_received = $decodedUltrasonicParams['item_id'];
                    $ultrasonic = $this->Ultrasonic->findById($ultrasonic_received);
                    if (!empty($ultrasonic)) {//Si existe el ultrasonic asociado

                        $assocConveyor = $this->Conveyor->findById($ultrasonic['Ultrasonic']['conveyor_id'],['id_company']);
                        $client_id = $assocConveyor['Conveyor']['id_company'];

                        $data = $this->request->data; //get data
                        $data['ultrasonic_id'] = $ultrasonic_received;
                        $data['temperature'] = !isset($data['temperature']) || $data['temperature'] == '' ? 0 : $data['temperature'];
                        $data['reading_date'] = $this->Core->transformUsDatetoMysqlFormat($data['reading_date']);
                        $data['filled_lang'] = $this->Core->_app_language;
                        $data['updated_at'] = date('Y-m-d H:i:s');
                        $data['conveyed_tons'] = str_replace(',', '', $data['conveyed_tons']);

                        $id_ultrasonic_reading = $data['reading_id'];
                        unset($data['reading_id']);

                        if ($id_ultrasonic_reading == 0) {
                            $data['created_at'] = date('Y-m-d H:i:s');
                            $this->UltrasonicReading->set($data);
                            if ($this->UltrasonicReading->save()) {
                                $response['success'] = true;
                                $response['msg'] = __('La lectura se guardo exitosamente', true);                           
                                $id_ultrasonic_reading = $this->UltrasonicReading->getInsertID();
                                $this->Notifications->ultrasonicReadingSaved($ultrasonic_received,$id_ultrasonic_reading);

                                //save score card statistic
                                $salespersonAssoc = $this->Core->getSalespersonIfExists($client_id);
                                if($salespersonAssoc>0){
                                    $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_READING_ULTRA);
                                }
                                /*
                                if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                                    $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_READING_ULTRA);
                                }*/
                            } else {
                                $response['msg'] = __('Error al guardar lectura, intentelo nuevamente', true);
                            }
                        } else {
                            $data = $this->Core->fixDataForUpdate($data);
                            if ($this->UltrasonicReading->updateAll($data, array('id' => $id_ultrasonic_reading))) {
                                $response['success'] = true;
                                $response['msg'] = __('La lectura se actualizo exitosamente', true);
                                $this->Notifications->ultrasonicReadingUpdated($ultrasonic_received,$id_ultrasonic_reading);
                            } else {
                                $response['msg'] = __('Error al actualizar lectura, intentelo nuevamente', true);
                            }
                        }
                    } else {
                        $response['msg'] = __('Error al consultar datos', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function updateUltrasonicConveyorData() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUltrasonicParams = $this->Core->decodePairParams($params);
                if ($decodedUltrasonicParams['isOk']) {
                    $ultrasonic_received = $decodedUltrasonicParams['item_id'];
                    $ultrasonic = $this->Ultrasonic->findById($ultrasonic_received);
                    if (!empty($ultrasonic)) {
                        $data = $this->request->data; //get data
                        $data['install_update_ultra'] = $this->Core->transformUsDatetoMysqlFormat($data['install_update_ultra']);
                        /*if($data['other_width']=="yes"){

                        }else{
                            $data['other_width'] = '';
                        }*/
                        $width_typed = $data['ultrasonic_width'];
                        $widths = [1, 0, 0, 0, 0, 0, $width_typed-1];
                        for($i=1;$i<count($widths)-1;$i++){
                            $widths[$i] = (($width_typed-2)/6) * $i;
                            $widths[$i] = number_format($widths[$i],0,'','');
                        }
                        $data['other_width'] = implode(',',$widths);


                        $data = $this->Core->fixDataForUpdate($data);
                        if(!isset($data['conveyor_price'])){
                            $data['conveyor_price'] = 0;
                        }else{
                            $data['conveyor_price'] = str_replace('$', '', $data['conveyor_price']);
                            $data['conveyor_price'] = str_replace(',', '', $data['conveyor_price']);
                        }

                        if ($this->Ultrasonic->updateAll($data, array('id' => $ultrasonic_received))) {
                            $response['success'] = true;
                            $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                            
                            $this->Notifications->ultrasonicUpdated($ultrasonic_received);
                            
                        } else {
                            $response['msg'] = __('Ocurrio un error al guardar los datos, intentelo nuevamente.');
                        }
                    } else {
                        $response['msg'] = __('Error al consultar datos', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * action for conveyor view
     */
    public function Item() {
        $this->set('options_toolbar', 'items-folder');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 3) {
            $typeItem = $params[0];
            $decodedItemsParams = $this->Core->decodePairParams($params, 1);
            if ($decodedItemsParams['isOk']) {
                $item_received = $decodedItemsParams['item_id'];
                $item = $this->$typeItem->find('first', array('conditions' => array('id' => $item_received, 'eliminada' => 0)));
                if (!empty($item)) {

                    $item = $item[$typeItem];
                    $items_folder = array();
                    $conveyor = $this->Conveyor->findByIdWithCompany($item['parent_conveyor']);
                    $parent_item_id = 0;
                    switch ($typeItem) {
                        case Item::REPORT:
                            $this->response->file($item['file']);
                            $this->response->header('Content-Disposition', 'inline');
                            return $this->response;
                            break;
                        case Item::FILE:
                            $this->response->file($item['path']);
                            $this->response->header('Content-Disposition', 'inline');
                            return $this->response;
                            break;
                        case Item::VIDEO:
                            $this->openCssToInclude[] = 'plugins/Assets/css/videojs/video-js';
                            $this->openJsToInclude[] = 'plugins/Assets/js/videojs/video';
                            $this->set('openCssToInclude', $this->openCssToInclude);
                            $this->set('openJsToInclude', $this->openJsToInclude);
                            $this->setJsVar('swfPath', $this->site . 'plugins/Assets/js/videojs/video-js.swf');

                            $mp4File = $this->site . $item['path'] . '.mp4';
                            //$oldPathVideo = strtotime($item['creada'])<strtotime('2017-07-20 00:00:00') ? $this->site . $item['path'] . '.flv':'';
                            $oldPathVideo = strtotime($item['creada'])<strtotime('2017-07-20 00:00:00') || ($item['cargada_desde']=='MOVIL' && strtotime($item['creada'])<=strtotime('2018-02-28 00:00:00')) ? $this->site . $item['path'] . '.flv':'';
                            $this->setJsVar('oldPathVideo', $oldPathVideo);
                            $this->setJsVar('pathVideo', $mp4File);

                            $this->set('oldPathVideo', $oldPathVideo);
                            $this->set('pathVideo', $mp4File);
                            $parent_item_id = $item['parent_folder'] != 0 ? $item['parent_folder'] : $item['parent_conveyor'];
                            break;
                        case Item::IMAGE:
                            $parent_item_id = $item['parent_folder'] != 0 ? $item['parent_folder'] : $item['parent_conveyor'];
                            break;
                        case Item::FOLDER:
                            $this->set('is_folder', true);
                            $this->openCssToInclude[] = 'plugins/Assets/css/lightbox/jquery.lightbox';
                            $this->openJsToInclude[] = 'plugins/Assets/js/lightbox/jquery.lightbox.min';
                            $this->set('openCssToInclude', $this->openCssToInclude);
                            $this->set('openJsToInclude', $this->openJsToInclude);
                            //$items_folder = $this->Conveyor->getItemsFolder($item_received);

                            $secureFolderParams = $this->Core->encodeParams($item_received);
                            $this->setJsVar('refreshItemsFolderAx', $this->_html->url(array('controller' => 'Conveyors', 'action' => 'refreshItemsFolder', $this->usercode, $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                            $parent_item_id = $item['parent_conveyor'];
                            $this->Core->setTutorialSectionViewed(9);
                            break;
                    }



                    $secureParentItem = $this->Core->encodeParams($parent_item_id);
                    $secureItemConveyor = $this->Core->encodeParams($item_received);
                    $urlEditItem = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'editItem', $typeItem, $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                    $urlRemoveItem = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'remove', $typeItem, $secureItemConveyor['item_id'], $secureItemConveyor['digest']));

                    $urlReturnRemove = '';
                    if ($typeItem == Item::FOLDER || $item['parent_folder'] == 0) {
                        $urlReturnRemove = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'View', $secureParentItem['item_id'], $secureParentItem['digest']));
                    } else {
                        $urlReturnRemove = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'Item', Item::FOLDER, $secureParentItem['item_id'], $secureParentItem['digest']));
                    }


                    $this->set('urlEditItem', $urlEditItem);
                    $this->set('urlRemoveItem', $urlRemoveItem);
                    $this->set('urlReturnRemove', $urlReturnRemove);


                    //Obtenemos los comentarios
                    $comments_item = $this->Comment->getCommentsItemByType($item_received, $typeItem);

                    $this->set('comments_item', $comments_item);
                    $this->set('type_item', $typeItem);

                    $this->set('item', $item);
                    $transportador = $conveyor['Conveyor'];
                    $empresa = $conveyor['Empresa'];

                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);

                    $this->set('conveyor', $transportador);
                    $this->set('company', $empresa);
                    $this->set('folder_items', $items_folder);
                    $response['success'] = true;

                    $this->setJsVar('uploadNicEditReportAx', $this->_html->url(array('controller' => 'Uploader', 'action' => 'uploadNicEditReport')));
                    $secureConveyorParams = $this->Core->encodeParams($item_received);
                    $this->setJsVar('secureConveyor', $secureConveyorParams);


                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Conveyors/item_view';
                    $this->set('jsToInclude', $this->jsToInclude);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function smartView() {
        $this->set('options_toolbar', 'smart-view');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 3) {
            $typeItem = $params[0];
            $decodedItemsParams = $this->Core->decodePairParams($params, 1);
            if ($decodedItemsParams['isOk']) {
                $item_received = $decodedItemsParams['item_id'];
                $item = $this->$typeItem->findById($item_received);
                if (!empty($item)) {
                    $item = $item[$typeItem];
                    $items_folder = array();
                    $conveyor = $this->Conveyor->findByIdWithCompany($item['parent_conveyor']);
                    //$items_folder = $this->Conveyor->getSmartItemsFolder($item_received);

                    $notes = $this->Conveyor->getSmartItemsByType(Item::NOTE, $item['parent_conveyor'], $item_received);
                    $images = $this->Conveyor->getSmartItemsByType(Item::IMAGE, $item['parent_conveyor'], $item_received);
                    //$videos = $this->Conveyor->getSmartItemsByType(Item::VIDEO, $item['parent_conveyor'], $item_received);

                    $folder_items = array($notes, $images);

                    $this->set('item', $item);
                    $transportador = $conveyor['Conveyor'];
                    $empresa = $conveyor['Empresa'];

                    $this->set('conveyor', $transportador);
                    $this->set('company', $empresa);
                    $this->set('folder_items', $folder_items);

                    $this->jsToInclude[] = 'application/Conveyors/smartview';
                    $this->set('jsToInclude', $this->jsToInclude);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function exportSmartview() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedItemsParams = $this->Core->decodePairParams($params);
            if ($decodedItemsParams['isOk']) {
                $item_received = $decodedItemsParams['item_id'];
                $item = $this->Bucket->findById($item_received);
                if (!empty($item)) {
                    $item = $item['Bucket'];
                    $items_folder = array();
                    $conveyor = $this->Conveyor->findByIdWithCompany($item['parent_conveyor']);
                    $distribuidor = $this->Empresa->findById($conveyor['Empresa']['parent']);

                    $notes = $this->Conveyor->getSmartItemsByType(Item::NOTE, $item['parent_conveyor'], $item_received);
                    $images = $this->Conveyor->getSmartItemsByType(Item::IMAGE, $item['parent_conveyor'], $item_received);

                    $folder_items = array($notes, $images);

                    $this->set('item', $item);
                    $this->set('conveyor', $conveyor);
                    $this->set('distribuidor', $distribuidor);
                    $this->set('folder_items', $folder_items);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function dataSheet() {
        $this->set('options_toolbar', 'datasheet-conveyor');

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $buoysystem_received = $decodedConveyorParams['item_id'];

                $buoyFolder = $this->FolderApp->findByIdAndDeleted($buoysystem_received, 0);
                if (!empty($buoyFolder)) {
                    $empresa = $buoyFolder['Client'];
                    
                    $folderBreadcrum = $this->Core->getParentsOfFolderId($buoysystem_received);
                    array_push($folderBreadcrum, ['FolderApp'=>['name' => __('Metadata',true)]]);

                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);

                    //$transportador = $conveyor['Conveyor'];
                    $this->set('buoySystem', $buoyFolder);
                    $this->set('folderBreadcrum', $folderBreadcrum);
                    //$this->set('comments_item', $comments_item);
                    $this->set('company', $empresa);
                    //$this->set('dealer', $distribuidor);

                    /*
                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);
*/
                    $this->jsToInclude[] = 'application/Conveyors/conveyor_datasheet';
                    $this->set('jsToInclude', $this->jsToInclude);

                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function dataSheetAsset() {
        $this->set('options_toolbar', 'datasheet-conveyor');

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $folder_received = $decodedConveyorParams['item_id'];

                Configure::load('metadata_fields');
                $fullFieldsMetadata = Configure::read('Metadata')['fields']['full_assets'];
                $fieldsMetadata = Configure::read('Metadata')['fields']['asset'];
                $fieldsMetadataToHide = Configure::read('Metadata')['fields_hide'];
                
                $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0, ['type']);
                $fieldsMetadata[] = 'id';
                $fieldsMetadata[] = 'created_at';
                if(!empty($currentFolder) && isset($fieldsMetadataToHide[$currentFolder['FolderApp']['type']])){
                    $fieldsMetadataToHide = $fieldsMetadataToHide[$currentFolder['FolderApp']['type']];
                    $fieldsMetadata = array_values(array_diff($fieldsMetadata, $fieldsMetadataToHide));
                }
                
                $fieldsAssetQuery = array_map(function ($field) {
                    return "AssetMetadata.$field";
                }, $fieldsMetadata);
                $fieldsAssetQuery = implode(',',$fieldsAssetQuery);

                $assetFolder = $this->FolderApp->find('first', [
                    'fields' => ['FolderApp.id','FolderApp.name', 'Client.id', 'Client.name', 'Client.parent', $fieldsAssetQuery],
                    'conditions'=>[
                        'FolderApp.id' => $folder_received,
                        'FolderApp.deleted' => 0,
                    ],
                    'joins' => [
                        array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                    ]
                ]);


                if (!empty($assetFolder)) {
                    $empresa = $assetFolder['Client'];
                    
                    $folderBreadcrum = $this->Core->getParentsOfFolderId($folder_received);
                    array_push($folderBreadcrum, ['FolderApp'=>['name' => __('Metadata',true)]]);

                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);

                    $this->set('assetFolder', $assetFolder);
                    $this->set('folderBreadcrum', $folderBreadcrum);
                    $this->set('company', $empresa);
                    $this->set('metadataFields', $fullFieldsMetadata);
                    
                    $this->jsToInclude[] = 'application/Conveyors/conveyor_datasheet';
                    $this->set('jsToInclude', $this->jsToInclude);

                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadDatasheet() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $buoysystem_received = $decodedConveyorParams['item_id'];
                $buoyFolder = $this->FolderApp->findByIdAndDeleted($buoysystem_received, 0);
                if (!empty($buoyFolder)) {
                    $this->set('buoySystem', $buoyFolder);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadDatasheetAsset() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $folder_received = $decodedConveyorParams['item_id'];
                Configure::load('metadata_fields');
                $fullFieldsMetadata = Configure::read('Metadata')['fields']['full_assets'];
                $fieldsMetadata = Configure::read('Metadata')['fields']['asset'];
                $fieldsMetadataToHide = Configure::read('Metadata')['fields_hide'];
                
                $currentFolder = $this->FolderApp->findByIdAndDeleted($folder_received, 0, ['type']);
                $fieldsMetadata[] = 'id';
                $fieldsMetadata[] = 'created_at';
                if(!empty($currentFolder) && isset($fieldsMetadataToHide[$currentFolder['FolderApp']['type']])){
                    $fieldsMetadataToHide = $fieldsMetadataToHide[$currentFolder['FolderApp']['type']];
                    $fieldsMetadata = array_values(array_diff($fieldsMetadata, $fieldsMetadataToHide));
                }
                
                $fieldsAssetQuery = array_map(function ($field) {
                    return "AssetMetadata.$field";
                }, $fieldsMetadata);
                $fieldsAssetQuery = implode(',',$fieldsAssetQuery);

                $assetFolder = $this->FolderApp->find('first', [
                    'fields' => ['FolderApp.id','FolderApp.name', 'Client.id', 'Client.name', 'Client.parent', $fieldsAssetQuery],
                    'conditions'=>[
                        'FolderApp.id' => $folder_received,
                        'FolderApp.deleted' => 0,
                    ],
                    'joins' => [
                        array('table' => 'asset_metadata', 'type' => 'LEFT', 'alias' => 'AssetMetadata', 'conditions' => array('FolderApp.id = AssetMetadata.folder_app_id')),
                    ]
                ]);

                if (!empty($assetFolder)) {
                    $this->set('assetFolder', $assetFolder);
                    $this->set('metadataFields', $fullFieldsMetadata);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadDatasheetUs() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $buoysystem_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $original_conveyor = $conveyor;
                    $this->set('conveyor', $conveyor);
                    $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadFullReport() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    if($conveyor["Conveyor"]["is_us_conveyor"]){
                        $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                        $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                    }else{
                        $conveyor = $this->Converter->process_convertion($conveyor);
                        $original_conveyor = $conveyor;
                        $estimated_lifetime = $this->Core->calcLifeEstimationBanda($original_conveyor);
                        $this->set('estimated_lifetime', $estimated_lifetime);
                    }

                    $reports = $this->Report->find('all', array('conditions' => array('parent_conveyor' => $conveyor_received, 'eliminada' => 0), 'order' => array('creada DESC')));
                    $images = $this->Image->find('all', array('conditions' => array('parent_conveyor' => $conveyor_received, 'eliminada' => 0), 'order' => array('creada DESC')));


                    $this->set('conveyor', $conveyor);
                    $this->set('reports', $reports);
                    $this->set('images', $images);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function conveyorQr() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $this->set('conveyor', $conveyor);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function reportingHistory() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $reports = $this->Conveyor->getReportsConveyor($conveyor_received);
                    $this->set('conveyor', $conveyor);
                    $this->set('reports', $reports);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadReportingHistory() {
        $this->layout = false;
        $this->autoRender = false;

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $reports = $this->Conveyor->getReportsConveyor($conveyor_received);
                    if (!empty($reports)) {
                        $file_zip = tempnam("tmp", "zip");
                        $zip = new ZipArchive();
                        if ($zip->open($file_zip, ZipArchive::CREATE) === TRUE) {
                            foreach ($reports AS $report) {
                                $report = $report['Reporte'];
                                $zip->addFile($report['file'], $report['nombre'] . '.pdf');
                            }
                            $zip->close();


                            $file_name = $conveyor['Conveyor']['numero'];
                            header("Content-Type: application/zip");
                            header("Content-Length: " . filesize($file_zip));
                            header("Content-Disposition: attachment; filename=\"$file_name.zip\"");
                            readfile($file_zip);
                            unlink($file_zip);
                        } else {
                            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                        }
                    } else {
                        $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                    }
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    private function create_zip($files = array(), $destination = '', $overwrite = true) {

        if (file_exists($destination) && !$overwrite) {
            return false;
        };
        $valid_files = array();
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $valid_files[] = $file;
                };
            };
        };
        if (count($valid_files)) {
            $zip = new ZipArchive();
            if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            };
            foreach ($valid_files as $file) {
                $zip->addFile($file, $file);
            };
            $zip->close();
            return file_exists($destination);
        } else {
            return false;
        };
    }

    

    public function dropItemToFolder() {
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data

            $uniqid_item_dropped_item = $params['dropped_item'];
            $folder = explode('@', $params['folder']);
            $item = explode('@', $params['dropped_item']);

            array_shift($folder);
            $typeElementDrop = array_shift($item);
            $typeElementDrop = $typeElementDrop == 'Video' ? 'Movie' : $typeElementDrop;
            $decodedFolderParams = $this->Core->decodePairParams($folder);
            $decodedDroppedItemParams = $this->Core->decodePairParams($item);
            if ($decodedFolderParams['isOk'] && $decodedDroppedItemParams['isOk']) {
                $ItemUpdate = array(
                    'parent_folder' => "'$decodedFolderParams[item_id]'"
                );

                if ($this->$typeElementDrop->updateAll($ItemUpdate, array('id' => $decodedDroppedItemParams['item_id']))) {
                    $revertUrl = array('controller' => 'Conveyors', 'action' => 'revertDroppedItem', $uniqid_item_dropped_item);
                    $link_revert = $this->_html->link(__('Deshacer', true), $revertUrl, array('class' => 'revert-drop revert-drop-item-link', 'rel' => $uniqid_item_dropped_item));
                    $response['msg'] = __('El elemento ha sido insertado correctamente. %s', $link_revert);
                    $response['success'] = true;
                } else {
                    $response['msg'] = __('Error al insertar elemento, intentelo nuevamente', true);
                }
            } else {
                $response['msg'] = __('Elemento y/o Folder no encontrado', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function revertDroppedItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $conveyor_data = $this->request->data; //get data
            $conveyor = array_values($conveyor_data['conveyor']);

            $response = array('success' => false, 'msg' => '');
            $item = explode('@', $params[0]);
            $typeElementDrop = array_shift($item);

            $typeElementDrop = $typeElementDrop == 'Video' ? 'Movie' : $typeElementDrop;
            $decodedDroppedItemParams = $this->Core->decodePairParams($item);
            $conveyorParams = $this->Core->decodePairParams($conveyor);

            if ($conveyorParams['isOk'] && $decodedDroppedItemParams['isOk']) {
                $ItemUpdate = array(
                    'parent_folder' => "'0'"
                );

                if ($this->$typeElementDrop->updateAll($ItemUpdate, array('id' => $decodedDroppedItemParams['item_id']))) {
                    $response['msg'] = __('La operacion se deshizo correctamente', true);
                    $response['success'] = true;
                } else {
                    $response['msg'] = __('No se pudo deshacer la operacion, intentelo nuevamente', true);
                }
            } else {
                $response['msg'] = __('No se pudo deshacer la operacion, intentelo nuevamente', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function trackInfo() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $tracking_code = $decodedConveyorParams['item_id'];
                    $this->set('tracking_code', $tracking_code);
                    $response['success'] = true;
                } else {
                    $response['msg'] = __('El codigo de rastreo ha sido alterado', true);
                }
            } else {
                $response['msg'] = __('No se recibio el codigo de rastreo', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function mobileTracking() {
        $this->layout = 'clean';
        $this->uses[] = 'Token';
        $conveyor = array();
        if ($this->request->is('mobile') || 1 == 1) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
            if (!empty($params) && count($params) == 3) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    $token = $this->Token->findByAuthKey($params[2]);
                    if (!empty($conveyor) && !empty($token)) {
                        $this->jsToInclude[] = 'application/Conveyors/mobile_tracking';
                        $this->set('jsToInclude', $this->jsToInclude);
                        $conveyor = $conveyor['Conveyor'];
                    } else {
                        $conveyor = array();
                    }
                }
            }
        }
        $this->set('conveyor', $conveyor);
    }

    public function mobilePremiumTrackingConveyor() {
        $this->layout = 'clean';
        $this->uses[] = 'Token';
        $conveyor = array();
        if ($this->request->is('mobile') || 1 == 1) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
            if (!empty($params) && count($params) == 3) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->TrackingConveyor->findById($conveyor_received);
                    $token = $this->Token->findByAuthKey($params[2]);
                    if (!empty($conveyor) && !empty($token)) {
                        $this->jsToInclude[] = 'application/Conveyors/mobile_tracking';
                        $this->set('jsToInclude', $this->jsToInclude);
                        $conveyor = $conveyor['TrackingConveyor'];
                    } else {
                        $conveyor = array();
                    }
                }
            }
        }
        $this->set('conveyor', $conveyor);
    }

    public function exportToHistory() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {


                        $this->set('conveyor', $conveyor);

                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function createDatasheetExport(){
        $this->layout = false;
        $response = ["file"=>'',"msg"=>'-'];
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    if($conveyor["Conveyor"]["is_us_conveyor"]){
                        $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                        $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                    }else{
                        $original_conveyor = $conveyor;
                        $conveyor = $this->Converter->process_convertion($conveyor);
                        $estimated_lifetime = $this->Core->calcLifeEstimationBanda($original_conveyor);
                        $this->set('estimated_lifetime', $estimated_lifetime);
                    }
                    $this->set('conveyor', $conveyor);
                    $this->set('response',$response);
                }
            }
        }
    }

    public function processExportHistory() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $dataform = $this->request->data;
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                    if (!empty($conveyor)) {
                        $this->uses[] = "TabInstalledBelt";
                        $this->uses[] = "UsTabInstalledBelt";

                        if($conveyor["Conveyor"]["is_us_conveyor"]){

                            $isUSConveyor = $conveyor["Conveyor"]["is_us_conveyor"];
                            if($isUSConveyor){
                                $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                            }

                            $install_date = $conveyor['TabInstalledBelt']['installation_date'];
                            $date_belt_failed = $conveyor["TabInstalledBelt"]["date_belt_failed"];

                            $failure_mode = $conveyor["TabInstalledBelt"]["failure_mode"];
                            $manufacturer = $conveyor["TabInstalledBelt"]["belt_manufacturer"]>=13 ? 19 : $conveyor["TabInstalledBelt"]["belt_manufacturer"];
                            $other_manufacturer = $manufacturer==19 ? $conveyor["TabInstalledBelt"]["open_belt_manufacturer"]:"";
                            $family = is_null($conveyor["TabInstalledBelt"]['belt_family']) ? 0 : $conveyor["TabInstalledBelt"]['belt_family'];
                            $open_belt_family = $manufacturer==19 ? $conveyor["TabInstalledBelt"]['open_belt_family']:"";
                            $belt_compound = is_null($conveyor["TabInstalledBelt"]['belt_compound']) ? 0 : $conveyor["TabInstalledBelt"]['belt_compound'];
                            $open_belt_compound = $manufacturer==19 ? $conveyor["TabInstalledBelt"]['open_belt_compound']:"";
                            $carcass = $conveyor["TabInstalledBelt"]['carcass'];
                            $tension_unit = $conveyor["TabInstalledBelt"]['tension_unit'];
                            $tension = $conveyor["TabInstalledBelt"]['tension'];
                            $open_tension = $conveyor["TabInstalledBelt"]['open_tension'];
                            $plies = $conveyor["TabInstalledBelt"]['plies'];
                            $width = $conveyor["TabInstalledBelt"]['width'];
                            $other_width = $conveyor["TabInstalledBelt"]['other_width'];
                            $top_cover = $conveyor["TabInstalledBelt"]['top_cover'];
                            $top_cover_metric = 0; //depende de la unidad selecciona
                            $pulley_cover = $conveyor["TabInstalledBelt"]['pulley_cover'];
                            $pulley_cover_metric = 0; //depende de la unidad seleccionada
                            $other_special = $conveyor["TabInstalledBelt"]['other_special'];
                            $other_special_data = $conveyor["TabInstalledBelt"]['other_special_data'];
                            $durometer_failed = $conveyor["TabInstalledBelt"]['durometer_failed'];
                            $existing_damage_belt = $conveyor["TabInstalledBelt"]['existing_damage_belt'];

                            $data = [];
                            $data['belt_length_install'] = $conveyor["TabInstalledBelt"]['belt_length_install'];
                            $data['splice_type'] = $conveyor["TabInstalledBelt"]['splice_type'];
                            $data['splice_quantity'] = $conveyor["TabInstalledBelt"]['splice_quantity'];
                            $data['splice_condition'] = $conveyor["TabInstalledBelt"]['splice_condition'];



                            $history_export = false;

                            if($date_belt_failed!="0000-00-00" && $date_belt_failed!="" && $install_date!="0000-00-00" && $install_date!=""){
                                $years_system = 0;//Calcular
                                $installed_date = date("Y-m-d", strtotime($install_date));
                                $installed_date = new DateTime($installed_date);
                                $failed_date = date("Y-m-d", strtotime($date_belt_failed));
                                $failed_date   = new DateTime($failed_date);


                                $interval = $failed_date->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_system = number_format($elapsed_years, "2",".", ",");

                                $existing_damage_belt_history = is_null($existing_damage_belt) ? "" : $existing_damage_belt;
                                $carcass_history = is_null($carcass) ? "EP":$carcass;
                                $tension_unit_history = "imperial_fabric";
                                if(!is_null($tension_unit)){
                                    $tension_unit_history = $carcass=="ST" ? $tension_unit."_steel" : $tension_unit."_fabric";
                                }


                                $history_reg = ["History" => [
                                    'client_id' => $conveyor['Conveyor']["id_company"],
                                    'conveyor_id' => $conveyor['Conveyor']["id"],
                                    'belt_manufacturer' => is_null($manufacturer) ? 0 : $manufacturer,
                                    'other_manufacturer' => $other_manufacturer,
                                    'family' => is_null($family) ? 0 : $family,
                                    'other_family' => $open_belt_family,
                                    'compounds_top_cover' => is_null($belt_compound) ? 0 : $belt_compound,
                                    'other_compound' => $open_belt_compound,
                                    'fabric_type' => is_null($carcass_history) ? '' : $carcass_history,
                                    'tension_unit' => is_null($tension_unit_history) ? '' : $tension_unit_history,
                                    'tension' => is_null($tension) ? 0 : $tension,
                                    'tension_steel' => "",
                                    'plies' => is_null($plies) ? 0 : $plies,
                                    'width' => is_null($width) ? '' : $width,
                                    'other_width' => is_null($other_width) ? '' : $other_width,
                                    'top_cover' => is_null($top_cover) ? '' : $top_cover,
                                    'top_cover_metric' => is_null($top_cover_metric) ? '' : $top_cover_metric,
                                    'pulley_cover' => is_null($pulley_cover) ? '' : $pulley_cover,
                                    'pulley_cover_metric' => is_null($pulley_cover_metric) ? '' : $pulley_cover_metric,
                                    'other_special' => is_null($other_special) ? 0 : $other_special,
                                    'other_special_data' => is_null($other_special_data) ? '' : $other_special_data,
                                    'date_install' => $install_date,
                                    'date_failed' => $date_belt_failed,
                                    'years_system' => $years_system,
                                    'failure_mode' => is_null($failure_mode) ? '' : $failure_mode,
                                    'remarks' => $existing_damage_belt_history,
                                    'datasheet_path' => $dataform['filePath']
                                ]];
                                //var_dump($history_reg);

                                $this->uses[] = 'History';
                                if ($this->History->save($history_reg)) {
                                    //save score card statistic

                                    //$salespersonAssoc = $this->Core->getSalespersonIfExists($clientId);
                                    //if($salespersonAssoc>0){
                                    //    $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_BELT_HISTORY);
                                    //}
                                    $history_export = true;
                                    $response['success'] = true;
                                    $response['msg'] = __('La banda transportadora ha sido actualizada exitosamente', true);
                                    $response['msg'] =  $response['msg']."<br>".__("La informacion de banda instalada fue exportada a history y los campos estan limpios para agregar informacion de una nueva banda",true);
                                }
                            }


                            if($history_export){
                                $manufacturer = $family = $belt_compound = null;
                                $open_belt_family = $open_belt_compound = "";
                                $carcass = $tension_unit = $tension = $plies = $width = $other_width = null;
                                $top_cover = $pulley_cover = $other_special = null;
                                $existing_damage_belt = $failure_mode = $durometer_failed = null;
                                $install_date = $date_belt_failed = "0000-00-00";
                                unset($data['belt_length_install'], $data['splice_type'],$data['splice_quantity'],$data['splice_condition']);
                            }


                            //tab_installed_belt
                            $tab_installed_belt_id = $this->UsTabInstalledBelt->findByConveyorId($conveyor_received);
                            $tab_installed_belt_id = empty($tab_installed_belt_id) ? null : $tab_installed_belt_id['UsTabInstalledBelt']['id'];
                            $installed_belt_tab = [
                                'id' => $tab_installed_belt_id,
                                'belt_manufacturer' => $manufacturer,
                                'open_belt_manufacturer' => "",
                                'belt_family' => $family,
                                'open_belt_family' => $open_belt_family,
                                'belt_compound' => $belt_compound,
                                'open_belt_compound' => $open_belt_compound,
                                'carcass' => $carcass,
                                'tension_unit' => $tension_unit,
                                'tension' => $tension,
                                'open_tension' => $open_tension,
                                'plies' => $plies,
                                'width' => $width,
                                'other_width' => $other_width,
                                'top_cover' => $top_cover,
                                'pulley_cover' => $pulley_cover,
                                'other_special' => $other_special,
                                'other_special_data' => $other_special_data,
                                'installation_date' => $install_date,
                                'belt_length_install' => isset($data['belt_length_install']) ? $data['belt_length_install'] : null,
                                'splice_type' => isset($data['splice_type']) ? $data['splice_type'] : null,
                                'splice_quantity' => isset($data['splice_quantity']) ? $data['splice_quantity'] : null,
                                'splice_condition' => isset($data['splice_condition']) ? $data['splice_condition'] : null,
                                'existing_damage_belt' => $existing_damage_belt,
                                'failure_mode' => $failure_mode,
                                'durometer_failed' => $durometer_failed,
                                'date_belt_failed' => $date_belt_failed
                            ];

                            //Clear fields
                            $this->UsTabInstalledBelt->save($installed_belt_tab);
                        }else{
                            $history_export = false;

                            $fecha_instalacion = $conveyor['Conveyor']['banda_fecha_instalacion'];
                            $date_belt_failed = $conveyor["TabInstalledBelt"]["date_belt_failed"];

                            $carcass = $conveyor["TabInstalledBelt"]["shell"];
                            $manufacturer = 19; //Other
                            $marca_banda = $conveyor['Conveyor']['banda_marca'];
                            $family = 0;
                            $other_family = $conveyor["TabInstalledBelt"]['belt_family'];
                            //$open_belt_family = isset($data['open_belt_family']) ? $data['open_belt_family'] : null;
                            $belt_compound = 0;
                            $other_belt_compound = $conveyor["TabInstalledBelt"]['used_belt_grade'];
                            $width = is_null($conveyor['Conveyor']['banda_ancho']) ? "" : $conveyor['Conveyor']['banda_ancho'];
                            $tension = $conveyor['Conveyor']['banda_tension'];
                            $plies = $conveyor['TabInstalledBelt']['plies_number'];
                            $top_cover_metric = 0; //depende de la unidad selecciona
                            $pulley_cover_metric = 0; //depende de la unidad seleccionada
                            $other_special = 0;
                            $other_special_data = "";
                            $reason_replacement = "";

                            $data = [];

                            $data['espesor_cubierta_sup'] = $conveyor['Conveyor']['id_espesor_cubierta_sup'];
                            $data['espesor_cubierta_inf'] = $conveyor['Conveyor']['id_espesor_cubierta_inf'];
                            if($date_belt_failed!="0000-00-00" && $date_belt_failed!="" && $fecha_instalacion!="0000-00-00" && $fecha_instalacion!=""){

                                $meta_units = $this->Core->getPairsMetaUnits($conveyor["Conveyor"]["meta_units"]);
                                $tension_unit = $meta_units["tension_banda"];
                                $tension_unit = $tension_unit == "PIW" ? "imperial" : "metric";

                                $years_system = 0;//Calcular
                                $installed_date = date("Y-m-d", strtotime($fecha_instalacion));
                                $installed_date = new DateTime($installed_date);
                                $failed_date = date("Y-m-d", strtotime($date_belt_failed));
                                $failed_date   = new DateTime($failed_date);

                                $interval = $failed_date->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_system = number_format($elapsed_years, "2",".", ",");



                                //$existing_damage_belt_history = is_null($existing_damage_belt) ? "" : $existing_damage_belt;
                                $carcass_history = is_null($carcass) ? "EP":$carcass;
                                $tension_unit_history = "imperial_fabric";
                                if(!is_null($tension_unit)){
                                    $tension_unit_history = $carcass=="ST" ? $tension_unit."_steel" : $tension_unit."_fabric";
                                }

                                $cover_translate = [
                                    244 => 2, // 1/16
                                    245 => 3, // 3/32
                                    246 => 4, // 1/8
                                    247 => 5, // 5/32
                                    248 => 6, // 3/16
                                    250 => 8, // 1/4
                                    251 => 10, // 5/16
                                    252 => 12, // 3/8
                                    255 => 15, // 1/2
                                    256 => 17, // 5/8
                                    257 => 19, // 3/4
                                    258 => 23, // 1"
                                ];

                                $top_cover_history = array_key_exists($data['espesor_cubierta_sup'], $cover_translate) ? $cover_translate[$data['espesor_cubierta_sup']] : 0;
                                $pulley_cover_history = array_key_exists($data['espesor_cubierta_inf'], $cover_translate) ? $cover_translate[$data['espesor_cubierta_inf']] : 0;
                                $failure_mode_history = "29";
                                $history_reg = ["History" => [
                                    'client_id' => $conveyor['Conveyor']["id_company"],
                                    'conveyor_id' => $conveyor['Conveyor']["id"],
                                    'belt_manufacturer' => $manufacturer,
                                    'other_manufacturer' => $marca_banda,
                                    'family' => $family,
                                    'other_family' => $other_family,
                                    'compounds_top_cover' => $belt_compound,
                                    'other_compound' => $other_belt_compound,
                                    'fabric_type' => $carcass_history,
                                    'tension_unit' => $tension_unit_history,
                                    'tension' => $tension=="" ? 0 : $tension,
                                    'tension_steel' => "",
                                    'plies' => is_null($plies) || $plies="" ? 0 : $plies,
                                    'width' => $width,
                                    'other_width' => "",
                                    'top_cover' => $top_cover_history,
                                    'top_cover_metric' => $top_cover_metric,
                                    'pulley_cover' => $pulley_cover_history,
                                    'pulley_cover_metric' => $pulley_cover_metric,
                                    'other_special' => $other_special,
                                    'other_special_data' => $other_special_data,
                                    'date_install' => $fecha_instalacion,
                                    'date_failed' => $date_belt_failed,
                                    'years_system' => $years_system,
                                    'failure_mode' => $failure_mode_history,
                                    'remarks' => "",
                                    'datasheet_path' => $dataform['filePath']
                                ]];

                                $this->uses[] = 'History';
                                if ($this->History->save($history_reg)) {
                                    //save score card statistic

                                    //$salespersonAssoc = $this->Core->getSalespersonIfExists($clientId);
                                    //if($salespersonAssoc>0){
                                    //    $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_BELT_HISTORY);
                                    //}
                                    $history_export = true;

                                    //limpiamos los campos del tab de installed belt
                                    $tension = $data['desarrollo_banda'] = $marca_banda = $data['operacion_hrs'] = $other_family = "";
                                    $width = $carcass = $data['cord_diameter'] = $data['number_cords'] = $data['cord_pitch'] = $other_belt_compound = null;
                                    $data['espesor_cubierta_sup'] = $data['espesor_cubierta_inf'] = $data['trade_name'] = $dropdown_values['damages'] = null;
                                    $dropdown_values['splice_type'] = $dropdown_values['splice_quantity'] = $dropdown_values['splice_condition'] = null;
                                    $data['shore_hardness_a'] = $plies = null;
                                    $fecha_instalacion = $date_belt_failed = "0000-00-00";

                                    $response['success'] = true;
                                    $response['msg'] = __('La banda transportadora ha sido actualizada exitosamente', true);
                                    $response['msg'] =  $response['msg']."<br>".__("La informacion de banda instalada fue exportada a history y los campos estan limpios para agregar informacion de una nueva banda",true);
                                }
                            }


                            $belt_monitoring_system = isset($data['belt_monitoring_system']) && $data['belt_monitoring_system']!='' ? implode(',',$data['belt_monitoring_system']) : "";
                            $failure_modes = isset($data['failure_mode']) && $data['failure_mode']!='' ? implode(',',$data['failure_mode']) : "";
                            $failure_modes = $history_export ? null : $failure_modes;

                            $conveyor['Conveyor']['banda_ancho'] = $width;
                            $conveyor['Conveyor']['banda_tension'] = $tension;
                            $conveyor['Conveyor']['id_espesor_cubierta_sup'] = $data['espesor_cubierta_sup'];//*
                            $conveyor['Conveyor']['id_espesor_cubierta_inf'] = $data['espesor_cubierta_inf'];//*
                            $conveyor['Conveyor']['banda_fecha_instalacion'] = $fecha_instalacion;
                            $conveyor['Conveyor']['banda_marca'] = $marca_banda;
                            $conveyor['Conveyor']['banda_desarrollo_total'] = $data['desarrollo_banda'];//*
                            $conveyor['Conveyor']['banda_operacion'] = $data['operacion_hrs'];//*

                            //tab_installed_belt
                            $tab_installed_belt_id = $this->TabInstalledBelt->findByConveyorId($conveyor_received);
                            $tab_installed_belt_id = empty($tab_installed_belt_id) ? null : $tab_installed_belt_id['TabInstalledBelt']['id'];
                            $installed_belt_tab = [
                                'id' => $tab_installed_belt_id,
                                'conveyor_id' => $conveyor_received,
                                'shell' => $carcass,
                                'cord_diameter' => $data['cord_diameter'],//*
                                'number_cords' => $data['number_cords'],//*
                                'cord_pitch' => $data['cord_pitch'],//*
                                'plies_number' => $plies,
                                'belt_family' => $other_family,
                                'used_belt_grade' => $other_belt_compound,
                                'trade_name' => $data['trade_name'],//*
                                'damages' => $dropdown_values['damages'],//*
                                'splice_type' => $dropdown_values['splice_type'],//*
                                'splice_quantity' => $dropdown_values['splice_quantity'],//*
                                'splice_condition' => $dropdown_values['splice_condition'],//*
                                'shore_hardness_a' => $data['shore_hardness_a'],//*
                                'failure_mode' => $failure_modes,
                                'date_belt_failed' => $date_belt_failed
                            ];

                            $this->Conveyor->save($conveyor);
                            $this->TabInstalledBelt->save($installed_belt_tab);
                        }

                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function lifeEstimation() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {
                        $this->set('sePuedeCalcularVidaEstimada', $this->Core->sePuedeCalcularVidaEstimada($conveyor));
                        $this->set('sePuedeCalcularBandaRecomendada', $this->Core->sePuedeCalcularBandaRecomendada($conveyor));

                        $estimated_lifetime = $this->Core->calcLifeEstimationBanda($conveyor);

                        $this->set('conveyor', $conveyor);

                        $this->set('estimation_months', $estimated_lifetime['estimated_lifetime']);
                        $this->set('estimation_tons', $estimated_lifetime['expected_tonnage']);
                        $this->set('change_date_estimation', $estimated_lifetime['approx_change_date']);

                        $this->set('banda_recomendada_piw', $estimated_lifetime['recommended_conveyor_in']);
                        $this->set('banda_recomendada_mm', $estimated_lifetime['recommended_conveyor_mm']);

                        $this->set('disclaimer_min_width', $estimated_lifetime['disclaimer_min_width']);
                        $this->set('disclaimer_max_width', $estimated_lifetime['disclaimer_max_width']);

                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function quoteRequest() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {
                        $estimated_lifetime = $this->Core->calcLifeEstimationBanda($conveyor);
                        if (!is_null($estimated_lifetime['recommended_conveyor_in'])) {
                            if ($this->Core->sendQuoteRequest($conveyor, $estimated_lifetime)) {
                                $response['success'] = true;
                                $response['msg'] = __('Su solicitud ha sido enviada', true);
                            } else {
                                $response['msg'] = __('Error al enviar la solicitud de cotizacion, intentelo nuevamente', true);
                            }
                        } else {
                            $response['msg'] = __('Se requiere mas informacion para calcular la banda recomendada y solicitar la cotizacion', true);
                        }
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function getGaugeChart(){
        $this->layout = false;
        //$this->autoRender = false;
        $abrasionLifeData = [];
        $gaugeCalculationResults = [];
        //$params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        //$params = ['MTQxMnxTaWduNjQ=','9d824059eed322ae6274a8de289a78053585be9f'];

        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(isset($data['gaugeConveyors'])){
                $gaugeConveyors = $data['gaugeConveyors'];
                foreach($gaugeConveyors AS $gaugeConveyor){
                    $gaugeCalculationResults[$gaugeConveyor] = $this->Core->calcAbrasionLife($gaugeConveyor);
                }
            }

            /*
            //if (!empty($params) && count($params) == 2) {
              //  $decodedConveyorParams = $this->Core->decodePairParams($params);
                //if ($decodedConveyorParams['isOk']) {
                    //$conveyor_received = $decodedConveyorParams['item_id'];
                    //$abrasionLifeData = $this->Core->calcAbrasionLife($conveyor_received);
                    if(!empty($abrasionLifeData)){

                    }
                //}
            //}*/
            $this->set('gaugeCalculationResults',$gaugeCalculationResults);
            $this->set('abrasionLifeData',$abrasionLifeData);
            //echo json_encode($abrasionLifeData);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /*
     * Load filters area for conveyor
     */
    public function getFilterAreasSubareas(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findById($conveyor_received,['Conveyor.id','Conveyor.id_company','Conveyor.area','Conveyor.subarea']);
                if (!empty($conveyor)) {
                    $empresa = $this->Empresa->findById($conveyor['Conveyor']['id_company']);
                    $this->set('conveyor', $conveyor['Conveyor']);
                    $this->set('areas', $empresa['Areas']);
                    $this->set('subareas', $empresa['Subareas']);
                }
            }
        }
    }

    public function addAreaSubarea(){
        $this->layout = false;
        $this->autoRender = false;

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {
                        $params = $this->request->data; //get data
                        parse_str($params['formdata'], $data);

                        $name = $data['item_area_subarea'];
                        $toSaveData = $params['invoker']=='area_select' ? ["CompanyArea" => ['company_id'=>$conveyor['Conveyor']['id_company'], 'name'=>$name]] : ["CompanySubarea" => ['company_id'=>$conveyor['Conveyor']['id_company'], 'name'=>$name]];
                        $result = $params['invoker']=='area_select' ? $this->CompanyArea->save($toSaveData) : $this->CompanySubarea->save($toSaveData);
                        if($result){
                            $response['success'] = true;
                            $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                        }else{
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                        }


                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function setFiltersAreaToConveyor(){
        $this->layout = false;
        $this->autoRender = false;

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];

                    $this->Conveyor->id = $conveyor_received;
                    $params = $this->request->data; //get data
                    if($params['invoker']=='area_select'){
                        $this->Conveyor->saveField('area', $params['filterId']);
                    }else{
                        $this->Conveyor->saveField('subarea', $params['filterId']);
                    }

                    $response['success'] = true;
                    $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function clearTags(){
        $this->layout = false;
        $this->autoRender = false;

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];

                    $this->Conveyor->id = $conveyor_received;
                    $params = $this->request->data; //get data
                    if($params['invoker']=='area_select'){
                        $this->Conveyor->saveField('area', 0);
                    }else{
                        $this->Conveyor->saveField('subarea', 0);
                    }

                    $response['success'] = true;
                    $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function viewInspections(){

        $this->set('options_toolbar', 'items-inpections');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = null;

                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);

                if (!empty($conveyor)) {
                    $empresa = $conveyor['Empresa'];
                    $secureClientConveyorParams = $this->Core->encodeParams($conveyor['Conveyor']['id_company']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));

                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $this->set('company', $empresa);
                    $this->set('conveyor', $conveyor);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function inspectionData(){
        $this->set('options_toolbar', 'inpection-section');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = null;

                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);

                if (!empty($conveyor)) {
                    $this->openCssToInclude[] = 'plugins/Assets/css/imgNotes/imgNotes';
                    $this->set('openCssToInclude', $this->openCssToInclude);

                    $this->openJsToInclude[] = 'plugins/Assets/js/imgNotes/imgViewer';
                    $this->openJsToInclude[] = 'plugins/Assets/js/imgNotes/imgNotes';
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Conveyors/inspection';
                    $this->set('jsToInclude', $this->jsToInclude);


                    $empresa = $conveyor['Empresa'];
                    $secureConveyorParams = $this->Core->encodeParams($conveyor['Conveyor']['id']);
                    $secureClientConveyorParams = $this->Core->encodeParams($conveyor['Conveyor']['id_company']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $urlDownloadInspectionData = $this->_html->url(array('controller' => 'Conveyors', 'action' => 'downloadInspection', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));

                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $this->set('urlDownloadInspectionData', $urlDownloadInspectionData);
                    $this->set('company', $empresa);
                    $this->set('conveyor', $conveyor);

                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }

    }

    public function downloadInspection() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $this->set('conveyor', $conveyor);

                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function saveRecommendedBeltInfo(){
        $this->layout = false;
        $this->autoRender = false;

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];

                    //$this->Conveyor->id = $conveyor_received;
                    $conveyorRecommendedInfo = $this->RecommendedBelt->findByConveyorId($conveyor_received);
                    $params = $this->request->data; //get data
                    parse_str($params['formdata'], $data);
                    if(isset($data["info"],$data["reason"]) && trim($data["info"])!="" && trim($data["reason"])!=""){
                        $recommendedBeltRow = ["RecommendedBelt" => [
                            'conveyor_id' => $conveyor_received,
                            'info' => trim($data["info"]),
                            'note' => trim($data["reason"]),
                            'user_maker' => $this->credentials['id']
                        ]];

                        //Ya existe una recomendacion para esa banda, solo modificar la informacion
                        if(!empty($conveyorRecommendedInfo)>0){
                            $recommendedBeltRow['RecommendedBelt']['id'] = $conveyorRecommendedInfo["RecommendedBelt"]['id'];
                        }

                        if($this->RecommendedBelt->save($recommendedBeltRow)){
                            $response['success'] = true;
                            $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                        }else{
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                        }
                        
                    }else{
                        $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                    }
                    
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

}
