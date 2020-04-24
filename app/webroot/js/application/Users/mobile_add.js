/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file mobile_add.js
 *     Events management for add user in action mobileAdd
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */

$(document).ready(function() {
    //$("#add_user_form select").chosen({search_contains: true});
    //$('.chosen-container').css('width', '105%');        

    $("#add_user_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

       
    //Onchange value distributors, load regions distributor
    $('#all_distributors').change(function() {
        eventSelectDistributor();
    });

/*
    $('#user_region').change(function() {
        var selected_region = $(this).val();
        if (selected_region != '') {
            user_update_countries_and_states_values();
        } else {
            $('#country_user').removeAttr('disabled');
        }
    });*/

    //Onchange value pais
    $('#country_user').change(function() {
        $('#msgs').html('loading...');
        var pais_id = $("#country_user option:selected").attr('rel');
        $('#state_user').attr('disabled', true);
        $.post(jsVars.updateStatesForCountryAx, {country: pais_id}, function(states) {
            $('#state_user').html(states);
            $('#state_user option:first-child').hide().remove();
            $('#state_user').prepend('<option value="" disabled selected>' + jsVars.stateLabel + '</option>');
            $('#state_user').removeAttr('disabled');
            $('#msgs').html('');
        });
    });

    $('#save_user').click(function(e) {
        e.stopPropagation();
        if ($("#add_user_form").validationEngine('validate')) {//if ok current section, go to other section      
            $('body').cocoblock();
            $.ajax({
                type: 'post',
                url: $("#add_user_form").attr('action'),
                data: {formdata: $("#add_user_form").serialize()},
                success: function(response) {
                    $('body').cocoblock('unblock');
                    try {
                        response = $.parseJSON(response);
                        if(response.success){
                            $("#add_user_form input").val('');
                            $("#add_user_form select").val('');
                        }
                        alert(response.msg);                        
                    } catch (e) {
                        alert(response);      
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert('Error 404');      
                    $('body').cocoblock('unblock');
                    //errorAjax(xhr, ajaxOptions, thrownError);
                }
            });
        }
        return false;
    });

});

function eventSelectDistributor() {
    $('body').cocoblock();
    var dist_id = $("#all_distributors option:selected").val();
    $('#country_user,#state_user').val('').attr('disabled', true);//Disabled select country and states
    $('#all_distributors_txt').val(dist_id);
    $.post(jsVars.updateRegionsForUserAx, {dist: dist_id}, function(regions) {
        $('#user_region').html(regions);
        $("#user_region option:first-child").attr('selected',true);        
        user_update_countries_and_states_values();        
    });
}

function user_update_countries_and_states_values() {
    var selected_region = $("#user_region option:selected").val();
    $('#country_user,#state_user').val('').attr('disabled', true);
    $('#user_region_txt').val(selected_region);
    $.post(jsVars.updateStatesForRegionAx, {region: selected_region}, function(response) {
        var states_corps = response.split('|');
        var states = states_corps[0];
        var corps = states_corps[1];

        $('#all_corporates').html(corps);

        //if (selected_region != 'CENAM' && selected_region != 'SURAM') {
        if (selected_region != 'CENAM' && selected_region != 'SURAM' &&
            selected_region != 'NORTHEAST' && selected_region != 'MIDWEST' && selected_region != 'SOUTH' &&
            selected_region != 'WEST' && selected_region != 'SOUTHEAST' && selected_region != 'AUSTRALIA' && selected_region != 'BRASIL' &&
            selected_region.search("UST-") < 0 && selected_region.search("CA-") < 0) {
            $('#state_user').html(states);
            var pais = $('#state_user option:last-child').attr('alt');
            var country_id = $('#state_user option:last-child').attr('rel');
            $('#country_user').html('<option rel="' + country_id + '" value="' + pais + '">' + pais + '</option>');
            $('#country_user').removeAttr('disabled');

            $('#state_user option:first-child').hide().remove();
            $('#state_user').prepend('<option value="" disabled selected>' + jsVars.stateLabel + '</option>');
            $('#state_user').removeAttr('disabled');

        } else {
            $('#country_user').html(states);
            $('#country_user option:first-child').hide().remove();
            $('#country_user').prepend('<option value="" disabled selected>' + jsVars.countryLabel + '</option>');

            $('#country_user').removeAttr('disabled');
        }

        $('body').cocoblock('unblock');
    });
}
