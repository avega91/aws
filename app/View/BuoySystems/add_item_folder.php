<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add_item_conveyor.php
 *     View layer for action addItemConveyor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $urlSave = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'saveItemFileFolder', $secure_params[0], $secure_params[1]));
?>
    <form id="add_item_conveyor_form" action="<?php echo $urlSave; ?>" class="fancy_form">
        <div class="fancy-content">
            <input type="hidden" id="path_item" name="path_item"/>
            <input type="hidden" name="item_type" value="<?php echo $type_item; ?>"/>
            <div class="full-controls">
                <input type="text" id="folder_title" placeholder="<?php echo __('Titulo carpeta', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
                <input type="hidden" name="item_description" id="item_description" value="required_field"/>
                <input type="hidden" name="path_item" value="required_field"/>                        
            </div>
            <div class="space"></div>
        </div>
        <div class="dialog-buttons">
            <a href="#" id="save_item_conveyor" class="link-button ladda-button" data-style="expand-left" data-size="l"><span class="ladda-label"><?php echo __('Guardar', true); ?></span></a>
            <section>
            </section>
        </div> 
    </form>

    <?php
} else {
    echo json_encode($response);
}
