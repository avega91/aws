<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file access_data.php
 *     View layer for action access data of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $secureUserParams = $this->Utilities->encodeParams($usuario['id']);
    ?>
    <form id="user_access_data_form" action="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'updateAccessData', $secureUserParams['item_id'], $secureUserParams['digest'])); ?>"  class="fancy_form">
        <div class="form-data">
            <div class="two-controls">
                <input type="text" placeholder="<?php echo __('Contrasena', true); ?>" name="password" id="password" class="validate[required]"/>
            </div>
            <div class="two-controls last-ctrl">
                <button type="button" id="pass_gen" class="active"><?php echo __('Generar contrasena', true); ?></button>
            </div>
            <div class="full-controls">
                <input type="text" placeholder="<?php echo __('Usuario', true); ?>" name="username" id="username" class="validate[required]" value="<?php echo $usuario['username']; ?>"/>
            </div>            
        </div>
        <div class="dialog-buttons normal-dialog-btns">  
            <section>
                <button type="button" id="update_access_data" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
            </section>
        </div> 
    </form>
    <?php
} else {
    
}
