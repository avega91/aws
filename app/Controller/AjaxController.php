<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file AjaxController.php
 *     Management of ajax requests
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class AjaxController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = false;
        $this->uses[] = 'SettingMsg';
        $this->uses[] = 'EstadoRegion';
        $this->uses[] = "RecommendedBelt";
    }

    public function ax_get_home_msg() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos    
            $msg_id = $params['msg_id'];
            $homeMsg = $this->SettingMsg->get_setting_msg($msg_id);
            $homeMsg = explode('||', $homeMsg['descripcion']);
            $msg_es = $homeMsg[0];
            $msg_en = isset($homeMsg[1]) ? $homeMsg[1] : '';

            $this->set('homeMsgEs', $msg_es);
            $this->set('homeMsgEn', $msg_en);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ax_process_change_home_msg() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos      
            
            //parse_str($params['formdata'], $data); //parseamos el parametro donde viene la info del form    

            $response = array();
            $response['error'] = true;
            $response['msg'] = '';

            $wysiwyg_msg_es = $params['wysiwyg_es'];
            $wysiwyg_msg_en = $params['wysiwyg_en'];
            if (isset($wysiwyg_msg_es, $wysiwyg_msg_en) && $wysiwyg_msg_es != '' && $wysiwyg_msg_en != '') {
                if ($this->SettingMsg->update_main_msg_by_msg_id('home_main_msg', '', $wysiwyg_msg_es . '||' . $wysiwyg_msg_en)) {
                    $response['error'] = false;
                    $response['msg'] = __('La operacion ha sido procesada exitomente.', true);
                } else {
                    $response['msg'] = __('No se ha procesado ningun cambio.', true);
                }
            } else {
                $response['msg'] = __('El mensaje de bienvenida no puede ir vacio', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

     /**
     * Carga la coleccion de ciudades para un estado determinado
     */
    public function ax_load_cities() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos      
            $id_state = $params['state'];
            $this->uses[] = 'City';
            $cities = $this->City->find('all', array('order' => array('name ASC'),'conditions' => array('state_id'=>$id_state)));
            $this->set('cities', $cities);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    
    /**
     * Carga la coleccion de estado para un pais determinado
     */
    public function ax_load_states() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos      
            $id_pais = $params['country'];

            if($id_pais>=1000){
                $assocsCenam = [
                    22 => 24, //region 22->Belice, is country 24...
                    23 => 54,
                    24 => 91,
                    25 => 99,
                    26 => 161,
                    27 => 174,
                    28 => 64,
                    29 => 49,
                    30 => 62,
                    31 => 187,
                    32 => 233,
                    33 => 13,
                    34 => 28,
                    35 => 57,
                    36 => 96,
                    37 => 176,
                    38 => 177,
                    39 => 230,
                    40 => 181
                ];
                $id_pais = isset($params['region']) ? $assocsCenam[$params['region']] : $id_pais;
            }

            $estados = $this->Estado->getStatesForCountry($id_pais);

            $this->set('estados', $estados);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Carga la coleccion de estados/paises para una region determinada
     */
    public function ax_load_states_regions() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos      

            $appAddFunction = !isset($params['type']) ? true : false;
            $type = isset($params['type']) ? $params['type'] : 'client';

            $region = $params['region'];
            $received_region = $region;
            $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
            $region = in_array($region, array('MX1','MX2','CENAM')) ? 'MX' : $region;
            $region = in_array($region, array('NORTHEAST','MIDWEST','SOUTH','WEST','SOUTHEAST','US')) ? 'US' : $region;
            $region = preg_match("/\bCA-\b/i", $region) ? 'CA' : $region;
            $corporativos = $this->Corporativo->find('all', array('conditions' => array('type' => $type, 'region' => $region), 'order' => array('name' => 'ASC')));

            if($this->credentials['role'] == UsuariosEmpresa::IS_ADMIN) {
                $logged_user = $this->credentials;
                $region = $logged_user['regions'] != "" ? $logged_user['regions'] : $received_region;
            }

            $estados = $this->EstadoRegion->getStatesForRegionId($received_region);

            $this->set('isAppAdd', $appAddFunction);
            $this->set('estados', $estados);
            $this->set('corporativos', $corporativos);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Obtiene via ajax la coleccion de regiones disponibles en el sistema
     */
    public function ax_load_all_regions() {
        if ($this->request->is('post')) {
            $regions = $this->Region->find('all', array('order' => array('name' => 'ASC')));
            $this->set('regions', $regions);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }


    /**
     * Obtiene via ajax la coleccion de regiones disponibles para un usuario
     */
    public function ax_load_dd_regions_user() {
        $userModel = _MODEL_AUTH_APP;
        if ($this->request->is('post')) {
            $params = $this->request->data; //Obtenemos los datos recibidos    
            $dist_id = $params['dist'];
            $regions = $this->Region->find('all', array('order' => array('id' => 'ASC'))); //Obtenemos todas las regiones        
            //$region_user = $this->$userModel->getRegionUser($dist_id);
            //$user_checking = $this->UsuariosEmpresa->findById($dist_id);            
            
            /*$user_checking = $this->UsuariosEmpresa->findByIdEmpresa($dist_id);
              $region_user = $user_checking['UsuariosEmpresa']['region'];
              $role_user = $user_checking['UsuariosEmpresa']['role'];
             */
            $empresa = $this->Empresa->findById($dist_id);
            $salespersons = [];

            if(!isset($params['type'])){
                if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                    $salespersons[] = ['id'=>$this->credentials['id'],'name'=>$this->credentials['name']];
                }else{
                    $allSalesperson = $this->Core->getSalesPersonList();
                    $salesperson_saved = Set::extract('/SalespersonShares/.', $empresa);
                    foreach ($allSalesperson AS $salesperson){
                        if($empresa['Empresa']['region']==$salesperson['region'] || in_array($salesperson['id'], array_column($salesperson_saved, 'user_sp_id'))){
                            $salespersons[] = ['id'=>$salesperson['id'],'name'=>$salesperson['name']];
                        }
                    }
                }
            }


            //$region_user = $empresa['Empresa']['region'];
            $region_user = $empresa['Empresa']['region'];
            if($this->credentials['role'] == UsuariosEmpresa::IS_ADMIN) {
                $logged_user = $this->credentials;
                $region_user = $logged_user['regions'] != "" ? str_replace(",","|",$logged_user['regions']) : $empresa['Empresa']['region'];
            }

            $role_user = $empresa['Empresa']['type'];
            $this->set('role_user_checking', $role_user);
            $this->set('regions', $regions);
            $this->set('region_user', $region_user);
            $this->set('salespersons',$salespersons);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ax_get_company() {
        if ($this->request->is('post')) {
            $params = $this->request->data;  //Get data
            $company = array();
            if (isset($params['company']) && is_numeric($params['company'])) {
                $company = $this->Empresa->findById($params['company']);
                $company = !empty($company) ? $company['Empresa'] : $company;
            }
            $this->set('company', $company);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ax_get_companies_by_type() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //Get data
            //$companies = $this->Empresa->findByTypeWithCorporate($params['type'], $this->global_filter_region);

            $region = $this->global_filter_region;
            if($this->credentials['role'] == UsuariosEmpresa::IS_ADMIN) {
                $logged_user = $this->credentials;
                $region = $logged_user['regions'] != "" ? $logged_user['regions'] : $region;
            }

            $companies = $this->Empresa->findByRegionAndTypeWithCorporate($params['type'], $region);


            if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                $userRelations = $this->UsuariosEmpresa->findById($logged_user['id'], ['UsuariosEmpresa.id']);
                if(!empty($userRelations['SharedClients'])){
                    $clientIdsShared = array_column($userRelations['SharedClients'], 'id');
                    $clientIdsShared = implode(',',$clientIdsShared);

                    $sharedClients = $this->Empresa->findByIdsWithCorporate($clientIdsShared);
                    $companies = array_merge($companies, $sharedClients);
                }
            }

            /*
            if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                $userRelations = $this->UsuariosEmpresa->findById($this->credentials['id'], ['UsuariosEmpresa.id']);
                if(!empty($userRelations['SharedDistributors'])){
                    $dealerIdsArr =  array_column($userRelations['SharedDistributors'], 'company_id');
                    $dealerIds = implode(',',$dealerIdsArr);
                    $distributorsShared = $this->Empresa->findByIdsWithCorporate($dealerIds);
                    $companies = array_merge($companies, $distributorsShared);
                }
            }*/


            //$companies = [];
            $corporativos = $this->Corporativo->find('all', array('conditions' => array('type' => $params['type']), 'order' => array('name' => 'ASC')));
            $this->set('companies', $companies);
            $this->set('corporates', $corporativos);
            $this->set('type_required',$params['type']);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ax_get_salesperson_distributor(){
        if ($this->request->is('post')) {
            $params = $this->request->data; //Get data

            $dist_id = $params['dist'];
            //get company dist for get all relations
            $empresa = $this->Empresa->findById($dist_id);
            $salespersons = [];

            //get all salesperson in system
            $allSalesperson = $this->Core->getSalesPersonList();
            //get the company's shared salesperson
            $salesperson_saved = Set::extract('/SalespersonShares/.', $empresa);
            if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                $key = array_search($this->credentials['id'], array_column($allSalesperson, 'id'));
                $salespersons[] = ['id' => $allSalesperson[$key]['id'], 'name' => $allSalesperson[$key]['name'], 'region' => $allSalesperson[$key]['region']];
            }else {
                foreach ($allSalesperson AS $salesperson) {
                    if ($empresa['Empresa']['region'] == $salesperson['region'] || in_array($salesperson['id'], array_column($salesperson_saved, 'user_sp_id'))) {
                        $salespersons[] = ['id' => $salesperson['id'], 'name' => $salesperson['name'], 'region' => $salesperson['region']];
                    }
                }
            }

            $this->set('salespersons',$salespersons);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function ax_get_clients_distributor() {
        if ($this->request->is('post')) {           
            $params = $this->request->data; //Get data

            $validCompanies = [];
            if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON) {
                $validCompanies = $this->Core->getCompaniesFilterAccordingUserLogged();
                $validCompanies = explode(',', $validCompanies);
            }

            $query_conds = array('Empresa.parent' => $params['dist'], 'Empresa.deleted'=>0);
            if($this->credentials['role'] == UsuariosEmpresa::IS_MANAGER && $this->credentials['role_company']==UsuariosEmpresa::IS_CLIENT){
                $query_conds = array('Empresa.parent' => $params['dist'], 'Empresa.id_corporativo' => $this->credentials['id_corporativo'], 'Empresa.deleted'=>0);
            }

            /*
            if(!empty($validCompanies)){
                array_push($query_conds,'Empresa.id',$validCompanies);
            }*/
            $companies = $this->Empresa->find('all', array('conditions' => $query_conds));

            if(!empty($validCompanies)){
               foreach ($companies AS $key => $company){
                    $company = $company['Empresa'];
                    if(!in_array($company['id'],$validCompanies )){
                        unset($companies[$key]);
                    }
                }
            }

            $this->set('clients', $companies);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function saveCommentItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemParams = $this->Core->decodePairParams($params);
                if ($decodedItemParams['isOk']) {
                    $item_id = $decodedItemParams['item_id'];
                    if ($this->Comment->save(array('type_item' => $data['type_item'], 'id_item' => $item_id, 'owner_user_id' => $this->credentials['id'], 'comment' => $data['comment_item']))) {
                        $response['msg'] = __('El comentario ha sido guardado exitosamente', true);
                        $response['secure_item_id'] = $params[0].'/'.$params[1];
                        $response['type_item'] = $data['type_item'];
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Ocurrio un error al tratar de guardar el comentario, intentelo nuevamente', true);
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

    public function updateCommentItem() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemParams = $this->Core->decodePairParams($params);
                if ($decodedItemParams['isOk']) {
                    $item_id = $decodedItemParams['item_id'];
                    $this->Comment->id = $item_id;
                    $this->Comment->saveField('comment', $data['comment_item']);
                    $response['msg'] = __('El comentario ha sido guardado exitosamente', true);
                    $response['secure_item_id'] = $params[0].'/'.$params[1];
                    $response['type_item'] = $this->Comment->type_item;
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

    public function deleteCommentItem() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemParams = $this->Core->decodePairParams($params);
                if ($decodedItemParams['isOk']) {
                    $item_id = $decodedItemParams['item_id'];
                    $this->Comment->id = $item_id;
                    $this->Comment->saveField('deleted', 1);
                    $response['msg'] = __('El comentario ha sido eliminado exitosamente', true);
                    $response['secure_item_id'] = $params[0].'/'.$params[1];
                    $response['type_item'] = $this->Comment->type_item;
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

    public function refreshCommentsItem() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 3) {
            $decodedItemParams = $this->Core->decodePairParams($params);
            if ($decodedItemParams['isOk']) {
                $item_id = $decodedItemParams['item_id'];
                $type_item = $params[2];
                $comments_item = $this->Comment->getCommentsItemByType($item_id, $type_item);
                $this->set('comments_item',$comments_item);
            }
        }
    }
    
    public function refreshLogConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_id = $decodedConveyorParams['item_id'];
                $log_rows = $this->Notification->find('all', array('order'=> array('creation_date DESC'),'conditions'=>array('just_for_log'=>1, 'conveyor_log'=>$conveyor_id)));
                $this->set('log_rows', $log_rows);
            }
        }
    }

    public function getRecommendedBeltInfoConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            
            if ($decodedConveyorParams['isOk']) {
                $conveyor_id = $decodedConveyorParams['item_id'];
                $this->RecommendedBelt->recursive = 0;
                $info_row = $this->RecommendedBelt->find('all', ['conditions'=>['conveyor_id'=>$conveyor_id]]);
                $this->set('recommended_info', $info_row);
            }
        }
    }
    
    public function getRegionsContactByArea(){
         if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $regions = $this->ContactRegion->find('all',array('conditions'=>array('id_area'=>$data['area'])));
            
            $this->set('regions',$regions);
         }else{
              $this->redirect(array('controller' => 'Index', 'action' => 'index'));
         }
    }
    
    public function getClientConveyors(){
         if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $client = $data['client'];
            $conveyors = $this->Conveyor->find('all',array('conditions'=>array('id_company'=>$client, 'eliminada'=>0)));
            $this->set('conveyors',$conveyors);
         }else{
              $this->redirect(array('controller' => 'Index', 'action' => 'index'));
         }
    }
    
    public function getClientsManager(){
        $parent_company = $this->credentials['role'] == UsuariosEmpresa::IS_DIST ? $this->credentials['id_empresa'] : '';
        $region = $this->global_filter_region;        
        $sort="";
        
        if($this->credentials['role'] == UsuariosEmpresa::IS_MANAGER){    
            $region = '';
            $sort = 'parent';
            if($this->credentials['role_company']==UsuariosEmpresa::IS_DIST){
                $parent_company = $this->Core->getCompanyIdsManagerUser();//Obtenemos todos los distribuidores asociados al corporativo           
            }else{
                $parent_company = '';//Solo tomamos el corporativo
            }
        }
        $client_companies = $this->Empresa->findByTypeWithTeamAndClients('client', $query = "", $sort, $region, $parent_company);
        //var_dump($client_companies);
        $this->set('clients', $client_companies);
    }

}
