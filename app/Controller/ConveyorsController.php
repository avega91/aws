<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ConveyorsController.php
 *     Management of actions for conveyors
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::import('Vendor', 'VideoEncoder', array('file' => 'VideoEncoder/VideoEncoder.php'));
App::import('Vendor', 'Dompdf', array('file' => 'Dompdf/dompdf_config.inc.php'));
App::uses('HttpSocket', 'Network/Http');
App::uses('IMarket', 'Model');


class ConveyorsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'UsConveyor';
        $this->uses[] = 'CompanyArea';
        $this->uses[] = 'CompanySubarea';
        $this->uses[] = "RecommendedBelt";


        if (!$this->Session->check(Statistic::GO_CONVEYORS)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_CONVEYORS);
            $this->Session->write(Statistic::GO_CONVEYORS, Statistic::GO_CONVEYORS);
        }

    }

    public function refreshConveyors() {
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
        //var_dump($rows);

        $filter_companies = $this->Session->read(parent::ASSOC_COMPANIES);
        $conveyors = $this->Core->getConveyorsUsingFilters($filter_companies, $query, $sort, $rows, $desde);

        if($clientId>0){
            $company = $this->Empresa->findById($clientId);
            $this->set('nameCompany', $company['Empresa']['name']);
        }

        $this->set('conveyors', $conveyors);
        $this->set('offset', $desde);
        $this->set('clientId', $clientId);
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

    /**
     * add action for add new conveyor
     */
    public function add() {

        $fieldOrderDesc = $this->Core->_app_language=="es" ? "titulo ASC" : "titulo_en ASC";
        $desc_materiales = $this->ConfigTransporter->getAllByDescIdSorted(ConfigTransporter::MATERIAL_DESCRIPTION, $fieldOrderDesc);

        $angulos_contacto = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ANGULO_CONTACTO);
        $tipos_tensor = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TENSOR_TYPE);
        $tipos_polea = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::POLEA_TYPE);

        $arcos_contacto = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ARCO_CONTACTO);
        $rodillos_config = array();
        $rodillos_config['diam_rodillos_ldc'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_DIAM_LDC);
        $rodillos_config['angle_rodillo_ldc'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_ANGLE_LDC);
        $rodillos_config['diam_rodillos_ldr'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_DIAM_LDR);
        $rodillos_config['angle_rodillo_ldr'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_ANGLE_LDR);


        $espesor_cubiertas = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CUBIERTA_DESGASTE, $orderById = true);
        $grado_material_transportado = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::GRADO_MAT_TRANSPORTADO);
        $condicion_alimentacion = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CONDICION_ALIMENTACION);
        $condicion_carga = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CONDICION_CARGA, $orderById = true, $sort="DESC");
        $frecuencia_carga = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::FRECUENCIA_CARGA);
        $tamanio_granular = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TAMANIO_GRANULAR);
        $tipo_densidad = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TIPO_DENSIDAD);
        $agresividad = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::AGRESIVIDAD, true);

        $oil_presence = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::PRESENCIA_ACEITE, $orderById = true);
        $conveyor_location = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::UBICACION_BANDA, $orderById = true);
        $conveyor_tamanio_material = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TAMANIO_MATERIAL, $orderById = true);

        $partes_artesa = array(
            array('ctransp' => array('id' => 1, 'titulo' => 1, 'titulo_en' => 1, 'mat_density' => 0)),
            array('ctransp' => array('id' => 2, 'titulo' => 2, 'titulo_en' => 2, 'mat_density' => 0)),
            array('ctransp' => array('id' => 3, 'titulo' => 3, 'titulo_en' => 3, 'mat_density' => 0)),
            array('ctransp' => array('id' => 5, 'titulo' => 5, 'titulo_en' => 5, 'mat_density' => 0)),
        );
        //array('ctransp' => array('id' => 4, 'titulo' => 4, 'titulo_en' => 4, 'mat_density' => 0)),

        $angulo_acanalamiento = array(
            array('ctransp' => array('id' => 20, 'titulo' => 20, 'titulo_en' => 20, 'mat_density' => 0)),
            array('ctransp' => array('id' => 35, 'titulo' => 35, 'titulo_en' => 35, 'mat_density' => 0)),
            array('ctransp' => array('id' => 45, 'titulo' => 45, 'titulo_en' => 45, 'mat_density' => 0))
        );

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

        $this->set('descripcion_materiales', $desc_materiales);
        $this->set('angulos_contacto', $angulos_contacto);
        $this->set('tipos_tensor', $tipos_tensor);
        $this->set('tipos_polea', $tipos_polea);
        $this->set('arcos_contacto', $arcos_contacto);
        $this->set('rodillos_config', $rodillos_config);

        $this->set('grado_material_transportado', $grado_material_transportado);
        $this->set('condicion_alimentacion', $condicion_alimentacion);

        $this->set('espesor_cubiertas', $espesor_cubiertas);

        $this->set('condicion_carga', $condicion_carga);
        $this->set('frecuencia_carga', $frecuencia_carga);
        $this->set('tamanio_granular', $tamanio_granular);
        $this->set('tipo_densidad', $tipo_densidad);
        $this->set('agresividad', $agresividad);

        $this->set('partes_artesa', $partes_artesa);
        $this->set('angulo_acanalamiento', $angulo_acanalamiento);

        $this->set('oil_presence', $oil_presence);
        $this->set('conveyor_location', $conveyor_location);
        $this->set('bulk_size', $conveyor_tamanio_material);

        $this->set('inst_belt_config', Configure :: read('Conveyor.installed_belt'));
        $this->set('conveyor_config', Configure :: read('Conveyor'));
        $this->Core->setTutorialSectionViewed(6);

        Configure::load('settings');
        $this->set('units_conveyor', Configure :: read('Settings.units_conveyor'));


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


        //Check region for display fields accord to region
        //if(!in_array($this->credentials['role'], array(UsuariosEmpresa::IS_MASTER))){
            $region = $this->Core->getRegion();

            //if(in_array($region, ["US","CA"])){
            if ($market_company==IMarket::Is_USCanada){
                $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                $this->render('add_us');
            }
        //}
    }

    public function save() {

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            if ($data['no_transportador'] == '' || $data['sel_perfil'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['code'] = 0; //Indice de la pestania en activar
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            } else {
                $client_id = $data['client_txt'];
                if ($client_id > 0) {

                    //values dropdowns
                    $dropdown_values = $data['data'];

                    $this->Empresa->recursive = 0;
                    $clientCompany = $this->Empresa->findById($client_id, ['i_country_id']);
                    $country_id = $clientCompany['Empresa']['i_country_id'];
                    $is_us_conveyor = in_array($country_id, [71, 44]) ? 1 : 0;
                    $meta_units = isset($data['data-field-units']) ? $data['data-field-units'] : "";
                    $belt_monitoring_system = isset($data['belt_monitoring_system']) && $data['belt_monitoring_system']!='' ? implode(',',$data['belt_monitoring_system']) : "";
                    $failure_modes = isset($data['failure_mode']) && $data['failure_mode']!='' ? implode(',',$data['failure_mode']) : "";


                    $fecha_instalacion = $data['fecha_instalacion'] != '' ? $this->Core->transformToMysqlDateFormat($data["fecha_instalacion"]) : '0000-00-00';
                    //$banda_aprobada = in_array($this->credentials['role'], array('admin', 'master')) ? 'SI' : 'NO';

                    $filledFields = array_filter($data, function($value) { return $value !== ''; });
                    $filledFields = count($filledFields) - 18;

                    $banda_aprobada = 'SI';
                    $conveyor_reg = array(
                        'id_company' => $client_id,
                        'is_us_conveyor' => $is_us_conveyor,
                        'meta_units' => utf8_decode($meta_units),
                        'numero' => $data['no_transportador'], 'perfil' => $data['sel_perfil'],
                        'trans_distancia_centros' => $data['distancia_centros'], 'trans_rpm_motor' => $data['rpm_motor'],
                        'trans_elevacion' => $data['elevacion'], 'trans_angulo_inclinacion' => $data['angulo_inclinacion'],
                        'trans_hp_motor' => $data['hp_motor'], 'trans_capacidad' => $data['capacidad'],
                        'trans_relacion_reductor' => $data['relacion_reductor'], 'trans_carga' => $data['porcentaje_carga'],
                        'trans_ubicacion' => $data['sel_ubicacion'],
                        'tensor_tipo' => $data['sel_tipo_tensor'], 'tensor_carrera' => $data['carrera'],
                        'tensor_peso_estimado' => $data['peso_estimado'], 'mat_descripcion' => $data['sel_desc_material'],
                        'mat_densidad' => $data['densidad_material'], 'mat_tam_terron' => $data['tamanio_terron'],
                        'mat_temperatura' => $data['temperatura'], 'mat_altura_caida' => $data['altura_caida'],
                        'mat_porcentaje_finos' => $data['porcentaje_finos'],
                        'mat_grado_mat_transportado' => $data['sel_desc_grado_mat'], 'mat_condicion_alimentacion' => $data['sel_desc_cond_alimentacion'],
                        'mat_condicion_carga' => $data['sel_condicion_carga'], 'mat_frecuencia_carga' => $data['sel_frecuencia_carga'],
                        'mat_tamanio_granular' => $data['sel_tamanio_granular'], 'mat_tipo_densidad' => $data['sel_tipo_densidad'],
                        'mat_agresividad' => $data['sel_agresividad'], 'mat_aceite' => $data['sel_oil_presence'], 'mat_tamanio' => $data['sel_tamanio_material'],
                        'banda_ancho' => $data['ancho_banda'],
                        'banda_tension' => $data['tension_banda'], 'banda_espesor_cubiertas' => $data['espesor_cubiertas'],
                        'id_espesor_cubierta_sup' => $data['espesor_cubierta_sup'], 'open_espesor_cubierta_sup' => $data['open_espesor_cubierta_sup'],
                        'id_espesor_cubierta_inf' => $data['espesor_cubierta_inf'], 'open_espesor_cubierta_inf' => $data['open_espesor_cubierta_inf'],
                        'banda_velocidad' => $data['velocidad_banda'], 'banda_fecha_instalacion' => $fecha_instalacion,
                        'banda_marca' => $data['marca_banda'], 'banda_desarrollo_total' => $data['desarrollo_banda'],
                        'banda_operacion' => $data['operacion_hrs'], 'polea_motriz' => $data['polea_motriz'],
                        'ancho_polea_motriz' => $data['ancho_polea_motriz'], 'polea_recubrimiento' => $data['recubrimiento_polea'],
                        'polea_arco_contacto' => $data['sel_arcos_contacto'], 'polea_cabeza' => $data['polea_cabeza'],
                        'ancho_pol_cabeza' => $data['ancho_polea_cabeza'], 'polea_cola' => $data['polea_cola'],
                        'ancho_pol_cola' => $data['ancho_polea_cola'], 'polea_contacto' => $data['polea_contacto'],
                        'ancho_pol_contacto' => $data['ancho_polea_contacto'], 'polea_doblez' => $data['polea_doblez'],
                        'ancho_pol_doblez' => $data['ancho_polea_doblez'], 'polea_tensora' => $data['polea_tensora'],
                        'ancho_pol_tensora' => $data['ancho_polea_tensora'], 'polea_uno_adicional' => $data['polea_adicional_1'],
                        'ancho_polea_uno_adicional' => $data['ancho_polea_adicional1'], 'polea_dos_adicional' => $data['polea_adicional_2'],
                        'ancho_pol_dos_adicional' => $data['ancho_polea_adicional2'], 'rod_diam_impacto' => $data['diam_impacto_rodillo'],
                        'rod_ang_impacto' => $data['impacto_rodillo_angle'], 'rod_diam_carga' => $data['sel_diam_rodillos_ldc'],
                        'rod_ang_carga' => $data['sel_angle_rodillos_ldc'], 'rod_diam_retorno' => $data['sel_diam_rodillos_ldr'],
                        'rod_ang_retorno' => $data['angulo_retorno_rodillo'], 'rod_espacio_ldc' => $data['espacio_LC_rodillo'],
                        'rod_espacio_ldr' => $data['espacio_LR_rodillo'], 'rod_partes_artesa' => $data['sel_partes_artesa'],
                        'rod_angulo_acanalamiento' => $data['sel_angulo_acanalamiento'],
                        'observaciones' => $data['observaciones'],
                        'creada' => date('Y-m-d H:i:s'), 'aprobada' => $banda_aprobada,
                        'filled_lang' => $this->Core->_app_language
                    );


                    //tab_installed_belt
                    $installed_belt_tab = [
                        'shell' => $dropdown_values['shell'],
                        'cord_diameter' => $data['cord_diameter'],
                        'number_cords' => $data['number_cords'],
                        'cord_pitch' => $data['cord_pitch'],
                        'plies_number' => $data['plies_number'],
                        'belt_family' => $data['belt_family'],
                        'used_belt_grade' => $data['used_belt_grade'],
                        'trade_name' => $data['trade_name'],
                        'damages' => $dropdown_values['damages'],
                        'splice_type' => $dropdown_values['splice_type'],
                        'splice_quantity' => $dropdown_values['splice_quantity'],
                        'splice_condition' => $dropdown_values['splice_condition'],
                        'history' => $dropdown_values['history'],
                        'last_replacement' => $dropdown_values['last_replacement'],
                        'reason_replacement' => $data['reason_replacement'],
                        'shore_hardness_a' => $data['shore_hardness_a'],
                        //'failure_mode' => $dropdown_values['failure_mode']
                        'failure_mode' => $failure_modes
                    ];

                    //tab_conveyor
                    $conveyor_tab = [
                        'number_stations' => $data['number_stations'],
                        'pipe_belt' => $dropdown_values['pipe_belt'],
                        'belt_turnover' => $data['belt_turnover'],
                        'direction_turnover' => $dropdown_values['direction_turnover'],
                        'ambient_conditions' => $dropdown_values['ambient_conditions'],
                        'min_temp' => $data['min_temp'],
                        'max_temp' => $data['max_temp'],
                        //'humidity' => $dropdown_values['humidity'],
                        //'sea_level' => $data['sea_level'],
                        'belt_monitoring_system' => $belt_monitoring_system,
                        'manufacturer' => $data['manufacturer'],
                        'housing' => $dropdown_values['housing'],
                        //'radius' => $data['radius'],
                        //'curves' => $dropdown_values['curves'],
                        'length_curve' => $data['length_curve'],
                        //'curve_int_angle' => $data['curve_int_angle'],
                        //'curve_ext_angle' => $data['curve_ext_angle'],
                        'friction_factor' => $dropdown_values['friction_factor'],
                        'length_factor' => $data['length_factor']
                    ];

                    //tab_idlers
                    $idlers_tab = [
                        'number_idler_impact' => $data['number_idler_impact'],
                        'number_idler_load' => $data['number_idler_load'],
                        'number_idler_return' => $data['number_idler_return'],
                        'part_troughing_load' => $dropdown_values['part_troughing_load'],
                        'general_condition' => $dropdown_values['general_condition'],
                        'stuck_idlers' => $dropdown_values['stuck_idlers'],
                        'offset_idlers' => $dropdown_values['offset_idlers'],
                        'misalignment_sensor_upper' => $dropdown_values['misalignment_sensor_upper'],
                        'misalignment_sensor_lower' => $dropdown_values['misalignment_sensor_lower'],
                        'open_stuck_idlers' => $data['open_stuck_idlers'],
                        'open_offset_idlers' => $data['open_offset_idlers'],
                        'open_misalignment_sensor_upper' => $data['open_misalignment_sensor_upper'],
                        'open_misalignment_sensor_lower' => $data['open_misalignment_sensor_lower']
                    ];

                    $pulleys_tab = [
                        'motriz_lagging_type' => $dropdown_values['motriz_lagging_type'],
                        'motriz_lagging_cover' => $data['motriz_lagging_cover'],
                        'motriz_surface_condition' => $dropdown_values['motriz_surface_condition'],
                        'brake_device' => $dropdown_values['brake_device'],
                        'head_lagging_type' => $dropdown_values['head_lagging_type'],
                        'head_surface_condition' => $dropdown_values['head_surface_condition'],
                        'tail_lagging_type' => $dropdown_values['tail_lagging_type'],
                        'tail_surface_condition' => $dropdown_values['tail_surface_condition'],
                        'contact_lagging_type' => $dropdown_values['contact_lagging_type'],
                        'contact_surface_condition' => $dropdown_values['contact_surface_condition'],
                        'fold_lagging_type' => $dropdown_values['fold_lagging_type'],
                        'fold_surface_condition' => $dropdown_values['fold_surface_condition'],
                        'tensioner_lagging_type' => $dropdown_values['tensioner_lagging_type'],
                        'tensioner_surface_condition' => $dropdown_values['tensioner_surface_condition']
                    ];

                    $transition_zone_tab = [
                        'flat_to_trough' => $data['flat_to_trough'],
                        //'flat_troughing_angle' => $data['flat_troughing_angle'],
                        'flat_pulley_lift' => $data['flat_pulley_lift'],
                        'pressure_outer_idlers' => $dropdown_values['pressure_outer_idlers'],
                        'trough_to_flat' => $data['trough_to_flat'],
                        //'troughflat_troughing_angle' => $data['troughflat_troughing_angle'],
                        'troughflat_pulley_lift' => $data['troughflat_pulley_lift'],
                        'material_guidance' => $dropdown_values['material_guidance']
                    ];

                    $remarks_tab = [
                        'maintenance_condition' => $dropdown_values['maintenance_condition'],
                        'overall_status' => $dropdown_values['overall_status'],
                        //'previous_belt_construction' => $data['previous_belt_construction'],
                        //'previous_belt_failure' => $data['previous_belt_failure'],
                    ];

                    if ($this->Conveyor->save($conveyor_reg)) {
                        $conveyor_id = $this->Conveyor->getInsertID();
                        //set id of saved conveyor
                        $installed_belt_tab['conveyor_id'] = $conveyor_id;
                        $conveyor_tab['conveyor_id'] = $conveyor_id;
                        $idlers_tab['conveyor_id'] = $conveyor_id;
                        $pulleys_tab['conveyor_id'] = $conveyor_id;
                        $transition_zone_tab['conveyor_id'] = $conveyor_id;
                        $remarks_tab['conveyor_id'] = $conveyor_id;


                        $this->TabInstalledBelt->save($installed_belt_tab);
                        $this->TabConveyor->save($conveyor_tab);
                        $this->TabIdler->save($idlers_tab);
                        $this->TabPulley->save($pulleys_tab);
                        $this->TabTransitionZone->save($transition_zone_tab);
                        $this->TabRemark->save($remarks_tab);

                        $cover_img = 0;
                        if ($data['path_logo_transportador'] != '') {
                            $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $conveyor_id, $data['path_logo_transportador']);
                        }
                        $conveyor_reg['id'] = $conveyor_id;
                        $conveyor_reg['cover_img'] = $cover_img;
                        $this->Conveyor->save($conveyor_reg);

                        $response['success'] = true;
                        $response['conveyor_number'] = $data['no_transportador'];
                        $response['msg'] = __('La banda transportadora ha sido guardada exitosamente', true);



                        // Guardamos la notificacion
                        $this->Notifications->conveyorSaved($conveyor_id);

                        // Guardamos log de navegacion
                        $this->Secure->saveBrowsingData(BrowsingLog::ALTA, Item::CONVEYOR, $data['no_transportador']);

                        //save score card statistic
                        $salespersonAssoc = $this->Core->getSalespersonIfExists($client_id);
                        if($salespersonAssoc>0){
                            $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_CONVEYOR);
                            $this->Statistic->create();  // initializes a new instance
                            $this->Secure->saveStatisticData($salespersonAssoc, Statistic::POPULATE_TECHNICAL_DATA, [], $filledFields);
                        }
                        /*
                        if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_CONVEYOR);
                        }*/

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

    public function processUpdate() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);
                    if (!empty($conveyor)) {

                        $this->uses[] = "ConveyorUnitChange";
                        if ($data['no_transportador'] == '' || $data['sel_perfil'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                        } else {
                            $client_id = $data['client_txt'];
                            if ($client_id > 0) {
                                $fecha_instalacion = $data['fecha_instalacion'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["fecha_instalacion"]) : '0000-00-00';
                                $date_belt_failed = $data['date_belt_failed'] != '' ? $this->Core->transformDateLanguagetoMysqlFormat($data["date_belt_failed"]) : '0000-00-00';
                                $banda_aprobada = in_array($this->credentials['role'], array('admin', 'master')) ? 'SI' : 'NO';

                                $meta_units = isset($data['data-field-units']) ? $data['data-field-units'] : "";
                                if($meta_units!='' && $meta_units!=$conveyor['Conveyor']['meta_units']){
                                    $logChangeUnitsRow = [
                                        'conveyor_id'=>$conveyor['Conveyor']['id'],
                                        'user_id' => $this->credentials['id'],
                                        'units_before' => $conveyor['Conveyor']['meta_units'],
                                        'units_after' => $meta_units
                                    ];
                                    $this->ConveyorUnitChange->save(['ConveyorUnitChange'=>$logChangeUnitsRow]);
                                    $conveyor['Conveyor']['meta_units'] = utf8_decode($meta_units);
                                }

                                //values dropdowns
                                $dropdown_values = $data['data'];

                                $marca_banda =  $data['marca_banda'];
                                $other_family =  $data['belt_family'];
                                $other_belt_compound = $data['used_belt_grade'];
                                $carcass = isset($dropdown_values['shell']) ? $dropdown_values['shell'] : null;
                                $tension = isset($data['tension_banda']) ? $data['tension_banda'] : "";
                                $plies = isset($data['plies_number']) ? $data['plies_number'] : null;
                                $width = isset($data['ancho_banda']) ? $data['ancho_banda'] : null;
                                $reason_replacement = $data['reason_replacement'];


                                $belt_monitoring_system = isset($data['belt_monitoring_system']) && $data['belt_monitoring_system']!='' ? implode(',',$data['belt_monitoring_system']) : "";
                                $failure_modes = isset($data['failure_mode']) && $data['failure_mode']!='' ? implode(',',$data['failure_mode']) : "";

                                $conveyor['Conveyor']['numero'] = $data['no_transportador'];
                                $conveyor['Conveyor']['perfil'] = $data['sel_perfil'];
                                $conveyor['Conveyor']['trans_distancia_centros'] = $data['distancia_centros'];
                                $conveyor['Conveyor']['trans_rpm_motor'] = $data['rpm_motor'];
                                $conveyor['Conveyor']['trans_elevacion'] = $data['elevacion'];
                                $conveyor['Conveyor']['trans_angulo_inclinacion'] = $data['angulo_inclinacion'];
                                $conveyor['Conveyor']['trans_hp_motor'] = $data['hp_motor'];
                                $conveyor['Conveyor']['trans_capacidad'] = $data['capacidad'];
                                $conveyor['Conveyor']['trans_relacion_reductor'] = $data['relacion_reductor'];
                                $conveyor['Conveyor']['trans_carga'] = $data['porcentaje_carga'];

                                $conveyor['Conveyor']['trans_ubicacion'] = $data['sel_ubicacion'];

                                $conveyor['Conveyor']['tensor_tipo'] = $data['sel_tipo_tensor'];
                                $conveyor['Conveyor']['tensor_carrera'] = $data['carrera'];
                                $conveyor['Conveyor']['tensor_peso_estimado'] = $data['peso_estimado'];
                                $conveyor['Conveyor']['mat_descripcion'] = $data['sel_desc_material'];
                                $conveyor['Conveyor']['mat_densidad'] = $data['densidad_material'];
                                $conveyor['Conveyor']['mat_tam_terron'] = $data['tamanio_terron'];
                                $conveyor['Conveyor']['mat_temperatura'] = $data['temperatura'];
                                $conveyor['Conveyor']['mat_altura_caida'] = $data['altura_caida'];
                                $conveyor['Conveyor']['mat_porcentaje_finos'] = $data['porcentaje_finos'];

                                $conveyor['Conveyor']['mat_grado_mat_transportado'] = $data['sel_desc_grado_mat'];
                                $conveyor['Conveyor']['mat_condicion_alimentacion'] = $data['sel_desc_cond_alimentacion'];

                                $conveyor['Conveyor']['mat_condicion_carga'] = $data['sel_condicion_carga'];
                                $conveyor['Conveyor']['mat_frecuencia_carga'] = $data['sel_frecuencia_carga'];
                                $conveyor['Conveyor']['mat_tamanio_granular'] = $data['sel_tamanio_granular'];
                                $conveyor['Conveyor']['mat_tipo_densidad'] = $data['sel_tipo_densidad'];
                                $conveyor['Conveyor']['mat_agresividad'] = $data['sel_agresividad'];

                                $conveyor['Conveyor']['mat_aceite'] = $data['sel_oil_presence'];
                                $conveyor['Conveyor']['mat_tamanio'] = $data['sel_tamanio_material'];

                                $conveyor['Conveyor']['banda_ancho'] = $width;
                                $conveyor['Conveyor']['banda_tension'] = $tension;
                                $conveyor['Conveyor']['banda_espesor_cubiertas'] = $data['espesor_cubiertas'];

                                $conveyor['Conveyor']['id_espesor_cubierta_sup'] = $data['espesor_cubierta_sup'];
                                $conveyor['Conveyor']['id_espesor_cubierta_inf'] = $data['espesor_cubierta_inf'];
                                $conveyor['Conveyor']['open_espesor_cubierta_sup'] = $data['open_espesor_cubierta_sup'];
                                $conveyor['Conveyor']['open_espesor_cubierta_inf'] = $data['open_espesor_cubierta_inf'];

                                $conveyor['Conveyor']['banda_velocidad'] = $data['velocidad_banda'];
                                $conveyor['Conveyor']['banda_fecha_instalacion'] = $fecha_instalacion;
                                $conveyor['Conveyor']['banda_marca'] = $marca_banda;
                                $conveyor['Conveyor']['banda_desarrollo_total'] = $data['desarrollo_banda'];
                                $conveyor['Conveyor']['banda_operacion'] = $data['operacion_hrs'];
                                $conveyor['Conveyor']['polea_motriz'] = $data['polea_motriz'];
                                $conveyor['Conveyor']['ancho_polea_motriz'] = $data['ancho_polea_motriz'];
                                $conveyor['Conveyor']['polea_recubrimiento'] = $data['recubrimiento_polea'];
                                $conveyor['Conveyor']['polea_arco_contacto'] = $data['sel_arcos_contacto'];
                                $conveyor['Conveyor']['polea_cabeza'] = $data['polea_cabeza'];
                                $conveyor['Conveyor']['ancho_pol_cabeza'] = $data['ancho_polea_cabeza'];
                                $conveyor['Conveyor']['polea_cola'] = $data['polea_cola'];
                                $conveyor['Conveyor']['ancho_pol_cola'] = $data['ancho_polea_cola'];
                                $conveyor['Conveyor']['polea_contacto'] = $data['polea_contacto'];
                                $conveyor['Conveyor']['ancho_pol_contacto'] = $data['ancho_polea_contacto'];
                                $conveyor['Conveyor']['polea_doblez'] = $data['polea_doblez'];
                                $conveyor['Conveyor']['ancho_pol_doblez'] = $data['ancho_polea_doblez'];
                                $conveyor['Conveyor']['polea_tensora'] = $data['polea_tensora'];
                                $conveyor['Conveyor']['ancho_pol_tensora'] = $data['ancho_polea_tensora'];
                                $conveyor['Conveyor']['polea_uno_adicional'] = $data['polea_adicional_1'];
                                $conveyor['Conveyor']['ancho_polea_uno_adicional'] = $data['ancho_polea_adicional1'];
                                $conveyor['Conveyor']['polea_dos_adicional'] = $data['polea_adicional_2'];
                                $conveyor['Conveyor']['ancho_pol_dos_adicional'] = $data['ancho_polea_adicional2'];
                                $conveyor['Conveyor']['rod_diam_impacto'] = $data['diam_impacto_rodillo'];
                                $conveyor['Conveyor']['rod_ang_impacto'] = $data['impacto_rodillo_angle'];
                                $conveyor['Conveyor']['rod_diam_carga'] = $data['sel_diam_rodillos_ldc'];
                                $conveyor['Conveyor']['rod_ang_carga'] = $data['sel_angle_rodillos_ldc'];
                                $conveyor['Conveyor']['rod_diam_retorno'] = $data['sel_diam_rodillos_ldr'];
                                $conveyor['Conveyor']['rod_ang_retorno'] = $data['angulo_retorno_rodillo'];
                                $conveyor['Conveyor']['rod_espacio_ldc'] = $data['espacio_LC_rodillo'];
                                $conveyor['Conveyor']['rod_espacio_ldr'] = $data['espacio_LR_rodillo'];
                                $conveyor['Conveyor']['rod_partes_artesa'] = $data['sel_partes_artesa'];
                                $conveyor['Conveyor']['rod_angulo_acanalamiento'] = $data['sel_angulo_acanalamiento'];

                                $conveyor['Conveyor']['observaciones'] = $data['observaciones'];
                                $conveyor['Conveyor']['tracking_code'] = $data['tracking_code'];
                                $conveyor['Conveyor']['filled_lang'] = $this->Core->_app_language;



                                //tab_installed_belt
                                $tab_installed_belt_id = $this->TabInstalledBelt->findByConveyorId($conveyor_received);
                                $tab_installed_belt_id = empty($tab_installed_belt_id) ? null : $tab_installed_belt_id['TabInstalledBelt']['id'];
                                $installed_belt_tab = [
                                    'id' => $tab_installed_belt_id,
                                    'conveyor_id' => $conveyor_received,
                                    'shell' => $carcass,
                                    'cord_diameter' => $data['cord_diameter'],
                                    'number_cords' => $data['number_cords'],
                                    'cord_pitch' => $data['cord_pitch'],
                                    'plies_number' => $plies,
                                    'belt_family' => $other_family,
                                    'used_belt_grade' => $other_belt_compound,
                                    'trade_name' => $data['trade_name'],
                                    'damages' => $dropdown_values['damages'],
                                    'splice_type' => $dropdown_values['splice_type'],
                                    'splice_quantity' => $dropdown_values['splice_quantity'],
                                    'splice_condition' => $dropdown_values['splice_condition'],
                                    'history' => $dropdown_values['history'],
                                    'last_replacement' => $dropdown_values['last_replacement'],
                                    'reason_replacement' => $reason_replacement,
                                    'shore_hardness_a' => $data['shore_hardness_a'],
                                    //'failure_mode' => $dropdown_values['failure_mode']
                                    'failure_mode' => $failure_modes,
                                    'date_belt_failed' => $date_belt_failed
                                ];

                                //tab_conveyor
                                $tab_conveyor_id = $this->TabConveyor->findByConveyorId($conveyor_received);
                                $tab_conveyor_id = empty($tab_conveyor_id) ? null : $tab_conveyor_id['TabConveyor']['id'];
                                $conveyor_tab = [
                                    'id' => $tab_conveyor_id,
                                    'conveyor_id' => $conveyor_received,
                                    'number_stations' => $data['number_stations'],
                                    'pipe_belt' => $dropdown_values['pipe_belt'],
                                    'belt_turnover' => $data['belt_turnover'],
                                    'direction_turnover' => $dropdown_values['direction_turnover'],
                                    'ambient_conditions' => $dropdown_values['ambient_conditions'],
                                    'min_temp' => $data['min_temp'],
                                    'max_temp' => $data['max_temp'],
                                    //'humidity' => $dropdown_values['humidity'],
                                    //'sea_level' => $data['sea_level'],
                                    'belt_monitoring_system' => $belt_monitoring_system,
                                    'manufacturer' => $data['manufacturer'],
                                    'housing' => $dropdown_values['housing'],
                                    //'radius' => $data['radius'],
                                    //'curves' => $dropdown_values['curves'],
                                    'length_curve' => $data['length_curve'],
                                    //'curve_int_angle' => $data['curve_int_angle'],
                                    //'curve_ext_angle' => $data['curve_ext_angle'],
                                    'friction_factor' => $dropdown_values['friction_factor'],
                                    'length_factor' => $data['length_factor']
                                ];

                                //tab_idlers
                                $tab_idlers_id = $this->TabIdler->findByConveyorId($conveyor_received);
                                $tab_idlers_id = empty($tab_idlers_id) ? null : $tab_idlers_id['TabIdler']['id'];
                                $idlers_tab = [
                                    'id' => $tab_idlers_id,
                                    'conveyor_id' => $conveyor_received,
                                    'number_idler_impact' => $data['number_idler_impact'],
                                    'number_idler_load' => $data['number_idler_load'],
                                    'number_idler_return' => $data['number_idler_return'],
                                    'part_troughing_load' => $dropdown_values['part_troughing_load'],
                                    'general_condition' => $dropdown_values['general_condition'],
                                    'stuck_idlers' => $dropdown_values['stuck_idlers'],
                                    'offset_idlers' => $dropdown_values['offset_idlers'],
                                    'misalignment_sensor_upper' => $dropdown_values['misalignment_sensor_upper'],
                                    'misalignment_sensor_lower' => $dropdown_values['misalignment_sensor_lower'],
                                    'open_stuck_idlers' => $data['open_stuck_idlers'],
                                    'open_offset_idlers' => $data['open_offset_idlers'],
                                    'open_misalignment_sensor_upper' => $data['open_misalignment_sensor_upper'],
                                    'open_misalignment_sensor_lower' => $data['open_misalignment_sensor_lower']
                                ];

                                $tab_pulleys_id = $this->TabPulley->findByConveyorId($conveyor_received);
                                $tab_pulleys_id = empty($tab_pulleys_id) ? null : $tab_pulleys_id['TabPulley']['id'];
                                $pulleys_tab = [
                                    'id' => $tab_pulleys_id,
                                    'conveyor_id' => $conveyor_received,
                                    'motriz_lagging_type' => $dropdown_values['motriz_lagging_type'],
                                    'motriz_lagging_cover' => $data['motriz_lagging_cover'],
                                    'motriz_surface_condition' => $dropdown_values['motriz_surface_condition'],
                                    'brake_device' => $dropdown_values['brake_device'],
                                    'head_lagging_type' => $dropdown_values['head_lagging_type'],
                                    'head_surface_condition' => $dropdown_values['head_surface_condition'],
                                    'tail_lagging_type' => $dropdown_values['tail_lagging_type'],
                                    'tail_surface_condition' => $dropdown_values['tail_surface_condition'],
                                    'contact_lagging_type' => $dropdown_values['contact_lagging_type'],
                                    'contact_surface_condition' => $dropdown_values['contact_surface_condition'],
                                    'fold_lagging_type' => $dropdown_values['fold_lagging_type'],
                                    'fold_surface_condition' => $dropdown_values['fold_surface_condition'],
                                    'tensioner_lagging_type' => $dropdown_values['tensioner_lagging_type'],
                                    'tensioner_surface_condition' => $dropdown_values['tensioner_surface_condition']
                                ];

                                $tab_transition_id = $this->TabTransitionZone->findByConveyorId($conveyor_received);
                                $tab_transition_id = empty($tab_transition_id) ? null : $tab_transition_id['TabTransitionZone']['id'];
                                $transition_zone_tab = [
                                    'id' => $tab_transition_id,
                                    'conveyor_id' => $conveyor_received,
                                    'flat_to_trough' => $data['flat_to_trough'],
                                    //'flat_troughing_angle' => $data['flat_troughing_angle'],
                                    'flat_pulley_lift' => $data['flat_pulley_lift'],
                                    'pressure_outer_idlers' => $dropdown_values['pressure_outer_idlers'],
                                    'trough_to_flat' => $data['trough_to_flat'],
                                    //'troughflat_troughing_angle' => $data['troughflat_troughing_angle'],
                                    'troughflat_pulley_lift' => $data['troughflat_pulley_lift'],
                                    'material_guidance' => $dropdown_values['material_guidance']
                                ];

                                $remarks_id = $this->TabRemark->findByConveyorId($conveyor_received);
                                $remarks_id = empty($remarks_id) ? null : $remarks_id['TabRemark']['id'];
                                $remarks_tab = [
                                    'id' => $remarks_id,
                                    'conveyor_id' => $conveyor_received,
                                    'maintenance_condition' => $dropdown_values['maintenance_condition'],
                                    'overall_status' => $dropdown_values['overall_status'],
                                    //'previous_belt_construction' => $data['previous_belt_construction'],
                                    //'previous_belt_failure' => $data['previous_belt_failure'],
                                ];

                                /*
                                $remarks_tab = $this->TabRemark->findByConveyorId($conveyor_received);
                                if(!empty($remarks_tab)){
                                    $remarks_tab['TabRemark']
                                }*/

                                if ($this->Conveyor->save($conveyor)) {
                                    $cover_img = $conveyor['Conveyor']['cover_img'];
                                    if ($data['path_logo_transportador'] != '') {
                                        $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $conveyor_received, $data['path_logo_transportador']);
                                    }

                                    $conveyor['Conveyor']['cover_img'] = $cover_img;
                                    $this->Conveyor->save($conveyor);

                                    $response['conveyor_number'] = $data['no_transportador'];
                                    $response['success'] = true;
                                    $response['msg'] = __('La banda transportadora ha sido actualizada exitosamente', true);

                                    $this->TabInstalledBelt->save($installed_belt_tab);
                                    $this->TabConveyor->save($conveyor_tab);
                                    $this->TabIdler->save($idlers_tab);
                                    $this->TabPulley->save($pulleys_tab);
                                    $this->TabTransitionZone->save($transition_zone_tab);
                                    $this->TabRemark->save($remarks_tab);


                                     // Guardamos la notificacion
                                    $this->Notifications->conveyorUpdated($conveyor['Conveyor']['id']);
                                    //Guardamos log de navegacion
                                    $this->Secure->saveBrowsingData(BrowsingLog::ACTUALIZACION, Item::CONVEYOR, $conveyor['Conveyor']['numero']);
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

    public function saveUs() {

        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            if ($data['no_transportador'] == '' || $data['sel_perfil'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['code'] = 0; //Indice de la pestania en activar
                $response['msg'] = __('Please fill all required fields', true);
            } else {
                $client_id = $data['client_txt'];

                if ($client_id > 0) {
                    $install_date = $data['installation_date'] != '' ? $this->Core->transformToMysqlDateFormatUs($data["installation_date"]) : '0000-00-00';
                    $date_belt_failed = $data['date_belt_failed'] != '' ? $this->Core->transformToMysqlDateFormatUs($data["date_belt_failed"]) : '0000-00-00';
                    $failure_mode = isset($data['failure_mode']) && $data['failure_mode']!='' ? implode(',',$data['failure_mode']) : "";
                    $meta_units = isset($data['data-field-units']) ? $data['data-field-units'] : "";

                    $filledFields = array_filter($data, function($value) { return $value !== ''; });
                    $filledFields = count($filledFields) - 18;

                    $conveyor_reg = array(
                        'id_company' => $client_id,
                        'numero' => $data['no_transportador'],
                        'perfil' => $data['sel_perfil'],
                        'is_us_conveyor' => 1,
                        'meta_units' => utf8_decode($meta_units),
                        'creada' => date('Y-m-d H:i:s')
                    );

                    $installed_belt_tab = [
                        'belt_manufacturer' => isset($data['belt_manufacturer']) ? $data['belt_manufacturer'] : null,
                        'open_belt_manufacturer' => isset($data['open_belt_manufacturer']) ? $data['open_belt_manufacturer'] : null,
                        'belt_family' => isset($data['belt_family']) ? $data['belt_family'] : null,
                        'open_belt_family' => isset($data['open_belt_family']) ? $data['open_belt_family'] : null,
                        'belt_compound' => isset($data['belt_compound']) ? $data['belt_compound'] : null,
                        'open_belt_compound' => isset($data['open_belt_compound']) ? $data['open_belt_compound'] : null,
                        'carcass' => isset($data['carcass']) ? $data['carcass'] : null,
                        'tension_unit' => isset($data['tension_unit']) ? $data['tension_unit'] : null,
                        'tension' => isset($data['tension']) ? $data['tension'] : null,
                        'open_tension' => isset($data['open_tension']) ? $data['open_tension'] : null,
                        'plies' => isset($data['plies']) ? $data['plies'] : null,
                        'width' => isset($data['width']) ? $data['width'] : null,
                        'other_width' => isset($data['other_width']) ? $data['other_width'] : null,
                        'top_cover' => isset($data['top_cover']) ? $data['top_cover'] : null,
                        'pulley_cover' => isset($data['pulley_cover']) ? $data['pulley_cover'] : null,
                        'other_special' => isset($data['other_special']) ? $data['other_special'] : null,
                        'other_special_data' => isset($data['other_special_data']) ? $data['other_special_data'] : null,
                        'installation_date' => $install_date,
                        'belt_length_install' => isset($data['belt_length_install']) ? $data['belt_length_install'] : null,
                        'splice_type' => isset($data['splice_type']) ? $data['splice_type'] : null,
                        'splice_quantity' => isset($data['splice_quantity']) ? $data['splice_quantity'] : null,
                        'splice_condition' => isset($data['splice_condition']) ? $data['splice_condition'] : null,
                        'existing_damage_belt' => isset($data['existing_damage_belt']) ? $data['existing_damage_belt'] : null,
                        'failure_mode' => $failure_mode,
                        'durometer_failed' => isset($data['durometer_failed']) ? $data['durometer_failed'] : null,
                        'date_belt_failed' => $date_belt_failed
                    ];

                    $material_tab = [
                        'material' => isset($data['material_desc']) ? $data['material_desc'] : null,
                        'other_material' => isset($data['other_material']) ? $data['other_material'] : null,
                        'material_density' => isset($data['material_density']) ? $data['material_density'] : null,
                        'lump_size' => isset($data['lump_size']) ? $data['lump_size'] : null,
                        'percent_fines' => isset($data['percent_fines']) ? $data['percent_fines'] : null,
                        'max_temp' => isset($data['max_temp']) ? $data['max_temp'] : null,
                        'min_temp' => isset($data['min_temp']) ? $data['min_temp'] : null,
                        'chute_drop' => isset($data['chute_drop']) ? $data['chute_drop'] : null,
                        'oil_presence' => isset($data['oil_presence']) ? $data['oil_presence'] : null
                    ];

                    $wear_life_tab = [
                        'operating_hours_year' => isset($data['operating_hours_year']) ? $data['operating_hours_year'] : null,
                        'tons_per_year' => isset($data['tons_per_year']) ? $data['tons_per_year'] : null,
                        'feed_angle' => isset($data['feed_angle']) ? $data['feed_angle'] : null,
                        'chute_angle' => isset($data['chute_angle']) ? $data['chute_angle'] : null,
                        'belt_incline_angle' => isset($data['belt_incline_angle']) ? $data['belt_incline_angle'] : null
                    ];

                    //tab_conveyor
                    $conveyor_tab = [
                        'center_to_center' => isset($data['center_to_center']) ? $data['center_to_center'] : null,
                        'lift' => isset($data['lift']) ? $data['lift'] : null,
                        'tons_per_hour' => isset($data['tons_per_hour']) ? $data['tons_per_hour'] : null,
                        'belt_speed' => isset($data['belt_speed']) ? $data['belt_speed'] : null,
                        'takeup_type' => isset($data['takeup_type']) ? $data['takeup_type'] : null,
                        'counterweight' => isset($data['counterweight']) ? $data['counterweight'] : null,
                        'takeup_travel' => isset($data['takeup_travel']) ? $data['takeup_travel'] : null,
                        'carry_side_angle' => isset($data['carry_side_angle']) ? $data['carry_side_angle'] : null,
                        'carry_side_diameter' => isset($data['carry_side_diameter']) ? $data['carry_side_diameter'] : null,
                        'carry_side_space' => isset($data['carry_side_space']) ? $data['carry_side_space'] : null,
                        'return_side_angle' => isset($data['return_side_angle']) ? $data['return_side_angle'] : null,
                        'return_side_diameter' => isset($data['return_side_diameter']) ? $data['return_side_diameter'] : null,
                        'return_side_space' => isset($data['return_side_space']) ? $data['return_side_space'] : null,
                        'drive_pulley_power' => isset($data['drive_pulley_power']) ? $data['drive_pulley_power'] : null,
                        'drive_pulley_wrap_angle' => isset($data['drive_pulley_wrap_angle']) ? $data['drive_pulley_wrap_angle'] : null,
                        'drive_pulley_surface' => isset($data['drive_pulley_surface']) ? $data['drive_pulley_surface'] : null,
                        'drive_pulley_diameter' => isset($data['drive_pulley_diameter']) ? $data['drive_pulley_diameter'] : null,
                        'head_pulley_diameter' => isset($data['head_pulley_diameter']) ? $data['head_pulley_diameter'] : null,
                        'takeup_pulley_diameter' => isset($data['takeup_pulley_diameter']) ? $data['takeup_pulley_diameter'] : null,
                        'tail_pulley_diameter' => isset($data['tail_pulley_diameter']) ? $data['tail_pulley_diameter'] : null,
                        'head_transition' => isset($data['head_transition']) ? $data['head_transition'] : null,
                        'tail_transition' => isset($data['tail_transition']) ? $data['tail_transition'] : null,
                        'type_trough_transitions' => isset($data['type_trough_transitions']) ? $data['type_trough_transitions'] : null,
                        'with_turnovers' => isset($data['with_turnovers']) ? $data['with_turnovers'] : null,
                        'turnover_length' => isset($data['turnover_length']) ? $data['turnover_length'] : null,
                        'number_stations' => isset($data['number_stations']) ? $data['number_stations'] : null,
                        'gear_ratio' => isset($data['gear_ratio']) ? $data['gear_ratio'] : null,
                        'drive_frecuency' => isset($data['drive_frecuency']) ? $data['drive_frecuency'] : null,
                        'conveyor_angle' => isset($data['conveyor_angle']) ? $data['conveyor_angle'] : null,
                        'percent_load' => isset($data['percent_load']) ? $data['percent_load'] : null,
                        'location' => isset($data['location']) ? $data['location'] : null,
                        'pipe_belt' => isset($data['pipe_belt']) ? $data['pipe_belt'] : null,
                        'direction_turnover' => isset($data['direction_turnover']) ? $data['direction_turnover'] : null,
                        'ambient_conditions' => isset($data['ambient_conditions']) ? $data['ambient_conditions'] : null,
                        'humidity' => isset($data['humidity']) ? $data['humidity'] : null,
                        'sea_level' => isset($data['sea_level']) ? $data['sea_level'] : null,
                        'housing' => isset($data['housing']) ? $data['housing'] : null,
                        'friction_factor' => isset($data['friction_factor']) ? $data['friction_factor'] : null
                    ];


                    $idlers_tab = [
                        'impact_diameter' => isset($data['impact_diameter']) ? $data['impact_diameter'] : null,
                        'number_impact_idlers' => isset($data['number_impact_idlers']) ? $data['number_impact_idlers'] : null,
                        'carry_side_idlers_number' => isset($data['carry_side_idlers_number']) ? $data['carry_side_idlers_number'] : null,
                        'return_side_idlers_number' => isset($data['return_side_idlers_number']) ? $data['return_side_idlers_number'] : null,
                        'part_troughing_load' => isset($data['part_troughing_load']) ? $data['part_troughing_load'] : null,
                        'part_troughing_return' => isset($data['part_troughing_return']) ? $data['part_troughing_return'] : null,
                        'impact_angle' => isset($data['impact_angle']) ? $data['impact_angle'] : null,
                        'general_condition' => isset($data['general_condition']) ? $data['general_condition'] : null,
                        'stuck_idlers' => isset($data['stuck_idlers']) ? $data['stuck_idlers'] : null,
                        'misalignment_sensor_upper' => isset($data['misalignment_sensor_upper']) ? $data['misalignment_sensor_upper'] : null,
                        'misalignment_sensor_lower' => isset($data['misalignment_sensor_lower']) ? $data['misalignment_sensor_lower'] : null
                    ];

                    $pulleys_tab = [
                        'drive_pulley_width' => isset($data['drive_pulley_width']) ? $data['drive_pulley_width'] : null,
                        'lagging_thickness' => isset($data['lagging_thickness']) ? $data['lagging_thickness'] : null,
                        'motriz_surface_condition' => isset($data['motriz_surface_condition']) ? $data['motriz_surface_condition'] : null,
                        'brake_device' => isset($data['brake_device']) ? $data['brake_device'] : null,
                        'head_pulley_width' => isset($data['head_pulley_width']) ? $data['head_pulley_width'] : null,
                        'head_lagging_type' => isset($data['head_lagging_type']) ? $data['head_lagging_type'] : null,
                        'head_surface_condition' => isset($data['head_surface_condition']) ? $data['head_surface_condition'] : null,
                        'tail_pulley_width' => isset($data['tail_pulley_width']) ? $data['tail_pulley_width'] : null,
                        'tail_lagging_type' => isset($data['tail_lagging_type']) ? $data['tail_lagging_type'] : null,
                        'tail_surface_condition' => isset($data['tail_surface_condition']) ? $data['tail_surface_condition'] : null,
                        'snub_pulley_diameter' => isset($data['snub_pulley_diameter']) ? $data['snub_pulley_diameter'] : null,
                        'snub_pulley_width' => isset($data['snub_pulley_width']) ? $data['snub_pulley_width'] : null,
                        'snub_lagging_type' => isset($data['snub_lagging_type']) ? $data['snub_lagging_type'] : null,
                        'snub_surface_condition' => isset($data['snub_surface_condition']) ? $data['snub_surface_condition'] : null,
                        'bend_pulley_diameter' => isset($data['bend_pulley_diameter']) ? $data['bend_pulley_diameter'] : null,
                        'bend_pulley_width' => isset($data['bend_pulley_width']) ? $data['bend_pulley_width'] : null,
                        'bend_lagging_type' => isset($data['bend_lagging_type']) ? $data['bend_lagging_type'] : null,
                        'bend_surface_condition' => isset($data['bend_surface_condition']) ? $data['bend_surface_condition'] : null,
                        'takeup_pulley_width' => isset($data['takeup_pulley_width']) ? $data['takeup_pulley_width'] : null,
                        'takeup_lagging_type' => isset($data['takeup_lagging_type']) ? $data['takeup_lagging_type'] : null,
                        'takeup_surface_condition' => isset($data['takeup_surface_condition']) ? $data['takeup_surface_condition'] : null,
                        'add1_pulley_diameter' => isset($data['add1_pulley_diameter']) ? $data['add1_pulley_diameter'] : null,
                        'add1_pulley_width' => isset($data['add1_pulley_width']) ? $data['add1_pulley_width'] : null,
                        'add2_pulley_diameter' => isset($data['add2_pulley_diameter']) ? $data['add2_pulley_diameter'] : null,
                        'add2_pulley_width' => isset($data['add2_pulley_width']) ? $data['add2_pulley_width'] : null
                    ];

                    $transition_zone_tab = [
                        'tail_pulley_lift' => isset($data['tail_pulley_lift']) ? $data['tail_pulley_lift'] : null,
                        'pressure_outer_idlers' => isset($data['pressure_outer_idlers']) ? $data['pressure_outer_idlers'] : null,
                        'head_pulley_lift' => isset($data['head_pulley_lift']) ? $data['head_pulley_lift'] : null,
                        'material_guidance' => isset($data['material_guidance']) ? $data['material_guidance'] : null
                    ];

                    $remarks_tab = [
                        'maintenance_condition' => isset($data['maintenance_condition']) ? $data['maintenance_condition'] : null,
                        'overall_status' => isset($data['overall_status']) ? $data['overall_status'] : null,
                        'remarks' => isset($data['remarks']) ? $data['remarks'] : null
                    ];

                    $this->uses[] = 'UsTabConveyor';
                    $this->uses[] = 'UsTabIdler';
                    $this->uses[] = 'UsTabInstalledBelt';
                    $this->uses[] = 'UsTabMaterial';
                    $this->uses[] = 'UsTabPulley';
                    $this->uses[] = 'UsTabRemark';
                    $this->uses[] = 'UsTabTransitionZone';
                    $this->uses[] = 'UsTabWearLife';

                    if ($this->Conveyor->save($conveyor_reg)) {
                        $conveyor_id = $this->Conveyor->getInsertID();

                        //set id of saved conveyor
                        $installed_belt_tab['conveyor_id'] = $conveyor_id;
                        $material_tab['conveyor_id'] = $conveyor_id;
                        $wear_life_tab['conveyor_id'] = $conveyor_id;
                        $conveyor_tab['conveyor_id'] = $conveyor_id;
                        $idlers_tab['conveyor_id'] = $conveyor_id;
                        $pulleys_tab['conveyor_id'] = $conveyor_id;
                        $transition_zone_tab['conveyor_id'] = $conveyor_id;
                        $remarks_tab['conveyor_id'] = $conveyor_id;


                        $this->UsTabInstalledBelt->save($installed_belt_tab);
                        $this->UsTabMaterial->save($material_tab);
                        $this->UsTabWearLife->save($wear_life_tab);
                        $this->UsTabConveyor->save($conveyor_tab);
                        $this->UsTabIdler->save($idlers_tab);
                        $this->UsTabPulley->save($pulleys_tab);
                        $this->UsTabTransitionZone->save($transition_zone_tab);
                        $this->UsTabRemark->save($remarks_tab);

                        $cover_img = 0;
                        if ($data['path_logo_transportador'] != '') {
                            $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $conveyor_id, $data['path_logo_transportador']);
                        }
                        $conveyor_reg['id'] = $conveyor_id;
                        $conveyor_reg['cover_img'] = $cover_img;
                        $this->Conveyor->save($conveyor_reg);

                        $response['success'] = true;
                        $response['conveyor_number'] = $data['no_transportador'];
                        $response['msg'] = __('La banda transportadora ha sido guardada exitosamente', true);



                        // Guardamos la notificacion
                        $this->Notifications->conveyorSaved($conveyor_id);

                        // Guardamos log de navegacion
                        $this->Secure->saveBrowsingData(BrowsingLog::ALTA, Item::CONVEYOR, $data['no_transportador']);

                        //save score card statistic
                        $salespersonAssoc = $this->Core->getSalespersonIfExists($client_id);
                        if($salespersonAssoc>0){
                            $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_CONVEYOR);
                            $this->Statistic->create();  // initializes a new instance
                            $this->Secure->saveStatisticData($salespersonAssoc, Statistic::POPULATE_TECHNICAL_DATA, [], $filledFields);
                        }
                        /*
                        if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_CONVEYOR);
                            $this->Statistic->create();  // initializes a new instance
                            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::POPULATE_TECHNICAL_DATA, [], $filledFields);
                        }*/

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

    public function processUpdateUs() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->UsConveyor->findFullById($conveyor_received);
                    $this->uses[] = "ConveyorUnitChange";
                    if (!empty($conveyor)) {
                        if ($data['no_transportador'] == '' || $data['sel_perfil'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                        } else {
                            $client_id = $data['client_txt'];
                            if ($client_id > 0) {
                                $install_date = $data['installation_date'] != '' ? $this->Core->transformToMysqlDateFormatUs($data["installation_date"]) : '0000-00-00';
                                $date_belt_failed = $data['date_belt_failed'] != '' ? $this->Core->transformToMysqlDateFormatUs($data["date_belt_failed"]) : '0000-00-00';
                                $failure_mode = isset($data['failure_mode']) && $data['failure_mode']!='' ? implode(',',$data['failure_mode']) : "";

                                $conveyor['Conveyor']['numero'] = $data['no_transportador'];
                                $conveyor['Conveyor']['perfil'] = $data['sel_perfil'];
                                $meta_units = isset($data['data-field-units']) ? $data['data-field-units'] : "";
                                if($meta_units!='' && $meta_units!=$conveyor['Conveyor']['meta_units']){
                                    $logChangeUnitsRow = [
                                        'conveyor_id'=>$conveyor['Conveyor']['id'],
                                        'user_id' => $this->credentials['id'],
                                        'units_before' => $conveyor['Conveyor']['meta_units'],
                                        'units_after' => $meta_units
                                    ];
                                    $this->ConveyorUnitChange->save(['ConveyorUnitChange'=>$logChangeUnitsRow]);
                                    $conveyor['Conveyor']['meta_units'] = utf8_decode($meta_units);
                                }

                                $this->uses[] = 'UsTabConveyor';
                                $this->uses[] = 'UsTabIdler';
                                $this->uses[] = 'UsTabInstalledBelt';
                                $this->uses[] = 'UsTabMaterial';
                                $this->uses[] = 'UsTabPulley';
                                $this->uses[] = 'UsTabRemark';
                                $this->uses[] = 'UsTabTransitionZone';
                                $this->uses[] = 'UsTabWearLife';


                                $manufacturer = isset($data['belt_manufacturer']) ? $data['belt_manufacturer'] : null;
                                $open_belt_manufacturer = isset($data['open_belt_manufacturer']) ? $data['open_belt_manufacturer'] : null;
                                $family = isset($data['belt_family']) ? $data['belt_family'] : null;
                                $open_belt_family = isset($data['open_belt_family']) ? $data['open_belt_family'] : null;
                                $belt_compound = isset($data['belt_compound']) ? $data['belt_compound'] : null;
                                $open_belt_compound = isset($data['open_belt_compound']) ? $data['open_belt_compound'] : null;
                                $carcass = isset($data['carcass']) ? $data['carcass'] : null;
                                $tension_unit = isset($data['tension_unit']) ? $data['tension_unit'] : null;
                                $tension = isset($data['tension']) ? $data['tension'] : null;
                                $open_tension = isset($data['open_tension']) ? $data['open_tension'] : null;
                                $plies = isset($data['plies']) ? $data['plies'] : null;
                                $width = isset($data['width']) ? $data['width'] : null;
                                $other_width = isset($data['other_width']) ? $data['other_width'] : null;
                                $top_cover = isset($data['top_cover']) ? $data['top_cover'] : null;
                                $top_cover_metric = 0; //depende de la unidad selecciona
                                $pulley_cover = isset($data['pulley_cover']) ? $data['pulley_cover'] : null;
                                $pulley_cover_metric = 0; //depende de la unidad seleccionada
                                $other_special = isset($data['other_special']) ? $data['other_special'] : null;
                                $other_special_data = isset($data['other_special_data']) ? $data['other_special_data'] : null;
                                $durometer_failed = isset($data['durometer_failed']) ? $data['durometer_failed'] : null;
                                $existing_damage_belt = isset($data['existing_damage_belt']) ? $data['existing_damage_belt'] : null;




                                $history_export = false;

                                if($history_export){
                                    $manufacturer = $family = $belt_compound = null;
                                    $open_belt_family = $open_belt_compound = "";
                                    $carcass = $tension_unit = $tension = $plies = $width = $other_width = null;
                                    $top_cover = $pulley_cover = $other_special = null;
                                    $existing_damage_belt = $failure_mode = $durometer_failed = null;
                                    $install_date = "0000-00-00";
                                    unset($data['belt_length_install'], $data['splice_type'],$data['splice_quantity'],$data['splice_condition']);

                                }

                                //tab_installed_belt
                                $tab_installed_belt_id = $this->UsTabInstalledBelt->findByConveyorId($conveyor_received);
                                $tab_installed_belt_id = empty($tab_installed_belt_id) ? null : $tab_installed_belt_id['UsTabInstalledBelt']['id'];
                                $installed_belt_tab = [
                                    'id' => $tab_installed_belt_id,
                                    'belt_manufacturer' => $manufacturer,
                                    'open_belt_manufacturer' => $open_belt_manufacturer,
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

                                //tab_material
                                $tab_material_id = $this->UsTabMaterial->findByConveyorId($conveyor_received);
                                $tab_material_id = empty($tab_material_id) ? null : $tab_material_id['UsTabMaterial']['id'];
                                $material_tab = [
                                    'id' => $tab_material_id,
                                    'material' => isset($data['material_desc']) ? $data['material_desc'] : null,
                                    'other_material' => isset($data['other_material']) ? $data['other_material'] : null,
                                    'material_density' => isset($data['material_density']) ? $data['material_density'] : null,
                                    'lump_size' => isset($data['lump_size']) ? $data['lump_size'] : null,
                                    'percent_fines' => isset($data['percent_fines']) ? $data['percent_fines'] : null,
                                    'max_temp' => isset($data['max_temp']) ? $data['max_temp'] : null,
                                    'min_temp' => isset($data['min_temp']) ? $data['min_temp'] : null,
                                    'chute_drop' => isset($data['chute_drop']) ? $data['chute_drop'] : null,
                                    'oil_presence' => isset($data['oil_presence']) ? $data['oil_presence'] : null
                                ];


                                //tab_material
                                $tab_wearlife_id = $this->UsTabWearLife->findByConveyorId($conveyor_received);
                                $tab_wearlife_id = empty($tab_wearlife_id) ? null : $tab_wearlife_id['UsTabWearLife']['id'];
                                $wear_life_tab = [
                                    'id' => $tab_wearlife_id,
                                    'operating_hours_year' => isset($data['operating_hours_year']) ? $data['operating_hours_year'] : null,
                                    'tons_per_year' => isset($data['tons_per_year']) ? $data['tons_per_year'] : null,
                                    'feed_angle' => isset($data['feed_angle']) ? $data['feed_angle'] : null,
                                    'chute_angle' => isset($data['chute_angle']) ? $data['chute_angle'] : null,
                                    'belt_incline_angle' => isset($data['belt_incline_angle']) ? $data['belt_incline_angle'] : null
                                ];



                                //tab_conveyor
                                $tab_conveyor_id = $this->UsTabConveyor->findByConveyorId($conveyor_received);
                                $tab_conveyor_id = empty($tab_conveyor_id) ? null : $tab_conveyor_id['UsTabConveyor']['id'];
                                $conveyor_tab = [
                                    'id' => $tab_conveyor_id,
                                    'center_to_center' => isset($data['center_to_center']) ? $data['center_to_center'] : null,
                                    'lift' => isset($data['lift']) ? $data['lift'] : null,
                                    'tons_per_hour' => isset($data['tons_per_hour']) ? $data['tons_per_hour'] : null,
                                    'belt_speed' => isset($data['belt_speed']) ? $data['belt_speed'] : null,
                                    'takeup_type' => isset($data['takeup_type']) ? $data['takeup_type'] : null,
                                    'counterweight' => isset($data['counterweight']) ? $data['counterweight'] : null,
                                    'takeup_travel' => isset($data['takeup_travel']) ? $data['takeup_travel'] : null,
                                    'carry_side_angle' => isset($data['carry_side_angle']) ? $data['carry_side_angle'] : null,
                                    'carry_side_diameter' => isset($data['carry_side_diameter']) ? $data['carry_side_diameter'] : null,
                                    'carry_side_space' => isset($data['carry_side_space']) ? $data['carry_side_space'] : null,
                                    'return_side_angle' => isset($data['return_side_angle']) ? $data['return_side_angle'] : null,
                                    'return_side_diameter' => isset($data['return_side_diameter']) ? $data['return_side_diameter'] : null,
                                    'return_side_space' => isset($data['return_side_space']) ? $data['return_side_space'] : null,
                                    'drive_pulley_power' => isset($data['drive_pulley_power']) ? $data['drive_pulley_power'] : null,
                                    'drive_pulley_wrap_angle' => isset($data['drive_pulley_wrap_angle']) ? $data['drive_pulley_wrap_angle'] : null,
                                    'drive_pulley_surface' => isset($data['drive_pulley_surface']) ? $data['drive_pulley_surface'] : null,
                                    'drive_pulley_diameter' => isset($data['drive_pulley_diameter']) ? $data['drive_pulley_diameter'] : null,
                                    'head_pulley_diameter' => isset($data['head_pulley_diameter']) ? $data['head_pulley_diameter'] : null,
                                    'takeup_pulley_diameter' => isset($data['takeup_pulley_diameter']) ? $data['takeup_pulley_diameter'] : null,
                                    'tail_pulley_diameter' => isset($data['tail_pulley_diameter']) ? $data['tail_pulley_diameter'] : null,
                                    'head_transition' => isset($data['head_transition']) ? $data['head_transition'] : null,
                                    'tail_transition' => isset($data['tail_transition']) ? $data['tail_transition'] : null,
                                    'type_trough_transitions' => isset($data['type_trough_transitions']) ? $data['type_trough_transitions'] : null,
                                    'with_turnovers' => isset($data['with_turnovers']) ? $data['with_turnovers'] : null,
                                    'turnover_length' => isset($data['turnover_length']) ? $data['turnover_length'] : null,
                                    'number_stations' => isset($data['number_stations']) ? $data['number_stations'] : null,
                                    'gear_ratio' => isset($data['gear_ratio']) ? $data['gear_ratio'] : null,
                                    'drive_frecuency' => isset($data['drive_frecuency']) ? $data['drive_frecuency'] : null,
                                    'conveyor_angle' => isset($data['conveyor_angle']) ? $data['conveyor_angle'] : null,
                                    'percent_load' => isset($data['percent_load']) ? $data['percent_load'] : null,
                                    'location' => isset($data['location']) ? $data['location'] : null,
                                    'pipe_belt' => isset($data['pipe_belt']) ? $data['pipe_belt'] : null,
                                    'direction_turnover' => isset($data['direction_turnover']) ? $data['direction_turnover'] : null,
                                    'ambient_conditions' => isset($data['ambient_conditions']) ? $data['ambient_conditions'] : null,
                                    'humidity' => isset($data['humidity']) ? $data['humidity'] : null,
                                    'sea_level' => isset($data['sea_level']) ? $data['sea_level'] : null,
                                    'housing' => isset($data['housing']) ? $data['housing'] : null,
                                    'friction_factor' => isset($data['friction_factor']) ? $data['friction_factor'] : null
                                ];

                                //tab_idlers
                                $tab_idlers_id = $this->UsTabIdler->findByConveyorId($conveyor_received);
                                $tab_idlers_id = empty($tab_idlers_id) ? null : $tab_idlers_id['UsTabIdler']['id'];
                                $idlers_tab = [
                                    'id' => $tab_idlers_id,
                                    'impact_diameter' => isset($data['impact_diameter']) ? $data['impact_diameter'] : null,
                                    'number_impact_idlers' => isset($data['number_impact_idlers']) ? $data['number_impact_idlers'] : null,
                                    'carry_side_idlers_number' => isset($data['carry_side_idlers_number']) ? $data['carry_side_idlers_number'] : null,
                                    'return_side_idlers_number' => isset($data['return_side_idlers_number']) ? $data['return_side_idlers_number'] : null,
                                    'part_troughing_load' => isset($data['part_troughing_load']) ? $data['part_troughing_load'] : null,
                                    'part_troughing_return' => isset($data['part_troughing_return']) ? $data['part_troughing_return'] : null,
                                    'impact_angle' => isset($data['impact_angle']) ? $data['impact_angle'] : null,
                                    'general_condition' => isset($data['general_condition']) ? $data['general_condition'] : null,
                                    'stuck_idlers' => isset($data['stuck_idlers']) ? $data['stuck_idlers'] : null,
                                    'misalignment_sensor_upper' => isset($data['misalignment_sensor_upper']) ? $data['misalignment_sensor_upper'] : null,
                                    'misalignment_sensor_lower' => isset($data['misalignment_sensor_lower']) ? $data['misalignment_sensor_lower'] : null
                                ];

                                $tab_pulleys_id = $this->UsTabPulley->findByConveyorId($conveyor_received);
                                $tab_pulleys_id = empty($tab_pulleys_id) ? null : $tab_pulleys_id['UsTabPulley']['id'];
                                $pulleys_tab = [
                                    'id' => $tab_pulleys_id,
                                    'drive_pulley_width' => isset($data['drive_pulley_width']) ? $data['drive_pulley_width'] : null,
                                    'lagging_thickness' => isset($data['lagging_thickness']) ? $data['lagging_thickness'] : null,
                                    'motriz_surface_condition' => isset($data['motriz_surface_condition']) ? $data['motriz_surface_condition'] : null,
                                    'brake_device' => isset($data['brake_device']) ? $data['brake_device'] : null,
                                    'head_pulley_width' => isset($data['head_pulley_width']) ? $data['head_pulley_width'] : null,
                                    'head_lagging_type' => isset($data['head_lagging_type']) ? $data['head_lagging_type'] : null,
                                    'head_surface_condition' => isset($data['head_surface_condition']) ? $data['head_surface_condition'] : null,
                                    'tail_pulley_width' => isset($data['tail_pulley_width']) ? $data['tail_pulley_width'] : null,
                                    'tail_lagging_type' => isset($data['tail_lagging_type']) ? $data['tail_lagging_type'] : null,
                                    'tail_surface_condition' => isset($data['tail_surface_condition']) ? $data['tail_surface_condition'] : null,
                                    'snub_pulley_diameter' => isset($data['snub_pulley_diameter']) ? $data['snub_pulley_diameter'] : null,
                                    'snub_pulley_width' => isset($data['snub_pulley_width']) ? $data['snub_pulley_width'] : null,
                                    'snub_lagging_type' => isset($data['snub_lagging_type']) ? $data['snub_lagging_type'] : null,
                                    'snub_surface_condition' => isset($data['snub_surface_condition']) ? $data['snub_surface_condition'] : null,
                                    'bend_pulley_diameter' => isset($data['bend_pulley_diameter']) ? $data['bend_pulley_diameter'] : null,
                                    'bend_pulley_width' => isset($data['bend_pulley_width']) ? $data['bend_pulley_width'] : null,
                                    'bend_lagging_type' => isset($data['bend_lagging_type']) ? $data['bend_lagging_type'] : null,
                                    'bend_surface_condition' => isset($data['bend_surface_condition']) ? $data['bend_surface_condition'] : null,
                                    'takeup_pulley_width' => isset($data['takeup_pulley_width']) ? $data['takeup_pulley_width'] : null,
                                    'takeup_lagging_type' => isset($data['takeup_lagging_type']) ? $data['takeup_lagging_type'] : null,
                                    'takeup_surface_condition' => isset($data['takeup_surface_condition']) ? $data['takeup_surface_condition'] : null,
                                    'add1_pulley_diameter' => isset($data['add1_pulley_diameter']) ? $data['add1_pulley_diameter'] : null,
                                    'add1_pulley_width' => isset($data['add1_pulley_width']) ? $data['add1_pulley_width'] : null,
                                    'add2_pulley_diameter' => isset($data['add2_pulley_diameter']) ? $data['add2_pulley_diameter'] : null,
                                    'add2_pulley_width' => isset($data['add2_pulley_width']) ? $data['add2_pulley_width'] : null
                                ];

                                $tab_transition_id = $this->UsTabTransitionZone->findByConveyorId($conveyor_received);
                                $tab_transition_id = empty($tab_transition_id) ? null : $tab_transition_id['UsTabTransitionZone']['id'];
                                $transition_zone_tab = [
                                    'id' => $tab_transition_id,
                                    'tail_pulley_lift' => isset($data['tail_pulley_lift']) ? $data['tail_pulley_lift'] : null,
                                    'pressure_outer_idlers' => isset($data['pressure_outer_idlers']) ? $data['pressure_outer_idlers'] : null,
                                    'head_pulley_lift' => isset($data['head_pulley_lift']) ? $data['head_pulley_lift'] : null,
                                    'material_guidance' => isset($data['material_guidance']) ? $data['material_guidance'] : null
                                ];

                                $remarks_id = $this->UsTabRemark->findByConveyorId($conveyor_received);
                                $remarks_id = empty($remarks_id) ? null : $remarks_id['UsTabRemark']['id'];
                                $remarks_tab = [
                                    'id' => $remarks_id,
                                    'maintenance_condition' => isset($data['maintenance_condition']) ? $data['maintenance_condition'] : null,
                                    'overall_status' => isset($data['overall_status']) ? $data['overall_status'] : null,
                                    'remarks' => isset($data['remarks']) ? $data['remarks'] : null
                                ];


                                if ($this->Conveyor->save($conveyor)) {
                                    $cover_img = $conveyor['Conveyor']['cover_img'];
                                    if ($data['path_logo_transportador'] != '') {
                                        $cover_img = $this->Transactions->addPictureConveyorForCompany($client_id, $conveyor_received, $data['path_logo_transportador']);
                                    }

                                    $conveyor['Conveyor']['cover_img'] = $cover_img;
                                    $this->Conveyor->save($conveyor);

                                    $response['conveyor_number'] = $data['no_transportador'];
                                    $response['success'] = true;
                                    $response['msg'] = __('La banda transportadora ha sido actualizada exitosamente', true);
                                    if($history_export){
                                        $response['msg'] =  $response['msg']."<br>".__("La informacion de banda instalada fue exportada a history y los campos estan limpios para agregar informacion de una nueva banda",true);
                                    }


                                    $this->UsTabInstalledBelt->save($installed_belt_tab);
                                    $this->UsTabMaterial->save($material_tab);
                                    $this->UsTabWearLife->save($wear_life_tab);
                                    $this->UsTabConveyor->save($conveyor_tab);
                                    $this->UsTabIdler->save($idlers_tab);
                                    $this->UsTabPulley->save($pulleys_tab);
                                    $this->UsTabTransitionZone->save($transition_zone_tab);
                                    $this->UsTabRemark->save($remarks_tab);


                                    // Guardamos la notificacion
                                    $this->Notifications->conveyorUpdated($conveyor['Conveyor']['id']);
                                    //Guardamos log de navegacion
                                    $this->Secure->saveBrowsingData(BrowsingLog::ACTUALIZACION, Item::CONVEYOR, $conveyor['Conveyor']['numero']);
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

    public function changeStatus() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $this->Conveyor->id = $conveyor_received;
                    $this->Conveyor->saveField('estatus', $data['new_status']);

                    $response['success'] = true;
                    $response['conveyor_number'] = $this->Conveyor->field('numero');
                    $response['msg'] = __('El estatus del transportador ha sido actualizado', true);
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

    public function removeReading(){
        $this->layout = false;
        $this->autoRender = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemParams = $this->Core->decodePairParams($params);
                if ($decodedItemParams['isOk']) {
                    $item_received = $decodedItemParams['item_id'];
                    $reading = $this->UltrasonicReading->findById($item_received);
                    if (!empty($reading)) {
                        $this->UltrasonicReading->id = $item_received;
                        $this->UltrasonicReading->saveField('deleted', true);
                        $response['msg'] = __('El elemento fue eliminado exitosamente', true);
                        $response['success'] = true;
                    }else{
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

    public function remove() {
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
                    //$conveyor = $this->Conveyor->findById($item_received);
                    $conveyor = $this->Conveyor->findFullById($item_received);
                    if (!empty($conveyor)) {
                        if($conveyor['Conveyor']['is_us_conveyor']){
                            $conveyor = $this->UsConveyor->findFullById($item_received);
                        }else{
                            $conveyor['Conveyor']['meta_units'] = strpos($conveyor['Conveyor']['meta_units'],'open_espesor_cubierta_sup=') !== false ? $conveyor['Conveyor']['meta_units'] : $conveyor['Conveyor']['meta_units'].'||open_espesor_cubierta_sup=in||open_espesor_cubierta_inf=in';
                            $conveyor = $this->Converter->process_convertion($conveyor);
                        }

                        $response['success'] = true;
                        $this->set('conveyor', $conveyor);

                        $imagen_portada = $conveyor['Conveyor']['cover_img'] > 0 ? $this->Image->findById($conveyor['Conveyor']['cover_img']) : array();
                        $path_imagen_portada = '';
                        if (!empty($imagen_portada)) {
                            $path_imagen_portada = $imagen_portada['Image']['eliminada'] == 0 ? $imagen_portada['Image']['path'] : '';
                        }

                        //$desc_materiales = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::MATERIAL_DESCRIPTION);
                        $fieldOrderDesc = $this->Core->_app_language=="es" ? "titulo ASC" : "titulo_en ASC";
                        $desc_materiales = $this->ConfigTransporter->getAllByDescIdSorted(ConfigTransporter::MATERIAL_DESCRIPTION, $fieldOrderDesc);

                        $angulos_contacto = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ANGULO_CONTACTO);
                        $tipos_tensor = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TENSOR_TYPE);
                        $tipos_polea = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::POLEA_TYPE);

                        $arcos_contacto = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ARCO_CONTACTO);
                        $rodillos_config = array();
                        $rodillos_config['diam_rodillos_ldc'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_DIAM_LDC);
                        $rodillos_config['angle_rodillo_ldc'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_ANGLE_LDC);
                        $rodillos_config['diam_rodillos_ldr'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_DIAM_LDR);
                        $rodillos_config['angle_rodillo_ldr'] = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ROLLER_ANGLE_LDR);

                        $espesor_cubiertas = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CUBIERTA_DESGASTE, $orderById = true);
                        $grado_material_transportado = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::GRADO_MAT_TRANSPORTADO);
                        $condicion_alimentacion = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CONDICION_ALIMENTACION);
                        $condicion_carga = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CONDICION_CARGA, $orderById = true, $sort="DESC");
                        $frecuencia_carga = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::FRECUENCIA_CARGA);
                        $tamanio_granular = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TAMANIO_GRANULAR);
                        $tipo_densidad = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TIPO_DENSIDAD);
                        $agresividad = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::AGRESIVIDAD, true);

                        $oil_presence = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::PRESENCIA_ACEITE, $orderById = true);
                        $conveyor_location = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::UBICACION_BANDA, $orderById = true);
                        $conveyor_tamanio_material = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TAMANIO_MATERIAL, $orderById = true);

                        $partes_artesa = array(
                            array('ctransp' => array('id' => 1, 'titulo' => 1, 'titulo_en' => 1, 'mat_density' => 0)),
                            array('ctransp' => array('id' => 2, 'titulo' => 2, 'titulo_en' => 2, 'mat_density' => 0)),
                            array('ctransp' => array('id' => 3, 'titulo' => 3, 'titulo_en' => 3, 'mat_density' => 0)),
                            array('ctransp' => array('id' => 5, 'titulo' => 5, 'titulo_en' => 5, 'mat_density' => 0)),
                        );
                        //array('ctransp' => array('id' => 4, 'titulo' => 4, 'titulo_en' => 4, 'mat_density' => 0)),
                        $angulo_acanalamiento = array(
                            array('ctransp' => array('id' => 20, 'titulo' => 20, 'titulo_en' => 20, 'mat_density' => 0)),
                            array('ctransp' => array('id' => 35, 'titulo' => 35, 'titulo_en' => 35, 'mat_density' => 0)),
                            array('ctransp' => array('id' => 45, 'titulo' => 45, 'titulo_en' => 45, 'mat_density' => 0))
                        );

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

                        $this->set('descripcion_materiales', $desc_materiales);
                        $this->set('angulos_contacto', $angulos_contacto);
                        $this->set('tipos_tensor', $tipos_tensor);
                        $this->set('tipos_polea', $tipos_polea);
                        $this->set('arcos_contacto', $arcos_contacto);
                        $this->set('rodillos_config', $rodillos_config);

                        $this->set('grado_material_transportado', $grado_material_transportado);
                        $this->set('condicion_alimentacion', $condicion_alimentacion);

                        $this->set('espesor_cubiertas', $espesor_cubiertas);

                        $this->set('condicion_carga', $condicion_carga);
                        $this->set('frecuencia_carga', $frecuencia_carga);
                        $this->set('tamanio_granular', $tamanio_granular);
                        $this->set('tipo_densidad', $tipo_densidad);
                        $this->set('agresividad', $agresividad);

                        $this->set('partes_artesa', $partes_artesa);
                        $this->set('angulo_acanalamiento', $angulo_acanalamiento);

                        $this->set('oil_presence', $oil_presence);
                        $this->set('conveyor_location', $conveyor_location);
                        $this->set('bulk_size', $conveyor_tamanio_material);



                        if($conveyor['Conveyor']['is_us_conveyor']){
                            $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                            $this->set('response', $response);
                            $this->render('update_us');
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
                    $folder = $this->Bucket->findById($folder_received);
                    if (!empty($folder)) {
                        $folderItems = $this->Conveyor->getItemsFolder($folder_received, $sort);
                        $transportador = $this->Conveyor->findById($folder['Bucket']['parent_conveyor']);
                        $transportador = $transportador['Conveyor'];
                    }
                    
                    $this->set('folder', $folder);
                }
            }
        }

        $this->set('item_id', $itemId);
        $this->set('conveyor', $transportador);
        $this->set('conveyor_items', $folderItems);
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
                $conveyor_received = $decodedConveyorParams['item_id'];

                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    if(!$conveyor["Conveyor"]["is_us_conveyor"]){
                        $conveyor = $this->Converter->process_convertion($conveyor);
                    }else{
                        $conveyor = $this->UsConveyor->findByIdWithCompany($conveyor_received);
                        $this->set('conveyor_us_config', Configure :: read('ConveyorUS'));
                    }
                    $empresa = $conveyor['Empresa'];
                    $distribuidor = $conveyor['Distribuidor'];

                    $comments_item = $this->Comment->getCommentsItemByType($conveyor_received, Item::CONVEYOR);

                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $this->set('isUsConveyor', $conveyor["Conveyor"]["is_us_conveyor"]);

                    $has_failed_date = false;
                    if($conveyor["Conveyor"]["is_us_conveyor"]){
                        $has_failed_date = $conveyor["TabInstalledBelt"]["installation_date"]!='0000-00-00' && $conveyor["TabInstalledBelt"]["installation_date"]!='' && $conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                    }else{
                        $has_failed_date = $conveyor["Conveyor"]["banda_fecha_instalacion"]!='0000-00-00' && $conveyor["Conveyor"]["banda_fecha_instalacion"]!='' && $conveyor["TabInstalledBelt"]["date_belt_failed"]!='0000-00-00' && $conveyor["TabInstalledBelt"]["date_belt_failed"]!='' ? true : false;
                    }

                    $this->set('has_failed_date', $has_failed_date);

                    //$transportador = $conveyor['Conveyor'];
                    $this->set('conveyor', $conveyor);
                    $this->set('comments_item', $comments_item);
                    $this->set('company', $empresa);
                    $this->set('dealer', $distribuidor);

                    $this->openCssToInclude[] = 'plugins/Assets/css/ladda-bootstrap/ladda-themeless.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/spin.min';
                    $this->openJsToInclude[] = 'plugins/Assets/js/ladda-bootstrap/ladda.min';

                    $this->set('openCssToInclude', $this->openCssToInclude);
                    $this->set('openJsToInclude', $this->openJsToInclude);

                    $this->jsToInclude[] = 'application/Conveyors/conveyor_datasheet';
                    $this->set('jsToInclude', $this->jsToInclude);


                    $this->Core->setTutorialSectionViewed(8);
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

    public function openDatasheet() {
        $this->layout = 'open';

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $response = array('success' => false, 'msg' => '');
        if (!empty($params) && count($params) == 2) {
            $decodedConveyorParams = $this->Core->decodePairParams($params);
            if ($decodedConveyorParams['isOk']) {
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $conveyor = $this->Converter->process_convertion($conveyor);
                    $empresa = $conveyor['Empresa'];
                    $distribuidor = $conveyor['Distribuidor'];

                    $comments_item = $this->Comment->getCommentsItemByType($conveyor_received, Item::CONVEYOR);
                    //$transportador = $conveyor['Conveyor'];
                    $this->set('conveyor', $conveyor);
                    $this->set('comments_item', $comments_item);
                    $this->set('company', $empresa);
                    $this->set('dealer', $distribuidor);

                    $this->set('is_mobile', $this->request->is('mobile'));
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
                $conveyor_received = $decodedConveyorParams['item_id'];
                $conveyor = $this->Conveyor->findByIdWithCompany($conveyor_received);
                if (!empty($conveyor)) {
                    $original_conveyor = $conveyor;
                    $conveyor = $this->Converter->process_convertion($conveyor);
                    $this->set('conveyor', $conveyor);
                    $estimated_lifetime = $this->Core->calcLifeEstimationBanda($original_conveyor);
                    $this->set('estimated_lifetime', $estimated_lifetime);
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
                $conveyor_received = $decodedConveyorParams['item_id'];
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

    public function addItemConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $data = $this->request->data; //get data
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $type_item = $data['type_item'];
                    
                    $yearsfolder = array();
                    if($type_item==Item::FOLDERYEAR){
                        //Obtenemos los folder de año existentes del conveyor
                        $foldersYearConveyor = $this->Bucket->find('all', array(
                            'fields' => array('nombre'),
                            'conditions' => array('parent_conveyor' => $conveyor_received, 'is_folder_year'=>1, 'eliminada'=>0),
                            'order' => array('nombre' => 'ASC')
                        ));
                    
                        $years_saved = array();
                        if(count($foldersYearConveyor)>0){
                            foreach ($foldersYearConveyor as $folderYear){
                                $years_saved[] = (int)$folderYear['Bucket']['nombre'];
                            }
                        }
                        
                        for($i = date('Y'); $i>= 1990; $i-- ){
                            $yearsfolder[] = $i;
                        }
                        $yearsfolder = array_diff($yearsfolder, $years_saved);
                    }
                    
                    
                    $response['success'] = true;
                    $this->set('type_item', $type_item);
                    $this->set('conveyor_id', $conveyor_received);
                    $this->set('secure_params', $params);
                    $this->set('yearsfolder',$yearsfolder);
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

    public function saveItemConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                $decodedFolderParams = count($params) == 4 ? $this->Core->decodePairParams($params, 2) : array();
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($conveyor)) {
                        $conveyor = $conveyor['Conveyor'];
                        if ($data['item_name'] != '' && $data['item_description'] != '' && $data['path_item'] != '') {
                            $itemSaved = 0;
                            switch ($data['item_type']) {
                                case Item::IMAGE:
                                    $itemSaved = $this->Transactions->addPictureConveyorForCompany($conveyor['id_company'], $conveyor['id'], $data['path_item'], $data['item_taken_at'] ,$data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('La imagen fue agregada exitosamente', true);

                                    //save score card statistic
                                    $salespersonAssoc = $this->Core->getSalespersonIfExists($conveyor['id_company']);
                                    if($salespersonAssoc>0){
                                        $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_ITEM_CONVEYOR);
                                    }
                                    /*
                                    if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                                        $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_ITEM_CONVEYOR);
                                    }*/
                                 break;
                                case Item::VIDEO:
                                    $itemSaved = $this->Transactions->addVideoConveyorForCompany($conveyor['id_company'], $conveyor['id'], $data['path_item'], $data['item_taken_at'] ,$data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('El video fue agregado exitosamente', true);

                                    //save score card statistic
                                    $salespersonAssoc = $this->Core->getSalespersonIfExists($conveyor['id_company']);
                                    if($salespersonAssoc>0){
                                        $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_ITEM_CONVEYOR);
                                    }
                                    /*
                                    if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                                        $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_ITEM_CONVEYOR);
                                    }*/
                                break;
                                case Item::FOLDERYEAR:
                                    $this->Bucket->save(array(
                                        'nombre' => $data['item_name'],
                                        'parent_conveyor' => $conveyor['id'],
                                        'is_folder_year' => $data['is_folder_year'],
                                        'actualizada' => date('Y-m-d H:i:s')
                                    ));
                                    
                                    $data['item_type'] = Item::FOLDER;
                                    $itemSaved = $this->Bucket->getInsertID();
                                    $response['msg'] = __('El folder fue agregado exitosamente', true);
                                break;
                                case Item::FOLDER:
                                    $this->Bucket->save(array(
                                        'nombre' => $data['item_name'],
                                        'parent_conveyor' => $conveyor['id'],
                                        'parent_folder' => $folder_id,
                                        'actualizada' => date('Y-m-d H:i:s')
                                    ));

                                    $itemSaved = $this->Bucket->getInsertID();

                                    $response['msg'] = __('El folder fue agregado exitosamente', true);
                                    break;
                                case Item::REPORT:
                                    $itemSaved = $this->Transactions->addReportConveyorForCompany($conveyor['id_company'], $conveyor['id'], $data['path_item'], $data['item_name'], $data['item_description'], $folder_id);
                                    $response['msg'] = __('El reporte fue agregado exitosamente', true);

                                    //save score card statistic
                                    $salespersonAssoc = $this->Core->getSalespersonIfExists($conveyor['id_company']);
                                    if($salespersonAssoc>0){
                                        $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_ITEM_CONVEYOR);
                                    }
                                    /*
                                    if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                                        $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_ITEM_CONVEYOR);
                                    }*/
                                    break;
                                case Item::NOTE:
                                    $this->Note->save(array(
                                        'nombre' => $data['item_name'],
                                        'contenido' => $data['item_description'],
                                        'parent_conveyor' => $conveyor['id'],
                                        'parent_folder' => $folder_id,
                                        'actualizada' => date('Y-m-d H:i:s')
                                    ));
                                    $itemSaved = $this->Note->getInsertID();
                                    $response['msg'] = __('La nota fue agregada exitosamente', true);
                                    break;
                            }

                            if (in_array($data['item_type'], array(Item::IMAGE, Item::VIDEO, Item::REPORT))) {
                                /*
                                 * Guardamos la notificacion *
                                 * ************************** */
                                $this->Notifications->itemSaved($data['item_type'], $itemSaved, $conveyor);
                            }else if (in_array($data['item_type'], array(Item::NOTE, Item::FOLDER, Item::FOLDERYEAR))) {
                                $this->Notifications->itemSaved($data['item_type'], $itemSaved, $conveyor);
                            }

                            if ($itemSaved > 0) {
                                $typeItem = $data['item_type'];
                                $item = $this->$typeItem->findById($itemSaved);
                                /*
                                 * Guardamos log de navegacion 
                                 */
                                $this->Secure->saveBrowsingData(BrowsingLog::ALTA, $typeItem, $item[$typeItem]['nombre']);
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

    public function saveFileConveyor() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) >= 2) {
                $decodedConveyorParams = $this->Core->decodePairParams($params);
                $decodedFolderParams = count($params) == 4 ? $this->Core->decodePairParams($params, 2) : array();
                if ($decodedConveyorParams['isOk']) {
                    $conveyor_received = $decodedConveyorParams['item_id'];
                    $conveyor = $this->Conveyor->findById($conveyor_received);

                    $folder_id = !empty($decodedFolderParams) ? $decodedFolderParams['item_id'] : 0;

                    if (!empty($conveyor)) {
                        $conveyor = $conveyor['Conveyor'];
                        if ($data['item_name'] != '' && $data['path_item'] != '') {
                            $itemSaved = 0;
                            $itemSaved = $this->Transactions->addFileConveyorForCompany($conveyor['id_company'], $conveyor['id'], $data['path_item'], $data['item_name'], $folder_id);
                            $this->Notifications->itemSaved(Item::FILE, $itemSaved, $conveyor);
                            $response['msg'] = __('El archivo fue agregado exitosamente', true);
                            $response['success'] = true;

                            //save score card statistic
                            $salespersonAssoc = $this->Core->getSalespersonIfExists($conveyor['id_company']);
                            if($salespersonAssoc>0){
                                $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_ITEM_CONVEYOR);
                            }
                            /*
                            if($this->credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
                                $this->Secure->saveStatisticData($this->credentials['id'], Statistic::NEW_ITEM_CONVEYOR);
                            }*/
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
