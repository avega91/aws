<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file SettingsController.php
 *     Management of actions for system settings
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class SettingsController extends AppController {
    public function beforeFilter() {
        parent::beforeFilter();            
    }
    
    /**
     * action for change app language
     * @param string $lang language to set in app
     */
    public function setLang($lang){
        $this->layout = false;
        $is_mobile = $this->request->is('mobile');
        
        //var_dump($this->request->is('get'));
        //var_dump($is_mobile);
        //var_dump(Controller::referer());
        //var_dump($this->request->referer());        
        //die;
        
        if($this->request->is('get')){            
            //$params = $this->request->params;
            //var_dump($params['named']['lang']);
            $this->Session->write(parent::APP_LANGUAGE, $lang);
            $this->redirect(Controller::referer());
        }else{
            $this->redirect(array('controller' => 'Index','action'=>'index'));
        }
    }
   
}
