<?php
/*
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file mobile_add.php
 *     view for mobile_add action controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */
?>
<style>
    #container{
        min-height: 400px !important;
        height: auto !important;
    }
    .cocoblocker{
        z-index: 1000 !important;
    }

    button{
        background-color: #FFA500;
        padding: 20px;
        line-height: 10px;
        color: #FFF;
        width: 100%;
        border: 1px solid #FFF;
    }

    .full-controls{
        margin: 10px 0 10px !important;
        padding: 0 !important;
    }
    .full-controls input{
        background: transparent !important;
        border: none;
        border-bottom: 2px solid #e1e4e1;
        color: #FFA500;
        width: 99%;
        padding: 2px 3px 2px 2px;
        font-family: Arial;
    }

    label.custom-select {
        position: relative;
        display: block;
        width: 100%;     
    }

    .custom-select select {
        color: #FFA500;
        width: 100%;        
        border: none;
        border-bottom: 2px solid #e1e4e1;

        background: transparent !important;
        display: block;
        padding: 4px 3px 3px 0px;
        margin: 0;
        font-family: Arial;
        outline:none; /* remove focus ring from Webkit */
        line-height: 1.2;


    }

    /* for Webkit's CSS-only solution */
    @media screen and (-webkit-min-device-pixel-ratio:0) { 
        .custom-select select {
            padding-right:30px;    
        }
    }


    /* Select arrow styling */
    .custom-select:after {
        content: "▼";
        position: absolute;
        top: -2px;
        right: 0;
        bottom: 0;
        font-size: 120%;
        line-height: 30px;
        padding: 0 5px;
        color: #FFA500;
        pointer-events:none;
    }

    .no-pointer-events .custom-select:after {
        content: none;
    }


    /* Safari 6.1+ (8.0 is the latest version of Safari at this time) */
    @media screen and (min-color-index:0) and(-webkit-min-device-pixel-ratio:0) { @media
                                                                                  {
                                                                                      .full-controls input  { 
                                                                                          /*padding-left: 4px !important;*/
                                                                                          /*width: 99%;*/
                                                                                      }
                                                                                      .custom-select:after {
                                                                                          font-size: 60%;
                                                                                      }
                                                                                  }}
    </style>
<?php
echo $this->Html->script("http://contiplus.net/js/application/Users/mobile_add.js?time".time());
?>
    <form id="add_user_form" action="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'saveSingleCompany')); ?>" class="fancy_form">  
    <div class="form-data">         
        <input type="hidden" name="user_type_txt" id="user_type_txt" value="client"/>        
        <input type="hidden" name="token" value="<?php echo $token; ?>"/>
        <?php $dist_id = $user['role'] == UsuariosEmpresa::IS_DIST ? $user['id_empresa'] : 0; ?>
        <div class="full-controls">
            <input type="hidden" name="all_distributors_txt" id="all_distributors_txt"/>
            <label class="custom-select"> 
                <select id="all_distributors" name="all_distributors" class="validate[required]">
                    <option value="" disabled selected><?php echo __('Distribuidor', true); ?></option>
                    <?php
                    if ($distributors) {
                        foreach ($distributors AS $distribuidor_reg) {
                            $empresa = $distribuidor_reg['Empresa'];
                            $selected = $dist_id > 0 ? 'selected="selected"' : '';
                            if($dist_id>0){
                                echo $empresa['id']==$dist_id ? '<option value="' . $empresa['id'] . '">' . utf8_encode($empresa['name']) . '</option>' : '';
                            }else{
                                echo '<option  value="' . $empresa['id'] . '">' . utf8_encode($empresa['name']) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </label>
        </div>      
        <div class="full-controls">
            <input type="hidden" name="user_region_txt" id="user_region_txt" value=""/>
            <label class="custom-select"> 
                <select id="user_region" name="user_region" class="validate[required] last-ctrl" disabled="disabled">
                    <option value="" disabled selected><?php echo __('Region', true); ?></option>
                </select>
            </label>
        </div>
        <div class="full-controls">
            <input type="text" placeholder="<?php echo __('Empresa', true); ?>" name="empresa" id="empresa" class="validate[required]"/>
        </div>
        <div class="full-controls">                
            <label class="custom-select"> 
                <select id="all_corporates" name="all_corporates" data-placeholder="<?php echo __('Corporativo', true); ?>">
                    <option value="" selected><?php echo __('Corporativo', true); ?></option>
                    <?php
                    if ($corporativos) {
                        foreach ($corporativos AS $corporativos_reg) {
                            $corporativo = $corporativos_reg['Corporativo'];
                            echo '<option value="' . $corporativo['id'] . '">' . utf8_encode($corporativo['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </label>
        </div>
        <div class="full-controls">
            <label class="custom-select"> 
                <select id="country_user" name="country_user" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Pais', true); ?>">
                    <option value="" disabled selected><?php echo __('Pais', true); ?></option>
                </select>
            </label>
        </div>
        <div class="full-controls">
            <label class="custom-select"> 
                <select id="state_user" name="state_user" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Estado', true); ?>">
                    <option value="" disabled selected><?php echo __('Estado', true); ?></option>
                </select>   
            </label>
        </div>        
        <div class="full-controls">
            <input type="text" placeholder="<?php echo __('Ciudad', true); ?>" name="ciudad" id="ciudad" class="validate[required]"/>
        </div>
        <div class="full-controls">
            <input type="text" placeholder="<?php echo __('Domicilio', true); ?>" name="direccion" id="direccion" class=""/>
        </div>
    </div>
    <div class="dialog-buttons">  
        <button type="button" id="save_user"><?php echo __('Guardar', true); ?></button>
    </div> 
</form>




