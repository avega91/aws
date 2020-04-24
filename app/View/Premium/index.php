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
 */ ?>

<?php
$viewMinuteManAllow = isset($credentials['permissions'][IElement::Is_MinuteMan]) && in_array('view', $credentials['permissions'][IElement::Is_MinuteMan]['allows']) ? true : false;
$viewContiUniAllow = isset($credentials['permissions'][IElement::Is_ContiUniversity]) && in_array('view', $credentials['permissions'][IElement::Is_ContiUniversity]['allows']) ? true : false;
?>

<div class="title-page premium-section">
    <?php echo __('Servicios Premium', true); ?>
</div>
<div class="disclaimer-pages">
    <div class="alert-box notice closable"><?php echo __('premium_services_on_dev',true); ?></div>
</div>
<div class="full-page">
    <div class="page-menu">
        <ul id="conti_menu" data-section="2" data-intro="<?php echo __('tutorial_servicios_premium',true);?>" data-position="top">
            <!--<li><?php //echo $this->Html->link(__('Rastreo de bandas', true), '#', array('rel' => 'tracking', 'class' => $active_tab == 'tracking' ? 'active' : '')); ?></li>-->
            <li><?php echo $this->Html->link(__('ContiUniversity', true), '#', array('rel' => 'training', 'class' => $active_tab == 'training' ? 'active' : '')); ?></li>
            <li><?php echo $this->Html->link(__('MinuteMan', true), '#', array('rel' => 'minuteman', 'class' => $active_tab == 'minuteman' ? 'active' : '')); ?></li>
        </ul>
    </div>
    <div class="wrapper-content">                        
        <!--<div id="tracking" class="<?php echo $active_tab == 'tracking' ? '' : 'hidden'; ?>">
            <div class="tab-menu-section" id="conveyors_data"></div>
        </div> -->
        <div id="training" class="<?php echo $active_tab == 'training' ? '' : 'hidden'; ?>">
            <p><?php echo __('conti-training-description', true); ?></p>
            <div class="space"></div>
            <div class="center-aligned">
                <?php if($viewContiUniAllow): echo $this->Html->link(__('Visitar ContiUniversity', true), 'http://contiuniversity.contiplus.net/perfil', array('class' => 'link-button', 'target' => '_blank')); endif; ?>
            </div>            
        </div>    
        <div id="minuteman" class="<?php echo $active_tab == 'minuteman' ? '' : 'hidden'; ?>">
            <p><?php echo __('minuteman-description', true); ?></p>
            <div class="space"></div>
            <div class="center-aligned">
                <?php if($viewMinuteManAllow): echo $this->Html->link(__('Visitar MinuteMan', true), 'http://minuteman.veyance.com/', array('class' => 'link-button', 'target' => '_blank')); endif; ?>
            </div>            
        </div>
    </div>
</div>
