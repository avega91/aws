<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file UsersController.php
 *     Management of actions for users
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'TipoIndustria';
        $this->uses[] = 'Token';
        Configure::load('fingerprint');
        Configure::load('settings');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $is_mobile = $this->request->is('mobile');
        //$is_mobile = true;
        // && $is_mobile
        $action = $this->action;
        if (strtolower($this->action) == 'mobileadd' && count($params) >= 2 && $is_mobile) {
            $lang = isset($params[2]) ? $params[2] : 'es';
            $folder_language = $lang == 'es' || $lang == 'esp' ? 'esp' : 'eng';
            Configure::write('Config.language', $folder_language);
            $this->language = $lang;
            $decodeParams = $this->Core->decodeUserParams($params);
            if ($decodeParams['isOk']) {
                $this->Auth->allow($action);
                $this->token = $decodeParams['user_id'];
            }
        }
        
    }

    /**Agregar usuario|add user */
    public function mobileAdd() {
        $this->layout = 'webview';
        $is_mobile = $this->request->is('mobile');
        $usuarioEmpresa = array();

        if (!is_null($this->token)) {
            $token = $this->Token->findByAuthKey($this->token);
            if (!empty($token)) {
                $userAssoc = $this->UsuariosEmpresa->findById($token['Token']['user_id']);
                if (!empty($userAssoc)) {
                    $usuarioEmpresa = $userAssoc['UsuariosEmpresa'];
                }
            }
        }

        if (empty($usuarioEmpresa) || $usuarioEmpresa['role'] == UsuariosEmpresa::IS_CLIENT) {
            $this->redirect(array('controller' => 'General', 'action' => 'help'));
        } else {
            $this->openCssToInclude[] = 'plugins/Assets/css/EngineValidation/validationEngine';
            $this->openJsToInclude[] = 'plugins/Assets/js/EngineValidation/validationEngine';
            $this->openJsToInclude[] = 'plugins/Assets/js/EngineValidation/validationEngine_' . strtoupper($this->language);
            $this->openJsToInclude[] = 'plugins/Assets/js/blockUI/jquery.blockUI';
            $this->jsToInclude[] = 'ownPlugins/cocoBlock/cocoblock';
            //$this->jsToInclude[] = 'application/Users/mobile_add';

            $this->set('openCssToInclude', $this->openCssToInclude);
            $this->set('openJsToInclude', $this->openJsToInclude);
            $this->set('jsToInclude', $this->jsToInclude);

            $regions = $this->Region->find('all', array('order' => array('id' => 'ASC')));
            $paises = $this->Pais->getAll();
            $empresas = $this->Empresa->find('all', array('order' => array('Empresa.name' => 'ASC')));
            $corporativos = $this->Corporativo->find('all', array('order' => array('Corporativo.name' => 'ASC')));

            $region_filter = $usuarioEmpresa['role'] == UsuariosEmpresa::IS_MASTER ? '' : $usuarioEmpresa['region'];
            $distributors = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $region_filter);
            
            $dist_id = $usuarioEmpresa['role'] == UsuariosEmpresa::IS_DIST ? $usuarioEmpresa['id_empresa'] : 0;
            //$this->setJsVar('updateRegionsForUserAx', $this->_html->url(array('controller' => 'Ajax', 'action' => 'ax_load_dd_regions_user')));
            $this->setJsVar('dist_id', $dist_id);
            
            $this->set('logged_user', $usuarioEmpresa);

            $this->set('user', $usuarioEmpresa);
            $this->set('regions', $regions);
            $this->set('paises', $paises);
            $this->set('empresas', $empresas);
            $this->set('corporativos', $corporativos);
            $this->set('distributors', $distributors);
            $this->set('is_mobile', $is_mobile);
            $this->set('token', $this->token);
        }
    }

    public function viewCompany() {
        $this->layout = false;
        $this->autoRender = false; // no view to render

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 3) {
            if ($params[0] == $this->usercode && in_array($params[1], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
                $empresa = $this->Empresa->findById($params[2]);
                if (!empty($empresa)) {
                    $this->Session->write('activeTab', $params[1]);
                    $this->Session->write('queryCompany', $empresa['Empresa']['name']);
                    $this->redirect(array('controller' => 'Users', 'action' => 'all'));
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
     * action for all view
     */
    public function all() {
        $type_manager = Configure::read('type_manager'); 
        if(!is_null($type_manager) && $this->credentials['role_company']!=UsuariosEmpresa::IS_DIST){
            $this->Auth->deny($this->action);
            $this->redirect($this->Auth->loginRedirect);
        }
        
        $this->set('options_toolbar', 'search-users');


        $queryUser = '';
        $formatDateX = $this->language == 'es' ? 'd-m-Y' : 'd-m-Y';
        $formatDate = $this->language == 'es' ? 'd/m/Y' : 'm/d/Y';
        $fecha_fin = date($formatDate);
        $fecha = new DateTime(date($formatDateX));
        $fecha->sub(new DateInterval('P7D'));
        $fecha_ini = $fecha->format($formatDate);
        $periodo = $fecha_ini . ' - ' .$fecha_fin;
        $ini_query = $this->Core->transformDateLanguagetoMysqlFormat($fecha_ini);
        $end_query = $this->Core->transformDateLanguagetoMysqlFormat($fecha_fin);
        $start = $ini_query;
        $end = $end_query;

        $start = '';
        $end = '';
        
        $locking_logs = $this->LockingLog->findAllWithAssocInfo( $queryUser, $start, $end);
        $browsing_logs = $this->BrowsingLog->findAllWithAssocInfo( $queryUser, $start, $end);
        $this->set('locking_log', $locking_logs);
        $this->set('browsing_log', $browsing_logs);
        
        $queryCompany = $sortCompany = '';

        $activeTab = '';
        //$activeTab = $this->credentials['role'] == UsuariosEmpresa::IS_MASTER ? 'market' : 'distributor';
        switch ($this->credentials['i_group_id']){
            case IGroup::MASTER:
                $activeTab = 'distributor';
            break;
            case IGroup::MARKET_MANAGER:
                $activeTab = 'distributor';
            break;
            case IGroup::COUNTRY_MANAGER:
                $activeTab = 'distributor';
            break;
            case IGroup::REGION_MANAGER:
                $activeTab = 'distributor';
            break;
            case IGroup::ADMIN:
                $activeTab = 'distributor';
            break;
            default:
                $activeTab = 'client';
            break;
        }

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $activeTab = $params[1];
                $sortCompany = $params[2];
                $queryCompany = isset($params[3]) ? $params[3] : '';
            }
        } else if ($this->Session->check('activeTab') && $this->Session->check('queryCompany')) {
            $activeTab = $this->Session->read('activeTab');
            $sortCompany = 'name';
            $this->setJsVar('queryCompany', $this->Session->read('queryCompany'));
            $this->setJsVar('activeTab', $activeTab);

            $this->Session->delete('activeTab');
            $this->Session->delete('queryCompany');
        }

        $activeTab = $this->credentials['role'] == UsuariosEmpresa::IS_DIST || $this->credentials['role'] == UsuariosEmpresa::IS_MANAGER ? 'client' : $activeTab;
        $parent_company = $this->credentials['role'] == UsuariosEmpresa::IS_DIST ? $this->credentials['id_empresa'] : '';
        $region = $this->global_filter_region;
        
        if($this->credentials['role'] == UsuariosEmpresa::IS_MANAGER){
            $parent_company = $this->Core->getCompanyIdsManagerUser();       
            if($this->credentials['role_company']==UsuariosEmpresa::IS_DIST){
                $region = '';
            }
        }else if($this->credentials['role'] == UsuariosEmpresa::IS_ADMIN){
            $logged_user = $this->credentials;
            $region = $logged_user['regions'] == "" ? $logged_user['region'] : $logged_user['regions'];
        }


        //Group user is greather than ADMIN
        $salespersonShares = [];
        $countries = $regions = [];
        if($this->credentials['i_group_id']>IGroup::ADMIN){
            $salespersonShares = $this->SalespersonCompany->find('list',['fields'=>'SalespersonCompany.company_id', 'group'=>'SalespersonCompany.company_id']);

            $countries = $this->ICountry->find('all');
            $regions = $this->IRegion->find('all');
        }

        $market_id = is_null($this->credentials['company_market_id']) || $this->credentials['i_group_id']==IGroup::MASTER ? 0 : $this->credentials['company_market_id'];
        $country_id = is_null($this->credentials['company_country_id']) || $this->credentials['i_group_id']==IGroup::MASTER ? 0 : $this->credentials['company_country_id'];
        $region_id = is_null($this->credentials['company_region_id']) ? 0 : $this->credentials['company_region_id'];

        //findByBucketTypeWithTeamAndClients
        $market_companies = $this->credentials['i_group_id'] <= IGroup::MARKET_MANAGER  ? array() : $this->Empresa->findByTypeWithTeamAndClients('bucket_market', $queryCompany, $sortCompany);
        $country_companies = $this->credentials['i_group_id'] <= IGroup::COUNTRY_MANAGER  ? array() : $this->Empresa->findByTypeWithTeamAndClients('bucket_country', $queryCompany, $sortCompany, "", $parent = "", 0, 0, $market_id);
        $region_companies = $this->credentials['i_group_id'] <= IGroup::REGION_MANAGER ? array() : $this->Empresa->findByTypeWithTeamAndClients('bucket_region', $queryCompany, $sortCompany, "", $parent = "", $region_id, $country_id, $market_id);
        $admin_companies = $this->credentials['i_group_id'] <= IGroup::TERRITORY_MANAGER ? array() : $this->Empresa->findByTypeWithTeamAndClients('admin', $queryCompany, $sortCompany, $this->global_filter_region,"",$region_id, $country_id, $market_id);
        //$dist_companies = $this->credentials['group_id'] <= Group::MANAGER ? array() : $this->Empresa->findByTypeWithTeamAndClients('distributor', $queryCompany, $sortCompany, $this->global_filter_region);


        if($this->credentials['i_group_id']==IGroup::ADMIN){
            $dist_companies = $this->credentials['group_id'] <= Group::MANAGER ? array() : $this->Empresa->findByTypeWithTeamAndClientsForSalesperson('distributor', $queryCompany, $sortCompany, $region);
            $client_companies = $this->Empresa->findByTypeWithTeamAndClientsForSalesperson('client', $queryCompany, $sortCompany, $region, $parent_company);
            //mail("elalbertgd@gmail.com","test",print_r($client_companies,true));
        }else{
            //mail("elalbertgd@gmail.com","parents",print_r(,true));
            if($this->credentials['group_id'] <= Group::MANAGER){
                $dist_companies = [];
            }else{
                if($this->credentials['group_id'] == Group::COUNTRY_MANAGER){
                    $otherCountries = $this->credentials['other_country_ids']!="" ? $country_id.','.$this->credentials['other_country_ids'] : $country_id;
                    $dist_companies = $this->Empresa->findByTypeWithTeamAndClients('distributor', $queryCompany, $sortCompany, $region="", "",$region_id = 0, $otherCountries, $market_id=0);
                }else{
                    $dist_companies = $this->Empresa->findByTypeWithTeamAndClients('distributor', $queryCompany, $sortCompany, $region, "",$region_id, $country_id, $market_id);
                }
            }

            if($this->credentials['group_id'] != Group::MANAGER){
                if($this->credentials['group_id'] == Group::COUNTRY_MANAGER){
                    $otherCountries = $this->credentials['other_country_ids']!="" ? $country_id.','.$this->credentials['other_country_ids'] : $country_id;
                    $client_companies = $this->Empresa->findByTypeWithTeamAndClients('client', $queryCompany, $sortCompany, $region="", $parent_company, $region_id = 0, $otherCountries, 0);
                }else{
                    $client_companies = $this->Empresa->findByTypeWithTeamAndClients('client', $queryCompany, $sortCompany, $region, $parent_company, $region_id, $country_id, $market_id);
                }

            }else{//Es manager
                $client_companies = $this->Empresa->findByTypeWithTeamAndClients('client', $queryCompany, $sortCompany, $region, $parent_company, 0, 0, $market_id);//, $region_id, $country_id, $market_id); Mismo market
            }

        }


        $dealerIdsArr = $clientIdsArr = [];
        if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON && $this->credentials['i_group_id']==IGroup::TERRITORY_MANAGER){//se agrega validacion de que sea territory manager on 23102017
            $userRelations = $this->UsuariosEmpresa->findById($this->credentials['id'], ['UsuariosEmpresa.id']);
            if(!empty($userRelations['SharedDistributors'])){
                $dealerIdsArr =  array_column($userRelations['SharedDistributors'], 'company_id');
                $dealerIds = implode(',',$dealerIdsArr);

                $clientIdsArr =  array_column($userRelations['SharedClients'], 'id');
                $clientIds = implode(',',$clientIdsArr);


                $distributorsShared = $this->Empresa->findSharedDistributorsWithClientsByIds($dealerIds, $clientIds);
                //Mix with normal distributors
                $dist_companies = array_merge($dist_companies, $distributorsShared);

                if($clientIds!=''){
                    $clientsShared = $this->Empresa->findSharedDistributorsWithClientsByIds($clientIds);
                    //Mix with normal clients
                    $client_companies = array_merge($client_companies, $clientsShared);
                    $client_companies = array_map("unserialize", array_unique(array_map("serialize", $client_companies)));
                    //mail("elalbertgd@gmail.com","test",print_r($test,true));
                }

            }
        }

        //get corporates
        $conditionsCorps = $country_id>0 && $this->credentials['i_group_id']!=IGroup::MASTER ? ['Corporativo.country_id'=>$country_id] : [];
        $conditionsCorps['Corporativo.type'] = 'client';
        $conditionsCorps['Corporativo.deleted'] = '0';
        if($queryCompany!='' && in_array($activeTab, ['client_corp','dist_corp'])){
            $conditionsCorps['Corporativo.name'] = $queryCompany;
        }


        $distCorps = $clientCorps = [];
        $clientCorps = $this->Corporativo->find('all',['order'=>['Corporativo.name ASC'],'conditions'=>$conditionsCorps]);
        //mail('elalbertgd@gmail.com','client_corps',print_r($clientCorps,true));

        $conditionsCorps['Corporativo.type'] = 'distributor';
        if($this->credentials['i_group_id']>IGroup::DISTRIBUTOR){
            $distCorps = $this->Corporativo->find('all',['order'=>['Corporativo.name ASC'],'conditions'=>$conditionsCorps]);
        }


        $this->set('allShareDealers', $salespersonShares);
        $this->set('sharedDealers', $dealerIdsArr);
        $this->set('sharedClients', $clientIdsArr);

        //For multiselect share saleperson companies
        $this->openCssToInclude[] = 'plugins/Assets/css/jquery-multiselect/multi-select';
        $this->openJsToInclude[] = 'plugins/Assets/js/jquery-multiselect/jquery.multi-select';
        $this->set('openCssToInclude', $this->openCssToInclude);
        $this->set('openJsToInclude', $this->openJsToInclude);

        $this->cssToInclude[] = 'reset';
        $this->set('cssToInclude', $this->cssToInclude);

        $this->jsToInclude[] = 'application/Users/usuarios';
        $this->set('jsToInclude', $this->jsToInclude);

        $admin_companies = [];
        $distCorps = [];
        $clientCorps = [];
        $autocompleteCompanies = $this->Core->initAutocompleteCompanies($admin_companies, $dist_companies, $client_companies, $distCorps, $clientCorps);

        $this->setJsVar('autocompleteCompanies', $autocompleteCompanies);
        $this->setJsVar('usersDataReload', $this->_html->url(array('controller' => 'Users', 'action' => 'all', $this->usercode)));

        $this->set('regions', $regions);
        $this->set('countries', $countries);

        $this->set('market_companies', $market_companies);
        $this->set('country_companies', $country_companies);
        $this->set('region_companies', $region_companies);
        $this->set('admin_companies', $admin_companies);
        $this->set('dist_companies', $dist_companies);
        $this->set('client_companies', $client_companies);
        $this->set('active_tab', $activeTab);

        $this->set('client_corps', $clientCorps);
        $this->set('dist_corps', $distCorps);
    }

    /**
     * clients action for clients view
     */
    public function clients() {
        $this->set('title_for_layout', 'Buoys');
        $type_manager = Configure::read('type_manager'); 
        if(!is_null($type_manager) && $this->credentials['role_company']!=UsuariosEmpresa::IS_DIST){
            $this->Auth->deny($this->action);
            $this->redirect($this->Auth->loginRedirect);
        }
        
        if (!$this->Session->check(Statistic::GO_CLIENTS)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_CLIENTS);
            $this->Session->write(Statistic::GO_CLIENTS, Statistic::GO_CLIENTS);
        }

        $this->set('options_toolbar', 'search-users-clients');

        $queryCompany = $sortCompany = '';
        $activeTab = 'admin';
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $sortCompany = $params[1];
                $queryCompany = isset($params[2]) ? $params[2] : '';
            }
        }

        //Obtener las empresas tipo cliente que aplican segun el tipo de usuario logueado
        $filterCompanies = $this->Core->getCompaniesFilterAccordingUserLogged();

        //if salesperson, get ids of shared clients with him
        $clientIdsArr = $this->Core->getSharedClientIdsSalesperson();


        //$client_companies = $this->Empresa->findByTypeWithTeam('client',$queryCompany, $sortCompany);
        $client_companies = $this->Empresa->findClientCompaniesByIdsWithTeam($filterCompanies, $queryCompany, $sortCompany);


        $this->jsToInclude[] = 'application/Users/clientes';
        $this->set('jsToInclude', $this->jsToInclude);

        $autocompleteCompanies = $this->Core->initAutocompleteCompanies(array(), array(), $client_companies);
        $this->setJsVar('autocompleteCompanies', $autocompleteCompanies);
        $this->setJsVar('clientsDataReload', $this->_html->url(array('controller' => 'Users', 'action' => 'clients', $this->usercode)));


        $this->set('client_companies', $client_companies);
        $this->set('sharedClients', $clientIdsArr);
        $this->Core->setTutorialSectionViewed(10);
    }

    /**
     * add action for save a new system user with client, distributor and admin profile
     */
    public function add() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $typeAdd = count($params)>0 ? $params[0] : '';

        $regions = $this->Region->find('all', array('order' => array('Region.id' => 'ASC')));
        $paises = $this->Pais->getAll();
        $empresas = $this->Empresa->find('all', array('order' => array('Empresa.name' => 'ASC')));
        $corporativos = $this->Corporativo->find('all', array('order' => array('Corporativo.name' => 'ASC')));
        //$order_industry = $this->language == IS_ESPANIOL ? 'name' : 'name_en';
        $order_industry = $this->language == IS_ESPANIOL ? 'name' : 'name';
        $industrias = $this->TipoIndustria->find('all', array('order' => array($order_industry => 'ASC')));
        $personasAtencion = $this->AtencionPersona->find('all', array('order' => array('name' => 'ASC')));
        
        $this->uses[] = 'CompanyRole';
        $field_order = $this->Core->_app_language == IS_ESPANIOL ? 'name' : 'name_en';
        $companyRoles = $this->CompanyRole->find('all',array('order'=>array($field_order => 'ASC'), 'fields' => array($field_order. ' AS name_role','id')));

        //Obtenemos todas las empresas distribuidoras 
        //$distributors = $this->Empresa->findByTypeWithCorporate('distributor');

        $region = $this->global_filter_region;
        if($this->credentials['role'] == UsuariosEmpresa::IS_ADMIN){
            $logged_user = $this->credentials;
            $region = $logged_user['regions'] == "" ? $region : $logged_user['regions'];
        }
        $distributors = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $region);
        
        $manager_corporate = Configure::read('manager_corporate'); 
        if(!is_null($manager_corporate) && $this->credentials['role_company']==UsuariosEmpresa::IS_DIST){//Si es un manager dis
              $distributors = $this->Empresa->findByRegionAndTypeWithCorporate('distributor','',$manager_corporate);
        }

        $sharedDealers = $this->Core->getSharedDealersSalesperson();
        if(!empty($sharedDealers)){
            $distributors = array_merge($distributors, $sharedDealers);
        }


        $isUsUser = false;
        $regionUserLogged = $this->Core->getRegion();
        if(in_array($regionUserLogged, ["US","CA"])){
            $isUsUser = true;
        }

        $this->set('settings', Configure :: read('Settings'));
        $this->set('isUsUser', $isUsUser);
        $this->set('typeAdd', $typeAdd);
        $this->set('regions', $regions);
        $this->set('paises', $paises);
        $this->set('empresas', $empresas);
        $this->set('corporativos', $corporativos);
        $this->set('industrias', $industrias);
        $this->set('personasAtencion', $personasAtencion);
        $this->set('distributors', $distributors);
        
        $this->set('companyRoles', $companyRoles);
        
        $this->Core->setTutorialSectionViewed(14);
    }



    public function saveSingleCompany() {
        $is_mobile = $this->request->is('mobile');
        if ($this->request->is('post') && $is_mobile) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            $token = isset($data['token']) ? $data['token'] : '0';
            $token = $this->Token->findByAuthKey($token);
            if (!empty($token)) {
                if (!isset($data['user_region_txt'], $data['country_user'], $data['state_user']) || $data['user_type_txt'] == '' || $data['empresa'] == '' ||
                        $data['all_distributors_txt'] == '' || (int) $data['all_distributors_txt'] <= 0) {
                    $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                } else {

                    $tipo_usuario = $data['user_type_txt'];
                    $region_usuario = $data['user_region_txt'];
                    $id_corporativo = isset($data['all_corporates']) &&  $data['all_corporates']!= '' ? $data['all_corporates'] : 0;
                    $empresa_name = $data['empresa'];
                    $ciudad = isset($data['ciudad']) ? $data['ciudad'] : '';
                    $direccion = isset($data['direccion']) ? $data['direccion'] : '';
                    $parent_dist = isset($data['all_distributors_txt']) && $data['all_distributors_txt'] != '' ? $data['all_distributors_txt'] : 0; //Para el caso de alta de usuario cliente
                    $dealer = $this->Empresa->findById($parent_dist);
                    $region_id = $dealer['Empresa']['region_id'];
                    $country_id = $dealer['Empresa']['i_country_id'];
                    $market_id = $dealer['Empresa']['i_market_id'];
                    $territory_id = $dealer['Empresa']['territory_id'];

                    $active = 1;
                    $aprobado = 'SI';
                    //
                    //Setting info user for processs
                    $info_usuario = $this->UsuariosEmpresa->findFullInfoById($token['Token']['user_id']); //Consultamos BD para actualizar posibles nuevos valores del registro
                    if (!empty($info_usuario)) {
                        $usuario = $info_usuario[0]['UsuariosEmpresa'];
                        $empresa = $info_usuario[0]['Empresa'];
                        $this->credentials = array_merge($usuario, $empresa);
                        $this->Core->setAppCredentials($this->credentials);
                        $id_empresa = $this->Transactions->addCompany($id_corporativo, $empresa_name, $ciudad, $direccion, '', $tipo_usuario, $parent_dist, $region_usuario, $active, $aprobado);
                        if ($id_empresa > 0) {
                            $this->Empresa->id = $id_empresa;
                            $this->Empresa->saveField('i_market_id', $market_id);
                            $this->Empresa->saveField('country_id', $country_id);
                            $this->Empresa->saveField('i_country_id', $country_id);
                            $this->Empresa->saveField('territory_id', $territory_id);
                            $this->Empresa->saveField('group_bucket_id', 20);
                            $this->Empresa->saveField('region_id', $region_id);

                            $response['msg'] = __('La informacion se proceso correctamente', true);
                            $response['success'] = true;                        
                        }else{
                            $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                        }
                    } else {
                        $response['msg'] = __('Acceso no autorizado', true);
                    }
                }
            } else {
                $response['msg'] = __('Acceso no autorizado', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Function save a new user account
     */
    public function save() {
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => 0, 'typeCompany'=>'');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            //var_dump(isset($params['nousers']));
            if ( ( isset($params['nousers']) && !isset($data['user_region_txt'], $data['country_user'], $data['state_user'])) || $data['user_type_txt'] == '' || $data['empresa_txt'] == '') {
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                $response['code'] = 1;
            }else if (!isset($params['nousers']) && ($data['user_type_txt'] == '' || $data['empresa_txt'] == '' || $data['nombre'] == '' || $data['email'] == '' || $data['password'] == '')) {
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                $response['code'] = 1;
            } else {
                list($tipo_usuario, $grupo) = explode('|', $data['user_type_txt']);
                $region_usuario = $data['user_region_txt'];
                $pais_usuario = isset($data['country_user']) ? $data['country_user'] : "";
                $estado_usuario = isset($data['state_user']) ? $data['state_user'] : "";
                
                $marca =  isset($data['brand']) ? $data['brand'] : "";

                $corporativo = isset($data['corporativo']) ? $data['corporativo'] : '';
                $empresa = $data['empresa_txt'];
                $ciudad = isset($data['ciudad']) ? $data['ciudad'] : '';
                $salesperson = isset($data['salesperson']) ? $data['salesperson'] : null;
                $direccion = isset($data['direccion']) ? $data['direccion'] : '';

                $sap_number = isset($data['sap_number']) && $data['sap_number']!='' ? $data['sap_number'] : 0;
                $salesforce_id = isset($data['salesforce_number']) && $data['salesforce_number']!='' ? $data['salesforce_number'] : 0;

                if(!isset($params['nousers'])) {
                    $nombre_usuario = $data['nombre'];
                    $email_usuario = $data['email'];
                    $telefono_usuario = $data['telefono'];
                    $puesto_usuario = $data['puesto'];

                    $no_emp_admin = $data['no_empleado'];
                    $unidad_neg_admin = $data['unidad_negocio'];

                    $zona_d = $data['zona'];
                    $atendio_d = $data['atiende'];

                    $area_c = $data['area'];
                    $industria_c = $data['industria'];

                    $encriptacion = _TYPECRYPT;
                    $password = $encriptacion($data['password']);
                    $password_nocrypt = $data['password'];
                    //$username = $this->Core->createSystemUsername($email_usuario, $tipo_usuario);
                    $username = $this->Core->createUniqueUsername($nombre_usuario);

                }else{
                    $email_usuario = 'go@registry.com';
                }

                $parent_dist = isset($data['all_distributors_txt']) && $data['all_distributors_txt'] != '' ? $data['all_distributors_txt'] : 0; //Para el caso de alta de usuario cliente
                $active = 1;
                $aprobado = in_array($this->credentials['role'], array('admin', 'master')) ? 'SI' : 'SI';

                $path_logo_empresa = $data['path_logo_empresa'];
                $path_logo_usuario = $data['path_logo_usuario'];
               
                $country_id = isset($params['country_id']) ? $params['country_id'] : 0;
                
                 //Primero checar si el email proporcionado ya existe
                if (filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
                    if(!isset($params['nousers'])) {//if !isset this index, create company with users
                        $userEmpresa = $this->UsuariosEmpresa->findByEmail($email_usuario);
                    }
                    //if (empty($userEmpresa)) {//Si esta vacio, es que el email no se encontro
                    $this->Corporativo->begin();
                    $id_corporativo = $this->Transactions->addCorporate($corporativo, $tipo_usuario, $region_usuario);
                    if ($id_corporativo >= 0) {//El corporativo no se eligio o fue uno nuevo y se guardo
                        //Ahora agregar la empresa
                        $this->Empresa->begin();
                        $id_empresa = $this->Transactions->addCompany($id_corporativo, $empresa, $ciudad, $direccion, '', $tipo_usuario, $parent_dist, $region_usuario, $active, $aprobado);
                        if ($id_empresa > 0) {
                           if(!isset($params['nousers'])) { //if !isset this index, create company with users

                               $companySelected = $this->Empresa->findById($id_empresa);
                               $companySelected = $companySelected["Empresa"];

                               $country_id = $companySelected["country_id"];
                               $pais_usuario = $companySelected["country"];
                               $estado_usuario = $companySelected["state"];


                               $professionalType = 'normal';
                               $regionUserLogged = $this->Core->getRegion();
                               if(in_array($regionUserLogged, ["US","CA"])){
                                   $professionalType = $params['professional_user'] == 1 ? 'pro' : 'lite';
                               }



                                $userType = $tipo_usuario;
                                $tipo_usuario = $params['manager'] == 1 ? UsuariosEmpresa::IS_MANAGER : $tipo_usuario;
                                $grupo = $params['manager'] == 1 ? Group::MANAGER : $grupo;
                                $user_id = $this->UsuariosEmpresa->add($username, $password, $id_empresa, $nombre_usuario, $email_usuario, $telefono_usuario, $puesto_usuario, $no_emp_admin, $unidad_neg_admin, $zona_d, $atendio_d, $area_c, $industria_c, '', $tipo_usuario, $grupo, $region_usuario, $pais_usuario, $estado_usuario, $active, $aprobado);
                                if ($user_id > 0) {
                                    $this->Corporativo->commit();
                                    $this->Empresa->commit();

                                    //save id_country user
                                    $this->UsuariosEmpresa->id = $user_id;
                                    $this->UsuariosEmpresa->saveField('country_id', $country_id);
                                    $this->UsuariosEmpresa->saveField('type', $professionalType);

                                    $iGroupId = $grupo;
                                    if($tipo_usuario == IGroup::MANAGER){
                                        $iGroupId = $userType == IGroup::CLIENT ? 30 : 50;
                                    }
                                    $this->UsuariosEmpresa->saveField('i_group_id', $iGroupId);

                                    //Actualizamos las imagenes de usuario y empresa
                                    $this->Transactions->updateCompanyLogo($path_logo_empresa, $id_empresa);
                                    $this->Transactions->updateUserLogo($path_logo_usuario, $id_empresa, $user_id);

                                    /*
                                     * SEND MAIL
                                     */
                                    $this->Mail->sendAccessUserData($user_id, $password_nocrypt);

                                    $response['msg'] = __('El usuario %s se guardo exitosamente', $nombre_usuario);
                                    $response['success'] = true;
                                } else {
                                    $this->Corporativo->rollback();
                                    $this->Empresa->rollback();
                                    //$this->Corporativo->delete($id_corporativo);
                                    //$this->Empresa->delete($id_empresa);
                                    $response['msg'] = __('No se pudo insertar el usuario %s, intentelo nuevamente', $nombre_usuario);
                                }
                            }else{
                               $this->Corporativo->commit();
                               $this->Empresa->commit();
                               $this->Empresa->id = $id_empresa;
                               //$this->Empresa->saveField('brand', $marca);
                               //$this->Empresa->id = $id_empresa;
                               $this->Empresa->saveField('brand', $marca);
                               $this->Empresa->saveField('salesperson_id', $salesperson);
                               $this->Empresa->saveField('salesperson_user_id', $salesperson);
                               $this->Empresa->saveField('sap_number', $sap_number);
                               $this->Empresa->saveField('salesforce_number', $salesforce_id);
                               $this->Empresa->saveField('country', $pais_usuario);
                               $this->Empresa->saveField('state', $estado_usuario);
                               $this->Empresa->saveField('country_id', $country_id);
                               //Actualizamos las imagenes de usuario y empresa
                               $this->Transactions->updateCompanyLogo($path_logo_empresa, $id_empresa);
                               $response['msg'] = __('La empresa %s se guardo exitosamente', $empresa);
                               $response['typeCompany'] = $tipo_usuario;
                               $response['companyName'] = $empresa;
                               $response['success'] = true;

                               //save score data statistic
                               $salespersonAssoc = $this->Core->getSalespersonIfExists($id_empresa);
                               //if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON && $grupo==Group::CLIENT){
                               if($salespersonAssoc>0 && $grupo==Group::CLIENT){
                                   $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_CUSTOMER);
                               }

                           }
                        } else {
                            $this->Corporativo->rollback();
                            //$this->Corporativo->delete($id_corporativo);
                            $response['msg'] = $id_empresa == 0 ? __('Ocurrio un error al registrar la empresa %s, intentelo nuevamente', array($empresa)) : __('La empresa %s ya se encuentra registrada', array($empresa));
                        }
                    } else {
                        $response['msg'] = __('El corporativo %s ya se encuentra registrado', $corporativo);
                    }
                    /* } else {
                      $response['msg'] = __('El email %s ya se encuentra registrado', $email_usuario);
                      } */
                } else {
                    $response['msg'] = __('El email %s no tiene un formato de correo valido', $email_usuario);
                }
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * action for company profile view
     */
    public function companyProfile() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findByIdWithCorporate($company_received);
                    if (!empty($company)) {
                        $empresa = $company['Empresa'];
                        $salespersonUsers = [];
                        /*if(in_array($empresa["type"],[UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST])) {
                            $distributorId = $empresa['parent']>0 ? $empresa['parent'] : $empresa['id'];
                            $salespersonUsers = $this->Core->getSalespersonForDistributorId($distributorId);
                        }*/

                        if(in_array($empresa["type"],[UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST])) {
                            $distributorId = $empresa['parent']>0 ? $empresa['parent'] : $empresa['id'];

                            //if($empresa['parent']>0){

                            //get company distributor and his shared salesperson
                            $empresaDistribuidor = $this->Empresa->findById($distributorId);

                            //get salesperson by shared salesperson to distributor
                            $salespersonAssocToDist = array_column($empresaDistribuidor['SalespersonShares'],'user_sp_id');
                            $salespersonUsers = $this->UsuariosEmpresa->find('all',['recursive'=>-1,'conditions'=>['id'=>$salespersonAssocToDist,'puesto'=>7,'deleted'=>0], 'fields'=>['id','name']]);

                            //get salesperson by territory of distributor
                            $salespersonTerritory = $this->UsuariosEmpresa->find('all',['recursive'=>-1,'conditions'=>['UsuariosEmpresa.i_group_id'=>IGroup::TERRITORY_MANAGER,'UsuariosEmpresa.region'=>$empresa['region'],'UsuariosEmpresa.puesto'=>7,'UsuariosEmpresa.deleted'=>0], 'fields'=>['id','name']]);

                            $salespersonUsers = array_merge($salespersonUsers, $salespersonTerritory);
                            //}
                        }





                        $corporativo = $company['Corporativo'];
                        $this->set('settings', Configure :: read('Settings'));
                        $this->set('empresa', $empresa);
                        $this->set('corporativo', $corporativo);
                        $this->set('salespersonAssoc', $salespersonUsers);
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('No se encontro informacion', true);
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

    public function updateCompany() {
        $response = array();
        $response['success'] = false;
        $response['msg'] = '';
        $response['code'] = 0;

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => 0);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            //if (trim($data['city-company']) == '') {
             //   $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            //} else {
                if (isset($data['tokencompany'])) {
                    $company_params = explode('|', $data['tokencompany']);
                    if (!empty($company_params) && count($company_params) == 2) {
                        $decodedCompanyParams = $this->Core->decodePairParams($company_params);
                        $companyForUpdate = $this->Empresa->findById($decodedCompanyParams['item_id']);
                        if ($decodedCompanyParams['isOk'] && !empty($companyForUpdate)) {
                            $empresa = $companyForUpdate['Empresa'];

                            $nameCompany = isset($data['company-name']) ? $data['company-name'] : $empresa['name'];
                            $salesperson = isset($data['salesperson']) ? $data['salesperson'] : null;
                            $address_company = utf8_decode(trim($data['address-company']));
                            $city_company = isset($data['city-company']) ? utf8_decode(trim($data['city-company'])) : $empresa['city'];
                            $empresaExists =  $this->Empresa->findByNameAndDeleted($nameCompany,0);
                            if(empty($empresaExists) || $empresaExists['Empresa']['id']==$empresa['id']){ //Si no existe la empresa o si el id de la empresa encontrada es igual a la que se esta editando
                                $companyDataUpdate = array(
                                    'name' => "'$nameCompany'",
                                    'salesperson_id' => is_null($salesperson) ? "''" : "'$salesperson'",
                                    'salesperson_user_id' => is_null($salesperson) || $salesperson == 0 ? null : "'$salesperson'",
                                    'address' => "'$address_company'",
                                    'city' => "'$city_company'"
                                );

                                if ($this->Empresa->updateAll($companyDataUpdate, array('Empresa.id' => $empresa['id']))) {
                                    $this->Transactions->updateCompanyLogo($data['logo-company-hidden'], $empresa['id']);
                                    $response['success'] = true;
                                    $response['msg'] = __('La informacion se proceso correctamente', true);
                                } else {
                                    $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                                }
                            }else{
                                $response['msg'] = __('La empresa %s ya se encuentra registrada', array($nameCompany));
                            }
                        } else {
                            $response['msg'] = __('Error, permisos insuficientes', true);
                        }
                    } else {
                        $response['msg'] = __('El registro no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('El registro no fue encontrado', true);
                }
            //}

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * profile action for view profile info user
     */
    public function profile() {
        $user_received = 0;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $userForView = $this->UsuariosEmpresa->findUserInfo($decodedUserParams['item_id']);
                    //$userForView = $this->UsuariosEmpresa->findUserInfo($this->credentials['id']);
                    
                    if (!empty($userForView)) {
                        $usuario = $userForView['Usuario'];
                        $empresa = $userForView['Empresa'];
                        $region = $userForView['Region'];
                        $atiende = $userForView['AtencionPersona'];
                        $tipo_industria = $userForView['TipoIndustria'];
                        $corporativo = !is_null($userForView['Corporativo']['name']) ? $userForView['Corporativo']['name'] : '';

                        $salesperson = [];
                        if(!is_null($empresa['salesperson_user_id'])){
                            $this->UsuariosEmpresa->recursive = 0;
                            $salesperson = $this->UsuariosEmpresa->findById($empresa['salesperson_user_id'],['UsuariosEmpresa.name']);
                        }


                        $this->uses[] = 'CompanyRole';
                        $field_order = $this->Core->_app_language == IS_ESPANIOL ? 'name' : 'name_en';
                        $companyRole = $this->CompanyRole->find('all',array('conditions'=>array('id'=>$usuario['puesto']),'fields' => array($field_order. ' AS name_role','id')));
                        $companyRole = !empty($companyRole) ? $companyRole[0]['CompanyRole']['name_role'] : '-';

                        $companyRoles = $this->CompanyRole->find('all',array('order'=>array($field_order => 'ASC'), 'fields' => array($field_order. ' AS name_role','id')));

                        //$isUsUser = $this->Core->isUsRegion($usuario['region']);
                        $isUsUser = $empresa['i_market_id'] == IMarket::Is_USCanada ? true : false;

                        $this->set('isUsUser', $isUsUser);
                        //Si llego hasta aqui, es que todo fue bien      
                        $this->set('usuario', $usuario);
                        $this->set('empresa', $empresa);
                        $this->set('corporativo', $corporativo);
                        $this->set('region', $region);
                        $this->set('user_params', $params);
                        $this->set('atendio', $atiende);
                        $this->set('industria', $tipo_industria);
                        $this->set('roleCompany', $companyRole);
                        $this->set('companyRoles', $companyRoles);
                        $this->set('salesperson', $salesperson);

                        $this->set('settings', Configure :: read('Settings'));
                    } else {
                        $response['msg'] = __('El registro no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function update() {
        $response = array();
        $response['success'] = false;
        $response['msg'] = '';
        $response['code'] = 0;

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => 0);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            if (trim($data['fullname']) == '' || trim($data['email']) == '') {
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                $response['code'] = 1;
            } else {
                if (isset($data['tokencompany'], $data['tokenuser'])) {
                    $user_params = explode('|', $data['tokenuser']);
                    $company_params = explode('|', $data['tokencompany']);
                    if (!empty($user_params) && !empty($company_params) && count($user_params) == 2 && count($company_params) == 2) {
                        $decodedUserParams = $this->Core->decodePairParams($user_params);
                        $decodedCompanyParams = $this->Core->decodePairParams($company_params);

                        $user_received = $decodedUserParams['item_id'];
                        $userForUpdate = $this->UsuariosEmpresa->findUserInfo($user_received);
                        if ($decodedUserParams['isOk'] && $decodedCompanyParams['isOk'] && !empty($userForUpdate)) {
                            $usuario = $userForUpdate['Usuario'];
                            $empresa = $userForUpdate['Empresa'];
                            $corporativo = $userForUpdate['Corporativo'];

                            $nameUser = utf8_decode(trim($data['fullname']));
                            $puesto = utf8_decode(trim($data['puesto']));
                            $email = utf8_decode(trim($data['email']));
                            $phone = utf8_decode(trim($data['phone']));
                            $no_empleado_a = isset($data['no_empleado_a']) ? utf8_decode(trim($data['no_empleado_a'])) : '';
                            $unidad_negocio_a = isset($data['unidad_negocio_a']) ? utf8_decode(trim($data['unidad_negocio_a'])) : '';

                            $area_c = isset($data['area_c']) ? utf8_decode(trim($data['area_c'])) : '';


                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $userCheck = $this->UsuariosEmpresa->findByEmail($email);//check received email

                                $repeatedEmail = empty($userCheck) ? false : true;
                                if($repeatedEmail){
                                    //if founded user id is equal to current user update, is just edition, allow continue
                                    $repeatedEmail = $userCheck['UsuariosEmpresa']['id'] == $usuario['id'] ? false : true;
                                }

                                //Si esta vacio, es que el email no se encontro o si existe pero son iguales los emails es que esta
                                //actualizando un usuario
                                //if (!$repeatedEmail) {

                                    $professionalType = 'normal';
                                    //if($this->Core->isUsRegion($userCheck['UsuariosEmpresa']['region'])){ //if is us user, get param
                                    //if($this->Core->isUsRegion($usuario['region'])){ //if is us user, get param
                                    if($empresa["i_market_id"]==IMarket::Is_USCanada) { //es de market US
                                        $professionalType = $params['professional_user'] == 1 ? 'pro' : 'lite';
                                    }

                                    $userDataUpdate = array(
                                        'name' => "'$nameUser'",
                                        'puesto' => "'$puesto'",
                                        'email' => "'$email'",
                                        'phone' => "'$phone'",
                                        'no_empleado_a' => "'$no_empleado_a'",
                                        'unidad_negocio_a' => "'$unidad_negocio_a'",
                                        'area_c' => "'$area_c'",
                                        'type' => "'$professionalType'"
                                    );

                                    $this->UsuariosEmpresa->begin();
                                    if ($this->UsuariosEmpresa->updateAll($userDataUpdate, array('UsuariosEmpresa.id' => $user_received))) {
                                        $address_company = utf8_decode(trim($data['address-company']));
                                        $city_company = isset($data['city-company']) ? utf8_decode(trim($data['city-company'])) : '';
                                        $salesperson = isset($data['salesperson']) ? $data['salesperson'] : 0;
                                        $companyDataUpdate = array(
                                            'salesperson_id' => "'$salesperson'",
                                            'address' => "'$address_company'",
                                            'city' => "'$city_company'"
                                        );
                                        if ($this->Empresa->updateAll($companyDataUpdate, array('Empresa.id' => $empresa['id']))) {
                                            $this->UsuariosEmpresa->commit();
                                            $this->Transactions->updateCompanyLogo($data['logo-company-hidden'], $empresa['id']);
                                            $this->Transactions->updateUserLogo($data['profile-picture-hidden'], $empresa['id'], $user_received);
                                            $response['success'] = true;
                                            $response['msg'] = __('La informacion se proceso correctamente', true);
                                        } else {
                                            $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                                            $this->UsuariosEmpresa->rollback();
                                        }
                                    } else {
                                        $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                                    }
                                /*} else {
                                    $response['msg'] = __('El email %s ya se encuentra registrado', $email);
                                }*/
                            } else {
                                $response['msg'] = __('El email %s no tiene un formato de correo valido', $email);
                            }
                        } else {
                            $response['msg'] = __('Error, permisos insuficientes', true);
                        }
                    } else {
                        $response['msg'] = __('El registro no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('El registro no fue encontrado', true);
                }
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function suspendUnsuspend() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForSuspend = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForSuspend)) {
                        $active = 1;
                        $msg = __("El usuario fue reactivado exitosamente", true);
                        if ($userForSuspend['UsuariosEmpresa']['active'] == 1) {
                            $active = 0;
                            $msg = __('El usuario fue suspendido exitosamente', true);
                        }

                        $this->UsuariosEmpresa->id = $user_received;
                        $this->UsuariosEmpresa->saveField('active', $active);
                        $response['msg'] = $msg;
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
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

    public function lockUnlock() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForLocking = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForLocking)) {
                        $lock_status = UsuariosEmpresa::IS_UNLOCKED;
                        $access_attempts = 0;

                        $msg = __("El usuario fue desbloqueado exitosamente", true);
                        if ($userForLocking['UsuariosEmpresa']['lock_status'] == UsuariosEmpresa::IS_UNLOCKED) {
                            $lock_status = UsuariosEmpresa::IS_PERMANENTLY_LOCKED;
                            $access_attempts = 5;
                            $msg = __('El usuario fue bloqueado exitosamente', true);
                        }

                        /*
                         * Si el usuario fue bloqueado por region hay que eliminar el registro de la ubicacion de
                         *  ultimo acceso
                         * * */
                        $last_login_info = array();
                        if ($userForLocking['UsuariosEmpresa']['lock_status'] == UsuariosEmpresa::IS_MISSED_GEO) {
                            $last_login_info = $this->Statistic->find('first', array(
                                'conditions' => array('user_id' => $user_received, 'section' => _SITE_ACCESS),
                                'order' => array('date' => 'desc')
                                    )
                            );
                        }

                        $userForLocking['UsuariosEmpresa']['lock_status'] = $lock_status;
                        $userForLocking['UsuariosEmpresa']['access_attempts'] = $access_attempts;
                        if ($this->UsuariosEmpresa->save($userForLocking)) {
                            $response['msg'] = $msg;
                            $response['success'] = true;
                            if (!empty($last_login_info)) {
                                /*                                 * Eliminamos el registro de ubicacion de ultimo acceso * */
                                $this->Statistic->delete($last_login_info['Statistic']['id']);
                            }
                        } else {
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente');
                        }
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
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

    public function delete() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForSuspend = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForSuspend)) {
                        if ($user_received != $this->credentials['id']) {
                            $this->UsuariosEmpresa->id = $user_received;
                            $this->UsuariosEmpresa->saveField('deleted', 1);
                            $response['msg'] = __("El usuario fue eliminado exitosamente", true);
                            $response['success'] = true;
                        } else {
                            $response['msg'] = __('Imposible eliminar a si mismo', true);
                        }
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
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

    public function accessData() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForView = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForView)) {
                        $this->set('usuario', $userForView['UsuariosEmpresa']);
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
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

    public function updateAccessData() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $params_form = $this->request->data; //get data
        parse_str($params_form['formdata'], $data);
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForUpdate = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForUpdate)) {
                        $encriptacion = _TYPECRYPT;
                        $password = $encriptacion($data['password']);
                        $password_nocrypt = $data['password'];
                        //$password = $data['password'];
                        $username = $data['username'];

                        $userForUpdate['UsuariosEmpresa']['password'] = $password;
                        $userForUpdate['UsuariosEmpresa']['username'] = $username;
                        if ($this->UsuariosEmpresa->save($userForUpdate)) {
                            /*
                             * SEND MAIL
                             */
                            //$this->Mail->sendAccessUserData($user_received, $password_nocrypt);
                            $response['msg'] = __('La informacion ha sido actualizada exitosamente', true);
                            $response['success'] = true;
                        } else {
                            $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                        }

                        /*
                          //No existe el username o es el mismo que el logueado, solo se cambio el password
                          if (empty($userCheck) || $username == $this->credentials['username']) {
                          $userDataUpdate = array(
                          'password' => "'$password'",
                          'username' => "'$username'"
                          );

                          if ($this->UsuariosEmpresa->updateAll($userDataUpdate, array('id' => $user_received))) {
                          $response['msg'] = __('La informacion ha sido actualizada exitosamente', true);
                          $response['success'] = true;
                          } else {
                          $response['msg'] = __('Error al procesar la informacion, intentelo nuevamente', true);
                          }
                          } else {
                          $response['msg'] = __('El username proporcionado, ya se encuentra registrado, elija uno diferente', true);
                          } */
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
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

    public function securityQuestion() {
        if ($this->request->is('post') && !is_null($this->credentials)) {
            $this->set('questions_config', Configure :: read('Fingerprint'));
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processSecurityQuestion(){
        if ($this->request->is('post') && !is_null($this->credentials)) {
            $params_form = $this->request->data; //get data
            parse_str($params_form['formdata'], $data);
            $response = array('success' => false, 'msg' => '');

            if(isset($data['question'],$data['answer']) && $data['answer']!=""){
                $this->uses[] = 'SecurityQuestion';

                $securityQuestion = [
                    'user_id' => $this->credentials['id'],
                    'question_id' => $data['question'],
                    'answer' => trim(strtolower($data['answer']))
                ];

                $userLogged = [
                    'id' => $this->credentials['id'],
                    'security_question' => $data['question']
                ];

                if ($this->SecurityQuestion->save($securityQuestion)) {
                    $this->UsuariosEmpresa->save($userLogged);
                    $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                    $response['success'] = true;
                }else{
                    $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                }
            }else{
                $response['msg'] = __('Proporcione todos los campos requeridos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function setEulaOk(){
        $this->layout = false;
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $this->UsuariosEmpresa->recursive = 0;
                    $userForUpdate = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForUpdate)) {
                        $this->uses[] = 'EulaUser';
                        $location = $this->Session->check('geolocalization_data') ?  $location = $this->Session->read('geolocalization_data'): $this->Secure->getGeolocalizationData();
                        $location = is_null($location['country']) && is_null($location['state']) ? 'Location not defined' : "$location[state] ($location[state_code]), $location[country] ($location[country_code]), $location[ip]";

                        $eulaUser = [
                            'user_id' => $user_received,
                            'location' => $location
                        ];
                        if ($this->EulaUser->save($eulaUser)) {
                            $eulaOkRowId = $this->EulaUser->getInsertID();
                            $userForUpdate['UsuariosEmpresa']['eula_ok'] = $eulaOkRowId;
                            $this->UsuariosEmpresa->save($userForUpdate);
                            $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                            $response['success'] = true;
                        }else{
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                        }
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }



    public function answerSecurityQuestion() {
        $error = true;
        if ($this->request->is('post')) {
            $params_form = $this->request->data; //get data
            if(isset($params_form['user'])){
                $user_id = explode('||',$params_form['user']);
                $decodedUserParams = $this->Core->decodePairParams($user_id);
                if ($decodedUserParams['isOk']) {
                    $user = $this->UsuariosEmpresa->findById($decodedUserParams['item_id']);
                    if(!empty($user)){
                        $error = false;
                        $this->set('questions_config', Configure :: read('Fingerprint'));
                        $this->set('user',$user['UsuariosEmpresa']);
                    }
                }
            }

        }

        if($error){
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processAnswerSecurityQuestion(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $params_form = $this->request->data; //get data
            parse_str($params_form['formdata'], $data);
            $response = array('success' => false, 'msg' => '', 'attempts' => 0);
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForCheck = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForCheck)) {
                        $this->uses[] = 'SecurityQuestion';
                        $question = $this->SecurityQuestion->findByUserIdAndQuestionId($userForCheck['UsuariosEmpresa']['id'],$userForCheck['UsuariosEmpresa']['security_question']);
                        if(!empty($question)){
                            $question = $question['SecurityQuestion'];
                            if($question['answer']==trim(strtolower($data['answer']))){
                                $response['success'] = true;
                                $userForCheck['UsuariosEmpresa']['attempts_answer'] = 0;
                                $this->UsuariosEmpresa->save($userForCheck);
                            }else{
                                $attempts = $userForCheck['UsuariosEmpresa']['attempts_answer'] + 1;
                                $userForCheck['UsuariosEmpresa']['attempts_answer'] = $attempts;
                                $this->UsuariosEmpresa->save($userForCheck);
                                $response['msg'] = __('Error on response security question', true);
                                $response['attempts'] = $attempts;
                                if($attempts>=3){
                                    $response["msg"] = __('The maximum number of attempts has been exceeded. Please send a request to your Administrator %s at this email %s to allow you accessing with a new device.',["security@contiplus.net"]);
                                }
                            }
                        }else{
                            $response['msg'] = __('Error on response security question', true);
                        }
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response',$response);
        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function getCompaniesDistributor(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $sortCompany = 'name';
            $data = $this->request->data;
            $dist_companies = [];
            if(isset($data['region'])){
                $region = $data['region'];
                $dist_companies =  $this->Empresa->findByTypeWithTeamAndClients('distributor', $queryCompany = "", $sortCompany, $region);
            }

            $this->set('dist_companies', $dist_companies);
        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function resendAccountDataManual($idToSend){
        $this->layout = false;
        $this->autoRender = false;


        if (!is_null($this->credentials) && $this->credentials['id']==1) { //my account
            $response = array('success' => false, 'msg' => '', 'attempts' => 0);

            $user_received = $idToSend; //use to resend invitation

                    $userForCheck = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForCheck)) {
                        $password = $this->Core->getRandomPassword();
                        //$username = str_replace('_','.',$userForCheck['UsuariosEmpresa']['username']);
                        $username = $userForCheck['UsuariosEmpresa']['username'];
                        $userForCheck['UsuariosEmpresa']['password'] = sha1($password);
                        $userForCheck['UsuariosEmpresa']['username'] = $username;
                        $this->UsuariosEmpresa->save($userForCheck);
                        $response['msg'] = __("El email de bienvenida ha sido reenviado exitosamente.", true);
                        $user_id = $userForCheck['UsuariosEmpresa']['id'];
                        $this->Mail->sendAccessUserData($user_id, $password);
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }

            echo json_encode($response);

        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }

    }

    public function resendAccountData(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'attempts' => 0);
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForCheck = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForCheck)) {
                        $password = $this->Core->getRandomPassword();
                        $username = str_replace('_','.',$userForCheck['UsuariosEmpresa']['username']);
                        $userForCheck['UsuariosEmpresa']['password'] = sha1($password);
                        $userForCheck['UsuariosEmpresa']['username'] = $username;
                        $this->UsuariosEmpresa->save($userForCheck);
                        $response['msg'] = __("El email de bienvenida ha sido reenviado exitosamente.", true);
                        $user_id = $userForCheck['UsuariosEmpresa']['id'];
                        $this->Mail->sendAccessUserData($user_id, $password);
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);

        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }

    }

    public function clearFingerprint(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'attempts' => 0);
            if (!empty($params) && count($params) == 2) {
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForCheck = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForCheck)) {
                        $userForCheck['UsuariosEmpresa']['fingerprint'] = "";
                        $this->UsuariosEmpresa->save($userForCheck);
                        $response['msg'] = __("la huella del usuario se limpio exitosamente.", true);
                        $response['success'] = true;
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);

        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function clearQuestionUser(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'attempts' => 0);
            if (!empty($params) && count($params) == 2) {
                $this->uses[] = "SecurityQuestion";
                $decodedUserParams = $this->Core->decodePairParams($params);
                if ($decodedUserParams['isOk']) {
                    $user_received = $decodedUserParams['item_id'];
                    $userForCheck = $this->UsuariosEmpresa->findById($user_received);
                    if (!empty($userForCheck)) {
                        $userForCheck['UsuariosEmpresa']['security_question'] = 0;
                        $this->UsuariosEmpresa->save($userForCheck);
                        $this->SecurityQuestion->deleteAll(['SecurityQuestion.user_id'=>$user_received]);

                        $response['msg'] = __("la pregunta de seguridad se limpio exitosamente.", true);
                        $response['success'] = true;
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);

        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function setRegionManager(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $companyForCheck = $this->Empresa->findById($company_received);
                    if (!empty($companyForCheck)) {
                        $data = $this->request->data;
                        $companyForCheck['Empresa']['rm_admin'] = $data['user_id'];
                        $this->Empresa->save($companyForCheck);
                        $response['msg'] = __('Los datos han sido guardados exitosamente.', true);
                        $response['success'] = true;
                    }else{
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                }else{
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            }else{
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            echo json_encode($response);

        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function sapQuery(){
        $this->layout = false;
        $this->autoRender = false;
        $response = [];

        if ($this->request->is('post')) {
            $params = $this->request->data;
            $HttpSocket = new HttpSocket();
            $data = [
                'api_key' => 'TeG2pROUE7EDjUtymwS3jGIFqxlWalRGiT00Z6Nc',
                'name' => $params['searchStr']
            ];

            $response_request = json_decode($HttpSocket->get('https://cloud.amdis-services.com/customer/', $data), true);
            if ($response_request['totalSize'] > 0 && isset($response_request['salesforceAccountSearchResultList'])) {
                foreach ($response_request['salesforceAccountSearchResultList'] AS $salesForceAccount) {
                    $salesForceAccount['value'] = $salesForceAccount['name'].' - '.$salesForceAccount['city'];
                    $response[] = $salesForceAccount;
                }
            }
            echo json_encode($response);
        }else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
}
