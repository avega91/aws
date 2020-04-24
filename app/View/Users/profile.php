<?php
/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file profile.php
 *     View layer for action profile of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
$secureUserParams = $this->Utilities->encodeParams($usuario['id']);
$secureCompanyParams = $this->Utilities->encodeParams($empresa['id']);

$editProfileAllow = isset($credentials['permissions'][IElement::Is_Profile]) && in_array('edit', $credentials['permissions'][IElement::Is_Profile]['allows']) ? true : false;
$editCompanyColaboratorAllow = isset($credentials['permissions'][IElement::Is_CompanyColaborator]) && in_array('edit', $credentials['permissions'][IElement::Is_CompanyColaborator]['allows']) ? true : false;

$canEdit = false;

//company is the same that current user and can edit his own profile
if($credentials['id_empresa']==$empresa['id']){
    $canEdit = $editProfileAllow ? true : false;
}else{//is edition company/colaborator, check if user can
    $canEdit = $editCompanyColaboratorAllow ? true : false;
}

?>
<style>
    .slide-form-section {
        height: 360px;
    }

    .chosen-container-single .chosen-single span{
        color: #7f7f7f;
        font-family: "sanslight";
        font-size: 16px;
        text-align: left;
    }
    .chosen-container-single .chosen-single {
        font-family: "sansbook";
        padding: 0px !important;
    }

    .puesto-profile {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .field-form{
        position: relative;
        padding: 5px 0px 5px;
    }
    .field-form > .input.select {
        margin: 10px 0 10px;
    }
    .field-form > span{
        position: absolute;
        top: 3px;
        left: 0;
        font-size: 12px;
    }
    .field-form > p{
        color: #7f7f7f;
        font-family: "sanslight",Helvetica,Arial,sans-serif;
        font-size: 16px;
        margin-bottom: 0px;
        margin-top: 0px;
    }
    .field-form > span ~ p{
        margin-top: 14px;
    }
    .field-form > span ~ .chosen-container, .field-form > span ~ .input.select{
        margin-top: 14px;
    }

    .field-form > input[type="text"]{
        cursor: text;
        border-bottom: 1px solid transparent;
    }
    .field-form > input[type="text"]:focus{
        border-bottom: 1px solid #FFA500 !important;
    }
</style>

<!--
<div id='slide_form'>
    <?php //if(!in_array($credentials['role'], [UsuariosEmpresa::IS_CLIENT])) : ?>
     <a rel="slide_empresa" class=""><?php echo __('Empresa', true); ?></a>
    <?php //endif; ?>
     <a rel="slide_usuario" class="active"><?php echo __('Usuario', true); ?></a>
</div>-->
<form id="info_profile_form" action="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'update')); ?>"> <!--  class="labelPattern go-right">-->
    <div class='slide-form-section'>
        <div id='slide_empresa' class='hidden'>
            <input type="hidden" name="tokencompany" value="<?php echo $secureCompanyParams['item_id'] . '|' . $secureCompanyParams['digest']; ?>"/>            
            <div id="frame-logo-empresa" class="profile-img">        
                <div id="profile-company" class="profile-company" title="<?php echo __('Logo de empresa', true); ?>">
                    <?php
                    $logo_empresa = 'img/img_company_off.png';
                    $path_img_empresa = '';
                    if (file_exists($empresa['path_image'])) {
                        $path_img_empresa = $logo_empresa = $empresa['path_image'];
                    }
                    ?>
                </div>    
            </div>
            <input type="hidden" id="path_logo_empresa" value="<?php echo $logo_empresa; ?>"/>            
            <input type="hidden" id="logo_company_hidden" name="logo-company-hidden" value="<?php echo $path_img_empresa; ?>"/>    
            <div class="frame-profile-info">
                <div class="title-profile">
                    <span><?php echo utf8_encode($empresa['name']); ?></span>           
                </div>
                <div class="puesto-profile" title="<?php echo utf8_encode($corporativo); ?>">
                    <span><?php echo utf8_encode($corporativo); ?></span>
                </div>
                <div class="space"></div>
                <?php if($usuario["role"]==UsuariosEmpresa::IS_CLIENT): ?>
                <div class="field-form">
                    <span><?php echo __('Salesperson', true); ?></span>
                    <?php
                    asort($settings["salesperson"]);
                    echo $this->Form->input('salesperson', array('name' => 'salesperson','type' => 'select','label' => false,'options' => $settings["salesperson"], 'default'=>$empresa["salesperson_id"],'data-placeholder'=>__('Salesperson',true)));
                    ?>
                </div>
                <?php endif; ?>
                <div class="field-row field-form">
                    <span><?php echo __('Direccion', true); ?></span>
                    <input type="text" required class="inline-edit-txt" name="address-company" id="address-company" value="<?php echo utf8_encode($empresa['address']); ?>" placeholder="-"/>
                    <!--<label for="address-company"><?php echo __('Direccion', true); ?></label>-->
                </div>

                <div class="address-profile field-form">
                    <span><?php echo __('Ciudad', true); ?></span>
                    <input type="text"  class="inline-edit-txt " name="city-company" value="<?php echo utf8_encode($empresa['city']); ?>" placeholder="-"/>
                    <!--<label for="city-company"><?php echo __('Ciudad', true); ?></label>-->
                </div>
                <div class="field-row field-form">
                    <span><?php echo __('Region', true); ?></span>
                    <p><?php echo utf8_encode($region['name']); ?></p>
                </div>
                <!--<div class="address-profile field-form"><div><?php echo $usuario['state']; ?></div></div>
                <div class="address-profile field-form"><div><?php echo __($usuario['country'], true); ?></div></div>
                <div class="address-profile field-form"><div><?php echo __('Region', true); ?>: <?php echo utf8_encode($region['name']); ?></div></div>-->
            </div>
        </div>
        <div id='slide_usuario' class='active_section'>
            <input type="hidden" name="tokenuser" value="<?php echo $secureUserParams['item_id'] . '|' . $secureUserParams['digest']; ?>"/>                        
            <div id="frame-profile-picture" class="profile-img">        
                <div id="profile-picture" class="profile-picture" title="<?php echo __('Imagen de perfil', true); ?>">
                    <?php
                    $profile_image = 'img/img_user_off.png';
                    $path_img_usuario = '';
                    //if (file_exists($usuario['path_image'])) {
                    if (!is_null($usuario['image'])) {
                        $image_name = 'userimage'.$usuario['id'];
                        $path_to_save_image = _ABSOLUTE_PATH.'uploads/tmpfiles/'.$image_name.'.jpg';
                        @unlink($path_to_save_image);//Eliminamos imagenes previas

                        $path_image = 'uploads/tmpfiles/'.$image_name.'.jpg';//
                        file_put_contents($path_to_save_image, $usuario['image']);//Escribimos una nueva imagen para poder desplegarla
                        $usuario['path_image'] = $path_image;
                        
                        $path_img_usuario = $profile_image = $usuario['path_image'];
                    }

                    ?>
                </div>

                <!--<div class="<?php if(!$isUsUser || $credentials['i_group_id']<IGroup::TERRITORY_MANAGER): ?> hidden <?php endif; ?>">
                        <div class="checkbox-ctrl">
                            <span id="is_professional" class="<?php if($usuario['type']=='pro'): ?> active <?php endif; ?>"><?php echo __('Professional user', true); ?></span>
                        </div>
                </div>-->
            </div>
            <input type="hidden" id="path_pic_profile" value="<?php echo $profile_image; ?>"/>
            <input type="hidden" id="profile_picture_hidden" name="profile-picture-hidden" value="<?php echo $path_img_usuario; ?>"/>    
            <div class="frame-profile-info">
                <div class="title-profile field-row field-form" title="<?php echo utf8_encode($usuario['name']); ?>">
                    <span><?php echo __('Nombre', true); ?></span>
                    <input type="text" required class="inline-edit-txt validate[required]" name="fullname" id="fullname" value="<?php echo utf8_encode($usuario['name']); ?>" placeholder="-"/>
                    <!--<label for="fullname"><?php echo __('Nombre', true); ?></label>-->
                </div>
                <!--<div class="puesto-profile field-row">
                    <input type="text" required class="inline-edit-txt validate[required]" id="puesto" name="puesto" value="<?php echo utf8_encode($usuario['puesto']); ?>"/>
                    <label for="puesto"><?php echo __('Puesto', true); ?></label>
                </div>-->
                <div class="field-row field-form">
                    <span><?php echo __('Puesto', true); ?></span>

                    <select id="puesto" name="puesto" class="" data-placeholder="<?php echo __('Puesto', true); ?>" style="width: 100%;">
                        <option value=""></option>
                        <?php
                        if (!empty($companyRoles)) {
                            foreach ($companyRoles AS $companyRole) {
                                $role = $companyRole['CompanyRole'];
                                $selected = $usuario['puesto'] == $role['id'] ? 'selected="selected"' : '';
                                echo '<option value="' . $role['id'] . '" '.$selected.'>' . utf8_encode($role['name_role']) . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <!--<input type="text" required class="inline-edit-txt" name="puesto" id="puesto" value="<?php echo utf8_encode($usuario['puesto']); ?>" placeholder="-"/>
                    <input type="hidden" id="puesto" name="puesto" value="<?php echo utf8_encode($usuario['puesto']); ?>"/>-->
                </div>

                <div class="field-row field-form">
                    <span><?php echo __('Email', true); ?></span>
                    <input type="text" required class="inline-edit-txt validate[required,custom[email]]" name="email" id="email" value="<?php echo utf8_encode($usuario['email']); ?>" placeholder="-"/>
                    <!--<label for="email"><?php echo __('Email', true); ?></label>-->
                </div>
                <div class="field-row field-form">
                    <span><?php echo __('Telefono', true); ?></span>
                    <input type="text" required class="inline-edit-txt" name="phone" id="phone" value="<?php echo utf8_encode($usuario['phone']); ?>" placeholder="-"/>
                    <!--<label for="phone"><?php echo __('Telefono', true); ?></label>-->
                </div>
                <!--<div class="field-row field-form">
                    <p><b><?php echo __('Username:', true); ?></b> <?php echo utf8_encode($usuario['username']); ?></p>
                </div>-->
                <div class="field-row field-form">
                    <span><?php echo __('Username', true); ?></span>
                    <p><?php echo utf8_encode($usuario['username']); ?></p>
                </div>

                <div class="field-row field-form">
                    <span><?php echo __('Permissions', true); ?></span>
                    <p>
                        <?php
                        $assocPermission = [
                            100 => __('Master/Engineer',true),
                            95 => __('Market manager',true),
                            90 => __('Country manager',true),
                            80 => __('Region manager',true),
                            60 => __('Territory manager',true),
                            50 => __('Corporate manager',true),
                            45 => __('Distributor user',true),
                            40 => __('Distributor admin',true),
                            30 => __('Customer admin',true),
                            20 => __('Customer user',true),
                        ];
                        echo $assocPermission[$usuario['i_group_id']];
                        ?>
                    </p>
                </div>

                <?php
                //switch ($role) {
                switch($usuario["role"]){
                    case 'admin':
                        ?>
                        <div class="field-row field-form">
                            <span><?php echo __('No. Empleado', true); ?></span>
                            <input type="text" required class="inline-edit-txt" name="no_empleado_a" id="no_empleado_a" value="<?php echo $usuario['no_empleado_a']; ?>" placeholder="-"/>
                            <!--<label for="no_empleado_a"><?php echo __('No. Empleado', true); ?></label>-->
                        </div>
                        <div class="field-row field-form">
                            <span><?php echo __('Unidad de negocio', true); ?></span>
                            <input type="text" required class="inline-edit-txt" name="unidad_negocio_a" id="unidad_negocio_a" value="<?php echo $usuario['unidad_negocio_a']; ?>" placeholder="-"/>
                            <!--<label for="unidad_negocio_a"><?php echo __('Unidad de negocio', true); ?></label>-->
                        </div>
                        <?php
                        break;
                    case 'distributor':
                        ?>
                        <div class="field-row field-form">
                            <span><?php echo __('Zona', true); ?></span>
                            <p><?php echo utf8_encode($usuario['zona_d']); ?></p>
                        </div>
                        <!-- <div class="field-row field-form">
                            <span><?php echo __('Atendido por', true); ?></span>
                            <?php
                                // $atendido = !empty($salesperson) ? $salesperson['UsuariosEmpresa']['name'] : $atendio['name'];
                            ?>
                            <p><?php // echo utf8_encode($atendido); ?></p>
                        </div> -->
                        <?php
                    break;
                    case 'client':
                         ?>
                        <div class="field-row field-form">
                            <span><?php echo __('Area', true); ?></span>
                            <input type="text" required class="inline-edit-txt" name="area_c" id="area_c" value="<?php echo $usuario['area_c']; ?>" placeholder="-"/>
                            <!--<input type="text" readonly="readonly" class="inline-edit-txt" name="area_c" id="area_c" value="<?php echo $usuario['area_c']; ?>" placeholder="-"/>
                            <label for="area_c"><?php echo __('Area', true); ?></label>-->
                        </div>
                        <div class="field-row field-form">
                            <span><?php echo __('Industria', true); ?></span>
                            <p><?php echo utf8_encode($industria['name_en']); ?></p>
                        </div>
                        <?php
                    break;
                }
                ?>
            </div>
        </div>
    </div>

    <div class="dialog-buttons normal-dialog-btns">  
        <section>
            <?php if($canEdit): ?>
            <button type="button" id="save_own_profile" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
            <?php endif; ?>
        </section>
    </div>

</form>