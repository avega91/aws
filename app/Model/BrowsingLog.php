<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file BrowsingLog.php
 *     Model for browsing_logs table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class BrowsingLog extends AppModel {

    public $name = 'BrowsingLog';

    const ACCESO = 'ACCESS';
    const ALTA = 'CREATE';
    const LECTURA = 'READ';
    const ACTUALIZACION = 'UPDATE';
    const ELIMINACION = 'DELETE';
    const FROM_DESKTOP = 'DESKTOP';
    const FROM_MOBILE = 'MOBILE';

    public function findAllWithAssocInfo($query = '', $start_date = '', $end_date = '') {
        $query = Sanitize::escape($query);
        $start_date = Sanitize::escape($start_date);
        $end_date = Sanitize::escape($end_date);
        
        $range = $start_date!='' && $end_date!='' ? " AND date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'" : '';
        $range = $query!='' ? $range.= " AND UsuariosEmpresa.name LIKE '%$query%'":$range;
        
        $query = " 
            SELECT BrowsingLog.*, UsuariosEmpresa.name, UsuariosEmpresa.id_empresa, UsuariosEmpresa.lock_status
            FROM browsing_logs AS BrowsingLog
            INNER JOIN usuarios_empresas AS UsuariosEmpresa ON BrowsingLog.user_id = UsuariosEmpresa.id
            WHERE 1=1 $range
            ORDER BY BrowsingLog.date DESC
            ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    
    public function getActivityCompanies() {
        $query = "
            SELECT * FROM (SELECT UsuariosEmpresa.name, Empresa.id AS company_id, Empresa.name AS company,
            BrowsingLog.user_id,
            SUM(CASE WHEN BrowsingLog.user_id IS NOT NULL THEN 1 ELSE 0 END) AS activity
            FROM empresas AS Empresa
            INNER JOIN usuarios_empresas AS UsuariosEmpresa ON Empresa.id = UsuariosEmpresa.id_empresa
            LEFT JOIN browsing_logs AS BrowsingLog ON UsuariosEmpresa.id = BrowsingLog.user_id
            WHERE Empresa.type IN('client','distributor','admin','master')
            GROUP BY Empresa.id
            ORDER BY activity DESC, Empresa.name ASC) AS ActivityCompanies
         ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    

}
