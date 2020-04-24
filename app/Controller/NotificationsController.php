<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file NotificationsController.php
 *     Management of actions for system notifications
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class NotificationsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function add() {
        $userProperties = $this->Core->getRegionCountryAndMarketForUserLogged();
        $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor', $this->global_filter_region, 0, $userProperties['region'],$userProperties['country'],$userProperties['market']);
        $manager_corporate = Configure::read('manager_corporate'); 
        if(!is_null($manager_corporate) && $this->credentials['role_company']==UsuariosEmpresa::IS_DIST){//Si es un manager dis
              $dist_companies = $this->Empresa->findByRegionAndTypeWithCorporate('distributor','',$manager_corporate);
        }

        $sharedDealers = $this->Core->getSharedDealersSalesperson();
        if(!empty($sharedDealers)){
            $dist_companies = array_merge($dist_companies, $sharedDealers);
        }

        $this->set('distribuidores', $dist_companies);
    }

    public function processAdd() {
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);
            if ($data['fecha'] == '' || $data['hora'] == '' || trim($data['notification']) == '' || $data['distributor_txt'] == '' || !isset($data['client_txt']) || $data['client_txt'] == '') {
                $response['msg'] = __('Favor de seleccionar todos los campos requeridos', true);
            } else {
                $mails = $params['send_mails']!='' ? substr($params['send_mails'], 0,-1):'';
                //$activation_date = gmdate("Y-m-d H:i:s", $bucket_data['created_at']);
                $fecha_activacion = $this->Core->mergeDateAndTimeToTimestamp($data['fecha'], $data['hora']);
                $notificacion = trim($data['notification']);
                $savedNotification = $this->Notifications->push($data['client_txt'], $fecha_activacion, $notificacion, $mails, $programada = true);
                if ($savedNotification > 0) {
                    $response['success'] = true;
                    $response['msg'] = __('La notificacion ha sido programada correctamente', true);
                } else {
                    $response['msg'] = __('Ocurrio un error al insertar la notificacion, intentelo nuevamente', true);
                }
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function refresh() {
        $notifications_data = $this->Notifications->getAllByCompanyAndUser();
        $programmed_notifications = in_array($this->credentials['role'], array(UsuariosEmpresa::IS_DIST, UsuariosEmpresa::IS_ADMIN, UsuariosEmpresa::IS_MASTER)) ? $this->Notifications->getAllProgrammedForCompany() : array();
        
        $readedNotifications = array();
        $notifications = $notifications_data['rows'];
        foreach ($notifications AS $notification) {
            $notificacion = $notification['Notification'];
            $vista_notificacion = $notification['ViewedNotification'];
            if(is_null($vista_notificacion['id_notification'])){
                $readedNotifications[] = array('ViewedNotification' => array('id_notification' => $notificacion['id'],'id_user'=>  $this->credentials['id']));
            }
        }
        if(!empty($readedNotifications)){
            $this->ViewedNotification->saveMany($readedNotifications);
        }

        $this->set('unreaded_notifications', $notifications_data['unreaded']);
        $this->set('programmed_notifications', $programmed_notifications);
        $this->set('automatic_notifications', $notifications_data['rows']);
    }
    
    
    public function remove(){
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedNotificationParams = $this->Core->decodePairParams($params);
                if ($decodedNotificationParams['isOk']) {
                    $notification_received = $decodedNotificationParams['item_id'];
                    $notification = $this->Notification->findById($notification_received);
                    if (!empty($notification)) {
                        if($this->Notification->delete($notification_received)){
                            $response['msg'] = __('La notificacion fue eliminada exitosamente', true);
                            $response['success'] = true;
                        }else{
                            $response['msg'] = __('Ocurrio un error al eliminar la notificacion, intentelo nuevamente', true);
                        }
                    } else {
                        $response['msg'] = __('Error, la notificacion a eliminar no fue encontrado', true);
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
    
    public function setByMail(){
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data            
            $usuario = $this->UsuariosEmpresa->findById($this->credentials['id']);            
            $usuario['UsuariosEmpresa']['accept_mail_notif'] = $params['activate'];                        
            if($this->UsuariosEmpresa->save($usuario)){
                $response['success'] = true;
                $this->refreshCredentials();
            }              
            $this->set('response',$response);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

}
