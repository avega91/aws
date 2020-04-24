<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file dashboard.php
 *     View layer for action dashboard of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
//echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['gauge']}]}");?>
<style>
    #content {
        padding: 0 25px 25px !important;
    }
</style>
<div class="title-page buoys-section">
    <?php echo __('Buoy System',true); ?>
</div>
<?php if(in_array($role, array(UsuariosEmpresa::IS_CLIENT)) & 1==2): ?>
    <div class="full-page">
        <div class="data-page">
            <div class="info-section">
                <?php
                $company_img = $site;
                $company_img .= $empresa['path_image'] != '' ? $empresa['path_image'] : _DEFAULT_COMPANY_IMG;
                $company_img = '<img src="' . $company_img . '"/>';
                ?>
                <div class="image-info">
                    <?php echo $company_img; ?>
                </div>
                <div class="description-info">
                    <p class="title"><?php echo $empresa['name']; ?></p>
                    <p class="highlight">
                        <?php echo!is_null($corporativo['name']) ? $corporativo['name'] : ''; ?>
                    </p>
                    <p class="highlight">
                        <?php
                        $suspendida = $empresa['active'] == 0 ? '<span class="generic-disclaimer">' . __('Suspendida', true) . '</span>' : '';
                        echo $suspendida;
                        ?>
                    </p>
                </div>
                <div class="description-info">
                    <p>
                        <?php echo __('Region', true); ?>:<br/>
                        <?php echo __($empresa['region'],true); ?>
                    </p>
                </div>
                <div class="description-info">
                    <p>
                        <?php echo __('Distribuidor asociado', true); ?>:<br/>
                        <?php echo $distribuidor['name']; ?></p>
                </div>
                <div class="description-info">
                    <p>
                        <?php echo __('Corporativo del distribuidor', true); ?>:<br/>
                        <?php
                        $corp_dealer = $empresa_dealer["Corporativo"];
                        echo !is_null($corp_dealer['name']) ? $corp_dealer['name'] : '-';
                        ?>
                </div>
                <div class="description-info">
                    <p>
                        <?php echo __('Contacto Distribuidor', true); ?>:<br/>
                        <?php
                        if(!empty($usuario_dealer)) {
                            echo $usuario_dealer['name']."<br/>";
                            echo $usuario_dealer['phone'];
                        }else{
                            echo "-";
                        }
                        ?>
                    </p>
                </div>
                <div class="description-info">
                    <p>
                        <?php echo __('Contacto Ventas Conti', true); ?>:<br/>
                        <?php
                        if(!empty($usuario_admin)) {
                            echo $usuario_admin['name']."<br/>";
                            echo $usuario_admin['phone'];
                        }else{
                            echo "-";
                        }
                        ?>
                </div>
            </div>
            <div class="data-section" id="buoys_data"></div>
        </div>
    </div>
<?php else: ?>
    <div class="full-page" id="buoys_data">
    </div>
<?php endif; ?>

