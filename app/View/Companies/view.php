<?php /*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file view.php
 *     View layer for action view of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

?>
<style>
    /*#filter_failures + div.filter-topbar >*/
    div.ms-drop{
        width: 200px !important;
    }
    div.ms-drop ul{
        overflow-x: hidden !important;
    }
    .ms-drop ul > li label{
        white-space: normal;
    }
    .info-section .image-info{
        height: 180px !important;
    }
</style>
<div class="title-page clients-section" title="<?php echo $empresa['name']; ?>">
    <?php echo $empresa['name']; ?>
</div>
<div class="breacrum-page">
    <?php 
        $client_link = $credentials['i_group_id'] > IGroup::CLIENT_MANAGER ? $this->Html->link(__('Customers', true), ['controller'=>'/','action'=>'customers']) : '<span class="tail">'.__('Customers', true).'</span>';
        echo $client_link;
    ?>
    <span class="separator">/</span> 
    <span class="tail"><?php echo $empresa['name']; ?></span>
</div>


<div class="full-page" id="buoys_data">
</div>

