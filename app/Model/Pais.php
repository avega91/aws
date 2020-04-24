<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Pais.php
 *     Model for paises table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class Pais extends AppModel {

    public $name = 'Pais';
    public $useTable = false;

    /**
     * Obtiene todos los paises
     * @return mixed
     */
    public function getAll(){
        $query = "
            SELECT *
            FROM paises AS pais
            ORDER BY name ASC
            ";
        $result = $this->query($query);
        return $this->getNumRows()>0 ? $result : false;
    }

}