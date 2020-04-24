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
 * @date 04, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */

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
$( window ).load( function() {
    if(typeof(jsVars.needsAuthForFingerprint)=='undefined') {
        $('input').each(function () {
            $(this).val('');
        });
    }
});
$(document).ready(function() {

    //Set browser location
    //getLocation();
    getIpApiLocation();

    if(typeof(jsVars.needsAuthForFingerprint)=='undefined') {
        $('input').each(function () {
            $(this).val(' ');
        });
    }

    $('#form_login').formValidation({
        framework: "bootstrap4",
        button: {
            selector: '#submitLogin',
            disabled: 'disabled'
        },
        icon: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        err: {
            // You can set it to popover
            // The message then will be shown in Bootstrap popover
            container: 'tooltip'
        },
        row: {
            invalid: 'has-danger',
            valid: ''
        },
    });


    $('input[name="fingerprint"]').val(getDeviceId());
    $('#form_login').submit(function(event) {
        $('#form_login').formValidation();
    });

    //$.removeCookie("contact_description_closed", {path: "/"}); // successfully deleted

    if(typeof(jsVars.needsAuthForFingerprint)!='undefined'){
        $('#confirmUniqueDevice').modal({
            keyboard: false,
            backdrop: 'static',
        }).one('click', '#confirmDevice', function(e) {
            $('input[name="force"]').val('yes');
            $('#submitLogin').trigger('click');
            $( this ).off( event );
        });/*.on('hidden.bs.modal', function (e) {
            $('input[name="force"]').val('');
        });*/
    }
});

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