<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Estado.php
 *     Model for estados table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class Estado extends AppModel {

    public $name = 'Estado';
    public $useTable = false;


    /**
     * Obtiene todos los estados que corresponden al pais recibido como parametro
     * @param int $id_country el id del pais
     * @return array
     */
    public function getStatesForCountry($id_country){
        $query = "
            SELECT *
            FROM estados AS estado
            WHERE relacion = '$id_country'
            ORDER BY name ASC
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }
    
    
    /**
     * Obtiene todos los estados
     * @return mixed
     */
    public function getAll(){
        $query = "
            SELECT *
            FROM estados AS estado
            ORDER BY id ASC
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

}

?>
