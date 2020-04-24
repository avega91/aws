<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file SettingMsg.php
 *     Model for settings_msgs table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class SettingMsg extends AppModel {
    public $name = 'SettingMsg';   
    public $useTable = false;
    
    
    /**
     * Obtiene el mensaje de la seccion Home de cada panel
     * @return mixed array|bool
     */
    public function get_main_msg(){
        $query = "
            SELECT *
            FROM settings_msgs AS data
            WHERE desc_id = 'home_main_msg' 
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result[0]['data'] : false;
    }
    
     /**
     * Obtiene un mensaje de los de home del sistema
     * @return mixed array|bool
     */
    public function get_setting_msg($type='home_main_new'){
        $query = "
            SELECT *
            FROM settings_msgs AS data
            WHERE desc_id = '$type' 
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result[0]['data'] : false;
    }
    
    /**
     * Actualiza el mensaje que aparece en todos los Home de cada panel
     * @param string $titulo el titulo del mensaje
     * @param string $descripcion la descripcion del mensaje
     * @return bool
     */
    public function update_main_msg($titulo, $descripcion){
        $titulo = Sanitize::escape($titulo);
        $descripcion = Sanitize::escape($descripcion);
        $query = "
            UPDATE settings_msgs SET titulo = '$titulo', descripcion = '$descripcion'
            WHERE desc_id = 'home_main_msg' 
            ";
        $this->query($query);
        return $this->getAffectedRows()>0 ? true : false;
    }
    
    /**
     * Actualiza el mensaje que aparece en todos los Home de cada panel
     * @param string $msg_id el id del mensaje 
     * @param string $titulo el titulo del mensaje
     * @param string $descripcion la descripcion del mensaje
     * @return bool
     */
    public function update_main_msg_by_msg_id($msg_id, $titulo, $descripcion){
        $titulo = Sanitize::escape($titulo);
        $descripcion = Sanitize::escape($descripcion);
        $query = "
            UPDATE settings_msgs SET titulo = '$titulo', descripcion = '$descripcion'
            WHERE desc_id = '$msg_id' 
            ";        
        $this->query($query);
        return $this->getAffectedRows()>0 ? true : false;
    }
}
