<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file CronController.php
 *     Management of actions for cron activities
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class CronController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter(); //this allow not registered actions in permissions file, not remove
        $this->autoRender = false;
        $this->layout = false;
    }

    public function test() {
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        $this->autoRender = false;
        mail('elalbertgd@gmail.com', 'test', 'este es un test');
        return;
    }

    public function toggle_logged_in() {
        //Check the action is being invoked by the cron dispatcher 
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        $this->UsuariosEmpresa->updateAll(array('logged_in' => 0), array('logged_in' => 1));
        return;
    }

    public function send_mail_programmed_notifications() {
        //Check the action is being invoked by the cron dispatcher 
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        $notifications = $this->Notification->findReady();
        if (!empty($notifications)) {
            foreach ($notifications AS $notification) {
                $notif_data = $notification['Notification'];
                $empresa = $notification['Empresa'];
                $language = in_array($empresa['region'], array('MX1', 'MX2', 'CENAM')) ? 'es' : 'en';
                if ($this->Mail->sendProgrammedMailNotification($notif_data['mails'], $notif_data['content'], $language)) {
                    $notif_data['sended'] = 1;
                    $notificacion = array('Notification' => $notif_data);
                    $this->Notification->save($notificacion);
                }
            }
        }
    }

    /**
     * Desbloque los usuarios que esten bloqueados temporalmente
     */
    public function unlock_users_in_time() {
        //Check the action is being invoked by the cron dispatcher 
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        $this->UsuariosEmpresa->updateAll(array('lock_status' => "'" . UsuariosEmpresa::IS_UNLOCKED . "'"), array('last_access_attempt + INTERVAL ' . _LOCK_TIME . ' MINUTE <= NOW()', 'lock_status' => UsuariosEmpresa::IS_TEMPORARY_LOCKED));
        return;
    }

    public function remove_unverified_items() {
        //Check the action is being invoked by the cron dispatcher 
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        //no view
        $this->autoRender = false;
        $proceso = $this->Core->remove_unautorized_items();
        mail('elalbertgd@gmail.com', 'test', print_r($proceso, true));
        return;
    }

    public function clear_render_img_cache() {
        //Check the action is being invoked by the cron dispatcher 
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        $proceso = $this->Core->clear_img_cache();
        return;
    }

    public function site_backup() {
        //Check the action is being invoked by the cron dispatcher 


        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        
        //$command = "/usr/bin/mysqldump --opt --user=biznefei_admin --password=s8KKGisZ3hUL biznefei_contiplus_x | gzip > /home/biznefei/public_html/x/backups/dump".time().".sql.gz";
        //$command = "/usr/bin/mysqldump --opt --user=biznefei_admin --password=s8KKGisZ3hUL biznefei_contiplus_x | gzip | uuencode biznefei_contiplus_x_".time().".sql.gz | mailx -s 'Database Backup Contiplus' elalbertgd@gmail.com";
          
        //GOOD CODE
        $command = "/usr/bin/mysqldump --opt --user=biznefei_admin --password=s8KKGisZ3hUL biznefei_contiplus_x | gzip | uuencode biznefei_contiplus_x_" . time() . ".sql.gz | mailx -s 'Database Backup Contiplus' contiplus.net@gmail.com";
        //$command = "/usr/bin/mysqldump --opt --user=biznefei_admin --password=s8KKGisZ3hUL biznefei_contiplus_x | gzip | uuencode biznefei_contiplus_x_" . time() . ".sql.gz | mailx -s 'Database Backup Contiplus' elalbertgd@gmail.com";
        exec($command);

         //$command = "mailx -s 'subject' elalbertgd@gmail.com > /home/biznefei/public_html/x/backups/log.txt 2>&1";
         //$command = "service postfix status > /home/biznefei/public_html/x/backups/log.txt 2>&1";
          //$command = "/usr/bin/mysqldump --opt --user=biznefei_admin --password=s8KKGisZ3hUL biznefei_contiplus_x | gzip > /home/biznefei/public_html/x/backups/dump".time().".sql.gz";
         // exec($command);
          //$command2 = "whereis mail > /home/biznefei/public_html/x/backups/log2.txt 2>&1";
          //exec($command2); */
        return;
    }

    public function updateContiSensorsInfo() {
        //Check the action is being invoked by the cron dispatcher
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        $this->render = false;
        $this->uses[] = "ColorLog";
        $this->uses[] = "FillLevelLog";

        $directory = "uploads/tmp/conti-sensors/";

        //GOOD CODE
        $command = "aws configure set aws_access_key_id AKIAJHTUPUVW2KKERCZQ";
        exec($command);
        $command = "aws configure set aws_secret_access_key 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";
        exec($command);
        $command = "aws configure set region us-east-1";
        exec($command);

        $command = "aws s3 cp s3://conti-sensor/ ".$directory." --recursive";//" > ". _ABSOLUTE_PATH . "uploads/tmp/conti-sensors/log-s3.txt 2>&1";
        exec($command);


        //Checar los items previamente guardados
        $already_saved_logs = $this->ColorLog->find('all',['fields'=>'id']);
        $already_saved_logs = Set::extract('/ColorLog/.', $already_saved_logs );

        $already_saved_fill_level_logs = $this->FillLevelLog->find('all',['fields'=>'id']);
        $already_saved_fill_level_logs = Set::extract('/FillLevelLog/.', $already_saved_fill_level_logs );

        $color_logs = [];
        $fill_level_logs = [];
        //date_default_timezone_set('America/Mexico_City');
        $dir = new Folder($directory);
        $files = $dir->findRecursive('.*');
        foreach ($files as $file) {
            $filex = new File($file);
            $linesFile = array_map('str_getcsv', file($file));
            if(!empty($linesFile)){
                foreach ($linesFile AS $line){
                    $values_line = explode(';',$line[0]);
                    $datetime = DateTime::createFromFormat('y-m-d\TH:i:s+', $values_line[1]);
                    $datetime_read = $datetime->format('Y-m-d H:i:s');
                    $unixtime_read = $datetime->format('U');

                    if($values_line[0]=='colorsensor'){

                        //var_dump($datetime_read.'.'.$unixtime_read);die;

                        $already_saved_row_key = array_search($unixtime_read, array_column($already_saved_logs, 'id'));
                        if($already_saved_row_key===false){//No existe el row
                            $color_logs[] = ["ColorLog"=>[
                                'id' => $unixtime_read,
                                'machine' => $values_line[0],
                                'color_value' => $values_line[2],
                                'color' => $values_line[3],
                                'event' => isset($values_line[4]) ? $values_line[4] : '',
                                'created_at' => $datetime_read,
                            ]];
                        }
                    }else{
                        $already_saved_row_key = array_search($unixtime_read, array_column($already_saved_fill_level_logs, 'id'));
                        if($already_saved_row_key===false){//No existe el row
                            $fill_level_logs[] = ["FillLevelLog"=>[
                                'id' => $unixtime_read,
                                'machine' => $values_line[0],
                                'distance' => $values_line[2],
                                'created_at' => $datetime_read,
                            ]];
                        }
                    }
                }
            }
            //echo "<br><br>";
            // $file->write('I am overwriting the contents of this file');
            // $file->append('I am adding to the bottom of this file.');
            $filex->delete(); // I am deleting this file
            $filex->close(); // Be sure to close the file when you're done
        }

        if (!empty($color_logs)) {
            $this->ColorLog->saveMany($color_logs);
        }

        if (!empty($fill_level_logs)) {
            $this->FillLevelLog->saveMany($fill_level_logs);
        }

        return;
    }

    private function multidimensional_search($parents, $searched) {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if($exists){ return $key; }
        }

        return false;
    }

    public function send_stats_to_conti_person(){

        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        $this->render = false;

        $all_conveyors = $this->Conveyor->getAllWithCompany();
        $client_companies = $this->Empresa->findByTypeWithTeamAndClients(UsuariosEmpresa::IS_CLIENT);
        $dist_companies = $this->Empresa->findByTypeWithTeamAndClients(UsuariosEmpresa::IS_DIST);
        $all_users = $this->UsuariosEmpresa->getTotalUsersByType(array('client', 'client_manager', 'distributor', 'distributor_manager', 'ruber_distributor', 'manager', 'admin', 'region_manager', 'country_manager', 'market_manager'));


        /*$activeConveyors = $this->Conveyor->find('count', array(
            'conditions' => array('Conveyor.eliminada' => 0)
        ));*/
        $activeConveyors = count($all_conveyors);
/*
        $activeClients = $this->Empresa->find('count', array(
            'conditions' => array('Empresa.deleted' => 0, 'Empresa.active' => 1, 'Empresa.type' => 'client')
        ));*/
        $activeClients = count($client_companies);

        /*$activeDist = $this->Empresa->find('count', array(
            'conditions' => array('Empresa.deleted' => 0, 'Empresa.active' => 1, 'Empresa.type' => 'distributor')
        ));*/
        $activeDist = count($dist_companies);

        /*
        $activeUsers = $this->UsuariosEmpresa->find('count', array(
            'conditions' => array('UsuariosEmpresa.deleted' => 0, 'UsuariosEmpresa.active' => 1)
        ));*/
        $activeUsers = count($all_users);


            $this->Mail->sendSecure(["virginie.marty@cbg.contitech.com.mx","elalbertgd@gmail.com"],'Automatic Stats Conti+ '.date('l jS \of F Y'),["active_conveyors"=>$activeConveyors,"active_clients"=>$activeClients,"active_distributors"=>$activeDist, "active_users"=>$activeUsers],'automatic_stats',[]);
    }

    public function check_estimation_life_conveyors(){

        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }
        $this->render = false;
        $this->uses[] = 'UsConveyor';
        $this->uses[] = 'CriticUltrasonicAlert';

        $previousAlerts = $this->CriticUltrasonicAlert->find('all',['order'=>['created_at DESC']]);
        $previousAlerts = Set::extract('/CriticUltrasonicAlert/.', $previousAlerts );

        $conveyors = $this->UsConveyor->findAllWithCompany("");
        $otherCnveyors = $this->Conveyor->findAllWithCompany("");
        $conveyors = array_merge($conveyors, $otherCnveyors);
        $newAlertsToSave = []; //store new alerts
        if (!empty($conveyors)) {
            foreach ($conveyors AS $conveyor) {
                $transportador = $conveyor['Conveyor'];
                $ultrasonic_readings = $conveyor['UltrasonicReading'];
                $has_ultrasonic = !is_null($ultrasonic_readings['ultrasonic_id']);
                if($has_ultrasonic){
                    $abrasionLifeData = $this->Core->calcAbrasionLife($transportador['id']);
                    if(!empty($abrasionLifeData)){
                        //set language depending conveyor type
                        $folder_language = $transportador['is_us_conveyor'] ? 'eng' : 'esp';
                        Configure::write('Config.language', $folder_language);
                        $current_language = $transportador['is_us_conveyor'] ? 'en' : 'es';
                        $this->Core->setAppLanguage($current_language);

                        $type = "";
                        $abrasionLifeData['percent_cover_used'] = 100 - $abrasionLifeData['percent_cover_used']; //get available percent

                        if($abrasionLifeData['percent_cover_used'] <= 30 && $abrasionLifeData['percent_cover_used']>10){
                            $type = "warning";
                        }else if($abrasionLifeData['percent_cover_used'] <= 10){
                            $type = "danger";
                        }
                        if($type!=""){
                            $projected_future_life = $abrasionLifeData['projected_future_life'];
                            if($projected_future_life>11){
                                $years = $projected_future_life / 12;
                                if($years>15) {
                                    $projected_future_life = __("15+ years left", true);
                                }else{
                                    $projected_future_life = __("%s years left", array(number_format($years, 1)));
                                }
                            }else{
                                $projected_future_life = __("%s months left", array($projected_future_life));
                            }


                            $alertSended = $this->multidimensional_search($previousAlerts, array('conveyor_id'=>$transportador['id'], 'type'=>$type));
                            if($alertSended === false){
                                $newAlertsToSave [] = [ 'CriticUltrasonicAlert' => [
                                                            'conveyor_id'=>$transportador['id'],
                                                            'percent_cover_used'=>$abrasionLifeData['percent_cover_used'],
                                                            'projected_future_life'=>$abrasionLifeData['projected_future_life'],
                                                            'type'=>$type
                                                        ]
                                                    ];

                                // Guardamos la notificacion
                                $this->Notifications->alertCriticUltrasonicConveyor($transportador['id'], $projected_future_life);

                            }
                        }

                    }
                }
            }
        }

        if(!empty($newAlertsToSave)){
            $this->CriticUltrasonicAlert->saveMany($newAlertsToSave);
        }
    }

    public function check_months_life_conveyors(){


        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        $this->render = false;
        $this->uses[] = 'UsConveyor';
        $this->uses[] = 'CriticMonthAlert';

        $previousAlerts = $this->CriticMonthAlert->find('all',['order'=>['created_at DESC']]);
        $previousAlerts = Set::extract('/CriticMonthAlert/.', $previousAlerts );

        $conveyors = $this->UsConveyor->findAllWithCompany("");
        $otherCnveyors = $this->Conveyor->findAllWithCompany("");
        $conveyors = array_merge($conveyors, $otherCnveyors);
        $newAlertsToSave = []; //store new alerts
        if (!empty($conveyors)) {
            foreach ($conveyors AS $conveyor) {
                $transportador = $conveyor['Conveyor'];
                $ultrasonic_readings = $conveyor['UltrasonicReading'];
                $has_ultrasonic = !is_null($ultrasonic_readings['ultrasonic_id']);
                if($has_ultrasonic){
                    $abrasionLifeData = $this->Core->calcAbrasionLife($transportador['id']);
                    if(!empty($abrasionLifeData)){
                        //set language depending conveyor type
                        $folder_language = $transportador['is_us_conveyor'] ? 'eng' : 'esp';
                        Configure::write('Config.language', $folder_language);
                        $current_language = $transportador['is_us_conveyor'] ? 'en' : 'es';
                        $this->Core->setAppLanguage($current_language);

                        $type = "";
                        if($abrasionLifeData['projected_future_life'] == 12){
                            $type = "info";
                        }else if($abrasionLifeData['projected_future_life'] == 6){
                            $type = "danger";
                        }else if($abrasionLifeData['projected_future_life'] < 12){
                            $type = "warning";
                        }
                        if($type!=""){
                            $projected_future_life = $abrasionLifeData['projected_future_life'];
                            if($projected_future_life>11){
                                $years = $projected_future_life / 12;
                                if($years>15) {
                                    $projected_future_life = __("15+ years left", true);
                                }else{
                                    $projected_future_life = __("%s years left", array(number_format($years, 1)));
                                }
                            }else{
                                $projected_future_life = __("%s months left", array($projected_future_life));
                            }


                            $alertSended = $this->multidimensional_search($previousAlerts, array('conveyor_id'=>$transportador['id'], 'type'=>$type));
                            if($alertSended === false){
                                $newAlertsToSave [] = [ 'CriticMonthAlert' => [
                                    'conveyor_id'=>$transportador['id'],
                                    'percent_cover_used'=>$abrasionLifeData['percent_cover_used'],
                                    'projected_future_life'=>$abrasionLifeData['projected_future_life'],
                                    'type'=>$type
                                    ]
                                ];

                                // Guardamos la notificacion
                                $this->Notifications->alertCriticUltrasonicConveyor($transportador['id'], $projected_future_life);

                            }
                        }

                    }
                }
            }
        }

        if(!empty($newAlertsToSave)){
            $this->CriticMonthAlert->saveMany($newAlertsToSave);
        }
    }

    public function remove_full_reports() {
        if (!defined('CRON_DISPATCHER')) {
            $this->redirect('/');
            exit();
        }

        //Remove pdf on tmp folder
        $command = "find " . _ABSOLUTE_PATH . "uploads/tmp/ -maxdepth 1 -amin +2 -type f -name '*.pdf' -delete > " . _ABSOLUTE_PATH . "uploads/tmp/log2.txt 2>&1";
        exec($command);

        //Remove ultrasonic xlsx
        $command = "find " . _ABSOLUTE_PATH . "uploads/tmp/ -maxdepth 1 -amin +2 -type f -name '*.xlsx' -delete > " . _ABSOLUTE_PATH . "uploads/tmp/log2.txt 2>&1";
        exec($command);

        //remove pdf on htmlreports
        $command2 = "find " . _ABSOLUTE_PATH . "uploads/htmlreports/ -maxdepth 1 -amin +2 -type f -name '*.pdf' -delete > " . _ABSOLUTE_PATH . "uploads/htmlreports/log2.txt 2>&1";
        exec($command2);

        //remove .html on htmlreports
        $command3 = "find " . _ABSOLUTE_PATH . "uploads/htmlreports/ -maxdepth 1 -amin +2 -type f -name '*.html' -delete > " . _ABSOLUTE_PATH . "uploads/htmlreports/log2.txt 2>&1";
        exec($command3);
    }

    public function create_public_pdf() {        

        /* if (!defined('CRON_DISPATCHER')) {
          $this->redirect('/');
          exit();
          } */

        //used for params in GET format ?param1=x&parms2=y
        //$params = $this->request->query;

        //used por POST params
        $params = $this->request->data;
        //mail("elalbertgd@gmail.com","params",print_r($this->request->data, true));
        $margins = $header = '';
        if (!empty($params) && isset($params['url'])) {
            $url = $params['url'];

            if(isset($params['margins'])){
                $margins = explode(',',$params['margins']);                
                $margins_page = $margins;
                switch(count($margins)){                    
                    case 1:
                        $margins = "-T $margins[0] -R $margins[0] -B $margins[0] -L $margins[0]";
                    break;
                    case 2:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[0] -L $margins[1]";
                    break;
                    case 3:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[2] -L $margins[1]";
                    break;
                    case 4:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[2] -L $margins[3]";
                    break;
                }                
                
            }
            
            if(!$margins){
                $margins = "-T 0 -R 0 -B 0 -L 0";
                $margins_page = $margins;
            }

            $position_header = ($margins_page[0]/2) - 4;
            $header = isset($params['header']) ? "--header-spacing $position_header --header-html $params[header]":"";

            $position_footer = 0;
            $footer = isset($params['footer']) ? "--footer-spacing $position_footer --footer-html $params[footer]":"";
            
            $size = isset($params['s']) && in_array($params['s'], array('A4','Letter','Legal')) ? $params['s'] : 'Letter';
            $orientation = isset($params['o']) && in_array($params['o'], array('Landscape','Portrait')) ? $params['o'] : 'Portrait';
            $page_command = isset($params['page']) && in_array($params['page'], array('right','left','center')) ? '--footer-'.$params['page'].' [page]' : '';
            
            //GENERAMOS EL RESTO DEL ARCHIVO
            $name_file = 'tmp'.time().'.pdf';
            $file = _ABSOLUTE_PATH . "uploads/tmp/$name_file";
            // --print-media-type
            $command = "/usr/local/bin/wkhtmltopdf -s $size -O $orientation $page_command --encoding 'utf-8' $margins --print-media-type $header $footer $url $file > " . _ABSOLUTE_PATH . "uploads/tmp/log_pdf.txt 2>&1";
            exec($command);
            
            //MEZCLAMOS LOS ARCHIVOS
            $file_pdf = _ABSOLUTE_PATH . "uploads/tmp/tmp_file".time().".pdf";
            $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$file_pdf ";
            $cmd .= $file . " ";
            
            $result = exec($cmd);
            
            
            header("Content-type:application/pdf");
            // It will be called downloaded.pdf
            header("Content-Disposition:attachment;filename=$name_file");
            // The PDF source is in original.pdf
            readfile($file_pdf);
            unlink($file_pdf);
            unlink($name_file);
            
        }
    }
    
    public function create_pdf() {        
        
        /* if (!defined('CRON_DISPATCHER')) {
          $this->redirect('/');
          exit();
          } */

        $params = $this->request->query;
        $margins = $header = '';
        if (!empty($params) && isset($params['url'])) {
            $url = $params['url'];            

            $cover = isset($params['cover']) ? "cover $params[cover]":"";
            //$header = '';
            //$cover= '';
            
            if(isset($params['margins'])){
                $margins = explode(',',$params['margins']);                
                $margins_page = $margins;
                switch(count($margins)){                    
                    case 1:
                        $margins = "-T $margins[0] -R $margins[0] -B $margins[0] -L $margins[0]";
                    break;
                    case 2:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[0] -L $margins[1]";
                    break;
                    case 3:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[2] -L $margins[1]";
                    break;
                    case 4:
                        $margins = "-T $margins[0] -R $margins[1] -B $margins[2] -L $margins[3]";
                    break;
                }                
                
                $position_header = $margins_page[0] - 10;
                $header = isset($params['header']) ? "--header-spacing $position_header --header-html $params[header]":"";
            }
            
            if(!$margins){
                $margins = "-T 0 -R 0 -B 0 -L 0";
            }            
            
            $size = isset($params['s']) && in_array($params['s'], array('A4','Letter','Legal')) ? $params['s'] : 'Letter';
            $orientation = isset($params['o']) && in_array($params['o'], array('Landscape','Portrait')) ? $params['o'] : 'Portrait';
            
            
            //$command = "/usr/bin/wkhtmltopdf http://app.micurriculum.pro/ "._ABSOLUTE_PATH."uploads/tmp/test".time().".pdf  > "._ABSOLUTE_PATH."uploads/tmp/log_pdf.txt 2>&1";
            
            //GENERAMOS LA PORTADA
            $margins_cover = "-T 10 -R 10 -B 10 -L 10";
            $name_file_cover = 'tmp_cover'.time().'.pdf';
            $file_cover = _ABSOLUTE_PATH . "uploads/tmp/$name_file_cover";            
            $command = "/usr/local/bin/wkhtmltopdf -s $size -O $orientation $margins_cover $cover $file_cover";
            exec($command);
            
            //GENERAMOS EL RESTO DEL ARCHIVO
            $name_file = 'tmp'.time().'.pdf';
            $file = _ABSOLUTE_PATH . "uploads/tmp/$name_file";
            // --print-media-type
            $command = "/usr/local/bin/wkhtmltopdf -s $size -O $orientation --encoding 'utf-8' $margins --print-media-type $header $url $file > " . _ABSOLUTE_PATH . "uploads/tmp/log_pdf.txt 2>&1";
            exec($command);
            
            
            
            //MEZCLAMOS LOS ARCHIVOS
            $file_pdf = _ABSOLUTE_PATH . "uploads/tmp/tmp_file".time().".pdf";
            $cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$file_pdf ";
            $cmd .= $file_cover . " ";
            $cmd .= $file . " ";
            
            $result = exec($cmd);
            
            
            header("Content-type:application/pdf");
            // It will be called downloaded.pdf
            header("Content-Disposition:attachment;filename=$name_file");
            // The PDF source is in original.pdf
            readfile($file_pdf);
            unlink($file_pdf);
            unlink($name_file);
            unlink($name_file_cover);
            
        }
    }

}
