<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file LockingLog.php
 *     Model for locking_logs table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class LockingLog extends AppModel {
    public $name = 'LockingLog';
    
    public function findAllWithAssocInfo($query = '', $start_date = '', $end_date = ''){
        $query = Sanitize::escape($query);
        $start_date = Sanitize::escape($start_date);
        $end_date = Sanitize::escape($end_date);
        
        $range = $start_date!='' && $end_date!='' ? " AND date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'" : '';
        $range = $query!='' ? $range.= " AND UsuariosEmpresa.name LIKE '%$query%'":$range;
        $query = " 
            SELECT LockingLog.*, UsuariosEmpresa.name, UsuariosEmpresa.id_empresa, UsuariosEmpresa.lock_status
            FROM locking_logs AS LockingLog
            INNER JOIN usuarios_empresas AS UsuariosEmpresa ON LockingLog.user_id = UsuariosEmpresa.id
            WHERE 1=1 $range
            ORDER BY LockingLog.id DESC
            ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
}
