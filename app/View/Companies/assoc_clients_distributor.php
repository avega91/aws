<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file assoc_clients_distributor
 *     View layer for action assoc_clients_distributor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<form id="assoc_clients_distributor_form" action="<?php echo $this->Html->url(array('controller' => 'Companies', 'action' => 'processAssocClientsDistributor')); ?>" class="fancy_form">
    <div class="center-aligned"><?php echo __('Seleccione el distribuidor al que asociara los clientes', true); ?></div>
    <input type="hidden" id="deleted_distributor" name="deleted_distributor" value="<?php echo $deleted_dist; ?>"/>
    <div class="space"></div>
    <div class="fancy-content">            
        <div class="full-controls">
            <?php $this->Content->putDropdownCompanies(__('Distribuidor', true), 'distributor', $distribuidores, $is_disabled = false, $with_label = false); ?>
        </div>
    </div>
    <div class="dialog-buttons">  
        <section>
            <button type="button" id="process_assoc_clients" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
        </section>
    </div> 
</form>