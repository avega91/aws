<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file get_regions_contact_by_area.php
 *     View layer for action getRegionsContactByArea of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

echo '<option value=""></option>';
if($regions){
    foreach ($regions AS $region){
        $region = $region['ContactRegion'];
        echo '<option value="'.$region['id'].'">'.utf8_encode($region['region']).'</option>';
    }
}