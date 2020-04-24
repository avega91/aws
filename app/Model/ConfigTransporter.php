<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ConfigTransporter.php
 *     Model for config_transporters table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class ConfigTransporter extends AppModel {
    
    const MATERIAL_DESCRIPTION = 'MAT_DESCRIPTION';
    const MATERIAL_DENSITY = 'MAT_DENSITY';
    const BANDWIDTH = 'BANDWIDTH';
    const ROLLER_DIAM_LDC = 'ROLLER_DIAM_LDC';
    const ROLLER_ANGLE_LDC = 'ROLLER_ANGLE_LDC';
    const ROLLER_DIAM_LDR = 'ROLLER_DIAM_LDR';
    const ROLLER_ANGLE_LDR = 'ROLLER_ANGLE_LDR';
    const TENSOR_TYPE = 'TENSOR_TYPE';
    const TIPO_POLEA_MOTRIZ = 'TIPO_POLEA_MOTRIZ';
    const ARCO_CONTACTO = 'ARCO_CONTACTO';    
    
    //Para calculadores
    const ANGULO_CONTACTO = 'CALC_ANGULO_CONTACTO';
    const POLEA_TYPE = 'POLEA_TYPE';
    
    
    const CUBIERTA_DESGASTE = 'COVER_WEAR';
    //Para vida estimada
    const GRADO_MAT_TRANSPORTADO = 'MAT_CONVEYED';
    const CONDICION_ALIMENTACION = 'FEED_CONDITION';
    
    const CONDICION_CARGA= 'LOADING_CONDITION';
    const FRECUENCIA_CARGA= 'LOADING_FRECUENCY';
    const TAMANIO_GRANULAR= 'GRANULAR_SIZE';
    const TIPO_DENSIDAD= 'TYPE_DENSITY';
    const AGRESIVIDAD= 'AGGRESSIVITY';
    
    const PRESENCIA_ACEITE= 'OIL_PRESENCE';
    const UBICACION_BANDA= 'CONVEYOR_LOCATION';
    const TAMANIO_MATERIAL = 'BULK_SIZE';
    
    
    
    public $name = 'ConfigTransporter';
    public $useTable = false;
    public $primaryKey = 'id';

    public function getOneById($id){
        $id = Sanitize::escape($id);
        $query = "
            SELECT *
            FROM config_transporter AS ConfigTransporter
            WHERE id = '$id'
            ORDER BY titulo ASC
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : array();
    }

    /**
     * Obtiene todos los registros segun el campo desc_id
     * @return mixed
     */
    public function getAllByDescIdSorted($desc_id, $sort=""){
        $desc_id = Sanitize::escape($desc_id);
        $order = $sort=="" ? "ORDER BY id ASC":"ORDER BY $sort";
        $query = "
            SELECT *
            FROM config_transporter AS ctransp
            WHERE desc_id = '$desc_id'
            $order
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

    /**
     * Obtiene todos los registros segun el campo desc_id
     * @return mixed
     */
    public function getAllByDescId($desc_id, $byId = false, $typeSort = "ASC"){
        $desc_id = Sanitize::escape($desc_id);
        $order = $byId==true ? "ORDER BY id $typeSort":"ORDER BY titulo ASC";
        $query = "
            SELECT *
            FROM config_transporter AS ctransp
            WHERE desc_id = '$desc_id'
            $order
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }
    
    /**
     * Obtiene todos los valores que sean cadenas
     * @return mixed
     */
    public function getAllStringValues(){
        $query = "
            SELECT *
            FROM config_transporter AS ctransp
            WHERE desc_id = 'MAT_DESCRIPTION' OR desc_id = 'TENSOR_TYPE' OR desc_id = 'POLEA_TYPE' OR desc_id = 'POLEA_TYPE'
            ORDER BY titulo ASC
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

}