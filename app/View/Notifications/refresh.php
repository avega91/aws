<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file refresh.php
 *     View layer for action refresh of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */


$viewScheduledNotificationAllow = isset($credentials['permissions'][IElement::Is_ScheduledNotifications]) && in_array('view', $credentials['permissions'][IElement::Is_ScheduledNotifications]['allows']) ? true : false;
?>
<div id="notifications_nav">      
    <ul class="notifications-list">
        <li><a class="active" rel="automatic_not" href="#"><?php echo __('Mis notificaciones', true); ?></a></li>
        <?php //if (in_array($credentials['role'], array(UsuariosEmpresa::IS_DIST, UsuariosEmpresa::IS_ADMIN, UsuariosEmpresa::IS_MASTER))) {
            if($viewScheduledNotificationAllow && 1==2) {
        ?>
            <li><a class="" rel="programmed_not" href="#"><?php echo __('Programadas', true); ?></a></li>
        <?php } ?>
    </ul>
</div>
<div id="notifications_container">
    <div id="automatic_not">
        <?php $this->Menu->automatic_notifications($automatic_notifications); ?>
    </div>
    <div id="programmed_not" class="not-visible">
        <?php $this->Menu->programmed_notifications($programmed_notifications); ?>
    </div>
</div>    