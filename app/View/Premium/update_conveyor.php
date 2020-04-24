<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file update_conveyor.php
 *     View layer for action updateConveyor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $secureItemParams = $this->Utilities->encodeParams($conveyor['TrackingConveyor']['id']);
    $urlEdit = $this->Html->url(array('controller' => 'Premium', 'action' => 'processUpdateConveyor', $secureItemParams['item_id'], $secureItemParams['digest']));
    $conveyor = $conveyor['TrackingConveyor'];
    ?>
    <form id="add_tracking_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form">
        <div class="fancy-content">
            <div class="full-controls">
                <input type="text" value="<?php echo $conveyor['title']; ?>" placeholder="<?php echo __('Titulo', true); ?>" name="titulo_transportador" id="titulo_transportador" class="validate[required] main-input"/>
            </div>
            <div class="full-controls">
                <?php $this->Content->putDropdownCompanies(__('Distribuidor', true), 'distributor', $distribuidores, $is_disabled = false); ?>
            </div>
            <div class="full-controls">
                <?php $this->Content->putDropdownCompanies(__('Cliente', true), 'client', array(), $is_disabled = true); ?>
            </div>
            <div class="full-controls">                    
                <div class="label-dropdown"><?php echo __('Codigo de rastreo', true); ?></div>
            </div>
            <div class="full-controls">
                <input type="text" value="<?php echo $conveyor['tracking_code']; ?>" placeholder="<?php echo __('Codigo de rastreo', true); ?>" name="tracking_code" class='validate[required]'/>
            </div>
        </div>
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="save_conveyor" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
            </section>
        </div> 
    </form>
    <?php
} else {
    echo json_encode($response);
}