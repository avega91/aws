<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file AccessController.php
 *     Management system access
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class AccessController extends AppController {

    public function beforeFilter() {
         parent::beforeFilter();

         $this->Auth->allow('login', 'logout', 'ajax_login', 'recaptcha_reload', 'ax_set_position', 'ax_set_message');
         $this->layout = 'login';
         $this->set('title_for_layout', 'LOGIN');

     }

     public function index() {
         $this->layout = false;
         $this->autoRender = false;
         //$this->doLogout();
         //$this->redirect($this->Auth->loginAction);

         if (!is_null($this->Auth->user())) {
             //$this->doLogout();
             $this->redirect($this->Auth->logoutRedirect);
         } else {
             $this->redirect($this->Auth->loginAction);
         }
     }

     public function ajax_login() {
         $this->layout = false;
         $response = array();
         $response['logged'] = false;
         $response['msg'] = '';

         if (is_null($this->Auth->user())) {//Si no esta ya logueado
             if ($this->request->is('post')) {
                 //$data_login = $this->request->data;
                 $data_login = array();
                 parse_str($this->request->data['formdata'], $data_login);
                 $fingerprint = isset($this->request->data['fingerprint']) ? $this->request->data['fingerprint'] : "";
                 $forceLogin = isset($this->request->data['force']) ? $this->request->data['force'] : false;

                 //mail("elalbertgd@gmail.com",'captcha',print_r([$data_login['s3capcha'], $this->Session->read('s3capcha')],true));

                 //if (isset($data_login['s3capcha'], $_SESSION['s3capcha']) && $data_login['s3capcha'] == $_SESSION['s3capcha'] && $data_login['s3capcha'] != '') {
                 if ((isset($data_login['s3capcha']) && $this->Session->check('s3capcha') && $data_login['s3capcha'] == $this->Session->read('s3capcha') && $data_login['s3capcha'] != '')) {

                     $username = $data_login[_NAME_FIELD_MODEL];
                     $password = $data_login[_PASSWORD_FIELD_MODEL];

                     $geolocalization = $this->Secure->getGeolocalizationData();
                     $this->Session->write('geolocalization_data', $geolocalization);

                     //$usuario = $this->User->isAuth($username, $password);
                     $usuario = $this->UsuariosEmpresa->isAuth($username, $password);
                     if (!empty($usuario) && $this->Auth->login($usuario)) {
                         $usuario = $this->UsuariosEmpresa->findById($usuario[0]['UsuariosEmpresa']['id']);

                         /*
                         if ($usuario['UsuariosEmpresa']['fingerprint'] == "") {
                             $usuario['UsuariosEmpresa']['fingerprint'] = $fingerprint;
                             $this->UsuariosEmpresa->save($usuario);
                         }

                         if ($usuario['UsuariosEmpresa']['fingerprint'] == $fingerprint) {*/

                            $site = substr($this->site, 0, -1);
                            $response['msg'] = __('session_init', true);
                            $response['logged'] = true;
                            $response['redir'] = $site . $this->Auth->loginRedirect;
                            $response['token'] = sha1($username);

                            $error_login = false;
                            $response['needsAuthForFingerprint'] = false;
                            $response['needsAnswerQuestion'] = false;
                            $response['needContactAdmin'] = false;
                            if($usuario['UsuariosEmpresa']['fingerprint'] == ""){ //user has no auth fingerprint for his device and fingerprint not comes correctly
                                if($forceLogin === false){
                                    $fingerPrintRequired = __('For security reason, your account will be associated to this device and it will not be possible to use another computer to access the system.', true);
                                    //$this->Session->write('auth_fingerprint', $fingerPrintRequired);
                                    $this->Session->write('
                                    ', $fingerprint);
                                    $response['msg'] = $fingerPrintRequired;
                                    $response['needsAuthForFingerprint'] = true;
                                    $error_login = true;
                                }else{ //force login, setting up the finger print
                                    $usuario['UsuariosEmpresa']['fingerprint'] = $fingerprint;
                                    $this->UsuariosEmpresa->save($usuario);
                                    //unset($_SESSION['s3capcha']);
                                    $this->Session->delete('s3capcha');
                                }
                            }else{ //check differences in fingerprint
                                if ($usuario['UsuariosEmpresa']['fingerprint'] != $fingerprint) {
                                    if($forceLogin === false) {
                                        $error_login = true;
                                        $response['needsAnswerQuestion'] = true;
                                        $secureUserParams = $this->Core->encodeParams($usuario['UsuariosEmpresa']['id']);
                                        $response['userId'] = implode('||', $secureUserParams);
                                    }else{
                                        $usuario['UsuariosEmpresa']['fingerprint'] = $fingerprint;
                                        $usuario['UsuariosEmpresa']['attempts_answer'] = 0;
                                        $this->UsuariosEmpresa->save($usuario);
                                        //unset($_SESSION['s3capcha']);
                                        $this->Session->delete('s3capcha');
                                    }
                                }else{
                                    $usuario['UsuariosEmpresa']['attempts_answer'] = 0;
                                    $this->UsuariosEmpresa->save($usuario);
                                    //unset($_SESSION['s3capcha']);
                                    $this->Session->delete('s3capcha');
                                }
                            }


                            $last_login_info = $this->Statistic->find('first', array(
                                    'conditions' => array('user_id' => $usuario['UsuariosEmpresa']['id'], 'section' => _SITE_ACCESS),
                                    'order' => array('date' => 'desc')
                                )
                            );

                            /*
                             * Se verifica el ultimo login, para checar si no se esta logueando de diferentes regiones
                             */
                            if (!empty($last_login_info) && !$error_login) {
                                //unset($_SESSION['s3capcha']);
                                $this->Session->delete('s3capcha');

                                $last_login_info = $last_login_info['Statistic'];
                                $to_time = strtotime(date('Y-m-d H:i:s'));
                                $from_time = strtotime($last_login_info['date']);
                                $minutes_dif = round(abs($to_time - $from_time) / 60, 2);


                                //Si accedio en 2 regiones diferentes en un periodo corto de tiempo
                                if ($last_login_info['device'] == _DESKTOP_DEVICE && $minutes_dif <= _LOCK_TIME && $geolocalization['state'] != $last_login_info['state']) {
                                    $usuario['UsuariosEmpresa']['lock_status'] = UsuariosEmpresa::IS_MISSED_GEO;
                                    $this->UsuariosEmpresa->save($usuario);
                                    $notification_text = __('El usuario %s fue bloqueado por intentos de login en diferentes regiones en un periodo corto de tiempo. Verifique posible robo de credenciales.', array($usuario['UsuariosEmpresa']['name']));
                                    $this->Session->write('geo_error', $notification_text);
                                    $error_login = true;
                                    $response['redir'] = $site . $this->Auth->logout();
                                    $response['msg'] = '';

                                    /*
                                     * Guardamos la notificacion *
                                     * ************************** */
                                    $this->Notifications->userHasBeenLocked($usuario['UsuariosEmpresa']['id']);
                                    /*
                                     * Guardamos log de navegacion
                                     */
                                    $this->Secure->saveBrowsingData(BrowsingLog::ACCESO, 'UsuariosEmpresa', $usuario['UsuariosEmpresa']['name'], $succes = false, $usuario['UsuariosEmpresa']['id']);
                                }
                            }


                            /*
                             * Si no hubo error al hacer login
                             */
                            if (!$error_login) {
                                //unset($_SESSION['s3capcha']);
                                $this->Session->delete('s3capcha');

                                //Checar region usuario y region logueo
                                if($usuario['UsuariosEmpresa']['role']!=UsuariosEmpresa::IS_MASTER){ //just if user role is not master
                                    $country_code_tracked = $geolocalization['country_code'];
                                    if(!is_numeric($country_code_tracked) && strlen($country_code_tracked)==2) { //if is text and value length is 2, then is valid country code
                                        $country_code_user = $this->Core->getRealRegion($usuario['UsuariosEmpresa']['region']);
                                        $cenamCodes = ["AR", "BZ", "BO", "CO", "CR", "CU", "EC", "SV", "GT", "HT", "HN", "NI", "PA", "PY", "PE", "PR", "DO", "UY", "VE"];
                                        $countryExclussion = ["DE"];
                                        if (!in_array($country_code_tracked, $countryExclussion)) { //Checar si el codigo trackedado no esta dentro de las exclusiones
                                            if ($country_code_user == "CENAM") { //la region del usuario es cenam, checar si el pais desde donde se logueo pertenece a cenam
                                                if (!in_array($country_code_tracked, $cenamCodes)) {
                                                    //create notif
                                                    $this->Notifications->userHasLoggedFromMissinCountry($usuario['UsuariosEmpresa']['id']);
                                                }
                                            } else if ($country_code_tracked != $country_code_user) { //si el codigo trackeado es diferente al codigo del pais del usuario
                                                //create notif
                                                $this->Notifications->userHasLoggedFromMissingCountry($usuario['UsuariosEmpresa']['id']);
                                            }
                                        }
                                    }
                                }


                                $this->refreshCredentials();
                                $this->Core->setAppCredentials($this->credentials);
                                //Clean access info and set to logged_in
                                //$usuario['UsuariosEmpresa']['logged_in'] = 1; //Problemas deslogueo por perdida de sesion en peticiones ajax
                                $usuario['UsuariosEmpresa']['access_attempts'] = 0;
                                $usuario['UsuariosEmpresa']['last_access_attempt'] = date('Y-m-d H:i:s');
                                $this->UsuariosEmpresa->save($usuario);

                                /*
                                 * Se guarda el registro de acceso
                                 * Save statistic browsing data
                                 */
                                $this->Secure->saveStatisticData($usuario['UsuariosEmpresa']['id'], Statistic::GO_SITE, $geolocalization);
                                /*
                                 * Guardamos log de navegacion
                                 */
                                $this->Secure->saveBrowsingData(BrowsingLog::ACCESO, 'UsuariosEmpresa', $usuario['UsuariosEmpresa']['name']);
                            } else {
                                $this->Auth->logout();
                            }
                           /*
                        }else{
                            $this->Auth->logout();
                            $response['msg'] = __("Esta usando un equipo diferente al habitual, por seguridad inicie sesion desde su equipo.", true);
                        }*/
                    } else {
                        $response['msg'] = $this->Secure->manageLoginTries($username, $password) . '<br>IP: ' . $geolocalization['ip'];
                        $this->Secure->addActionLog('LOGIN', 'Login', 'log_user_try_login_in_system', $username);
                        //manejar los registros de inicio de session erroneo
                        //$response['msg'] = __('Usuario y/o Contrase&ntilde;a son incorrectas.', true);
                    }
                } else {
                    $response['msg'] = __('La imagen seleccionada es incorrecta.', true);
                }
            } else {
                $this->redirect($this->Auth->loginRedirect);
            }
        } else {
            $response['msg'] = __('session_init', true);
            $response['logged'] = true;
            $response['redir'] = $this->site . $this->Auth->loginRedirect;
            $response['token'] = sha1($this->credentials['username']);
        }
        $this->set('response', $response);

    }

    public function recaptcha_reload() {
        $this->layout = 'ajax';
    }

    public function login() {

        $this->setJsVar('urlLoginAx', $this->_html->url(array('controller' => 'Access', 'action' => 'ajax_login')));
        $this->setJsVar('urlRefreshCaptchaAx', $this->_html->url(array('controller' => 'Access', 'action' => 'recaptcha_reload')));
        //$this->setJsVar('urlPositionAx', $this->_html->url(array('controller' => 'Access', 'action' => 'ax_set_position')));
        $this->setJsVar('urlPositionAx', '/locatedevice');

        $this->jsToInclude[] = 'application/Access/action';
        $this->set('jsToInclude', $this->jsToInclude);


        //$this->Session->setFlash(__('share_location_required', true));
        
        //Mensajes para cuando se cierra por inactividad
        if ($this->Session->check('expire_session')) {
            $this->Session->setFlash($this->Session->read('expire_session'));
            $this->Session->delete('expire_session');
        } else if ($this->Session->check('geo_error')) {
            $this->Session->setFlash($this->Session->read('geo_error'));
            $this->Session->delete('geo_error');
        } else if ($this->Session->check('auth_fingerprint')) {
            $this->Session->setFlash($this->Session->read('auth_fingerprint'));
            $this->Session->delete('auth_fingerprint');
        }


        if (is_null($this->Auth->user())) {//Si usuario null, esta deslogueado, continuar
            if ($this->request->is('post')) {
                $data_login = $this->request->data;
                //mail("elalbertgd@gmail.com","se procede a hacer login",print_r($data_login, true));
                if (isset($data_login['s3capcha']) && $this->Session->check('s3capcha') && $data_login['s3capcha'] == $this->Session->read('s3capcha') && $data_login['s3capcha'] != '') {
                    //unset($_SESSION['s3capcha']);
                    $this->Session->delete('s3capcha');
                    $username = $data_login[_NAME_FIELD_MODEL];
                    $password = $data_login[_PASSWORD_FIELD_MODEL];
                    $result = $this->doLogin($username, $password);
                } else {
                    $this->Session->setFlash(__('La imagen seleccionada es incorrecta.', true));
                    $this->redirect($this->Auth->loginAction);
                }
            }
        } else {
            //mail("elalbertgd@gmail.com","se encontro usuario en login, hubo problema en deslogueo",print_r(array("user"=>$this->Auth->user(),"x"=>1), true));
            $this->redirect($this->Auth->loginRedirect);
        }
    }

    public function logout() {
        $this->layout = false;
        $this->autoRender = false;
        $this->Session->delete(self::APP_REGION);

        $this->Session->delete('LATI');
        $this->Session->delete('LONG');

        //if (!is_null($this->Auth->user())) {//Si efectivamente esta logueado
        if ($this->Auth->loggedIn()) {//Si efectivamente esta logueado
            $id_usuario = $this->credentials['id'];
            $usuario = $this->UsuariosEmpresa->findById($id_usuario);
            if (!empty($usuario)) {
                //mail("elalbertgd@gmail.com","se encontro usuario en bd, actualizar datos","-");
                //marcamos el usuario como deslogueado
                $usuario['UsuariosEmpresa']['logged_in'] = 0;
                $this->UsuariosEmpresa->save($usuario);
                $this->Session->delete(Statistic::GO_CONVEYORS);
                $this->Session->delete(Statistic::GO_CLIENTS);
                $this->Session->delete(Statistic::GO_TOOLS);
                $this->Session->delete(Statistic::GO_NEWS);
                $this->Session->delete(Statistic::GO_HELP);
                $this->Session->delete(self::APP_LANGUAGE);
                $this->Session->delete(self::COUNTRY_USER);
                
                $this->Session->delete('geolocalization_data');
            }

            //Manage automatic tab logout
            setcookie('USER_UNIQ', '', -1, '/', _DOMAIN_COOKIE, false, false);
            setcookie('USERTYP', '', -1, '/', _DOMAIN_COOKIE, false, false);

            $this->redirect($this->Auth->logout());
        } else {
           $this->redirect($this->Auth->loginAction);
        }
    }

    protected function doLogout(){
        if (!is_null($this->Auth->user())) {//Si efectivamente esta logueado
            //mail("elalbertgd@gmail.com","Esta logueado se procede con deslogueo","-");
            $id_usuario = $this->credentials['id'];
            $usuario = $this->UsuariosEmpresa->findById($id_usuario);
            if (!empty($usuario)) {
                //mail("elalbertgd@gmail.com","se encontro usuario en bd, actualizar datos","-");
                //marcamos el usuario como deslogueado
                $usuario['UsuariosEmpresa']['logged_in'] = 0;
                $this->UsuariosEmpresa->save($usuario);
                $this->Session->delete(Statistic::GO_CONVEYORS);
                $this->Session->delete(Statistic::GO_CLIENTS);
                $this->Session->delete(Statistic::GO_TOOLS);
                $this->Session->delete(Statistic::GO_NEWS);
                $this->Session->delete(Statistic::GO_HELP);
                $this->Session->delete(self::APP_LANGUAGE);
                $this->Session->delete(self::COUNTRY_USER);

                $this->Session->delete('geolocalization_data');
            }

            //Manage automatic tab logout
            setcookie('USER_UNIQ', '', -1, '/', _DOMAIN_COOKIE, false, false);
            setcookie('USERTYP', '', -1, '/', _DOMAIN_COOKIE, false, false);

            //$this->Auth->logout();
            //mail("elalbertgd@gmail.com","se encontro usuario",print_r(array("user"=>$this->Auth->user(),"x"=>1), true));
            //$this->redirect($this->site);
            //$this->Auth->logout();
            //$this->redirect($this->Auth->loginAction);
            //$this->redirect($this->Auth->logout());
            //header('Refresh: 0; URL='.$this->site);
            $this->Auth->logout();
        }
    }

    protected function doLogin($username, $password) {
        $usuario = $this->User->isAuth($username, $password);
        if ($usuario && $this->Auth->login($usuario)) {
            $this->redirect($this->Auth->loginRedirect);
//            $this->redirect($redirects[$group_user]);
        } else {
            $this->Session->setFlash(__('Usuario y/o Contrase&ntilde;a son incorrectas.', true));
            $this->redirect($this->Auth->loginAction);
        }
    }

    public function ax_set_message() {
        $this->layout = false;
        $this->autoRender = false;
        $msg = '';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if (isset($data['m']) && $data['m'] == 1) {
                $msg = __('inactivity_closed_session_msg', true);
            }
        }
        $this->Session->write('expire_session', $msg);
    }

    public function ax_set_position() {
        $this->layout = false;
        $this->autoRender = false;

        if ($this->request->is('post')) {
            $data = $this->request->data;
            $lat = $data['latitude'];
            $lon = $data['longitude'];
            $this->Session->write('LATI', $lat);
            $this->Session->write('LONG', $lon);
                        
            //$this->Session->write(self::APP_REGION, $data['region']);                
        } else {
            $this->redirect($this->Auth->loginRedirect);
        }
    }

}
