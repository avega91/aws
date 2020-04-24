var login_tracking = false;
var activated_hover = false;
var current_password = "";
var cookies_deleted = false;
function getLocation() {    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(setPosition, failPosition);
    } else {
        //console.log("Geolocation is not supported by this browser.");
    }
}

/*
function deleteAllCookies() {
    if(!cookies_deleted){
        var cookies = document.cookie.split(";");

        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf("=");
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
        }

        cookies_deleted = true;
    }

}*/

function failPosition() {
    displayError(jsVars.location_required);
}
function setPosition(position) {   
    //DESCOMENTAR ESTO AL ACTIVAR TRACKEO
    //$('body').cocoblock();
    $.ajax({
        async: true,
        type: 'post',
        url: jsVars.urlPositionAx,
        data: {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            //region: position.region
        },
        success: function(response) {

        }
    });
}

function getIpApiLocation() {
    jQuery.ajax({
        url: 'http://ip-api.com/json',
        //url: 'https://freegeoip.net/json/',
        //url: 'https://flourishworks-webutils.appspot.com/req',
        success: function (data) {

            //var localization = {coords: {latitude: 0, longitude: 0}, region: 'MX'};
            var localization = {coords: {latitude: 0, longitude: 0}};
            if (typeof data == 'object') {
                localization.coords.latitude = data.lat;
                localization.coords.longitude = data.lon;
                //localization.region = data.countryCode;

                //Comentar esta linea cuando se active el trackeo obligatorio
                setPosition(localization);
            }
        },
        async: true
    });
}
var refreshCaptchaMonitor = null;
$(document).ready(function() {

    //deleteAllCookies();
    refreshCaptchaMonitor = setInterval('refreshCaptcha()',120000);

    //Set browser location
    //getLocation(); //enable this for ask user for share his location
    getIpApiLocation();//if user share his location, not use this method
    $('#pass_conti').val($('#pass_conti').attr('placeholder'));


    $(":input[placeholder]").placeholder();

    blockedLogin = false;
    $("body").ezBgResize({
        img: jsVars.site + "img/bg_ifs_login.jpg", // Relative path example.  You could also use an absolute url (http://...).                    
        opacity: 0.2, // Opacity. 1 = 100%.  This is optional.
        center: true // Boolean (true or false). This is optional. Default is true.
    });

    setFooterPosition();
    $(window).resize(function() {
        setFooterPosition();
    });

    // $('#information_login').tipsy({html: true, trigger: 'click'});
    $(document).click(function(){
        if($('.tipsy').length > 0){
            $('#information_login').tipsy('hide');
        }
    });
    $('#information_login').tipsy({html: true, trigger: 'hover',delayOut: 3000});
    $('#information_login').click(function(e){
        e.stopPropagation();
        if($('.tipsy').length == 0){
            $(this).tipsy('show');
        }else{
            $(this).tipsy('hide');
        }
        return false;
    });

    /*
    $(document).on('click','#link_help', function(e){
        e.stopPropagation();
        console.log("click");
        return false;
    });*/



    $('#north-west').tipsy({gravity: 'nw'});
    $('#captcha').imageSecurityToggle();


    $("#form_login").validationEngine({
        validationEventTrigger: '',
        scroll: false,
        promptPosition: "centerRight"
    });

    $('#form_login').submit(function() {
        clearInterval(refreshCaptchaMonitor);

        ajaxLogin();

        return false;
    });

    $.removeCookie("contact_description_closed", {path: "/"}); // successfully deleted
});

function ajaxLogin(forceLogin){
    var data = {}
    if(typeof(forceLogin)!='undefined'){
        data["force"] = true;
        $('#pass_conti').val(current_password);
    }
    current_password = $('#pass_conti').val();

    data["formdata"] = $("#form_login").serialize();
    data["fingerprint"] = getDeviceId();


    login_tracking = true;
    if (login_tracking && $("#form_login").validationEngine('validate')) {
        $('body').cocoblock({overlayColor: '#000', opacity: 0.6});
        
        $.ajax({
            type: 'post',
            url: jsVars.urlLoginAx,
            data: data,
            success: function(response) {
                var result = $.parseJSON(response);
                if (result.logged) {
                    if(!result.needsAcceptEula){
                        if(result.needsAuthForFingerprint){ //necesita definir el fingerprint del equipo actual
                            $('body').cocoblock('unblock');
                            $.conti.confirm({msg: result.msg, type: 'warning-dialog', callbackOk: 'continueLogin', callbackCancel:'cancelAuthFinger',paramsOk: {redirTo: result.redir},paramsCancel: {redirTo: result.redir}});
                        }else if(result.needsAnswerQuestion){ //entro desde otro equipo, es necesario confirmar pregunta de seguridad
                            answerSecurityQuestion(result.userId);
                        }else{
                            $("#form_login input").val('');
                            window.location = result.redir;
                        }
                    }else{
                        needsToAcceptEula(result.userId);
                    }

                } else {
                    $('body').cocoblock('unblock');

                    //refresjh captcha
                    $("#captcha").load(jsVars.urlRefreshCaptchaAx+"?"+$.now(), function() {
                        $('#captcha').imageSecurityToggle();
                    });
                    //run captcha refresh each 2 minutes
                    refreshCaptchaMonitor = setInterval('refreshCaptcha()',120000);

                    $("#login_msgs").html(result.msg);
                    var width = result.logged ? 300 : 500;
                    $("#login_msgs").dialog({
                        modal: true,
                        resizable: false,
                        width: width,
                        open: function(event, ui) {
                            $(this).addClass('conti-dialog warning-dialog');
                        }
                    });
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (xhr.status == 404) {
                    $.gritter.add({
                        title: jsVars.systemNotifications.title,
                        text: jsVars.ajaxPetition.error,
                        sticky: true,
                        time: ''
                    });
                }
            }
        }); 
    }
}

function continueLogin(redirTo){
    ajaxLogin(true);
}

function cancelAuthFinger(redirTo) {
    $('body').cocoblock();
    window.location = redirTo;
}

function answerSecurityQuestion(userParams){
        $.ajax({
            type: 'post',
            url: jsVars.answerSecurityQuestion,
            data: {user:userParams},
            success: function(response) {
                $('body').cocoblock('unblock');
                $.conti.dialog({wrapper:alert_wrap, html: response, style: 'warning-dialog', modal: true, callbackOpen: 'callAnswerSecurityQuestion'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
}

function needsToAcceptEula(userParams){
    $.ajax({
        type: 'post',
        url: jsVars.getEulaUrl,
        data: {user:userParams},
        success: function(response) {
            $('body').cocoblock('unblock');
            $.conti.dialog({wrapper:alert_wrap, html: response, style: 'warning-dialog', modal: true, callbackOpen: 'callEulaEventsDialog'});
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function callEulaEventsDialog(){
    $('#accept_eula').click(function(e){
        $('#accept_eula').addClass('active orange');
        $('#set_eula_ok').loadingButton('enabled');//Habilitamos el boton de continue
    });

    $('#set_eula_ok').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();
                $.ajax({
                    type: 'post',
                    url: $("#set_eula_form").attr('action'),
                    data: {eula_ok: 1},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                           /*$.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });*/

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                $('#alert_wrap').dialog('close');
                                ajaxLogin(true);
                            } else {
                                btn.loadingButton('stop', -1);
                            }
                        } catch (e) {
                            btn.loadingButton('stop', -1);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        btn.loadingButton('stop', -1);
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
            return false;
        }
    });
    $('#set_eula_ok').loadingButton('disabled');
};



function callAnswerSecurityQuestion(){
    $("#answer_security_question_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        onFieldFailure: function () {
            $('.active_section').css('position', 'static');
            $('.ps-container .ps-scrollbar-y-rail').css('position', 'static');
        }
    });


    $('#answer_security').loadingButton({
        onClick: function (e, btn) {
            e.stopPropagation();
            if ($("#answer_security_question_form").validationEngine('validate')) {
                $.ajax({
                    type: 'post',
                    url: $("#answer_security_question_form").attr('action'),
                    data: {formdata: $("#answer_security_question_form").serialize()},
                    success: function (response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                $('#alert_wrap').dialog('close');
                                ajaxLogin(true);
                            } else {
                                btn.loadingButton('stop', -1);
                                if(response.attempts>=3){
                                    $('#alert_wrap').dialog('close');
                                }
                            }
                        } catch (e) {
                            btn.loadingButton('stop', -1);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        btn.loadingButton('stop', -1);
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
            } else {
                btn.loadingButton('stop', -1);
            }
            return false;
        }
    });

    $('input[name="answer"]').keypress(function(e) {
        if(e.which == 13) {
            return false;
        }

    });
}

function dark_background() {
    if (!$("#jq_ez_bg > img").hasClass('animated') && !blockedLogin) {
        $("#jq_ez_bg > img").addClass('animated');
        $("#jq_ez_bg > img").clearQueue().stop().animate({queue: false, opacity: 0.20}, 800, function() {
        });
    }
}

function clear_background() {
    if (!blockedLogin) {
        $("#jq_ez_bg > img").removeClass('animated');
        $("#jq_ez_bg > img").clearQueue().stop().animate({queue: false, opacity: 1.00}, 800, function() {
        });
    }

}

function setFooterPosition() {
    var position = parseInt($(window).height() - 50);
    position = position < 750 ? 700 : position;
    $('#container').css('min-height', position + 'px');
    $('#wrapper').css('height', position + 'px');
    $('#access_form').css('height', position + 'px');
}

uuid=function(){
    var u = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,
        function(c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    return u;
}


getDeviceId = function(){
    var current = window.localStorage.getItem("_DEVICEID_")
    if (current) return current;
    var id = uuid();
    window.localStorage.setItem("_DEVICEID_",id);
    return id;
}

function refreshCaptcha(){
    $("#captcha").load(jsVars.urlRefreshCaptchaAx+"?"+$.now(), function() {
        $('#captcha').imageSecurityToggle();
    });
}