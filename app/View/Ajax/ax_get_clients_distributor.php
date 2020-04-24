<?php
/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ax_get_clients_distributor.php
 *     View layer for action ax_get_clients_distributor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$companies = '<option value=""></option>';
if(!empty($clients)){
    foreach($clients AS $client){
        $client = $client['Empresa'];
        $companies .= '<option value="'.$client['id'].'">'.$client['name'].'</option>';
    }
}
echo $companies;

        
        