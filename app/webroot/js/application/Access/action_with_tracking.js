var login_tracking = false;
var activated_hover = false;
function getLocation() {    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(setPosition, failPosition);
    } else {
        //console.log("Geolocation is not supported by this browser.");
    }
}

function failPosition() {
    displayError(jsVars.location_required);
}
function setPosition(position) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: jsVars.urlPositionAx,
        data: {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            region: position.region
        },
        success: function(response) {
            //LOGIN
            if (login_tracking && $("#form_login").validationEngine('validate')) {
                $.ajax({
                    type: 'post',
                    url: jsVars.urlLoginAx,
                    data: {
                        formdata: $("#form_login").serialize()
                    },
                    success: function(response) {
                        var result = $.parseJSON(response);
                        if (result.logged) {
                            $("#form_login input").val('');
                            window.location = result.redir;
                        } else {
                            //$('#container').unblock();
                            //spinner.stop();
                            $('body').cocoblock('unblock');

                            $("#captcha").load(jsVars.urlRefreshCaptchaAx, function() {
                                $('#captcha').imageSecurityToggle();
                            });
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
            } else {
                $('body').cocoblock('unblock');
            }

        }
    });
}

function getIpApiLocation() {
    $.get('http://ip-api.com/json', function(data) {
        var localization = {coords: {latitude: 0, longitude: 0}, region: 'MX'};
        if (typeof data == 'object') {
            localization.coords.latitude = data.lat;
            localization.coords.longitude = data.lon;
            localization.region = data.countryCode;
            //setPosition(localization);
        }
    });
}
$(document).ready(function() {
    
    //$.fn.snow();
    
    //Set browser location
    getIpApiLocation();
    //getLocation();
    $('#pass_conti').val($('#pass_conti').attr('placeholder'));

     $("#submit_login_off").hover(function() {
            $(this).stop().fadeTo("slow", 0.0, function() {});
     }, function() {
            $(this).stop().fadeTo("slow", 1.0, function() {});
     });



    $(":input[placeholder]").placeholder();

    blockedLogin = false;
    $("body").ezBgResize({
        img: jsVars.site + "img/bg_conti.jpg", // Relative path example.  You could also use an absolute url (http://...).                    
        opacity: 1, // Opacity. 1 = 100%.  This is optional.
        center: true // Boolean (true or false). This is optional. Default is true.
    });

    setFooterPosition();
    $(window).resize(function() {
        setFooterPosition();
    });

    $('#information_login').tipsy();
    $('#north-west').tipsy({gravity: 'nw'});
    //$('#captcha').s3Capcha();
    $('#captcha').imageSecurityToggle();

/*
    $('#access_form').hover(
            function() {
                if (!activated_hover) {
                    dark_background();
                    activated_hover = true;
                }
            }, function() {
        if (activated_hover) {
            clear_background();
            activated_hover = false;
        }
    });*/

    $("body").mousemove(function(event) {
        var item_mouse_move = $(event.target);
        if(item_mouse_move.attr('id') == 'access_form' ||  item_mouse_move.closest('#access_form').size()>0){
            if(!activated_hover){
                dark_background();
                activated_hover = true;
            }
        }else{
             if(activated_hover){
                    clear_background();
                    activated_hover = false;
             }
        }
    });

    $("#form_login").validationEngine({
        validationEventTrigger: '',
        scroll: false,
        promptPosition: "centerRight"
    });

    $('#form_login').submit(function() {
        login_tracking = true;
        getLocation();
        return false;
    });

    $.removeCookie("contact_description_closed", {path: "/"}); // successfully deleted
});

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