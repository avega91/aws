<?php
/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file company_profile.php
 *     View layer for action company profile of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>

<style>
    .chosen-container-single .chosen-single span{
        color: #ff9900;
        text-align: left;
        font-size: 14px;
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
    .field-form > input[type="text"]:focus{
        background: #DFDFDF !important;
    }
</style>
<?php

$editCompanyColaboratorAllow = isset($credentials['permissions'][IElement::Is_CompanyColaborator]) && in_array('edit', $credentials['permissions'][IElement::Is_CompanyColaborator]['allows']) ? true : false;

if ($response['success']) {
    $secureCompanyParams = $this->Utilities->encodeParams($empresa['id']);
    ?>
    <form id="company_profile_form" action="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'updateCompany')); ?>">  <!--class="labelPattern go-right">-->
        <input type="hidden" name="tokencompany" value="<?php echo $secureCompanyParams['item_id'] . '|' . $secureCompanyParams['digest']; ?>"/>            
        <div id="frame-logo-empresa" class="profile-img">        
            <div id="profile-company" class="profile-company" title="<?php echo __('Logo de empresa', true); ?>">
                <?php
                $logo_empresa = 'img/img_company_off.png';
                $path_img_empresa = '';
                //if (file_exists($empresa['path_image'])) {
                if (!is_null($empresa['image'])) {
                    $image_name = 'profileimage'.$empresa['id'];
                    $path_to_save_image = _ABSOLUTE_PATH.'uploads/tmpfiles/'.$image_name.'.jpg';
                    @unlink($path_to_save_image);//Eliminamos imagenes previas

                    $path_image = 'uploads/tmpfiles/'.$image_name.'.jpg';//
                    file_put_contents($path_to_save_image, $empresa['image']);//Escribimos una nueva imagen para poder desplegarla
                    $empresa['path_image'] = $path_image;
                    $path_img_empresa = $logo_empresa = $empresa['path_image'];
                }
                ?>
            </div>    
        </div>
        <input type="hidden" id="path_logo_empresa" value="<?php echo $logo_empresa; ?>"/>            
        <input type="hidden" id="logo_company_hidden" name="logo-company-hidden" value="<?php echo $path_img_empresa; ?>"/>    
        <div class="frame-profile-info">
            <div class="title-profile">
                <?php if($empresa['type']!=UsuariosEmpresa::IS_ADMIN): ?>
                    <input type="text" required class="inline-edit-txt" name="company-name" id="company-name" value="<?php echo $empresa['name']; ?>" placeholder="-"/>
                <?php else: ?>
                    <span><?php echo $empresa['name']; ?></span>
                <?php endif; ?>
            </div>
            <div class="puesto-profile" title="<?php echo trim($corporativo['name']); ?>">
                <span><?php echo trim($corporativo['name']) ? $corporativo['name'] : '-'; ?></span>
            </div>
            <div class="space"></div>
            <?php if($empresa["type"]==UsuariosEmpresa::IS_CLIENT):// || $empresa["type"]==UsuariosEmpresa::IS_DIST): ?>
            <?php /*
            <div class="field-form">
                <span><?php echo __('Salesperson', true); ?></span>
                <select id="salesperson" name="salesperson">
                    <option value="0"><?php echo __("Select",true); ?></option>
                    <?php foreach($salespersonAssoc AS $salesperson):
                            $selected = $salesperson['UsuariosEmpresa']['id']==$empresa["salesperson_id"] ? 'selected' : '';
                    ?>
                        <option <?php echo $selected; ?> value="<?php echo $salesperson['UsuariosEmpresa']['id']; ?>"><?php echo $salesperson['UsuariosEmpresa']['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <!--<?php echo $this->Form->input('salesperson', array('name' => 'salesperson','type' => 'select','label' => false,'options' => $settings["salesperson"], 'default'=>$empresa["salesperson_id"],'data-placeholder'=>__('Salesperson',true))); ?>-->
            </div>
            */ ?>
            <?php endif; ?>

            <div class="field-row field-form">
                <span><?php echo __('Direccion', true); ?></span>
                <input type="text" required class="inline-edit-txt" name="address-company" id="address-company" value="<?php echo utf8_encode($empresa['address']); ?>" placeholder="-"/>
                <!--<label for="address-company"><?php echo __('Direccion', true); ?></label>-->
            </div>

            <div class="address-profile field-form">
                <span><?php echo __('Ciudad', true); ?></span>
                <input type="text" required class="inline-edit-txt" name="city-company" value="<?php echo utf8_encode($empresa['city']); ?>" placeholder="-"/>
                <!--<label for="city-company"><?php echo __('Ciudad', true); ?></label>-->
            </div>

            <div class="field-row field-form">
                <span><?php echo __('Region', true); ?></span>
                <p><?php echo __($empresa['region'],true); ?></p>
            </div>
            <!--
            <div class="description-info">
                <span><?php echo __('Region', true); ?>: <?php echo __($empresa['region'],true); ?></span>
            </div>-->
        </div>

        <div class="dialog-buttons normal-dialog-btns">  
            <section>
                <?php if($editCompanyColaboratorAllow): ?>
                <button type="button" id="save_company_profile" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
                <?php endif; ?>
            </section>
        </div>

    </form>
    <?php } else {
    ?>


<?php
}