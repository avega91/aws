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
 *     @date      2017
 */

class SignController extends AppController {

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('login');
        $this->layout = 'login';
    }

    public function login() {
        $this->set('title_for_layout', 'Log In');
        $this->setJsVar('urlLoginAx', $this->_html->url(array('controller' => 'Sign', 'action' => 'ajax_login')));
        $this->setJsVar('urlRefreshCaptchaAx', $this->_html->url(array('controller' => 'Sign', 'action' => 'recaptcha_reload')));
        $this->setJsVar('urlPositionAx', 'locatedevice');

        $this->set("loginAction",$this->_html->url(array('controller' => 'Sign', 'action' => 'signin')));
        $this->jsToInclude[] = 'application/Sign/action';
        $this->set('jsToInclude', $this->jsToInclude);


        //Confirm fingerprint for current device
        $needsFingerprintMsg = "";
        $currentUser = "";
        $currentPass = "";
        if($this->Session->check('needsAuthForFingerprint')){
            $this->setJsVar('needsAuthForFingerprint', true);
            $needsFingerprintMsg = $this->Session->read('needsAuthForFingerprint');
            $currentUser = $this->Session->read('currentUser');
            $currentPass = $this->Session->read('currentPass');

            $this->Session->delete('needsAuthForFingerprint');
            $this->Session->delete('currentUser');
            $this->Session->delete('currentPass');
        }
        $this->set('fingerprintConfirmMsg',$needsFingerprintMsg);
        $this->set('currentUser',$currentUser);
        $this->set('currentPass',$currentPass);

        /*
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
        }*/

        /*//if already logged-in, redirect
        if($this->Session->check('Auth.User')){
            $this->redirect(array('action' => 'index'));
        }*/

        /*
        // if we get the post information, try to authenticate
        if ($this->request->is('post')) {
            $data = $this->request->data;
            parse_str($data['formdata'], $data_login);
            $this->Session->setFlash($data);

            if ($this->Auth->login()) {
                $this->Session->setFlash(__('Welcome, '. $this->Auth->user('username')));
                $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Session->setFlash(__('Invalid username or password'));
            }
        }
        */
    }

    public function signin(){
        $this->layout = false;
        $this->autoRender = false;
        $login_successfull = false;
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $fingerprint = isset($data['fingerprint']) ? $data['fingerprint'] : "";
            $forceLogin = isset($data['force']) && $data['force']!="" ? true : false;

            if(isset($data['username'],$data['password']) && trim($data['username'])!='' && $data['password']!='') {
                if ((isset($data['g-recaptcha-response']) && $data['g-recaptcha-response'] != '') || $_SERVER['SERVER_NAME'] == 'localhost' || $forceLogin) {
                    $captcha = isset($data['g-recaptcha-response']) ? $data['g-recaptcha-response'] : "";
                    $responseCaptcha = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcuzOcSAAAAAGaJRxK1_aPBJp1MZO6F-R8oj-qM&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
                    $secureVerification = json_decode($responseCaptcha, true);
                    if ($secureVerification['success'] || $_SERVER['SERVER_NAME'] == 'localhost' || $forceLogin) {
                        $username = $data['username'];
                        $password = $data['password'];

                        //get localization info
                        $geolocalization = [];
                        if ($this->Session->check('geolocalization_data')) {
                            $geolocalization = $this->Session->read('geolocalization_data');
                        } else {
                            $geolocalization = $this->Secure->getGeolocalizationData();
                            $this->Session->write('geolocalization_data', $geolocalization);
                        }

                        $usuario = $this->UsuariosEmpresa->isAuth($username, $password);
                        if (!empty($usuario) && $this->Auth->login($usuario)) {
                            $usuario = $this->UsuariosEmpresa->findById($usuario[0]['UsuariosEmpresa']['id']);
                            $site = substr($this->site, 0, -1);
                            $error_login = false;

                            if($usuario['UsuariosEmpresa']['fingerprint'] == "") { //user has no auth fingerprint for his device and fingerprint not comes correctly
                                if($forceLogin === false){
                                    $fingerPrintRequired = __('For security reason, your account will be associated to this device and it will not be possible to use another computer to access the system.', true);
                                    $this->Session->write('needsAuthForFingerprint', $fingerPrintRequired);
                                    $this->Session->write('currentUser', $username);
                                    $this->Session->write('currentPass', $password);
                                    $error_login = true;
                                }else{ //force login, setting up the finger print
                                    $usuario['UsuariosEmpresa']['fingerprint'] = $fingerprint;
                                    $this->UsuariosEmpresa->save($usuario);
                                }
                            }/*else{ //check differences in fingerprint
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
                            }*/


                            //Get Info of Last Access (for check login in different regions in a short period of time)
                            $last_login_info = $this->Statistic->find('first', array(
                                    'conditions' => array('user_id' => $usuario['UsuariosEmpresa']['id'], 'section' => _SITE_ACCESS),
                                    'order' => array('date' => 'desc')
                                )
                            );

                            if (!empty($last_login_info) && !$error_login) {
                                $last_login_info = $last_login_info['Statistic'];
                                $to_time = strtotime(date('Y-m-d H:i:s'));
                                $from_time = strtotime($last_login_info['date']);
                                $minutes_dif = round(abs($to_time - $from_time) / 60, 2);
                                if ($last_login_info['device'] == _DESKTOP_DEVICE && $minutes_dif <= _LOCK_TIME && $geolocalization['state'] != $last_login_info['state']) {
                                    $usuario['UsuariosEmpresa']['lock_status'] = UsuariosEmpresa::IS_MISSED_GEO;
                                    $this->UsuariosEmpresa->save($usuario);
                                    $notification_text = __('El usuario %s fue bloqueado por intentos de login en diferentes regiones en un periodo corto de tiempo. Verifique posible robo de credenciales.', array($usuario['UsuariosEmpresa']['name']));
                                    //$this->Session->write('geo_error', $notification_text);
                                    $this->Session->setFlash($notification_text);
                                    $error_login = true;

                                    //Save Notification
                                    $this->Notifications->userHasBeenLocked($usuario['UsuariosEmpresa']['id']);
                                    //Save navigation log row
                                    $this->Secure->saveBrowsingData(BrowsingLog::ACCESO, 'UsuariosEmpresa', $usuario['UsuariosEmpresa']['name'], $succes = false, $usuario['UsuariosEmpresa']['id']);
                                }
                            }

                            if (!$error_login) {
                                //Check region user founded and region logged
                                if ($usuario['UsuariosEmpresa']['role'] != UsuariosEmpresa::IS_MASTER) { //just if user role is not master
                                    $country_code_tracked = $geolocalization['country_code'];
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


                                $this->refreshCredentials();
                                $this->Core->setAppCredentials($this->credentials);
                                //Clean access info and set to logged_in
                                $usuario['UsuariosEmpresa']['logged_in'] = 1;
                                $usuario['UsuariosEmpresa']['access_attempts'] = 0;
                                $usuario['UsuariosEmpresa']['last_access_attempt'] = date('Y-m-d H:i:s');
                                $this->UsuariosEmpresa->save($usuario);

                                //Save information about browsing of user
                                $this->Secure->saveStatisticData($usuario['UsuariosEmpresa']['id'], Statistic::GO_SITE, $geolocalization);
                                //Save navigation log row
                                $this->Secure->saveBrowsingData(BrowsingLog::ACCESO, 'UsuariosEmpresa', $usuario['UsuariosEmpresa']['name']);


                                //Set flag login for redirection to application
                                $login_successfull = true;

                            } else {//Logout user
                                $this->Auth->logout();
                            }
                        } else {
                            $msg = $this->Secure->manageLoginTries($username, $password) . '<br>IP: ' . $geolocalization['ip'];
                            $this->Session->setFlash($msg);
                            //Log login try
                            $this->Secure->addActionLog('LOGIN', 'Login', 'log_user_try_login_in_system', $username);
                        }
                    } else {
                        $this->Session->setFlash(__('Verificacion captcha incorrecta.', true));
                    }
                } else {
                    $this->Session->setFlash(__('Verificacion captcha incorrecta.', true));
                }
            }else{
                $this->Session->setFlash(__('Username and password are required.', true));
            }

        }

        if($login_successfull){
            $this->redirect($this->Auth->loginRedirect);
        }else{
            $this->redirect($this->Auth->loginAction);
        }
    }

    public function logout() {
        $this->layout = false;
        $this->autoRender = false;
        $this->Session->delete(self::APP_REGION);
        $this->Session->delete('LATI');
        $this->Session->delete('LONG');

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
        } else { //if not, go to login form
            $this->redirect($this->Auth->loginAction);
        }
    }

    /*
    public function ajax_login() {
        $this->layout = false;
        $this->autoRender = false;

        if ($this->request->is('post')) {
            if (is_null($this->Auth->user())) {//Si no esta ya logueado
                //$data_login = $this->request->data;
                $data = $this->request->data;
                parse_str($data['formdata'], $data_login);
                $fingerprint = isset($data['fingerprint']) ? $data['fingerprint'] : "";
                $forceLogin = isset($data['force']) ? $data['force'] : false;
                if ((isset($data_login['g-recaptcha-response']) && $data_login['g-recaptcha-response'] != '') || $_SERVER['SERVER_NAME'] == 'localhost') {
                    $captcha = $data_login['g-recaptcha-response'];
                    $responseCaptcha=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcuzOcSAAAAAGaJRxK1_aPBJp1MZO6F-R8oj-qM&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
                    $secureVerification = json_decode($responseCaptcha, true);
                    if($secureVerification['success'] || $_SERVER['SERVER_NAME']=='localhost'){
                        $username = $data_login['username'];
                        $password = $data_login['password'];

                        $geolocalization = $this->Secure->getGeolocalizationData();
                        $this->Session->write('geolocalization_data', $geolocalization);

                    }else{
                        $response['msg'] = __('Verificacion captcha incorrecta.', true);
                    }
                } else {
                    $response['msg'] = __('Verificacion captcha incorrecta.', true);
                }
            }else{
                $response['msg'] = __('session_init', true);
                $response['logged'] = true;
                $response['redir'] = $this->site . $this->Auth->loginRedirect;
                $response['token'] = sha1($this->credentials['username']);
            }
            echo json_encode($response);
        } else {
            $this->redirect($this->Auth->loginRedirect);
        }
    }*/



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
