<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file GeneralController.php
 *     Management of actions for common tasks
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class GeneralController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'Token';        
        if($this->request->is('mobile')){
             $this->Auth->allow('help');
            $this->set('webroot', $this->webroot);
        }
    }

    /**
     * MOBILE Support
     */
    public function support(){
        $this->layout = 'open';
        $this->set('is_mobile', $this->request->is('mobile'));
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if(count($params)>0){
            $lang = $params[0];
            $this->set('language', $lang);
            $this->setJsVar("systemLanguage",$lang);
        }
        $this->jsToInclude[] = 'application/General/support';
        $this->set('jsToInclude', $this->jsToInclude);
    }

    /**
     * help action for help view
     */
    public function help() {
        $this->jsToInclude[] = 'application/General/help';
        $this->set('jsToInclude', $this->jsToInclude);
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        $token = isset($params[0]) ? $params[0]:'-';
        $digest = isset($params[1]) ? $params[1]:'-';
        $signature = $token.'/'.$digest;
        //mail('elalbertgd@gmail.com','params',  print_r($signature, true));
        if (!$this->Session->check(Statistic::GO_HELP)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_HELP);
            $this->Session->write(Statistic::GO_HELP, Statistic::GO_HELP);
        }
        
        $is_mobile = $this->request->is('mobile');
        //$is_mobile = true;
        if($is_mobile){
            //mail("elalbertgd@gmail.com",'mail',print_r($this->request->here,true));
            $this->layout = 'open';
            $this->set('responsive', true);
        }
        
        /**Agregar usuario|add user */
        $role = 'client';
        if(count($params)>=2 && $is_mobile){
            $decodeParams = $this->Core->decodeUserParams($params);        
            $token = $this->Token->findByAuthKey($decodeParams['user_id']);
             if(!empty($token)){
                $userAssoc = $this->UsuariosEmpresa->findById($token['Token']['user_id']);
                $role = !empty($userAssoc) ? $userAssoc['UsuariosEmpresa']['role'] : 'client';
             }
            $lang = isset($params[2]) ? $params[2] : 'es';
            $this->set('language', $lang);
        }

        $this->set('is_mobile', $is_mobile);
        $this->set('signature',$signature);
        $this->set('role',$role);
    }

    /**
     * terms action for terms view
     */
    public function terms() {
        $this->layout = 'open';
        $this->set('is_mobile', $this->request->is('mobile'));
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if(count($params)>0){
            $lang = $params[0];
            $this->set('language', $lang);
            $this->setJsVar("systemLanguage",$lang);
        }
        $this->jsToInclude[] = 'application/General/terms';
        $this->set('jsToInclude', $this->jsToInclude);
    }

    public function eulaTermsLogin(){
        $this->layout = false;
        if ($this->request->is('post')) {
            $params = $this->request->data; //get data
            $this->set('userParams', $params['user']);
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    
     /**
     * terms action for terms view
     */
    public function privacy() {
        $this->layout = 'open';
        $this->set('is_mobile', $this->request->is('mobile'));
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if(count($params)>0){
            $lang = $params[0];
            $this->set('language', $lang);
            $this->setJsVar("systemLanguage",$lang);
        }
        $this->jsToInclude[] = 'application/General/terms';
        $this->set('jsToInclude', $this->jsToInclude);
    }

    public function processContact() {
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data);

            $regionContacto = $this->ContactRegion->findById($data['region_contacto']);
            $regionContacto = $regionContacto['ContactRegion'];

            /*
             * SEND MAIL
             */
            if ($this->Mail->sendContactMsg($regionContacto['email'], $data['notification'])) {
                $response['msg'] = __('Tu mensaje ha sido enviado', true);
                $response['success'] = true;
            } else {
                $response['msg'] = __('Error al enviar mensaje, intentalo nuevamente', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

}
