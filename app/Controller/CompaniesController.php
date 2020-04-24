<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file CompaniesController.php
 *     Management of actions for companies
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

App::import('Vendor', 'VideoEncoder', array('file' => 'VideoEncoder/VideoEncoder.php'));
App::import('Vendor', 'Dompdf', array('file' => 'Dompdf/dompdf_config.inc.php'));
App::import('Vendor', 'PhpExcel', array('file' => 'PhpExcel/PHPExcel.php'));
class CompaniesController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'TipoIndustria';
        $this->uses[] = "ClientItem";
        $this->uses[] = "FolderApp";
    }

    /**
     * action for company profile view
     */
    public function view() {
        $this->set('options_toolbar', 'company-buoy-system-root');
        $this->set('title_for_layout', 'Buoys');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedCompanyParams = $this->Core->decodePairParams($params);
            if ($decodedCompanyParams['isOk']) {
                $company_received = $decodedCompanyParams['item_id'];
                $company = $this->Empresa->findByIdWithCorporate($company_received);
                if (!empty($company)) {
                    //$conveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query, $sort);
                    $filter_companies = $company_received;

                    
                    $this->Session->write(parent::ASSOC_COMPANIES, $filter_companies);
                    //$conveyors = $this->Core->getConveyorsUsingFilters($filter_companies);
                    $conveyors = $this->Core->getConveyorsBasicFieldsUsingFilters($filter_companies);
                    $autocompleteConveyors = $this->Core->initAutocompleteConveyors($conveyors);
                    $this->setJsVar('autocompleteConveyors', $autocompleteConveyors);
                    $this->setJsVar('totConveyors', count($conveyors));

                    $distributorClient = $this->Empresa->findById($company_received);
                    $distributorClient = $distributorClient['Empresa'];


                    $secureCompanyParams = $this->Core->encodeParams($company_received);
                    $urlReportsCompany = $this->_html->url(array('controller' => 'Companies', 'action' => 'reportingHistory', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    $urlConveyorReportsCompany = $this->_html->url(array('controller' => 'Companies', 'action' => 'listReportingConveyors', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    
                    $urlProfileCompany = $this->_html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    $urlSuspendActivateCompany = $this->_html->url(array('controller' => 'Companies', 'action' => 'suspendUnsuspend', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    $textSuspendActivate = $company['Empresa']['active']==0 ? __('Reactivar',true) : __('Suspender',true);
                    $urlDeleteCompany = $this->_html->url(array('controller' => 'Companies', 'action' => 'delete', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    $deleteReferer = $this->_html->url(array('controller' => 'Users', 'action' => 'clients'));
                    
                    $this->set('urlProfileCompany', $urlProfileCompany);
                    $this->set('urlSuspendActivateCompany', $urlSuspendActivateCompany);
                    $this->set('textSuspendActivate', $textSuspendActivate);
                    $this->set('urlDeleteCompany', $urlDeleteCompany);
                    $this->set('deleteReferer', $deleteReferer);
                    $this->set('urlreportingHistoryConveyor', $urlReportsCompany);
                    $this->set('urlreportingListConveyors', $urlConveyorReportsCompany);

                    //GetDealer
                    $userDealer = [];
                    $companyDealer = $this->Empresa->findByIdWithCorporate($distributorClient['parent']);


                    if($this->credentials['role']==UsuariosEmpresa::IS_DIST){
                        $userDealer = $this->credentials;
                    }else{
                        $userDealer = $this->UsuariosEmpresa->find("first",array(
                            'conditions' => array('UsuariosEmpresa.id_empresa' => $distributorClient['parent'], 'UsuariosEmpresa.deleted'=>0)
                        ));
                        $userDealer = !empty($userDealer) ? $userDealer["UsuariosEmpresa"] : $userDealer;
                    }

                    //GetAdminRegion
                    $empresaAdmin = $this->Empresa->find("first", array(
                        'conditions' => array('Empresa.region' => $companyDealer["Empresa"]["region"], "Empresa.type" => "admin")
                    ));
                    $userAdmin = $this->UsuariosEmpresa->find("first",array(
                        'conditions' => array('UsuariosEmpresa.id_empresa' => $empresaAdmin["Empresa"]['id'], 'UsuariosEmpresa.deleted'=>0)
                    ));

                    $userAdmin = !empty($userAdmin) ? $userAdmin["UsuariosEmpresa"] : $userAdmin;

                    $this->setJsVar('distCompanyId', $distributorClient['parent']);
                    $this->setJsVar('clientCompanyId', $company_received);

                    $empresa = $company['Empresa'];
                    $corporativo = $company['Corporativo'];


                    $salesperson = [];
                    if(!is_null($empresa['salesperson_user_id'])){
                        $this->UsuariosEmpresa->recursive = 0;
                        $salesperson = $this->UsuariosEmpresa->findById($empresa['salesperson_user_id'],['UsuariosEmpresa.name']);
                    }


                    $this->set('empresa', $empresa);
                    $this->set('secureClient',$secureCompanyParams);
                    $this->set('salesperson', $salesperson);

                    $this->set('empresa_dealer', $companyDealer);
                    $this->set('usuario_dealer', $userDealer);
                    $this->set('usuario_admin', $userAdmin);


                    $this->set('corporativo', $corporativo);
                    $this->set('distribuidor', $company['Distribuidor']);

                    Configure::load('settings');
                    $this->set('settings', Configure :: read('Settings'));

                    //$this->openCssToInclude[] = 'plugins/Assets/css/multiple-select/multiple-select';
                    //$this->set('openCssToInclude', $this->openCssToInclude);

                    //$this->openJsToInclude[] = 'plugins/Assets/js/multiple-select/multiple-select';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ajaxQ/ajaxq';
                    $this->set('openJsToInclude', $this->openJsToInclude);


                    //$this->cssToInclude[] = 'reset';
                    //$this->set('cssToInclude', $this->cssToInclude);
                    $this->jsToInclude[] = 'application/Companies/view';
                    $this->jsToInclude[] = 'scrolling';
                    $this->set('jsToInclude', $this->jsToInclude);
                    $response['success'] = true;
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

    public function append(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $selectedType = !empty($params) ? $params[0] : '';
        $error = true;
        if ($this->request->is('post')) {
            $this->ICountry->recursive = 2;

            $userProperties = $this->Core->getRegionCountryAndMarketForUserLogged();

            if($this->credentials['i_group_id']==IGroup::MASTER){
                $countries = $this->ICountry->find('all',[
                    'conditions' => ['deleted' => false],
                    'order'=>['ICountry.name ASC']
                    ]);
            }else if($this->credentials['i_group_id']==IGroup::MARKET_MANAGER) {
                $countries = $this->ICountry->find('all',['conditions'=>['ICountry.market_id'=>$userProperties['market']],'order'=>['ICountry.name ASC']]);
            }else{
                $countries = $this->ICountry->find('all',['conditions'=>['ICountry.id'=>$this->credentials['company_country_id']],'order'=>['ICountry.name ASC']]);
            }

            $countryList = $regionList = $territoryList = [];
            $countryList[] = '';
            if(!empty($countries)){
                foreach ($countries as $country){
                    $regionsCountry = $country['Regions'];
                    $country = $country['ICountry'];
                    $countryList[$country['id']] = $country['name'];
                    if(!empty($regionsCountry)){
                        foreach ($regionsCountry AS $regionCountry){
                            $skip = $this->credentials['i_group_id']<IGroup::COUNTRY_MANAGER && $regionCountry['id']!=$this->credentials['company_region_id'] ? true : false;

                            if(!$skip){
                                $territoriesRegions = $regionCountry['Territories'];
                                $regionList[] = ['id'=>$regionCountry['id'], 'country_id'=>$regionCountry['country_id'],'name'=>$regionCountry['name'],'code'=>$regionCountry['code']];
                                if(!empty($territoriesRegions)){
                                    foreach ($territoriesRegions AS $territoryRegion){
                                        $skipTerritory = $this->credentials['i_group_id']<IGroup::REGION_MANAGER && $territoryRegion['id']!=$this->credentials['company_territory_id'] ? true : false;
                                        if(!$skipTerritory){
                                            $territoryList[] = $territoryRegion;
                                        }
                                    }
                                }
                            }

                        }

                    }
                }
            }

            $dist_companies = [];
            if($this->credentials['role']!=UsuariosEmpresa::IS_DIST){
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
                $sharedDealers = $this->Core->getSharedDealersSalesperson();
                if(!empty($sharedDealers)){
                    $dist_companies = array_merge($dist_companies, $sharedDealers);
                }
            }else{
                $dist_companies = $this->Empresa->findByIdWithCorporate($this->credentials['id_empresa']);
                $dist_companies = array($dist_companies);
            }


            $regionUser = $this->Core->getRegion();//return Us, CA, MX, RASIL, AUSTRALIA...
            $corporates = $this->Corporativo->find('all', ['order'=>['Corporativo.name ASC']]);

            $salespersonSystem = $this->Core->getSalespersonForCurrentSession();

            $error = false;
            $this->set('selectedTypeCompany', $selectedType);
            $this->set('error',$error);
            $this->set('countries', $countryList);
            $this->set('regions', $regionList);
            $this->set('territories', $territoryList);
            $this->set('dealers', $dist_companies);
            $this->set('corporates', $corporates);
            $this->set('salespersons', $salespersonSystem);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function saveCompany(){
        $this->layout = false;
        $this->autoRender = false;

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => 0, 'typeCompany'=>'');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            set_error_handler('CoreComponent::warning_handler', E_WARNING);
            try {
                if ($data['type']=='' || $data['country'] == '' || ($data['type'] > 20 && ($data['region'] == '' || $data['territory'] == ''))) {
                    $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                    $response['code'] = 1;
                }else {
                
                    $corporate = $data['corporate'] == '' ? 0 : $data['corporate'];
                    $new_name_corporate = isset($data['new-corporate']) && trim($data['new-corporate'])!='' ? trim($data['new-corporate']) : '';

                    $name = $data['name'];
                    $city = $data['city'];
                    $direccion = $data['direccion'];
                    $state = $data['state'];
                    $country_id = $data['country'];
                    $group_bucket_id = $data['type'];
                    $type = $data['type'] == IGroup::CLIENT ? 'client' : 'distributor';
                    $salesperson = null;
                    $parent_dist = 0;
                    $active = 1;
                    $aprobado = 'SI';
                    $marca = isset($data['brand']) ? $data['brand'] : "";
                    $path_logo_empresa = $data['path_logo_empresa'];

                    if (isset($data['distributor'])) { //is client company
                        $distributor_id = $data['distributor'];
                        $parent_dist = $distributor_id;
                        $this->Empresa->recursive = 0;
                        $dealer = $this->Empresa->findById($distributor_id);
                        $territory_name = $dealer['Empresa']['region'];
                        $country_id = $dealer['Empresa']['i_country_id'];
                        $region_id = $dealer['Empresa']['region_id'];
                        $territory_id = $dealer['Empresa']['territory_id'];
                        $salesperson = isset($data['salesperson']) ? $data['salesperson'] : null;
                    } else { //is distributor
                        $territory_name = $data['name_territory'];
                        $territory_id = $data['territory'];
                        $region_id = $data['region'];
                        $salesperson = null;
                    }

                    $this->Empresa->recursive = 0;
                    $territoryProperties = $this->Empresa->find('first',['fields'=>['Empresa.i_market_id'],'conditions'=>['Empresa.type'=>'admin','Empresa.region'=>$territory_name]]);
                    $market_id = $territoryProperties['Empresa']['i_market_id'];

                    $corporativo = $corporate > 0 ? $corporate : $new_name_corporate;
                    $this->Corporativo->begin();
                    //mail("elalbertgd@gmail.com",'test', print_r([$corporativo, $type, $territory_name], true));
                    $id_corporativo = $this->Transactions->addCorporate($corporativo, $type, $territory_name);

                    if ($id_corporativo >= 0) {
                        $this->Empresa->begin();
                        $id_empresa = $this->Transactions->addCompany($corporate, $name, $city, $direccion, $logo = '', $type, $parent_dist, $territory_name, $active, $aprobado);
                        if ($id_empresa > 0) {
                            $this->Corporativo->commit();
                            $this->Empresa->commit();

                            $this->Empresa->id = $id_empresa;
                            $this->Empresa->saveField('salesperson_id', $salesperson);
                            $this->Empresa->saveField('salesperson_user_id', $salesperson);
                            $this->Empresa->saveField('state', $state);
                            $this->Empresa->saveField('country_id', $country_id);
                            $this->Empresa->saveField('i_country_id', $country_id);
                            $this->Empresa->saveField('group_bucket_id', $group_bucket_id);
                            $this->Empresa->saveField('region_id', $region_id);
                            $this->Empresa->saveField('territory_id', $territory_id);
                            $this->Empresa->saveField('id_corporativo', $id_corporativo);
                            $this->Empresa->saveField('i_market_id', $market_id);

                            if($id_corporativo>0){
                                $this->Corporativo->id = $id_corporativo;
                                $this->Corporativo->saveField('country_id', $country_id);
                            }

                            //Actualizamos las imagenes de usuario y empresa
                            $this->Transactions->updateCompanyLogo($path_logo_empresa, $id_empresa);
                            $response['msg'] = __('La empresa %s se guardo exitosamente', $name);
                            $response['typeCompany'] = $type;
                            $response['companyName'] = $name;
                            $response['success'] = true;

                        } else {
                            $this->Corporativo->rollback();
                            $response['msg'] = $id_empresa == 0 ? __('Ocurrio un error al registrar la empresa %s, intentelo nuevamente', array($name)) : __('La empresa %s ya se encuentra registrada', array($name));
                        }
                    } else {
                        $response['msg'] = __('El corporativo %s ya se encuentra registrado', $corporativo);
                    }
                }
            }catch (Exception $e) {
                $response['msg'] = $e->getMessage();
            }
            echo json_encode($response);

        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * add action for save a new colaborator for company
     */
    public function appendColaborator() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $error = true;
        if (!empty($params) && count($params) == 2) {
            $decodedItemParams = $this->Core->decodePairParams($params);
            if ($decodedItemParams['isOk']) {
                $company_received = $decodedItemParams['item_id'];
                $this->Empresa->recursive = 1;
                $company = $this->Empresa->findById($company_received);
                if(!empty($company)){
                    $error = false;

                    $order_industry = $this->language == IS_ESPANIOL ? 'name' : 'name';
                    $industrias = $this->TipoIndustria->find('all', array('order' => array($order_industry => 'ASC')));

                    $this->uses[] = 'CompanyRole';
                    $field_order = $this->Core->_app_language == IS_ESPANIOL ? 'name' : 'name_en';
                    $companyRoles = $this->CompanyRole->find('all',array('order'=>array($field_order => 'ASC'), 'fields' => array($field_order. ' AS name_role','id')));

                    $market_company = $company["Empresa"]["i_market_id"];
                    $isUsUser = $market_company==IMarket::Is_USCanada ? true : false;
                    /*$regionUserLogged = $this->Core->getRegion();
                    if(in_array($regionUserLogged, ["US","CA"])){
                        $isUsUser = true;
                    }*/

                    $this->set("company", $company);
                    $this->set('isUsUser', $isUsUser);
                    $this->set('companyRoles', $companyRoles);
                    $this->set('industrias', $industrias);
                }
            }
        }

        $this->set('error',$error);
        $this->Core->setTutorialSectionViewed(14);
    }

    public function saveColaborator(){
        $this->layout = false;
        $this->autoRender = false;

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => 0, 'typeCompany'=>'');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            set_error_handler('CoreComponent::warning_handler', E_WARNING);
            try {
                if ($data['i_group_id']=='' || $data['name'] == '' || $data['email'] == '' || $data['password']=='') {
                    $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                    $response['code'] = 1;
                }else{

                    $company_id = $data['company_id'];
                    $country_name = $data['country'];
                    $country_id = $data['country_id'];
                    $region = $data['region'];
                    $i_group_id = $data['i_group_id'];
                    $role = in_array($i_group_id, [IGroup::CLIENT_MANAGER, IGroup::RUBBER_DISTRIBUTOR, IGroup::DISTRIBUTOR_MANAGER]) ? UsuariosEmpresa::IS_MANAGER : $data['role'];
                    $group = in_array($i_group_id, [IGroup::CLIENT_MANAGER, IGroup::RUBBER_DISTRIBUTOR, IGroup::DISTRIBUTOR_MANAGER]) ? 50 : $i_group_id;

                    $is_us_company = $data['is_us_company'];
                    $name = $data['name'];
                    $email = $data['email'];
                    $phone = $data['telefono'];
                    $puesto = $data['puesto'];
                    $area = isset($data['area']) ? $data['area'] : '';
                    $industy = isset($data['industria']) ? $data['industria'] : '';

                    $employe_number = isset($data['no_empleado']) ? $data['no_empleado'] : '';
                    $business_unit = isset($data['unidad_negocio']) ? $data['unidad_negocio'] : '';

                    $encriptacion = _TYPECRYPT;
                    $password = $encriptacion($data['password']);
                    $password_nocrypt = $data['password'];
                    $path_logo_usuario = $data['path_logo_usuario'];
                    $username = $this->Core->createUniqueUsername($name);
                    $active = 1;
                    $aprobado = 'SI';
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $professionalType = 'normal';
                        /*$regionUserLogged = $this->Core->getRegion();
                        if(in_array($regionUserLogged, ["US","CA"])){
                            $professionalType = $params['professional_user'] == 1 ? 'pro' : 'lite';
                        }*/
                        if($is_us_company){
                            $professionalType = $params['professional_user'] == 1 ? 'pro' : 'lite';
                        }

                        $user_id = $this->UsuariosEmpresa->add($username, $password, $company_id, $name, $email, $phone, $puesto, $employe_number, $business_unit, $zona_d = '', $atendio_d = '', $area, $industy, $profile_img = '', $role, $group, $region, $country_name, $estado_usuario= '', $active, $aprobado);
                        $userCreated = $this->UsuariosEmpresa->findByUsername($username);
                        $user_id = !empty($userCreated) ? $userCreated['UsuariosEmpresa']['id'] : $user_id;
                        if ($user_id > 0) {
                            $this->UsuariosEmpresa->id = $user_id;
                            $this->UsuariosEmpresa->saveField('country_id', $country_id);
                            $this->UsuariosEmpresa->saveField('type', $professionalType);
                            $this->UsuariosEmpresa->saveField('i_group_id', $i_group_id);
                            $this->Transactions->updateUserLogo($path_logo_usuario, $company_id, $user_id);

                            //$this->Mail->sendAccessUserData($user_id, $password_nocrypt);
                            $response['msg'] = __('El usuario %s se guardo exitosamente', $name);
                            $response['success'] = true;
                        }else{
                            $response['msg'] = __('No se pudo insertar el usuario %s, intentelo nuevamente', $name);
                        }
                    } else {
                        $response['msg'] = __('El email %s no tiene un formato de correo valido', $email);
                    }

                }
            }catch (Exception $e) {
                $response['msg'] = $e->getMessage();
            }
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    

    public function suspendUnsuspend() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $companyForSuspend = $this->Empresa->findById($company_received);
                    if (!empty($companyForSuspend)) {
                        $active = 1;
                        $msg = __("La empresa fue reactivada exitosamente", true);
                        if ($companyForSuspend['Empresa']['active'] == 1) {
                            $active = 0;
                            $msg = __('La empresa fue suspendida exitosamente', true);
                        }

                        $companyForSuspend['Empresa']['active'] = $active;
                        if ($this->Empresa->save($companyForSuspend)) {
                            $response['msg'] = $msg;
                            $response['success'] = true;
                        } else {
                            $response['msg'] = __('Ocurrio un problema al procesar la operacion, intentelo nuevamente', true);
                            $response['success'] = false;
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
            $response = array('success' => false, 'msg' => '', 'deletedCompany' => 0);
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $companyForDelete = $this->Empresa->findById($company_received);
                    if (!empty($companyForDelete)) {
                        if ($company_received != $this->credentials['id_empresa']) {
                            $proceed = true;
                            if($companyForDelete['Empresa']['group_bucket_id']==IGroup::DISTRIBUTOR) { //if distributor, then process on transaction
                                $this->Empresa->recursive = 0;
                                $clientsDealer = $this->Empresa->findByParent($companyForDelete['Empresa']['id'], ['Empresa.id']); //get clients of dealer
                                if(!empty($clientsDealer)){
                                    $proceed = false;
                                    $response['success'] = true;
                                    $response['deletedCompany'] = $companyForDelete['Empresa']['id'];
                                    $response['assocMsg'] = __('Para completar la operacion es necesario asociar los clientes del distribuidor', true);
                                }
                            }

                            if($proceed){
                                $companyForDelete['Empresa']['deleted'] = 1;
                                if ($this->Empresa->save($companyForDelete)) {
                                    $response['msg'] = __("La empresa fue eliminada exitosamente", true);
                                    $response['success'] = true;
                                    /*
                                    * Guardamos log de navegacion
                                    */
                                    $this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, 'Empresa', $companyForDelete['Empresa']['name']);
                                } else {
                                    $response['msg'] = __('Ocurrio un problema al procesar la operacion, intentelo nuevamente', true);
                                    $response['success'] = false;
                                }
                            }

                        } else {
                            $response['msg'] = __('Imposible eliminar la empresa asociada a tu usuario', true);
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
    
    public function assocClientsDistributor(){
        if ($this->request->is('post')) {
            $params = $this->request->data; //get data
            $reassoc_dist = $params['deleted_company'];
            $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor',$this->global_filter_region);
            
            $this->set('distribuidores', $dist_companies);
            $this->set('deleted_dist',$reassoc_dist);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    
    public function processAssocClientsDistributor(){        
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            
            $deleted_distributor = $data['deleted_distributor'];
            $new_distributor = $data['distributor'];
            
            $distributor = $this->Empresa->findById($new_distributor);
            if(!empty($distributor)){
                $this->Empresa->id = $deleted_distributor;
                $this->Empresa->saveField('deleted', 1);

                /*
                * Guardamos log de navegacion
                */
                $this->Secure->saveBrowsingData(BrowsingLog::ELIMINACION, 'Empresa', $distributor['Empresa']['name']);

                $distributor = $distributor['Empresa'];
                $new_region = $distributor['region'];
                $this->Empresa->updateAll(array('parent' => "'$new_distributor'", 'region'=>"'$new_region'"),array('parent' => $deleted_distributor));
                //Automaticly exec db trigger for update region users companay
                $response['success'] = true;
                $response['msg'] = __('Los clientes han sido asociados al distribuidor %s',$distributor['name']);
            }else{
               $response['msg'] = __('Ocurrio un error al asociar el distribuidor');
            }
            $this->set('response', $response);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function shareCompanyWithSalesperson(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {
                        $response['success'] = true;
                        $this->set('company', $company);
                        //$allTerritories = $this->ITerritory->find('all');
                        //$allTerritories = Set::extract('/ITerritory/.', $allTerritories);

                        $this->set('salesperson_list',$this->Core->getSalesPersonList());
                        //$this->set('territory_list',$allTerritories);
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
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function setSalespersonForCompanies(){
        $this->layout = false;
        $this->autoRender = false;
        $this->uses[] = 'SalespersonCompany';
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {
                        $params = $this->request->data; //get data
                        parse_str($params['formdata'], $data);
                        $salesperson_selected = isset($data['salespersons']) ? $data['salespersons'] : [];

                        //get companies type client for current distributor for check selected salesperson (if salesperson_id is not in the $salesperson_selected, set null )
                        //$this->Empresa->find('all', ['conditions'=>['parent' => $company_received]]);

                        //get current salesperson shares of distributor
                        $currentSalespersonForDealer = $this->SalespersonCompany->find('list',['conditions'=>['company_id'=>$company_received], 'fields'=>['SalespersonCompany.user_sp_id']]);
                        //detect salesperson removed
                        $salespersonRemoved = array_diff($currentSalespersonForDealer, $salesperson_selected);

                        //set salesperson_id field to null for all client companies associated to current dealer and salesperson_id field within list above
                        if(!empty($salespersonRemoved)){
                            //remove salesperson for company dist
                            $this->Empresa->updateAll(['Empresa.salesperson_id' => 0, 'Empresa.salesperson_user_id' => null], ['Empresa.id' => $company_received, 'Empresa.salesperson_user_id' => $salespersonRemoved]);

                            //remove salesperson for all clients of distributor
                            $this->Empresa->updateAll(['Empresa.salesperson_id' => 0, 'Empresa.salesperson_user_id' => null], ['Empresa.parent' => $company_received, 'Empresa.salesperson_user_id' => $salespersonRemoved]);
                        }

                        //remove all previous associations for company received
                        $this->SalespersonCompany->deleteAll(['SalespersonCompany.company_id'=>$company_received]);
                        if(!empty($salesperson_selected)){
                            //save new assocs
                            $toSave = [];
                            foreach ($salesperson_selected AS $selected){
                                $toSave[] = ['SalespersonCompany'=>['company_id'=>$company_received, 'user_sp_id'=>$selected]];
                            }
                            $this->SalespersonCompany->saveMany($toSave);
                        }

                        $response['success'] = true;
                        $response['msg'] = __('Company was updated with the salesperson selected', true);

                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            echo json_encode($response);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function updateCorporate(){
        $this->layout = false;
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            if(isset($data['corporate_id'], $data['corporate_name']) && trim($data['corporate_name'])!=''){
                $corporate = $this->Corporativo->findById($data['corporate_id'], ['Corporativo.id']);
                if (!empty($corporate)) {
                    $this->Corporativo->id = $data['corporate_id'];
                    $this->Corporativo->saveField('name', $data['corporate_name']);
                    $response['corp_name'] = $data['corporate_name'];
                    $response['success'] = true;
                    $response['msg'] = __('La informacion se proceso correctamente', true);
                } else {
                    $response['msg'] = __('No se encontro la informacion asociada', true);
                }
            } else{
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            }

            echo json_encode($response);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function clientBucket(){

        $this->set('options_toolbar', 'items-client');

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) >= 2) {
            $decodedCompanyParams = $this->Core->decodePairParams($params);
            if ($decodedCompanyParams['isOk']) {
                $company_received = $decodedCompanyParams['item_id'];
                $company = $this->Empresa->findByIdWithCorporate($company_received);
                if (!empty($company)) {
                    $empresa = $company['Empresa'];
                    $corporativo = $company['Corporativo'];

                    $secureClientParams = $this->Core->encodeParams($empresa['id']);
                    $urlClient = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientParams['item_id'], $secureClientParams['digest']));


                    $conditions = ['deleted'=>0, 'client_id'=>$empresa['id'], 'parent_folder'=>0];
                    $sortData = isset($params[2]) ? $params[2] : 'updated_at';
                    $sortData = $sortData=='updated_at'? [$sortData => 'DESC']:[$sortData=>'ASC'];

                    $queryData = isset($params[3]) ? $params[3] : '';
                    if($queryData!=''){
                        $conditions = ['deleted'=>0, 'client_id'=>$empresa['id'], 'parent_folder'=>0,'name LIKE'=>"%".$queryData."%"];
                    }

                    $itemsClient = $this->ClientItem->find('all',[
                        'conditions'=>$conditions,
                        'order' => $sortData
                    ]);

                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';

                    $this->openCssToInclude[] = 'plugins/Assets/css/lightbox/jquery.lightbox';
                    $this->openJsToInclude[] = 'plugins/Assets/js/lightbox/jquery.lightbox.min';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Companies/clientBucket';
                    $this->set('jsToInclude', $this->jsToInclude);

                    $this->setJsVar('clientItemsReload', $this->_html->url('/customer/bucket/'.$secureClientParams['item_id'].'/'.$secureClientParams['digest']));

                    $this->set('companyLink', $urlClient);
                    $this->set("empresa",$empresa);
                    $this->set("items", $itemsClient);
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

    public function addItemClient() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedClientParams = $this->Core->decodePairParams($params);
                if ($decodedClientParams['isOk']) {
                    $client_received = $decodedClientParams['item_id'];
                    $type_item = 'file';

                    $response['success'] = true;
                    $this->set('type_item', $type_item);
                    $this->set('client_id', $client_received);
                    $this->set('secure_params', $params);
                    //$this->set('yearsfolder',$yearsfolder);
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

    public function saveItemClient() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedClientParams = $this->Core->decodePairParams($params);
                $decodedFolderParams = count($params) == 4 ? $this->Core->decodePairParams($params, 2) : array();
                if ($decodedClientParams['isOk']) {
                    $client_received = $decodedClientParams['item_id'];
                    $empresa = $this->Empresa->findById($client_received);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($empresa)) {
                        $empresa = $empresa['Empresa'];
                        if ($data['item_name'] != '' && $data['item_description'] != '' && $data['path_item'] != '') {
                            $itemSaved = 0;
                            switch ($data['item_type']) {

                                case Item::IMAGE:
                                    $itemSaved = $this->Transactions->addPictureForCompany($empresa['id'], 0, $data['path_item'], false ,$data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('La imagen fue agregada exitosamente', true);

                                break;
                                case Item::VIDEO:
                                    $itemSaved = $this->Transactions->addVideoForCompany($empresa['id'], 0, $data['path_item'], false ,$data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('El video fue agregado exitosamente', true);
                                break;

                                case Item::FOLDER:
                                    $this->ClientItem->save(array(
                                        'type' => Item::IS_CLIENT_FOLDER,
                                        'name' => $data['item_name'],
                                        'client_id' => $empresa['id'],
                                        'parent_folder' => $folder_id,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ));

                                    $itemSaved = $this->ClientItem->getInsertID();

                                    $response['msg'] = __('El folder fue agregado exitosamente', true);
                                    break;

                                case Item::REPORT:
                                    $itemSaved = $this->Transactions->addReportForCompany($empresa['id'], 0, $data['path_item'], $data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('El reporte fue agregado exitosamente', true);
                                break;
                                case Item::NOTE:

                                    $this->ClientItem->save([
                                        'type' => Item::IS_CLIENT_NOTE,
                                        'name' => $data['item_name'],
                                        'description' => $data['item_description'],
                                        'client_id' => $empresa['id'],
                                        'parent_folder' => $folder_id,
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                                    $itemSaved = $this->ClientItem->getInsertID();
                                    $response['msg'] = __('La nota fue agregada exitosamente', true);
                                    break;
                            }

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
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function importBsFromXls() {
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

    public function processImportExcel() {
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
                $decodedClientParams = $this->Core->decodePairParams($params);
                if ($decodedClientParams['isOk']) {
                    $client_received = $decodedClientParams['item_id'];
                    $empresa = $this->Empresa->findById($client_received);
                    $buoySystemsClientDB = $this->FolderApp->find('all', [
                        'fields' => ['FolderApp.name', 'BsMetadata.id'],
                        'conditions'=>[
                            'FolderApp.deleted' => 0,
                            'FolderApp.type' => 'buoy_system',
                            'FolderApp.client_id' => $client_received
                            ]
                        ]
                    );
                    
                    $buoySystemsClient = [];
                    if(!empty($buoySystemsClientDB)){
                        $buoySystemsClient = array_filter($buoySystemsClientDB, function($bsClient, $key) {
                            return !is_null($bsClient['BsMetadata']['id']);
                        }, ARRAY_FILTER_USE_BOTH);
                    }
                    $buoySystemsClient = !empty($buoySystemsClient) ? Set::extract('/FolderApp/.', $buoySystemsClient) : [];
                    $buoySystemsClient = array_map('strtolower', array_column($buoySystemsClient, 'name'));

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

                    $bsNamesFile = [];
                    $bsMetadata = [];
                    for($i=1;$i<count($fileData[1]);$i++){//Iteramos sobre las columnas
                        $bsData = [];
                        $emptyValues = 0;
                        foreach($fileData AS $rowIndex => $row){ //iteramos sobre cada una de las filas
                            $cellValue = $row[$i];
                            $bsData[] = $cellValue;
                            $emptyValues = is_null($cellValue) ? $emptyValues + 1 : $emptyValues;
                        }

                        $bsNamesFile[] = strtolower($bsData[0]);//para comparar los names
                        array_push($bsMetadata, $bsData );
                        if(count($bsData) == $emptyValues){//Si todos los valores del array son nulos
                            array_pop($bsMetadata);
                        }
                    }

                    $coincidences = array_intersect($bsNamesFile, $buoySystemsClient);
                    if(empty($coincidences) || $overwrite >= 0){ //if bss not exist
                        $request = "/clients/$client_received/buoysystems";
                    
                        $request = new CakeRequest($request);
                        $request->data('data',$bsMetadata);
                        $response = new CakeResponse();
                        $d = new Dispatcher();
                        $d->dispatch(
                            $request,
                            $response
                        );
                        $processedData = true;
                    }else{
                        $response['duplicated'] = true;
                        $response['msg'] = __('The buoy system: <b>[%s]</b> already contains metadata. <br> Do you want to overwrite it?', implode(', ', $coincidences));
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

    public function addFileClient() {
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

    public function saveFileClient() {
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedClientParams = $this->Core->decodePairParams($params);
                $decodedFolderParams = count($params) == 4 ? $this->Core->decodePairParams($params, 2) : array();
                if ($decodedClientParams['isOk']) {
                    $client_received = $decodedClientParams['item_id'];
                    $empresa = $this->Empresa->findById($client_received);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($empresa)) {
                        $empresa = $empresa['Empresa'];
                        if ($data['item_name'] != '' && $data['path_item'] != '') {
                            $itemSaved = 0;
                            $itemSaved = $this->Transactions->addFileForCompany($empresa['id'], 0, $data['path_item'], $data['item_name'], $folder_id);
                            $response['msg'] = __('El archivo fue agregado exitosamente', true);
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
                    $item = $this->ClientItem->findById($item_received);
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
        $this->layout = false;
        $this->autoRender = false;
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
                    $item = $this->ClientItem->findById($item_received);
                    if (!empty($item)) {
                        if ($data['item_name'] != '' && $data['item_description'] != '') {

                            $this->ClientItem->id = $item_received;
                            $this->ClientItem->saveField('name', $data['item_name']);
                            switch ($typeItem) {
                                case Item::IS_CLIENT_IMAGE: case Item::IS_CLIENT_VIDEO://nombre y descripcion
                                $this->ClientItem->saveField('description', $data['item_description']);
                                break;
                                case Item::NOTE://nombre y contenido
                                    $this->ClientItem->saveField('description', $data['item_description']);
                                    break;
                            }

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

    public function removeItem() {
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
                    $item = $this->ClientItem->findById($item_received);
                    if (!empty($item)) {

                        $this->ClientItem->id = $item_received;
                        $this->ClientItem->saveField('deleted', true);


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
            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function Item() {
        //$this->set('options_toolbar', 'items-folder');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 3) {
            $typeItem = $params[0];
            $decodedItemsParams = $this->Core->decodePairParams($params, 1);
            if ($decodedItemsParams['isOk']) {
                $item_received = $decodedItemsParams['item_id'];
                $item = $this->ClientItem->find('first', array('conditions' => array('id' => $item_received, 'deleted' => 0)));
                if (!empty($item)) {

                    $item = $item["ClientItem"];
                    $items_folder = array();

                    $parent_item_id = 0;
                    switch ($typeItem) {
                        case Item::IS_CLIENT_REPORT:
                            $this->response->file($item['path']);
                            $this->response->header('Content-Disposition', 'inline');
                            return $this->response;
                            break;
                        case Item::IS_CLIENT_FILE:
                            $this->response->file($item['path']);
                            $this->response->header('Content-Disposition', 'inline');
                            return $this->response;
                            break;

                        case Item::IS_CLIENT_VIDEO:
                            $this->openCssToInclude[] = 'plugins/Assets/css/videojs/video-js';
                            $this->openJsToInclude[] = 'plugins/Assets/js/videojs/video';
                            $this->set('openCssToInclude', $this->openCssToInclude);
                            $this->set('openJsToInclude', $this->openJsToInclude);
                            $this->setJsVar('swfPath', $this->site . 'plugins/Assets/js/videojs/video-js.swf');

                            $mp4File = $this->site . $item['path'] . '.mp4';
                            $this->setJsVar('pathVideo', $mp4File);

                            $this->set('pathVideo', $mp4File);
                            $parent_item_id = $item['parent_folder'] != 0 ? $item['parent_folder'] : $item['client_id'];
                            break;


                        case Item::IS_CLIENT_IMAGE:
                            $parent_item_id = $item['parent_folder'] != 0 ? $item['parent_folder'] : $item['client_id'];
                            break;

                        case Item::IS_CLIENT_FOLDER:
                            $this->set('options_toolbar', 'items-client');
                            $this->set('is_folder', true);
                            $this->openCssToInclude[] = 'plugins/Assets/css/lightbox/jquery.lightbox';
                            $this->openJsToInclude[] = 'plugins/Assets/js/lightbox/jquery.lightbox.min';
                            $this->set('openCssToInclude', $this->openCssToInclude);
                            $this->set('openJsToInclude', $this->openJsToInclude);

                            $secureFolderParams = $this->Core->encodeParams($item_received);
                            $this->setJsVar('refreshItemsFolderAx', $this->_html->url(array('controller' => 'Companies', 'action' => 'refreshItemsFolder', $this->usercode, $secureFolderParams['item_id'], $secureFolderParams['digest'])));
                            $parent_item_id = $item['client_id'];
                            break;
                    }



                    $secureParentItem = $this->Core->encodeParams($parent_item_id);
                    $secureItemConveyor = $this->Core->encodeParams($item_received);
                    $urlEditItem = $this->_html->url(array('controller' => 'Companies', 'action' => 'editItem', $typeItem, $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                    $urlRemoveItem = $this->_html->url(array('controller' => 'Companies', 'action' => 'remove', $typeItem, $secureItemConveyor['item_id'], $secureItemConveyor['digest']));

                    $urlReturnRemove = '';
                    if ($typeItem == Item::IS_CLIENT_FOLDER || $item['parent_folder'] == 0) {
                        $urlReturnRemove = $this->_html->url(array('controller' => 'Companies', 'action' => 'View', $secureParentItem['item_id'], $secureParentItem['digest']));
                    } else {
                        $urlReturnRemove = $this->_html->url(array('controller' => 'Companies', 'action' => 'Item', Item::FOLDER, $secureParentItem['item_id'], $secureParentItem['digest']));
                    }


                    $this->set('urlEditItem', $urlEditItem);
                    $this->set('urlRemoveItem', $urlRemoveItem);
                    $this->set('urlReturnRemove', $urlReturnRemove);


                    //Obtenemos los comentarios
                    //$comments_item = $this->Comment->getCommentsItemByType($item_received, $typeItem);
                    $comments_item = [];

                    $this->set('comments_item', $comments_item);
                    $this->set('type_item', $typeItem);

                    $this->set('item', $item);


                    $empresa = $this->Empresa->findById($item['client_id']);
                    $empresa = $empresa['Empresa'];
                    $secureClientConveyorParams = $this->Core->encodeParams($item['client_id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyLink', $urlClientConveyor);
                    $this->set('empresa', $empresa);


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

                    $this->jsToInclude[] = 'application/Companies/item_view';
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

    public function refreshItemsFolder() {
        $query = $sort = '';
        $folderItems = $transportador = array();
        $itemId = 0;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) >= 3) {
            if ($params[0] == $this->usercode) {

                $decodedFolderParams = $this->Core->decodePairParams($params, 1);
                if ($decodedFolderParams['isOk']) {
                    $folder_received = $decodedFolderParams['item_id'];
                    $itemId = $folder_received;
                    $folder = $this->ClientItem->findById($folder_received);
                    if (!empty($folder)) {

                        $conditions = ['deleted'=>0, 'parent_folder'=>$folder_received];
                        $sortData = isset($params[3]) ? $params[3] : 'updated_at';
                        $sortData = $sortData=='updated_at'? [$sortData => 'DESC']:[$sortData=>'ASC'];

                        $queryData = isset($params[4]) ? $params[4] : '';
                        if($queryData!=''){
                            $conditions = ['deleted'=>0, 'parent_folder'=>$folder_received,'name LIKE'=>"%".$queryData."%"];
                        }


                        $folderItems = $this->ClientItem->find('all',[
                                'conditions' => $conditions,
                                'order' => $sortData
                            ]
                        );
                        //$transportador = $this->Conveyor->findById($folder['Bucket']['parent_conveyor']);
                        //$transportador = $transportador['Conveyor'];
                        $this->set('folder', $folder['ClientItem']);
                    }
                }
            }
        }

        $this->set('item_id', $itemId);
        //$this->set('conveyor', $transportador);
        $this->set('items', $folderItems);
    }

}