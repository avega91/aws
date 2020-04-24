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
        min-height: 445px !important;
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
    $roles = [
        IGroup::CLIENT => 'client',
        IGroup::DISTRIBUTOR =>  'distributor',
        IGroup::TERRITORY_MANAGER => 'admin',
        IGroup::REGION_MANAGER => 'region_manager',
        IGroup::COUNTRY_MANAGER =>'country_manager',
        IGroup::MARKET_MANAGER =>'market_manager',
        IGroup::MASTER => 'master'
    ];

    //group id for company of user
    $group_id_company = $company['Empresa']['group_bucket_id'];


    //special permission for clients and distributors
    $specialPermissions = [
        IGroup::CLIENT => [
            '' => '',
            IGroup::CLIENT => __("Customer User",true),
            IGroup::CLIENT_MANAGER => __("Customer Admin",true)//__("Corporate manager",true)
        ],
        IGroup::DISTRIBUTOR  => [
            '' => '',
            IGroup::RUBBER_DISTRIBUTOR => __("Distributor User",true),
            IGroup::DISTRIBUTOR => __("Distributor Admin", true),
            ///IGroup::DISTRIBUTOR_MANAGER => __("Distributor manager",true),
        ]
    ];

?>
<form id="add_user_form" action="<?php echo $this->Html->url(array('controller'=>'Companies','action'=>'saveColaborator')); ?>" class="fancy_form">
    <div class='slide-form-section'>
        <div id='slide_usuario' class='active_section'>
            <div id="logo_usuario" title="<?php echo __('Agregar foto de usuario', true); ?>"></div>
            <div class="form-data">
                <input type="hidden" name="company_id" value="<?php echo $company['Empresa']['id']; ?>">
                <input type="hidden" name="country" value="<?php echo $company['CountryCompany']['name']; ?>">
                <input type="hidden" name="country_id" value="<?php echo $company['CountryCompany']['id']; ?>">
                <input type="hidden" name="region" value="<?php echo $company['Empresa']['region']; ?>">
                <input type="hidden" name="role" value="<?php echo $roles[$group_id_company]; ?>">

                <?php if(in_array($group_id_company, [IGroup::CLIENT, IGroup::DISTRIBUTOR])): ?>
                    <div class="full-controls">
                        <?php echo $this->Form->input('i_group_id', array('name' => 'i_group_id','type' => 'select','label' => false, 'class' => 'validate[required]', 'options' => $specialPermissions[$group_id_company], 'data-placeholder'=>__('Choose Permissions',true))); ?>
                        <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="i_group_id" value="<?php echo $company['Empresa']['group_bucket_id']; ?>">
                <?php endif; ?>
                <div class="full-controls">
                    <input type="text" placeholder="<?php echo __('Nombre completo', true); ?>" name="name" id="nombre" class="validate[required]"/>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="two-controls">
                    <input type="text" placeholder="<?php echo __('Email', true); ?>" name="email" id="email" class="validate[required,custom[email]]"/>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="two-controls last-ctrl">
                    <input type="text" placeholder="<?php echo __('Telefono', true); ?>" name="telefono" id="telefono" class=""/>
                </div>
                <div class="full-controls">
                    <select id="puesto" name="puesto" class="" data-placeholder="<?php echo __('Choose Role', true); ?>" style="width: 100%;">
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

                <?php
                    switch ($group_id_company):
                        case IGroup::CLIENT: ?>
                            <div class="full-controls">
                                <input type="text" placeholder="<?php echo __('Area', true); ?>" name="area" id="area" class=""/>
                            </div>
                            <div class="full-controls">
                                <select id="industria" name="industria" class="" data-placeholder="<?php echo __('Tipo de industria', true); ?>">
                                    <option value=""></option>
                                    <?php
                                    foreach ($industrias AS $industria) {
                                        $industria = $industria['TipoIndustria'];
                                        echo '<option value="' . $industria['id'] . '">' . __($industria['name'],true) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php
                        break;
                        case IGroup::DISTRIBUTOR: ?>


                            <?php
                        break;
                        case IGroup::TERRITORY_MANAGER:case IGroup::REGION_MANAGER:case IGroup::COUNTRY_MANAGER: ?>
                            <div class="full-controls">
                                <input type="text" placeholder="<?php echo __('No. empleado', true); ?>" name="no_empleado" id="no_empleado" class=""/>
                            </div>
                            <div class="full-controls">
                                <input type="text" placeholder="<?php echo __('Unidad de Negocio', true); ?>" name="unidad_negocio" id="unidad_negocio" class=""/>
                            </div>
                        <?php
                        break;
                    endswitch;
                ?>

                <div class="two-controls">
                    <input type="text" placeholder="<?php echo __('Contrasena', true); ?>" name="password" id="password" class="validate[required]"/>
                    <span class="required_field in-modal tooltiped" title="<? echo __('Required', true); ?>">*</span>
                </div>
                <div class="two-controls last-ctrl">
                    <button type="button" id="pass_gen" class="active"><?php echo __('Generar contrasena', true); ?></button>
                </div>
                <div class="full-controls">
                    <div id="disclaimer_password"><?php echo __("Click this button for generating a secure password",true); ?></div>
                </div>

                <div class="full-controls">
                    <p class="disclaimer-fields-required"><?php echo __('Fields marked with * are required',true); ?></p>
                </div>

                <input type="hidden" name="is_us_company" value="<?php echo $isUsUser; ?>"?>
                <?php //if(in_array($group_id_company, [IGroup::CLIENT, IGroup::DISTRIBUTOR]) && $isUsUser):
                if($isUsUser):
                ?>
                <div class="two-controls">
                    <div class="checkbox-ctrl">
                        <span id="is_professional"><?php echo __('Professional user', true); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <input type="hidden" id="path_logo_usuario" name="path_logo_usuario"/>
    </div>
    <div class="dialog-buttons" data-section="14" data-intro="<?php echo __('tutorial_guardar_usuario_agregar_cliente',true);?>" data-position="bottom">
        <section>
                <button type="button" id="save_user" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar Usuario', true); ?></button>
        </section>
    </div>
</form>
<?php else: ?>
    <div class=""><?php echo __("Error al recuperar la informacion",true);?></div>
<?php endif; ?>
