<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file edit_item.php
 *     View layer for action editItem of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $secureItemParams = $this->Utilities->encodeParams($item_id);
    $urlEdit = $this->Html->url(array('controller' => 'Companies', 'action' => 'processEditItem', $type_item, $secureItemParams['item_id'], $secureItemParams['digest']));
    ?>
    <form id="edit_item_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form">
        <div class="fancy-content">
            <div class="full-controls">
                <input type="text" value="<?php echo $item["ClientItem"]['name']; ?>" placeholder="<?php echo __('Titulo', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
                <textarea name="item_description" id="item_description" value="" class="hidden"></textarea>
            </div>
            <?php
            switch ($type_item) {
                case Item::IS_CLIENT_IMAGE:case Item::IS_CLIENT_VIDEO:
                    ?>     

                    <div class="space"></div>
                    <div class="form-data">                  
                        <div class="full-controls">
                            <textarea class="item-conveyor-comment validate[required]" id="item_description" name="item_description" placeholder="<?php echo __('Descripcion', true); ?>"><?php echo $item["ClientItem"]['description']; ?></textarea>
                        </div>
                    </div>
                    <?php
                    break;
                case Item::IS_CLIENT_NOTE:
                    ?>
                    <div class="space"></div>
                    <div class="form-data" id="note_edit">                  
                        <div class="full-controls">                            
                            <?php
                            $item["ClientItem"]['description'] = stripslashes($item["ClientItem"]['description']);
                            ?>
                            <textarea class="validate[required]" name="wysiwyg_description" id="wysiwyg_description" placeholder="<?php echo __('Agregar nota', true); ?>"><?php echo $item["ClientItem"]['description']; ?></textarea>
                        </div>                        
                    </div>   
                <?php
                break;
                case Item::IS_CLIENT_REPORT: case Item::IS_CLIENT_FOLDER: case Item::IS_CLIENT_FILE:
                    ?>      
                 <input type="hidden" id="rep_fol_title"/>
                    <?php
                    break;
            }
            ?>
        </div>

        <div class="dialog-buttons">  
            <section>
                <button type="button" id="update_item_conveyor" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
            </section>
        </div>
    </form>
    <?php
} else {
    echo json_encode($response);
}