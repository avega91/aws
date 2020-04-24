<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file CustomReport.php
 *     Model for custom_reports table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class CustomReport extends AppModel {

    public $name = 'CustomReport';
    public $companies = null;

    public function findAllWithCompany($filter_companies, $report_title = '', $sort = '', $rows = 0, $from = 0) {
        $filter_companies = Sanitize::escape($filter_companies);
        $report_title = Sanitize::escape($report_title);
        $sort = Sanitize::escape($sort);
        $sort = $sort == '' || $sort == 'actualizada' ? 'actualizada DESC' : $sort . ' ASC';
        
        $pagination = $rows>0 ? "LIMIT $rows OFFSET $from":"";
        $query = "
                SELECT CustomReport.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,Distribuidor.id, Distribuidor.name
                FROM custom_reports AS CustomReport
                INNER JOIN empresas AS Empresa ON CustomReport.company_id = Empresa.id
                LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                WHERE (CustomReport.title LIKE '%$report_title%' OR Empresa.name LIKE '%$report_title%') AND company_id IN ($filter_companies)
                      AND CustomReport.eliminada = 0
                ORDER BY CustomReport.$sort
                $pagination 
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

}
