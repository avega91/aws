<?php /*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file all.php
 *     View layer for action all of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */ 
 
$active_tab = $active_tab[strlen($active_tab) - 1]=='s' ? substr($active_tab, 0, -1) : $active_tab;
?>
<style>
    .wrapper-content{
        padding-bottom: 150px !important;
    }
    .company-name-accord{
        min-width: 250px;
    }
    #edit_corporate_form{
        width: 480px;
        height: 100px;
    }
</style>
<div class="title-page advanced-section">
    <?php echo __('Admin settings', true); ?>
</div>
<div class="full-page">
    <div class="space"></div>
    <div class="space"></div>
    <div class="page-menu">
        <ul id="conti_menu" data-section="13" data-intro="<?php echo __('tutorial_grupo_usuarios',true);?>" data-position="top">

            <?php if($credentials['group_id']>Group::MANAGER){ ?>
            <li>
                <?php echo $this->Html->link(__('OEM', true), '#', array('rel' => 'distributor', 'class' => $active_tab == 'distributor' ? 'active':'')); ?>
                <?php if($credentials['i_group_id']>IGroup::DISTRIBUTOR): ?>
                <button class="trigger-action-li more add-company-link" rel="<?php echo $this->Html->url(array('controller' => 'Companies', 'action' => 'append', IGroup::DISTRIBUTOR)); ?>"></button>
                <?php endif; ?>
            </li>
            <?php } ?>
            <li>
                <?php echo $this->Html->link(__('Clientes', true), '#', array('rel' => 'client', 'class' => $active_tab == 'client' ? 'active':'')); ?>
                <?php if($credentials['i_group_id']>IGroup::CLIENT): ?>
                <button class="trigger-action-li more add-company-link" rel="<?php echo $this->Html->url(array('controller' => 'Companies', 'action' => 'append', IGroup::CLIENT)); ?>"></button>
                <?php endif; ?>
            </li>
            <?php if($credentials['i_group_id']>IGroup::TERRITORY_MANAGER): ?>
            <li><?php echo $this->Html->link(__('Seguridad', true), '#', array('rel' => 'security', 'class' => $active_tab == 'security' ? 'active' : '')); ?></li>
            <li><?php echo $this->Html->link(__('Activity', true), '#', array('rel' => 'history', 'class' => $active_tab == 'history' ? 'active' : '')); ?></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="wrapper-content" data-section="13" data-intro="<?php echo __('tutorial_listado_empresas_usuarios',true);?>" data-position="top">
        <?php if($credentials['i_group_id']>IGroup::TERRITORY_MANAGER): ?>
        <div id="securitys" class="<?php echo $active_tab == 'security' ? '' : 'hidden'; ?>">
            <?php $this->Content->printLockingLog($locking_log); ?>
        </div>
        <div id="historys" class="<?php echo $active_tab == 'history' ? '' : 'hidden'; ?>">
            <?php $this->Content->printBrowsingLog($browsing_log); ?>
        </div>
        <?php endif; ?>
        
        <div id="distributors" class="<?php echo $active_tab == 'distributor' ? '':'hidden';?>">
            <?php $this->Content->printCompanies($dist_companies, Group::DISTRIBUTOR); ?>
        </div>
        <div id="clients" class="<?php echo $active_tab == 'client' ? '':'hidden';?>">
            <?php $this->Content->printCompanies($client_companies, Group::CLIENT); ?>
        </div>
        
    </div>
</div>