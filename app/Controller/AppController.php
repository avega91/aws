<?php
header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0, max-stale = 0', false);  // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');                  // Date in the past
header('Expires: 0', false);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Pragma: no-cache');
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

App::uses('IElement', 'Model');
App::uses('IGroup', 'Model');
App::uses('IMarket', 'Model');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */

abstract class BuoySystem {
    const IsWorking = 'OPERATING';
    const IsInMaintenance = 'MAINTENANCE';
}
abstract class ApiRequest {
    const IsPost = 'POST';
    const IsGet = 'GET';
    const IsPut = 'PUT';
}

class AppController extends Controller {

    const APP_LANGUAGE = 'APP_LANG';
    const ASSOC_COMPANIES = 'ASSOC_COMPANIES';
    const COUNTRY_USER = 'COUNTRY_ID';
    const APP_REGION = 'REGION_APP';

    protected $_html;
    public $ext = '.php';
    public $helpers = array('Menu', 'Captcha', 'Core', 'Content', 'Utilities', 'ImageSize');
    public $components = array(
        'JsVars',
        'Core',
        'CustomSocket',
        'FireBase',
        'Converter',
        //'Analytics',
        'Transactions',
        'Notifications',
        'Mail',
        'Secure',
        //'Security',
        'Session',
        'RequestHandler',
        'Auth' => array(
            'loginRedirect' => _LOGIN_REDIRECT,
            'logoutRedirect' => _LOGOUT_REDIRECT, //"/Access/logout",
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

        // set eng language always
        // $language = $this->Session->read(self::APP_LANGUAGE);
        $language = 'en';
        $this->Session->write(self::APP_LANGUAGE, $language);
        $folder_language = $language == 'es' ? 'esp' : 'eng';

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

        $this->uses[] = 'ICountry';
        $this->uses[] = 'IRegion';
        $this->uses[] = 'SalespersonCompany';
        $this->uses[] = 'IGroup';
        $this->uses[] = 'IElement';
        $this->uses[] = 'ITerritory';
        $this->uses[] = 'IPermissionGroup';
    }

    public function checkPermissionsRole(){
        $name = strtolower($this->name);
        $action = strtolower($this->action);

        //var_dump($name); var_dump($action);
        $restrictedSections = [
            'news' => [
                'index' => ['needs'=>'view','permission_element'=>IElement::Is_News],//index needs permission view
            ],
            'conveyors' => [
                'dashboard' => ['needs'=>'view','permission_element'=>IElement::Is_Conveyor],//dashboard needs permission view
                'ultrasonic' => ['needs'=>'view','permission_element'=>IElement::Is_UltrasonicWithGauge],//dashboard needs permission view
            ],
            'users' => [
                'clients' => ['needs'=>'view','permission_element'=>IElement::Is_Customer],
                'all' => ['needs'=>'view','permission_element'=>IElement::Is_UsersSection],
            ],
            'advanced' => [
                'index' => ['needs'=>'view','permission_element'=>IElement::Is_AdvancedSection],
            ],
            'general' => [
                'help' => ['needs'=>'view','permission_element'=>IElement::Is_HelpSection],
                'terms' => ['needs'=>'view','permission_element'=>IElement::Is_TermsSection],
            ],
            'savings' => [
                'main' => ['needs'=>'view','permission_element'=>IElement::Is_Saving],
            ],
            'history' => [
                'view' => ['needs'=>'view','permission_element'=>IElement::Is_History],
            ],
        ];

        if(array_key_exists($name, $restrictedSections) && array_key_exists($action, $restrictedSections[$name])){
            $permissions = $this->credentials['permissions'];
            $permissionNeeded = $restrictedSections[$name][$action]['needs'];
            if(isset($permissions[$restrictedSections[$name][$action]['permission_element']]) && in_array($permissionNeeded,$permissions[$restrictedSections[$name][$action]['permission_element']]['allows'])){
            }else{
                $this->redirect($this->Auth->loginRedirect);
            }
        }
    }

    // Add this function in your AppController
    public function forceSSL() {
        return $this->redirect('https://' . env('SERVER_NAME') . $this->here);
    }

    public function activateSSL(){
        // Check if current action is secure but shouldn't be
        // Codes added for SSL security
        $this->Security->validatePost = false;
        $this->Security->csrfCheck = false;
        $this->Security->csrfUseOnce = false;

        //Forsar SSL
        $sslnotallowed_url = array('terms', 'privacy', 'security');
        if(!defined('CRON_DISPATCHER') && $_SERVER['HTTP_HOST']!='localhost'){//No es cronjob
            $this->Security->blackHoleCallback = 'forceSSL';
            if (!in_array($this->params['action'], $sslnotallowed_url)) {
                $this->Security->requireSecure('*');
            }
        }
    }

    public function require_ssl() {
        global $config_require_ssl;

        if ($config_require_ssl == FALSE)
        {
                return;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
        {
                if (strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
                {
                       $_SERVER['HTTPS']='on';
                }
        }

        if(empty($_SERVER['HTTPS']) || $_SERVER["HTTPS"] != "on")
        {
                header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);

                exit();
        }
    }

    public function beforeFilter(){
       //activate ssl
       //$this->activateSSL();

       //$this->require_ssl();

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

        Configure::write('manager_corporate', null);
        Configure::write('type_manager', null);

        /*if(is_null($this->Auth->user()) && $this->name != 'access'){
            $this->redirect($this->Auth->logoutRedirect);
        }else if(is_null(AuthComponent::user())){
            $this->layout = 'login';
        }*/
        $this->usercode = 0;
        $this->site = Router::url('/', true); //define domain path site
        if(!defined('CRON_DISPATCHER') && $_SERVER['HTTP_HOST']!='localhost'){//No es cronjob
            $this->site = str_replace('http://','https://', $this->site);
        }
        
        $pieces = parse_url(Router::url('/', true));
        $this->set('webroot', $this->webroot);
        $this->set('site', $this->site);
        $this->set('host', $pieces['host']);
        $this->set('mobile_host', 'm.contiplus.net');
        $this->initAppEnviroment();//init all models
        $this->refreshCredentials();
        
        
        
        if ($this->isAuthorized()) {
            if (!is_null($this->Auth->user())) {

                $this->checkPermissionsRole();

                if ($this->credentials['role'] === UsuariosEmpresa::IS_MANAGER) {
                    Configure::write('manager_corporate', $this->credentials['id_corporativo']); //is the id of corporate
                    Configure::write('type_manager', $this->credentials['role_company']); //is the type of company (client or distributor)
                }


            }

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


            //SEt Different flag for oother us country
            /*if (in_array($codeCountry, array('DE', 'HU', 'FI'))) {
                $usFlag = strtolower($codeCountry);
            }*/

            $this->setJsVar('flagAppEs', $this->site . 'img/flags/' . $mxFlag);
            $this->setJsVar('flagAppEn', $this->site . 'img/flags/' . $usFlag);

            $this->global_filter_region = $this->credentials['role'] != UsuariosEmpresa::IS_MASTER ? $this->credentials['region'] : '';


            $current_language = $this->_setLanguage($codeCountry);
            $this->language = $current_language;
            $this->set('language', $current_language);
            $this->set('app_lang', $current_language);
            $this->set('credentials', $this->credentials);
            $this->set('options_toolbar', '');
            $this->set('name_controller', $this->name);
            $this->set('name_action', $this->action);
            $this->set('conveyor_config', Configure:: read('Conveyor'));


            //if ($this->credentials['role'] == UsuariosEmpresa::IS_CLIENT || ($this->credentials['role']==UsuariosEmpresa::IS_MANAGER && $this->credentials['role_company']==UsuariosEmpresa::IS_CLIENT)) {
            if ($this->credentials['role'] == UsuariosEmpresa::IS_CLIENT || $this->credentials['i_group_id'] == IGroup::CLIENT_MANAGER) {
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
            $this->Core->setAppLanguage($current_language);
            $this->Core->setGlobalRegion($this->global_filter_region);

            $notifications_data = ['unreaded'=>[]];
            if(!$this->request->is('ajax')){
                $notifications_data = $this->Notifications->getAllByCompanyAndUser();
            }

            $this->set('unreaded_notifications', $notifications_data['unreaded']);
            $viewedSections = $this->Core->getViewedTutorialSections();
            $this->setJsVar('tutorialOptions', array('area' => strtolower($this->name), 'section' => strtolower($this->action), 'viewed' => $viewedSections));

            //For get Sap info form conti germany
            $this->setJsVar('sapFeed', $this->_html->url(array('controller' => 'Users', 'action' => 'sapQuery')));

            $this->JsVars->initSystemsVars($current_language, $this->_html);
            $this->JsVars->initMsgsUploader();
            $this->JsVars->initLangWidgets();
            $this->JsVars->initSystemMsgs($this->_html);

        }
        
        $this->JsVars->initGlobalAjaxVars($this->_html, $this->usercode);
        if(!$this->request->is('post') || strtolower($this->action) == 'mobileadd' || strtolower($this->name)=='advanced') {
            $site_assets = $this->Core->loadBgAssets($this->_html);
            $this->set('site_assets', $site_assets);
            $this->setJsVar('assets', []);
            
            $jsVars = $this->JsVars->_getVars();
            foreach ($jsVars as $name => $url) {
                $this->setJsVar($name, $url);
            }
        }


    }

    /**
     * Actualiza las credenciales y auth
     */
    function refreshCredentials() {
        //var_dump($this->UsuariosEmpresa);
        //if (isset($this->UsuariosEmpresa) ) {
        if (!is_null($this->Auth->user())) {
            $this->setUserCredentials();
            $this->set('credentials', $this->credentials); //actualiza la variable credentials
        }

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

            if(!$this->Session->check('permissionsRole') || 1==1){
                $this->IGroup->recursive = 2;
                $this->IGroup->hasMany['PermissionsForGroup']['conditions'] = ['PermissionsForGroup.market_id'=>$empresa['market_id']]; //permisos segun el market de su empresa
                $userGroup = $this->IGroup->find('first',['conditions'=>['IGroup.id'=>$this->credentials['i_group_id']]]);
                $permissions = Set::extract('/PermissionsForGroup/.', $userGroup);
                /*$userGroup = $this->IGroup->find('first',['recursive'=>2, 'conditions'=>['IGroup.id'=>$this->credentials['i_group_id']]]);
                $permissions = Set::extract('/PermissionsForGroup/.', $userGroup);
*/
                $permissionMatrix= [];
                if(!empty($permissions)){
                    foreach ($permissions AS $permission){
                        if(isset($permission['element_id'])){
                            $permissionMatrix[$permission['element_id']] = ['elementName' => $permission['Element']['name'],'version' => $permission['version'], 'allows' => []];
                            if($permission['permission'][0] == '1') $permissionMatrix[$permission['element_id']]['allows'][] = 'add';
                            if($permission['permission'][1] == '1') $permissionMatrix[$permission['element_id']]['allows'][] = 'edit';
                            if($permission['permission'][2] == '1') $permissionMatrix[$permission['element_id']]['allows'][] = 'delete';
                            if($permission['permission'][3] == '1') $permissionMatrix[$permission['element_id']]['allows'][] = 'view';
                            if($permission['permission'][4] == '1') $permissionMatrix[$permission['element_id']]['allows'][] = 'download';
                        }
                    }
                }


                $this->Session->write('permissionsRole', $permissionMatrix);
            }

            $this->credentials['permissions'] = $this->Session->read('permissionsRole');
        }


    }

    /**
     * Verifica si se tiene permiso de acceder a la accion requerida
     * ESTA SECCION ES AUTOMATICA, UNA VEZ LOGUEADO, EL AUTH COMPONENT LA DETECTA AUTOMATICAMENTE
     * @return boolean
     */
    public function isAuthorized() {
        $permissions = [];
        if($this->Session->check('permissions')){
            $permissions = $this->Session->read('permissions');
        }else{
            $permissions = Configure::read('App.permissions');
            $this->Session->write('permissions', $permissions);
        }
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

    /*
    
    function redirect($url, $status = null, $exit = true) {
        if ($this->RequestHandler->isAjax()) {
            $url = '/ajax/' . ltrim($url, '/');
        }
        parent::redirect($url, $status, $exit);
    }*/

    function array_change_key_case_recursive($arr) {
        $arr = array_change_key_case($arr);
        foreach ($arr as $key => $val) {
            $arr[$key] = array_change_key_case($val);
        }
        return $arr;
    }

}
