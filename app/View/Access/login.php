<style>
    .tipsy a{
         color: #FFA500 !important;
     }
</style>
<div id="login_form_container">
    <div id="login_msgs"><?php echo $this->Session->flash(); ?></div>
    <form id="form_login" action="" method="post" autocomplete="off">
        <div id="user_div">                        
            <input type="text" id="user_conti" class="validate[required]" name="username" placeholder="<?php echo __('Usuario', true); ?>"/>
        </div>
        <hr class="login_separator"/>
        <div id="pass_div">
            <input type="password" id="pass_conti" class="validate[required]" name="password" placeholder="<?php echo __('Contraseña', true); ?>"/>
        </div>           
        <div id="captcha"> 
            <?php 
                $this->Captcha->init(array('battery','camera','car','flag','rocket'),'png','img/captcha/',35,30); 
                echo $this->Captcha->draw();
                ?> 
        </div>

        <div id="actions_login">
                <input type="submit" id="submit_login_on" class="submit_btn on" value=""/>                
                <input type="submit" id="submit_login_off" class="submit_btn off" value=""/>                
        </div>

        <?php echo $this->Html->link(__('¿Olvidó sus datos?', true),'#',array('id'=>'information_login','title'=>__('Si tiene problemas para entrar al panel, por favor repórtelo a login@contiplus.net', true))); ?>

        <div id="disclaimer_login" class="iefixable">                        
            <?php $link_terminos = $this->Html->link(__('Terminos de Servicio', true), array('controller' => 'General', 'action' => 'Terms')); ?>                        
            <?php $link_politica = $this->Html->link(__('Politica de Privacidad', true), array('controller' => 'General', 'action' => 'Privacy')); ?>                        
            <?php echo __('Al iniciar la sesion, estas de acuerdo con los <br>%s y %s', array($link_terminos,$link_politica)); ?>             
        </div>
              
    </form>
</div>  