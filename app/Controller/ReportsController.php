<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ReportsController.php
 *     Management of actions for reports
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::import('Vendor', 'Dompdf', array('file' => 'Dompdf/dompdf_config.inc.php'));
class ReportsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }
    
    public function refreshCustomReports() {
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
        $items = $this->Core->getCustomReportsUsingFilters($filter_companies, $query, $sort, $rows, $desde);

        $this->set('items', $items);
        $this->set('offset', $desde);
    }
    
    public function custom(){        
        $this->set('options_toolbar', 'custom-reports-section');
        
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
        
        
        $items = $this->Core->getCustomReportsUsingFilters($filter_companies, $query, $sort);
        $this->jsToInclude[] = 'application/Reports/custom';
        $this->jsToInclude[] = 'scrolling';
        $this->set('jsToInclude', $this->jsToInclude);
        
        $conveyorsClientUrl = $this->_html->url(array('controller' => 'Ajax', 'action' => 'getClientConveyors'));
        $autocompleteItems = $this->Core->initGenericAutocomplete($items, 'CustomReport');
        $this->setJsVar('autocompleteItems', $autocompleteItems);
        $this->setJsVar('totItems', count($items));
        $this->setJsVar('conveyorsClientsAx', $conveyorsClientUrl);
        
        $this->Core->setTutorialSectionViewed(3);
    }
    
    public function removeCustomReport() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->CustomReport->findById($item_received);
                    if (!empty($item)) {
                        $this->CustomReport->id = $item_received;
                        $this->CustomReport->saveField('eliminada', true);
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
    
    
    public function addCustomReport(){
        $userProperties = $this->Core->getRegionCountryAndMarketForUserLogged();
        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region, 0, $userProperties['region'],$userProperties['country'],$userProperties['market']);
        $manager_corporate = Configure::read('manager_corporate'); 
        if(!is_null($manager_corporate)){
            if($this->credentials['role_company']==UsuariosEmpresa::IS_DIST){//Si es un manager dis        
              $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor','',$manager_corporate);
            }else{//es manager cli
                $dist_companies = $this->Empresa->findByIdWithCorporate($this->credentials['parent']);
                $dist_companies = array($dist_companies);
            }
        }


        $sharedDealers = $this->Core->getSharedDealersSalesperson();
        if(!empty($sharedDealers)){
            $dist_companies = array_merge($dist_companies, $sharedDealers);
        }
            
        $plantillas = $this->ReportTemplate->find('all',array('order'=>array('title ASC')));
        $this->set('distribuidores', $dist_companies);
        $this->set('plantillas', $plantillas);
        $this->Core->setTutorialSectionViewed(4);
    }
    
    public function saveCustomReport() {
        $this->layout=false;
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            $conveyors = $params['bandas'];
            $fields = $params['campos'];
            $label_fields = $params['titulos'];

            if ($data['titulo_reporte'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['code'] = 0; //Indice de la pestania en activar
                $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
            } else {
                $client_id = $data['client_txt'];
                if ($client_id > 0) {
                    $this->CustomReport->begin();
                    $custom_report_reg = array(
                        'company_id' => $client_id,
                        'title' => $data['titulo_reporte'], 
                        'conveyors' => $conveyors,
                        'fields' => $fields,
                        'label_fields' => utf8_decode($label_fields),
                        'actualizada' => date('Y-m-d H:i:s')
                    );

                    if ($this->CustomReport->save($custom_report_reg)) {
                        $custom_report_id = $this->CustomReport->getInsertID();
                        $report_template_id = $this->Transactions->addReportTemplate($data['templates'], $fields);
                        if($report_template_id>=0){//si es -1 hay que modificar el nombre de la plantilla       
                            $this->CustomReport->commit();
                            $response['success'] = true;
                            $response['custom_report_title'] = $data['titulo_reporte'];
                            $response['msg'] = __('El reporte ha sido guardado exitosamente', true);
                        }else{
                            $this->CustomReport->rollback();
                            $response['msg'] = __('El nombre de la plantilla ya existe, intentelo nuevamente', true);
                        }

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
    
    public function updateCustomReport() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $custom_report = $this->CustomReport->findById($item_received);
                    if (!empty($custom_report)) {
                        $response['success'] = true;                        
                        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region);

                        $sharedDealers = $this->Core->getSharedDealersSalesperson();
                        if(!empty($sharedDealers)){
                            $dist_companies = array_merge($dist_companies, $sharedDealers);
                        }

                        $plantillas = $this->ReportTemplate->find('all',array('order'=>array('title ASC')));
                        $conveyors = $custom_report['CustomReport']['conveyors'];
                        $fields = $custom_report['CustomReport']['fields'];                        
                        
                        $this->set('custom_report', $custom_report);                        
                        $this->set('conveyors', $conveyors);                        
                        $this->set('fields', $fields);                        
                        $this->set('distribuidores', $dist_companies);
                        $this->set('plantillas', $plantillas);                        
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
    
    public function processUpdateCustomReport() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $form_params = $this->request->data; //get data
            parse_str($form_params['formdata'], $data);
            $conveyors = $form_params['bandas'];
            $fields = $form_params['campos'];
            $label_fields = $form_params['titulos'];
            
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedCustomReportParams = $this->Core->decodePairParams($params);
                if ($decodedCustomReportParams['isOk']) {
                    $custom_report_received = $decodedCustomReportParams['item_id'];
                    $custom_report = $this->CustomReport->findById($custom_report_received);
                    if (!empty($custom_report)) {
                        if ($data['titulo_reporte'] == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('Favor de proporcionar todos los campos requeridos', true);
                        } else {
                            $client_id = $data['client_txt'];
                            if ($client_id > 0) {
                                
                                $this->CustomReport->begin();
                                $custom_report['CustomReport']['title'] = $data['titulo_reporte'];
                                $custom_report['CustomReport']['conveyors'] = $conveyors;
                                $custom_report['CustomReport']['fields'] = $fields;
                                $custom_report['CustomReport']['label_fields'] = utf8_decode($label_fields);
                                $custom_report['CustomReport']['actualizada'] = date('Y-m-d H:i:s');
                                
                                if ($this->CustomReport->save($custom_report)) {                                    
                                    $report_template_id = $this->Transactions->addReportTemplate($data['templates'], $fields);
                                    if($report_template_id>=0){//si es -1 hay que modificar el nombre de la plantilla       
                                        $this->CustomReport->commit();
                                        $response['success'] = true;
                                        $response['custom_report_title'] = $data['titulo_reporte'];
                                        $response['msg'] = __('El reporte ha sido actualizado exitosamente', true);
                                    }else{
                                        $this->CustomReport->rollback();
                                        $response['msg'] = __('El nombre de la plantilla ya existe, intentelo nuevamente', true);
                                    }
                                } else {
                                    $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                                }
                            } else {
                                $response['msg'] = __('Favor de proporcionar el cliente', true);
                            }
                        }
                    } else {
                        $response['msg'] = __('El reporte que intenta actualizar no existe', true);
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
    
    public function generatePdf() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedCustomReportParams = $this->Core->decodePairParams($params);
            if ($decodedCustomReportParams['isOk']) {
                $custom_report_received = $decodedCustomReportParams['item_id'];
                $custom_report = $this->CustomReport->findById($custom_report_received);
                if (!empty($custom_report)) {
                    $company = $this->Empresa->findByIdWithCorporate($custom_report['CustomReport']['company_id']);
                    $conveyor_ids = $custom_report['CustomReport']['conveyors'];
                    $label_selected_fields = $custom_report['CustomReport']['label_fields'];
                    $selected_fields = $custom_report['CustomReport']['fields'];
                    $conveyors = $this->Conveyor->findByIdsWithCompany($conveyor_ids);
                    $conveyor_list = array();
                    if (!empty($conveyors)) {
                        foreach ($conveyors AS $conveyor) {
                            $conveyor_list[] = $this->Converter->process_convertion($conveyor);
                        }
                    }
                    $this->set('custom_report', $custom_report);
                    $this->set('company', $company);
                    $this->set('label_fields', $label_selected_fields);
                    $this->set('fields', $selected_fields);
                    $this->set('conveyors', $conveyor_list);
                    $this->set('Socket', $this->CustomSocket);
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
    
    public function downloadPdf() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedCustomReportParams = $this->Core->decodePairParams($params);
            if ($decodedCustomReportParams['isOk']) {
                $custom_report_received = $decodedCustomReportParams['item_id'];
                $custom_report = $this->CustomReport->findById($custom_report_received);
                if (!empty($custom_report)) {
                    $company = $this->Empresa->findByIdWithCorporate($custom_report['CustomReport']['company_id']);
                    $conveyor_ids = $custom_report['CustomReport']['conveyors'];
                    $label_selected_fields = $custom_report['CustomReport']['label_fields'];
                    $selected_fields = $custom_report['CustomReport']['fields'];
                    $conveyors = $this->Conveyor->findByIdsWithCompany($conveyor_ids);
                    $conveyor_list = array();
                    if (!empty($conveyors)) {
                        foreach ($conveyors AS $conveyor) {
                            $conveyor_list[] = $this->Converter->process_convertion($conveyor);
                        }
                    }
                    $this->set('custom_report', $custom_report);
                    $this->set('company', $company);
                    $this->set('label_fields', $label_selected_fields);
                    $this->set('fields', $selected_fields);
                    $this->set('conveyors', $conveyor_list);
                    $this->set('Socket', $this->CustomSocket);
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

    public function dwlLitePdf() {
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedCustomReportParams = $this->Core->decodePairParams($params);
            if ($decodedCustomReportParams['isOk']) {
                $custom_report_received = $decodedCustomReportParams['item_id'];
                $custom_report = $this->CustomReport->findById($custom_report_received);
                if (!empty($custom_report)) {
                    $company = $this->Empresa->findByIdWithCorporate($custom_report['CustomReport']['company_id']);
                    $conveyor_ids = $custom_report['CustomReport']['conveyors'];
                    $label_selected_fields = $custom_report['CustomReport']['label_fields'];
                    $selected_fields = $custom_report['CustomReport']['fields'];
                    $conveyors = $this->Conveyor->findByIdsWithCompany($conveyor_ids);
                    $conveyor_list = array();
                    if (!empty($conveyors)) {
                        foreach ($conveyors AS $conveyor) {
                            $conveyor_list[] = $this->Converter->process_convertion($conveyor);
                        }
                    }
                    $this->set('custom_report', $custom_report);
                    $this->set('company', $company);
                    $this->set('label_fields', $label_selected_fields);
                    $this->set('fields', $selected_fields);
                    $this->set('conveyors', $conveyor_list);
                    $this->set('Socket', $this->CustomSocket);
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
}
