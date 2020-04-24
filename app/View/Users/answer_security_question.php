<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file security_question.php
 * @description
 *
 * @date 04, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
?>
<!--Code here-->
<style>
    #answer_security_question_form{
        width: 750px;
        height: 400px;
        padding-top: 50px;
    }
    #fingerprint_disclaimer{
        margin-bottom: 50px;
    }
    h1 {
        font-family: "sanslight";
        font-size: 55px;
        font-weight: lighter;
        color: #A2A2A2 !important;
        margin: 25px 0 25px;
    }
    .chosen-container {
        width: 102% !important;
    }
    #user_question{
        font-weight: bold;
        font-size: 18px;
    }
    form.fancy_form input[type="password"]{
        background-color: #f8f8f8;
        width: 100%;
        height: 35px;
    }
</style>
<?php if($user['security_question']>0 && $user['attempts_answer']<3):
    $secureUserParams = $this->Utilities->encodeParams($user['id']);
?>
<form id="answer_security_question_form" action="<?php echo $this->Html->url(array('controller'=>'Users','action'=>'processAnswerSecurityQuestion', $secureUserParams['item_id'], $secureUserParams['digest'])); ?>" class="fancy_form">
    <h1><?php echo __("Security question",true); ?></h1>
    <div id="fingerprint_disclaimer">
        <?php echo __("To access the system with a new device, please answer correctly your security question.",true); ?>
    </div>
    <div>
        <div class="conveyor-ctrls">
            <div class="column-middle"></div>
            <div class="column-middle last">
                <div id="user_question">
                    <?php echo __($questions_config['questions'][$user['security_question']],true); ?>
                </div>
            </div>
        </div>
        <div class="conveyor-ctrls no-margin">
            <div class="column-middle"><div class="conveyor-label"><?php echo __('Answer',true); ?></div></div>
            <div class="column-middle last"></div>
        </div>
        <div class="conveyor-ctrls">
            <div class="column-middle"><input type="password" name="answer" class='validate[required]'/></div>
            <div class="column-middle last"></div>
        </div>
    </div>
    <div class="dialog-buttons">
        <section>
            <button type="button" id="answer_security" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Continue', true); ?></button>
        </section>
    </div>
</form>
<?php else: ?>
<style>
    #answer_security_question_form{
        width: 400px;
        height: 30px;
        padding-top: 0px;
    }
</style>
<form id="answer_security_question_form" class="fancy_form">
    <div id="fingerprint_disclaimer">
        <?php echo __("Please send a request to your Administrator %s at this email %s to allow you accessing with a new device.",["security@contiplus.net"]); ?>
    </div>
</form>

<?php endif; ?>