<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file SystemLog.php
 *     Model for system_logs table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::uses('CakeSession', 'Model/Datasource');

class SystemLog extends AppModel {

    public $name = 'SystemLog';
    public $useTable = false;
    public $primaryKey = 'id';
    
    /**
     * Agrega una accion al log del sistema
     * @param int $id_usuario el id del usuario
     * @param enum $tipo_log tipo de accion (CRUD)
     * @param string $section la seccion (Fotos, Videos, Reportes, etc)
     * @param string $action la descripcion de la accion
     * @param int $id_item_reference el id del item al cual hace referencia el log
     * @param string $item_reference el nombre del elemento al cual hace referencia
     * @param string $device el dispositivo desde donde se genero la accion
     * @return mixed
     */
    public function add($id_usuario, $tipo_log, $section, $action, $item_reference = '', $id_item_reference = 0, $device = 'DESKTOP'){
        $id_usuario = Sanitize::escape($id_usuario);
        $tipo_log = Sanitize::escape($tipo_log);
        $section = Sanitize::escape($section);
        $action = Sanitize::escape($action);
        $location = '';
        if(CakeSession::check('geolocalization_data')){
            $location = CakeSession::read('geolocalization_data');
            $location = $location['country'].'|'.$location['state'].'|'.$location['ip'];
        }
        
        $id_item_reference = Sanitize::escape($id_item_reference);
        
        $query = "
            INSERT INTO system_log(id_usuario,tipo_log,section,action,item_ref, id_item_ref, device,location)
            VALUES('$id_usuario','$tipo_log','$section','$action','$item_reference', '$id_item_reference','$device','$location')
            ";
        $this->query($query);
        return $this->getAffectedRows() > 0 ? $this->getLastInsertID() : false;
    }
    
    /**
     * Obtiene todos los registros del log
     * @return type
     */
    public function findAll(){        
        $query = "
            SELECT log.*,u.*
            FROM system_log AS log
            LEFT JOIN users AS u ON log.id_usuario = u.id
            ORDER BY log.id DESC            
            ";
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * Obtiene todos los registros del log pero limitando la cantidad
     * @return type
     */
    public function findAllLimit($start=0, $limit=10, $where = ''){
        $where = Sanitize::escape($where);
        $where = "log.item_ref LIKE '%$where%' OR u.username LIKE '%$where%'";
        $query = "
            SELECT log.*,u.*
            FROM system_log AS log
            LEFT JOIN users AS u ON log.id_usuario = u.id
            WHERE $where
            ORDER BY log.id DESC
            LIMIT $start, $limit
            ";
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * Obtiene todos los registros del log pero limitando la cantidad
     * @param string $interval el intervalo de fechas
     * @return type
     */
    public function findAllLimitInterval($start=0, $limit=10, $interval, $where = ''){
        $where = Sanitize::escape($where);
        $where = "(log.item_ref LIKE '%$where%' OR u.username LIKE '%$where%')";
        
        list($desde, $hasta) = explode(' - ', $interval);        
        $desde = explode('/',$desde);
        $desde = array_reverse($desde);
        $desde = implode('-', $desde);
        
        $hasta = explode('/',$hasta);
        $hasta = array_reverse($hasta);
        $hasta = implode('-', $hasta);
        
        $query = "
            SELECT log.*,u.*
            FROM system_log AS log
            LEFT JOIN users AS u ON log.id_usuario = u.id
            WHERE $where AND log.date BETWEEN '$desde 00:00:00' AND '$hasta 23:59:59' AND log.date != '0000-00-00 00:00:00'
            ORDER BY log.id DESC
            LIMIT $start, $limit
            ";
        //echo $query;
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * Obtiene un registro del log segun su id
     * @param int $id el id del registro
     * @return mixed
     */
    public function getById($id){
         $id = Sanitize::escape($id);
         $query = "
            SELECT log.*,u.*
            FROM system_log AS log
            LEFT JOIN users AS u ON log.id_usuario = u.id
            WHERE log.id = '$id'
         ";
         $result = $this->query($query);
         return count($result)>0 ? $result[0] : false;
    }
}
