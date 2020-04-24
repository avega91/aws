<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file SecurityComponent.php
 *     Component to manage system security
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class SecureComponent extends Component {

    var $components = array('Core', 'Session', 'Notifications'); // the other component your component uses

    /**
     * Administra el comportamiento de login si introduce mal la contraseÃ±a
     * @param string $username el usuario
     * @param string $password el password
     * @return string
     */

    public function manageLoginTries($username, $password) {
        $msg = '';
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $usuario = $this->UsuariosEmpresa->findByUsername($username);
        if (!empty($usuario)) {//Si existe el usuario y no hizo login es porque la contraseÃ±a que proporciono es incorrecta o esta bloqueado
            if ($usuario['UsuariosEmpresa']['lock_status'] == UsuariosEmpresa::IS_UNLOCKED && $usuario['UsuariosEmpresa']['logged_in'] == 0) {//Si el usuario no esta bloqueado y no ha hecho login anteriormente
                //Hay que actualizar intento de login
                $usuario['UsuariosEmpresa']['access_attempts'] = $usuario['UsuariosEmpresa']['access_attempts'] + 1;
                $usuario['UsuariosEmpresa']['last_access_attempt'] = date('Y-m-d H:i:s');
                //$this->UsuariosEmpresa->save($usuario);

                $login_tries = $usuario['UsuariosEmpresa']['access_attempts']; //Obtenemos el numero total de intentos de login actuales
                switch ($login_tries) {
                    case 2://Hay que notificarle al usuario que a la proxima sera bloquedo por 30 minutos
                        //$msg = __('Si proporciona una contrasena incorrecta nuevamente, su cuenta sera bloqueada por %s minutos. En caso de duda acerca de sus datos de acceso, por favor reportelo a admin@contiplus.net.', array(_LOCK_TIME));
                        $msg = __('Si proporciona una contrasena incorrecta nuevamente, su cuenta sera bloqueada. En caso de duda acerca de sus datos de acceso, por favor reportelo a admin@contiplus.net.', true);
                        break;
                    case 3://Bloquear usuario
                        //$usuario['UsuariosEmpresa']['lock_status'] = UsuariosEmpresa::IS_TEMPORARY_LOCKED;
                        //$usuario['UsuariosEmpresa']['last_date_lock'] = date('Y-m-d H:i:s');
                        //$msg = __('Por seguridad, la cuenta ha sido bloqueada temporalmente. Espere %s minutos para intentar nuevamente. En caso de duda acerca de sus datos de acceso, por favor reportelo a admin@contiplus.net.', array(_LOCK_TIME));
                        $msg = __('Por seguridad la cuenta ha sido bloqueado debido a varios intentos de acceso con una contrasena incorrecta. Por favor notifique a admin@contiplus.net para verificar sus datos y desbloquear la cuenta.', true);
                        $usuario['UsuariosEmpresa']['lock_status'] = UsuariosEmpresa::IS_PERMANENTLY_LOCKED;
                        $usuario['UsuariosEmpresa']['last_date_lock'] = date('Y-m-d H:i:s');
                    break;
                    case 4://notificar al usuario que sera bloqueado indefinidamente
                        $msg = __('Si proporciona una contrasena incorrecta nuevamente, su cuenta sera bloqueada. En caso de duda acerca de sus datos de acceso, por favor reportelo a admin@contiplus.net.', true);
                        break;
                    case 5:
                        $msg = __('Por seguridad la cuenta ha sido bloqueado debido a varios intentos de acceso con una contrasena incorrecta. Por favor notifique a admin@contiplus.net para verificar sus datos y desbloquear la cuenta.', true);
                        $usuario['UsuariosEmpresa']['lock_status'] = UsuariosEmpresa::IS_PERMANENTLY_LOCKED;
                        $usuario['UsuariosEmpresa']['last_date_lock'] = date('Y-m-d H:i:s');
                        break;
                    default:
                        $msg = __('La contrasena proporcionada es incorrecta', true);
                        break;
                }

                $this->UsuariosEmpresa->save($usuario);
                if ($login_tries == 3) {
                    $this->Notifications->userHasBeenLocked($usuario['UsuariosEmpresa']['id']);
                }
                if ($login_tries >= 5) {
                    /*
                     * Guardamos la notificacion *
                     * ************************** */
                    $this->Notifications->userHasBeenLocked($usuario['UsuariosEmpresa']['id']);
                }

                /*
                 * Guardamos log de navegacion - intento de acceso
                 */
                $this->saveBrowsingData(BrowsingLog::ACCESO, 'UsuariosEmpresa', $usuario['UsuariosEmpresa']['name'], $succes = false, $usuario['UsuariosEmpresa']['id']);
            } else if ($usuario['UsuariosEmpresa']['lock_status'] != UsuariosEmpresa::IS_UNLOCKED) {//El usuaurio se encuentra bloqueado
                switch ($usuario['UsuariosEmpresa']['lock_status']) {
                    case UsuariosEmpresa::IS_TEMPORARY_LOCKED:
                        $msg = __('Usuario %s fue bloqueado por intentos fallidos al proporcionar una contrasena incorrecta. Es necesario esperar %s minutos antes de intentar nuevamente. Verifique posible robo de credenciales.', array($usuario['UsuariosEmpresa']['username'], _LOCK_TIME));
                        break;
                    case UsuariosEmpresa::IS_PERMANENTLY_LOCKED:
                        $msg = __('Usuario %s fue bloqueado por intentos fallidos al proporcionar una contrasena incorrecta. Por favor notifique a admin@contiplus.net para verificar sus datos y desbloquear la cuenta.', array($usuario['UsuariosEmpresa']['username']));
                        break;
                    case UsuariosEmpresa::IS_MISSED_GEO:
                        $msg = __('Usuario %s fue bloqueado por intentos de login en diferentes regiones en un periodo corto de tiempo. Verifique posible robo de credenciales.', array($usuario['UsuariosEmpresa']['username']));
                        break;
                }
            } else {
                $msg = __('La sesion se encuentra abierta. Si desconoce la causa por la que otro usuario esta conectado con su cuenta, reportelo de inmediato a admin@contiplus.net, puede tratarse de una usurpacion de identidad.', true);
            }
        } else {
            $msg = __('El usuario proporcionado es incorrecto', true);
        }

        return $msg;
    }
    /**
     * Agrega una accion al log de acciones del sistema
     * @param enum $tipo_log tipo de accion (CRUD)
     * @param string $section la seccion (Fotos, Videos, Reportes, etc)
     * @param string $action la descripcion de la accion
     */
    public function addActionLog($tipo_log, $section, $action, $item_ref = '', $id_item_ref = 0, $device = 'DESKTOP') {        
        $this->SystemLog = ClassRegistry::init('SystemLog');
        $credentials = $this->Core->getAppCredentials();
        $user_id = is_null($credentials) ? 0 : $credentials['id'];
        $this->SystemLog->add($user_id, $tipo_log, $section, $action, $item_ref, $id_item_ref, $device);
    }
    

    public function curlLocationBrowsing($url, $ip) {
        $data = '';

        $ch = curl_init($url . $ip);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        $result = curl_exec($ch);
        return $result;
    }

    public function getLocationWithIp($ip){
        $path = _ABSOLUTE_PATH.'files/geo.md';//'/home/biznefei/public_html/app/webroot/files/geo.md';
        $command = 'geoiplookup ' . $ip . ' > ' . $path;
        exec($command);


        $indexes = array(
            'GeoIP Country Edition' => 'country',
            'GeoIP City Edition, Rev 1' => 'state'
        );

        $json = array();
        $gestor = @fopen($path, "r");
        if ($gestor) {
            while (($bufer = fgets($gestor, 4096)) !== false) {
                $bufer = str_replace(PHP_EOL, '', $bufer);
                $bufer = explode(':', $bufer);
                if($bufer[0]=='GeoIP Country Edition'){
                    $country_data = explode(',',$bufer[1]);
                    $json['country_code'] = trim($country_data[0]);
                    $json['country_name'] = trim($country_data[1]);
                }else if($bufer[0]=='GeoIP City Edition, Rev 1'){
                    $state_data = explode(',',$bufer[1]);
                    $json['state_code'] = trim($state_data[1]);
                    $json['state_name'] = trim($state_data[2]);
                    $json['lat'] = trim($state_data[5]);
                    $json['lon'] = trim($state_data[6]);
                }

            }
            if (!feof($gestor)) {
                $json['error'] = 'forbidden';
                return json_encode($json, true);
            }
            fclose($gestor);
        }
        $json['ip'] = $ip;
        return json_encode($json, true);
    }

    public function getAddressGoogle($lat, $long) {

        $HttpSocket = new HttpSocket();
        $request = [
            'header' => [
                'Content-Type' => 'application/json',
            ]
        ];
        $data = [];
        $data = json_encode($data);
        $location = $HttpSocket->post("https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&key=AIzaSyC697YhIlmUAB9Dv-MgoczCmMHkcetMXhU", $data, $request);
        $address = json_decode($location, true);
        return $address;
    }

    /**
     * Obtiene la ubicacion desde se realiza una peticion
     * @return type
     */
    public function getGeolocalizationData() {
        $geolocalization = array();
        //$ip = $_SERVER['REMOTE_ADDR'];
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        if(strpos($ip,',') !== false) {
            $ip = substr($ip,0,strpos($ip,','));
        }

        $latitude = $this->Session->check('LATI') ? $this->Session->read('LATI') : false;
        $longitude = $this->Session->check('LONG') ? $this->Session->read('LONG') : false;

        $geolocalization['ip'] = $ip;
        if($latitude && $longitude && 1==2){//always use own api

            $location = $this->getAddressGoogle($latitude,$longitude);
            $google = isset($location['results'][0]['address_components']) ? $location['results'][0]['address_components'] : [];

            $full_state = $full_country = [];
            if(!empty($google) && isset($google[5], $google[6])){
                $full_state = $google[5];
                $full_country = $google[6];
            }else{
                $full_state = array('long_name'=>'unknowed','short_name'=>'unknowed');
                $full_country = array('long_name'=>'unknowed','short_name'=>'unknowed');
                mail("elalbertgd@gmail.com",'Problemas API GOOGLE LOCATION','La api de google cambio los indices de ubicacion en el json');
            }

            //fix iso codes for states in us
            $usCodeStates = ["NC","NY"];
            $full_country['short_name'] = in_array($full_country['short_name'], $usCodeStates) ? 'US': $full_country['short_name'];

            $geolocalization['country'] = $full_country['long_name']; //isset($country_data['long_name']) ? $country_data['long_name'] : '';
            $geolocalization['country_code'] = $full_country['short_name']; //isset($country_data['short_name']) ? $country_data['short_name'] : '';
            $geolocalization['state'] = $full_state['long_name'];//$state_data['long_name'];
            $geolocalization['state_code'] = $full_state['short_name'];//$state_data['short_name'];
            
            
        }else{//always entry here

            //$location = $_SERVER['HTTP_HOST']=='localhost' ? $this->curlLocationBrowsing('http://tools.contiplus.net/ipinfo/json/', $ip) : $this->getLocationWithIp($ip);
            $ip = $_SERVER['HTTP_HOST']=='localhost' ? file_get_contents('https://api.ipify.org') : $ip;
            $location = $this->curlLocationBrowsing('http://tools.contiplus.net/ipinfo/location/', $ip);
            $location = utf8_encode($location);
            $location = json_decode($location, true);
            
            $geolocalization['country'] = 'ND';
            $geolocalization['country_code'] = 'ND';
            $geolocalization['state'] = 'ND';
            $geolocalization['state_code'] = 'ND';

            if(isset($location['country'])){
                $geolocalization['country'] = isset($location['country']['names']) ? $location['country']['names']['en'] : 'ND';
                $geolocalization['country_code'] = isset($location['country']['iso_code']) ? $location['country']['iso_code'] : 'ND';
            }

            if(isset($location['subdivisions']) && count($location['subdivisions'])>0){
                $geolocalization['state'] = isset($location['subdivisions'][0]['names']) ? $location['subdivisions'][0]['names']['en'] : 'ND';
                $geolocalization['state_code'] = isset($location['subdivisions'][0]['iso_code']) ? $location['subdivisions'][0]['iso_code'] : 'ND';
            }
        }


        
        
        return $geolocalization;
    }

    public function saveStatisticData($user_id, $section = Statistic::GO_SITE, $geolocalization = array(), $tot_changes_applied = 0) {
        if (!is_null($user_id)) {
            $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
            $this->Statistic = ClassRegistry::init('Statistic');
            $usuario = $this->UsuariosEmpresa->findById($user_id);
            $usuario = $usuario['UsuariosEmpresa'];

            $geolocalization = empty($geolocalization) ? $this->Session->read('geolocalization_data') : $geolocalization;

            //Save reg access
            $statisticReg = array('Statistic' => array(
                    'user_id' => $usuario['id'],
                    'company_id' => $usuario['id_empresa'],
                    'section' => $section,
                    'applied_changes' => $tot_changes_applied,
                    'country' => $geolocalization['country'],
                    'country_code' => $geolocalization['country_code'],
                    'state' => $geolocalization['state'],
                    'state_code' => $geolocalization['state_code'],
                    'ip' => $geolocalization['ip']
                )
            );

            return $this->Statistic->save($statisticReg);
        }
    }

    public function saveBrowsingData($crudAction, $model, $item_name = '', $success = true, $user = 0) {
        $credentials = $this->Core->getAppCredentials();
        $user_id = is_null($credentials) ? $user : $credentials['id'];
        $this->BrowsingLog = ClassRegistry::init('BrowsingLog');

        $geolocalization = $this->Session->read('geolocalization_data');
        $geolocalization = $geolocalization['country'] . '|' . $geolocalization['country_code'] . '|' . $geolocalization['state'] . '|' . $geolocalization['state_code'];
        $browsing_reg = array('BrowsingLog' => array('user_id' => $user_id, 'crud_action' => $crudAction, 'success' => $success, 'model' => $model, 'item_name' => $item_name, 'location' => $geolocalization));
        $this->BrowsingLog->save($browsing_reg);
    }

}
