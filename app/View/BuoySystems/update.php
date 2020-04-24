<style>
    .active_section {
        padding-right: 30px;
    }
    .image-chosen + div.chosen-container > a.chosen-single span{
        color: #ed760a !important;
    }
    /* Hide first option empty on profile imgs conveyorse*/
    #sel_perfil_chosen .chosen-results > li.active-result:first-child {
        /*display: none !important;*/
    }


    #add_conveyor_form{
        min-width: 400px !important;
        min-height: 500px !important;
    }
    #logo_transportador{
        right: inherit !important;
        top: 40px;
        left: 70px;
    }

    form.fancy_form .conveyor-ctrls .column-label {
        width: 25%;
        margin-right: 2% !important;
    }
    form.fancy_form .conveyor-ctrls .column-ctrl {
        margin-right: 13%;
        position: relative;
    }


    .column-ctrl button.ms-choice{
        background-color: transparent !important;
        border: none;
        border-bottom: 1px solid #e1e4e1;
    }

    .column-ctrl button.ms-choice > span{
        color: #ed760a !important;
        font-family: "sansbook" !important;
        font-size: 15px !important;
    }
    .column-ctrl .ms-drop{
        width: 130% !important;
    }
    .column-ctrl .ms-drop li{
        color: #707571;
        font-family: "sanslight";
        padding: 5px 0 5px;
        font-size: 13px;
        padding: 0;
    }
    .column-ctrl .ms-drop li:hover{
        color: #FFF;
    }
    .column-ctrl .ms-drop ul > li label{
        white-space: normal !important;
    }


   .close-stick.hidden + .unit-indicator{
       display: none;
   }
</style>

<?php
if ($response['success']) {
    $secureItemParams = $this->Utilities->encodeParams($folderApp['FolderApp']['id']);
    $urlEdit = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'processUpdate', $secureItemParams['item_id'], $secureItemParams['digest']));
    

    $path_img_empresa = '';
    //if (file_exists($empresa['path_image'])) {
    if (!is_null($folderApp['FolderApp']['image'])) {
        $image_name = 'buoysystemimage'.$folderApp['FolderApp']['id'];
        $path_to_save_image = _ABSOLUTE_PATH.'uploads/tmpfiles/'.$image_name.'.jpg';
        @unlink($path_to_save_image);//Eliminamos imagenes previas

        $path_image = 'uploads/tmpfiles/'.$image_name.'.jpg';//
        file_put_contents($path_to_save_image, $folderApp['FolderApp']['image']);//Escribimos una nueva imagen para poder desplegarla
        $folderApp['FolderApp']['cover_img'] = $path_image;
        $path_imagen_portada = $folderApp['FolderApp']['cover_img'];
    }
    
    ?>
    <form id="add_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form">
        <input type="hidden" value="<?php echo $path_imagen_portada; ?>" id="reference_bg_uploader"/>
        <input type="hidden" id="path_logo_transportador" name="path_logo_transportador"/>
        <div class='slide-form-section'>        
            <div id="detalle" class="active_section">
                <div id="logo_transportador" title="<?php echo __('Clic to add/change cover image', true); ?>"></div>
                <div class="space"></div>
                <div class="full-controls">
                    <input type="text" value="<?php echo $folderApp['FolderApp']['name']; ?>" placeholder="<?php echo __('Add title', true); ?>" name="no_transportador" id="no_transportador" class="validate[required] main-input"/>
                </div>
                <div class="form-data">     
                    <div>           
                        <div class="full-controls">
                            <?php $this->Content->putDropdownCompanies(__('Choose Distributor', true), 'distributor', $distribuidores, $is_disabled = false); ?>
                        </div>
                        <div class="full-controls">
                            <?php $this->Content->putDropdownCompanies(__('Choose Client', true), 'client', array(), $is_disabled = true); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="save_conveyor" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
            </section>
        </div> 
    </form>
    <?php
} else {
    echo json_encode($response);
}