<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file AnalyticsComponent.php
 *     Component to manage common analytics methods
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::import('Vendor', 'Analytics', array('file' => 'Analytics/GoogleAnalyticsAPI.class.php'));

class AnalyticsComponent extends Component {

    private $_ga = null;

    /**
     * Class constructor
     */
    public function __construct(ComponentCollection $collection, array $settings = array()) {
        parent::__construct($collection, $settings);
        $this->_ga = new GoogleAnalyticsAPI('service');
        $this->_ga->auth->setClientId('34926273835-1qdohg0m1p893kmouijknfj97c2qhqdb.apps.googleusercontent.com'); // From the APIs console
        $this->_ga->auth->setEmail('34926273835-1qdohg0m1p893kmouijknfj97c2qhqdb@developer.gserviceaccount.com'); // From the APIs console
        $this->_ga->auth->setPrivateKey(_ABSOLUTE_PATH.'files/private/6664722f872eb728c59f39770a95cc7326e5e91a-privatekey.p12'); // Path to the .p12 file

        $auth = $this->_ga->auth->getAccessToken();
// Try to get the AccessToken
        if ($auth['http_code'] == 200) {
            $accessToken = $auth['access_token'];
            $tokenExpires = $auth['expires_in'];
            $tokenCreated = time();
        } else {
            // error...
        }

        if(_ABSOLUTE_PATH!=''){
            // Set the accessToken and Account-Id
            $this->_ga->setAccessToken($accessToken);
            $this->_ga->setAccountId('ga:71882816');
        }
    }

    public function setDateRange($start_date, $end_date) {
        // Definimos las fechas de inicio y fin de consulta
        $defaults = array(
            //'start-date' => date('Y-m-d', strtotime('-1 month')),
            'start-date' => $start_date,
            'end-date' => $end_date,
        );
        $this->_ga->setDefaultQueryParams($defaults);
    }

    public function getFullVisits() {
        //Obtenemos las visitas del periodo seleccionado
        $params = array('metrics' => 'ga:visits,ga:percentNewSessions,ga:newUsers');
        $data = $this->_ga->query($params);
        $info_ga = array();

        if(isset($data['totalsForAllResults'])){
            $percent_new = round($data['totalsForAllResults']['ga:percentNewSessions'], 2);
            $percent_back = round(100-$data['totalsForAllResults']['ga:percentNewSessions'], 2);
            $info_ga['total'] = array(__('Visitas en el periodo: %s',array($data['totalsForAllResults']['ga:visits'])),$data['totalsForAllResults']['ga:visits'],100);
            $info_ga['nuevos'] = array(__('Nuevos visitantes %s%',array($percent_new)),(int)$data['totalsForAllResults']['ga:newUsers'],$percent_new);
            $info_ga['regresan'] = array(__('Visitantes que regresan %s%',array($percent_back)),$data['totalsForAllResults']['ga:visits']-$data['totalsForAllResults']['ga:newUsers'],$percent_back);
        }
        return $info_ga;
    }

    public function getAvgSessionDuration() {
        $params = array('metrics' => 'ga:avgSessionDuration');
        $data = $this->_ga->query($params);
        $segundos = $data['totalsForAllResults']['ga:avgSessionDuration'];
        
        $minutos = floor($segundos / 60);
        $segundos = $segundos % 60;

        $minutos = $minutos < 10 ? '0'.$minutos : $minutos; 
        $segundos = $segundos < 10 ? '0'.$segundos : $segundos; 
        
        return isset($data['totalsForAllResults']) ? $minutos.':'.$segundos: '00:00';
    }

    public function getTopStatisticsByType($type, $number_rows = 5) {
        $params = array(
            'metrics' => 'ga:visits',
            'dimensions' => 'ga:'.$type,
            'sort' => '-ga:visits',
            'max-results' => $number_rows,
        );
        $data = $this->_ga->query($params);
        $info_ga = array();
        
        if(isset($data['totalsForAllResults'])){
            $total_visits = $data['totalsForAllResults']['ga:visits'];

            foreach ($data['rows'] AS $row_info){
                $percent = round(($row_info[1]/$total_visits)*100,2);
                $row_info[0] = str_replace('Internet Explorer', 'IE', $row_info[0]);
                $info_ga[] = array($row_info[0].' '.$row_info[1], (int)$row_info[1], $percent.'%');
            }
        }
        return $info_ga;
    }
}
