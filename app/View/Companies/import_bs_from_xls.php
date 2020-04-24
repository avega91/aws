<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add_file_conveyor.php
 *     View layer for action addFileConveyor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<style>
#add_file_conveyor_form{    
    width: 350px;
    height: 180px;
}
#name_file{
    text-align: center;
}
</style>
<?php
if ($response['success']) {
    $urlSave = $this->Html->url(array('controller' => 'Companies', 'action' => 'processImportExcel', $secure_params[0], $secure_params[1]));
?>
    <form id="add_file_conveyor_form" action="<?php echo $urlSave; ?>" class="fancy_form">
        <div class="fancy-content">
            <input type="hidden" id="path_item" name="path_item"/>
            <div class="full-controls hidden">
                  <input type="text" placeholder="<?php echo __('Titulo de archivo', true); ?>" name="item_name" id="item_name" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
            </div>                    
             <div class="space"></div>
            <div id="file_report">
                <div class="space"></div>
                <div id="file_upload_item" class="" title="<?php echo __('Cargar archivo', true); ?>"></div>
                <div id="name_file" class=""></div>
                <div class="space"></div>
            </div>
        </div>
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="save_file_conveyor" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Subir archivo', true); ?></button>            
            </section>
        </div> 
    </form>

    <?php
} else {
    echo json_encode($response);
}
