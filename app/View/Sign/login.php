<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file login.php
 * @description
 *
 * @date 04, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
?>
<style>
/*
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: inset 0px 0px 0px 9999px black !important;
        opacity: 0.4;
        -webkit-text-fill-color: white !important;
        transition: all 5000s ease-in-out 0s;
        transition-property: background-color, color;
    }*/


@-webkit-keyframes autofill {
    to {
        color: #fff;
        background: transparent;
        border-bottom: 2px solid #bdbdbd;
    }
}


.form-material.floating .form-control{
    color: #fff !important;
}
.fv-control-feedback ~ .floating-label {
    color: #fff !important;
}
.fv-control-feedback.glyphicon.glyphicon-ok ~ .floating-label {
    color: #bdbdbd !important;
}
.fv-control-feedback.glyphicon.glyphicon-remove ~ .floating-label {
    color: #f44336 !important;
}
/*
.form-material .form-control ~ .floating-label, .form-material .form-control:not(.focus) ~ .floating-label {
    color: #fff !important;
}*/
.form-material .form-control:focus ~ .floating-label, .form-material .form-control.focus ~ .floating-label {
    color: #ff9800 !important;
}

input:-webkit-autofill {
    -webkit-animation-name: autofill;
    -webkit-animation-fill-mode: both;
}

    .g-recaptcha > div {
        width: 100% !important;
    }

.loader-overlay {
    background: #fff !important;
}

.loader-content h2 {
    color: #fb8c00 !important;
}
.loader-index > div {
    background: #fb8c00 !important;
}

    .page-login form.modal-content {
        width: inherit !important;
        height: inherit !important;
    }

    .modal-body p{
        color: #757575 !important;
        text-align: left;
    }
</style>
<!--Code here-->
<form id="form_login" action="<?php echo $loginAction; ?>" method="post" autocomplete="off" class="form-horizontal fv-form fv-form-bootstrap4">
    <!--Set Flash -->
    <?php echo $this->Session->flash(); ?>
    <!--end flash msgs -->
    <input type="hidden" name="fingerprint" value=""/>
    <input type="hidden" name="force" value=""/>
    <div class="form-group form-material floating" data-plugin="formMaterial">
        <input type="text"  value="<?php echo $currentUser; ?>" class="form-control" id="inputUsername" name="username" required data-fv-notempty-message="<?php echo __('Username is required',true); ?>">
        <label class="floating-label" for="inputUsername"><?php echo __('Usuario', true); ?></label>
    </div>
    <div class="form-group form-material floating" data-plugin="formMaterial">
        <input type="password" class="form-control" value="<?php echo $currentPass; ?>" id="inputPassword" name="password" required data-fv-notempty-message="<?php echo __('Password is required',true); ?>">
        <label class="floating-label" for="inputPassword"><?php echo __('Contraseña', true); ?></label>
    </div>
    <div class="form-group clearfix">
        <!--<div class="checkbox-custom checkbox-inline checkbox-primary float-left">
            <input type="checkbox" id="inputCheckbox" name="remember">
            <label for="inputCheckbox">Remember me</label>
        </div>
        <a class="float-right" href="forgot-password.html">Forgot password?</a>
        -->
        <div class="g-recaptcha" data-sitekey="6LcuzOcSAAAAAK5BNdAmxE35H4G5IzgS84aTzzHI"></div>
    </div>
    <button type="submit" class="btn btn-primary btn-block" id="submitLogin">LOG IN</button>
</form>
<p>
    <?php $link_terminos = $this->Html->link(__('Terminos de Servicio', true), array('controller' => 'General', 'action' => 'Terms')); ?>
    <?php $link_politica = $this->Html->link(__('Politica de Privacidad', true), array('controller' => 'General', 'action' => 'Privacy')); ?>
    <?php echo __('Al iniciar la sesion, estas de acuerdo con los <br>%s y %s', array($link_terminos,$link_politica)); ?>
</p>

<!-- Modal msgs-->
<div class="modal fade" id="confirmUniqueDevice" aria-hidden="true" aria-labelledby="confirmUniqueDevice"
     role="dialog" tabindex="-1">
    <div class="modal-dialog modal-simple modal-center">
        <div class="modal-content">
            <div class="modal-body">
                <p><?php echo $fingerprintConfirmMsg; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-pure" data-dismiss="modal" id="canc"><?php echo __("Cancelar",true); ?></button>
                <button type="button" class="btn btn-primary" id="confirmDevice"><?php echo __("Confirmar",true); ?></button>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->