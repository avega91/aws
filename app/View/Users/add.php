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
$brands = array('ContiHeritage' => 'ContiTech Heritage', 'ContiSelect' => 'ContiTech Select');
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
    #puesto_chosen{
        padding-top: 12px !important;
    }
</style>
<!--
<div id='slide_form'>    
    <div>
        <?php if($typeAdd=="company" || $typeAdd==""): ?>
            <a rel="slide_empresa" class="active"><?php echo __('Empresa', true); ?></a>
        <?php endif; ?>
        <?php if($typeAdd=="user" || $typeAdd==""): ?>
            <a rel="slide_usuario" class="" data-section="14" data-intro="<?php echo __('tutorial_link_usuario_agregar_cliente',true);?>" data-position="right"><?php echo __('Usuario', true); ?></a>
        <?php endif; ?>
    </div>
</div>-->
<form id="add_user_form" action="<?php echo $this->Html->url(array('controller'=>'Users','action'=>'save')); ?>" class="fancy_form">  
    <div class='slide-form-section'>
        <?php if($typeAdd=="company" || $typeAdd==""): ?>
        <div id='slide_empresa' class='active_section'>
            <div id="logo_empresa" title="<?php echo __('Agregar logo de empresa', true); ?>"></div>
            <div class="form-data">
                <div id="zone_admin">zona</div>
                <?php echo $this->Content->putDropdowns_TipoUsuario($regions); ?>                                
                <div class="full-controls" data-section="14" data-intro="<?php echo __('tutorial_empresa_agregar_cliente',true);?>" data-position="right">
                    <!--<select id="empresa" name="empresa" class="validate[required]" data-placeholder="<?php echo __('Empresa o Planta', true); ?>" rel="<?php echo $this->Html->url(array('controller'=>'Ajax','action'=>'ax_get_company')); ?>" disabled="disabled"></select>-->
                    <input type="text" placeholder="<?php echo __('Empresa o Planta', true); ?>" name="empresa_txt" id="empresa_txt"/>
                </div>                
                <div class="full-controls hidden">
                    <select id="brand" name="brand" class="validate[required]" data-placeholder="<?php echo __('Marca', true); ?>">
                        <option value=""></option>
                        <?php
                        if (!empty($brands)) {
                            foreach ($brands AS $brand_id => $brand) {                                
                                echo '<option value="' . $brand_id . '">' . $brand . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="full-controls" data-section="14" data-intro="<?php echo __('tutorial_corporativo_agregar_cliente',true);?>" data-position="left">
                    <select id="corporativo" name="corporativo" class="" data-placeholder="<?php echo __('Corporativo', true); ?>" disabled="disabled"></select>
                </div>
                <div class="full-controls hidden">
                    <?php
                    asort($settings["salesperson"]);
                    echo $this->Form->input('salesperson', array('name' => 'salesperson','type' => 'select','label' => false,'options' => $settings["salesperson"], 'data-placeholder'=>__('Salesperson',true)));
                    ?>
                </div>
                <div data-section="14" data-intro="<?php echo __('tutorial_otros_campos_agregar_cliente',true);?>" data-position="bottom">
                    <div class="two-controls">
                        <select id="country_user" name="country_user" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Pais', true); ?>">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="two-controls last-ctrl">
                        <select id="state_user" name="state_user" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Estado', true); ?>">
                            <option value=""></option>
                        </select>   
                    </div>
                    <div class="full-controls">
                        <!--<input type="text" placeholder="<?php echo __('Ciudad', true); ?>" name="ciudad" id="ciudad" class="validate[required]"/>-->
                        <select id="ciudad" name="ciudad" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Ciudad', true); ?>">
                            <option value=""></option>
                        </select>
                    </div>
                    <div class="full-controls">
                        <input type="text" placeholder="<?php echo __('Domicilio', true); ?>" name="direccion" id="direccion" class=""/>
                    </div>
                    <!--<div class="two-controls distributor-field user-field hidden">
                        <input type="text" placeholder="<?php echo __('SAP number', true); ?>" name="sap_number" id="sap_number" readonly class=""/>
                    </div>
                    <div class="two-controls distributor-field user-field hidden last-ctrl">
                        <input type="text" placeholder="<?php echo __('Salesforce Id', true); ?>" name="salesforce_number" readonly id="salesforce_number" class=""/>
                    </div>-->
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($typeAdd=="user" || $typeAdd==""): ?>
        <div id='slide_usuario' class='active_section'>
            <div id="logo_usuario" title="<?php echo __('Agregar foto de usuario', true); ?>"></div>
            <div class="form-data">
                <div id="zone_admin">zona</div>
                <div class="hidden">
                    <?php echo $this->Content->putDropdowns_TipoUsuario($regions); ?>
                </div>
                <div class="full-controls hidden" data-section="14" data-intro="<?php echo __('tutorial_empresa_agregar_cliente',true);?>" data-position="right">
                    <select id="empresa" name="empresa" class="validate[required]" data-placeholder="<?php echo __('Empresa o Planta', true); ?>" rel="<?php echo $this->Html->url(array('controller'=>'Ajax','action'=>'ax_get_company')); ?>" disabled="disabled"></select>
                    <input type="hidden" name="empresa_txt" id="empresa_txt"/>
                </div>
                <div class="hidden">
                    <select id="country_user" name="country_user" class="validate[required]" data-placeholder="<?php echo __('Pais', true); ?>">
                        <option value=""></option>
                    </select>
                    <select id="state_user" name="state_user" class="validate[required]" data-placeholder="<?php echo __('Estado', true); ?>">
                        <option value=""></option>
                    </select>
                </div>


                <div class="full-controls">
                    <input type="text" placeholder="<?php echo __('Nombre completo', true); ?>" name="nombre" id="nombre" class="validate[required]"/>
                </div>
                <div class="two-controls">
                    <input type="text" placeholder="<?php echo __('Email', true); ?>" name="email" id="email" class="validate[required,custom[email]]"/>
                </div>
                <div class="two-controls last-ctrl">
                    <input type="text" placeholder="<?php echo __('Telefono', true); ?>" name="telefono" id="telefono" class=""/>
                </div>                
                <div class="full-controls">
                    <!--<input type="text" placeholder="<?php echo __('Puesto', true); ?>" name="puesto" id="puesto" class=""/>-->
                    <select id="puesto" name="puesto" class="" data-placeholder="<?php echo __('Puesto', true); ?>" style="width: 100%;">
                        <option value=""></option>
                        <?php
                        if (!empty($companyRoles)) {
                            foreach ($companyRoles AS $companyRole) {
                                $role = $companyRole['CompanyRole'];
                                echo '<option value="' . $role['id'] . '">' . utf8_encode($role['name_role']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <?php if($credentials['group_id']>Group::MANAGER && 1==2){ ?>
                <div class="two-controls last-ctrl hidden">
                    <div class="checkbox-ctrl">
                        <span class="inactive" id="user_manager_ctrl" title="<?php echo __('Funcion exclusiva para usuarios asociados a un corporativo'); ?>"><?php echo __('Manager', true); ?></span>
                    </div>
                </div>
                <?php } ?>
                <div class="full-controls admin-field user-field hidden">
                    <input type="text" placeholder="<?php echo __('No. empleado', true); ?>" name="no_empleado" id="no_empleado" class=""/>
                </div>
                <div class="full-controls admin-field user-field hidden">
                    <input type="text" placeholder="<?php echo __('Unidad de Negocio', true); ?>" name="unidad_negocio" id="unidad_negocio" class=""/>
                </div>
                <div class="full-controls distributor-field user-field hidden">
                    <input type="text" placeholder="<?php echo __('Zona', true); ?>" name="zona" id="zona" class="validate[required]"/>
                </div>
                <div class="full-controls distributorxxx-field userxxx-field hidden">
                    <select id="atiende" name="atiende" class="validate[required]" data-placeholder="<?php echo __('Atendido por', true); ?>">
                        <option value=""></option>
                        <?php
                        foreach ($personasAtencion AS $persona) {
                            $persona = $persona['AtencionPersona'];
                            echo '<option value="' . $persona['id'] . '">' . $persona['name'] . '</option>';
                        }
                        ?>
                    </select> 
                </div>
                 <div class="full-controls client-field user-field hidden">
                    <input type="text" placeholder="<?php echo __('Area', true); ?>" name="area" id="area" class="validate[required]"/>
                </div>
                <div class="full-controls client-field user-field hidden">                     
                    <select id="industria" name="industria" class="validate[required]" data-placeholder="<?php echo __('Tipo de industria', true); ?>">
                        <option value=""></option>
                        <?php
                        foreach ($industrias AS $industria) {
                            $industria = $industria['TipoIndustria'];
                            echo '<option value="' . $industria['id'] . '">' . __($industria['name'],true) . '</option>';
                        }
                        ?>
                    </select> 
                </div>
                <div class="two-controls">
                    <input type="text" placeholder="<?php echo __('Contrasena', true); ?>" name="password" id="password" class="validate[required]"/>
                </div>
                <div class="two-controls last-ctrl">
                    <button type="button" id="pass_gen" class="active"><?php echo __('Generar contrasena', true); ?></button>
                </div>
                <div class="full-controls">
                    <div id="disclaimer_password"><?php echo __("Click this button for generating a secure password",true); ?></div>
                </div>
                <div class="two-controls <?php if(!$isUsUser): ?> hidden <?php endif; ?>">
                    <div class="checkbox-ctrl">
                        <span id="is_professional"><?php echo __('Professional user', true); ?></span>
                    </div>
                </div>
                <div class="two-controls last-ctrl">
                </div>

            </div>
        </div>
        <?php endif; ?>
        <input type="hidden" id="path_logo_empresa" name="path_logo_empresa"/>
        <input type="hidden" id="path_logo_usuario" name="path_logo_usuario"/>
    </div>
    <div class="dialog-buttons" data-section="14" data-intro="<?php echo __('tutorial_guardar_usuario_agregar_cliente',true);?>" data-position="bottom">
        <section>
            <?php if($typeAdd=="company" || $typeAdd==""): ?>
                <button type="button" id="save_user_nouser" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar Sin Usuarios', true); ?></button>
            <?php endif; ?>
            <?php if($typeAdd=="user" || $typeAdd==""): ?>
                <button type="button" id="save_user" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar Usuario', true); ?></button>
            <?php endif; ?>
        </section>
    </div> 
</form>