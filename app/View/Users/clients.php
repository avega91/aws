<?php
/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file clients.php
 *     View layer for action add of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */ ?>
<div class="title-page clients-section">    
    <?php echo __('Customers',true); ?>    
</div>
<div class="full-page">    
    <?php $this->Content->printGraphicCompanies($client_companies, Group::CLIENT); ?>
</div>
