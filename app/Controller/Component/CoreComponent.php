<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file CoreComponent.php
 *     Component to manage common core methods
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class CoreComponent extends Component {

    var $components = array('Converter','Session'); // the other component your component uses
    private $_app_credentials;
    public $_app_language;
    public $_global_region;

    /**
     * Function set app user credentials
     * @param array $credentials
     */
    public function setAppCredentials($credentials) {
        $this->_app_credentials = $credentials;
        if(!is_null($credentials)){
            setcookie('USER_UNIQ',  sha1($credentials['id']),_COOKIE_TIME,'/',_DOMAIN_COOKIE,false,false);
            setcookie('USERTYP',$credentials['role'],_COOKIE_TIME,'/',_DOMAIN_COOKIE,false,false);
        }
    }

    /**
     * Function return app user credentials
     * @return array
     */
    public function getAppCredentials() {
        return $this->_app_credentials;
    }

    /**
     * Function set current app language
     * @param type $language
     */
    public function setAppLanguage($language) {
        $this->_app_language = $language;
    }

    /**
     * Set current region system according user logged
     * @param type $region
     */
    public function setGlobalRegion($region) {
        $this->_global_region = $region;
    }

    /**
     * Actualizacion de buoy sistem id info para todos los hijos
     *
     * @param [array] $folderApp object row
     * @param [array] $folderSettings data with settings
     * @return void
     */
    public function updateBSIDChildFolders($folderApp, $bsId){
        $this->FolderApp = ClassRegistry::init('FolderApp');
        $folder_id = $folderApp['FolderApp']['id'];
        //$this->FolderApp->recursive = -1;
        $childsBS = $this->FolderApp->findAllByFolderIdAndDeleted($folder_id, 0);
        if(!empty($childsBS)){//Si no tiene hijos, crear la estructura por primera vez
            foreach($childsBS AS $childFolder){
                $this->updateBSIDChildFolders($childFolder, $bsId);
            }
        }
        if($folderApp['FolderApp']['buoy_system_id']==0){
            $this->FolderApp->id = $folder_id;
            $this->FolderApp->saveField('buoy_system_id', $bsId);
        }
    }

    /**
     * Creacion del arbol de folders
     *
     * @param [array] $folderApp object row
     * @param [array] $folderSettings data with settings
     * @return void
     */
    public function createFullImodcoTreeFolders($folderApp, $folderSettings, $buoySystemId = 0){
        $this->FolderApp = ClassRegistry::init('FolderApp');

        $folder_id = $folderApp['FolderApp']['id'];
        $client_id = $folderApp['Client']['id'];

        if($folderApp['FolderApp']['buoy_system_id']==0){
            $this->FolderApp->id = $folder_id;
            $this->FolderApp->saveField('buoy_system_id', $buoySystemId);
        }
        

        //Obtenemos los folders que deberia tener el tipo de folder actual
        $typeCurrentFolder = $folderApp['FolderApp']['type'];
        $mandatoryFolders = isset($folderSettings[$typeCurrentFolder]) ? $folderSettings[$typeCurrentFolder]['nodes'] : [];

        if(!empty($mandatoryFolders)){
            //Obtener los hijos del folder actual del cliente actual
            $buoyFolders = $this->FolderApp->find('all', [
                'fields' => ['type', 'id'],
                'conditions'=>[
                    'FolderApp.client_id' => $client_id, //el id del client
                    'FolderApp.folder_id' => $folder_id,// el id del folderactual
                    'FolderApp.deleted' => 0,
                    ]
                ]
            );
            $buoyFolders = !empty($buoyFolders) ? Set::extract('/FolderApp/.', $buoyFolders ) : $buoyFolders;

            foreach($mandatoryFolders AS $mandatoryFolderId => $mandatoryFolder){
                $existFolder = array_search($mandatoryFolderId, array_column($buoyFolders, 'type'));
                
                if($existFolder===false){//Si no existe el folder, se crea
                    $mandatoryFolderName= is_array($mandatoryFolder) ? $mandatoryFolder[0] : $mandatoryFolder;
                    if(is_array($mandatoryFolder)){
                        $allowAssetsInFolder = $mandatoryFolder[1];
                        $isAssetFolder = false;
                    } else if(isset($folderSettings[$mandatoryFolderId])) {
                        $allowAssetsInFolder = $folderSettings[$mandatoryFolderId]['allow_assets'];
                        $isAssetFolder = false;
                    }else{
                        $allowAssetsInFolder = false;
                        $isAssetFolder = true;
                    }
                    //$allowAssetsInFolder = isset($folderSettings[$mandatoryFolderId]) ? $folderSettings[$mandatoryFolderId]['allow_assets'] : true; //->Para la otra estructura
                    //$allowAssetsInFolder = isset($folderSettings[$mandatoryFolderId]) ? $folderSettings[$mandatoryFolderId]['allow_assets'] : false;
                    $bucket = ['FolderApp' => [
                            'name' => $mandatoryFolderName,
                            'client_id' => $client_id,
                            'buoy_system_id' => $buoySystemId,
                            'folder_id' => $folder_id,
                            'type' => $mandatoryFolderId,
                            'allow_assets' => $allowAssetsInFolder,
                            'is_asset_folder' => $isAssetFolder,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    ];
                    //model data is reset and ready to accept new data
                    $this->FolderApp->create();
                    $this->FolderApp->save($bucket);
                    $folderSavedId = $this->FolderApp->getInsertID();
                }else{ // Si ya existe obtenemos su id
                    $folder = $buoyFolders[$existFolder];
                    $folderSavedId = $folder['id'];
                }
                //Pass the saved folder for create de next sheet
                $buoyFolder = $this->FolderApp->findByIdAndDeleted($folderSavedId, 0);
                $this->createFullImodcoTreeFolders($buoyFolder, $folderSettings, $buoySystemId);
            }
        }else if(!$folderApp['FolderApp']['allow_assets']){//No es un folder de assets, es de files, agregar el GHMC
            $folderApp['FolderApp']['type'] = 'sheet_folder';
            $this->createGenericFoldersInAssetsFolder($folderApp, $folderSettings, $buoySystemId);
        }

    }

    /**
     * Creacion de folders G,H,M,C en folders de assets
     *
     * @param [type] $folderApp
     * @param [type] $folderSettings
     * @return void
     */
    public function createGenericFoldersInAssetsFolder($folderApp, $folderSettings, $buoySystemId = 0){
        $this->FolderApp = ClassRegistry::init('FolderApp');

        $folder_id = $folderApp['FolderApp']['id'];
        $client_id = $folderApp['Client']['id'];

        //Obtenemos los folders que deberia tener el tipo de folder actual
        $typeCurrentFolder = $folderApp['FolderApp']['type'];
        $mandatoryFolders = isset($folderSettings[$typeCurrentFolder]) ? $folderSettings[$typeCurrentFolder]['nodes'] : [];

        if(!empty($mandatoryFolders)){
            //Obtener los hijos del folder actual del cliente actual
            $buoyFolders = $this->FolderApp->find('all', [
                'fields' => ['type', 'id'],
                'conditions'=>[
                    'FolderApp.client_id' => $client_id, //el id del client
                    'FolderApp.folder_id' => $folder_id,// el id del folderactual
                    'FolderApp.deleted' => 0,
                    ]
                ]
            );
            $buoyFolders = !empty($buoyFolders) ? Set::extract('/FolderApp/.', $buoyFolders ) : $buoyFolders;

            foreach($mandatoryFolders AS $mandatoryFolderId => $mandatoryFolder){
                $existFolder = array_search($mandatoryFolderId, array_column($buoyFolders, 'type'));
                
                if($existFolder===false){//Si no existe el folder, se crea
                    $allowAssetsInFolder = false;
                    $bucket = ['FolderApp' => [
                            'name' => $mandatoryFolder,
                            'client_id' => $client_id,
                            'buoy_system_id' => $buoySystemId,
                            'folder_id' => $folder_id,
                            'type' => $mandatoryFolderId,
                            'allow_assets' => $allowAssetsInFolder,
                            'is_file_folder' => true,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    ];
                    //model data is reset and ready to accept new data
                    $this->FolderApp->create();
                    $this->FolderApp->save($bucket);
                    $folderSavedId = $this->FolderApp->getInsertID();
                }
            }
        }
    }

    public function createCustomFileFoldersIn($folderApp, $folderChilds, $buoySystemId = 0){
        $this->FolderApp = ClassRegistry::init('FolderApp');
        $this->FileFolder = ClassRegistry::init('FileFolder');
        $client_id = $folderApp['Client']['id'];
        $folder_id = $folderApp['FolderApp']['id'];

        $fileFoldersClient = $this->FileFolder->find('all', [
            'fields' => ['name', 'lower_name', 'client_id'],
            'conditions'=>[
                'FileFolder.client_id' => $client_id, //el id del client
                'FileFolder.deleted' => 0,
                ]
            ]
        );

        $fileFoldersClient = !empty($fileFoldersClient) ? Set::extract('/FileFolder/.', $fileFoldersClient) : [];
        $folderChilds = !empty($folderChilds) ? Set::extract('/FolderApp/.', $folderChilds) : [];

        //Convert to lower
        $folderChilds = array_map('strtolower', array_column($folderChilds, 'name'));


        if(!empty($fileFoldersClient)){
            $folderFileToSave = [];
            foreach($fileFoldersClient AS $fileFolderClient){
                if(!in_array($fileFolderClient['lower_name'], $folderChilds)){
                    $folderFileToSave[] = [
                        'FolderApp'=>[
                            'name' => $fileFolderClient['name'], 
                            'client_id' => $fileFolderClient['client_id'],
                            'buoy_system_id' => $buoySystemId,
                            'folder_id' => $folder_id,
                            'type' => 'file_folder',
                            'allow_assets' => false,
                            'is_file_folder' => true,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    ];
                }
            }
            if(!empty($folderFileToSave)){
                $this->FolderApp->saveMany($folderFileToSave);
            }
        }
        
    }

    public function getAssetsFoldersForNode($node, $assetFolders){
        $assetsFolderForNode = [];
        foreach($assetFolders AS $assetFolderNode => $assetFolderData){
            if($assetFolderData['parent'] == $node){
                $assetsFolderForNode = array_merge($assetsFolderForNode, $assetFolderData['nodes']); 
            }
        }
        return $assetsFolderForNode;
    }

    public function getParentsOfFolderId($folderId){
        $folderList = [];
        $this->FolderApp = ClassRegistry::init('FolderApp');
        $buoyFolder = $this->FolderApp->findByIdAndDeleted($folderId, 0);
        
        if(!empty($buoyFolder)){
            if($buoyFolder['FolderApp']['folder_id']!=0){
                $folderList = $this->getParentsOfFolderId($buoyFolder['FolderApp']['folder_id']);//Agregamos lo que encontro en la ultima llamada
                $folderList[] = $buoyFolder; //despues el folder actual
            }else{
                $folderList[] = $buoyFolder; //se inicializa el array con la ultima hoja
            }
        }
        

        return $folderList;
    }





    /* Get country code
     * MX for mexico
     * US for usa
     * * */

    public function get_localization() {
         $servidor = $_SERVER['REMOTE_ADDR'];
        $country_code = 'MX';
        
        /*
       
        //$servidor = '2.175.255.255';//Alemania
        //$servidor = '31.7.31.255';//Finlandia
        //$servidor = '2.87.255.255';//Hungria

        $location_data = @file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $servidor);
        $location_data = @unserialize($location_data);
        if ($location_data === null) {
            //Error case
        } else {
            $country_code = $location_data['geoplugin_countryCode'];
        }*/
        /*
        $location_data = @file_get_contents('http://ip-api.com/json');
        $location_data = json_decode($location_data, true);
        if ($location_data === null) {
            //Error case
        } else {
            $country_code = $location_data['countryCode'];
        }*/
        /*
        $location_data = @file_get_contents('https://freegeoip.net/json/177.233.104.129');
        $location_data = json_decode($location_data, true);
        if ($location_data === null) {
            //Error case
        } else {
            $country_code = $location_data['country_code'];
        }*/
        
        if($this->Session->check('geolocalization_data')){
            $geolocalization = $this->Session->read('geolocalization_data');
            //var_dump($geolocalization);
            $country_code = $geolocalization['country_code'];
        }
        return $country_code;
    }

    /**
     * Decodifica los parametros recibidos sobre un usuario y su cadena codificada
     * Se verifica que sean correctos y no se trate de un intento de ver un usuario cualquiera que no le corresponda
     * @param array $params parametros GET de la url
     * @return array
     */
    public function decodeUserParams($params) {
        $decodeResponse = array();
        $decodeResponse['isOk'] = false;


        $user_encoded = $params[0];
        $digestivo = $params[1];

        $user_decoded = base64_decode($user_encoded);
        list($user_id, $sign64_received) = explode('|', $user_decoded);
        //Se crea un digestivo temporal con el usuario recibido y se verifica que sea igual al digestivo que viene
        $digest_verification = sha1($user_id . '|' . _SHA1_SIGNATURE);
        $decodeResponse['isOk'] = $digest_verification == $digestivo && $sign64_received == _B64_SIGNATURE ? true : false;
        $decodeResponse['user_id'] = $user_id;

        return $decodeResponse;
    }

    /**
     * Codifica el un identificador ya sea de album, user, photo, report etc
     * @param int $id_item el id del item
     * @return array
     */
    public function encodeParams($id_item) {
        $coded_params = array();
        $coded_params['item_id'] = base64_encode($id_item . '|' . _B64_SIGNATURE);
        $coded_params['digest'] = sha1($id_item . '|' . _SHA1_SIGNATURE);

        return $coded_params;
    }

    /**
     * Decodifica los parametros recibidos sobre un item del sistema, puede ser un id de usuario, album, foto, reporte etc
     * Se verifica que sean correctos y no se trate de un intento de ver algun otro dato diferente al elegido
     * @param array $params parametros GET de la url
     * @param int $from a partir de donde se inicia el par de params
     * @return array
     */
    public function decodePairParams($params, $from = 0) {
        $decodeResponse = array();
        $decodeResponse['isOk'] = false;


        $item_encoded = $params[$from];
        $digestivo = $params[$from + 1];

        $item_decoded = base64_decode($item_encoded);
        list($item_id, $sign64_received) = explode('|', $item_decoded);
        //Se crea un digestivo temporal con el usuario recibido y se verifica que sea igual al digestivo que viene
        $digest_verification = sha1($item_id . '|' . _SHA1_SIGNATURE);
        $decodeResponse['isOk'] = $digest_verification == $digestivo && $sign64_received == _B64_SIGNATURE ? true : false;
        $decodeResponse['item_id'] = $item_id;

        return $decodeResponse;
    }

    /**
     * Function: sanitize
     * Returns a sanitized string, typically for URLs.
     *
     * Parameters:
     *     $string - The string to sanitize.
     *     $force_lowercase - Force the string to lowercase?
     *     $anal - If set to *true*, will remove all non-alphanumeric characters.
     */
    function sanitize($string, $force_lowercase = true, $anal = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "Ã¢â¬â", "Ã¢â¬â", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return ($force_lowercase) ?
                (function_exists('mb_strtolower')) ?
                        mb_strtolower($clean, 'UTF-8') :
                        strtolower($clean) :
                $clean;
    }

    /**
     * Function load in array the application assets for later use
     * @param Helper $html
     * @return array
     */
    public function loadBgAssets($html) {
        $site_images = array();
        $dir_images = new Folder('./css/images');
        $img_files = $dir_images->findRecursive('.*\.png');
        foreach ($img_files AS $img) {
            list($fake, $real) = explode('webroot', $img);
            $real = str_replace('\\', '/', $real);
            $real = substr($real, 1);
            $site_images[] = $html->webroot . $real;
        }
        $img_files = $dir_images->findRecursive('.*\.gif');
        foreach ($img_files AS $img) {
            list($fake, $real) = explode('webroot', $img);
            $real = str_replace('\\', '/', $real);
            $real = substr($real, 1);
            $site_images[] = $html->webroot . $real;
        }
        return $site_images;
    }

    /**
     * Function creates a system username using email and user type params and timestamp
     * @param string $email user email
     * @param string $usertype 
     * @return string username
     */
    public function createSystemUsername($email, $usertype) {
        $username = '';
        list($mail, $domain) = explode('@', $email);
        $username = $mail . '.' . date('y') . strtoupper($usertype[0]) . (date('z') + 1); // _ change for . (underscore is changed by point)
        return $username;
    }


    public function createUniqueUsername($name) {
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $username = '';
        $name = strtolower($name);
        $name_parts = explode(" ", $name);
        $username = implode(".",$name_parts);

        $matches = $this->UsuariosEmpresa->find('count', ['conditions' => ['username LIKE' => $username."%"]]);
        if($matches>0){
            $matches = $matches<10 ? '0'.$matches: $matches;
            $username = $username.$matches;
        }

        return $username;
    }


    public function initAutocompleteConveyors($conveyors) {
        $autocomplete = array();
        if (!empty($conveyors)) {
            foreach ($conveyors AS $conveyor) {
                $transportador = $conveyor['Conveyor'];
                //$label = utf8_decode($transportador['numero']);
                $label = html_entity_decode($transportador['numero'], ENT_QUOTES, "UTF-8");
                $label = utf8_encode($label);
                //$label = str_replace("Â","",$label);
                //$label = str_replace(chr(194),"",$label);
                $autocomplete[] = array('label' => $label);
            }
        }
        return $autocomplete;
    }

    public function initAutocompleteTrackingConveyors($conveyors) {
        $autocomplete = array();
        if (!empty($conveyors)) {
            foreach ($conveyors AS $conveyor) {
                $transportador = $conveyor['TrackingConveyor'];
                $autocomplete[] = array('label' => $transportador['title']);
            }
        }
        return $autocomplete;
    }

    public function initGenericAutocomplete($items, $model) {
        $autocomplete = array();
        if (!empty($items)) {
            foreach ($items AS $item) {
                $item = $item[$model];
                $autocomplete[] = array('label' => $item['title']);
            }
        }
        return $autocomplete;
    }

    public function initAutocompleteCompanies($admins, $distributors, $clients, $dist_corps=[], $client_corps = []) {
        $autocomplete = array();
        if (!empty($admins)) {
            foreach ($admins AS $company) {
                $empresa = $company['Empresa'];
                $autocomplete[] = array('label' => $empresa['name'], 'category' => 'admin' . '|' . __('Administradores', true));
            }
        }

        if (!empty($distributors)) {
            foreach ($distributors AS $company) {
                $empresa = $company['Empresa'];
                $autocomplete[] = array('label' => $empresa['name'], 'category' => 'distributor' . '|' . __('Distribuidores', true));
            }
        }

        if (!empty($clients)) {
            foreach ($clients AS $company) {
                $empresa = $company['Empresa'];
                $autocomplete[] = array('label' => $empresa['name'], 'category' => 'client' . '|' . __('Clientes', true));
            }
        }

        if (!empty($dist_corps)) {
            foreach ($dist_corps AS $dist_corp) {
                $dist_corp = $dist_corp['Corporativo'];
                $autocomplete[] = array('label' => $dist_corp['name'], 'category' => 'dist_corp' . '|' . __('Distributor corporates', true));
            }
        }

        if (!empty($client_corps)) {
            foreach ($client_corps AS $client_corp) {
                $client_corp = $client_corp['Corporativo'];
                $autocomplete[] = array('label' => $client_corp['name'], 'category' => 'client_corp' . '|' . __('Client corporates', true));
            }
        }

        return $autocomplete;
    }

    /**
     * Convierte una fecha al formato que utiliza mysql 0000-00-00
     * @param date $date feche en format dd mm yyyy
     * @param char $original_separator el separador de origen puede ser / o -
     * @param char $final_separator el separador final, mysql utiliza -
     */
    public function transformToMysqlDateFormat($date, $original_separator = '/', $final_separator = '-') {
        $date_aux = explode($original_separator, $date);
        if (strlen($date_aux[0]) == 2) {//solo si trae el formato dia mes anio
            $date_aux = array_reverse($date_aux);
        }
        $date = implode($final_separator, $date_aux);
        return $date;
    }

    public function transformToMysqlDateFormatUs($date, $original_separator = '/', $final_separator = '-') {
        $date_aux = explode($original_separator, $date);
        if (strlen($date_aux[0]) == 2) {//solo si trae el formato dia mes anio
            $date_aux = [$date_aux[2],$date_aux[0], $date_aux[1]];
        }
        $date = implode($final_separator, $date_aux);
        return $date;
    }


    public function transformDateLanguagetoMysqlFormat($date, $original_separator = '/', $final_separator = '-') {
        $date_aux = explode($original_separator, $date);
        if(count($date_aux)<3){ //no parseo segun $original_separator
            $original_separator = $original_separator == "-" ? "/":"-";
            $date_aux = explode($original_separator, $date);
        }
        $date_aux = $this->_app_language == IS_ESPANIOL ? array($date_aux[2], $date_aux[1], $date_aux[0]) : array($date_aux[2], $date_aux[0], $date_aux[1]);
        $date = implode($final_separator, $date_aux);
        return $date;
    }
    
    public function transformUsDatetoMysqlFormat($date){
        $months = $this->getMonthLanguageMatrix();
        $date = str_replace(',','', $date);
        list($month_name, $day, $year) = explode(' ',$date);
        $month_number = array_search($month_name, $months); // $clave = 2;
        
        return $year.'-'.$month_number.'-'.$day;
    }

    /**
     * Comvierte una fecha del formato timestamp al formato dd/mm/yyyy segun el separador de fecha
     * @param timestamp $date timestamp
     * @param string $separator cadena de separacion
     * @param string $separator cadena de final
     * @return date
     */
    public function timestampToCorrectFormat($date, $separator = '/', $glue_separator = '') {
        //$glue_separator = $glue_separator=='' ? $separator : $glue_separator;
        //list($fecha, $hora) = explode(' ', $date);
        $datetime = explode(' ', $date);
        $fecha = $datetime[0];
        $fecha = explode('-', $fecha);
        $fecha = array_reverse($fecha);
        $fecha = implode($separator, $fecha);
        return $fecha;
    }

    /**
     * Merge date and time in a single timestamp format date
     * @param string $date string with date
     * @param string $time string with time
     */
    public function mergeDateAndTimeToTimestamp($date, $time) {
        $format = $this->_app_language == IS_ESPANIOL ? 'd/m/Y' : 'm/d/Y';
        $fecha = date_create_from_format($format, $date);
        $timestamp = date_format($fecha, 'Y-m-d');
        $time = strlen($time) == 5 ? $time . ':00' : $time;
        $timestamp .= ' ' . $time;

        return $timestamp;
    }
    
    /**
     * Transform to american/latin visual date
     * @param datetime $date
     * @return string
     */
   public function timestampToUsDate($date) {
       $datetime = explode(' ', $date);
       list($anio, $mes, $dia) = explode('-', $datetime[0]);
       $transformed = $this->getMonthName($mes).' '. $dia .', '. $anio;
       return $transformed;
    }
    
    /**
     * Regresa el nombre del mes
     * @param string $month_index index month
     * @return string month name
     */
    public function getMonthName($month_index) {
        $months = array();
        $months['00'] = '--';
        $months['01'] = 'Ene';
        $months['02'] = 'Feb';
        $months['03'] = 'Mar';
        $months['04'] = 'Abr';
        $months['05'] = 'May';
        $months['06'] = 'Jun';
        $months['07'] = 'Jul';
        $months['08'] = 'Ago';
        $months['09'] = 'Sep';
        $months['10'] = 'Oct';
        $months['11'] = 'Nov';
        $months['12'] = 'Dic';

        $months_en = array();
        $months_en['00'] = '--';
        $months_en['01'] = 'Jan';
        $months_en['02'] = 'Feb';
        $months_en['03'] = 'Mar';
        $months_en['04'] = 'Apr';
        $months_en['05'] = 'May';
        $months_en['06'] = 'Jun';
        $months_en['07'] = 'Jul';
        $months_en['08'] = 'Aug';
        $months_en['09'] = 'Sep';
        $months_en['10'] = 'Oct';
        $months_en['11'] = 'Nov';
        $months_en['12'] = 'Dec';

        $lang = $this->_app_language;
        return $lang == 'es' ? $months[$month_index] : $months_en[$month_index];
    }
    
    /**
     * Regresa el nombre del mes
     * @param string $month_index index month
     * @return string month name
     */
    public function getMonthLanguageMatrix() {
        $months = array();
        $months['01'] = 'Ene';
        $months['02'] = 'Feb';
        $months['03'] = 'Mar';
        $months['04'] = 'Abr';
        $months['05'] = 'May';
        $months['06'] = 'Jun';
        $months['07'] = 'Jul';
        $months['08'] = 'Ago';
        $months['09'] = 'Sep';
        $months['10'] = 'Oct';
        $months['11'] = 'Nov';
        $months['12'] = 'Dic';

        $months_en = array();
        $months_en['01'] = 'Jan';
        $months_en['02'] = 'Feb';
        $months_en['03'] = 'Mar';
        $months_en['04'] = 'Apr';
        $months_en['05'] = 'May';
        $months_en['06'] = 'Jun';
        $months_en['07'] = 'Jul';
        $months_en['08'] = 'Aug';
        $months_en['09'] = 'Sep';
        $months_en['10'] = 'Oct';
        $months_en['11'] = 'Nov';
        $months_en['12'] = 'Dec';

        $lang = $this->_app_language;
        return $lang == 'es' ? $months : $months_en;
    }

    public function getCompaniesFilterAccordingUserLogged() {
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $companies = '';
        $logged_user = $this->_app_credentials;
        $market_id = is_null($logged_user['company_market_id']) || $logged_user['i_group_id']==IGroup::MASTER ? 0 : $logged_user['company_market_id'];
        $country_id = is_null($logged_user['company_country_id']) || $logged_user['i_group_id']==IGroup::MASTER ? 0 : $logged_user['company_country_id'];
        $region_id = is_null($logged_user['company_region_id']) ? 0 : $logged_user['company_region_id'];

        //switch ($logged_user['role']) {
        switch ($logged_user['i_group_id']) {
            //case 'master':
            case IGroup::MASTER:
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client');
                break;
            case IGroup::MARKET_MANAGER:
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', "", "", 0, 0, $market_id);
                break;
            case IGroup::COUNTRY_MANAGER:
                $otherCountries = $logged_user['other_country_ids']!="" ? $country_id.','.$logged_user['other_country_ids'] : $country_id;
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', "", "", 0, $otherCountries);
            break;
            case IGroup::REGION_MANAGER:
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', "", "", $region_id, $country_id);
            break;
            //case 'admin'://All cliente companies
            case IGroup::ADMIN:
                $region = $logged_user['regions'] == "" ? $logged_user['region'] : $logged_user['regions'];
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegionForSalesperson('client', $region); //fix 140217 multiple region);
                break;

                /*
            case IGroup::RUBBER_DISTRIBUTOR://@todo Checar si puede ver empresas de otros territorios que compartan su corporativo, probar con empresa id = 71
                $parent_company = $this->getCompanyIdsManagerUser(); //Esto traera ids de distribuidores
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', '', $parent_company, 0, $country_id, $market_id);//obtener los ids de clientes
            break;*/

            //case 'manager'://EL USUARIO ES UN MANAGER
            case IGroup::DISTRIBUTOR_MANAGER://@todo Checar si puede ver empresas de otros territorios que compartan su corporativo, probar con empresa id = 71
                $parent_company = $this->getCompanyIdsManagerUser(); //Esto traera ids de distribuidores
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', '', $parent_company, 0, $country_id, $market_id);//, $region_id, $country_id, $market_id); //obtener los ids de clientes
                break;
                /*
            case IGroup::CLIENT_MANAGER:
                $parent_company = $this->getCompanyIdsManagerUser(); //Esto traera ids de clientes
                $companies = $parent_company;
            break;*/
            //case 'distributor':
            case IGroup::DISTRIBUTOR: case IGroup::RUBBER_DISTRIBUTOR:
                $companies = $this->Empresa->getClientCompanyIdsByTypeAndRegion('client', $logged_user['region'], $logged_user['id_empresa']);
                break;
            //case 'client':
            case IGroup::CLIENT: case IGroup::CLIENT_MANAGER:
                $companies = $logged_user['id_empresa'];
                break;
        }



        if($logged_user['puesto']==UsuariosEmpresa::IS_SALESPERSON && $logged_user['i_group_id']==IGroup::TERRITORY_MANAGER){
            $userRelations = $this->UsuariosEmpresa->findById($logged_user['id'], ['UsuariosEmpresa.id']);
            if(!empty($userRelations['SharedClients'])){
                $clientIdsShared = array_column($userRelations['SharedClients'], 'id');
                $clientIdsShared = implode(',',$clientIdsShared);
                $companies = $companies=="" ? $clientIdsShared : $companies.','.$clientIdsShared;
            }
        }

        if($logged_user['i_group_id']==IGroup::TERRITORY_MANAGER){
            $whiteListRegionIds = [1,2,3,4,5,6,7,10,11,15,16,18]; //regions us, canada, germany and russia
            //if(in_array($logged_user['company_region_id'], $whiteListRegionIds)){
               // $companies = $companies=="" ? "686" : $companies.',686'; //Client test AA - P Test ->Ya no se desplegaran los items de Demo Pete por ordenes de Pez @18/04/2018
            //}
        }

        //mail("elalbertgd@gmail.com",'companies from web',print_r([$companies],true));
        return is_null($companies) ? 0 : $companies;
    }

    public function getConveyorsBasicFieldsUsingFilters($filter_companies, $query_numbers = '', $sort = '', $rows = 0, $from = 0) {
        $this->UsConveyor = ClassRegistry::init('UsConveyor');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $conveyors = [];
        $region = $this->getRegion();
        $credentials = $this->_app_credentials;
        if($credentials['i_group_id'] == IGroup::MASTER || $credentials['i_group_id'] == IGroup::MARKET_MANAGER || $credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){ //if master, get us and mx conveyors

            //$conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
            $conveyors = $this->Conveyor->findAllConveyorsBasic($filter_companies, $query_numbers, $sort, $rows, $from);

            //mail("elalbertgd@gmail.com","like master",print_r($conveyors,true));
        }else{ //is admin, or dist, depend region
            if($region=="" && $credentials['i_group_id']>IGroup::TERRITORY_MANAGER){
                $conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
            }else if($credentials['i_group_id']<=IGroup::DISTRIBUTOR){
                $conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                //$conveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                //mail("elalbertgd@gmail.com","like dist",print_r($conveyors,true));
            }else if(in_array($region, ["US","CA"])){ //if user region is (@todo Checar si un dist manager puede ver todas)
                $conveyors = $this->UsConveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                //esto desaparecera cuando se haga la migracion de las bandas us que se quedaron como normales
                if($credentials["id"]==1 || $credentials["id"]==2 || $credentials["id"]==239 || $credentials["id"]==88 || $credentials["id"]==89){
                    $otherCnveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                    $conveyors = array_merge($conveyors, $otherCnveyors);
                }
            }else{
                $conveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
            }
        }

        return $conveyors;
    }

    public function getConveyorsUsingFilters($filter_companies, $query_numbers = '', $sort = '', $rows = 0, $from = 0) {
        $this->UsConveyor = ClassRegistry::init('UsConveyor');
        $this->Conveyor = ClassRegistry::init('Conveyor');

        $conveyors = [];
        //var_dump($rows);
        if((Int)$rows >= 0 ){
            $region = $this->getRegion();
            $credentials = $this->_app_credentials;
            if($credentials['i_group_id'] == IGroup::MASTER || $credentials['i_group_id'] == IGroup::MARKET_MANAGER || $credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){ //if master, get us and mx conveyors

                $conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);

                //mail("elalbertgd@gmail.com","like master",print_r($conveyors,true));
            }else{ //is admin, or dist, depend region
                if($region=="" && $credentials['i_group_id']>IGroup::TERRITORY_MANAGER){
                    $conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                }else if($credentials['i_group_id']<=IGroup::DISTRIBUTOR){
                    $conveyors = $this->Conveyor->findAllConveyorsWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                    //$conveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                    //mail("elalbertgd@gmail.com","like dist",print_r($conveyors,true));
                }else if(in_array($region, ["US","CA"])){ //if user region is (@todo Checar si un dist manager puede ver todas)
                    $conveyors = $this->UsConveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                    //esto desaparecera cuando se haga la migracion de las bandas us que se quedaron como normales
                    if($credentials["id"]==1 || $credentials["id"]==2 || $credentials["id"]==239 || $credentials["id"]==88 || $credentials["id"]==89){
                        $otherCnveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                        $conveyors = array_merge($conveyors, $otherCnveyors);
                    }
                }else{
                    $conveyors = $this->Conveyor->findAllWithCompany($filter_companies, $query_numbers, $sort, $rows, $from);
                }
            }
        }

        //var_dump(count($conveyors));

        return $conveyors;
    }

    public function getTrackingConveyorsUsingFilters($filter_companies, $query_numbers = '', $sort = '', $rows = 0, $from = 0) {
        $this->TrackingConveyor = ClassRegistry::init('TrackingConveyor');
        $credentials = $this->_app_credentials;
        $conveyors = $this->TrackingConveyor->findAllWithCompany($filter_companies, $credentials, $query_numbers, $sort, $rows, $from);
        return $conveyors;
    }

    public function getCustomReportsUsingFilters($filter_companies, $query = '', $sort = '', $rows = 0, $from = 0) {
        $this->CustomReport = ClassRegistry::init('CustomReport');
        $conveyors = $this->CustomReport->findAllWithCompany($filter_companies, $query, $sort, $rows, $from);
        return $conveyors;
    }

    public function getSavingsUsingFilters($filter_companies, $query = '', $sort = '', $rows = 0, $from = 0) {
        $this->Saving = ClassRegistry::init('Saving');
        $savings = $this->Saving->findAllWithCompany($filter_companies, $query, $sort, $rows, $from);
        return $savings;
    }

    //Get company ids for manager type
    public function getCompanyIdsManagerUser() {
        $this->Empresa = ClassRegistry::init('Empresa');
        $company_ids = '';
        $credentials = $this->_app_credentials;
        $manager_corporate = Configure::read('manager_corporate'); //the corporate
        $type_manager = Configure::read('type_manager'); //the type of company (client or dist)
        //mail("elalbertgd@gmail.com",'test',print_r([$manager_corporate, $type_manager],true));

        $market_id = $credentials['company_market_id'];
        $country_id = $credentials['company_country_id'];
        $region_id = $credentials['company_region_id'];

        if (!is_null($type_manager) && $manager_corporate>0) {
            //Si type_manager es client, encontraria todos los clientes del mismo corporativo
            $assoc_companies = $this->Empresa->findByRegionAndTypeWithCorporate($type_manager, '', $manager_corporate, 0, $country_id, $market_id); //7, $region_id, $country_id, $market_id);
            //mail("elalbertgd@gmail.com",'corporate',print_r([$type_manager, $manager_corporate],true));
            //mail("elalbertgd@gmail.com","corps",print_r([$type_manager, $manager_corporate, $assoc_companies],true));
            if (!empty($assoc_companies)) {
                foreach ($assoc_companies AS $company) {
                    $empresa = $company['Empresa'];
                    $company_ids .= $empresa['id'] . ',';
                }
                $company_ids = substr($company_ids, 0, -1);
            }
        }else if($manager_corporate <= 0 && !is_null($type_manager) && $type_manager==UsuariosEmpresa::IS_DIST){
            $credentials = $this->_app_credentials;
            $company_ids = $credentials['id_empresa'];
        }else if($manager_corporate <= 0 && !is_null($type_manager) && $type_manager==UsuariosEmpresa::IS_CLIENT){
            $credentials = $this->_app_credentials;
            $company_ids = $credentials['id_empresa'];//retornar el mismo cliente
        }
        return $company_ids;
    }

    /**
     * Procesa el video para generar uno de menor tamanio y con sus respectivo thumbnail
     * @param string $path_video es el path del video original
     * @param string $name_video es el nombre del archivo
     * @param string $ori_ext es la extension del archivo origen
     * @return string el path del thumbnail
     */
    public function process_video_mp4($path_video, $name_video, $ori_ext) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video . '.' . $ori_ext;
        $out_video = $path_video . '/' . $name_video . '.flv';
        $video_for_image = $path_video . '/' . $name_video . '.mpeg';
        $out_thumbnail = $path_video . '/' . $name_video . '.jpg';
        //Procesamos el video
        $videoEncoder->convert_video($in_video, $out_video, 480, 360, true); //Del original cargado obtenemos el video FLV
        //Generamos el video mpeg temporal para sacar la imagen
        $videoEncoder->convert_video($in_video, $video_for_image, 480, 360, false); //Del original cargado obtenemos el MPEG
        //Generamos un thumbnail del video
        $videoEncoder->grab_image($video_for_image, $out_thumbnail);


        //$out_mp4_video = $path_video . '/' . $name_video . '.mp4';
        //$videoEncoder->convert_video($out_video, $out_mp4_video, 480, 360, true);//Del Mpeg obtenemos el MP4
        //Eliminamos el archivo origen
        $videoEncoder->remove_uploaded_video($in_video);
        $videoEncoder->remove_uploaded_video($video_for_image);

        return $out_thumbnail;
    }

    /**
     * Procesa el video para generar uno de menor tamanio y con sus respectivo thumbnail
     * @param string $path_video es el path del video original
     * @param string $name_video es el nombre del archivo
     * @param string $ori_ext es la extension del archivo origen
     * @return string el path del thumbnail
     */
    public function process_video($path_video, $name_video, $ori_ext) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video . '.' . $ori_ext;
        $out_thumbnail = "";

        $out_mp4_video = $path_video . '/' . $name_video . '.mp4';
        switch(strtolower($ori_ext)){
            case 'mov':
                $videoEncoder->convert_mov_to_mp4($in_video, $out_mp4_video); //Del Mpeg obtenemos el MP4 -> video queda en server
            break;
            case 'wmv':
                $videoEncoder->convert_wmv_to_mp4($in_video, $out_mp4_video); //Del Mpeg obtenemos el MP4 -> video queda en server
            break;
            default:
            break;
        }

        //Eliminamos el archivo origen
        $videoEncoder->remove_uploaded_video($in_video);

        return $out_thumbnail;
    }

    /**
     * Procesa el video para generar uno de menor tamanio y con sus respectivo thumbnail
     * @param string $path_video es el path del video original
     * @param string $name_video es el nombre del archivo
     * @param string $ori_ext es la extension del archivo origen
     * @return string el path del thumbnail
     */
    public function process_video_X($path_video, $name_video, $ori_ext) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video . '.' . $ori_ext;
        $out_video = $path_video . '/' . $name_video . '_c.flv';
        $video_for_image = $path_video . '/' . $name_video . '_c.mpeg';
        $out_thumbnail = $path_video . '/' . $name_video . '.jpg';
        //Procesamos el video
        $videoEncoder->convert_video($in_video, $out_video, 800, 600, true); //Del original cargado obtenemos el video FLV -> Video queda en server
        //Generamos el video mpeg temporal para sacar la imagen
        $videoEncoder->convert_video($in_video, $video_for_image, 800, 600, false); //Del original cargado obtenemos el MPEG
        //Generamos un thumbnail del video
        $videoEncoder->grab_image($video_for_image, $out_thumbnail);


        $out_mp4_video = $path_video . '/' . $name_video . '_c.mp4';
        $videoEncoder->convert_video($video_for_image, $out_mp4_video, 800, 600, true); //Del Mpeg obtenemos el MP4 -> video queda en server
        //Eliminamos el archivo origen
        $videoEncoder->remove_uploaded_video($in_video);
        $videoEncoder->remove_uploaded_video($video_for_image); //eliminamos el video del cual se extrajo el frame

        return $out_thumbnail;
    }

    /**
     * Procesa el video para generar uno de menor tamanio y con sus respectivo thumbnail
     * @param string $path_video es el path del video original
     * @param string $name_video es el nombre del archivo
     * @return string el path del thumbnail
     */
    public function get_thumbnail_video($path_video, $name_video) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video;
        list($namevideo, $extvideo) = explode('.', $name_video);
        $video_for_image = $path_video . '/' . $namevideo . '.mpeg';
        $out_thumbnail = $path_video . '/' . $namevideo . '.jpg';

        //Generamos el video mpeg temporal para sacar la imagen
        $videoEncoder->convert_video($in_video, $video_for_image, 480, 360, false);
        //Generamos un thumbnail del video
        $videoEncoder->grab_image($video_for_image, $out_thumbnail);

        //Eliminamos el archivo origen
        $videoEncoder->remove_uploaded_video($video_for_image);

        return $out_thumbnail;
    }

    public function get_fast_thumbnail_video($path_video, $name_video) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video;
        list($namevideo, $extvideo) = explode('.', $name_video);
        $out_thumbnail = $path_video . '/' . $namevideo . '.jpg';

        //Generamos un thumbnail del video
        $videoEncoder->grab_image_from_mp4($in_video, $out_thumbnail);
        return $out_thumbnail;
    }

    public function createAssocArrayFromCsvValuesForMetaUnits($values){
        $data = [];
        foreach ($values AS $value_arr){
            $data[$value_arr[0]] = [$value_arr[1],$value_arr[2]];
        }
        return $data;
    }

    //Return the associated units of one conveyor
    public function getMetaUnitsMx($conveyor){
        $defaults = array_map('str_getcsv', file('files/calculos/default_units_mx.csv'));
        $unitsForFields = $this->createAssocArrayFromCsvValuesForMetaUnits($defaults);

        $metaUnits = $conveyor['meta_units'];
        if(trim($metaUnits)!=''){
            $unitsFields = explode('||', $metaUnits);
            foreach ($unitsFields AS $unitField){
                list($field, $unit) = explode('=', $unitField);
                array_unshift($unitsForFields[$field], utf8_encode($unit)); //set the unit saved in conveyor
            }
        }

        return $unitsForFields;
    }

    public function calcLifeEstimationBanda($conveyor) {
        $this->ConfigTransporter = ClassRegistry::init('ConfigTransporter');
        $response = array('estimated_lifetime' => null, 'expected_tonnage' => null, 'approx_change_date' => null, 'recommended_conveyor_in' => null, 'recommended_conveyor_mm' => null, 'disclaimer_min_width' => null, 'disclaimer_max_width' => null);

        $conveyor = $this->Converter->process_convertion($conveyor, 'es');

        $full_conveyor = $conveyor;
        $conveyor = $conveyor['Conveyor'];

        /*
          $conveyor['banda_operacion'] = $conveyor['banda_operacion']=='' || !is_numeric($conveyor['banda_operacion']) ? 0 : $conveyor['banda_operacion'];
          $conveyor['banda_velocidad'] = $conveyor['banda_velocidad']=='' || is_null($conveyor['banda_velocidad'])  ? 0 : $conveyor['banda_velocidad'];
          $conveyor['trans_capacidad'] = $conveyor['trans_capacidad']=='' || is_null($conveyor['trans_capacidad']) ? 0 : $conveyor['trans_capacidad'];
          $conveyor['trans_distancia_centros'] = $conveyor['trans_distancia_centros']=='' || is_null($conveyor['trans_distancia_centros']) ? 0 : $conveyor['trans_distancia_centros'];

          if ((int)$conveyor['trans_capacidad']>0 && (int)$conveyor['trans_distancia_centros']>0 && (int)$conveyor['banda_velocidad']>0 && (int)$conveyor['banda_operacion']>0 &&
          !is_null($conveyor['mat_grado_mat_transportado']) && !is_null($conveyor['mat_condicion_alimentacion']) && !is_null($conveyor['id_espesor_cubierta_sup'])) { */

        if ($this->sePuedeCalcularVidaEstimada($full_conveyor)) {
            $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric
            //if (!is_null($conveyor['mat_grado_mat_transportado']) && !is_null($conveyor['mat_condicion_alimentacion']) && !is_null($conveyor['id_espesor_cubierta_sup'])) {
            $grado_material_transportado = $this->ConfigTransporter->getOneById($conveyor['mat_grado_mat_transportado']);
            $condicion_alimentacion = $this->ConfigTransporter->getOneById($conveyor['mat_condicion_alimentacion']);
            $espesor_cubierta_sup = $this->ConfigTransporter->getOneById($conveyor['id_espesor_cubierta_sup']);

            $grado_material_transportado = $grado_material_transportado[0]['ConfigTransporter'];
            $condicion_alimentacion = $condicion_alimentacion[0]['ConfigTransporter'];
            $espesor_cubierta_sup = $espesor_cubierta_sup[0]['ConfigTransporter'];

            $belt_speed = $conveyor['banda_velocidad'];
            if($metaUnits['velocidad_banda'][0]!='m/s'){ //hacer conversion si no esta en las unidades deseadas
                $belt_speed = $belt_speed * 0.00507999983744; //Velocidad en m/s
            }

            $distance_between_centers = $conveyor['trans_distancia_centros'];
            if($metaUnits['distancia_centros'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
                //1ft -> 0.3048 mts = x ft * 0.3048
                $distance_between_centers = $distance_between_centers * 0.3048;
            }
            $mass_flow = $conveyor['trans_capacidad']; //siempre es capturada en (t/h)
            $operating_hours = $conveyor['banda_operacion'];
            //$operating_hours = 8064;

            $load_percent = $conveyor['trans_carga'];
            //$cover_for_wear = $conveyor['banda_espesor_cubiertas'];
            $cover_for_wear = $espesor_cubierta_sup['valor'];//siempre vienen mm

            $rel_mat_conveyed_feeded_cond = array(
                'favorable' => array('grado1' => 0.4, 'grado2' => 0.6, 'grado3' => 1.4),
                'estandar' => array('grado1' => 0.8, 'grado2' => 1.4, 'grado3' => 2.4),
                'desfavorable' => array('grado1' => 1.6, 'grado2' => 2.6, 'grado3' => 4.0),
            );

            $grade_conveyed_mat = $grado_material_transportado['valor'];
            $feeding_conds = $condicion_alimentacion['valor'];
            $wear_factor_sw = $rel_mat_conveyed_feeded_cond[$feeding_conds][$grade_conveyed_mat];

            $estimation_monts = $cover_for_wear * 200 * $distance_between_centers * 12 / ($belt_speed * $wear_factor_sw * $operating_hours * 3.6);
            $estimation_monts_complete = $estimation_monts;
            $estimation_monts = $estimation_monts / 12;
            $estimation_tons = $operating_hours * $estimation_monts * $mass_flow / 1000000;

            $response['estimated_lifetime'] = round($estimation_monts, 1);
            $response['expected_tonnage'] = round($estimation_tons, 0);



            $change_date = null;
            if ($conveyor['banda_fecha_instalacion'] != '0000-00-00') {
                $estimation_monts_complete = round($estimation_monts_complete, 0);
                $response['approx_change_date'] = date('Y-m-d', strtotime("+" . $estimation_monts_complete . " months", strtotime($conveyor['banda_fecha_instalacion'])));

                /*list($years, $fraction) = explode('.', $estimation_monts);
                if($years>0){
                    $estimation_monts_complete = round($estimation_monts_complete, 0);
                    var_dump($estimation_monts_complete);
                    $response['approx_change_date'] = date('Y-m-d', strtotime("+" . $estimation_monts_complete . " months", strtotime($conveyor['banda_fecha_instalacion']))); //sumar tantos años como hayan sido calculados
                }else{//check fraction, get the number of months according to year fraction
                    $months_year = $estimation_monts * 12; //use the complete fraction year
                    $months_year = round($months_year, 0);
                    $response['approx_change_date'] = date('Y-m-d', strtotime("+" . $months_year . " months", strtotime($conveyor['banda_fecha_instalacion']))); //sumar tantos años como hayan sido calculados
                }*/

            }
        }


        //Calculamos la banda recomendada
        if ($this->sePuedeCalcularBandaRecomendada($full_conveyor)) {
            $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

            $desgaste = $this->calcFactorDeDesgasteBanda($full_conveyor);
            $abrasion = $this->calcAbrasionBanda($full_conveyor);
            $compuesto = $this->calcCompuestoBanda($full_conveyor);
            $piw = $this->calcPiwBanda($full_conveyor);

            $total_cover = !is_null($desgaste) && !is_null($abrasion) ? $desgaste[1] + $abrasion[1] : null;
            $espesor_recomendado = !is_null($total_cover) ? $this->calcEspesorRecomendadoBanda($total_cover) : array(null, null);

            $nombre_banda = $this->calcNombreBanda($piw, $full_conveyor);

            $calculo_banda = $nombre_banda;
            $nombre_banda = $nombre_banda['nombres_banda'];
            $indice_ancho_maximo = 0;

            if($metaUnits['distancia_centros'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
                //1ft -> 0.3048 mts = x ft * 0.3048
                $conveyor['trans_distancia_centros'] = $conveyor['trans_distancia_centros'] * 0.3048;
            }

            if($metaUnits['densidad_material'][0]!='(lb/cu.ft)'){ //hacer conversion si no esta en las unidades deseadas
                //1kg -> 2.20462 lbs = x kg * 2.20462
                $conveyor['mat_densidad'] = $conveyor['mat_densidad'] * 2.20462; //siempre se manejara (lb/cu.ft) -> libra por pie cubico
            }

            //Si no es ST
            if ($conveyor['trans_distancia_centros'] <= 800) {
                $response['recommended_conveyor_in'] = $nombre_banda[0] . ' ' . $espesor_recomendado[0] . ' ' . $compuesto;
                $indice_ancho_maximo = $this->indiceAnchoMaximo($conveyor['mat_densidad']);
            } else {//Si es ST no aplica recommended_conveyor_in
                $conveyor['rod_angulo_acanalamiento'] = 30;
                $response['recommended_conveyor_in'] = '-';
                $indice_ancho_maximo = 1;
            }
            $response['recommended_conveyor_mm'] = $nombre_banda[1] . ' ' . $espesor_recomendado[1] . ' ' . $compuesto;

            $calculo_banda = $calculo_banda['acanalamiento'][$conveyor['rod_angulo_acanalamiento']];
            $ancho_minimo = $calculo_banda[0];
            $ancho_maximo = $calculo_banda[$indice_ancho_maximo];


            if($metaUnits['ancho_banda'][0]!='in'){ //hacer conversion si no esta en las unidades deseadas
                //1mm -> 0.0393701in = x mm * 0.0393701
                $conveyor['banda_ancho'] = $conveyor['banda_ancho'] * 0.0393701; //se hace la comparacion con pulgadas
            }

            $response['disclaimer_min_width'] = $conveyor['banda_ancho'] < $ancho_minimo ? __('For throughing condition, the minimum width should be: %s', array($ancho_minimo)) : __('The Minimum Belt Width is correct', true);
            if (is_numeric($ancho_maximo)) {
                if ($conveyor['banda_ancho'] > $ancho_maximo) {
                    $response['disclaimer_max_width'] = __("This Belt can't handle the load support selected, the maximum is: %s", array($ancho_maximo));
                    /* Calcular la que se recomienda segun ancho proporcionado * */
                    $nombre_banda = $this->calcOtroNombreBanda($full_conveyor);
                    if (!is_null($nombre_banda)) {
                        $response['disclaimer_max_width'] .= '<p>*' . __("For this width and load support, consider: %s", array($nombre_banda[0])) . '</p>';
                        $response['recommended_conveyor_in'] = $nombre_banda[0] . ' ' . $espesor_recomendado[0] . ' ' . $compuesto;
                        $response['recommended_conveyor_mm'] = $nombre_banda[1] . ' ' . $espesor_recomendado[1] . ' ' . $compuesto;
                    } else {
                        $response['recommended_conveyor_in'] = '-';
                        $response['recommended_conveyor_mm'] = '-';
                    }
                } else {
                    $response['disclaimer_max_width'] = __('With the load support mentioned, the Maximum Width is Correct', true);
                }
            } else {//Es NR
                $response['disclaimer_max_width'] = __('Please review your data, any belt can handle the width desired', true);
            }
        }

        return $response;
    }

    public function indiceAnchoMaximo($densidad) {
        $indice = 0;
        $limites = array(40, 80, 120, 121);
        foreach ($limites AS $index => $limit) {
            $indice = $index;
            if ($densidad <= $limit) {
                break;
            }
        }
        return $indice + 1;
    }

    public function calcFactorDeDesgasteBanda($conveyor) {
        $this->ConfigTransporter = ClassRegistry::init('ConfigTransporter');

        //$conveyor['mat_condicion_carga'], $conveyor['mat_frecuencia_carga'],$conveyor['mat_tamanio_granular'],$conveyor['mat_tipo_densidad'],$conveyor['mat_agresividad']
        $wear_table = array_map('str_getcsv', file('files/calculos/matriz_desgaste.csv'));
        $conveyor = $conveyor['Conveyor'];

        $factor_de_desgaste = array(0, 0);
        $mat_condicion_carga = $this->ConfigTransporter->getOneById($conveyor['mat_condicion_carga']);
        $mat_frecuencia_carga = $this->ConfigTransporter->getOneById($conveyor['mat_frecuencia_carga']);
        $mat_tamanio_granular = $this->ConfigTransporter->getOneById($conveyor['mat_tamanio_granular']);
        $mat_tipo_densidad = $this->ConfigTransporter->getOneById($conveyor['mat_tipo_densidad']);
        $mat_agresividad = $this->ConfigTransporter->getOneById($conveyor['mat_agresividad']);

        $mat_condicion_carga = $mat_condicion_carga[0]['ConfigTransporter']['valor'];
        $mat_frecuencia_carga = $mat_frecuencia_carga[0]['ConfigTransporter']['valor'];
        $mat_tamanio_granular = $mat_tamanio_granular[0]['ConfigTransporter']['valor'];
        $mat_tipo_densidad = $mat_tipo_densidad[0]['ConfigTransporter']['valor'];
        $mat_agresividad = $mat_agresividad[0]['ConfigTransporter']['valor'];


        foreach ($wear_table AS $wear) {
            if ($wear[0] == $mat_condicion_carga && $wear[1] == $mat_frecuencia_carga &&
                    $wear[2] == $mat_tamanio_granular && $wear[3] == $mat_tipo_densidad && $wear[4] == $mat_agresividad) {
                $factor_de_desgaste[0] = $wear[5];
                break;
            }
        }
        $factor_de_desgaste[1] = $factor_de_desgaste[0] * .04;
        return $factor_de_desgaste;
    }

    public function calcAbrasionBanda($conveyor) {
        $this->ConfigTransporter = ClassRegistry::init('ConfigTransporter');

        $conveyor = $conveyor['Conveyor'];
        $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

        $abrasion = array(0, 0);
        $grado_material_transportado = $this->ConfigTransporter->getOneById($conveyor['mat_grado_mat_transportado']);
        $condicion_alimentacion = $this->ConfigTransporter->getOneById($conveyor['mat_condicion_alimentacion']);

        $grado_material_transportado = $grado_material_transportado[0]['ConfigTransporter'];
        $condicion_alimentacion = $condicion_alimentacion[0]['ConfigTransporter'];

        $grade_conveyed_mat = $grado_material_transportado['valor'];
        $feeding_conds = $condicion_alimentacion['valor'];
        $belt_speed = $conveyor['banda_velocidad'];
        if($metaUnits['velocidad_banda'][0]!='m/s'){ //hacer conversion si no esta en las unidades deseadas
            $belt_speed = $belt_speed * 0.00507999983744; //Velocidad en m/s
        }


        $operating_hours = $conveyor['banda_operacion'];
        //$operating_hours = 24;
        //$operating_hours = 8064;
        $rel_mat_conveyed_feeded_cond = array(
            'favorable' => array('grado1' => 0.4, 'grado2' => 0.6, 'grado3' => 1.4),
            'estandar' => array('grado1' => 0.8, 'grado2' => 1.4, 'grado3' => 2.4),
            'desfavorable' => array('grado1' => 1.6, 'grado2' => 2.6, 'grado3' => 4.0),
        );
        $wear_factor_sw = $rel_mat_conveyed_feeded_cond[$feeding_conds][$grade_conveyed_mat];
        $distance_between_centers = $conveyor['trans_distancia_centros'];
        if($metaUnits['distancia_centros'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
            //1ft -> 0.3048 mts = x ft * 0.3048
            $distance_between_centers = $distance_between_centers * 0.3048;
        }


        //$abrasion[0] = 1 + (3.6 * $belt_speed * $wear_factor_sw * $operating_hours) / (200 * $distance_between_centers);        
        $abrasion[0] = 0 + (3.6 * $belt_speed * $wear_factor_sw * $operating_hours) / (200 * $distance_between_centers);
        $abrasion[1] = $abrasion[0] * 0.04;
        return $abrasion;
    }

    /**
     * Para efectos de calculo, los valores estan convertidos a espanol, aunque
     * cuando hay sido capturada en ingles
     * @param type $conveyor
     * @return string
     */
    public function calcCompuestoBanda($conveyor) {
        $conveyor = $conveyor['Conveyor'];
        $compuesto_calculado = null;
        $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

        $descripcion_material = $this->ConfigTransporter->getOneById($conveyor['mat_descripcion']);
        $descripcion_material = $descripcion_material[0]['ConfigTransporter'];

        $mat_temperatura = $conveyor['mat_temperatura'];
        if($metaUnits['temperatura'][0]!='°C') { //hacer conversion si no esta en las unidades deseadas
            //°F a °C -> Resta 32, después multiplica por 5, después divide entre 9
            $mat_temperatura = (($mat_temperatura-32)*5)/9;
        }

        $mat_aceite = $conveyor['mat_aceite'];

        $mat_temperatura = trim($mat_temperatura) != '' && is_numeric($mat_temperatura) ? $mat_temperatura : null;
        $mat_temperatura = !is_null($mat_temperatura) && $mat_temperatura >= 86 ? $mat_temperatura : null;

        if (!is_null($mat_aceite)) {
            $mat_aceite = $this->ConfigTransporter->getOneById($mat_aceite);
            $mat_aceite = $mat_aceite[0]['ConfigTransporter']['valor_en'];
        }

        $codigo_abrasion = $descripcion_material['codigo_abrasion'];
        $descripcion_material = $descripcion_material['valor_en'];

        $compuestos = array(
            1 => 'Sentry',
            2 => 'Sentry AR',
            3 => 'Sentry Plus',
            4 => 'Din Y',
            5 => 'Din W',
            6 => 'Din X',
            7 => 'Durastar',
            8 => 'Durastar AR',
            9 => 'Everlast',
            10 => 'Lumberjack',
            11 => 'MOR',
            12 => 'SOR',
            13 => 'OilSand',
            14 => 'Vulcan Classicc',
            15 => 'Vulcan Prime',
            16 => 'Vulcan Optimum',
            17 => 'Coaline',
            18 => 'Coaline Plus',
            19 => 'FireSentinel',
            20 => 'ContiGrain',
            21 => 'Coaline OR',
            22 => 'Vulcan Prime FG+'
        );

        if (preg_match("/\bcoke rated\b/i", $descripcion_material)) {//Coque clasificado
            $compuesto_calculado = $compuestos[8]; //Durastar AR
        } else if (preg_match("/\bcoal\b/i", $descripcion_material)) {//carbon, checar ubicacion
            $ubicacion_transportador = $this->ConfigTransporter->getOneById($conveyor['trans_ubicacion']);
            $ubicacion_transportador = $ubicacion_transportador[0]['ConfigTransporter']['valor_en'];
            if (preg_match("/\bunderground\b/i", $ubicacion_transportador)) {
                $compuesto_calculado = $compuestos[19]; //FireSentinel
            } else {
                $compuesto_calculado = $compuestos[17]; //Coaline
            }
        } else if (!is_null($mat_temperatura) && !is_null($mat_aceite) && !preg_match("/\bno oil\b/i", $mat_aceite)) {//Tiene temperatura y aceite
            $compuesto_calculado = $compuestos[20]; //ContiGrain
        } else if (!is_null($mat_temperatura) && (is_null($mat_aceite) || preg_match("/\bno oil\b/i", $mat_aceite))) {//Tiene temperatura, pero no aceite
            $rel_compuestos = array(150 => 14, 205 => 15, 220 => 16, 504 => 22);
            $compuesto_seleccionado = 0;
            foreach ($rel_compuestos AS $temp => $compuesto) {
                $compuesto_seleccionado = $compuesto;
                if ($mat_temperatura <= $temp) {
                    break;
                }
            }
            $compuesto_calculado = $compuestos[$compuesto];
        } else if (is_null($mat_temperatura) && !is_null($mat_aceite) && !preg_match("/\bno oil\b/i", $mat_aceite)) {//NO Tiene temperatura, pero si aceite
            $compuesto_calculado = preg_match("/\bmoderate\b/i", $mat_aceite) ? $compuestos[11] : $compuestos[12];
        } else {
            $tamanio_material = $this->ConfigTransporter->getOneById($conveyor['mat_tamanio']);
            $tamanio_material = $tamanio_material[0]['ConfigTransporter']['valor_en'];

            $rel_compuestos = array(12 => 5, 50 => 5, 100 => 1, 200 => 2, 300 => 3, 400 => 4, 500 => 6, 650 => 7);
            $compuesto_seleccionado = 0;
            foreach ($rel_compuestos AS $tamanio => $compuesto) {
                $compuesto_seleccionado = $compuesto;
                if ($tamanio_material <= $tamanio) {
                    break;
                }
            }
            $compuesto_calculado = $compuestos[$compuesto];
            //$compuesto_calculado = $compuestos[$codigo_abrasion];//Segun codigo de abrasion
        }
        return $compuesto_calculado;
    }

    public function calcPiwBanda($conveyor) {
        $conveyor = $conveyor['Conveyor'];
        $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

        if($metaUnits['densidad_material'][0]!='(lb/cu.ft)'){ //hacer conversion si no esta en las unidades deseadas
            //1kg -> 2.20462 lbs = x kg * 2.20462
            $conveyor['mat_densidad'] = $conveyor['mat_densidad'] * 2.20462; //siempre se manejara (lb/cu.ft)
        }

        $ro = $conveyor['mat_densidad']; //Densidad de material t/m3
        $ro = $ro * 0.01601846; //conversion de lb/ft3 a t/m3 -> a tonelada por metro cubico

        $C_BETA = 15; //Angulo reposo
        $Qm = $conveyor['trans_capacidad']; //Capacidad de tranportador en t/h , siempre es capturado en unidades t/h

        if($metaUnits['tamanio_terron'][0]!='mm') { //hacer conversion si no esta en las unidades deseadas
            //1in ->25.4mm = x in * 25.4
            $conveyor['mat_tam_terron'] = $conveyor['mat_tam_terron']*25.4;
        }
        $k = $conveyor['mat_tam_terron']; //Tamanio max terron in mm

        $C_f = 0.0165;
        $C_TEMP = 160; //Temperatura
        $C_g = 9.8;
        $C_hrel = 0.01;

        if($metaUnits['distancia_centros'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
            //1ft -> 0.3048 mts = x ft * 0.3048
            $conveyor['trans_distancia_centros'] = $conveyor['trans_distancia_centros']*0.3048;
        }
        $L = $conveyor['trans_distancia_centros']; //Distancia entre centros in mts

        if($metaUnits['elevacion'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
            //1ft -> 0.3048 mts = x ft * 0.3048
            $conveyor['trans_elevacion'] = $conveyor['trans_elevacion']*0.3048;
        }
        $H = $conveyor['trans_elevacion']; //Elevacion in mts

        $gra = $conveyor['trans_angulo_inclinacion']; //Angulo de inclinacion

        if($metaUnits['ancho_banda'][0]!='mm') { //hacer conversion si no esta en las unidades deseadas
            //1in ->25.4mm = x in * 25.4
            $conveyor['banda_ancho'] = $conveyor['banda_ancho']*25.4;
        }
        //$B = (int) $conveyor['banda_ancho'] * 25; //Ancho banda, lo maneja en mm
        $B = (int) $conveyor['banda_ancho']; //ya esta en mm

        if($metaUnits['velocidad_banda'][0]!='m/s') { //hacer conversion si no esta en las unidades deseadas
            //1fpm -> 0.00507999983744 m/s = x fpm * 0.00507999983744
            $conveyor['banda_velocidad'] = $conveyor['banda_velocidad'] * 0.00507999983744; //Velocidad en m/s
        }
        $v = $conveyor['banda_velocidad']; //Velocidad in m/s
        //$v = $v * 0.00507999983744; //Velocidad en m/s

        $carga_grados = $this->ConfigTransporter->getOneById($conveyor['rod_ang_carga']);
        $carga_grados = $carga_grados[0]['ConfigTransporter'];
        $carga_grados = $carga_grados['valor'];

        $carga_pulgadas = $this->ConfigTransporter->getOneById($conveyor['rod_diam_carga']);
        $carga_pulgadas = $carga_pulgadas[0]['ConfigTransporter'];
        $carga_pulgadas = $carga_pulgadas['valor'];

        $Iam = $carga_grados; //Carga en grados

        if($metaUnits['espacio_LC_rodillo'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
            //1ft -> 0.3048 mts = x ft * 0.3048
            $conveyor['rod_espacio_ldc'] = $conveyor['rod_espacio_ldc']*0.3048;
        }
        $lo = $conveyor['rod_espacio_ldc']; //Espacio LC (M) in mts

        $lu = $lo * 2; //siempre lo x 2

        if($metaUnits['sel_diam_rodillos_ldc'][0]!='mm') { //hacer conversion si no esta en las unidades deseadas
            //1in ->25.4mm = x in * 25.4
            $carga_pulgadas = $carga_pulgadas*25.4;
        }
        //$ld = $carga_pulgadas * 25; //Lo maneja en mm, es el diametro de rodillo en mm
        $ld = $carga_pulgadas; //ya esta en mm, no es necesario volver a multiplicar

        $partes_artesa = $conveyor['rod_partes_artesa']; //Part troughing

        $C_n = 0.96;
        $C_number_drive_pulleys = 1;
        $C_Ka = 1.25;
        $C_miu = 0.425;
        $C_fi1 = 0.85;
        $C_fi2 = 1;
        $C_mG = 20;
        if($metaUnits['hp_motor'][0]!='hp') { //hacer conversion si no esta en las unidades deseadas
            //1 Kw -> 1.341022317569 hp = x Kw * 1.341022317569
            $conveyor['trans_hp_motor'] = $conveyor['trans_hp_motor'] * 1.341022317569;
        }
        $Pmn = $conveyor['trans_hp_motor'];//in HP

        $arco_contacto = $this->ConfigTransporter->getOneById($conveyor['polea_arco_contacto']);
        $arco_contacto = $arco_contacto[0]['ConfigTransporter'];
        $arco_contacto = $arco_contacto['valor'];

        $alpha = $arco_contacto;
        $C_tB = 10;

        /**
         * Calculation
         * */
        $A = ($Qm * $C_fi2) / ($v * 3600 * $ro * $C_fi1);
        $mL = $Qm / (3.6 * $v);

        $mRo = $this->obtenValorDeMasa($B, $ld, $partes_artesa);
        $mRu = $mRo;

        $mR = ($mRo / $lo) + ($mRu / $lu);

        $C = $this->obtenCoeficienteDeLongitud($L);

        $Fm = $C * $C_f * $L * ($mR + (2 * $C_mG + $mL) * cos($gra)) * $C_g + $H * $mL * $C_g;
        $FH = $C_f * $L * ($mR + (2 * $C_mG + $mL) * cos($gra)) * $C_g;
        $FN = ($C - 1) * $FH;

        $Ptr = $Fm / 1000 * $v;
        $FU = $Ptr * 1000 / $v;
        $FU_fract = $FU / $C_number_drive_pulleys;

        $C_c1 = 1.4;
        $c2 = $C_c1 - 1;

        $Fu2 = $C_f * $L * ($mRu + ($C_mG + $mL) * cos($gra)) * $C_g - $H * $C_mG * $C_g;
        $Fo = $C_f * $L * ($mRo + ($C_mG + $mL) * cos($gra)) * $C_g + $H * ($C_mG + $mL) * $C_g;

        $t_min = (($mL + $C_mG) * $C_g * $lo) / (8 * $C_hrel);
        $C_FU2 = 0;

        $T2 = round($t_min, 2);
        $T3 = round($T2 + $Fu2, 2);
        $T4 = round($T3 - $C_FU2, 2);
        $T5 = round($T4 + $FN, 2);
        $T1 = round($T5 + $Fo, 2);
        $T2pf = round($T1 - $FU_fract, 2);

        $T2 = round($T2, 0);
        $T3 = round($T3, 0);
        $T4 = round($T4, 0);
        $T5 = round($T5, 0);
        $T1 = round($T1, 0);
        $T2pf = round($T2pf, 0);

        $tfd1 = $T2 >= $FU_fract * $c2 ? 0 : round($FU_fract * $c2 - $T2pf, 2);
        $tfd2 = $T4 >= $FU_fract * $c2 ? 0 : round($FU_fract * $c2 - $T4, 2);

        $sfp = $T5 >= $t_min ? 0 : round($t_min - $T5, 2);
        $sd = $T1 >= $t_min ? 0 : round($T1 - $t_min, 2);

        $tfd1 = round($tfd1, 0);
        $tfd2 = round($tfd2, 0);
        $sfp = round($sfp, 0);
        $sd = round($sd, 0);

        $tc_matrix = array($tfd1, $tfd2, $sfp, $sd);
        $tc = max($tc_matrix);
        $T2_tc = $T2 + $tc;
        $T3_tc = $T3 + $tc;
        $T4_tc = $T4 + $tc;
        $T5_tc = $T5 + $tc;
        $T1_tc = $T1 + $tc;
        $T2_pf_tc = $T2pf + $tc;

        $correction_tc = array($T2_tc, $T3_tc, $T4_tc, $T5_tc, $T1_tc, $T2_pf_tc);
        //$fuerza_maxima = round(max($correction_tc)*(1+0.10),0);
        $factor_seguridad = 0.10;
        $fuerza_maxima = round(max($correction_tc) * (1 + $factor_seguridad), 0);

        $B = $B / 25;
        $PIW = round($fuerza_maxima * 0.224808943 / $B, 2);
        $PIW = round($PIW, 0);

        return $PIW;
    }

    /**
     * Si el ancho capturado es mayor al ancho maximo calculado, se debe de obtener una recomendacion
     * @param model $conveyor
     * @return string
     */
    public function calcOtroNombreBanda($conveyor) {
        $nombre_banda = null;
        $conveyor = $conveyor['Conveyor'];
        $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

        $nomenclatura = Configure::read('App.nomenclaturas_extra');
        $conveyor_distancia_centros = $conveyor['trans_distancia_centros'];

        /*
         * OBTENEMOS LA MATRIZ QUE CORRESPONDA (CONTIFLEX, CONTITITAN, ST)
         */
        $banda_seleccionada = null;
        foreach ($nomenclatura AS $distancia_centros => $nombres_banda) {
            $banda_seleccionada = $nombres_banda;
            if ($conveyor_distancia_centros <= $distancia_centros) {
                break;
            }
        }

        if($metaUnits['densidad_material'][0]!='(lb/cu.ft)'){ //hacer conversion si no esta en las unidades deseadas
            //1kg -> 2.20462 lbs = x kg * 2.20462
            $conveyor['mat_densidad'] = $conveyor['mat_densidad'] * 2.20462; //siempre se manejara (lb/cu.ft) -> libra por pie cubico
        }
        if($metaUnits['ancho_banda'][0]!='in'){ //hacer conversion si no esta en las unidades deseadas
            //1mm -> 0.0393701in = x mm * 0.0393701
            $conveyor['banda_ancho'] = $conveyor['banda_ancho'] * 0.0393701; //se hace la comparacion con pulgadas
        }

        $indice_ancho_maximo = $this->indiceAnchoMaximo($conveyor['mat_densidad']);
        $indice_ancho_maximo = $indice_ancho_maximo - 1; //No hay ancho minimo
        //De la tabla que aplique obtenemos los valores de la columna segun mat_densidad y angulo de acanalamiento
        $filtro_inclinacion = array();
        if (!empty($banda_seleccionada)) {
            foreach ($banda_seleccionada AS $index_name_conveyor => $data) {
                $filtro_inclinacion[$index_name_conveyor] = $data['acanalamiento'][$conveyor['rod_angulo_acanalamiento']][$indice_ancho_maximo];
            }

            $this->aksort($filtro_inclinacion); //Para que se ordene manteniendo los indices
            //Una vez ordenados, buscamos el valor igual o siguiente inmediato acorde al tamaño maximo capturado        
            foreach ($filtro_inclinacion AS $index_name_conveyor => $ancho_maximo) {
                $indice_nombre_banda = $index_name_conveyor;
                if (is_numeric($ancho_maximo) && $ancho_maximo >= $conveyor['banda_ancho']) {
                    break;
                }
            }

            $matched_width = $filtro_inclinacion[$indice_nombre_banda];
            if ($matched_width >= $conveyor['banda_ancho']) {
                $nombre_banda = $banda_seleccionada[$indice_nombre_banda]['nombres_banda'];
            }
        }

        return $nombre_banda;
    }

    public function calcNombreBanda($piw, $conveyor) {
        $conveyor = $conveyor['Conveyor'];
        $metaUnits = $this->getMetaUnitsMx($conveyor); //[0]=> original capture, [1] => imperial, [2] => metric

        $nomenclatura = Configure::read('App.nomenclaturas');
        $conveyor_distancia_centros = $conveyor['trans_distancia_centros'];
        if($metaUnits['distancia_centros'][0]!='m') { //hacer conversion si no esta en las unidades deseadas
            //1ft -> 0.3048 mts = x ft * 0.3048
            $conveyor_distancia_centros = $conveyor_distancia_centros * 0.3048;
        }

        //Pruebas
        //$piw = 252;

        /*
         * OBTENEMOS LA MATRIZ QUE CORRESPONDA (CONTIFLEX, CONTITITAN, ST)
         */
        $banda_seleccionada = null;
        foreach ($nomenclatura AS $distancia_centros => $nombres_banda) {
            $banda_seleccionada = $nombres_banda;
            if ($conveyor_distancia_centros <= $distancia_centros) {
                break;
            }
        }

        /*
         * OBTENEMOS LA FILA DE PIW QUE CORESPONDA
         */
        foreach ($banda_seleccionada AS $piw_data => $banda) {
            $banda_seleccionada = $banda;
            if ($piw <= $piw_data) {
                break;
            }
        }
        return $banda_seleccionada;
    }

    public function calcEspesorRecomendadoBanda($total_cover) {
        $espesor_recomendado = array();
        $matriz = array(
            array(0.09375, '3/32 x 1/16', '2.5 x 1.6mm'),
            array(0.125, '1/8 x 1/16', '3.2 x 1.6mm'),
            array(0.15625, '5/32 x 1/16', '4 x 1.6mm'),
            array(0.1875, '3/16 x 1/16', '4.8 x 1.6mm'),
            array(0.234375, '15/64 x 3/32', '5.9 x 2mm'),
            array(0.25, '1/4 x 1/8', '6.4 x 3.2mm'),
            array(0.3125, '5/16 x 1/8', '8 x 3.2mm'),
            array(0.375, '3/8 x 1/8', '9.5 x 3.2mm'),
            array(0.390625, '25/64 x 5/32', '10 x 4mm'),
            array(0.46875, '15/32 x 5/32', '12 x 4mm'),
            array(0.5, '1/2 x 3/16', '12.7 x 4.8mm'),
            array(0.625, '5/8 x 1/4', '16 x 6.4mm'),
            array(0.75, '3/4 x 1/4', '19 x 6.4mm'),
            array(1, '1 x 1/4', '25 x 6.4mm')
        );

        for ($i = 0; $i < count($matriz); $i++) {
            /*             * SE CAMBIA POR LA MEDIA: MODIFICACION 090615* */
            $media = isset($matriz[$i + 1][0]) ? ($matriz[$i][0] + $matriz[$i + 1][0]) / 2 : $matriz[$i][0];
            if ($total_cover < $media) {
                $espesor_recomendado[] = $matriz[$i][1];
                $espesor_recomendado[] = $matriz[$i][2];
                break;
            }
        }
        $espesor_recomendado = empty($espesor_recomendado) ? array('1', '25') : $espesor_recomendado;

        return $espesor_recomendado;
    }

    public function sendQuoteRequest($conveyor, $estimated_lifetime) {
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->AtencionPersona = ClassRegistry::init('AtencionPersona');

        $conveyor = $conveyor['Conveyor'];
        $cliente_conveyor = $this->Empresa->findById($conveyor['id_company']);
        $cliente_conveyor = $cliente_conveyor['Empresa'];

        $distribuidor_conveyor = $this->Empresa->findById($cliente_conveyor['parent']);
        $distribuidor_conveyor = $distribuidor_conveyor['Empresa'];

        $credentials = $this->_app_credentials;

        $companies_master = $this->Empresa->getClientCompanyIdsByTypeAndRegion('master');
        $companies_admin = $this->Empresa->getClientCompanyIdsByTypeAndRegion('admin', $cliente_conveyor['region']);

        $companies_master = !empty($companies_master) ? explode(',', $companies_master) : $companies_master;
        $usuarios_master = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $companies_master, 'deleted' => 0)));

        $companies_admin = !empty($companies_admin) ? explode(',', $companies_admin) : $companies_admin;
        $usuarios_admin = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => $companies_admin, 'deleted' => 0)));

        $usuarios_distributor = $this->UsuariosEmpresa->find('all', array('conditions' => array('id_empresa' => array($distribuidor_conveyor['id']), 'deleted' => 0)));



        $language = in_array($cliente_conveyor['region'], array('MX1', 'MX2', 'CENAM')) ? 'es' : 'en';

        $to = '';
        $bcc = '';
        if (!empty($usuarios_master)) {
            foreach ($usuarios_master AS $user) {
                $user = $user['UsuariosEmpresa'];
                if ($to == '') {
                    $to = $user['email'];
                } else {
                    $bcc .= $user['email'] . ',';
                }
            }
        }

        if (!empty($usuarios_admin)) {
            foreach ($usuarios_admin AS $user) {
                $user = $user['UsuariosEmpresa'];
                $bcc .= $user['email'] . ',';
            }
        }

        $names_user_distribuidores = '';
        //Si quien pidio la cotizacion no es distribuidor, enviar copia oculta al dist del cliente
        if (!empty($usuarios_distributor) && $credentials['role'] != UsuariosEmpresa::IS_DIST) {
            foreach ($usuarios_distributor AS $user) {
                $user = $user['UsuariosEmpresa'];
                $bcc .= $user['email'] . ',';
                $names_user_distribuidores .= $user['name'] . ', ';
            }
        }



        //quitamos la coma final
        $bcc = $bcc != '' ? 'Bcc: ' . substr($bcc, 0, -1) . "\r\n" : $bcc;
        $names_user_distribuidores = $names_user_distribuidores != '' ? substr($names_user_distribuidores, 0, -2) : $names_user_distribuidores;

        //Debug
        //$to = 'elalbertgd@gmail.com';
        //$bcc = '';

        $quote_success = false;
        if ($to != '') {
            $subject = $language == 'es' ? 'Solicitud de cotización' : 'Quote request';
            $html_message = file_get_contents('files/mail_templates/cotizacion_' . $language . '.html');
            $html_message = str_replace('{usuario}', $credentials['name'], $html_message);
            $html_message = str_replace('{empresa}', $credentials['name_company'], $html_message);
            $html_message = str_replace('{recommended_conveyor}', $estimated_lifetime['recommended_conveyor_in'] . '<br/>' . $estimated_lifetime['recommended_conveyor_mm'], $html_message);
            $html_message = str_replace('{conveyor_number}', $conveyor['numero'], $html_message);
            $html_message = str_replace('{dealer_conveyor}', $distribuidor_conveyor['name'], $html_message);


            $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= $bcc;

            $quote_success = mail($to, $subject, $html_message, $headers);
        }

        if ($quote_success) {
            /** Mandar la confirmacion de envio * */
            //Obtenemos los usuarios que atenderan a quien envio la cotizacio
            $email_request_user = $credentials['email'];
            $personasQueAtienden = '';

            if ($credentials['role'] == UsuariosEmpresa::IS_DIST) {
                $personaAtiende = $this->AtencionPersona->findById($credentials['id_atiende_d']);
                $personasQueAtienden = $personaAtiende['AtencionPersona']['name'];

                /*                 * *mandar el email a la persona atiende al distribuidor* */
                $to = $personaAtiende['AtencionPersona']['email'];
                //Debug
                //$to = 'lauramarsant@gmail.com';

                $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
                $headers .= 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                mail($to, $subject, $html_message, $headers);
            } else {
                $personasQueAtienden = $names_user_distribuidores;
            }

            $subject = __('Confirmacion automatica', true);
            $html_message = file_get_contents('files/mail_templates/confirmacion_quote_' . $language . '.html');
            $html_message = str_replace('{name_user}', $credentials['name'], $html_message);
            $html_message = str_replace('{recommended_conveyor}', $estimated_lifetime['recommended_conveyor_in'] . '<br>' . $estimated_lifetime['recommended_conveyor_mm'], $html_message);
            $html_message = str_replace('{conveyor_number}', $conveyor['numero'], $html_message);
            $html_message = str_replace('{personas_atencion}', $personasQueAtienden, $html_message);
            $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            mail($email_request_user, $subject, $html_message, $headers);
            /*             * Fin confirmacion envio * */
        }

        return $quote_success;
    }

    public function sePuedeCalcularVidaEstimada($conveyor) {

        $calculo = false;
        $conveyor = $conveyor['Conveyor'];
        $conveyor['banda_operacion'] = $conveyor['banda_operacion'] == '' || !is_numeric($conveyor['banda_operacion']) ? 0 : $conveyor['banda_operacion'];
        $conveyor['banda_velocidad'] = $conveyor['banda_velocidad'] == '' || is_null($conveyor['banda_velocidad']) ? 0 : $conveyor['banda_velocidad'];
        $conveyor['trans_capacidad'] = $conveyor['trans_capacidad'] == '' || is_null($conveyor['trans_capacidad']) ? 0 : $conveyor['trans_capacidad'];
        $conveyor['trans_distancia_centros'] = $conveyor['trans_distancia_centros'] == '' || is_null($conveyor['trans_distancia_centros']) ? 0 : $conveyor['trans_distancia_centros'];
        //Se cambia cast de int a double
        if ((double) $conveyor['trans_capacidad'] > 0 && (double) $conveyor['trans_distancia_centros'] > 0 && (double) $conveyor['banda_velocidad'] > 0 && (double) $conveyor['banda_operacion'] > 0 &&
                !is_null($conveyor['mat_grado_mat_transportado']) && !is_null($conveyor['mat_condicion_alimentacion']) && !is_null($conveyor['id_espesor_cubierta_sup'])) {
            //paso filtros
            $calculo = true;
        }
        return $calculo;
    }

    public function sePuedeCalcularBandaRecomendada($conveyor) {
        $this->ConfigTransporter = ClassRegistry::init('ConfigTransporter');
        $calculo = false;
        $conveyor = $conveyor['Conveyor'];

        if (!is_null($conveyor['mat_condicion_carga']) && !is_null($conveyor['mat_frecuencia_carga']) && !is_null($conveyor['mat_tamanio_granular']) && !is_null($conveyor['mat_tipo_densidad']) && !is_null($conveyor['mat_agresividad']) &&
                !is_null($conveyor['mat_grado_mat_transportado']) && !is_null($conveyor['mat_condicion_alimentacion']) && !is_null($conveyor['id_espesor_cubierta_sup']) &&
                !is_null($conveyor['mat_descripcion']) && $conveyor['mat_descripcion'] > 0 &&
                !is_null($conveyor['mat_densidad']) && !is_null($conveyor['trans_capacidad']) && !is_null($conveyor['mat_tam_terron']) &&
                !is_null($conveyor['trans_distancia_centros']) && !is_null($conveyor['trans_elevacion']) && !is_null($conveyor['trans_angulo_inclinacion']) &&
                !is_null($conveyor['banda_ancho']) && !is_null($conveyor['banda_velocidad']) && !is_null($conveyor['rod_ang_carga']) && !is_null($conveyor['rod_diam_carga']) &&
                !is_null($conveyor['rod_espacio_ldc']) && $conveyor['rod_espacio_ldc'] > 0  && !is_null($conveyor['rod_partes_artesa']) && !is_null($conveyor['rod_angulo_acanalamiento']) && !is_null($conveyor['trans_hp_motor']) && !is_null($conveyor['polea_arco_contacto'])) {

            $descripcion_material = $this->ConfigTransporter->getOneById($conveyor['mat_descripcion']);
            $descripcion_material = $descripcion_material[0]['ConfigTransporter']['valor_en'];

            $mat_temperatura = $conveyor['mat_temperatura'];
            $mat_aceite = $conveyor['mat_aceite'];
            $mat_tamanio = $conveyor['mat_tamanio'];

            $mat_temperatura = trim($mat_temperatura) != '' && is_numeric($mat_temperatura) ? $mat_temperatura : null;
            $mat_temperatura = !is_null($mat_temperatura) && $mat_temperatura >= 86 ? $mat_temperatura : null;

            if (!is_null($mat_aceite)) {
                $mat_aceite = $this->ConfigTransporter->getOneById($mat_aceite);
                $mat_aceite = $mat_aceite[0]['ConfigTransporter']['valor_en'];
            }

            //paso filtros
            $calculo = true;
            //Si el material es carbon, el campo de ubicacion transportador debe estar presente
            if (preg_match("/\bcoal\b/i", $descripcion_material) && is_null($conveyor['trans_ubicacion'])) {
                $calculo = false;
                //Si NO tiene temperatura y si tampoco tiene aceite, el compuesto se calcula en base al tamaño
                //por es necesario saber si tiene tamaño material
            } else if (is_null($mat_tamanio) && is_null($mat_temperatura) && (is_null($mat_aceite) || preg_match("/\bno oil\b/i", $mat_aceite))) {
                $calculo = false;
            }
        }
        return $calculo;
    }

    private function obtenValorDeMasa($ancho_banda, $diametro_rodillo, $partes_artesa) {
        $matriz_masas = array(
            300 => array('88.9' => array()),
            400 => array('88.9' => array(), '108' => array(), '133' => array()),
            500 => array('88.9' => array(), '108' => array(), '133' => array()),
            650 => array('88.9' => array(), '108' => array(), '133' => array()),
            800 => array('88.9' => array(), '108' => array(), '133' => array()),
            1000 => array('108' => array(), '133' => array(), '159' => array()),
            1200 => array('108' => array(), '133' => array(), '159' => array()),
            1400 => array('133' => array(), '159' => array()),
            1600 => array('133' => array(), '159' => array()),
            1800 => array('133' => array(), '159' => array()),
            2000 => array('133' => array(), '159' => array(), '193.7' => array()),
            2200 => array('159' => array(), '193.7' => array()),
            2400 => array('159' => array(), '193.7' => array()),
            2600 => array('159' => array(), '193.7' => array()),
            2800 => array('159' => array(), '193.7' => array()),
            3000 => array('159' => array(), '193.7' => array()),
            3200 => array('159' => array(), '193.7' => array())
        );

        $matriz_masas[300]['88.9'][1] = 3.2;
        $matriz_masas[300]['88.9'][2] = 4.1;
        $matriz_masas[300]['88.9'][3] = null;
        $matriz_masas[300]['88.9'][4] = null;
        $matriz_masas[300]['88.9'][5] = null;

        $matriz_masas[400]['88.9'][1] = 3.9;
        $matriz_masas[400]['88.9'][2] = 4.7;
        $matriz_masas[400]['88.9'][3] = 5.4;
        $matriz_masas[400]['88.9'][4] = null;
        $matriz_masas[400]['88.9'][5] = null;
        $matriz_masas[400]['108'][1] = 5.6;
        $matriz_masas[400]['108'][2] = 6.6;
        $matriz_masas[400]['108'][3] = 7.3;
        $matriz_masas[400]['108'][4] = null;
        $matriz_masas[400]['108'][5] = null;
        $matriz_masas[400]['133'][1] = 7.6;
        $matriz_masas[400]['133'][2] = 8.7;
        $matriz_masas[400]['133'][3] = 9.6;
        $matriz_masas[400]['133'][4] = null;
        $matriz_masas[400]['133'][5] = null;

        $matriz_masas[500]['88.9'][1] = 4.5;
        $matriz_masas[500]['88.9'][2] = 5.5;
        $matriz_masas[500]['88.9'][3] = 6.1;
        $matriz_masas[500]['88.9'][4] = null;
        $matriz_masas[500]['88.9'][5] = null;
        $matriz_masas[500]['108'][1] = 6.6;
        $matriz_masas[500]['108'][2] = 7.8;
        $matriz_masas[500]['108'][3] = 8.4;
        $matriz_masas[500]['108'][4] = null;
        $matriz_masas[500]['108'][5] = null;
        $matriz_masas[500]['133'][1] = 8.9;
        $matriz_masas[500]['133'][2] = 10.4;
        $matriz_masas[500]['133'][3] = 11.1;
        $matriz_masas[500]['133'][4] = null;
        $matriz_masas[500]['133'][5] = null;

        $matriz_masas[650]['88.9'][1] = 5.5;
        $matriz_masas[650]['88.9'][2] = 6.3;
        $matriz_masas[650]['88.9'][3] = 7;
        $matriz_masas[650]['88.9'][4] = null;
        $matriz_masas[650]['88.9'][5] = null;
        $matriz_masas[650]['108'][1] = 8;
        $matriz_masas[650]['108'][2] = 9;
        $matriz_masas[650]['108'][3] = 9.8;
        $matriz_masas[650]['108'][4] = null;
        $matriz_masas[650]['108'][5] = null;
        $matriz_masas[650]['133'][1] = 10.8;
        $matriz_masas[650]['133'][2] = 12.1;
        $matriz_masas[650]['133'][3] = 13.1;
        $matriz_masas[650]['133'][4] = null;
        $matriz_masas[650]['133'][5] = null;

        $matriz_masas[800]['88.9'][1] = 6.7;
        $matriz_masas[800]['88.9'][2] = 7.4;
        $matriz_masas[800]['88.9'][3] = 8.3;
        $matriz_masas[800]['88.9'][4] = null;
        $matriz_masas[800]['88.9'][5] = 9;
        $matriz_masas[800]['108'][1] = 9.8;
        $matriz_masas[800]['108'][2] = 10.6;
        $matriz_masas[800]['108'][3] = 11.6;
        $matriz_masas[800]['108'][4] = null;
        $matriz_masas[800]['108'][5] = 12.4;
        $matriz_masas[800]['133'][1] = 13.3;
        $matriz_masas[800]['133'][2] = 14.2;
        $matriz_masas[800]['133'][3] = 15.6;
        $matriz_masas[800]['133'][4] = null;
        $matriz_masas[800]['133'][5] = 16.3;

        $matriz_masas[1000]['108'][1] = 11.7;
        $matriz_masas[1000]['108'][2] = 13.2;
        $matriz_masas[1000]['108'][3] = 13.6;
        $matriz_masas[1000]['108'][4] = null;
        $matriz_masas[1000]['108'][5] = 14.2;
        $matriz_masas[1000]['133'][1] = 15.9;
        $matriz_masas[1000]['133'][2] = 17.8;
        $matriz_masas[1000]['133'][3] = 18.2;
        $matriz_masas[1000]['133'][4] = null;
        $matriz_masas[1000]['133'][5] = 18.9;
        $matriz_masas[1000]['159'][1] = 21.9;
        $matriz_masas[1000]['159'][2] = 24.7;
        $matriz_masas[1000]['159'][3] = 26.3;
        $matriz_masas[1000]['159'][4] = null;
        $matriz_masas[1000]['159'][5] = 28;

        $matriz_masas[1200]['108'][1] = 14.2;
        $matriz_masas[1200]['108'][2] = 15;
        $matriz_masas[1200]['108'][3] = 16.3;
        $matriz_masas[1200]['108'][4] = null;
        $matriz_masas[1200]['108'][5] = 16.3;
        $matriz_masas[1200]['133'][1] = 19.3;
        $matriz_masas[1200]['133'][2] = 20.5;
        $matriz_masas[1200]['133'][3] = 22.3;
        $matriz_masas[1200]['133'][4] = null;
        $matriz_masas[1200]['133'][5] = 21.7;
        $matriz_masas[1200]['159'][1] = 26.1;
        $matriz_masas[1200]['159'][2] = 28;
        $matriz_masas[1200]['159'][3] = 29.8;
        $matriz_masas[1200]['159'][4] = null;
        $matriz_masas[1200]['159'][5] = 31.9;

        $matriz_masas[1400]['133'][1] = 21.8;
        $matriz_masas[1400]['133'][2] = 23.3;
        $matriz_masas[1400]['133'][3] = 25;
        $matriz_masas[1400]['133'][4] = null;
        $matriz_masas[1400]['133'][5] = 24.3;
        $matriz_masas[1400]['159'][1] = 29.3;
        $matriz_masas[1400]['159'][2] = 31.6;
        $matriz_masas[1400]['159'][3] = 35.5;
        $matriz_masas[1400]['159'][4] = null;
        $matriz_masas[1400]['159'][5] = 35;

        $matriz_masas[1600]['133'][1] = 25.1;
        $matriz_masas[1600]['133'][2] = 26.5;
        $matriz_masas[1600]['133'][3] = 28;
        $matriz_masas[1600]['133'][4] = null;
        $matriz_masas[1600]['133'][5] = 28.5;
        $matriz_masas[1600]['159'][1] = 33.4;
        $matriz_masas[1600]['159'][2] = 35;
        $matriz_masas[1600]['159'][3] = 38.7;
        $matriz_masas[1600]['159'][4] = null;
        $matriz_masas[1600]['159'][5] = 39.3;

        $matriz_masas[1800]['133'][1] = 27.6;
        $matriz_masas[1800]['133'][2] = 29.1;
        $matriz_masas[1800]['133'][3] = 30.7;
        $matriz_masas[1800]['133'][4] = null;
        $matriz_masas[1800]['133'][5] = 31.5;
        $matriz_masas[1800]['159'][1] = 37.8;
        $matriz_masas[1800]['159'][2] = 39.5;
        $matriz_masas[1800]['159'][3] = 42.4;
        $matriz_masas[1800]['159'][4] = null;
        $matriz_masas[1800]['159'][5] = 42.5;

        $matriz_masas[2000]['133'][1] = 30.2;
        $matriz_masas[2000]['133'][2] = 31.8;
        $matriz_masas[2000]['133'][3] = 33.3;
        $matriz_masas[2000]['133'][4] = null;
        $matriz_masas[2000]['133'][5] = 33.5;
        $matriz_masas[2000]['159'][1] = 40.2;
        $matriz_masas[2000]['159'][2] = 43.3;
        $matriz_masas[2000]['159'][3] = 47;
        $matriz_masas[2000]['159'][4] = null;
        $matriz_masas[2000]['159'][5] = 46.5;
        $matriz_masas[2000]['193.7'][1] = 69.1;
        $matriz_masas[2000]['193.7'][2] = 76.4;
        $matriz_masas[2000]['193.7'][3] = 80.1;
        $matriz_masas[2000]['193.7'][4] = null;
        $matriz_masas[2000]['193.7'][5] = 89.5;

        $matriz_masas[2200]['159'][1] = 46.5;
        $matriz_masas[2200]['159'][2] = 49;
        $matriz_masas[2200]['159'][3] = 50.1;
        $matriz_masas[2200]['159'][4] = null;
        $matriz_masas[2200]['159'][5] = 49.5;
        $matriz_masas[2200]['193.7'][1] = 77.8;
        $matriz_masas[2200]['193.7'][2] = 82.6;
        $matriz_masas[2200]['193.7'][3] = 93.2;
        $matriz_masas[2200]['193.7'][4] = null;
        $matriz_masas[2200]['193.7'][5] = 95.5;

        $matriz_masas[2400]['159'][1] = 50.7;
        $matriz_masas[2400]['159'][2] = 51.5;
        $matriz_masas[2400]['159'][3] = 53.5;
        $matriz_masas[2400]['159'][4] = null;
        $matriz_masas[2400]['159'][5] = 53;
        $matriz_masas[2400]['193.7'][1] = 86.6;
        $matriz_masas[2400]['193.7'][2] = 91.4;
        $matriz_masas[2400]['193.7'][3] = 93.2;
        $matriz_masas[2400]['193.7'][4] = null;
        $matriz_masas[2400]['193.7'][5] = 100.5;

        $matriz_masas[2600]['159'][1] = null;
        $matriz_masas[2600]['159'][2] = 55.1;
        $matriz_masas[2600]['159'][3] = 57.5;
        $matriz_masas[2600]['159'][4] = null;
        $matriz_masas[2600]['159'][5] = 56.5;
        $matriz_masas[2600]['193.7'][1] = null;
        $matriz_masas[2600]['193.7'][2] = 97.2;
        $matriz_masas[2600]['193.7'][3] = 97.6;
        $matriz_masas[2600]['193.7'][4] = null;
        $matriz_masas[2600]['193.7'][5] = 107;

        $matriz_masas[2800]['159'][1] = null;
        $matriz_masas[2800]['159'][2] = 58.5;
        $matriz_masas[2800]['159'][3] = 59.1;
        $matriz_masas[2800]['159'][4] = null;
        $matriz_masas[2800]['159'][5] = 60;
        $matriz_masas[2800]['193.7'][1] = null;
        $matriz_masas[2800]['193.7'][2] = 103;
        $matriz_masas[2800]['193.7'][3] = 106.4;
        $matriz_masas[2800]['193.7'][4] = null;
        $matriz_masas[2800]['193.7'][5] = 113;

        $matriz_masas[3000]['159'][1] = null;
        $matriz_masas[3000]['159'][2] = 63;
        $matriz_masas[3000]['159'][3] = 65.5;
        $matriz_masas[3000]['159'][4] = null;
        $matriz_masas[3000]['159'][5] = 65;
        $matriz_masas[3000]['193.7'][1] = null;
        $matriz_masas[3000]['193.7'][2] = 109;
        $matriz_masas[3000]['193.7'][3] = 112.5;
        $matriz_masas[3000]['193.7'][4] = null;
        $matriz_masas[3000]['193.7'][5] = 121.5;

        $matriz_masas[3200]['159'][1] = null;
        $matriz_masas[3200]['159'][2] = 70;
        $matriz_masas[3200]['159'][3] = 71.5;
        $matriz_masas[3200]['159'][4] = null;
        $matriz_masas[3200]['159'][5] = 68;
        $matriz_masas[3200]['193.7'][1] = null;
        $matriz_masas[3200]['193.7'][2] = 120;
        $matriz_masas[3200]['193.7'][3] = 123;
        $matriz_masas[3200]['193.7'][4] = null;
        $matriz_masas[3200]['193.7'][5] = 126.5;

        //obtenemos el ancho banda que corresponda
        $belt_width_calc = null;
        $prev_key = null;
        foreach ($matriz_masas AS $belt_width => $idlers) {
            if ($ancho_banda < $belt_width) {
                $belt_width_calc = $prev_key != null ? $prev_key : $belt_width;
                break;
            }
            $prev_key = $belt_width;
        }
        $belt_width_calc = is_null($belt_width_calc) ? $belt_width : $belt_width_calc;

        //Obtenemos el diametro rodillo que corresponda
        $diam_idler_calc = null;
        //$prev_key = null;
        foreach ($matriz_masas[$belt_width_calc] AS $diam_idler => $valores_partes_artesa) {
            if ($diametro_rodillo < (int) $diam_idler) {
                $diam_idler_calc = $diam_idler;
                //$diam_idler_calc = $prev_key!=null ? $prev_key : $diam_idler;
                break;
            }
            //$prev_key = $diam_idler;
        }
        $diam_idler_calc = is_null($diam_idler_calc) ? $diam_idler : $diam_idler_calc;

        $valores_partes_artesa = $matriz_masas[$belt_width_calc][$diam_idler_calc];

        return $valores_partes_artesa[$partes_artesa];
    }

    private function obtenCoeficienteDeLongitud($L) {
        $matriz_coeficientes = array(3 => 9, 4 => 7.6, 6 => 5.9, 10 => 4.5, 16 => 3.6, 20 => 3.2,
            25 => 2.9, 32 => 2.6, 40 => 2.4, 50 => 2.2, 63 => 2, 80 => 1.92, 90 => 1.86, 100 => 1.78,
            120 => 1.7, 140 => 1.63, 160 => 1.56, 180 => 1.5, 200 => 1.45, 250 => 1.38, 300 => 1.31,
            350 => 1.27, 400 => 1.25, 450 => 1.23, 500 => 1.2, 550 => 1.18, 600 => 1.17, 700 => 1.14,
            800 => 1.12, 900 => 1.1, 1000 => 1.09, 1500 => 1.06, 2000 => 1.05, 2500 => 1.04, 5000 => 1.03);

        $coeficiente = null;
        $ant_L_in_m = 0;
        //$prev_key = null;
        foreach ($matriz_coeficientes AS $L_in_m => $c) {
            if ($L < (int) $L_in_m) {
                $coeficiente = $ant_L_in_m == $L ? $matriz_coeficientes[$ant_L_in_m] : $c;
                break;
            }
            $ant_L_in_m = $L_in_m;
        }
        $coeficiente = is_null($coeficiente) ? $c : $coeficiente;
        return $coeficiente;
    }

    /**
     * Elimina las imagenes cacheadas (las utilizadas para visualizar con menor tamaÃ±o los albumes)
     * @return bool
     */
    public function clear_img_cache() {
        $folder_path = _ABSOLUTE_PATH . 'files/small_renders';
        $folder_render = new Folder($folder_path); //leemos el archivo
        return $folder_render->delete(); //Eliminamos el folder
    }

    /**
     * Custom array sort keeping index asociation and sorted indexes
     * @param type $array
     * @param type $valrev
     * @param type $keyrev
     */
    public function aksort(&$array, $valrev = false, $keyrev = false) {
        if ($valrev) {
            arsort($array);
        } else {
            asort($array);
        }
        $vals = array_count_values($array);
        $i = 0;
        foreach ($vals AS $val => $num) {
            $first = array_splice($array, 0, $i);
            $tmp = array_splice($array, 0, $num);
            if ($keyrev) {
                krsort($tmp);
            } else {
                ksort($tmp);
            }
            $array = array_merge($first, $tmp, $array);
            unset($tmp);
            $i = $num;
        }
    }

    public function multidimensionalSort($array, $on, $order=SORT_ASC){
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    natcasesort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    public function fixDataForUpdate($data) {
        array_walk($data, create_function('&$str', '$str = "\'$str\'";'));
        return $data;
    }

    public function getCompoundMatrixValues() {
        $matrix = array(
            1 => array(0.0041, 0.0013, 0.0002, '6740A'),
            2 => array(0.001987, 0.001374, 0.000051, 'Shield'),
            3 => array(0.0043, 0.0008, 0.0001, 'FRAR-2G (Arma II, Elite)'),
            4 => array(0.0043, 0.0008, 0.0001, 'FR-2G (Arma-Sbr)'),
            5 => array(-0.001274, 0.002484, 0.000031, 'Defender (Plus)'),
            6 => array(0.002248, 0.001479, 0.000068, 'Gorilla'),
            7 => array(0.0041, 0.0013, 0.0002, 'MORS'),
            8 => array(0.0043, 0.0008, 0.0001, 'MSHA'),
            9 => array(0.0041, 0.0013, 0.0002, 'Premarc'),
            10 => array(0.002248, 0.001479, 0.000068, 'Solarshield XL750'),
            11 => array(0.006, -0.0002, 0.0002, 'STACKER'),
            12 => array(0.006, -0.0002, 0.0002, 'Survivor')
        );

        return $matrix;
    }
    
    public function getUltrasonicWidths(){
        $matrix = array(
            '16' => '1,2.5,5,7.5,10,12.5,15',
            '18' => '1,3,6,9,12,15,17',
            '20' => '1,3.5,7,10.5,14,17.5,19',
            '24' => '1,4,8,12,16,20,23',
            '30' => '1,5,10,15,20,25,29',
            '32' => '1,6,11,16,21,26,31',
            '36' => '1,6,12,18,24,30,35',
            '42' => '1,7,14,21,28,35,41',
            '48' => '1,8,16,24,32,40,47',
            '54' => '1,9,18,27,36,45,53',
            '60' => '1,10,20,30,40,50,59',
            '66' => '1,11,22,33,44,55,65',
            '72' => '1,12,24,36,48,60,71',
            '78' => '1,13,26,39,52,65,77',
            '84' => '1,14,28,42,56,70,83',
            '96' => '1,16,32,48,64,80,95',
            __('Otro',true) => ''
        );
        
        return $matrix;
    }

    public function getUltrasonicWidthsMetric(){
        $matrix = array(
            '406' => '25,64,127,191,254,318,381',
            '457' => '25,76,152,229,305,381,432',
            '508' => '25,89,178,267,356,445,483',
            '610' => '25,102,203,305,406,508,584',
            '762' => '25,127,254,381,508,635,737',
            '813' => '25,152,279,406,533,660,787',
            '914' => '25,152,305,457,610,762,889',
            '1067' => '25,178,356,533,711,889,1041',
            '1219' => '25,203,406,610,813,1016,1194',
            '1372' => '25,229,457,686,914,1143,1346',
            '1524' => '25,254,508,762,1016,1270,1499',
            '1676' => '25,279,559,838,1118,1397,1651',
            '1829' => '25,305,610,914,1219,1524,1803',
            '1981' => '25,330,660,991,1321,1651,1956',
            '2134' => '25,356,711,1067,1422,1778,2108',
            '2438' => '25,406,813,1219,1626,2032,2413',
            __('Otro',true) => ''
        );

        return $matrix;
    }

    public function getUltrasonicPlotData($ultrasonic_readings, $ultrasonic, $conveyor) {
        $installation_date_conveyor = $ultrasonic['install_update_ultra']=='0000-00-00 00:00:00' ? $this->timestampToUsDate($conveyor['banda_fecha_instalacion']) : $this->timestampToUsDate($ultrasonic['install_update_ultra']);
        $plot_data = array(array(' ', $installation_date_conveyor));

        //Set the Labels for each data series we want to plot
        foreach ($ultrasonic_readings AS $ultrasonic_row) {
            $ultrasonic_row = $ultrasonic_row['UltrasonicReading'];
            //$reading_date = $this->timestampToCorrectFormat($ultrasonic_row['reading_date']);
            $reading_date = $this->timestampToUsDate($ultrasonic_row['reading_date']);            
            array_push($plot_data[0], $reading_date);
        }


        $top_cover = $ultrasonic['original_top_cover'] != '' && (float) $ultrasonic['original_top_cover'] > 0 ? (double) $ultrasonic['original_top_cover'] : 0;

        //X-Axis Labels
        /*$ultrasonic_widths = $ultrasonic["units"]=='imperial' ? $this->getUltrasonicWidths():$this->getUltrasonicWidthsMetric();
        $ultrasonic_widths = isset($ultrasonic_widths[$ultrasonic['ultrasonic_width']]) ? $ultrasonic_widths[$ultrasonic['ultrasonic_width']]:$ultrasonic['other_width'];
        $ultrasonic_widths = explode(',',$ultrasonic_widths);*/
        $ultrasonic_widths = $ultrasonic['other_width'];
        $ultrasonic_widths = explode(',',$ultrasonic_widths);

        $x_axis_labels = $ultrasonic_widths;
        $unit_indicator = $ultrasonic["units"]=='imperial' ? '"':'';
        foreach ($x_axis_labels AS $x_axis) {

            $plot_data[] = array($x_axis.$unit_indicator, $top_cover);
        }

        //Set the Data values for each data series we want to plot
        foreach ($ultrasonic_readings AS $ultrasonic_row) {
            $ultrasonic_row = $ultrasonic_row['UltrasonicReading'];
            list($avg, $avg_adjusted) = explode('||', $ultrasonic_row['avgs']);
            $avg_adjusted = explode(',', $avg_adjusted);

            //Los valores guardados estan en inches
            if($ultrasonic["units"]=='metric'){
                $avg_adjusted = array_map('CoreComponent::convertToMM', $avg_adjusted);
            }

            foreach ($avg_adjusted AS $index_avg => $avg_value) {
                array_push($plot_data[$index_avg + 1], round(((float) $avg_value), 3));
            }
        }

        return $plot_data;
    }
    
    public function getCompoundNameById($id){
        $compounds = array(
            1 => '6740A',
            2 => 'Shield',
            3 => 'FRAR-2G (Arma II, Elite)',
            4 => 'FR-2G (Arma-Sbr)',
            5 => 'Defender (Plus)',
            6 => 'Gorilla',
            7 => 'MORS',
            8 => 'MSHA',
            9 => 'Premarc',
            10 => 'Solarshield XL750',
            11 => 'STACKER)',
            12 => 'Survivor'            
        );
        return $compounds[$id];
    }
    
    public function getSpecificationsUltrasonic($ultrasonic,$conveyor){
        $table_data = array();
        //$conveyor = $conveyor['Conveyor'];
        
        
        $table_data[] = array(
            __('Specifications', true),''            
        );
        
        $date_installed = $ultrasonic['install_update_ultra']=='0000-00-00 00:00:00' ? $this->timestampToUsDate($conveyor['banda_fecha_instalacion']) : $this->timestampToUsDate($ultrasonic['install_update_ultra']);
        $banda_marca = $ultrasonic['conveyor_brand_ultra']=='' ? $conveyor['banda_marca'] : $ultrasonic['conveyor_brand_ultra'];
        
        $table_data[] = array(__('Belt Specification', true),$banda_marca);
        $table_data[] = array(__('Date Installed', true),$date_installed);
        $table_data[] = array(__('Compound name',true), $this->getCompoundNameById($ultrasonic['compound_name']));
        $table_data[] = array(__('Original Top Cover Thickness',true),$ultrasonic['original_top_cover']);
        $table_data[] = array(__('Ultrasonic width',true),$ultrasonic['ultrasonic_width']);
        $table_data[] = array(__('Price of belt installed',true),$ultrasonic['conveyor_price']);
        $table_data[] = array(__('Durometer New Belt',true),$ultrasonic['durometer_new_belt']);
        
        return $table_data;
    }

    public function getUltrasonicDatesAndMeasured($ultrasonic_data, $ultrasonic, $conveyor) {
        $table_data = array();
        //$conveyor = $conveyor['Conveyor'];
        //$date_installed = $this->timestampToCorrectFormat($conveyor['banda_fecha_instalacion']);
        $date_installed = $ultrasonic['install_update_ultra']=='0000-00-00 00:00:00' ? $this->timestampToUsDate($conveyor['banda_fecha_instalacion']) : $this->timestampToUsDate($ultrasonic['install_update_ultra']);
        
        
        $table_data[] = array(__('Temperature Adjusted Readings', true),'','');            
        $table_data[] = array('','',__('Location (inches) across belt width', true),'','','','','','',__('Shore-A', true));
        
        //X-Axis Labels
        /*$ultrasonic_widths = $ultrasonic["units"]=='imperial' ? $this->getUltrasonicWidths():$this->getUltrasonicWidthsMetric();
        $ultrasonic_widths = isset($ultrasonic_widths[$ultrasonic['ultrasonic_width']]) ? $ultrasonic_widths[$ultrasonic['ultrasonic_width']]:$ultrasonic['other_width'];
        $ultrasonic_widths = explode(',',$ultrasonic_widths);*/
        $ultrasonic_widths = $ultrasonic['other_width'];
        $ultrasonic_widths = explode(',',$ultrasonic_widths);
        
        $headers_temps = array(__('Dates installed & Measured', true),'');
        foreach ($ultrasonic_widths AS $x_axis) {
            $headers_temps[] = $x_axis;
        }
        $headers_temps[] = __('Durometer', true);
        
        
        $table_data[] = $headers_temps;        
        $table_data[] = array(
            __('Install Date', true),
            $date_installed,
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            (double) $ultrasonic['original_top_cover'],
            $ultrasonic['durometer_new_belt'],
        );        
        if (!empty($ultrasonic_data)) {
            foreach ($ultrasonic_data AS $index => $ultrasonic_reading) {
                $ultrasonic_reading = $ultrasonic_reading['UltrasonicReading'];
                $reading_title = __('%s date read', array($index + 1));
                $date_reading = $this->timestampToUsDate($ultrasonic_reading['reading_date']);
                $durometer = $ultrasonic_reading['durometer'];
                $readings = $ultrasonic_reading['avgs'];
                $readings = explode('||', $readings); // get rows                            
                $readings = explode(',', $readings[1]);

                $table_data[] = array(
                    $reading_title,
                    $date_reading,
                    round($readings[0], 3),
                    round($readings[1], 3),
                    round($readings[2], 3),
                    round($readings[3], 3),
                    round($readings[4], 3),
                    round($readings[5], 3),
                    round($readings[6], 3),
                    $durometer
                );
            }
        }
        return $table_data;
    }

    public function getUltrasonicStatisticProjectionsData($ultrasonic_data, $ultrasonic, $conveyor) {
        $table_data = array();
        $table_data[] = array(__('Wear Rate Statistics & Projections', true),'','');            
        $table_data[] = array(
            __('Tons Conveyed', true),
            __('#months', true),
            __('Wear rate', true),
            __('Projected Future Tons', true),
            __('Projected Future Life', true),
            __('Estimated Total Life', true),
            __('Estimated Total Life', true),
            __('Est. Total Cost per Ton', true),
            __('Est. Total Cost per Month', true)
        );

        
        $table_data[] = array(
            __('(tons)', true),
            __('(months)', true),
            __('tons/0.001*', true),
            __('(tons)', true),
            __('(months)', true),
            __('(tons)', true),
            __('(months)', true),
            __('($/ton)', true),
            __('($/month)', true)
        );

        $conveyor_update_ultra = $ultrasonic['install_update_ultra']=='0000-00-00 00:00:00' ? $conveyor['banda_fecha_instalacion'] : $ultrasonic['install_update_ultra'];
        //$conveyor = $conveyor['Conveyor'];
        if (!empty($ultrasonic_data)) {
            foreach ($ultrasonic_data AS $index => $ultrasonic_reading) {
                $ultrasonic_reading = $ultrasonic_reading['UltrasonicReading'];
                $conveyed_tons = $ultrasonic_reading['conveyed_tons'];
                $months_reading = $this->diffInMonths($conveyor_update_ultra, $ultrasonic_reading['reading_date']);
                $avgs = explode('||', $ultrasonic_reading['avgs']);
                $avgs = explode(',', $avgs[1]);

                $ultrasonic['original_top_cover'] = $ultrasonic["units"]=='metric' ? $ultrasonic['original_top_cover']*0.0393701 : $ultrasonic['original_top_cover'];
                $wear_rate = $conveyed_tons / ($ultrasonic['original_top_cover'] - (min($avgs))) / 1000;
                $future_tons = $wear_rate * min($avgs) * 1000;
                $future_life = $conveyed_tons <= 0 ? 0 : $months_reading * $future_tons / $conveyed_tons;
                $estimated_total_life_tons = $ultrasonic['original_top_cover'] * $wear_rate * 1000;
                $estimated_total_life_months = $months_reading + $future_life;
                $total_cost_per_ton = $estimated_total_life_tons <= 0 ? 0 : $ultrasonic['conveyor_price'] / $estimated_total_life_tons;
                $total_cost_per_month = $estimated_total_life_months <= 0 ? 0 : $ultrasonic['conveyor_price'] / $estimated_total_life_months;

                $table_data[] = array(
                    number_format($conveyed_tons, 0, '', ','),
                    $months_reading,
                    number_format($wear_rate, 0, '', ','),
                    number_format($future_tons, 0, '', ','),
                    number_format($future_life, 0, '', ','),
                    number_format($estimated_total_life_tons, 0, '', ','),
                    number_format($estimated_total_life_months, 0, '', ','),
                    '$'.number_format($total_cost_per_ton, 4, '.', ','),
                    '$'.number_format($total_cost_per_month, 2, '.', ',')
                );                
            }
        }

        return $table_data;
    }

    public function addOrdinalNumberSuffix($num) {
        if (!in_array(($num % 100), array(11, 12, 13))) {
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1: return $num . 'st';
                case 2: return $num . 'nd';
                case 3: return $num . 'rd';
            }
        }
        return $num . 'th';
    }

    public function diffInMonths($date1, $date2) {
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $diff = $date1->diff($date2);
        $months = $diff->y * 12 + $diff->m + $diff->d / 30;
        return (int) round($months);
    }
    
    public function addUltrasonicAssocIfNotHave($conveyor_id){
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $ultrasonicAssoc = $this->Ultrasonic->findByConveyorId($conveyor_id);
        if(empty($ultrasonicAssoc)){
            $data = array('conveyor_id'=>$conveyor_id, 'updated_at'=>date('Y-m-d H:i:s'));
            $this->Ultrasonic->set($data);
            $this->Ultrasonic->save();            
        }
    }
    
    public function setDataForUpdate($data) {
        array_walk($data, create_function('&$str', '$str = "\'$str\'";'));
        return $data;
    }
    
    public function getViewedTutorialSections(){
        $this->TutorialSection = ClassRegistry::init('TutorialSection'); 
        $user_id = $this->_app_credentials['id'];
        $tutorialSection = $this->TutorialSection->findByUserId($user_id);
        $sections = array();
        if(!empty($tutorialSection)){//Save
            $viewed_sections_user = $tutorialSection['TutorialSection']['section'];
            $sections = array_map('intval', explode(',',$viewed_sections_user));
        }
        
        return $sections;
    }
    
    public function setTutorialSectionViewed($section){
        $credentials = $this->_app_credentials;
        $viewTutorialAllow = isset($credentials['permissions'][IElement::Is_Tutorial]) && in_array('view', $credentials['permissions'][IElement::Is_Tutorial]['allows']) ? true : false;

        if($viewTutorialAllow){
            $this->TutorialSection = ClassRegistry::init('TutorialSection');
            $user_id = $this->_app_credentials['id'];
            $tutorialSection = $this->TutorialSection->findByUserId($user_id);
            if(empty($tutorialSection)){//Save
                $data = array('user_id'=>$user_id, 'section'=>$section);
                $this->TutorialSection->set($data);
                $this->TutorialSection->save();
            }else{
                $viewed_sections_user = $tutorialSection['TutorialSection']['section'];
                $viewed_sections_user = explode(',',$viewed_sections_user);
                if(!in_array($section, $viewed_sections_user)){

                    $section = $tutorialSection['TutorialSection']['section'].','.$section;
                    $data = array('section'=>$section);
                    $data = $this->setDataForUpdate($data);
                    $this->TutorialSection->updateAll($data, array('user_id' => $user_id));
                }
            }
        }

    }

    public function getAbrassionLifeDebug($conveyor_id){
        $errors = [];
        $this->Conveyor = ClassRegistry::init('Conveyor');
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $this->UltrasonicReading = ClassRegistry::init('UltrasonicReading');
        $ultrasonicAssoc = $this->Ultrasonic->findByConveyorId($conveyor_id);
        if(!empty($ultrasonicAssoc)){
            $ultrasonicAssoc = $ultrasonicAssoc['Ultrasonic'];
            $ultrasonic_readings = $this->UltrasonicReading->find('all', array(
                'conditions' => array('UltrasonicReading.ultrasonic_id' => $ultrasonicAssoc['id'], 'UltrasonicReading.deleted' => 0),
                'order' => array('reading_date' => 'ASC')
            ));

            if(!empty($ultrasonic_readings)){
                $lastLecture = $ultrasonic_readings[count($ultrasonic_readings)-1];
                $lastLecture = $this->Converter->process_convertion_ultrasonic($lastLecture);
                $lastLecture = $lastLecture['UltrasonicReading'];

                //Init calculation
                $tons_conveyed = $lastLecture['conveyed_tons'];
                if ($tons_conveyed) {
                    $original_top_cover = $ultrasonicAssoc['original_top_cover'];
                    $calc_avgs = $this->calcAvgsWithReadings($lastLecture, $ultrasonicAssoc['compound_name']);
                    $adjustedTempAvgs = $calc_avgs[1];
                    $calculated_min_temp_adjusted_avg = round(min($adjustedTempAvgs), 3);
                    if($original_top_cover<$calculated_min_temp_adjusted_avg){
                        $errors[] = __("Favor de llenar correctamente el campo toneladas transportadas de la ultima lectura",true);
                    }
                }else{
                    $errors[] = __("Favor de llenar correctamente el campo toneladas transportadas de la ultima lectura",true);
                }
            }
        }
    }

    public function calcAbrasionLife($conveyor_id){
        $this->Conveyor = ClassRegistry::init('Conveyor');
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $this->UltrasonicReading = ClassRegistry::init('UltrasonicReading');
        $abrassionLifeData = [];

        $ultrasonicAssoc = $this->Ultrasonic->findByConveyorId($conveyor_id);
        if(!empty($ultrasonicAssoc)){
            $originalUltra = $ultrasonicAssoc;
            $ultrasonicAssoc = $ultrasonicAssoc['Ultrasonic'];
            $ultrasonic_readings = $this->UltrasonicReading->find('all', array(
                'conditions' => array('UltrasonicReading.ultrasonic_id' => $ultrasonicAssoc['id'], 'UltrasonicReading.deleted' => 0),
                'order' => array('reading_date' => 'ASC')
            ));

            if(!empty($ultrasonic_readings)){
                $lastLecture = $ultrasonic_readings[count($ultrasonic_readings)-1];
                $lastLecture = $this->Converter->process_convertion_ultrasonic($lastLecture, $language=null, $originalUltra);
                $lastLecture = $lastLecture['UltrasonicReading'];

                //Init calculation
                $tons_conveyed = $lastLecture['conveyed_tons'];
                if ($tons_conveyed) {
                    $original_top_cover = $ultrasonicAssoc['original_top_cover'];
                    $original_top_cover = $ultrasonicAssoc['units']=="metric" ? $original_top_cover * 0.0393701 : $original_top_cover;//convertir a inches si esta en metric
                    $calc_avgs = $this->calcAvgsWithReadings($lastLecture, $ultrasonicAssoc['compound_name']);
                    /*echo '<pre>';
                    var_dump($calc_avgs);
                    echo '</pre>';*/
                    $adjustedTempAvgs = $calc_avgs[1];
                    //var_dump($adjustedTempAvgs);

                    $calculated_min_temp_adjusted_avg = round(min($adjustedTempAvgs), 3);
                    $wear_rate = $tons_conveyed / (($original_top_cover - $calculated_min_temp_adjusted_avg) * 1000);
                    $projected_future_tons = round($wear_rate * $calculated_min_temp_adjusted_avg * 1000, 2);

                    $current_date = date('Y-m-d');
                    $installed_date = date("Y-m-d", strtotime($ultrasonicAssoc['install_update_ultra']));
                    $datetime1 = new DateTime($installed_date);
                    //$datetime2 = new DateTime($lastLecture['reading_date']);
                    $datetime2 = new DateTime($current_date);//For maintain updated days

                    $interval = $datetime1->diff($datetime2);
                    $elapsed_days = $interval->days;
                    $elapsed_months = round($elapsed_days / 30.4);

                    $projected_future_life_months = ($elapsed_months * $projected_future_tons) / $tons_conveyed;
                    $projected_future_life_months = round($projected_future_life_months, 1);

                    $estimated_total_tons = round($original_top_cover * $wear_rate * 1000, 2);
                    $estimated_total_life_months = $elapsed_months + $projected_future_life_months;

                    //mail("elalbertgd@gmail.com",'ultra', print_r([$calculated_min_temp_adjusted_avg, $wear_rate, $projected_future_tons, $elapsed_days, $elapsed_months, $estimated_total_life_months], true));

                    //if $estimated_total_life_months = 0, may be is due to install_date and reading date in same month, check if put one month for this case
                    $estimated_total_life_months = abs($estimated_total_life_months);
                    if ($estimated_total_life_months > 0) {
                        $percent_cover_used = (($estimated_total_life_months - $projected_future_life_months) * 100) / $estimated_total_life_months;
                        $percent_cover_used = round($percent_cover_used, 4);
                        $abrassionLifeData = ['percent_cover_used' => round($percent_cover_used, 0), 'durometer' => $lastLecture['durometer'], 'projected_future_life' => round($projected_future_life_months, 0)];
                    }

                    $abrassionLifeData['percent_cover_used'] = (100 - $abrassionLifeData['percent_cover_used']) <= 0 ? 100 : $abrassionLifeData['percent_cover_used'];
                    $abrassionLifeData['projected_future_life'] = $abrassionLifeData['projected_future_life'] <= 0 ? 0 : $abrassionLifeData['projected_future_life'];
                }

            }
        }

        return $abrassionLifeData;
    }

    public function readingsStrToArray($readingsStr){
        $readingsArr = [];
        $readings = explode('||',$readingsStr);
        foreach($readings AS $reading){
            if(strlen($reading)>6){//Tiene valores de lecturas
                $single_readings = explode(',',$reading);
                if(array_sum($single_readings)>0){
                    $readingsArr[] = $single_readings;
                }
            }
        }
        return $readingsArr;
    }
    /**
     * Calc avgs readings
     * @param array $table_readings
     */
    public function calcAvgsWithReadings($reading_data, $compoundUltrasonic) {
        $this->Ultrasonic = ClassRegistry::init('Ultrasonic');
        $ultrasonicAssoc = $this->Ultrasonic->findById($reading_data['ultrasonic_id']);
        $ultrasonicAssoc = $ultrasonicAssoc["Ultrasonic"];

        $compound_id = $compoundUltrasonic;
        $table_readings = $this->readingsStrToArray($reading_data['readings']);
        $temperature = $reading_data['temperature'];
        //Si el sistema esta en español se toma como que se esta capturando celsius

        //$temperature = $ultrasonicAssoc['units']=='imperial' && $reading_data["filled_lang"]=='es' ?
        //var_dump($temperature);
        $temperature = $ultrasonicAssoc['units']=='metric' ? ($temperature * 1.8) + 32 : $temperature; //Si esta en español (capturaron celsius) -> convertir a farenheit
        //$temperature = $this->_app_language == 'es'? ($temperature * 1.8) + 32 : $temperature; //Si esta en español (capturaron celsius) -> convertir a farenheit
        //var_dump($temperature);

        $compoundMatrix = $this->getCompoundMatrixValues();

        $avgs = array();
        $avgs_tmp = array();
        for ($cols = 0; $cols < 7; $cols++) {//Iter cols
            $sumatoria = 0;
            $columns_filled = 0;
            for ($rows = 0; $rows < count($table_readings); $rows++) {//iter rows
                if (isset($table_readings[$rows][$cols]) && floatval($table_readings[$rows][$cols]) > 0) {//if defined index
                    $sumatoria += $table_readings[$rows][$cols];
                    $columns_filled++;
                }
            }

            $avg = $avg_temp = 0;
            if (floatval($sumatoria) > 0) {
                $avg = ($sumatoria / $columns_filled);
                if($ultrasonicAssoc['units']=='metric'){
                    $avg = $avg * 0.0393701; //Convertir a inches, la formular de avg adj temp, usa inches
                }

                if ($avg != 0) {
                    $avg_temp = (70 - $temperature) * ($compoundMatrix[$compound_id][0] * (pow($avg, 2)) + ($compoundMatrix[$compound_id][1] * $avg) + $compoundMatrix[$compound_id][2]);
                    $avg_temp = $avg_temp + $avg;
                }
            }
            $avgs[] = $avg;
            $avgs_tmp[] = $avg_temp;
        }
        return array($avgs, $avgs_tmp);
    }

    /**
     * get region
     * @return string
     */
    public function getRegion(){
        $region = $this->_app_credentials["region"];
        $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
        $region = in_array($region, array('MX1','MX2','CENAM')) ? 'MX' : $region;
        $region = in_array($region, array('NORTHEAST','MIDWEST','SOUTH','WEST','SOUTHEAST','US')) ? 'US' : $region;
        $region = preg_match("/\bCA-\b/i", $region) ? 'CA' : $region;
        return $region;
    }

    /**
     * Check if some region is from US
     * @param $region
     * @return bool
     */
    public function isUsRegion($region){
        $isUSRegion = false;
        $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
        $region = in_array($region, array('MX1','MX2','CENAM')) ? 'MX' : $region;
        $region = in_array($region, array('NORTHEAST','MIDWEST','SOUTH','WEST','SOUTHEAST','US')) ? 'US' : $region;
        $region = preg_match("/\bCA-\b/i", $region) ? 'CA' : $region;
        if(in_array($region, ["US","CA"])){
            $isUSRegion = true;
        }
        return $isUSRegion;
    }

    public function getRealRegion($region) {
        $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
        $region = in_array($region, array('MX1','MX2')) ? 'MX' : $region;
        $region = in_array($region, array('NORTHEAST','MIDWEST','SOUTH','WEST','SOUTHEAST','US')) ? 'US' : $region;
        $region = preg_match("/\bCA-\b/i", $region) ? 'CA' : $region;
        $region = in_array($region, array('CHILE')) ? 'CL' : $region;
        $region = in_array($region, array('BRASIL')) ? 'BR' : $region;
        $region = in_array($region, array('AUSTRALIA')) ? 'AU' : $region;
        $region = in_array($region, array('CENAM')) ? 'CENAM' : $region;

        return $region;
    }

    /**
     * Create a random password
     * @return string
     */
    public function getRandomPassword()
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Get all salesperson users in the system
     * salesperson are user with puesto = 7 and role = admin
     * @return mixed
     */
    public function getSalesPersonList(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');

        //$this->UsuariosEmpresa->recursive = 1;
        $this->UsuariosEmpresa->unbindModel(
            array('hasMany' => ['SharedDistributors','SharedClients','Statistics'])
        );

        $salesperson_list = $this->UsuariosEmpresa->find('all',['conditions'=>['UsuariosEmpresa.puesto'=>7, 'UsuariosEmpresa.role'=>'admin','UsuariosEmpresa.deleted'=>0,'UsuariosEmpresa.active'=>1], 'fields'=>['UsuariosEmpresa.id','UsuariosEmpresa.name','UsuariosEmpresa.email','UsuariosEmpresa.id_empresa','UsuariosEmpresa.region']]); //get all user with puesto is 7 (ventas), and belongs to admin compoany(territory company)
        $salesperson_list = Set::extract('/UsuariosEmpresa/.', $salesperson_list);

        return $salesperson_list;
    }

    /**
     * get salesperson according to current user session
     * @return array
     */
    public function getSalespersonForCurrentSession(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');

        $credentials = $this->_app_credentials;
        $salesperson_list = [];
        switch ($credentials['i_group_id']) {
            case IGroup::MASTER:case IGroup::REGION_MANAGER:case IGroup::COUNTRY_MANAGER:case IGroup::TERRITORY_MANAGER: //all salesperson
                $salesperson_list = $this->UsuariosEmpresa->find('all',['recursive'=>-1, 'conditions'=>['UsuariosEmpresa.puesto'=>7, 'UsuariosEmpresa.role'=>'admin','UsuariosEmpresa.deleted'=>0], 'fields'=>['UsuariosEmpresa.id','UsuariosEmpresa.name','UsuariosEmpresa.email','UsuariosEmpresa.id_empresa','UsuariosEmpresa.region']]); //get all user with puesto is 7 (ventas), and belongs to admin compoany(territory company)
             break;
            case IGroup::DISTRIBUTOR:case IGroup::RUBBER_DISTRIBUTOR:case IGroup::DISTRIBUTOR_MANAGER:
                $empresaDistribuidor = $this->Empresa->findById($credentials['id_empresa']);//get company of user
                //get salesperson by shared salesperson to distributor
                $salespersonAssocToDist = array_column($empresaDistribuidor['SalespersonShares'],'user_sp_id');
                $salespersonAssocToDist = $this->UsuariosEmpresa->find('all',['recursive'=>-1, 'conditions'=>['UsuariosEmpresa.id'=>$salespersonAssocToDist,'UsuariosEmpresa.puesto'=>7,'UsuariosEmpresa.role'=>'admin','UsuariosEmpresa.deleted'=>0], 'fields'=>['UsuariosEmpresa.id','UsuariosEmpresa.name','UsuariosEmpresa.email','UsuariosEmpresa.id_empresa','UsuariosEmpresa.region']]);

                $salespersonTerritory = $this->UsuariosEmpresa->find('all',['recursive'=>-1, 'conditions'=>['UsuariosEmpresa.region' => $credentials['region'], 'UsuariosEmpresa.puesto'=>7, 'UsuariosEmpresa.role'=>'admin','UsuariosEmpresa.deleted'=>0], 'fields'=>['UsuariosEmpresa.id','UsuariosEmpresa.name','UsuariosEmpresa.email','UsuariosEmpresa.id_empresa','UsuariosEmpresa.region']]); //get all user with puesto is 7 (ventas), and belongs to admin compoany(territory company)
                $salesperson_list = array_merge($salespersonAssocToDist, $salespersonTerritory);
            break;
        }


        return $salesperson_list;
    }

    public function getsalesPersonApp(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $blackList = ['MX1','MX2','CENAM','AUSTRALIA','BRASIL','CHILE','GERMANY','SWEDEN','RUSSIA','HUNGARY','SOUTHAFRICA'];
        $salesperson = $this->UsuariosEmpresa->find('all',['order' => ['UsuariosEmpresa.name ASC'],'conditions'=>['NOT'=>['UsuariosEmpresa.region' =>$blackList],'UsuariosEmpresa.puesto'=>7, 'UsuariosEmpresa.role'=>'admin','UsuariosEmpresa.deleted'=>0]]); //get all user with puesto is 7 (ventas), and belongs to admin compoany(territory company)

        return $salesperson;
    }

    /**
     * Get salesperson for registry score card
     * @return int
     */
    public function getSalespersonIfExists($company_id = 0){
        $this->Empresa = ClassRegistry::init('Empresa');
        $salespersonId = 0;
        $credentials = $this->_app_credentials;
        //if user logged is a salesperson
        if($credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON && $credentials['i_group_id']==IGroup::TERRITORY_MANAGER){ //if is valid salesperson
            $salespersonId = $credentials['id'];
        }else if($credentials['i_group_id']==IGroup::DISTRIBUTOR && $company_id>0){
            $this->Empresa->recursive = 0;
            $companyClient = $this->Empresa->findById($company_id, ['salesperson_user_id']);
            if(!is_null($companyClient['Empresa']['salesperson_user_id'])){
                $salespersonId = $companyClient['Empresa']['salesperson_user_id'];
            }
        }else if($credentials['i_group_id']==IGroup::CLIENT && !is_null($credentials['salesperson'])){ //Is client and his salesperson is valid id
            $salespersonId = $credentials['salesperson'];
        }

        return $salespersonId;
    }


    /**
     * get shared distributors for one salesperson logged
     * @return array
     */
    public function getSharedDealersSalesperson(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');
        $credentials = $this->_app_credentials;
        $distributorsShared = [];
        if($credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
            $userRelations = $this->UsuariosEmpresa->findById($credentials['id'], ['UsuariosEmpresa.id']);
            if(!empty($userRelations['SharedDistributors'])){
                $dealerIdsArr =  array_column($userRelations['SharedDistributors'], 'company_id');
                $dealerIds = implode(',',$dealerIdsArr);
                $distributorsShared = $this->Empresa->findByIdsWithCorporate($dealerIds);
            }
        }

        return $distributorsShared;
    }

    /**
     * get collection of shared clients  for one salesperson logged
     * @return array
     */
    public function getSharedClientsSalesperson(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');
        $credentials = $this->_app_credentials;
        $clientsShared = [];
        if($credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
            $userRelations = $this->UsuariosEmpresa->findById($credentials['id'], ['UsuariosEmpresa.id']);
            if(!empty($userRelations['SharedClients'])){
                $clientIdsShared = array_column($userRelations['SharedClients'], 'id');
                $clientIdsShared = implode(',',$clientIdsShared);
                $clientsShared = $this->Empresa->findByIdsWithCorporate($clientIdsShared);
            }
        }

        return $clientsShared;
    }

    /**
     * get array with ids of shared clients for some salesperson
     * @return array
     */
    public function getSharedClientIdsSalesperson(){
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $this->Empresa = ClassRegistry::init('Empresa');
        $credentials = $this->_app_credentials;
        $clientsShared = [];
        if($credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
            $userRelations = $this->UsuariosEmpresa->findById($credentials['id'], ['UsuariosEmpresa.id']);
            if(!empty($userRelations['SharedClients'])){
                $clientsShared = array_column($userRelations['SharedClients'], 'id');
            }
        }

        if($credentials['i_group_id']==IGroup::TERRITORY_MANAGER){
            $whiteListRegionIds = [1,2,3,4,5,6,7,10,11,15,16,18]; //regions us, canada, germany and russia
            if(in_array($credentials['company_region_id'], $whiteListRegionIds)){
                $clientsShared[] = 686; //Client test AA - P Test
            }
        }

        return $clientsShared;
    }

    public function getSalespersonForDistributorId($dealerId){
        $this->Empresa = ClassRegistry::init('Empresa');
        //get company dist for get all relations
        $empresa = $this->Empresa->findById($dealerId);
        $salespersons = [];

        $credentials = $this->_app_credentials;

        //get all salesperson in system
        $allSalesperson = $this->getSalesPersonList();
        //get the company's shared salesperson
        $salesperson_saved = Set::extract('/SalespersonShares/.', $empresa);
        if($credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON){
            $key = array_search($credentials['id'], array_column($allSalesperson, 'id'));
            $salespersons[] = ['id' => $allSalesperson[$key]['id'], 'name' => $allSalesperson[$key]['name'], 'region' => $allSalesperson[$key]['region']];
        }else {
            foreach ($allSalesperson AS $salesperson) {
                if ($empresa['Empresa']['region'] == $salesperson['region'] || in_array($salesperson['id'], array_column($salesperson_saved, 'user_sp_id'))) {
                    $salespersons[] = ['id' => $salesperson['id'], 'name' => $salesperson['name'], 'region' => $salesperson['region']];
                }
            }
        }
        return $salespersons;
    }

    public function getRegionCountryAndMarketForUserLogged(){
        $data = ['region'=>0, 'country'=>0, 'market'=>0];
        $logged_user = $this->_app_credentials;

        switch ($logged_user['i_group_id']) {
            case IGroup::MASTER: //dont matters, all dist
            break;
            case IGroup::MARKET_MANAGER:
                $data['market'] = $logged_user['company_market_id'];
            break;
            case IGroup::COUNTRY_MANAGER:
                $data['market'] = $logged_user['company_market_id'];
                $data['country'] = $logged_user['company_country_id'];
            break;
            case IGroup::REGION_MANAGER:case IGroup::TERRITORY_MANAGER:case IGroup::DISTRIBUTOR:
                $data['market'] = $logged_user['company_market_id'];
                $data['country'] = $logged_user['company_country_id'];
                $data['region'] = $logged_user['company_region_id'];
            break;
        }

        return $data;
    }

    public function getPairsMetaUnits($metaUnits){
        $pairs_units =  explode("||",$metaUnits);
        $unitsArr = [];
        foreach ($pairs_units AS $unit_pair){
            list($field, $unit) = explode("=",$unit_pair);
            $unitsArr[$field] = $unit;
        }

        return $unitsArr;
    }

    public function convertToMM($valueInches){
        return($valueInches*25.4);
    }


    public function fillMissingValuesInArrayTime($time_data){
        $normalize_data = [];
        if(!empty($time_data)){
            $lastElement = end($time_data);
            foreach ($time_data as $hour => $data) {
                $normalize_data[] = $data;
                if($data!=$lastElement){
                    $nextHour = $hour;
                    do{
                        $nextHour = date('H:00', strtotime($nextHour) + 60 * 60);
                        if(!isset($time_data[$nextHour])){
                            $normalize_data[] = [$nextHour, $data[1]];
                        }
                    }while(!isset($time_data[$nextHour]) );
                }
            }
        }else{
            $normalize_data =  $time_data;
        }
        return $normalize_data;
    }

    /*
    public function fillLastHoursColors($numberHours,$time_data){

        $normalized_data = [];
        if(!empty($time_data)){
            $lastElement = end($time_data);
            foreach ($time_data as $hour => $data) {
                $normalized_data[$hour] = $data;
                if($data!=$lastElement){
                    $nextHour = $hour;
                    do{
                        $nextHour = date('H:00', strtotime($nextHour) + 60 * 60);
                        if(!isset($time_data[$nextHour])){
                            $normalized_data[$nextHour] = [$nextHour, $data[1], $data[2], $data[3], $data[4]];
                        }
                    }while(!isset($time_data[$nextHour]) );
                }
            }
        }else{
            $normalized_data =  $time_data;
        }

        //date_default_timezone_set('Europe/Berlin');
        $difHoursGermany = 1;
        $current_date=date('Y-m-d H:i:s');
        $normalized_data_ok = [];
        $zeroHours = date('Y-m-d 00:00:00');
        $endHour = date('Y-m-d H:i:s', strtotime($current_date) + (3600*$difHoursGermany));
        $hoursToZero = abs(strtotime($endHour) - strtotime($zeroHours))/3600;
        $numberHours = $hoursToZero<$numberHours ? $hoursToZero+1:$numberHours;
        $initialHour = date('H:00', strtotime($endHour) - (3600 * ($numberHours-1)));

        $lastData = null;
        $prevHour = $initialHour;
        $nextHour = $initialHour;
        for($i=0;$i<$numberHours;$i++){
            if(isset($normalized_data[$nextHour])){
                $lastData = $normalized_data[$nextHour];
                $normalized_data_ok[] = [$nextHour, $lastData[1], $lastData[2], $lastData[3], $lastData[4]];
            }else{
                $normalized_data_ok[] = !is_null($lastData) ? [$nextHour,  $lastData[1], $lastData[2], $lastData[3], $lastData[4]] : [$nextHour, 0,0,0,0];
            }
            $prevHour = $nextHour;
            $nextHour = date('H:00', strtotime($nextHour) + 60 * 60);

        }


        //no encontro datos
        if(is_null($lastData)){
            $lastElementInRange = end($normalized_data);
            $firstElementInRange = reset($normalized_data);
            foreach ($normalized_data_ok as $index =>$data) {
                if(strtotime($data[0])>strtotime($lastElementInRange[0])){//si el item que tiene 0, es de una hora mayor a la hora del ultimo elemento calculado
                    $normalized_data_ok[$index] = [$data[0],$lastElementInRange[1],$lastElementInRange[2],$lastElementInRange[3],$lastElementInRange[4]];
                }
            }
        }

        return $normalized_data_ok;
    }

    public function fillLastHours($numberHours,$time_data){
        //date_default_timezone_set('Europe/Berlin');
        //date_default_timezone_set('Europe/Berlin');

        $normalized_data = [];
        if(!empty($time_data)){
            $lastElement = end($time_data);
            foreach ($time_data as $hour => $data) {
                $normalized_data[$hour] = $data;
                if($data!=$lastElement){
                    $nextHour = $hour;
                    do{
                        $nextHour = date('H:00', strtotime($nextHour) + 60 * 60);
                        if(!isset($time_data[$nextHour])){
                            $normalized_data[$nextHour] = [$nextHour, $data[1]];
                        }
                    }while(!isset($time_data[$nextHour]) );
                }
            }
        }else{
            $normalized_data =  $time_data;
        }

        date_default_timezone_set('Europe/Berlin');
        $difHoursGermany = 1;
        $current_date=date('Y-m-d H:i:s');
        $normalized_data_ok = [];
        $zeroHours = date('Y-m-d 00:00:00');
        $endHour = date('Y-m-d H:i:s', strtotime($current_date) + (3600*$difHoursGermany));
        $hoursToZero = abs(strtotime($endHour) - strtotime($zeroHours))/3600;
        $numberHours = $hoursToZero<$numberHours ? $hoursToZero+1:$numberHours;
        $initialHour = date('H:00', strtotime($endHour) - (3600 * ($numberHours-1)));

        $lastData = null;
        $prevHour = $initialHour;
        $nextHour = $initialHour;
        for($i=0;$i<$numberHours;$i++){
            if(isset($normalized_data[$nextHour])){
                $lastData = $normalized_data[$nextHour][1];
                $normalized_data_ok[] = [$nextHour, $lastData];
            }else{
                $normalized_data_ok[] = !is_null($lastData) ? [$nextHour, $lastData] : [$nextHour, 0];
            }
            $prevHour = $nextHour;
            $nextHour = date('H:00', strtotime($nextHour) + 60 * 60);

        }


        //no encontro datos
        if(is_null($lastData)){
           $lastElementInRange = end($normalized_data);
           $firstElementInRange = reset($normalized_data);
           foreach ($normalized_data_ok as $index =>$data) {
               if(strtotime($data[0])>strtotime($lastElementInRange[0])){//si el item que tiene 0, es de una hora mayor a la hora del ultimo elemento calculado
                   $normalized_data_ok[$index] = [$data[0],$lastElementInRange[1]];
               }
           }
        }

        return $normalized_data_ok;
    }
*/

    public function calcPPTInMonthRows($rows){
        $total_ppt = 0;
        if(!empty($rows)){

            $black=$blue=$green=$red=0;
            $green_ppt = $red_ppt = 0;
            foreach ($rows AS $colorLog){
                $colorLog = $colorLog['ColorLog'];

                switch ($colorLog['color']){
                    case 'black': $black++; break;
                    case 'blue': $blue++; break;
                    case 'green': $green++; $green_ppt++; break;
                    case 'red': $red++; $red_ppt++; break;
                }
            }

            $total_ppt = ($red_ppt*5) + ($green_ppt*12);
        }

        return $total_ppt;
    }

    public function calcFillLevelWithData($data){
        $resultData = [['0',0]];
        if(!empty($data)){
            $fillLevelData=[];
            foreach ($data AS $fillLevelLog){
                $fillLevelLog = $fillLevelLog['FillLevelLog'];

                $date = date("Y-m-d", $fillLevelLog['id']);
                $time = date("H:i:s", $fillLevelLog['id']);
                $time_normal = date("H:i", $fillLevelLog['id']);

                $fillLevelData[]= [(int)$fillLevelLog['id'], (float)$fillLevelLog['distance']];
            }
            $resultData = $fillLevelData;
        }

        return $resultData;
    }

    public function calcMeasureData($data){
        $resultData = [
            'maxPercent' => '100',
            'headers'=>['Value',''],
            'values'=>['',0],
            'colors'=>[ ['color'=>'#000000'] ]
        ];
        if(!empty($data)) {
            $lastLecture = end($data);
            $lastPercent = round($lastLecture['FillLevelLog']['distance']);
            $valuesData = ["$lastPercent%"];
            for($i=10;$i<$lastPercent;$i+=10){
              array_push($valuesData, 10);
            }


            if(($i-10)<$lastPercent){
                $diffBlue = $lastPercent%10;
                $diffGray = 10-$diffBlue;

                //Aagregar las unidades en azul
                $blueColors = array_fill(0, count($valuesData)+$diffBlue, ['color'=>'#1F56A0']);
                $diffBlue = array_fill(0, $diffBlue, 1);
                $valuesData = array_merge($valuesData, $diffBlue);

                //agregar las unidades en gris
                $diffGray = array_fill(0, $diffGray, 1);
                $valuesData = array_merge($valuesData, $diffGray);

                $fullPercentDiff = 100-$i;
                $emptyFullSeries = 0;
                if($fullPercentDiff>0){
                    $emptyFullSeries = $fullPercentDiff/10;
                    $emptyFullSeries = array_fill(0, $emptyFullSeries, 10);
                    $valuesData = array_merge($valuesData, $emptyFullSeries);
                }
                $grayColors = array_fill(0, count($emptyFullSeries)+count($diffGray), ['color'=>'#D3D4D4']);


                //var_dump($diffBlue);
                //var_dump($diffGray);
            }
            //#D3D4D4

            //set headers
            $headers = array_fill(0, count($valuesData), '');
            $resultData['maxPercent'] = $lastPercent<=100 ? 100 : $lastPercent;
            $resultData['headers'] = $headers;
            $resultData['colors'] = array_merge($blueColors, $grayColors);

            $resultData['values'] = $valuesData;
        }
        return $resultData;
    }
    public function calcEventsDayData($data){
        $resultData = [['0',0,'']];

        if(!empty($data)){
            $resultData = [];
            foreach ($data AS $colorLog){
                $colorLog = $colorLog['ColorLog'];

                $date = date("Y-m-d", $colorLog['id']);
                $time = date("H:i:s", $colorLog['id']);
                $time_normal = date("H:i", $colorLog['id']);

                $resultData[] = [(int)$colorLog['id'], 1, 'stroke-color: #1041FB; stroke-width: 0.5; fill-color: #FFFFFF'];
            }
        }

        return $resultData;
    }

    public function calcEventsDayList($data){
        $resultData = [];


        $events = [
            ['Pulley #1', 'Pulley #1 has a temperature superior to normal parameters. Please verify status.'],
            ['Sensor #63, Belt #1','A rip has been identified by Sensor #63 on Belt #1. Please verify status.'],
            ['Sensor #54, Belt #2','Overweight has been detected by Sensor #54 on Belt #2. Please verify status.'],
            ['Belt #3','A critical wear has been detected on Belt #3. Please verify status.'],
            ['Belt #2','The estimated remaining lifetime of Belt #2 is inferior to 6 months. Please concider ordering a new belt.'],
            ['Idler #23','Idler #23 has a temperature superior to normal parameters. Please verify status.'],
            ['Sensor #42, Belt #4','A rip has been identified by Sensor #42 on Belt #4. Please verify status.'],
            ['Sensor #17, Belt #1','Overweight has been detected by Sensor #17 on Belt #1. Please verify status.'],
            ['Belt #2','A critical wear has been detected on Belt #2. Please verify status.'],
            ['Belt #4','The estimated remaining lifetime of Belt #4 is inferior to 1 year. Please concider ordering a new belt.'],
        ];
        if(!empty($data)){
            $resultData = [];
            foreach ($data AS $colorLog){
                $colorLog = $colorLog['ColorLog'];

                $date = date("Y-m-d", $colorLog['id']);
                $time = date("H:i:s", $colorLog['id']);
                $time_normal = date("H:i", $colorLog['id']);

                $hight_priority = '<div class="priority hight"></div>';
                $medium_priority = '<div class="priority medium"></div>';
                $low_priority = '<div class="priority low"></div>';
                $prioritySelected = rand(0, 2);
                $eventSelected = rand(0, count($events)-1);
                $resultData[] = [[$low_priority, $medium_priority,$hight_priority][$prioritySelected],  $time_normal, $events[$eventSelected][0],$events[$eventSelected][1]];
            }
        }

        return $resultData;
    }

    public function getLastWeekColorData($data, $initialDate){
        $resultData = [[0,0,0]];
        if(!empty($data)){
            $resultDataTemp = [];
            for($i=0;$i<7;$i++){
                $nextDay = strtotime($initialDate. "+ $i days");
                $resultDataTemp[$nextDay] = [$nextDay, 0,0];
            }

            $green=$red=0;
            $lastIndex = 0;
            foreach ($data AS $colorLog){
                $colorLog = $colorLog['ColorLog'];

                $date = date("Y-m-d", $colorLog['id']);
                $date_full = strtotime(date("Y-m-d 00:00:00", $colorLog['id']));

                if($lastIndex!=$date_full){
                    $green = 0;
                    $red = 0;
                    $lastIndex = $date_full;
                }

                switch ($colorLog['color']){
                    case 'green': $green++; break;
                    case 'red': $red++; break;
                }

                $resultDataTemp[$date_full]  = [$date_full, $red, $green];
            }

            $resultData = [];
            foreach ($resultDataTemp AS $index => $value){
                $resultData[] = $value;
            }
        }

        return $resultData;
    }

    public function getLastWeekColorDataPPT($data, $initialDate){
        $resultData = [[0,0]];
        if(!empty($data)){
            $resultDataTemp = [];
            for($i=0;$i<7;$i++){
                $nextDay = strtotime($initialDate. "+ $i days");
                $resultDataTemp[$nextDay] = [$nextDay, 0];
            }

            $green=$red=0;
            $lastIndex = 0;
            foreach ($data AS $colorLog){
                $colorLog = $colorLog['ColorLog'];

                $date = date("Y-m-d", $colorLog['id']);
                $date_full = strtotime(date("Y-m-d 00:00:00", $colorLog['id']));

                if($lastIndex!=$date_full){
                    $green = 0;
                    $red = 0;
                    $lastIndex = $date_full;
                }

                switch ($colorLog['color']){
                    case 'green': $green++; break;
                    case 'red': $red++; break;
                }

                $ppt_in_time = ($red*5) + ($green*12);
                $resultDataTemp[$date_full]  = [$date_full, $ppt_in_time];
            }

            $resultData = [];
            foreach ($resultDataTemp AS $index => $value){
                $resultData[] = $value;
            }
        }

        return $resultData;
    }

    public function cleanJson($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->cleanJson($v);
            }
        } else if (is_string($d)) {
            return utf8_encode($d);
        }
        return $d;
    }
    
    function throwErrorException($errstr = null,$code = null, $errno = null, $errfile = null, $errline = null) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
      }

    public static function warning_handler($errno, $errstr, $errfile, $errline, array $errcontext) {
        return false && $this->throwErrorException($errstr, 0, $errno, $errfile, $errline);
        # error_log("AAA"); # will never run after throw
        /* Do execute PHP internal error handler */
        # return false; # will never run after throw
      }

}