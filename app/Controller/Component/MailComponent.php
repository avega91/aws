<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file MailComponent.php
 *     Component to manage common mail system methods
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::uses('CakeEmail', 'Network/Email');
class MailComponent extends Component {

    var $components = array('Core','FireBase'); // the other component your component uses
    private $_credentials = null;

    /**
     * Class constructor
     */
    public function __construct(ComponentCollection $collection, array $settings = array()) {
        parent::__construct($collection, $settings);
    }

    public function sendContactMsg($to, $msg) {
        //$to = 'ieialbertogd@gmail.com';
        $credentials = $this->Core->getAppCredentials();
        $language = $this->Core->_app_language;
        $subject = __('Nuevo mensaje de contacto de parte de: ', true) . $credentials['name_company'];

        $html_message = file_get_contents('files/mail_templates/contacto_' . $language . '.html');
        $html_message = str_replace('{usuario}', $credentials['name'], $html_message);
        $html_message = str_replace('{empresa}', $credentials['name_company'], $html_message);
        $html_message = str_replace('{msg_text}', $msg, $html_message);

        $headers = 'From: ' . $credentials['name'] . ' <' . $credentials['email'] . '>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        if (($sended = mail($to, $subject, $html_message, $headers))) {
            $html_message = file_get_contents('files/mail_templates/confirmacion_' . $language . '.html');
            $html_message = str_replace('{name_user}', $credentials['name'], $html_message);

            $subject = __('Confirmacion automatica. Contacto Contiplus', true);
            $headers = 'From: No-Reply Contiplus <no-reply@contiplus.net>' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            mail($credentials['email'], $subject, $html_message, $headers);
        }

        return $sended;
    }

    public function sendAccessUserData($user_id, $password) {
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $usuario = $this->UsuariosEmpresa->findById($user_id);
        $usuario = $usuario['UsuariosEmpresa'];

        $language = in_array($usuario['region'], array('MX1', 'MX2', 'CENAM')) ? IS_ESPANIOL : IS_ENGLISH;

        $subject = in_array($usuario['region'], array('MX1', 'MX2', 'CENAM')) ? 'Bienvenido a Contiplus':'Welcome to Contiplus';

        $html_message = file_get_contents('files/mail_templates/bienvenido_' . $language . '.html');
        $html_message = str_replace('{name_user}', $usuario['name'], $html_message);
        $html_message = str_replace('{username}', $usuario['username'], $html_message);
        $html_message = str_replace('{password}', $password, $html_message);

        $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

        return $this->sendSecure([$usuario['email']],$subject,['name_user'=>$usuario['name'],'username'=>$usuario['username'],'password'=>$password],'bienvenido_'.$language);
        //return mail($usuario['email'], $subject, $html_message, $headers);
    }

    public function sendSavingsReport($savings_id, $auth_link = '') {

        $this->Saving = ClassRegistry::init('Saving');
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $savingsRow = $this->Saving->findByIdWithCompany($savings_id);
        $subject = 'Savings Report';

        $usuarioCreador = $this->UsuariosEmpresa->findById($savingsRow[0]['Saving']['user_logged']);

        $html_message = file_get_contents('files/mail_templates/savings_en.html');
        $html_message = str_replace('{name_user}', $savingsRow[0]['Empresa']['name'], $html_message);
        $html_message = str_replace('{link_approvation}', $auth_link, $html_message);

        $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        return mail($usuarioCreador['UsuariosEmpresa']['email'], $subject, $html_message, $headers);
    }

    public function sendSecure($dest = array(), $subject = "", $dinamicData = array(), $template="", $atachments = array()){
        $sended = false;
        if($template!=""){
            $Email = new CakeEmail('gmail');
            if (!empty($dest)) {

                if(count($dest)>1){//si trae mas de 1 correo
                    if($dest[0]!='00.list@contiplus.net'){//no viene el correo generico
                        array_unshift($dest, "00.list@contiplus.net");
                    }
                }
                $main_recipient = array_shift($dest);//get the first item from mails dest array

                $Email->to([$main_recipient]);
                if(!empty($dest)){ //if dest have more items, send like bcc type
                    $Email->bcc($dest);
                }
                $Email->subject($subject);
                $Email->sendAs = 'html';
                $Email->from('no-reply@contiplus.net', 'Contiplus');
                $Email->sender('no-reply@contiplus.net', 'Contiplus');
                $Email->emailFormat('html');
                $Email->template($template,null);
                $Email->viewVars($dinamicData);

                if (!empty($atachments)) {
                    $files = array();
                    foreach ($atachments AS $atachment) {
                        $path = parse_url($atachment, PHP_URL_PATH);
                       // $path = substr($path, 1);
                        $files[] = $path;
                    }
                    $Email->attachments($files);
                }

                if($Email->send()){
                    $sended = true;
                }
            }
        }
        return $sended;
    }

    public function sendPushNotification($usersToSend, $notification) {

        $subject = __('Notificacion Contiplus', true);

        $userIdsToPush = [];
        if (!empty($usersToSend)) {
            foreach ($usersToSend AS $user) {
                $user = $user['UsuariosEmpresa'];
                if($user['deleted']==0){
                    $userIdsToPush[] = $user['id'];
                }
            }
        }

        //TO SEND PUSH MESSAGES TROUGH FIREBASE
        if(!empty($userIdsToPush)){
            $this->FireBase->push($subject,$notification,$userIdsToPush);
        }
    }

    public function sendMailNotification($usersToSend, $notification) {
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $language = $this->Core->_app_language;
        
        $to = '';
        $bcc = '';
        $subject = __('Notificacion Contiplus', true);
        $all_mails_to_send = [];
        $all_mails_to_send[] = "00.list@contiplus.net";

        if (!empty($usersToSend)) {
            foreach ($usersToSend AS $user) {
                $user = $user['UsuariosEmpresa'];
                if($user['accept_mail_notif']==_ACCEPT_MAIL_NOTIFICATIONS && $user['deleted']==0){
                    $all_mails_to_send[] = $user['email'];
                     if($to==''){
                        $to = $user['email'];
                    }else{
                        $bcc .= $user['email'].',';
                    }
                }
            }
            //quitamos la coma final
            $bcc = $bcc!='' ? 'Bcc: '.substr($bcc, 0, -1) . "\r\n" : $bcc;           
        }

        /*if($to!=''){

            $html_message = file_get_contents('files/mail_templates/notificacion_' . $language . '.html');
            $html_message = str_replace('{notification_text}', $notification, $html_message);
            $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= $bcc;

            return mail($to, $subject, $html_message, $headers);

            $this->Mail->sendSecure($all_mails_to_send,$subject,['notification_text'=>$notification],'notificacion_'.$language);
        }*/

        //mail("elalbertgd@gmail.com",'emails',print_r($all_mails_to_send,true));
        if(!empty($all_mails_to_send)){
            return $this->sendSecure($all_mails_to_send,$subject,['notification_text'=>$notification],'notificacion_'.$language);
        }

    }
    
    public function sendProgrammedMailNotification($mailsToSend, $notification, $language) {
        $to = '';
        $bcc = '';
        $mailsToSend = explode(',', $mailsToSend);
        if (!empty($mailsToSend)) {
            foreach ($mailsToSend AS $mail) {
                    if($to==''){
                        $to = $mail;
                    }else{
                        $bcc .= $mail.',';
                    }
            }
            //quitamos la coma final
            $bcc = $bcc!='' ? 'Bcc: '.substr($bcc, 0, -1) . "\r\n" : $bcc;           
        }
        
        if($to!=''){
            $subject = $language == 'es' ? 'Notificación contiplus':'Contiplus notificacion';
            
            $html_message = file_get_contents(_ABSOLUTE_PATH.'files/mail_templates/notificacion_' . $language . '.html');
            $html_message = str_replace('{notification_text}', $notification, $html_message);
            $headers = 'From: Contiplus <no-reply@contiplus.net>' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
            $headers .= $bcc;
            
            return mail($to, $subject, $html_message, $headers);
        }
    }

}
