<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file EstadoRegion.php
 *     Model for estados_region table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class EstadoRegion extends AppModel {

    public $name = 'EstadoRegion';
    public $useTable = false;
    
    
    public function findData($name_state){
        //return $this->find('first', array('conditions' => array('name LIKE' => "%$name_state%")));             
        return $this->query("SELECT * FROM estados_region AS EstadoRegion WHERE asociated_state LIKE '%$name_state%' AND asociated_country_id = '156' LIMIT 1");
    }
    /**
     * Obtiene todos los estados que corresponden al pais recibido como parametro
     * @param string $region el string id de la region
     * @return array
     */
    public function getStatesForRegionId($region){
        $region = Sanitize::escape($region);
        $region_filter = "";
        if($region!=""){
            $region_received = explode(",", $region);//separate items
            $region_separated = "'".implode("','", $region_received)."'";
            $region_filter = "identificator_region IN ($region_separated)";
        }
        $query = "
            SELECT *
            FROM estados_region AS estado_pais
            INNER JOIN paises AS pais ON pais.id = estado_pais.asociated_country_id
            WHERE $region_filter
            ORDER BY asociated_state ASC
            ";
        //WHERE identificator_region = '$region'
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

    /**
     * Obtiene todas las regiones disponibles
     * @return mixed
     */
    public function getAll(){
        $query = "
            SELECT *
            FROM estados_region AS region
            ORDER BY id
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

}

?>
