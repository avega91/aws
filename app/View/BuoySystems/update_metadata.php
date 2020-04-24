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
        min-width: 850px !important;
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
$metadataBS = $folderApp['BsMetadata'];
$secureItemParams = $this->Utilities->encodeParams($folderApp['FolderApp']['id']);
$urlEdit = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'processUpdateMetadata', $secureItemParams['item_id'], $secureItemParams['digest']));

?>
<div id='slide_form' class="iconized">    
    <div>
        <a rel="metadata" class="details active" title="<?php echo __('Metadata Buoy Project',true); ?>"></a>
    </div>
</div>
<form id="add_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form is-us-form">
    <div class='slide-form-section'>        
        <div id="metadata" class="active_section scrollable-tab">
            <div class="slide-form-title"><?php echo __('Metadata Buoy Project',true); ?></div>
            <input type="hidden" name="Metadata[0]" value="<?php echo $folderApp['FolderApp']['name']; ?>" class=''/>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Project number',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[1]" value="<?php echo $metadataBS['project_number']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of products',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[26]" value="<?php echo (float)$metadataBS['number_products']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Engineering name',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[2]" value="<?php echo $metadataBS['engineering_name']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Anchor weight (tons)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[27]" value="<?php echo (float)$metadataBS['anchor_weight']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('SB relative number',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[3]" value="<?php echo (float)$metadataBS['sb_relative_numbers']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Model tests',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[28]" value="<?php echo $metadataBS['model_tests']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Client name',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[4]" value="<?php echo $metadataBS['client_name']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Revision date',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php $revision_date = date("d/m/Y", strtotime($metadataBS['revision_date'])); ?>
                    <input type="text" id="revision_date" name="Metadata[29]" value="<?php echo $revision_date; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Country code',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[5]" value="<?php echo $metadataBS['country_code']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Water depth (meters)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[30]" value="<?php echo (float)$metadataBS['water_depth']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Field name',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[6]" value="<?php echo $metadataBS['field_name']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Return period',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[31]" value="<?php echo (float)$metadataBS['return_period']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Longitude (mN or deg)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[7]" value="<?php echo $metadataBS['longitude']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Directional conditions for design',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[32]" value="<?php echo $metadataBS['directional_conds']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Originator / Design center',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[8]" value="<?php echo $metadataBS['originator']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Survival Hs (meters)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[33]" value="<?php echo (float)$metadataBS['survival_hs']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('System function',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[9]" value="<?php echo $metadataBS['system_function']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Operationg Hs (meters)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[34]" value="<?php echo (float)$metadataBS['operating_hs']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Mooring system',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[10]" value="<?php echo $metadataBS['mooring_system']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Period type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[35]" value="<?php echo $metadataBS['period_type']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Related nb on L/O terminal brochure',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[11]" value="<?php echo $metadataBS['related_nb']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Survival period (seconds)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[36]" value="<?php echo (float)$metadataBS['survival_period']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tanker DWT (tons)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[12]" value="<?php echo (float)$metadataBS['tanker_dwt']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Operating period (seconds)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[37]" value="<?php echo (float)$metadataBS['operating_period']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Product type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[13]" value="<?php echo $metadataBS['product_type']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Spectrum',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[38]" value="<?php echo $metadataBS['spectrum']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Product throughput (bbls/d)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[14]" value="<?php echo (float)$metadataBS['product_throughput']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Gamma factor',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[39]" value="<?php echo $metadataBS['gamma_factor']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Anchor type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[15]" value="<?php echo $metadataBS['anchor_type']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Survival 1-min Vw (m/s)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[40]" value="<?php echo (float)$metadataBS['survival_1min']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Design load no.',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[16]" value="<?php echo (float)$metadataBS['design_load']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Operating 1-min Vw (m/s)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[41]" value="<?php echo (float)$metadataBS['operating_1min']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Certifying authority',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[17]" value="<?php echo $metadataBS['certifying_authority']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Survival Vc (0-m) (m/s)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[42]" value="<?php echo (float)$metadataBS['survival_vc']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Present status',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[18]" value="<?php echo $metadataBS['present_status']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Operating Vc (0-m) (m/s)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[43]" value="<?php echo (float)$metadataBS['operating_vc']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Present location',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[19]" value="<?php echo $metadataBS['present_location']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Dimensional case',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[44]" value="<?php echo $metadataBS['dimensional_case']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Present owner',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[20]" value="<?php echo $metadataBS['present_owner']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Ice layer thickness (cm)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[45]" value="<?php echo (float)$metadataBS['ice_layer_thickness']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('General comments',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[21]" value="<?php echo $metadataBS['general_comments']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tidal range',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[46]" value="<?php echo (float)$metadataBS['tidal_range']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Original system ref.',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[22]" value="<?php echo $metadataBS['original_system']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('2 e tidal max (meters)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[47]" value="<?php echo (float)$metadataBS['tidal_max']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Project Scope',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[23]" value="<?php echo $metadataBS['project_scope']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Expected Terminal occupancy (%)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[48]" value="<?php echo (float)$metadataBS['expected_occupancy']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Year of Contract',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[24]" value="<?php echo $metadataBS['year_contract']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Environment comments',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="Metadata[49]" value="<?php echo $metadataBS['environment_comments']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Latitude (mE or deg)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="Metadata[25]" value="<?php echo $metadataBS['latitude']; ?>" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

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