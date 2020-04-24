<?php
ini_set('session.cookie_httponly', 1);
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');
App::uses('Sanitize', 'Utility');

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

App::uses('Group', 'Model');
App::uses('Item', 'Model');
App::uses('UsuariosEmpresa', 'Model');
App::uses('BrowsingLog', 'Model');
App::uses('Statistic', 'Model');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    const APP_LANGUAGE = 'APP_LANG';
    const ASSOC_COMPANIES = 'ASSOC_COMPANIES';
    const COUNTRY_USER = 'COUNTRY_ID';
    const APP_REGION = 'REGION_APP';

    protected $_html;
    public $ext = '.php';
    public $helpers = array('Menu', 'Captcha', 'Core', 'Content', 'Utilities', 'ImageSize','Minify.Minify');
    public $components = array(
        'JsVars',
        'Core',
        'CustomSocket',
        'FireBase',
        'Converter',
        'Analytics',
        'Transactions',
        'Notifications',
        'Mail',
        'Secure',
        'Security',
        'Session',
        'RequestHandler',
        'Auth' => array(
            'loginRedirect' => _LOGIN_REDIRECT,
            'logoutRedirect' => "/Access/logout",
            'loginAction' => _LOGOUT_REDIRECT,
            'authorize' => array('Controller'),
            'authenticate' => array(
                'Form' => array(
                    'userModel' => 'UsuariosEmpresa',
                    'fields' => array('username' => _NAME_FIELD_MODEL, 'password' => _PASSWORD_FIELD_MODEL)
                )
            )
        )
    );
    public $credentials;
    public $usercode;

    /**
     * Array to hold js variables to be used in layout/view.
     * @var   array $_jsVars Array of the js variables to be used in the layout/view
     */
    var $_jsVars = array();

    /**
     * Add a js variable
     * @param type $name Name of the variable
     * @param type $value  Value of the variable
     * @return void
     */
    public function setJsVar($name, $value) {
        $this->_jsVars[$name] = $value;
    }
    
    public function getJsVar($name){
        return isset($this->_jsVars[$name]) ? $this->_jsVars[$name] : null;
    }

    // Add this function in your AppController
    public function forceSSL() {
        return $this->redirect('https://' . env('SERVER_NAME') . $this->here);
    }

    /**
     * Function (callback) which gets called before rendering the output
     * @return void
     */
    public function beforeRender() {
        // Set the jsVars array which holds the variables to be used in js
        $this->set('jsVars', $this->_jsVars);
        if (strtolower($this->name) == 'cakeerror') {
            $this->layout = 'failed';
        }
    }

    /**
     * management for app language
     * @param string $codeCountry el codigo del pais
     * @return string
     */
    public function _setLanguage($codeCountry = 'MX', $mobileLanguageConfig = false) {
        
        $folder_language = $language = 'es';
        if (!$this->Session->check(self::APP_LANGUAGE)) {//if language is not defined            
            if (is_null($this->credentials) || !$this->credentials || $mobileLanguageConfig) {//if not logged in set language for entry zone
                $language = $codeCountry == 'MX' || $codeCountry == '' ? 'es' : 'en';
                $folder_language = $language == 'es' ? 'esp' : 'eng';
                //if ($mobileLanguageConfig) {
                $this->Session->write(self::APP_LANGUAGE, $language);
                //}
            } else {//is logged in
                //check region                
                $region = $this->credentials['region'];
                $region = in_array($region, array('MX1', 'MX2', 'CENAM', 'SURAM')) ? 'mx' : $region;
                if ($region == 'na' || $region == 'mx') {
                    $language = 'es';
                } else {
                    $language = 'en';
                }
                $folder_language = $language == 'es' ? 'esp' : 'eng';
                $this->Session->write(self::APP_LANGUAGE, $language);
            }
        } else {
            $language = $this->Session->read(self::APP_LANGUAGE);
            $folder_language = $language == 'es' ? 'esp' : 'eng';
        }

        Configure::write('Config.language', $folder_language);
        return $language;
    }

    public function initAppEnviroment(){

        $this->uses[] = 'Noticia';
        $this->uses[] = 'ConfigTransporter';
        $this->uses[] = 'Region';
        $this->uses[] = 'Pais';
        $this->uses[] = 'Estado';
        $this->uses[] = 'AtencionPersona';
        $this->uses[] = 'Empresa';
        $this->uses[] = 'Corporativo';
        $this->uses[] = 'PerfilesTransportadores';
        $this->uses[] = 'Conveyor';
        $this->uses[] = 'TrackingConveyor';
        $this->uses[] = 'CustomReport';
        $this->uses[] = 'ReportTemplate';
        $this->uses[] = 'Image';
        $this->uses[] = 'Movie';
        $this->uses[] = 'Comment';
        $this->uses[] = 'Bucket';
        $this->uses[] = 'Report';
        $this->uses[] = 'Note';
        $this->uses[] = 'Notification';
        $this->uses[] = 'ViewedNotification';
        $this->uses[] = 'ContactArea';
        $this->uses[] = 'ContactRegion';
        $this->uses[] = 'Statistic';
        $this->uses[] = 'LockingLog';
        $this->uses[] = 'BrowsingLog';
        $this->uses[] = 'Paise';
        $this->uses[] = 'Ultrasonic';
        $this->uses[] = 'UltrasonicReading';
        $this->uses[] = 'TutorialSection';
        $this->uses[] = 'Archive';

        //updates v.2.5.0
        $this->uses[] = 'TabInstalledBelt';
        $this->uses[] = 'TabConveyor';
        $this->uses[] = 'TabIdler';
        $this->uses[] = 'TabPulley';
        $this->uses[] = 'TabTransitionZone';
        $this->uses[] = 'TabRemark';
        $this->uses[] = 'UserDevice';

        Configure::write('manager_corporate', null);
        Configure::write('type_manager', null);



        $mxFlag = 'mx';
        $usFlag = 'us';

        if (!is_null($this->Auth->user())) {
            $this->usercode = sha1($this->credentials['id']);
            $this->set('role', $this->credentials['role']);
            $this->set('region_user', $this->credentials['region']);
            $this->set('user_code', $this->usercode);

            //indica el id del usuario
            $this->setJsVar('ixc', $this->credentials['id']);
            $this->setJsVar('uid', $this->credentials['id']);
            $this->setJsVar('cid', $this->credentials['id_empresa']);
            //indica el role del usuario
            $this->setJsVar('rxc', $this->credentials['role']);

            if (!$this->Session->check(self::COUNTRY_USER)) {

                //Si el usuario logueado es de region de latinoamerica, hay que poner la bandera segun su pais
                if (in_array($this->credentials['region'], array('MX1', 'MX2', 'CENAM'))) {
                    if ($this->credentials['region'] == 'CENAM') {//si es CENAM poner bandera del pais de centro y sudamerica
                        $pais = $this->Paise->findById($this->credentials['country_id']);
                        if (!empty($pais)) {
                            $mxFlag = $pais['Paise']['code'];
                        }
                    }
                }
                $this->Session->write(self::COUNTRY_USER, $mxFlag);
            } else {
                $mxFlag = $this->Session->read(self::COUNTRY_USER);
            }
        }

        $this->setJsVar('flagAppEs', $this->site . 'img/flags/' . $mxFlag);
        $this->setJsVar('flagAppEn', $this->site . 'img/flags/' . $usFlag);

        $this->setJsVar('tabla_k', $this->Core->getTransmisionKFactorTable()); //For calculator

        $site_assets = $this->Core->loadBgAssets($this->_html);
        $this->set('site_assets', $site_assets);
        $this->setJsVar('assets', $site_assets);

        $this->JsVars->initMsgsUploader();
        $this->JsVars->initLangWidgets();
        $this->JsVars->initGlobalAjaxVars($this->_html, $this->usercode);
        $this->JsVars->initSystemMsgs($this->_html);
        $jsVars = $this->JsVars->_getVars();
        foreach ($jsVars as $name => $url) {
            $this->setJsVar($name, $url);
        }
    }

    public function beforeFilter(){
        // Check if current action is secure but shouldn't be
        // Codes added for SSL security
        $this->Security->validatePost = false;
        $this->Security->csrfCheck = false;
        $this->Security->csrfUseOnce = false;
        $sslnotallowed_url = array('terms', 'privacy', 'security');

        /*
                if(!defined('CRON_DISPATCHER')){//No es cronjob
                    $this->Security->blackHoleCallback = 'forceSSL';
                    if (!in_array($this->params['action'], $sslnotallowed_url)) {
                        $this->Security->requireSecure('*');
                    }
                }*/



        $this->uses[] = _MODEL_AUTH_APP;
        //$this->uses[] = 'UsuariosEmpresa';
        Configure::load('conveyor');
        Configure::load('permissions');
        Configure::load('nomenclaturas');
        $viewedSections = array();
        $this->set('session', $this->Session);

        $view = new View($this);
        $this->_html = $view->loadHelper('Html');
        $this->jsToInclude = array();
        $this->cssToInclude = array();
        $this->openJsToInclude = array();
        $this->openCssToInclude = array();

        $this->name = strtolower($this->name);
       /* if(!$this->Auth->loggedIn() && !$this->isAuthorized() && $this->name != 'access'){
            //$this->response->header('HTTP/1.0 201', 'custom message');
            //$this->response->send();
            //$this->redirect($this->Auth->loginRedirect);
        }*/

        if ($this->isAuthorized()) {

            if (!is_null($this->Auth->user())) {
                $this->refreshCredentials();

                if ($this->credentials['role'] === UsuariosEmpresa::IS_MANAGER) {
                    Configure::write('manager_corporate', $this->credentials['id_corporativo']);
                    Configure::write('type_manager', $this->credentials['role_company']);
                }


            }
            $this->initAppEnviroment();

            $this->usercode = 0;
            $this->site = Router::url('/', true); //define domain path site
            $pieces = parse_url(Router::url('/', true));
            $this->set('site', $this->site);
            $this->set('webroot', $this->webroot);
            $this->set('host', $pieces['host']);
            $this->set('mobile_host', 'm.contiplus.net');

            $codeCountry = '';
            if (!$this->Session->check(self::APP_REGION)) {
                //Obtenemos la localizacion ya sea porque este seteada la latitud y longitud o por la ip
                $geolocalization = $this->Secure->getGeolocalizationData();
                $this->Session->write('geolocalization_data', $geolocalization);

                $codeCountry = $this->Core->get_localization();
                $this->Session->write(self::APP_REGION, $codeCountry);
            } else {
                $codeCountry = $this->Session->read(self::APP_REGION);
            }

            //@TODO
            //$codeCountry = 'MX';
            if ($codeCountry == 'MX') {
                date_default_timezone_set('America/Mexico_City');
            }


            if (in_array($codeCountry, array('DE', 'HU', 'FI'))) {
                $usFlag = strtolower($codeCountry);
            }

            $this->global_filter_region = $this->credentials['role'] != UsuariosEmpresa::IS_MASTER ? $this->credentials['region'] : '';

            $current_language = $this->_setLanguage($codeCountry);
            $this->language = $current_language;
            $this->set('language', $current_language);
            $this->set('app_lang', $current_language);
            $this->Core->setAppLanguage($current_language);
            $this->JsVars->initSystemsVars($current_language, $this->_html);


            $this->set('credentials', $this->credentials);
            $this->set('options_toolbar', '');
            $this->set('name_controller', $this->name);
            $this->set('conveyor_config', Configure:: read('Conveyor'));


            //if ($this->credentials['role'] == UsuariosEmpresa::IS_CLIENT || ($this->credentials['role']==UsuariosEmpresa::IS_MANAGER && $this->credentials['role_company']==UsuariosEmpresa::IS_CLIENT)) {
            if ($this->credentials['role'] == UsuariosEmpresa::IS_CLIENT) {
                $empresaCliente = $this->credentials['id_empresa'];
                $empresaCliente = $this->Empresa->findById($empresaCliente);
                $this->setJsVar('distCompanyId', $empresaCliente['Empresa']['parent']);
                $this->setJsVar('clientCompanyId', $empresaCliente['Empresa']['id']);
            } else if ($this->credentials['role'] == UsuariosEmpresa::IS_DIST) {
                $empresaDistribuidor = $this->credentials['id_empresa'];
                $this->setJsVar('distCompanyId', $empresaDistribuidor);
            }


            $this->setJsVar('site', $this->site);


            $this->Core->setAppCredentials($this->credentials);

            $this->Core->setGlobalRegion($this->global_filter_region);

            $notifications_data = $this->Notifications->getAllByCompanyAndUser();
            $this->set('unreaded_notifications', $notifications_data['unreaded']);
            $viewedSections = $this->Core->getViewedTutorialSections();

        }
    }

    /**
     * Actualiza las credenciales y auth
     */
    function refreshCredentials() {
        //var_dump($this->UsuariosEmpresa);
        //if (isset($this->UsuariosEmpresa) ) {
            $this->setUserCredentials();
            $this->set('credentials', $this->credentials); //actualiza la variable credentials

        //}
    }

    /**
     * Establece las credenciales del usuario 
     */
    function setUserCredentials() {
        $credentials = $this->Auth->user();
        $credentials = $credentials[0];
        //$usuario = $credentials[0][_MODEL_AUTH_BD];
        //$usuario = $this->User->getUserById($usuario['id']);//Consultamos BD para actualizar posibles nuevos valores del registro

        $usuario = $credentials['UsuariosEmpresa'];
        $empresa = $credentials['Empresa'];
        $info_usuario = $this->UsuariosEmpresa->findFullInfoById($usuario['id']); //Consultamos BD para actualizar posibles nuevos valores del registro
        if (!empty($info_usuario)) {
            //var_dump($info_usuario);
            $usuario = $info_usuario[0]['UsuariosEmpresa'];
            $empresa = $info_usuario[0]['Empresa'];
        }
        //$this->credentials = $usuario;
        if (!is_null($usuario)) {
            $this->credentials = array_merge($usuario, $empresa);
        }
    }

    /**
     * Verifica si se tiene permiso de acceder a la accion requerida
     * ESTA SECCION ES AUTOMATICA, UNA VEZ LOGUEADO, EL AUTH COMPONENT LA DETECTA AUTOMATICAMENTE
     * @return boolean
     */
    public function isAuthorized() {
        $permissions = Configure::read('App.permissions');
        $access = false;
        $permissions = $this->array_change_key_case_recursive($permissions);

        $this->name = strtolower($this->name);
        $this->action = strtolower($this->action);
        /* Si no esta definido en el mapa -> sera libre la seccion* */
        //primero checamos que la seccion sea libre
        if (!isset($permissions[$this->name][$this->action])) {
            //mail("elalbertgd@gmail.com","libre",print_r(array($this->name,$this->action), true));
            $this->Auth->allow($this->action);
            $access = true;
        } else if (!is_null($this->Auth->user())) {

            $usuario = $this->Auth->user();
            //var_dump($usuario);
            //$group = $usuario[0][_MODEL_AUTH_BD]['group_id'];
            $group = $usuario[0]['UsuariosEmpresa']['group_id'];
            /**
             * Se checa el id minimo necesario
             * Si el grupo al que pertenece el usuario es al menos el necesario requerido segun el mapa de permisos
             *  * */
            $access = ($group >= $permissions[$this->name][$this->action]);
        }/*else{
            setcookie('USER_UNIQ', '', -1, '/', _DOMAIN_COOKIE, false, false);
            setcookie('USERTYP', '', -1, '/', _DOMAIN_COOKIE, false, false);
            $this->Auth->logout();
        }*/

        return $access;
    }

    function redirect($url, $status = null, $exit = true) {
        if ($this->RequestHandler->isAjax()) {
            $url = '/ajax/' . ltrim($url, '/');
        }
        parent::redirect($url, $status, $exit);
    }

    function array_change_key_case_recursive($arr) {
        $arr = array_change_key_case($arr);
        foreach ($arr as $key => $val) {
            $arr[$key] = array_change_key_case($val);
        }
        return $arr;
    }

}
