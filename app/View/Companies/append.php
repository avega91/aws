<?php
/*
 * The Continental License
 * Copyright 2017  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add.php
 *     View layer for action add of controller
 *
 *     @project    Contiplus
 *     @author     ieialbertogd@gmail.com
 *     @date      2017
 */

if(!$error):
?>
<style>
    #add_user_form {
        min-height: 395px !important;
    }
    form .form-data {
        position: relative;
    }
    .dialog-buttons button {
        float: right;
        margin-left: 20px;
    }
    #save_user_nouser{
        font-size: 14px;
        width: 190px;
    }
    #save_user{
        font-size: 14px;
        width: 190px;
    }
    #zone_admin{
        position: absolute;
        right: 0;
        top: 38px;
        display: none;
        color: #EEB67A;
        font-family: "sansbook";
        font-size: 15px;
        width: 187px;
        z-index: 1;
        background: #f0f5eb;
    }
    #all_distributors_chosen{
        width: 103.5% !important;
    }
    .chosen-container{
        padding-top: 10px !important;
    }
</style>
<?php

    if($credentials['role_company']==UsuariosEmpresa::IS_DIST){
        $typeCompanies = [
            '' => '',
            IGroup::CLIENT => __("Cliente",true),
        ];
    }else{
        $typeCompanies = [
            '' => '',
            IGroup::CLIENT => __("Cliente",true),
            IGroup::DISTRIBUTOR => __("Distribuidor",true),
        ];
    }

?>
<form id="add_user_form" action="<?php echo $this->Html->url(array('controller'=>'Companies','action'=>'saveCompany')); ?>" class="fancy_form">
    <div class='slide-form-section'>
        <div id='slide_usuario' class='active_section'>
            <div id="logo_empresa" title="<?php echo __('Agregar logo de empresa', true); ?>"></div>
            <input type="hidden" name="name_territory" id="name_territory" value=""/>
            <div class="form-data">
                <div class="two-controls">
                    <?php echo $this->Form->input('type', array('name' => 'type', 'type' => 'select', 'disabled' => $selectedTypeCompany !=='', 'label' => false,'options' => $typeCompanies, 'default'=>$selectedTypeCompany,'data-placeholder'=>__('Choose Type of Company',true))); ?>
                    <?php if($selectedTypeCompany !==''): ?>
                        <input type="hidden" name="type" id="type" value="<?php echo $selectedTypeCompany; ?>"/>
                    <?php endif; ?>
                </div>
                <div class="two-controls last-ctrl">
                    <?php echo $this->Form->input('country', array('name' => 'country','type' => 'select','label' => false, 'disabled' => $selectedTypeCompany=='' ? true : false, 'options' => $countries,  'data-placeholder'=>__('Choose Country',true))); ?>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="two-controls dealer-field <?php if($selectedTypeCompany==IGroup::CLIENT || $selectedTypeCompany=='' || 1==1): ?> hidden <?php endif; ?>">
                        <select id="region" name="region" class="validate[required]" data-placeholder="Choose Region" disabled>
                        <option value=""></option>
                        <?php
                        if (!empty($regions)) {
                            foreach ($regions AS $region) {
                                echo '<option value="' . $region['id'] . '" data-country="'.$region['country_id'].'">' . $region['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="two-controls dealer-field <?php if($selectedTypeCompany==IGroup::CLIENT || $selectedTypeCompany=='' || 1==1): ?> hidden <?php endif; ?> last-ctrl">
                    <select id="territory" name="territory" class="validate[required]" data-placeholder="<?php echo __('Choose Territory', true); ?>" disabled>
                        <option value=""></option>
                        <?php
                        if (!empty($territories)) {
                            foreach ($territories AS $territory) {
                                echo '<option value="' . $territory['id'] . '" data-territory="'.$territory['code'].'" data-region="'.$territory['region_id'].'">' . $territory['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="full-controls client-field <?php if($selectedTypeCompany==IGroup::DISTRIBUTOR || $selectedTypeCompany==''): ?> hidden <?php endif; ?>">
                    <select id="distributor" name="distributor" class="validate[required]" data-placeholder="<?php echo __('Choose Distribuidor', true); ?>" disabled>
                        <option value=""></option>
                        <?php
                        if (!empty($dealers)) {
                            foreach ($dealers AS $dealer) {
                                $dealer = $dealer['Empresa'];
                                $selected = '';
                                //$selected = in_array($credentials['i_group_id'], [IGroup::DISTRIBUTOR, IGroup::RUBBER_DISTRIBUTOR, IGroup::DISTRIBUTOR_MANAGER]) && $dealer['id']==$credentials['id_empresa'] ? 'selected':'';
                                echo '<option '.$selected.' value="' . $dealer['id'] . '" data-country="'.$dealer['i_country_id'].'" data-region="'.$dealer['region_id'].'">' . utf8_encode($dealer['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="full-controls">
                    <input type="text" placeholder="<?php echo __('Company', true); ?>" name="name" id="nombre" autocomplete="off" class="validate[required]"/>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="full-controls closable-input-ctrl">
                    <select id="corporate" name="corporate" class="" data-placeholder="<?php echo __('Choose Corporate', true); ?>" <?php if($selectedTypeCompany==''): ?>disabled <?php endif; ?>>
                        <option value=""></option>
                        <option value="new"><?php echo __('Nuevo', true); ?></option>
                        <?php
                        if (!empty($corporates)) {
                            foreach ($corporates AS $corporate) {
                                $corporate = $corporate['Corporativo'];
                                $display = $selectedTypeCompany == IGroup::CLIENT && $corporate['type']=='distributor' ? 'style="display:none;"':'';
                                $display = $selectedTypeCompany == IGroup::DISTRIBUTOR && $corporate['type']=='client' ? 'style="display:none;"':'';
                                echo '<option '.$display.' value="' . $corporate['id'] . '" data-region="'.$corporate['region'].'" data-country="'.$corporate['country_id'].'" data-type="'.$corporate['type'].'">' . utf8_encode($corporate['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <input type="text" id="new_corporate" name="new-corporate" class='hidden'/><span id="close_new_corp" class="close-stick hidden"></span>
                </div>
                <div class="full-controls">
                    <select id="state" name="state" class="validate[required]" data-placeholder="<?php echo __('Choose State', true); ?>" disabled></select>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="full-controls">
                    <input type="text" placeholder="<?php echo __('Ciudad', true); ?>" name="city" id="city" autocomplete="off" class=""/>
                </div>
                <div class="full-controls">
                    <input type="text" placeholder="<?php echo __('Domicilio', true); ?>" name="direccion" id="direccion" autocomplete="off" class=""/>
                </div>
                <div class="full-controls">
                    <p class="disclaimer-fields-required"><?php echo __('Fields marked with * are required',true); ?></p>
                </div>
                <!--<div class="full-controls client-field <?php if($selectedTypeCompany==IGroup::DISTRIBUTOR): ?> hidden <?php endif; ?>">
                    <select id="salesperson" name="salesperson" class="" data-placeholder="<?php echo __('Salesperson', true); ?>" disabled>
                        <option value=""></option>
                        <?php
                        if (!empty($salespersons)) {
                            foreach ($salespersons AS $salesperson) {
                                $salesperson = $salesperson['UsuariosEmpresa'];
                                $skip = $credentials['puesto']==UsuariosEmpresa::IS_SALESPERSON && $salesperson['id']!=$credentials['id'] ? true : false;
                                if(!$skip){
                                    echo '<option value="' . $salesperson['id'] . '" data-region="'.$salesperson['region'].'">' . utf8_encode($salesperson['name']) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>-->
            </div>
        </div>
        <input type="hidden" id="path_logo_empresa" name="path_logo_empresa"/>
    </div>
    <div class="dialog-buttons" data-section="14" data-intro="<?php echo __('tutorial_guardar_usuario_agregar_cliente',true);?>" data-position="bottom">
        <section>
                <button type="button" id="save_company" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
        </section>
    </div>
</form>
<?php else: ?>
    <div class=""><?php echo __("Error al recuperar la informacion",true);?></div>
<?php endif; ?>
