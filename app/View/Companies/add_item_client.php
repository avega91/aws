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
    //$secureConveyorParams = $this->Utilities->encodeParams($conveyor_id);
    $urlSave = null;
    if(count($secure_params)>2){
        $urlSave = $this->Html->url(array('controller' => 'Companies', 'action' => 'saveItemClient', $secure_params[0], $secure_params[1],$secure_params[2], $secure_params[3]));
    }else{
        $urlSave = $this->Html->url(array('controller' => 'Companies', 'action' => 'saveItemClient', $secure_params[0], $secure_params[1]));
    }
?>
    <form id="add_item_conveyor_form" action="<?php echo $urlSave; ?>" class="fancy_form">
        <div class="fancy-content">
            <input type="hidden" id="path_item" name="path_item"/>
            <input type="hidden" name="item_type" value="<?php echo $type_item; ?>"/>
            <?php
            $class_button = "";
            switch ($type_item) {
                case Item::IMAGE:
                    $class_button = 'disabled-btn';
                    ?>                                                        
                    <div id="image_upload_item" class="wrapper-file-upload" title="<?php echo __('Agregar imagen a transportador', true); ?>"></div>                    
                    <div class="full-controls">
                        <input type="text" placeholder="<?php echo __('Titulo imagen', true); ?>" name="item_name" id="item_name" class="validate[required] main-input"/>
                    </div>
                    <div class="space"></div>                    
                    <div class="form-data">
                        <div class="full-controls">
                            <textarea class="item-conveyor-comment validate[required]" id="item_description" name="item_description" placeholder="<?php echo __('Descripcion', true); ?>"></textarea>
                        </div>
                    </div>
                    <?php
                    break;
                case Item::VIDEO:
                    $class_button = 'disabled-btn';
                    ?>

                    <div id="video_upload_item" class="wrapper-file-upload" title="<?php echo __('Agregar video a transportador', true); ?>"></div>
                    <div class="full-controls">
                        <input type="text" placeholder="<?php echo __('Titulo video', true); ?>" name="item_name" id="item_name" class="validate[required] main-input"/>
                    </div>
                    <div class="space"></div>
                    <div class="form-data">
                        <div class="full-controls">
                            <textarea class="item-conveyor-comment validate[required]" id="item_description" name="item_description" placeholder="<?php echo __('Descripcion', true); ?>"></textarea>
                        </div>
                    </div>
                    <div id="disclaimer-video"><?php echo __("supported_files_videos", true); ?></div>
                    <?php
                    break;
                case Item::FOLDER:
                    ?>
                    <div class="full-controls">
                        <input type="text" id="folder_title" placeholder="<?php echo __('Titulo carpeta', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
                        <input type="hidden" name="item_description" id="item_description" value="required_field"/>
                        <input type="hidden" name="path_item" value="required_field"/>                        
                    </div>
                    <div class="space"></div>
                    <?php
                break;
                case Item::REPORT:
                    ?>                    
                    <div class="full-controls">
                        <input type="text" id="report_title" placeholder="<?php echo __('Titulo reporte', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
                    </div>                    
                    <div class="space"></div>
                    <div class="form-data" id="manual_report">                  
                        <div class="full-controls">
                            <textarea name="item_description" id="item_description" value="" class="hidden"></textarea>
                            <textarea class="validate[required]" cols="100" name="wysiwyg_description" id="wysiwyg_description" placeholder="<?php echo __('Descripcion', true); ?>"></textarea>                            
                        </div>                        
                    </div>                    
                    <div class="hidden" id="file_report">
                        <div id="report_upload_item" class="" title="<?php echo __('Cargar pdf de reporte', true); ?>"></div>
                        <div id="name_report_file"></div>
                        <div class="space"></div>
                    </div>
                    <div class="full-controls">
                            <input type="checkbox" id="is_pdf_file"/><label for="is_pdf_file"><?php echo __('Desde PDF', true); ?></label>
                    </div>
                    <?php
                break;
                case Item::NOTE:
                    ?>                    
                    <div class="full-controls">
                        <input type="text" id="note_title" placeholder="<?php echo __('Nota', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
                    </div>                    
                    <div class="space"></div>
                    <div class="form-data" id="manual_report">                  
                        <div class="full-controls">
                            <textarea name="item_description" id="item_description" value="" class="hidden"></textarea>
                            <textarea class="validate[required]" name="wysiwyg_description" id="wysiwyg_description" placeholder="<?php echo __('Agregar nota', true); ?>"></textarea>                            
                        </div>                        
                    </div>                    
                    <?php
                break;
            }
            ?>
        </div>
        <div class="dialog-buttons">
            <a href="#" id="save_item_conveyor" class="link-button ladda-button <?php echo $class_button; ?>" data-style="expand-left" data-size="l"><span class="ladda-label"><?php echo __('Guardar', true); ?></span></a>
            <section>
                <!--<button type="button" id="save_item_conveyor" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>-->
            </section>
        </div> 
    </form>

    <?php
} else {
    echo json_encode($response);
}
