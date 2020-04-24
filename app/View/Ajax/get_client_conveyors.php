<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file get_client_conveyors.php
 *     View layer for action getClientConveyors of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$conveyor_list = '<ul>';
if($conveyors){
    foreach ($conveyors AS $conveyor){
        $conveyor = $conveyor['Conveyor'];
        $conveyor_list .= '<li title="'.utf8_encode($conveyor['numero']).'" id="'.$conveyor['id'].'">'.utf8_encode($conveyor['numero']).'</li>';
    }
}
$conveyor_list .= '</ul>';
echo $conveyor_list;