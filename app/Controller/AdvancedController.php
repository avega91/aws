<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file AdvancedController.php
 *     Management of advanced functions
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class AdvancedController extends AppController {
    //public $components = array('Analytics');
    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'IPermissionGroup';
    }
    
    public function index() {
        $this->set('options_toolbar', 'advanced-dashboard');
        $this->uses[] = 'IGroup';
        $this->uses[] = 'IMarket';

        if(!in_array($this->credentials['username'], ['master_405D'])){
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
        
        $queryUser = '';
        $activeTab = 'security';
        if ($this->request->is('post')) {
            $params = $this->request->data; //get data
            $fecha_ini = $params['ini_date'];
            $fecha_fin = $params['end_date'];
            $periodo = $fecha_ini . ' - ' .$fecha_fin;

            $queryUser = isset($params['query_user']) ? $params['query_user'] : $queryUser;
            $activeTab = isset($params['active_tab']) && in_array($params['active_tab'], array('security','statistics','history')) ? $params['active_tab'] : $activeTab;
        }else{
            $formatDateX = $this->language == 'es' ? 'd-m-Y' : 'd-m-Y';
            $formatDate = $this->language == 'es' ? 'd/m/Y' : 'm/d/Y';
            $fecha_fin = date($formatDate);
            $fecha = new DateTime(date($formatDateX));
            $fecha->sub(new DateInterval('P7D'));
            $fecha_ini = $fecha->format($formatDate);
            $periodo = $fecha_ini . ' - ' .$fecha_fin;
        }
        
        $ini_query = $this->Core->transformDateLanguagetoMysqlFormat($fecha_ini);
        $end_query = $this->Core->transformDateLanguagetoMysqlFormat($fecha_fin);
        $start = $ini_query;
        $end = $end_query;
        /*$this->Analytics->setDateRange(date($start),date($end));
        $visits = $this->Analytics->getFullVisits();
        $avgDuration = $this->Analytics->getAvgSessionDuration();
        
        $byCountry = $this->Analytics->getTopStatisticsByType('country');
        $byBrowser = $this->Analytics->getTopStatisticsByType('browser');
        $byOs = $this->Analytics->getTopStatisticsByType('operatingSystem');*/

        $locking_logs = $this->LockingLog->findAllWithAssocInfo( $queryUser, $start, $end);
        $browsing_logs = $this->BrowsingLog->findAllWithAssocInfo( $queryUser, $start, $end);
        
        $client_companies = $this->Empresa->findByTypeWithTeamAndClients(UsuariosEmpresa::IS_CLIENT);       
        $dist_companies = $this->Empresa->findByTypeWithTeamAndClients(UsuariosEmpresa::IS_DIST);               
        $all_users = $this->UsuariosEmpresa->getTotalUsersByType(array('client', 'client_manager', 'distributor', 'distributor_manager', 'ruber_distributor', 'manager', 'admin', 'region_manager', 'country_manager', 'market_manager'));
        $all_conveyors = $this->Conveyor->getAllWithCompany();
        
        $activity_companies = $this->BrowsingLog->getActivityCompanies();
        
        $total_visitas_seccion = $this->Statistic->find('count', array('conditions' => array('section !=' => _SITE_ACCESS, 'date BETWEEN ? AND ?' => array($start, $end))));

        $black_list = [Statistic::GO_SITE, Statistic::NEW_CONVEYOR, Statistic::NEW_CUSTOMER, Statistic::NEW_READING_ULTRA, Statistic::POPULATE_TECHNICAL_DATA, Statistic::NEW_ITEM_CONVEYOR, Statistic::NEW_SAVING_STANDBY,Statistic::NEW_SAVING_APPROVED, Statistic::NEW_BELT_HISTORY];
        $visitas_por_seccion = $this->Statistic->find('all', array(
                            'conditions' => array('section NOT IN' => $black_list, 'date BETWEEN ? AND ?' => array($start, $end)),
                            'fields' => array('section', 'COUNT(section) AS visitas'),
                            'order' => array('visitas' => 'desc'),
                            'group' => 'section'
                             )
                        );


        $this->IGroup->recursive = 2;
        $groupPermissionsMarket = [];
        //Get all markets
        $this->IMarket->recursive = 0;
        $markets = $this->IMarket->find('all',['order'=>['IMarket.name'=>'ASC']]);
        $market_list = [];
        foreach ($markets AS $market){
            $market_id = $market['IMarket']['id'];
            $market_list[$market_id] = $market['IMarket']['name'];
            $this->IGroup->hasMany['PermissionsForGroup']['conditions'] = ['PermissionsForGroup.market_id'=>$market_id];
            $groupPermissionsMarket[$market_id] = $this->IGroup->find('all',['conditions'=>['NOT'=>['IGroup.id'=>[0]]],'order'=>['IGroup.id DESC']]);
        }
        $this->set('markets', $market_list);

        //$key = array_search(594, array_column($salespersonsSystem, 'id'));
        //var_dump($key);

        $this->jsToInclude[] = 'application/Advanced/action';
        $this->set('jsToInclude', $this->jsToInclude);

        $this->set('locking_log', $locking_logs);
        $this->set('browsing_log', $browsing_logs);
        $this->set('active_tab', $activeTab);
        
        $this->setJsVar('query',$queryUser);
        //$this->setJsVar('visitas', $visits);
        
        //$this->setJsVar('tituloPorPais', __('Visitas por pais (top 5)',true));
        //$this->setJsVar('visitas_por_pais', $byCountry);
        
        //$this->setJsVar('tituloPorBrowser', __('Acceso por navegador',true));
        //$this->setJsVar('visitas_por_browser', $byBrowser);
        
        //$this->setJsVar('tituloPorOs', __('Acceso por SO',true));
        //$this->setJsVar('visitas_por_os', $byOs);

        $visitas_seccion = array();
        $name_sections = array( Statistic::GO_CONVEYORS => __('Transportadores',true),
                                Statistic::GO_CLIENTS => __('Clientes',true),
                                Statistic::GO_TOOLS => __('Calculador',true),
                                Statistic::GO_NEWS => __('Noticias'),
                                Statistic::GO_HELP => __('Ayuda',true)
                            );
        if(!empty($visitas_por_seccion)){
            foreach ($visitas_por_seccion AS $visita){
                $seccion = $visita['Statistic'];
                $visitas = $visita[0];                
                $percent = round(($visitas['visitas']/$total_visitas_seccion)*100,2);
                $visitas_seccion[] = array($name_sections[$seccion['section']].' '.$visitas['visitas'], (int)$visitas['visitas'], $percent.'%');
            }
        }
        $this->setJsVar('tituloPorSeccion', __('Visitas por seccion',true));
        $this->setJsVar('visitas_por_seccion', $visitas_seccion);
        
        $this->set('actividad_empresas',$activity_companies);
        $this->set('total_bandas', count($all_conveyors));
        $this->set('total_usuarios', count($all_users));
        $this->set('total_clientes', count($client_companies));
        $this->set('total_distribuidores', count($dist_companies));
        //$this->set('visitas_totales', $visits['total'][1]);
        //$this->set('tiempo_promedio', $avgDuration);
        $this->set('groupPermissionsMarket', $groupPermissionsMarket);
        
        $this->setJsVar('datePickerIcon', $this->site.'img/icon_date.png');        
       $this->set('fecha_ini',$fecha_ini);
       $this->set('fecha_fin',$fecha_fin);
       $this->set('periodo',$periodo);
    }

    public function refreshSalespersonTable(){
        if ($this->request->is('post')) {
            $salespersonsSystem = $this->Core->getsalesPersonApp();
            $this->set('salespersonsApp', $salespersonsSystem);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function savePermissionsRole(){
        $this->layout = false;
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data;
            if(isset($params['group'], $params['permissions']) && !empty($params['permissions'])){
                $group = $params['group'];
                $permission = $params['permissions'];
                $toSave = [];
                foreach ($permission AS $permission){
                    //$this->IPermissionGroup->group_id
                    $toSave[] = ['IPermissionGroup'=>['id'=>$permission['id'],'permission'=>$permission['permission'], 'market_id'=>$permission['market']]];
                }
                $this->IPermissionGroup->saveMany($toSave);
                $response['msg'] = __('La informacion se proceso correctamente', true);
                $response['success'] = true;
            } else {
                $response['msg'] = __('Acceso no autorizado', true);
            }

            echo json_encode($response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

}
