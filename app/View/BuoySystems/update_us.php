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
$meta_units = utf8_encode($conveyor['Conveyor']['meta_units']);
$meta_units = str_replace("º","°",$meta_units);

//$meta_units = str_replace(chr(194),"",$conveyor['Conveyor']['meta_units']);
//$meta_units = str_replace("Â","",$conveyor['Conveyor']['meta_units']);
?>
<script type="text/javascript">
    //var mynum = <?php echo intval(1); ?>;
    //var myarray = <?php echo json_encode(array("one" => 1, "two" => 2)); ?>;
    var meta_unit_fields = "<?php echo addslashes($meta_units); ?>";
</script>
<?php
if ($response['success']) {
$secureItemParams = $this->Utilities->encodeParams($conveyor['Conveyor']['id']);
$urlEdit = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'processUpdateUs', $secureItemParams['item_id'], $secureItemParams['digest']));
$full_conveyor = $conveyor;
$conveyor = $conveyor['Conveyor'];

    $tabInstalledBelt = $full_conveyor['TabInstalledBelt'];
    $tabConveyor = $full_conveyor['TabConveyor'];
    $tabIdler = $full_conveyor['TabIdler'];
    $tabPulley = $full_conveyor['TabPulley'];
    $tabRemark = $full_conveyor['TabRemark'];
    $tabTransitionZone = $full_conveyor['TabTransitionZone'];
    $tabMaterial = $full_conveyor['TabMaterial'];
    $tabWearLife = $full_conveyor['TabWearLife'];
?>
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
<form id="add_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form is-us-form">
    <input type="hidden" value="<?php echo $path_imagen_portada; ?>" id="reference_bg_uploader"/>
    <input type="hidden" id="path_logo_transportador" name="path_logo_transportador"/>
    <?php if($credentials['role']==UsuariosEmpresa::IS_MASTER): ?>
        <input type="hidden" id="data-field-units" name="data-field-units"/>
    <?php endif; ?>
    <div class='slide-form-section'>        
        <div id="detalle" class="active_section">
            <div id="logo_transportador" title="<?php echo __('Agregar imagen de transportador', true); ?>" data-section="6" data-intro="<?php echo __('tutorial_portada_agregar_banda',true);?>" data-position="left"></div>
            <div class="space"></div>
            <div class="full-controls">
                    <input type="text" value="<?php echo $conveyor['numero']; ?>" placeholder="<?php echo __('Conveyor number', true); ?>" name="no_transportador" id="no_transportador" class="validate[required] main-input"/>
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
                <div class="space"></div>
                <div class="space"></div>
                <div class="two-controls">
                    <div class="label-dropdown"><?php echo __('Profile of conveyor',true);?></div>
                </div>
                <div class="two-controls last-ctrl">
                    <?php $this->Content->putDropdownPerfilesBandas($perfiles,'sel_perfil', 'validate[required]', __('Select', true), $conveyor['perfil']); ?>
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
                    <input type="hidden" id="belt_manufacturer_saved" value="<?php echo $tabInstalledBelt['belt_manufacturer']; ?>"/>
                    <?php echo $this->Form->input('belt_manufacturer', array('name'=>'belt_manufacturer', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['manufacturer'], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_manufacturer" value="<?php echo $tabInstalledBelt['open_belt_manufacturer']; ?>" class='hidden'/><span id="close_other_manufacturer" class="close-stick hidden"></span>
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
                    <input type="hidden" id="belt_family_saved" value="<?php echo $tabInstalledBelt['belt_family']; ?>"/>
                    <?php echo $this->Form->input('belt_family', array('name'=>'belt_family','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_family" value="<?php echo $tabInstalledBelt['open_belt_family']; ?>" class='hidden'/>
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
                    <input type="hidden" id="belt_compound_saved" value="<?php echo $tabInstalledBelt['belt_compound']; ?>"/>
                    <?php echo $this->Form->input('belt_compound', array('name'=>'belt_compound','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="open_belt_compound" value="<?php echo $tabInstalledBelt['open_belt_compound']; ?>" class='hidden'/>
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
                    <input type="hidden" id="carcass_saved" value="<?php echo $tabInstalledBelt['carcass']; ?>"/>
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
                    <input type="hidden" id="tension_unit_saved" value="<?php echo $tabInstalledBelt['tension_unit']; ?>"/>
                    <?php echo $this->Form->input('tension_unit', array('name'=>'tension_unit', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['units'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tension',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="hidden" id="tension_saved" value="<?php echo $tabInstalledBelt['tension']; ?>"/>
                    <?php echo $this->Form->input('tension', array('name'=>'tension','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <input type="text" name="open_tension" value="<?php echo $tabInstalledBelt['open_tension']; ?>" class='hidden validate[custom[number]]'/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Plies',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" id="plies_saved" value="<?php echo $tabInstalledBelt['plies']; ?>"/>
                    <?php echo $this->Form->input('plies', array('name'=>'plies', 'type' => 'select','disabled' =>'disabled','label' => false,'options' => $conveyor_us_config['installed_belt']['plies'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="hidden" id="width_saved" value="<?php echo $tabInstalledBelt['width']; ?>"/>
                    <?php echo $this->Form->input('width', array('name'=>'width','type' => 'select','disabled' =>'disabled','label' => false,'options' => [], 'data-placeholder'=>__('Select',true))); ?>
                    <div class="closable-input-ctrl">
                        <input type="text" name="other_width" value="<?php echo $tabInstalledBelt['other_width']; ?>" class='hidden validate[custom[number]]'/><span id="close_other_width" class="close-stick hidden"></span>
                    </div>
                    <!--<a href="#" class="unit-indicator disabled">in</a>-->
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Top cover',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" id="top_cover_saved" value="<?php echo $tabInstalledBelt['top_cover']; ?>"/>
                    <?php echo $this->Form->input('top_cover', array('name'=>'top_cover', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['top_covers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Pulley cover',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="hidden" id="pulley_cover_saved" value="<?php echo $tabInstalledBelt['pulley_cover']; ?>"/>
                    <?php echo $this->Form->input('pulley_cover', array('name'=>'pulley_cover', 'type' => 'select','label' => false,'options' => $conveyor_us_config['installed_belt']['pulley_covers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Other special',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" id="other_special_saved" value="<?php echo $tabInstalledBelt['other_special']; ?>"/>
                    <input type="hidden" id="other_special_data_saved" value="<?php echo $tabInstalledBelt['other_special_data']; ?>"/>
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
                    <?php
                    if(!is_null($tabInstalledBelt['installation_date']) && $tabInstalledBelt['installation_date']!="0000-00-00"):
                        list($anio, $mes, $dia) = explode('-', $tabInstalledBelt['installation_date']);
                        $installDate = implode("/",[$mes,$dia,$anio]);
                        ?>
                    <input type="hidden" id="installation_date_saved" value="<?php echo $installDate; ?>"/>
                    <?php endif; ?>
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
                    <input type="text" name="belt_length_install" value="<?php echo $tabInstalledBelt['belt_length_install']; ?>" class=''/>
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
                    <input type="hidden" id="splice_type_saved" value="<?php echo $tabInstalledBelt['splice_type']; ?>"/>
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
                    <input type="hidden" id="splice_quantity_saved" value="<?php echo $tabInstalledBelt['splice_quantity']; ?>"/>
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
                    <input type="hidden" id="splice_condition_saved" value="<?php echo $tabInstalledBelt['splice_condition']; ?>"/>
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
                    <input type="text" name="existing_damage_belt" value="<?php echo $tabInstalledBelt['existing_damage_belt']; ?>" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Failure mode',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php
                    $selected = explode(',',$tabInstalledBelt['failure_mode']);
                    ?>
                    <?php echo $this->Form->input('failure_mode', array('name'=>'failure_mode', 'multiple' => true, 'type' => 'select', 'class' => 'multiple', 'label' => false,'options' => $conveyor_us_config['installed_belt']['failure_modes']['modes'], 'selected' => $selected, 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Durometer of failed belt',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="durometer_failed" value="<?php echo $tabInstalledBelt['durometer_failed']; ?>" class=''/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>

            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Date belt failed',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php
                    if(!is_null($tabInstalledBelt['date_belt_failed']) && $tabInstalledBelt['date_belt_failed']!="0000-00-00"):
                        list($anio, $mes, $dia) = explode('-', $tabInstalledBelt['date_belt_failed']);
                        $beltFailedDate = implode("/",[$mes,$dia,$anio]);
                        ?>
                        <input type="hidden" id="date_belt_failed_saved" value="<?php echo $beltFailedDate; ?>"/>
                    <?php endif; ?>
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
                    <input type="hidden" id="material_desc_saved" value="<?php echo $tabMaterial['material']; ?>"/>
                    <?php echo $this->Form->input('material_desc', array('name'=>'material_desc', 'type' => 'select','label' => false,'options' => $materials, 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label other-material-txt hidden">
                    <div class="conveyor-label"><?php echo __('Other',true); ?></div>
                </div>
                <div class="column-ctrl other-material-txt hidden last">
                    <input type="text" name="other_material" value="<?php echo $tabMaterial['other_material']; ?>" class='' maxlength="50"/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Material density (lb/ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="material_density" value="<?php echo $tabMaterial['material_density']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                </div>
                <div class="column-ctrl last">
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Lump size (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="lump_size" value="<?php echo $tabMaterial['lump_size']; ?>" class=''/>
                </div>
                <div class="column-label lump_size-fields <?php if($tabMaterial['lump_size']>=6): ?> hidden <?php endif; ?>">
                    <div class="conveyor-label"><?php echo __d('forms','Percent of fines (%)',true); ?></div>
                </div>
                <div class="column-ctrl last lump_size-fields <?php if($tabMaterial['lump_size']>=6 || $tabMaterial['lump_size']==""): ?> hidden <?php endif; ?>"">
                    <input type="text" name="percent_fines" value="<?php echo $tabMaterial['percent_fines']; ?>" class=''/>
                    <a href="#" class="unit-indicator disabled">%</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Maximum temperature (°F)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="max_temp" value="<?php echo $tabMaterial['max_temp']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Minimum temperature (°F)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="min_temp" value="<?php echo $tabMaterial['min_temp']; ?>" class=''/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Approx. chute drop (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="chute_drop" value="<?php echo $tabMaterial['chute_drop']; ?>" class=''/>
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
                    <?php $this->Content->printConfigTranspDropDownUs($oil_presence, 'oil_presence', 'not-required', __('Select', true), $tabMaterial['oil_presence']); ?>
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
                    <input type="text" name="operating_hours_year" value="<?php echo $tabWearLife['operating_hours_year']; ?>" class=''/>
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
                    <input type="text" name="tons_per_year" value="<?php echo $tabWearLife['tons_per_year']; ?>" class=''/>
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
                    <input type="text" name="feed_angle" value="<?php echo $tabWearLife['feed_angle']; ?>" class=''/>
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
                    <input type="text" name="chute_angle" value="<?php echo $tabWearLife['chute_angle']; ?>" class=''/>
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
                    <input type="text" name="belt_incline_angle" value="<?php echo $tabWearLife['belt_incline_angle']; ?>"  class=''/>
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
                    <div class="conveyor-label"><?php echo __d('forms','Center to center length (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="center_to_center" value="<?php echo $tabConveyor['center_to_center']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="lift" value="<?php echo $tabConveyor['lift']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Tons per hour loading',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tons_per_hour" value="<?php echo $tabConveyor['tons_per_hour']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Belt speed (fpm)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="belt_speed" value="<?php echo $tabConveyor['belt_speed']; ?>" class=''/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Type of takeup',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" id="takeup_type_saved" value="<?php echo $tabConveyor['takeup_type']; ?>"/>
                    <?php echo $this->Form->input('takeup_type', array('name'=>'takeup_type', 'type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['takeup_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label counterweight-txt hidden">
                    <div class="conveyor-label"><?php echo __d('forms','Counterweight (lbs)',true); ?></div>
                </div>
                <div class="column-ctrl counterweight-txt hidden last">
                    <input type="text" name="counterweight" value="<?php echo $tabConveyor['counterweight']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Takeup travel (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="takeup_travel" value="<?php echo $tabConveyor['takeup_travel']; ?>" class=''/>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Carry side idler angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_angle" class='' value="<?php echo $tabConveyor['carry_side_angle']=="" ? "35":$tabConveyor['carry_side_angle']; ?>"/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Carry side idler diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="carry_side_diameter" class='' value="<?php echo $tabConveyor['carry_side_diameter']=="" ? "6":$tabConveyor['carry_side_diameter']; ?>"/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Carry side idler spacing (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_space" class='' value="<?php echo $tabConveyor['carry_side_space']=="" ? "4":$tabConveyor['carry_side_space']; ?>"/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Return side idler angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <!--<input type="text" name="return_side_angle" class=''/>-->
                    <?php echo $this->Form->input('return_side_angle', array('name'=>'return_side_angle', 'type' => 'select','label' => false,'options' => $conveyor_us_config['conveyor']['return_side_angles'],'default'=>$tabConveyor['return_side_angle'], 'data-placeholder'=>__('Select',true))); ?>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Return side idler diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="return_side_diameter" class='' value="<?php echo $tabConveyor['return_side_diameter']=="" ? "6":$tabConveyor['return_side_diameter']; ?>"/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Return side idler spacing (ft)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="return_side_space" class='' value="<?php echo $tabConveyor['return_side_space']=="" ? "10":$tabConveyor['return_side_space']; ?>"/>
                </div>
            </div>


            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive power (hp)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_pulley_power" value="<?php echo $tabConveyor['drive_pulley_power']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive wrap angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="drive_pulley_wrap_angle" value="<?php echo $tabConveyor['drive_pulley_wrap_angle']; ?>" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Drive pulley surface',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('drive_pulley_surface', array('name'=>'drive_pulley_surface', 'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['drive_surface'],'default'=>$tabConveyor['drive_pulley_surface'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="drive_pulley_diameter" value="<?php echo $tabConveyor['drive_pulley_diameter']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Head pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_diameter" value="<?php echo $tabConveyor['head_pulley_diameter']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Takeup pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="takeup_pulley_diameter" value="<?php echo $tabConveyor['takeup_pulley_diameter']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Tail pulley diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_diameter" value="<?php echo $tabConveyor['tail_pulley_diameter']; ?>" class=''/>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>


            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Head transition (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_transition" value="<?php echo $tabConveyor['head_transition']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Tail transition (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="tail_transition" value="<?php echo $tabConveyor['tail_transition']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('1/2 or full trough transitions?',true); ?></div>
                </div>
                <div class="column-ctrl tooltiped" title="<?php echo __('Full is the standard transition configuration',true); ?>">
                    <?php echo $this->Form->input('type_trough_transitions', array('name'=>'type_trough_transitions','type' => 'select','default'=>$tabConveyor['type_trough_transitions'],'label' => false,'options' => $conveyor_us_config['pulleys']['trough_transition_types'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label no-margin">
                    <div class="conveyor-label"><?php echo __('Turnovers?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="hidden" id="with_turnovers_saved" value="<?php echo $tabConveyor['with_turnovers']; ?>"/>
                    <?php echo $this->Form->input('with_turnovers', array('name'=>'with_turnovers','type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['turnovers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label turnover-length-txt hidden">
                    <div class="conveyor-label"><?php echo __d('forms','Turnover length (ft)',true); ?></div>
                </div>
                <div class="column-ctrl turnover-length-txt hidden">
                    <input type="text" name="turnover_length" value="<?php echo $tabConveyor['turnover_length']; ?>" class=''/>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>




            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of stations',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="hidden" name="number_stations" value="<?php echo $tabConveyor['number_stations']; ?>"/>
                    <?php
                    $tabConveyor['number_stations'] = is_null($tabConveyor['number_stations']) || $tabConveyor['number_stations']=='' ? '2,1,1' : $tabConveyor['number_stations'];
                    $stations = explode(',',$tabConveyor['number_stations']);
                    $stations = array_map('trim', $stations);
                    ?>
                    <select id="stations" multiple="multiple" class="multiple">
                        <optgroup label="Stations">
                            <option value="1s" disabled="disabled" class="stations">1</option>
                            <option value="2s" <?php if($stations[0]==2): ?> selected="selected" <?php endif; ?> class="stations">2</option>
                            <option value="3s" <?php if($stations[0]==3): ?> selected="selected" <?php endif; ?> class="stations">3</option>
                            <option value="4s" <?php if($stations[0]==4): ?> selected="selected" <?php endif; ?> class="stations">4</option>
                            <option value="5s" <?php if($stations[0]==5): ?> selected="selected" <?php endif; ?> class="stations">5</option>
                            <option value="6s" <?php if($stations[0]==6): ?> selected="selected" <?php endif; ?> class="stations">6</option>
                        </optgroup>
                        <optgroup label="Drives">
                            <?php for($i=1; $i<=$stations[0];$i++): ?>
                                <option value="<?php echo $i; ?>d" <?php if($stations[1]==$i): ?> selected="selected" <?php endif; ?> class="drives <?php if($i>2): ?> removable <?php endif; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </optgroup>
                        <optgroup label="Takeups">
                            <?php for($i=1; $i<=$stations[0];$i++): ?>
                                <option value="<?php echo $i; ?>t" <?php if($stations[2]==$i): ?> selected="selected" <?php endif; ?> class="takeups <?php if($i>2): ?> removable <?php endif; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Gear ratio',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="gear_ratio" value="<?php echo $tabConveyor['gear_ratio']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Drive frequency (rpm)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_frecuency" value="<?php echo $tabConveyor['drive_frecuency']; ?>" class=''/>
                    <a href="#" class="unit-indicator disabled">rpm</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Angle (°)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="conveyor_angle" value="<?php echo $tabConveyor['conveyor_angle']; ?>" class=''/>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label"><div class="conveyor-label"><?php echo __d('forms','Percent load (%)',true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="percent_load" value="<?php echo $tabConveyor['percent_load']; ?>" class='validate[custom[positive_number]]'/>
                    <a href="#" class="unit-indicator disabled">%</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Location',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php $this->Content->printConfigTranspDropDownUs($conveyor_location, 'location', 'not-required', __('Select', true), $tabConveyor['location']); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Pipe belt',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('pipe_belt', array('name'=>'pipe_belt','type' => 'select','label' => false,'default'=>$tabConveyor['pipe_belt'],'options' => $conveyor_us_config['conveyor']['pipe_belt'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Direction turnover',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('direction_turnover', array('name'=>'direction_turnover','type' => 'select','label' => false,'default'=>$tabConveyor['direction_turnover'],'options' => $conveyor_us_config['conveyor']['direction_turnover'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Ambient conditions',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('ambient_conditions', array('name'=>'ambient_conditions','type' => 'select','default'=>$tabConveyor['ambient_conditions'],'label' => false,'options' => $conveyor_us_config['conveyor']['ambient_conditions'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Humidity',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('humidity', array('name'=>'humidity','type' => 'select','default'=>$tabConveyor['humidity'],'label' => false,'options' => $conveyor_us_config['conveyor']['humidity'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin pro-section">
                <div class="column-label"><div class="conveyor-label"><?php echo __d('forms','Height above sea level (ft)',true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="sea_level" value="<?php echo $tabConveyor['sea_level']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Housing',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('housing', array('name'=>'housing','type' => 'select','default'=>$tabConveyor['housing'],'label' => false,'options' => $conveyor_us_config['conveyor']['housing'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls pro-section">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Friction factor',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('friction_factor', array('name'=>'friction_factor','type' => 'select','default'=>$tabConveyor['friction_factor'],'label' => false,'options' => $conveyor_us_config['conveyor']['friction_factor'], 'data-placeholder' => __('Select',true))); ?>
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
                    <div class="conveyor-label"><?php echo __d('forms','Impacto Ø (plg)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('impact_diameter', array('name'=>'impact_diameter','type' => 'select','default'=>$tabIdler['impact_diameter'],'label' => false,'options' => $conveyor_us_config['idlers']['impact_diameter'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"><div class="conveyor-label"><?php echo __('Number of idlers',true); ?></div></div>
                <div class="column-ctrl last">
                    <input type="text" name="number_impact_idlers" value="<?php echo $tabIdler['number_impact_idlers']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of carry side idlers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="carry_side_idlers_number" value="<?php echo $tabIdler['carry_side_idlers_number']; ?>" class=''/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of return side idlers',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="return_side_idlers_number" value="<?php echo $tabIdler['return_side_idlers_number']; ?>" class=''/>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number of carry idler rollers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('part_troughing_load', array('name'=>'part_troughing_load','type' => 'select','default'=>$tabIdler['part_troughing_load'],'label' => false,'options' => $conveyor_us_config['idlers']['part_troughing'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Number return idler rollers',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php $this->Content->printConfigTranspDropDownUs($partes_artesa, 'part_troughing_return', '', __('Select', true), $tabIdler['part_troughing_return']); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Angle impact (°)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php $this->Content->printConfigTranspDropDownUs($rodillos_config['angle_rodillo_ldr'], 'impact_angle', 'not-required', __('Select', true), $tabIdler['impact_angle']); ?>
                    <a href="#" class="unit-indicator disabled">°</a>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('General condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('general_condition', array('name'=>'general_condition','type' => 'select', 'default'=>$tabIdler['general_condition'],'label' => false,'options' => $conveyor_us_config['idlers']['general_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Stuck idlers',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php
                    echo $this->Form->input('stuck_idlers', array('name'=>'stuck_idlers','type' => 'select', 'default'=>$tabIdler['stuck_idlers'],'label' => false,'options' => $conveyor_us_config['idlers']['stuck_idlers'], 'data-placeholder'=>__('Select',true)));
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
                    <?php echo $this->Form->input('misalignment_sensor_upper', array('name'=>'misalignment_sensor_upper','type' => 'select', 'default'=>$tabIdler['misalignment_sensor_upper'],'label' => false,'options' => $conveyor_us_config['idlers']['misalignment_sensor_upper'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Misalignment sensors lower',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('misalignment_sensor_lower', array('name'=>'misalignment_sensor_lower','type' => 'select', 'default'=>$tabIdler['misalignment_sensor_lower'],'label' => false,'options' => $conveyor_us_config['idlers']['misalignment_sensor_lower'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>


        </div>

        <!-- PULLEYS -->
        <div id="poleas" class="hidden scrollable-tab">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Drive pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="drive_pulley_width" value="<?php echo $tabPulley['drive_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Lagging Thickness',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="lagging_thickness" value="<?php echo $tabPulley['lagging_thickness']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('motriz_surface_condition', array('name'=>'motriz_surface_condition','default'=>$tabPulley['motriz_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['drive_surface'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Brake device?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('brake_device', array('name'=>'brake_device','default'=>$tabPulley['brake_device'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['brake_device'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Head pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_width" value="<?php echo $tabPulley['head_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('head_lagging_type', array('name'=>'head_lagging_type','default'=>$tabPulley['head_lagging_type'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('head_surface_condition', array('name'=>'head_surface_condition','default'=>$tabPulley['head_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Tail pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_width" value="<?php echo $tabPulley['tail_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('tail_lagging_type', array('name'=>'tail_lagging_type','default'=>$tabPulley['tail_lagging_type'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('tail_surface_condition', array('name'=>'tail_surface_condition','default'=>$tabPulley['tail_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Snub pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="snub_pulley_diameter" value="<?php echo $tabPulley['snub_pulley_diameter']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="snub_pulley_width" value="<?php echo $tabPulley['snub_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('snub_lagging_type', array('name'=>'snub_lagging_type','default'=>$tabPulley['snub_lagging_type'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('snub_surface_condition', array('name'=>'snub_surface_condition','default'=>$tabPulley['snub_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Bend pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="bend_pulley_diameter" value="<?php echo $tabPulley['bend_pulley_diameter']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="bend_pulley_width" value="<?php echo $tabPulley['bend_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('bend_lagging_type', array('name'=>'bend_lagging_type','default'=>$tabPulley['bend_lagging_type'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('bend_surface_condition', array('name'=>'bend_surface_condition','default'=>$tabPulley['bend_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Takeup pulley",true); ?></div></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="takeup_pulley_width" value="<?php echo $tabPulley['takeup_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Lagging type',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('takeup_lagging_type', array('name'=>'takeup_lagging_type','default'=>$tabPulley['takeup_lagging_type'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['lagging_type'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Surface condition',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <?php echo $this->Form->input('takeup_surface_condition', array('name'=>'takeup_surface_condition','default'=>$tabPulley['takeup_surface_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['pulleys']['surface_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label"></div>
                <div class="column-ctrl last"></div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Additional pulley 1",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="add1_pulley_diameter" value="<?php echo $tabPulley['add1_pulley_diameter']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="add1_pulley_width" value="<?php echo $tabPulley['add1_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Additional pulley 2",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Diameter (in)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="add2_pulley_diameter" value="<?php echo $tabPulley['add2_pulley_diameter']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Width (in)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <input type="text" name="add2_pulley_width" value="<?php echo $tabPulley['add2_pulley_width']; ?>" class='validate[custom[positive_number]]'/>
                </div>
            </div>
        </div>

        <div id="zona_transicion" class="hidden">
            <div class="slide-form-title"></div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Tail transition",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Pulley lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="tail_pulley_lift" value="<?php echo $tabTransitionZone['tail_pulley_lift']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Condition (pressure on outer idlers)',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('pressure_outer_idlers', array('name'=>'pressure_outer_idlers','default'=>$tabTransitionZone['pressure_outer_idlers'],'type' => 'select','label' => false,'options' => $conveyor_us_config['transition']['pressure_outer_idlers'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>

            <div class="conveyor-ctrls no-margin"><div class="title-section"><?php echo __("Head transition",true); ?></div></div>
            <div class="conveyor-ctrls">
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __d('forms','Pulley lift (ft)',true); ?></div>
                </div>
                <div class="column-ctrl">
                    <input type="text" name="head_pulley_lift" value="<?php echo $tabTransitionZone['head_pulley_lift']; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Material guidance?',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('material_guidance', array('name'=>'material_guidance','default'=>$tabTransitionZone['material_guidance'],'type' => 'select','label' => false,'options' => $conveyor_us_config['transition']['material_guidance'], 'data-placeholder'=>__('Select',true))); ?>
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
                    <?php echo $this->Form->input('maintenance_condition', array('name'=>'maintenance_condition','default'=>$tabRemark['maintenance_condition'],'type' => 'select','label' => false,'options' => $conveyor_us_config['remarks']['maintenance_condition'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
                <div class="column-label">
                    <div class="conveyor-label"><?php echo __('Overall status',true); ?></div>
                </div>
                <div class="column-ctrl last">
                    <?php echo $this->Form->input('overall_status', array('name'=>'overall_status','default'=>$tabRemark['overall_status'],'type' => 'select','label' => false,'options' => $conveyor_us_config['remarks']['overall_status'], 'data-placeholder'=>__('Select',true))); ?>
                </div>
            </div>
            <div class="full-controls">
                <div class="conveyor-label"><?php echo __('Additional remarks',true); ?></div>
            </div>
            <div class="full-controls">                
                <textarea name="remarks" class="textarea-form" placeholder=""><?php echo $tabRemark['remarks']; ?></textarea>
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
<?php
} else {
    echo json_encode($response);
}