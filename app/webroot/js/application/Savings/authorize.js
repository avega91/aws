/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file
 * @description
 *
 * @date 12, 2016
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
/*
 require(["jquery.alpha", "jquery.beta"], function() {
 //las extensiones jquery.alpha.js y jquery.beta.js han sido cargadas.
 $(function() {
 $('body').alpha().beta();
 });
 });
 */
$(function () {
    var $sigdiv = $("#signature").jSignature({'UndoButton':false,height:150,"background-color":"#FFF", width:"100%"});
    $('#clean-option').click(function(e){
        $sigdiv.jSignature('reset')
    });
    $('#extract_signature').click(function(){
        var validation = $sigdiv.jSignature('getData', 'base30');
        var signatureDate = validation[1] == "" ? validation[1] : $sigdiv.jSignature('getData');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.urlSaveAuth,
            data: {signature: signatureDate},
            success: function(response) {
                $('body').cocoblock('unblock');
                response = $.parseJSON(response);
                $.coconotif.add({
                    text: response.msg,
                    time: 3000
                });

                if (response.success) {
                    $('#signature-container, #extract_signature').fadeOut('slow');
                    $('#messages > div').html(response.msg).addClass('green');
                    $('#messages').removeClass('hidden');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
            }
        });
    });
});