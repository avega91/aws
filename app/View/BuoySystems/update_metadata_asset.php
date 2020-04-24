<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file update_metadata.php
 *     View layer for action add of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
//var_dump($inst_belt_config);
?>
<style>
    .ui-multiselect-checkboxes{
        background: #f0f0f0 !important;
        border: 1px solid #cdd2cd;
        box-shadow: none;
        padding: 5px;
    }
    .ui-multiselect-checkboxes li.ui-multiselect-optgroup-label{
        text-align: left !important;
        color: #969b96 !important;
        border: none !important;
        margin-top: 10px;
    }
    .ui-multiselect-checkboxes li.ui-multiselect-optgroup-label a{
        color: #969b96 !important;
    }
    .ui-multiselect-optgrp-child label{
        padding: 3px;
        font-size: 13px !important;
        border: none;
    }
    .ui-multiselect-optgrp-child .ui-state-hover{
        color: #FFF !important;
        background: #FF2D37 !important;
        cursor: pointer;
    }
    .ui-multiselect{
        border: none !important;
        border-bottom: 1px solid #e1e4e1 !important;
        color: #ed760a !important;
        font-family: "sansbook" !important;
        font-size: 15px !important;
    }
    #failure_mode_ms{
        width: 100% !important;
    }
    .active_section{
        padding-right: 30px;
    }

    .ui-multiselect-menu{
        width: 200px !important;
    }

    <?php if($credentials["type"]!='pro'): ?>
    .pro-section{
        display: none !important;
    }
    <?php endif;?>

    .image-chosen + div.chosen-container > a.chosen-single span{
        color: #ed760a !important;
    }

    /* Hide first option empty on profile imgs conveyorse*/
    #sel_perfil_chosen .chosen-results > li.active-result:first-child {
        /*display: none !important;*/
    }


    #add_conveyor_form{
        min-width: 450px !important;
    }

    form.fancy_form .conveyor-ctrls .column-label {
        width: 25%;
        margin-right: 2% !important;
    }
    form.fancy_form .conveyor-ctrls .column-ctrl {
        margin-right: 13%;
        position: relative;
    }
    #close_other_manufacturer{
        right: -30px;
    }
</style>

<?php
if ($response['success']) {
$metadata = $assetFolder['AssetMetadata'];
$secureItemParams = $this->Utilities->encodeParams($assetFolder['FolderApp']['id']);
$urlEdit = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'processUpdateMetadataAsset', $secureItemParams['item_id'], $secureItemParams['digest']));

?>
<div id='slide_form' class="iconized">    
    <div>
        <a rel="metadata" class="details active" title="<?php echo __('Metadata %s',$assetFolder['FolderApp']['name']); ?>"></a>
    </div>
</div>
<form id="add_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form">
    <div class='slide-form-section'>        
        <div id="metadata" class="active_section scrollable-tab">
            <div class="slide-form-title"><?php echo __('Metadata %s',$assetFolder['FolderApp']['name']); ?></div>
            <input type="hidden" name="assetId" value="<?php echo $metadata['id']; ?>" class=''/>
            <?php
            $metadataRows = array_map(function ($value, $key) use ($metadataFields){
                if(isset($metadataFields[$key])){
                    $value = is_numeric($value) ?  (float)$value : $value;
                    $label = '<div class="full-controls"><div class="conveyor-label">'.$metadataFields[$key]['label'].'</div></div>';
                    return $label.'<div class="full-controls"><input type="text" value="'.$value.'" name="Metadata['.$metadataFields[$key]['position'].']" id="'.$key.'" class="" /></div>';
                }
            }, $metadata, array_keys($metadata));
            echo implode('',$metadataRows);
            ?>
            

        </div>

    </div>
    <!-- <div id="slide_navigation" class="slide-form-navigation"><a rel="prev"><?php echo __('Anterior',true); ?></a><a rel="next"><?php echo __('Siguiente',true); ?></a></div>-->
    <div class="dialog-buttons">  
        <section>
            <button type="button" id="save_conveyor" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar y Cerrar', true); ?></button>            
        </section>
    </div>
</form>
<?php
} else {
    echo json_encode($response);
}