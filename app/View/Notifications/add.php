<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add.php
 *     View layer for action add of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<form id="add_notification_form" action="<?php echo $this->Html->url(array('controller' => 'Notifications', 'action' => 'processAdd')); ?>" class="fancy_form">
    <div class="fancy-content">
        <div class="full-controls">
            <?php $this->Content->putDropdownCompanies(__('Distribuidor', true), 'distributor', $distribuidores, $is_disabled = false, $with_label = false); ?>
        </div>
        <div class="full-controls">
            <?php $this->Content->putDropdownCompanies(__('Cliente', true), 'client', array(), $is_disabled = true, $with_label = false); ?>
        </div>
        <div class="two-controls">
            <input type="text" name="fecha" class='validate[required]' readonly="readonly" placeholder="<?php echo __('Fecha'); ?>"/>
            <a class="input-ctrl btn-calendar" id="open_date_link"></a>
        </div>
        <div class="two-controls last-ctrl clockpicker">
            <input type="text" value="00:00" name="hora" class="validate[required]" readonly="readonly">
        </div>
        <div class="space"></div>
        <div class="space"></div>
        <div class="space"></div>
        <div class="full-controls">
            <textarea class="validate[required]" id="notification" name="notification" placeholder="<?php echo __('Mensaje', true); ?>"></textarea>
        </div>
        <div class="space"></div>
        <a href="#" id="send_copy_email"><?php echo __('Enviar copia via email', true); ?></a>
        <div class="full-controls" id="wrapper_mails"></div>
    </div>
    <div class="dialog-buttons normal-dialog-btns">  
        <section>
            <button type="button" id="save_notification" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
        </section>
    </div> 
</form>
