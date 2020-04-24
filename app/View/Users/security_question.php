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
    #set_security_question_form{
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
</style>
<form id="set_security_question_form" action="<?php echo $this->Html->url(array('controller'=>'Users','action'=>'processSecurityQuestion')); ?>" class="fancy_form">
    <h1><?php echo __("Security question",true); ?></h1>
    <div id="fingerprint_disclaimer">
        <?php echo __("Select a security question. This will help us verify your identity.",true); ?>
    </div>
    <div>
        <div class="conveyor-ctrls">
            <div class="column-middle"></div>
            <div class="column-middle last">
                <?php echo $this->Form->input('question', array('name'=>'question','type' => 'select','label' => false,'options' => array_map("__",$questions_config['questions']), 'data-placeholder'=>__('v.2.5.1.Select',true))); ?>
            </div>
        </div>
        <div class="conveyor-ctrls no-margin">
            <div class="column-middle"><div class="conveyor-label"><?php echo __('Answer',true); ?></div></div>
            <div class="column-middle last"></div>
        </div>
        <div class="conveyor-ctrls">
            <div class="column-middle"><input type="text" name="answer" class='validate[required]'/></div>
            <div class="column-middle last"></div>
        </div>
    </div>
    <div class="dialog-buttons">
        <section>
            <button type="button" id="save_security" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Continue', true); ?></button>
        </section>
    </div>
</form>
