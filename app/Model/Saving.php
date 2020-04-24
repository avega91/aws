<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Saving.php
 *     Model for savings table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2016
 */

class Saving extends AppModel {

    public $name = 'Saving';
    public $companies = null;

    public function findByIdWithCompany($id) {
        $id = Sanitize::escape($id);
        $query = "
                SELECT Saving.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,Distribuidor.id, Distribuidor.name, SavingFile.path AS saving_file
                FROM savings AS Saving
                INNER JOIN empresas AS Empresa ON Saving.company_id = Empresa.id
                LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                LEFT JOIN saving_files AS SavingFile ON Saving.id = SavingFile.savings_id
                WHERE Saving.id = '$id' AND Saving.deleted = 0
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findAllWithCompany($filter_companies, $report_title = '', $sort = '', $rows = 0, $from = 0) {
        $filter_companies = Sanitize::escape($filter_companies);
        $report_title = Sanitize::escape($report_title);
        $sort = Sanitize::escape($sort);
        $sort = $sort == '' || $sort == 'updated_at' ? 'updated_at DESC' : $sort . ' ASC';
        
        $pagination = $rows>0 ? "LIMIT $rows OFFSET $from":"";
        $query = "
                SELECT Saving.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,Distribuidor.id, Distribuidor.name, SavingFile.path AS saving_file
                FROM savings AS Saving
                INNER JOIN empresas AS Empresa ON Saving.company_id = Empresa.id
                LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                LEFT JOIN saving_files AS SavingFile ON Saving.id = SavingFile.savings_id
                WHERE (Saving.title LIKE '%$report_title%' OR Empresa.name LIKE '%$report_title%') AND company_id IN ($filter_companies)
                      AND Saving.deleted = 0
                ORDER BY Saving.$sort
                $pagination 
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

}
