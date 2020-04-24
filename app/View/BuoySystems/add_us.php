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
        padding-right: 20px;
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
    .chosen-results > li.active-result:first-child {
        /*display: none !important;*/
    }


    .filter-profiles{
        margin-top: 11px;
    }
    .filter-profiles .two-controls{
        height: 30px;
    }

    .filter-profiles .label-dropdown {
        margin-top: 10px;
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
<script type="text/javascript">
    var meta_unit_fields = "";
</script>
<div id='slide_form' class="iconized">    
    <div data-section="6" data-intro="<?php echo __('tutorial_slide_agregar_banda',true);?>" data-position="right">
        <a rel="detalle" class="details active" title="<?php echo __('Details',true); ?>"></a>
        <a rel="banda_actual" class="beltin" title="<?php echo __('Installed belt',true); ?>"></a>
        <a rel="material" class="material" title="<?php echo __('Material',true); ?>"></a>
        <a rel="wearlife" class="wear_life" title="<?php echo __('Wear life',true); ?>"></a>
        <a rel="transportador" class="tensor" title="<?php echo __('Conveyor',true); ?>"></a>
        <?php if($credentials["type"]=='pro'): ?>
        <a rel="rodillos" class="idler" title="<?php echo __('Idlers',true); ?>"></a>
        <a rel="poleas" class="pulley" title="<?php echo __('Pulleys',true); ?>"></a>
        <a rel="zona_transicion" class="transition_zone" title="<?php echo __('Transition zone',true); ?>"></a>
        <a rel="observaciones" class="remark" title="<?php echo __('Remarks',true); ?>"></a>
        <?php endif;?>
    </div>
</div>
<form id="add_conveyor_form" action="<?php echo $this->Html->url(array('controller'=>'Conveyors','action'=>'saveUs')); ?>" class="fancy_form is-us-form" autocomplete="off">
    <input type="hidden" id="path_logo_transportador" name="path_logo_transportador"/>
    <input type="hidden" id="data-field-units" name="data-field-units"/>
    <div class='slide-form-section'>        
        <div id="detalle" class="active_section">
            <div id="logo_transportador" title="<?php echo __('Agregar imagen de transportador', true); ?>" data-section="6" data-intro="<?php echo __('tutorial_portada_agregar_banda',true);?>" data-position="left"></div>
            <div class="space"></div>
            <div class="full-controls" data-section="6" data-intro="<?php echo __('tutorial_numero_agregar_banda',true);?>" data-position="left">
                    <input type="text" placeholder="<?php echo __('Conveyor number', true); ?>" name="no_transportador" id="no_transportador" class="validate[required] main-input"/>
            </div>
            <div class="form-data">
                <div data-section="6" data-intro="<?php echo __('tutorial_dist_cte_agregar_banda',true);?>" data-position="left">
                    <div class="full-controls">
                        <?php $this->Content->putDropdownCompanies(__('Distributor', true),'distributor',$distribuidores, $is_disabled = false);?>
                    </div>
                    <div class="full-controls">
                        <?php $this->Content->putDropdownCompanies(__('Customer', true),'client',array(), $is_disabled = true);?>
                    </div>
                </div>
                <div class="filter-profiles">
                    <div class="two-controls">
                        <div class="label-dropdown"><?php echo __('Units',true);?></div>
                    </div>
                    <div class="two-controls last-ctrl">
                        <?php echo $this->Form->input('capture_units', array('name'=>'capture_units','type' => 'select','label' => false,'options' => $units_conveyor, 'data-placeholder'=>__('Seleccione',true))); ?>
                    </div>
                    <div class="space"></div>
                    <div class="space"></div>
                    <div class="two-controls" data-section="6" data-intro="<?php echo __('tutorial_perfil_agregar_banda',true);?>" data-position="left">
                        <div class="label-dropdown"><?php echo __('Profile of conveyor',true);?></div>
                    </div>
                    <div class="two-controls last-ctrl">
                        <?php $this->Content->putDropdownPerfilesBandas($perfiles,'sel_perfil', 'validate[required]', __('Select', true)); ?>
                    </div>
                </div>

            </div>
        </div>
        <div id="banda_actual" class="hidden scrollable-tab">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Belt manufacturer',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('belt_manufacturer', array('name'=>'belt_manufacturer', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['manufacturer'], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_manufacturer" class='hidden'/><span id="close_other_manufacturer" class="close-stick hidden"></span>
                    </div>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Belt family',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('belt_family', array('name'=>'belt_family','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_family" class='hidden'/>
                    </div>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Compound',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('belt_compound', array('name'=>'belt_compound','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_compound" class='hidden'/>
                    </div>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Carcass',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('carcass', array('name'=>'carcass', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['carcass'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tension unit',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('tension_unit', array('name'=>'tension_unit', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['units'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tension',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('tension', array('name'=>'tension','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <input type="text" name="open_tension" class='hidden validate[custom[number]]'/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Plies',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('plies', array('name'=>'plies', 'type' => 'select','disabled' =>'disabled','label' => false,'options' => $conveyor_us_config['installed_belt']['plies'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('width', array('name'=>'width','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="other_width" class='hidden validate[custom[number]]'/><span id="close_other_width" class="close-stick hidden"></span>
                    </div>
                    <!--<a href="#" class="unit-indicator disabled">in</a>-->
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Top cover',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('top_cover', array('name'=>'top_cover', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['top_covers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Pulley cover',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('pulley_cover', array('name'=>'pulley_cover', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['pulley_covers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Other special',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('other_special', array('name'=>'other_special', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['other_special'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label other-special-txt hidden">
                    <div class="conveyor-label hidden"><?php echo __('v.2.5.1.NameRipDetection',true); ?></div>
                    <div class="conveyor-label hidden"><?php echo __('v.2.5.1.Other',true); ?></div>
                </div>
                <div class="column-ctrl other-special-txt hidden last">
                    <input type="text" name="other_special_data" class='' maxlength="50"/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Installation date',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="installation_date" class='' readonly="readonly"/><span id="clear_installed_date" class="close-stick"></span>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Belt length for installation (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="belt_length_install" class=''/>
                    <a href="#" class="unit-indicator disabled">ft</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Splice type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('splice_type', array('name'=>'splice_type', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['splice_types'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Splice quantity',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('splice_quantity', array('name'=>'splice_quantity', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['splice_quantity'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Splice condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('splice_condition', array('name'=>'splice_condition', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['splice_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Existing damage to belt',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="existing_damage_belt" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin hidden">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Failure mode',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('failure_mode', array('name'=>'failure_mode', 'multiple' => true, 'type' => 'select', 'class' => 'multiple', 'label' => false,'options' => $conveyor_us_config['installed_belt']['failure_modes']['modes'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin hidden">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Durometer of failed belt',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="durometer_failed" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls hidden">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Date belt failed',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="date_belt_failed" class='' readonly="readonly"/><span id="clear_date_failed" class="close-stick"></span>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>


        </div>

        <!--MATERIAL-->
        <div id="material" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Material being carried',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php
                    $materials = $conveyor_us_config['material']['materials'];
                    asort($materials);
                    ?>
                    <!--<?php $this->Content->printConfigTranspDropDownUs($descripcion_materiales, 'material_desc', 'not-required', __('Select', true)); ?>-->
                    <?php echo $this->Form->input('material_desc', array('name'=>'material_desc', 'type' => 'select','label' => false,'options' => $materials, 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label other-material-txt hidden">
                    <div class="conveyor-label"><?php echo __('Other',true); ?></div>
                </div>
                <div class="column-ctrl other-material-txt hidden last">
                    <input type="text" name="other_material" class='' maxlength="50"/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Material density (lb/ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="material_density" class='validate[custom[positive_number]]'/>
                    <a  href="#" class="unit-indicator" data-units="(lb/ft3)|(kg/m3)" title="(kg/m3)">(lb/ft3)</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Lump size (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="lump_size" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label lump_size-fields hidden">
                    <div class="conveyor-label"><?php echo __d('forms','Percent of fines (%)',true); ?></div>
                </div>
                <div class="column-ctrl last lump_size-fields hidden">
                    <input type="text" name="percent_fines" class=''/>
                    <a href="#" class="unit-indicator disabled">%</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Maximum temperature (°F)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="max_temp" class=''/>
                    <a href="#" class="unit-indicator" data-units="°F|°C" title="°C">°F</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Minimum temperature (°F)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="min_temp" class=''/>
                    <a href="#" class="unit-indicator" data-units="°F|°C" title="°C">°F</a>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Approx. chute drop (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="chute_drop" class=''/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Presence of oil',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php $this->Content->printConfigTranspDropDownUs($oil_presence, 'oil_presence', 'not-required', __('Select', true)); ?>
                </div>

                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
        </div>

        <!-- WEAR LIFE -->
        <div id="wearlife" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Operating hours per year',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="operating_hours_year" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tons per year',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tons_per_year" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Feed Angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="feed_angle" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Chute Angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="chute_angle" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Belt incline angle in load zone (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="belt_incline_angle" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
        </div>

        <!--    CONVEYOR -->
        <div id="transportador" class="hidden scrollable-tab">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Center to center length (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="center_to_center" class=''/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="lift" class=''/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tons per hour loading',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tons_per_hour" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Belt speed (fpm)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="belt_speed" class=''/>
                    <a href="#" class="unit-indicator" data-units="fpm|m/s" title="m/s">fpm</a>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Type of takeup',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <!--<select name="takeup_type" id="takeup_type" class="image-chosen with-text" data-placeholder="<?php echo __('Select', true); ?>">
                        <?php foreach ($conveyor_us_config['conveyor']['takeup_type'] AS $id => $data): ?>
                            <option data-img-src="<?php echo $site.$data[1]; ?>" value="<?php echo $id; ?>"><?php echo $data[0]; ?></option>
                        <?php endforeach; ?>
                    </select>-->
                    <?php echo $this->Form->input('takeup_type', array('name'=>'takeup_type', 'type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['takeup_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label counterweight-txt hidden">
                    <div class="conveyor-label"><?php echo __d('forms', 'Counterweight (lbs)',true); ?></div>
                </div>
                <div class="column-ctrl counterweight-txt hidden last">
                    <input type="text" name="counterweight" class=''/>
                    <a href="#" class="unit-indicator" data-units="lbs|kg" title="kg">lbs</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Takeup travel (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="takeup_travel" class=''/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Carry side idler angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_angle" class='' value="35"/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Carry side idler diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="carry_side_diameter" class='' value="6"/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Carry side idler spacing (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_space" class='' value="4"/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Return side idler angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <!--<input type="text" name="return_side_angle" class=''/>-->
                    <?php echo $this->Form->input('return_side_angle', array('name'=>'return_side_angle', 'type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['return_side_angles'], 'data-placeholder'=>__('Select',true))); ?>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Return side idler diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="return_side_diameter" class='' value="6"/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Return side idler spacing (ft)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="return_side_space" class='' value="10"/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
            </div>


            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Drive power (hp)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_pulley_power" class=''/>
                    <a href="#" class="unit-indicator" data-units="hp|kW" title="kW">hp</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive wrap angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="drive_pulley_wrap_angle" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Drive pulley surface',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('drive_pulley_surface', array('name'=>'drive_pulley_surface', 'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['drive_surface'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Drive pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="drive_pulley_diameter" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Head pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_diameter" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Takeup pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="takeup_pulley_diameter" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Tail pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_diameter" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>


            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Head transition (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_transition" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Tail transition (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="tail_transition" class=''/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('1/2 or full trough transitions?',true); ?></div>
                </div>
                <div class="column-ctrl tooltiped" title="<?php echo __('Full is the standard transition configuration',true); ?>">
                    <?php echo $this->Form->input('type_trough_transitions', array('name'=>'type_trough_transitions','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['trough_transition_types'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label no-margin">
                    <div class="conveyor-label"><?php echo __('Turnovers?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('with_turnovers', array('name'=>'with_turnovers','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['turnovers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label turnover-length-txt hidden">
                    <div class="conveyor-label"><?php echo __d('forms', 'Turnover length (ft)',true); ?></div>
                </div>
                <div class="column-ctrl turnover-length-txt hidden">
                    <input type="text" name="turnover_length" class=''/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>




            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of stations',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" name="number_stations" />
                    <select id="stations" multiple="multiple" class="multiple">
                        <optgroup label="Stations">
                            <option value="1s" disabled="disabled" class="stations">1</option>
                            <option value="2s" selected="selected" class="stations">2</option>
                            <option value="3s" class="stations">3</option>
                            <option value="4s" class="stations">4</option>
                            <option value="5s" class="stations">5</option>
                            <option value="6s" class="stations">6</option>
                        </optgroup>
                        <optgroup label="Drives">
                            <option value="1d" selected="selected" class="drives">1</option>
                            <option value="2d" class="drives">2</option>
                        </optgroup>
                        <optgroup label="Takeups">
                            <option value="1t" selected="selected" class="takeups">1</option>
                            <option value="2t" class="takeups">2</option>
                        </optgroup>
                    </select>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Gear ratio',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="gear_ratio" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive frequency (rpm)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_frecuency" class=''/>
                    <a href="#" class="unit-indicator disabled">rpm</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="conveyor_angle" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label"><div class="conveyor-label"><?php echo __d('forms','Percent load (%)',true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="percent_load" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator disabled">%</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Location',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php $this->Content->printConfigTranspDropDownUs($conveyor_location, 'location', 'not-required', __('Select', true)); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Pipe belt',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('pipe_belt', array('name'=>'pipe_belt','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['pipe_belt'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Direction turnover',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('direction_turnover', array('name'=>'direction_turnover','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['direction_turnover'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Ambient conditions',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('ambient_conditions', array('name'=>'ambient_conditions','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['ambient_conditions'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Humidity',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('humidity', array('name'=>'humidity','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['humidity'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label"><div class="conveyor-label"><?php echo __d('forms', 'Height above sea level (ft)',true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="sea_level" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Housing',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('housing', array('name'=>'housing','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['housing'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Friction factor',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('friction_factor', array('name'=>'friction_factor','type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['friction_factor'], 'data-placeholder' => __('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>
        </div>


        <!-- IDLERS -->
        <div id="rodillos" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Impacto Ø (plg)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <!--<input type="text" name="impact_diameter" class='validate[custom[positive_number]]'/>-->
                    <?php echo $this->Form->input('impact_diameter', array('name'=>'impact_diameter','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['impact_diameter'], 'data-placeholder'=>__('Select',true))); ?>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label"><div class="conveyor-label"><?php echo __('Number of idlers',true); ?></div></div>
                <div class="column-ctrl last">
                    <input type="text" name="number_impact_idlers" class='validate[custom[positive_number]]'/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of carry side idlers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_idlers_number" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of return side idlers',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="return_side_idlers_number" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of carry idler rollers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('part_troughing_load', array('name'=>'part_troughing_load','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['part_troughing'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number return idler rollers',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php $this->Content->printConfigTranspDropDownUs($partes_artesa, 'part_troughing_return', '', __('Select', true)); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Angle impact (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php $this->Content->printConfigTranspDropDownUs($rodillos_config['angle_rodillo_ldr'], 'impact_angle', 'not-required', __('Select', true)); ?>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('General condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('general_condition', array('name'=>'general_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['general_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Stuck idlers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php
                    echo $this->Form->input('stuck_idlers', array('name'=>'stuck_idlers','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['stuck_idlers'], 'data-placeholder'=>__('Select',true)));
                    ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Misalignment sensors upper',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('misalignment_sensor_upper', array('name'=>'misalignment_sensor_upper','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['misalignment_sensor_upper'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Misalignment sensors lower',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('misalignment_sensor_lower', array('name'=>'misalignment_sensor_lower','type' => 'select','label' => false,'options' => $conveyor_us_config['idlers']['misalignment_sensor_lower'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>


        </div>

        <!-- PULLEYS -->
        <div id="poleas" class="hidden scrollable-tab">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Drive pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Lagging Thickness',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="lagging_thickness" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('motriz_surface_condition', array('name'=>'motriz_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['drive_surface'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Brake device?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('brake_device', array('name'=>'brake_device','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['brake_device'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Head pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('head_lagging_type', array('name'=>'head_lagging_type','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('head_surface_condition', array('name'=>'head_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Tail pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('tail_lagging_type', array('name'=>'tail_lagging_type','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('tail_surface_condition', array('name'=>'tail_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Snub pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="snub_pulley_diameter" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="snub_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('snub_lagging_type', array('name'=>'snub_lagging_type','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('snub_surface_condition', array('name'=>'snub_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Bend pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="bend_pulley_diameter" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="bend_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('bend_lagging_type', array('name'=>'bend_lagging_type','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('bend_surface_condition', array('name'=>'bend_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Takeup pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="takeup_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('takeup_lagging_type', array('name'=>'takeup_lagging_type','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('takeup_surface_condition', array('name'=>'takeup_surface_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Additional pulley 1",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="add1_pulley_diameter" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="add1_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Additional pulley 2",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="add2_pulley_diameter" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="add2_pulley_width" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="in|mm" title="mm">in</a>
                </div>
            </div>
        </div>

        <div id="zona_transicion" class="hidden">
            <div class="slide-form-title"></div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Tail transition",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Pulley lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_lift" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Condition (pressure on outer idlers)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('pressure_outer_idlers', array('name'=>'pressure_outer_idlers','type' => 'select','label' => false,'options' => $conveyor_us_config['transition']['pressure_outer_idlers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Head transition",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms', 'Pulley lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_lift" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator" data-units="ft|m" title="m">ft</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Material guidance?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('material_guidance', array('name'=>'material_guidance','type' => 'select','label' => false,'options' => $conveyor_us_config['transition']['material_guidance'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
        </div>
        <div id="observaciones" class="hidden scrollable-tab">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Maintenance condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('maintenance_condition', array('name'=>'maintenance_condition','type' => 'select','label' => false,'options' => $conveyor_us_config['remarks']['maintenance_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Overall status',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('overall_status', array('name'=>'overall_status','type' => 'select','label' => false,'options' => $conveyor_us_config['remarks']['overall_status'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="full-controls">
                <div class="conveyor-label"><?php echo __('Additional remarks',true); ?></div>
            </div>
            <div class="full-controls">                
                <textarea name="remarks" class="textarea-form" placeholder=""></textarea>
            </div>
        </div>

    </div>
    <div id="slide_navigation" class="slide-form-navigation"><a rel="prev"><?php echo __('Anterior',true); ?></a><a rel="next"><?php echo __('Siguiente',true); ?></a></div>
    <div class="dialog-buttons">  
        <section>
            <button type="button" id="save_conveyor" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar y Cerrar', true); ?></button>            
        </section>
    </div> 
</form>