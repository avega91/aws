<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file NotificationsComponent.php
 *     Component to manage system notifications
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class NotificationsComponent extends Component {

    var $components = array('Core', 'Mail','Session'); // the other component your component uses
    private $_credentials = null;
    private $_notificationsQueue = array();

    /**
     * Class constructor
     */
    public function __construct(ComponentCollection $collection, array $settings = array()) {
        parent::__construct($collection, $settings);
    }

    /**
     * Get all notification created by company of user logged
     * @return array
     */
    public function getAllProgrammedForCompany() {
        $this->_credentials = $this->Core->getAppCredentials();
        $this->Notification = ClassRegistry::init('Notification');
        $notifications = $this->Notification->findallByProgrammedCompany($this->_credentials['id_empresa']);
        return $notifications;
    }

    /**
     * Get all notifications targeted for company and user logged
     * @return array
     */
    public function getAllByCompanyAndUser() {
        $this->_credentials = $this->Core->getAppCredentials();

        $this->Notification = ClassRegistry::init('Notification');
        $notifications = $this->Notification->findallByCompanyAndUser($this->_credentials['id_empresa'], $this->_credentials['id']);
        $unread = count(array_filter($notifications, create_function('$a', 'return $a["ViewedNotification"]["id_notification"] === null;')));
        return array('unreaded' => $unread, 'rows' => $notifications);
    }

    /**
     * Insert notification row
     * @param int $company_dest id company target
     * @param timestamp $activation_date string with activation date YY-MM-DD HH:MM:SS
     * @param string $content notification content
     * @param string $mails mails to send
     * @param bool $is_programmed type notification
     * @return int
     */
    public function push($company_dest, $activation_date, $content, $mails = '', $is_programmed = false, $type_item = 'None', $id_item = 0) {
        $this->_credentials = $this->Core->getAppCredentials();
        $this->Notification = ClassRegistry::init('Notification');
        $user_credentials = $this->_credentials;
        $notificacion = array('Notification' => array(
                'id_company_dest' => $company_dest,
                'id_company_ori' => $user_credentials['id_empresa'],
                'generated_by' => $user_credentials['id'],
                'content' => $content,
                'activation_date' => $activation_date,
                'is_programmed' => $is_programmed,
                'type_item' => $type_item,
                'id_item' => $id_item,
                'mails' => $mails
            )
        );

        $notification_inserted = 0;
        if ($this->Notification->save($notificacion)) {
            $notification_inserted = $this->Notification->getInsertID(); //obtenemos el ultimo registro insertado
        }
        return $notification_inserted;
    }

    public function addToQueue($company_dest, $activation_date, $content, $is_programmed = false, $type_item = 'None', $id_item = 0) {
        $user_credentials = is_null($this->_credentials) ? array('id_empresa' => 0, 'id' => 0) : $this->_credentials;
        $this->_notificationsQueue[] = array(
            'Notification' => array(
                'id_company_dest' => $company_dest,
                'id_company_ori' => $user_credentials['id_empresa'],
                'generated_by' => $user_credentials['id'],
                'content' => $content,
                'activation_date' => $activation_date,
                'is_programmed' => $is_programmed,
                'type_item' => $type_item,
                'id_item' => $id_item
            )
        );
    }
    
    public function addToQueueLog($company_dest, $activation_date, $content, $is_programmed = false, $type_item = 'None', $id_item = 0, $just_for_log = 0, $conveyor_log = 0) {
        $user_credentials = is_null($this->_credentials) ? array('id_empresa' => 0, 'id' => 0) : $this->_credentials;
        $this->_notificationsQueue[] = array(
            'Notification' => array(
                'id_company_dest' => $company_dest,
                'id_company_ori' => $user_credentials['id_empresa'],
                'generated_by' => $user_credentials['id'],
                'content' => $content,
                'activation_date' => $activation_date,
                'is_programmed' => $is_programmed,
                'type_item' => $type_item,
                'id_item' => $id_item,
                'just_for_log' => $just_for_log,
                'conveyor_log' => $conveyor_log
            )
        );
    }

    public function processQueue() {
        $this->Notification = ClassRegistry::init('Notification');
        if (!empty($this->_notificationsQueue)) {
            $this->Notification->saveMany($this->_notificationsQueue);
        }
        //CakeLog::write('debug', 'cola de notificaciones');
        //CakeLog::write('debug', print_r($this->_notificationsQueue, true));
    }
    
    

    public function buoySystemSaved($conveyor_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->FolderApp = ClassRegistry::init('FolderApp');

        $conveyorSaved = $this->FolderApp->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['FolderApp'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['client_id']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role_company'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }


            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, agrego el transportador %s para el cliente %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['name'], $empresaCliente['name']));
                //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
                //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }


        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role_company'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', agrego el transportador %' . $conveyorSaved['name'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, agrego el transportador %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['name']));
            //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
            //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role_company'] != UsuariosEmpresa::IS_CLIENT) {
            $text_notification = 'Se agrego un nuevo transportador con titulo %' . $conveyorSaved['name'] . '%';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaCliente['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('Se agrego un nuevo transportador con titulo %s', array($conveyorSaved['name']));
            //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
            //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }


    public function buoySystemDeleted($conveyor_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->FolderApp = ClassRegistry::init('FolderApp');

        $conveyorSaved = $this->FolderApp->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['FolderApp'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['client_id']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        $conveyorSaved['id'] = 0;

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role_company'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el transportador %' . $conveyorSaved['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, elimino el transportador %s del cliente %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['name'], $empresaCliente['name']));
                //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
                //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }


        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role_company'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', elimino el transportador %' . $conveyorSaved['name'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, elimino el transportador %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['name']));
            //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
            //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }








    public function conveyorSaved($conveyor_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $conveyorSaved = $this->Conveyor->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['Conveyor'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['id_company']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' para el cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }


            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, agrego el transportador %s para el cliente %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['numero'], $empresaCliente['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }


        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', agrego el transportador %' . $conveyorSaved['numero'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, agrego el transportador %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['numero']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role'] != UsuariosEmpresa::IS_CLIENT) {
            $text_notification = 'Se agrego un nuevo transportador con titulo %' . $conveyorSaved['numero'] . '%';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaCliente['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('Se agrego un nuevo transportador con titulo %s', array($conveyorSaved['numero']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }
    
    public function trackingConveyorSaved($conveyor_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->TrackingConveyor = ClassRegistry::init('TrackingConveyor');

        $conveyorSaved = $this->TrackingConveyor->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['TrackingConveyor'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['company_id']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();


        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role'] != UsuariosEmpresa::IS_CLIENT) {
            $text_notification = 'Rastrea en tiempo real la banda "%' . $conveyorSaved['title'] . '%" ';
            $text_notification .= 'desde la seccion Servicios Premium o dando clic aqui';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, Item::TRACKING_CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaCliente['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('Rastrea en tiempo real la banda %s desde la seccion Servicios Premium', array($conveyorSaved['title']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }

    public function conveyorUpdated($conveyor_id) {

        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $conveyorSaved = $this->Conveyor->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['Conveyor'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['id_company']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();
        
        $justForLog = 1;        
        $logNotification = "El usuario %$credentials[name]% edito la hoja tecnica del transportador";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $conveyor_id, $justForLog, $conveyor_id);
        
        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', edito el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', edito el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', edito el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', edito el transportador %' . $conveyorSaved['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, edito el transportador %s del cliente %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['numero'], $empresaCliente['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }


        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', edito el transportador %' . $conveyorSaved['numero'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, edito el transportador %s', array($credentials['name'], $credentials['name_company'], $conveyorSaved['numero']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }
        
        $this->processQueue();
    }

    

    public function itemSaved($typeItem, $idItem, $conveyor) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->FolderApp = ClassRegistry::init('FolderApp');
        $this->Archive = ClassRegistry::init('Archive');

        //Obtenemos el elemento recientemente guardado
        $itemSaved = $this->FolderApp->findById($idItem);
        $itemSaved = $itemSaved['FolderApp'];

        $strings_add_item = array(
            Item::FOLDER => 'agrego el folder',
            Item::FOLDER_FILE => 'agrego el folder de archivo',
            Item::FILE =>  'agrego un archivo en el folder',
        );

        $empresaCliente = $this->Empresa->findById($conveyor['client_id']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();
        $justForLog = 1;
        
        $logNotification = "El usuario %$credentials[name]% ".$strings_add_item[$typeItem]." %$itemSaved[name]%";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $idItem, $justForLog, $conveyor['id']);

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role_company'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, ' . $strings_add_item[$typeItem] . ' %s al transportador %s del cliente %s', array($credentials['name'], $credentials['name_company'], $itemSaved['name'], $conveyor['name'], $empresaCliente['name']));
                //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
                //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }

        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role_company'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
            $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, ' . $strings_add_item[$typeItem] . ' %s al transportador %s', array($credentials['name'], $credentials['name_company'], $itemSaved['name'], $conveyor['name']));
            //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
            //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role_company'] != UsuariosEmpresa::IS_CLIENT) {

            $strings_add_item = array(
                Item::FOLDER => 'Se agrego el folder',
                Item::FOLDER_FILE => 'Se agrego el folder de archivo',
                Item::FILE =>  'Se agrego un archivo al folder',
            );

            $text_notification = $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
            $text_notification .= ' al transportador %' . $conveyor['name'] . '%';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaCliente['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __($strings_add_item[$typeItem] . ' %s al transportador %s', array($itemSaved['name'], $conveyor['name']));
            //$this->Mail->sendMailNotification($users_to_send, $mail_notification);
            //$this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }
    
    
    public function itemEdited($typeItem, $idItem, $conveyor) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Image = ClassRegistry::init('Image');
        $this->Movie = ClassRegistry::init('Movie');
        $this->Report = ClassRegistry::init('Report');
        $this->Note = ClassRegistry::init('Note');
        $this->Bucket = ClassRegistry::init('Bucket');
        $this->Archive = ClassRegistry::init('Archive');

        //Obtenemos el elemento recientemente guardado
        $typeItem = $typeItem==Item::FOLDERYEAR ? Item::FOLDER : $typeItem;
        $itemSaved = $this->$typeItem->findById($idItem);
        $itemSaved = $itemSaved[$typeItem];

        $strings_add_item = array(
            Item::IMAGE => 'edito la imagen',
            Item::VIDEO => 'edito el video',
            Item::REPORT => 'edito el reporte',
            Item::NOTE => 'edito la nota',
            Item::FOLDER => 'edito el folder',
            Item::FILE =>  'edito el archivo',
        );

        $empresaCliente = $this->Empresa->findById($conveyor['id_company']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();
        $justForLog = 1;
        
        $logNotification = "El usuario %$credentials[name]% ".$strings_add_item[$typeItem]." %$itemSaved[nombre]%";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $idItem, $justForLog, $conveyor['id']);
/*
        $jusForLog = 0;
        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporate('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['nombre'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $jusForLog);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporate('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['nombre'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['numero'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $jusForLog);

                    $company_ids[] = $company['id'];
                }
            }

        }

        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['nombre'] . '%';
            $text_notification .= ' del transportador %' . $conveyor['numero'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $jusForLog);
            
        }

        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role'] != UsuariosEmpresa::IS_CLIENT) {

            $strings_add_item = array(
                Item::IMAGE => 'Se edito la imagen',
                Item::VIDEO => 'Se edito el video',
                Item::REPORT => 'Se edito el reporte',
                Item::NOTE => 'Se edito la nota',
                Item::FOLDER => 'Se edito el folder',
                Item::FILE =>  'Se edito el archivo',
            );

            $text_notification = $strings_add_item[$typeItem] . ' %' . $itemSaved['nombre'] . '%';
            $text_notification .= ' del transportador %' . $conveyor['numero'] . '%';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $jusForLog);

        }
*/
        $this->processQueue();
    }
    
    
    
    
    public function itemDeleted($typeItem, $idItem, $conveyor)
    {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->FolderApp = ClassRegistry::init('FolderApp');
        $this->Archive = ClassRegistry::init('Archive');

        //Obtenemos el elemento recientemente guardado
        $itemSaved = $this->FolderApp->findById($idItem);
        $itemSaved = $itemSaved['FolderApp'];

        $strings_add_item = array(
            Item::IMAGE => 'elimino la imagen',
            Item::VIDEO => 'elimino el video',
            Item::REPORT => 'elimino el reporte',
            Item::NOTE => 'elimino la nota',
            Item::FOLDER => 'elimino el folder',
            Item::FOLDER_FILE => 'elimino el folder de archivo',
            Item::FILE => 'elimino archivos del folder',
        );

        $empresaCliente = $this->Empresa->findById($conveyor['client_id']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();
        $justForLog = 1;

        $logNotification = "El usuario %$credentials[name]% " . $strings_add_item[$typeItem] . " %$itemSaved[name]%";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $idItem, $justForLog, $conveyor['id']);

        $idItem = in_array($typeItem, [Item::FOLDER, Item::FOLDER_FILE]) ? $conveyor['id'] : $idItem;

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role_company'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            $text_notification = "";
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
                    $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);

                    $company_ids[] = $company['id'];
                }
            }

        }

        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role_company'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', ' . $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
            $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);
        }

        //Si quien agrega el elemento es cualquier otro diferente a cliente, hay que generarle la notificacion al cliente
        //de que se la cargoun elemento
        if ($credentials['role_company'] != UsuariosEmpresa::IS_CLIENT) {

            $strings_add_item = array(
                Item::FOLDER => 'Se elimino el folder',
                Item::FOLDER_FILE => 'Se elimino el folder de archivo',
                Item::FILE => 'Se eliminaron archivos del folder',
            );

            $text_notification = $strings_add_item[$typeItem] . ' %' . $itemSaved['name'] . '%';
            $text_notification .= ' del transportador %' . $conveyor['name'] . '%';
            $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, $typeItem, $idItem, $justForLog);

        }
        

        $this->processQueue();
    }
    

    public function reportDeleted($report_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Report = ClassRegistry::init('Report');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $reportSaved = $this->Report->findById($report_id);
        $reportSaved = $reportSaved['Report'];

        $assocConveyor = $this->Conveyor->findById($reportSaved['parent_conveyor']);
        $assocConveyor = $assocConveyor['Conveyor'];

        $empresaCliente = $this->Empresa->findById($assocConveyor['id_company']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();        

        //Solo si el usuario que agrego el elemento es cliente o distribuidor
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el reporte %' . $reportSaved['nombre'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el reporte %' . $reportSaved['nombre'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el reporte %' . $reportSaved['nombre'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false);

                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', elimino el reporte %' . $reportSaved['nombre'] . '%';
                    $text_notification .= ' del cliente %' . $empresaCliente['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, elimino el reporte %s del cliente %s', array($credentials['name'], $credentials['name_company'], $reportSaved['nombre'], $empresaCliente['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }


        //Solo si quien agrego el elemento es cliente, se genera la notificacion a su distribuidor
        if ($credentials['role'] == UsuariosEmpresa::IS_CLIENT) {
            $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
            $text_notification .= ', elimino el reporte %' . $reportSaved['nombre'] . '%';
            $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false);

            /*
             * SEND MAIL NOTIFICATION
             */
            $company_ids = array($empresaDistribuidor['id']);
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
            $mail_notification = __('%s de %s, elimino el reporte %s', array($credentials['name'], $credentials['name_company'], $reportSaved['nombre']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }

        $this->processQueue();
    }
    
    public function ultrasonicUpdated($ultrasonic_id) {        
        $credentials = $this->Core->getAppCredentials();
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $ultrasonicSaved = $this->Ultrasonic->findById($ultrasonic_id);
        $ultrasonic = $ultrasonicSaved['Ultrasonic'];

        $assocConveyor = $this->Conveyor->findById($ultrasonic['conveyor_id']);
        $assocConveyor = $assocConveyor['Conveyor'];

        $current_date = date('Y-m-d H:i:s');
        $justForLog = 1;
        
        $logNotification = "El usuario %$credentials[name]% edito los datos de ultrasonic";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $ultrasonic['id'], $justForLog, $assocConveyor['id']);
        

        $this->processQueue();
    }
    
    public function ultrasonicReadingSaved($ultrasonic_id, $ultrasonic_reading_id) {
        $credentials = $this->Core->getAppCredentials();
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $this->UltrasonicReading = ClassRegistry::init('UltrasonicReading');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $ultrasonicSaved = $this->Ultrasonic->findById($ultrasonic_id);
        $ultrasonic = $ultrasonicSaved['Ultrasonic'];
        
        $ultrasonicReading = $this->UltrasonicReading->findById($ultrasonic_reading_id);
        $ultrasonicReading = $ultrasonicReading['UltrasonicReading'];

        $assocConveyor = $this->Conveyor->findById($ultrasonic['conveyor_id']);
        $assocConveyor = $assocConveyor['Conveyor'];

        $current_date = date('Y-m-d H:i:s');
        $justForLog = 1;
        
        $reading_date = $this->Core->timestampToUsDate($ultrasonicReading['reading_date']);
        $logNotification = "El usuario %$credentials[name]% agrego la lectura %$reading_date% en ultrasonic";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $ultrasonicReading['id'], $justForLog, $assocConveyor['id']);

        $this->processQueue();
    }
    
    public function ultrasonicReadingUpdated($ultrasonic_id, $ultrasonic_reading_id) {
        $credentials = $this->Core->getAppCredentials();
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $this->UltrasonicReading = ClassRegistry::init('UltrasonicReading');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $ultrasonicSaved = $this->Ultrasonic->findById($ultrasonic_id);
        $ultrasonic = $ultrasonicSaved['Ultrasonic'];
        
        $ultrasonicReading = $this->UltrasonicReading->findById($ultrasonic_reading_id);
        $ultrasonicReading = $ultrasonicReading['UltrasonicReading'];

        $assocConveyor = $this->Conveyor->findById($ultrasonic['conveyor_id']);
        $assocConveyor = $assocConveyor['Conveyor'];

        $current_date = date('Y-m-d H:i:s');
        $justForLog = 1;
        
        $reading_date = $this->Core->timestampToUsDate($ultrasonicReading['reading_date']);
        $logNotification = "El usuario %$credentials[name]% edito los datos de la lectura %$reading_date% en ultrasonic";
        $this->addToQueueLog(0, $current_date, $logNotification, $programmed = false, 'Log', $ultrasonicReading['id'], $justForLog, $assocConveyor['id']);

        $this->processQueue();
    }

    public function companySaved($company_id) {
        $credentials = $this->Core->getAppCredentials();
        $this->_credentials = $credentials;

        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');

        $companySaved = $this->Empresa->findById($company_id);
        $companySaved = $companySaved['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        //Se obtienen todos los masters a quien se les enviara la notificacion
        //En masters no se checa region        
        $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
        //Obtener todas las empresas admins correspondientes a esa region
        $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $companySaved['region']);

        //Solo si el usuario que agrego el elemento es distribuidor        
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_DIST))) {
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = 'El distribuidor %' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego al cliente %' . $companySaved['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, UsuariosEmpresa::IS_CLIENT, $companySaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $companySaved['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = 'El distribuidor %' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego al cliente %' . $companySaved['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, UsuariosEmpresa::IS_CLIENT, $companySaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $companySaved['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = 'El distribuidor %' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego al cliente %' . $companySaved['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, UsuariosEmpresa::IS_CLIENT, $companySaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = 'El distribuidor %' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                    $text_notification .= ', agrego al cliente %' . $companySaved['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, UsuariosEmpresa::IS_CLIENT, $companySaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('El distribuidor %s de %s, agrego al cliente %s', array($credentials['name'], $credentials['name_company'], $companySaved['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        } else if (in_array($credentials['role'], array(UsuariosEmpresa::IS_ADMIN))) { //Si es admin, solo notificar a los masters y a los dist cuando es cliente
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = 'El administrador %' . $credentials['name'] . '% de la region %' . $credentials['region'] . '%';
                    $text_notification .= ', agrego la empresa %' . $companySaved['name'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $companySaved['type'], $companySaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('El administrador %s de la region %s, agrego la empresa %s', array($credentials['name'], $credentials['region'], $companySaved['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }

            //Si la empresa que agrego el admin es cliente, mandarle una notificacion a su distribuidor
            if ($companySaved['type'] == UsuariosEmpresa::IS_CLIENT) {
                $text_notification = '%' . $credentials['name'] . '% de %' . $credentials['name_company'] . '%';
                $text_notification .= ', agrego la empresa %' . $companySaved['name'] . '%';
                $this->addToQueue($companySaved['parent'], $current_date, $text_notification, $programmed = false, UsuariosEmpresa::IS_CLIENT, $companySaved['id']);

                /*
                 * SEND MAIL NOTIFICATION
                 */
                $company_ids = array($companySaved['parent']);
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('%s de %s, agrego la empresa %s', array($credentials['name'], $credentials['name_company'], $companySaved['name']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }

        $this->processQueue();
    }

    public function newsSaved($news_id) {

        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Noticia = ClassRegistry::init('Noticia');

        $noticiaSaved = $this->Noticia->findById($news_id);
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        //Obtener todas la empresas de tipo dist sin importar su region
        $distribuidores = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications(UsuariosEmpresa::IS_DIST);
        //Obtener todas la empresas de tipo client sin importar su region
        $clientes = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications(UsuariosEmpresa::IS_CLIENT);

        //Solo si el usuario que agrego la noticia es master
        if (in_array($credentials['role'], array(UsuariosEmpresa::IS_MASTER))) {
            if (!empty($distribuidores)) {
                foreach ($distribuidores AS $dist_company) {
                    $company = $dist_company['Empresa'];
                    $text_notification = 'Se agrego la noticia %' . $noticiaSaved['titulo'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, 'Noticia', $noticiaSaved['id']);

                    $company_ids[] = $company['id'];
                }
            }

            if (!empty($clientes)) {
                foreach ($clientes AS $client_company) {
                    $company = $client_company['Empresa'];
                    $text_notification = 'Se agrego la noticia %' . $noticiaSaved['titulo'] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, 'Noticia', $noticiaSaved['id']);
                }
            }

            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('Se agrego la noticia %s', array($noticiaSaved['titulo']));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }
        }

        $this->processQueue();
    }
    
    public function userHasBeenLocked($user_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->LockingLog = ClassRegistry::init('LockingLog');

        $lockedUser = $this->UsuariosEmpresa->findById($user_id);
        $lockedUser = $lockedUser['UsuariosEmpresa'];
                
        $empresaUser = $this->Empresa->findById($lockedUser['id_empresa']);
        $empresaUser = $empresaUser['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();

        $reason_lock = array(
            UsuariosEmpresa::IS_TEMPORARY_LOCKED => 'temporalmente por intentos fallidos al proporcionar una contrasena',
            UsuariosEmpresa::IS_MISSED_GEO => 'intentos de login en diferentes regiones en un periodo corto de tiempo',
            UsuariosEmpresa::IS_PERMANENTLY_LOCKED => 'intentos fallidos al proporcionar una contrasena'
        );

        
        if(in_array($lockedUser['lock_status'], array(UsuariosEmpresa::IS_TEMPORARY_LOCKED, UsuariosEmpresa::IS_MISSED_GEO, UsuariosEmpresa::IS_PERMANENTLY_LOCKED))){
            //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region        
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $lockedUser['region']);

            //Solo si el usuario que agrego el elemento es distribuidor        
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = 'El usuario %' . $lockedUser['name'] . '% de %' . $empresaUser['name'] . '%';
                    $text_notification .= ', fue bloqueado por %' . $reason_lock[$lockedUser['lock_status']] . '%';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                    $company_ids[] = $company['id'];
                }
            }

            if (in_array($lockedUser['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {

                //Obtener todas las empresas country manager correspondientes
                $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaUser['i_country_id']);
                if (!empty($countryManagers)) {
                    foreach ($countryManagers AS $country_company) {
                        $company = $country_company['Empresa'];
                        $text_notification = 'El usuario %' . $lockedUser['name'] . '% de %' . $empresaUser['name'] . '%';
                        $text_notification .= ', fue bloqueado por %' . $reason_lock[$lockedUser['lock_status']] . '%';
                        $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                        $company_ids[] = $company['id'];
                    }
                }

                //Obtener todas las empresas region manager correspondientes
                $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaUser['region_id']);
                if (!empty($regionManagers)) {
                    foreach ($regionManagers AS $region_company) {
                        $company = $region_company['Empresa'];
                        $text_notification = 'El usuario %' . $lockedUser['name'] . '% de %' . $empresaUser['name'] . '%';
                        $text_notification .= ', fue bloqueado por %' . $reason_lock[$lockedUser['lock_status']] . '%';
                        $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                        $company_ids[] = $company['id'];
                    }
                }

                if (!empty($admins)) {
                    foreach ($admins AS $admin_company) {
                        $company = $admin_company['Empresa'];
                        $text_notification = 'El usuario %' . $lockedUser['name'] . '% de %' . $empresaUser['name'] . '%';
                        $text_notification .= ', fue bloqueado por %' . $reason_lock[$lockedUser['lock_status']] . '%';
                        $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                        $company_ids[] = $company['id'];
                    }
                }
            }

            $geolocalization = $this->Session->read('geolocalization_data');
            $geolocalization = $geolocalization['country'].'|'.$geolocalization['country_code'].'|'.$geolocalization['state'].'|'.$geolocalization['state_code'];
            $this->LockingLog->save(array('LockingLog'=>array('user_id'=>$lockedUser['id'],'reason'=>$text_notification,'location'=>$geolocalization)));
            
            /*
             * SEND MAIL NOTIFICATION
             */
            if (!empty($company_ids)) {
                //generar notificacion para todos los usuarios menos para el usuario implicado en la notificacion
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('UsuariosEmpresa.id !=' => $lockedUser['id'],'id_empresa' => $company_ids,'UsuariosEmpresa.id')));
                $reason = __($reason_lock[$lockedUser['lock_status']],true);
                $mail_notification = __('El usuario %s de %s, fue bloqueado por %s', array($lockedUser['name'], $empresaUser['name'], $reason));
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }


            $this->processQueue();
        }
    }

    public function alertCriticUltrasonicConveyor($conveyor_id, $life) {
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $conveyorSaved = $this->Conveyor->findById($conveyor_id);
        $conveyorSaved = $conveyorSaved['Conveyor'];

        $empresaCliente = $this->Empresa->findById($conveyorSaved['id_company']);
        $empresaCliente = $empresaCliente['Empresa'];
        $empresaDistribuidor = $this->Empresa->findById($empresaCliente['parent']);
        $empresaDistribuidor = $empresaDistribuidor['Empresa'];
        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();


        //Se obtienen todos los masters a quien se les enviara la notificacion
            //En masters no se checa region
            $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
            if (!empty($masters)) {
                foreach ($masters AS $master_company) {
                    $company = $master_company['Empresa'];
                    $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% de la empresa %'.$empresaCliente['name'].'% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su cliente.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaCliente['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% de la empresa %'.$empresaCliente['name'].'% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su cliente.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaCliente['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% de la empresa %'.$empresaCliente['name'].'% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su cliente.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }


            //Obtener todas las empresas admins correspondientes a esa region
            $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $empresaCliente['region']);
            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% de la empresa %'.$empresaCliente['name'].'% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su cliente.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);
                    $company_ids[] = $company['id'];
                }
            }

            //SEND MAIL NOTIFICATION
            if (!empty($company_ids)) {
                $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
                $mail_notification = __('A la banda del transportador %s de la empresa %s le queda un tiempo estimado de vida de %s. Favor de contactar a su cliente.',[$conveyorSaved['numero'], $empresaCliente['name'], $life]);
                $this->Mail->sendMailNotification($users_to_send, $mail_notification);
                $this->Mail->sendPushNotification($users_to_send, $mail_notification);
            }



        //SEND TO DIST
        //Web notif
        $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% de la empresa %'.$empresaCliente['name'].'% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su cliente.';
        $this->addToQueue($empresaDistribuidor['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

        //SEND MAIL NOTIFICATION and push
        $company_ids = array($empresaDistribuidor['id']);
        $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
        $mail_notification = __('A la banda del transportador %s de la empresa %s le queda un tiempo estimado de vida de %s. Favor de contactar a su cliente.',[$conveyorSaved['numero'], $empresaCliente['name'], $life]);
        $this->Mail->sendMailNotification($users_to_send, $mail_notification);
        $this->Mail->sendPushNotification($users_to_send, $mail_notification);


        //Send to CLIENT
        //Web notif
        $text_notification = 'A la banda del transportador %' . $conveyorSaved['numero'] . '% le queda un tiempo estimado de vida de %'.$life.'%. Favor de contactar a su distribuidor.';
        $this->addToQueue($empresaCliente['id'], $current_date, $text_notification, $programmed = false, Item::CONVEYOR, $conveyorSaved['id']);

        //SEND MAIL NOTIFICATION and push
        $company_ids = array($empresaCliente['id']);
        $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $company_ids)));
        $mail_notification = __('A la banda del transportador %s le queda un tiempo estimado de vida de %s. Favor de contactar a su distribuidor.',[$conveyorSaved['numero'], $life]);
        $this->Mail->sendMailNotification($users_to_send, $mail_notification);
        $this->Mail->sendPushNotification($users_to_send, $mail_notification);


        $this->processQueue();
        $this->_notificationsQueue = []; //clean queue
    }

    public function userHasLoggedFromMissingCountry($user_id) {
        $credentials = $this->Core->getAppCredentials();
        $filter_region = $this->Core->_global_region;
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->LockingLog = ClassRegistry::init('LockingLog');

        $lockedUser = $this->UsuariosEmpresa->findById($user_id);
        $lockedUser = $lockedUser['UsuariosEmpresa'];

        $empresaUser = $this->Empresa->findById($lockedUser['id_empresa']);
        $empresaUser = $empresaUser['Empresa'];

        $current_date = date('Y-m-d H:i:s');
        $company_ids = array();


        //Se obtienen todos los masters a quien se les enviara la notificacion
        //En masters no se checa region
        $masters = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('master');
        //Obtener todas las empresas admins correspondientes a esa region
        $admins = $this->Empresa->findByRegionAndTypeWithCorporateForNotifications('admin', $lockedUser['region']);

        //Solo si el usuario que agrego el elemento es distribuidor
        if (!empty($masters)) {
            foreach ($masters AS $master_company) {
                $company = $master_company['Empresa'];
                $text_notification = 'El usuario %' . $lockedUser['name'] . '% de la empresa %' . $empresaUser['name'] . '%';
                $text_notification .= ' entro al sistema desde un pais distinto al que pertenece. Favor de verificar.';
                $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                $company_ids[] = $company['id'];
            }
        }

        if (in_array($lockedUser['role'], array(UsuariosEmpresa::IS_CLIENT, UsuariosEmpresa::IS_DIST))) {

            //Obtener todas las empresas country manager correspondientes
            $countryManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_country', $empresaUser['i_country_id']);
            if (!empty($countryManagers)) {
                foreach ($countryManagers AS $country_company) {
                    $company = $country_company['Empresa'];
                    $text_notification = 'El usuario %' . $lockedUser['name'] . '% de la empresa %' . $empresaUser['name'] . '%';
                    $text_notification .= ' entro al sistema desde un pais distinto al que pertenece. Favor de verificar.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                    $company_ids[] = $company['id'];
                }
            }

            //Obtener todas las empresas region manager correspondientes
            $regionManagers = $this->Empresa->findByCountryRegionWithCorporateForNotifications('bucket_region', $country=null, $empresaUser['region_id']);
            if (!empty($regionManagers)) {
                foreach ($regionManagers AS $region_company) {
                    $company = $region_company['Empresa'];
                    $text_notification = 'El usuario %' . $lockedUser['name'] . '% de la empresa %' . $empresaUser['name'] . '%';
                    $text_notification .= ' entro al sistema desde un pais distinto al que pertenece. Favor de verificar.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                    $company_ids[] = $company['id'];
                }
            }

            if (!empty($admins)) {
                foreach ($admins AS $admin_company) {
                    $company = $admin_company['Empresa'];
                    $text_notification = 'El usuario %' . $lockedUser['name'] . '% de la empresa %' . $empresaUser['name'] . '%';
                    $text_notification .= ' entro al sistema desde un pais distinto al que pertenece. Favor de verificar.';
                    $this->addToQueue($company['id'], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

                    $company_ids[] = $company['id'];
                }
            }
        }

        //Send to dist
        if (in_array($lockedUser['role'], array(UsuariosEmpresa::IS_CLIENT))) {
            $text_notification = 'El usuario %' . $lockedUser['name'] . '% de la empresa %' . $empresaUser['name'] . '%';
            $text_notification .= ' entro al sistema desde un pais distinto al que pertenece. Favor de verificar.';
            $this->addToQueue($empresaUser["parent"], $current_date, $text_notification, $programmed = false, $lockedUser['role'], $lockedUser['id']);

            $company_ids[] = $empresaUser["parent"];
        }

        /*
         * SEND MAIL NOTIFICATION
         */
        if (!empty($company_ids)) {
            //generar notificacion para todos los usuarios menos para el usuario implicado en la notificacion
            $users_to_send = $this->UsuariosEmpresa->find('all', array('conditions' => array('UsuariosEmpresa.id !=' => $lockedUser['id'],'UsuariosEmpresa.id_empresa' => $company_ids,'UsuariosEmpresa.id')));
            $mail_notification = __('El usuario %s de la empresa %s entro al sistema desde un pais distinto al que pertenece. Favor de verificar.', array($lockedUser['name'], $empresaUser['name']));
            $this->Mail->sendMailNotification($users_to_send, $mail_notification);
            $this->Mail->sendPushNotification($users_to_send, $mail_notification);
        }


        $this->processQueue();
    }

}
