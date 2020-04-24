<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file PremiumController.php
 *     Management of actions for premium section
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class PremiumController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }
    
    public function refreshTrackingConveyors() {
        $query = $sort = '';
        $rows = $desde = 0;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $sort = $params[1];
                $query = isset($params[2]) ? $params[2] : '';
                $query = $query == '-' ? '':$query;
                
                $rows = isset($params[3]) && $params[3]>0 ? $params[3] : 0;
                $desde = isset($params[4]) && $rows>0 ? $params[4] : 0;
            }
        }

        $filter_companies = $this->Session->read(parent::ASSOC_COMPANIES);
        $conveyors = $this->Core->getTrackingConveyorsUsingFilters($filter_companies, $query, $sort, $rows, $desde);

        $this->set('conveyors', $conveyors);
        $this->set('offset', $desde);
    }

    public function index(){        
        $this->set('options_toolbar', 'premium-section-hidden');
        
        $activeTab = 'training';
        $query = $sort = '';
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params)) {
            if ($params[0] == $this->usercode) {
                $sort = $params[1];
                $query = isset($params[2]) ? $params[2] : '';
            }
        }
        
        $filter_companies = $this->Core->getCompaniesFilterAccordingUserLogged();
        $this->Session->write(parent::ASSOC_COMPANIES, $filter_companies);
        
        $conveyors = $this->Core->getTrackingConveyorsUsingFilters($filter_companies, $query, $sort);
        $this->jsToInclude[] = 'application/Premium/action';
        $this->jsToInclude[] = 'scrolling';
        $this->set('jsToInclude', $this->jsToInclude);
        
        $autocompleteConveyors = $this->Core->initAutocompleteTrackingConveyors($conveyors);
        $this->setJsVar('autocompleteConveyors', $autocompleteConveyors);
        $this->setJsVar('totConveyors', count($conveyors));
        
        $this->set('active_tab', $activeTab);        
        $this->Core->setTutorialSectionViewed(2);
    }
    
    public function changeConveyorStatus() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $this->TrackingConveyor->id = $conveyor_received;
                    $this->TrackingConveyor->saveField('status', $data['new_status']);
                    $this->TrackingConveyor->saveField('actualizada', date('Y-m-d H:i:s'));
                    $response['success'] = true;
                    $response['conveyor_number'] = $this->TrackingConveyor->field('title');
                    $response['msg'] = __('El estatus de la banda ha sido actualizado', true);
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
    
    public function removeConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->TrackingConveyor->findById($item_received);
                    if (!empty($item)) {
                        $this->TrackingConveyor->id = $item_received;
                        $this->TrackingConveyor->saveField('eliminada', true);
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
    
    public function toggleShowConveyor(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->TrackingConveyor->findById($item_received);
                    if (!empty($item)) {
                        $visible = $item['TrackingConveyor']['visible_for_client'] == 1 ? 0 : 1;
                        $msg = $item['TrackingConveyor']['visible_for_client'] == 1 ? __('El rastreo de la banda fue desactivado para el cliente',true) : __('El rastreo de la banda fue activado para el cliente',true);
                        $this->TrackingConveyor->id = $item_received;
                        $this->TrackingConveyor->saveField('visible_for_client', $visible);
                        $response['msg'] = $msg;
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Error, el elemento a actualizar no fue encontrado', true);
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
    
    public function addConveyor(){
        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region);
        $this->set('distribuidores', $dist_companies);
    }
    
    public function saveConveyor() {
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            if ($data['titulo_transportador'] == '' || $data['tracking_code'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['code'] = 0; //Indice de la pestania en activar
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            } else {
                $client_id = $data['client_txt'];
                if ($client_id > 0) {
                    $conveyor_reg = array(
                        'company_id' => $client_id,
                        'title' => $data['titulo_transportador'], 
                        'tracking_code' => $data['tracking_code'],
                        'actualizada' => date('Y-m-d H:i:s')
                    );

                    if ($this->TrackingConveyor->save($conveyor_reg)) {
                        $conveyor_id = $this->TrackingConveyor->getInsertID();

                        $response['success'] = true;
                        $response['conveyor_number'] = $data['titulo_transportador'];
                        $response['msg'] = __('La banda ha sido guardada exitosamente', true);

                        /*
                         * Guardamos la notificacion *
                         * ************************** */
                        $this->Notifications->trackingConveyorSaved($conveyor_id);

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
    
    public function updateConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $conveyor = $this->TrackingConveyor->findById($item_received);
                    if (!empty($conveyor)) {
                        $response['success'] = true;                        
                        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region);
                        $this->set('conveyor', $conveyor);
                        $this->set('distribuidores', $dist_companies);
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
    
    public function processUpdateConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->TrackingConveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {
                        if ($data['titulo_transportador'] == '' || $data['tracking_code'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                        } else {
                            $client_id = $data['client_txt'];
                            if ($client_id > 0) {
                                $conveyor['TrackingConveyor']['title'] = $data['titulo_transportador'];
                                $conveyor['TrackingConveyor']['tracking_code'] = $data['tracking_code'];
                                $conveyor['TrackingConveyor']['actualizada'] = date('Y-m-d H:i:s');
                                
                                if ($this->TrackingConveyor->save($conveyor)) {
                                    $response['conveyor_number'] = $data['titulo_transportador'];
                                    $response['success'] = true;
                                    $response['msg'] = __('La banda ha sido actualizada exitosamente', true);
                                   
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

    public function monitoringSystem(){
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

    public function viewMonitoringCompany(){
        $this->set('title_for_layout', 'Monitoring System');
        date_default_timezone_set('Europe/Berlin');
        $this->set('options_toolbar', 'monitoring-company-section');
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();

        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedCompanyParams = $this->Core->decodePairParams($params);
            if ($decodedCompanyParams['isOk']) {
                $company_received = $decodedCompanyParams['item_id'];
                $company = $this->Empresa->findById($company_received);
                if (!empty($company)) {

                    $formatDate = $this->language == 'es' ? 'd/m/Y' : 'm/d/Y';
                    $fecha = date($formatDate);

                    $this->setJsVar('colorSensorInfoUrl', $this->_html->url(array('controller' => 'Premium', 'action' => 'getColorSensorData',$params[0],$params[1])));
                    $this->setJsVar('predictiveMaintenanceInfoUrl', $this->_html->url(array('controller' => 'Premium', 'action' => 'getMaintenanceData',$params[0],$params[1])));
                    $this->setJsVar('fillLevelInfoUrl', $this->_html->url(array('controller' => 'Premium', 'action' => 'getFillLevelData',$params[0],$params[1])));
                    $this->setJsVar('datePickerIcon', $this->site.'img/icon_date.png');
                    $this->jsToInclude[] = 'application/Premium/monitoring';
                    $this->set('jsToInclude', $this->jsToInclude);

                    $this->set('currentDate',$fecha);
                    $this->set('empresa',$company);
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

    function getFillLevelData(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '',
                'chart_data' => [
                'fill_level'=>[['0',0]],
                'measure_data'=>[
                    'maxPercent' => '100',
                    'headers'=>['',''],
                    'values'=>['',0],
                    'colors'=>[ ['color'=>'#000000'] ]
                ]
            ]);
            if (!empty($params) && count($params) >= 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                $data = $this->request->data; //get data
                $fecha = $data['date'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["date"]) : '0000-00-00';
                $this->uses[] = "FillLevelLog";
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {
                        $matches = $this->FillLevelLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $fecha."%"]]);
                        $response['chart_data']['fill_level'] = $this->Core->calcFillLevelWithData($matches);
                        $response['chart_data']['measure_data'] = $this->Core->calcMeasureData($matches);

                        $difHoursGermany = 8;
                        $current_date=$fecha.' '.date('H:i:s');
                        $current_date_germany = strtotime($current_date) + (3600*$difHoursGermany);
                        $response['chart_data']['min'] = $current_date_germany - (3600 * 6);
                        $response['chart_data']['max'] = $current_date_germany;


                        //$response['chart_data']['fill_level'] = ['%',10,10,10,]

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

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    function getMaintenanceData(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $data = $this->request->data; //get data
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '',
                'chart_data' => []
            );


            if (!empty($params) && count($params) >= 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);

                $fecha = $data['date'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["date"]) : '0000-00-00';
                $this->uses[] = "ColorLog";

                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {

                            $fecha_events = $fecha;
                            //$fecha_events = '2018-04-20';
                            $matchesEvents = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['color'=>'blue', 'event !=' => '','created_at LIKE' => $fecha_events."%"]]);
                            $response['chart_data']['events_day_data'] = $this->Core->calcEventsDayData($matchesEvents);
                            $matchesEvents = $this->ColorLog->find('all', ['order'=>'id DESC','conditions' => ['color'=>'blue', 'event !=' => '','created_at LIKE' => $fecha_events."%"]]);
                            $response['chart_data']['events_day_list'] = $this->Core->calcEventsDayList($matchesEvents);

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

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    function getColorSensorData(){

        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $data = $this->request->data; //get data
        if ($this->request->is('post')) {
            $is_overview_section = isset($data['section']) && $data['section']=='overview';
            $response = array('success' => false, 'msg' => '',
                'overview_data'=>['red_tons'=>0, 'green_tons'=>0, 'total_ppt_today'=>'0€', 'total_ppt_month'=>'0€', 'credit_today'=>'0€'],
                'chart_data' => [
                    'color_data'=>[
                        $is_overview_section ? ['Time','Σ green', 'Σ red']:['Time', 'Σ yellow', 'Σ blue', 'Σ green', 'Σ red'],
                        $is_overview_section ? ['0',0,0]:['0',0,0,0,0]
                    ],
                    'ppt_data'=>[['0',0]]
                ]
            );


            if (!empty($params) && count($params) >= 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);

                $fecha = $data['date'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["date"]) : '0000-00-00';
                $this->uses[] = "ColorLog";
                $this->uses[] = "FillLevelLog";
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {

                        $matches = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $fecha."%"]]);


                        $black=$blue=$green=$red=0;
                        $green_ppt = $red_ppt = 0;
                        $credit_red = $credit_green = 0;
                        $creditToday = [];
                        if(!empty($matches)){

                            $ppt_data = [];
                            $headers = $is_overview_section ? ['Time', 'Σ green', 'Σ red']:['Time', 'Σ yellow', 'Σ blue', 'Σ green', 'Σ red'];
                            $charData = ['color_data' => [$headers], 'ppt_data'=>$ppt_data];
                            foreach ($matches AS $colorLog){
                                $colorLog = $colorLog['ColorLog'];

                                $date = date("Y-m-d", $colorLog['id']);
                                $time = date("H:i:s", $colorLog['id']);
                                $time_normal = date("H:i", $colorLog['id']);
                                $closedHour = date('H:00', strtotime($time_normal));
                                if(!isset($creditToday[$closedHour])) {
                                    $credit_red = 0;
                                    $credit_green = 0;
                                }

                                switch ($colorLog['color']){
                                    case 'yellow': $black++; break;
                                    case 'blue': $blue++; break;
                                    case 'green': $green++; $green_ppt++;$credit_green++; break;
                                    case 'red': $red++; $red_ppt++;$credit_red++; break;
                                }

                                $creditToday[$closedHour] = ['red'=>$red,'green'=>$green];


                                $ppt_in_time = ($red_ppt*5) + ($green_ppt*12);
                                if($ppt_in_time>0){
                                    $nextHour = date('H:00', strtotime($time_normal) + 60 * 60);
                                }
                                if(isset($nextHour)){
                                    //$ppt_data[$nextHour] = [strtotime($date.' '.$nextHour), $ppt_in_time];
                                    if(empty($ppt_data)){
                                        $prevHour = date('H:00', strtotime($time_normal));
                                        $ppt_data[$prevHour] = [$prevHour, 0];
                                    }
                                    $ppt_data[$nextHour] = [$nextHour, $ppt_in_time];
                                }

                                $charData['color_data'][] = $is_overview_section ? [strtotime($date.' '.$time), $green, $red] : [strtotime($date.' '.$time), $black, $blue, $green, $red];
                            }


                            //$ppt_data['23:00'] = !isset($ppt_data['23:00']) ? ['23:00', $ppt_in_time] : $ppt_data['23:00'];

                            $ppt_cleared = [];
                            $ppt_data = $this->Core->fillMissingValuesInArrayTime($ppt_data);
                            foreach ($ppt_data AS $value){
                                $ppt_cleared[] = [strtotime($fecha.' '.$value[0]), $value[1]];
                            }

                            $lastItemColors = end($charData['color_data']);
                            $charData['color_data'][] = $is_overview_section ? [strtotime($fecha.' 23:59:59'),$lastItemColors[1],$lastItemColors[2]]:[strtotime($fecha.' 23:59:59'),$lastItemColors[1],$lastItemColors[2],$lastItemColors[3],$lastItemColors[4]];

                            $lastItemPPT = end($ppt_data);
                            $ppt_cleared[] = [strtotime($fecha.' 23:59:59'), $lastItemPPT[1]];

                            $charData['ppt_data'] = $ppt_cleared;
                            $response['chart_data'] = $charData;


                        }



                        //Calcular PPT Mensual si se requiere
                        $total_ppt_month = 0;
                        $creditTodayEuros = 0;
                        list($year, $month, $day) = explode('-',$fecha);
                        if($is_overview_section){

                            if(!empty($creditToday)){
                                foreach ($creditToday AS $hour => $calcs){
                                    $calculo = $calcs['red']+$calcs['green'];
                                    //var_dump($hour.'=>'.$calculo);echo '<br>';
                                    if($calculo>100){
                                        $calcEuros = ($calcs['red']*5)+($calcs['green']*12);
                                        $calcEuros = $calcEuros*0.2;
                                        $creditTodayEuros = $creditTodayEuros + $calcEuros;
                                        //var_dump($hour.'=>'.$calcEuros);
                                    }
                                }
                            }

                            $yearMonth = "$year-$month-";
                            $matchesMonth = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $yearMonth."%"]]);
                            $total_ppt_month = $this->Core->calcPPTInMonthRows($matchesMonth);

                            $matchesFillLevel = $this->FillLevelLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $fecha."%"]]);
                            $response['chart_data']['measure_data'] = $this->Core->calcMeasureData($matchesFillLevel);

                            $fecha_events = $fecha;
                            //$fecha_events = '2018-04-20';
                            $matchesEvents = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['color'=>'blue', 'event !=' => '','created_at LIKE' => $fecha_events."%"]]);
                            $response['chart_data']['events_day_data'] = $this->Core->calcEventsDayData($matchesEvents);
                            $matchesEvents = $this->ColorLog->find('all', ['order'=>'id DESC','conditions' => ['color'=>'blue', 'event !=' => '','created_at LIKE' => $fecha_events."%"]]);
                            $response['chart_data']['events_day_list'] = $this->Core->calcEventsDayList($matchesEvents);

                            $initLastWeekDate = date('Y-m-d', strtotime($fecha. ' - 6 days'));
                            $initLastWeekDate = $initLastWeekDate. ' 00:00:00';
                            $matchesLastWeek = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['created_at >=' => $initLastWeekDate]]);
                            $response['chart_data']['color_data_week'] = $this->Core->getLastWeekColorData($matchesLastWeek, $initLastWeekDate);
                            $response['chart_data']['color_data_week_ppt'] = $this->Core->getLastWeekColorDataPPT($matchesLastWeek, $initLastWeekDate);
                        }

                        /*
                        //date_default_timezone_set('Europe/Berlin');
                        $difHoursGermany = 1;
                        $current_date=date('Y-m-d H:i:s'); //$fecha.' '.date('H:i:s');
                        $current_date_germany = strtotime($current_date) + (3600*$difHoursGermany);
                        $response['chart_data']['min'] = $current_date_germany - (3600 * 6);
                        $response['chart_data']['max'] = $current_date_germany;*/


                        $total_ppt = ($red_ppt*5) + ($green_ppt*12);
                        $response['overview_data']['red_tons'] = $red_ppt;
                        $response['overview_data']['green_tons'] = $green_ppt;
                        $response['overview_data']['credit_today'] = '-'.number_format($creditTodayEuros,0,'',' ').'€';
                        $response['overview_data']['total_ppt_today'] = number_format($total_ppt,0,'',' ').'€';
                        $response['overview_data']['total_ppt_month'] = number_format($total_ppt_month,0,'',' ').'€';

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

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /*
    function getColorSensorData(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '','overview_data'=>['red_tons'=>0, 'green_tons'=>0, 'total_ppt_today'=>0, 'total_ppt_month'=>0],'chart_data' => ['color_data'=>[['0',0,0,0,0]],'ppt_data'=>[['0',0]]]);
            if (!empty($params) && count($params) >= 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                $data = $this->request->data; //get data
                $fecha = $data['date'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["date"]) : '0000-00-00';
                $this->uses[] = "ColorLog";
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {
                        $matches = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $fecha."%"]]);

                        //Calcular PPT Mensual si se requiere
                        $total_ppt_month = 0;
                        list($year, $month, $day) = explode('-',$fecha);
                        if(isset($data['section']) && $data['section']=='overview'){
                            $yearMonth = "$year-$month-";
                            $matchesMonth = $this->ColorLog->find('all', ['order'=>'id ASC','conditions' => ['created_at LIKE' => $yearMonth."%"]]);
                            $total_ppt_month = $this->Core->calcPPTInMonthRows($matchesMonth);
                        }


                        $black=$blue=$green=$red=0;
                        $green_ppt = $red_ppt = 0;
                        if(!empty($matches)){

                            $ppt_data = [];
                            $single_colors_data=[];
                            $charData = ['color_data' => [], 'ppt_data'=>$ppt_data];
                            foreach ($matches AS $colorLog){
                                $colorLog = $colorLog['ColorLog'];

                                $date = date("Y-m-d", $colorLog['id']);
                                $time = date("H:i:s", $colorLog['id']);
                                $time_normal = date("H:i", $colorLog['id']);
                                $time_index = str_replace(':','',$time);

                                switch ($colorLog['color']){
                                    case 'yellow': $black++; break;
                                    case 'blue': $blue++; break;
                                    case 'green': $green++; $green_ppt++; break;
                                    case 'red': $red++; $red_ppt++; break;
                                }


                                $nextHourCalc = date('H:00', strtotime($time_normal) + 60 * 60);
                                $single_colors_data[$nextHourCalc] = [$nextHourCalc, $black, $blue, $green, $red];

                                $ppt_in_time = ($red_ppt*5) + ($green_ppt*12);
                                if($ppt_in_time>0){
                                    $nextHour = date('H:00', strtotime($time_normal) + 60 * 60);
                                }
                                if(isset($nextHour)){
                                    $ppt_data[$nextHour] = [$nextHour, $ppt_in_time];
                                }


                                $charData['color_data'][] = [$colorLog['id'], $black, $blue, $green, $red];//[$time_index, $black, $blue, $green, $red];
                                //$charData['color_data'][] = [$time_index, $black, $blue, $green, $red];
                            }
                            $normalized_color_data = $this->Core->fillLastHoursColors($hours=6,$single_colors_data);
                            //var_dump($normalized_color_data);
                            //$normalize_ppt_data = $this->Core->fillMissingValuesInArrayTime($ppt_data);
                            $normalized_ppt_data = $this->Core->fillLastHours($hours=6,$ppt_data);


                            $charData['min'] = strtotime(date('Y-m-d H:i:s')) - (3600 * 6) ;
                            $charData['max'] = strtotime(date('Y-m-d H:i:s'));

                            //$charData['color_data'] = $normalized_color_data;
                            $charData['ppt_data'] = $normalized_ppt_data;
                            $response['chart_data'] = $charData;
                        }

                        $total_ppt = ($red_ppt*5) + ($green_ppt*12);
                        $response['overview_data']['red_tons'] = $red_ppt;
                        $response['overview_data']['green_tons'] = $green_ppt;
                        $response['overview_data']['credit_today'] = number_format(0,0,'',' ').'€';
                        $response['overview_data']['total_ppt_today'] = number_format($total_ppt,0,'',' ').'€';
                        $response['overview_data']['total_ppt_month'] = number_format($total_ppt_month,0,'',' ').'€';

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

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }*/
}
