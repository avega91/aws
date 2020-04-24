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
 * @date 01, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
var saved_history = false;
$(function () {
    $('.title-page').tipsy({gravity: 's', fade: true});
    $('.actions-item-dashboard a').tipsy({gravity: 'e', fade: true});

    $('#details_link').click(function(e) {
        e.stopPropagation();
        $(this).next().slideToggle("slow");
        return false;
    });

    $('.btn-opt-link:not(.disabled-btn), .edit-history-link').click(function() {
        var pet = $(this).attr('rel');
        var callback = $(this).attr('assoc-callback');
        var style = $(this).attr('dialog-style');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            data: {},
            success: function(response) {
                try {
                    //if no parse JSON is HTML
                    response = $.parseJSON(response);
                    //if parse JSON, is error
                    $('body').cocoblock('unblock');
                    $.conti.alert({msg: response.msg, type: 'error-dialog'});
                } catch (e) {
                    $.conti.dialog({html: response, style: style, modal: true, callbackOpen: callback, paramsOpen:{
                                                families: jsVars.brandCompoundFamilies, top_covers: jsVars.brandCompoundTopCover,
                                                tension: jsVars.beltDetailsTension, widths: jsVars.beltDetailsWidths,
                                                importUrl: jsVars.importTechnicalDataUrl
                                            }, callbackClose: 'refreshPage'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);chosen-container
            }
        });
    });
});

function callAddHistory(families, top_covers, tension, widths, importUrl){
    $('#slide_form a').tipsy({gravity: 's', fade: true});
    $("#add_history_form select#conveyor").chosen({search_contains: true});
    $("#add_history_form select:not(#conveyor):not(#failure_mode)").chosen({disable_search: true});

    $("#failure_mode").multiselect({
        header: false,
        minWidth: 195,
        selectedList: 5,
        beforeopen: function(event, ui){
            $('input[name="date_install"]').datepicker( "destroy" );
        },
        close: function(event, ui){
            $('input[name="date_install"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
            $('input[name="years_system"]').focus();
        }
    });

    $('#import-selector').click(function(e){
        e.stopPropagation();
        if ($(this).hasClass('unselected')) {//No selected, select
            $(this).removeClass('unselected').addClass('selected');
            getTechnicalData(importUrl);
        } else {
            $(this).removeClass('selected').addClass('unselected');
        }
        return false;
    });

    $.datepicker.setDefaults({
        showOtherMonths: true,
        selectOtherMonths: true,
        changeYear: true,
        //changeMonth: true,
        //yearRange: "c-6:c+6"
        yearRange: "-30:+0",
        onSelect: function(){
            $("#focusfix").focus();
        },

    }, $.datepicker.regional[ "" ]);

    $('input[name="date_install"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);//.click(function(){$(this).focus()});;
    $('input[name="date_failed"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);//.click(function(){$(this).focus()});;


    $('input[name="date_install"]').change(function(e){
        $('#date_install_ok').val($(this).val());
        var fixed_date = timestampToUsDate($(this).val());
        $(this).val(fixed_date);
        $('input[name="date_install"]').datepicker( "hide" );
        calcYearsElapsed();

    });
    //If its update
    if($('#date_install_saved').size()>0){
        var date = $('#date_install_saved').val().split('-');
        var install_date = jsVars.systemLanguage=='es' ? date[2]+'/'+date[1]+'/'+date[0] : date[1]+'/'+date[2]+'/'+date[0];
        $('#date_install_ok').val(install_date);
        var fixed_date = timestampToUsDate(install_date);
        $('input[name="date_install"]').val(fixed_date)
        calcYearsElapsed();
    }


    $('input[name="date_failed"]').change(function(e){
        $('#date_failed_ok').val($(this).val());
        var fixed_date = timestampToUsDate($(this).val());
        $(this).val(fixed_date);
        $('input[name="date_failed"]').datepicker( "hide" );
        calcYearsElapsed();
    });
    //If its update
    if($('#date_failed_saved').size()>0){
        var date = $('#date_failed_saved').val().split('-');
        var failed_date = jsVars.systemLanguage=='es' ? date[2]+'/'+date[1]+'/'+date[0] : date[1]+'/'+date[2]+'/'+date[0];
        $('#date_failed_ok').val(failed_date);
        var fixed_date = timestampToUsDate(failed_date);
        $('input[name="date_failed"]').val(fixed_date)
        calcYearsElapsed();
        $('input[name="years_system"]').focus();
    }

    $('#clear_date_failed').click(function(e){
        e.stopPropagation();
        $('input[name="date_failed"]').val('');
        $('input[name="years_system"]').val('');
       return false;
    });

    $("#add_history_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $('#belt_manufacturer').change(function() {
        var beltManufacturer = $(this).val();
        var options = '';
        $.each(families[beltManufacturer], function(index, value){
            options += '<option value="'+index+'">'+value+'</option>'
        });
        $('#family').html(options);
        $('#family').trigger("chosen:updated");
        //If its update
        if($('#family_saved').size()>0){
            $('#family').val($('#family_saved').val()).trigger("chosen:updated");
        }


        options = '';
        $.each(top_covers[beltManufacturer], function(index, value){
            options += '<option value="'+index+'">'+value+'</option>'
        });
        $('#compounds_top_cover').html(options);
        $('#compounds_top_cover').trigger("chosen:updated");
        //If its update
        if($('#compounds_top_cover_saved').size()>0){
            $('#compounds_top_cover').val($('#compounds_top_cover_saved').val()).trigger("chosen:updated");
        }
    });

    //If its update
    if($('#belt_manufacturer_saved').size()>0){
        $('#belt_manufacturer').val($('#belt_manufacturer_saved').val()).trigger("chosen:updated");
        $('#belt_manufacturer').trigger("change");
    }

    $('#fabric_type').change(function() {
        unitTensionHasChanged(tension, widths);
        $('#tension_unit').attr('disabled', false).trigger("chosen:updated");
        if($(this).val()!='ST'){
            $('#plies').attr('disabled', false).trigger("chosen:updated");
            //If its update
            if($('#plies_saved').size()>0){
                $('#plies').val($('#plies_saved').val()).trigger("chosen:updated");
            }
        }else{
            $('#plies').val(0);
            $('#plies').attr('disabled', true).trigger("chosen:updated");
        }
    });
    //If its update
    if($('#fabric_type_saved').size()>0){
        $('#fabric_type').val($('#fabric_type_saved').val()).trigger("chosen:updated");
        $('#fabric_type').trigger("change");
        $('#save_history').loadingButton('enabled');//Habilitamos el boton de guardar
    }

    $('#tension_unit').change(function() {
        unitTensionHasChanged(tension, widths);
    });
    //If its update
    if($('#tension_unit_saved').size()>0){
        $('#tension_unit').val($('#tension_unit_saved').val()).trigger("chosen:updated");
        $('#tension_unit').trigger("change");
    }

    $('#conveyor').change(function() {
        getTechnicalData(importUrl);
    });

    $('#width').change(function() {
        if($(this).val()=='other'){
            $('input[name="other_width"]').removeClass('hidden');
            $('input[name="other_width"]').next().removeClass('hidden');
            $('#width').val(0).trigger("chosen:updated");
            $('#width_chosen').hide();
        }
    });

    $('#close_other_width').click(function(e){
        e.stopPropagation();
        $('#width_chosen').show();
        $('input[name="other_width"]').val('').addClass('hidden');
        $('input[name="other_width"]').next().addClass('hidden');
        return false;
    });

    $('#other_special').change(function() {
        if($(this).val()>=3){
            $('.other-special-txt input').val('');
            $('.other-special-txt').removeClass('hidden');
            $('.other-special-txt > .conveyor-label').addClass('hidden');//hide all labels
            $('.other-special-txt > .conveyor-label').eq($(this).val()-3).removeClass('hidden');//show appropiate label
            //If its update
            if($('#other_special_data_saved').size()>0){
                $('.other-special-txt input').val($('#other_special_data_saved').val());
            }
        }else{
            $('.other-special-txt').addClass('hidden');
            $('.other-special-txt input').val('');
        }
    });
    //If its update
    if($('#other_special_saved').size()>0){
        $('#other_special').val($('#other_special_saved').val()).trigger("chosen:updated");
        $('#other_special').trigger("change");
    }

    $('#save_history').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function (e, btn) {
            e.stopPropagation();
            $.ajax({
                type: 'post',
                url: $("#add_history_form").attr('action'),
                data: {formdata: $("#add_history_form").serialize()},
                success: function(response) {
                    try {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('stop', 1);
                            saved_history = true;
                            $('#dialog_wrap').dialog('close');
                        } else {
                            if (response.code >= 0) {
                                $('#slide_form a').eq(response.code).trigger('click');
                            }
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

    //If its update
    if($('#fabric_type_saved').size()>0){
        $('#save_history').loadingButton('enabled');//Habilitamos el boton de guardar
    }
}

function calcYearsElapsed(){
    if($('#date_install_ok').val()!='' && $('#date_failed_ok').val()!=''){
        var instDate = parseDateLanguage($('#date_install_ok').val());
        var failDate = parseDateLanguage($('#date_failed_ok').val());
        var daysElapsed = dayDifference(instDate,failDate);
        if(daysElapsed>0){
            var years = daysElapsed/365;
            $('input[name="years_system"]').val(daysElapsed%365==0 ? number_format(years,0,'.') : number_format(years,2,'.'));
        }else{
            $('input[name="years_system"]').val('0');
        }
    }
}

function dayDifference(first, second) {
    return Math.round((second-first)/(1000*60*60*24));
}
function parseDateLanguage(str) {
    var mdy = str.split('/');
    return jsVars.systemLanguage=='es' ? new Date(mdy[2], mdy[1]-1, mdy[0]) : new Date(mdy[2], mdy[0]-1, mdy[1]);
}

function refreshPage(){
    if(saved_history){
        saved_history = false;
        $('body').cocoblock();
        location.reload();
    }

}

function getTechnicalData(url){
    var conveyorId = $("#conveyor option:selected").val();
    if(conveyorId>0){
        $('#save_history').loadingButton('enabled');//Habilitamos el boton de guardar
    }else{
        $('#save_history').loadingButton('disabled');//Deshabilitamos el boton de guardar
    }
    if(conveyorId>0 && $('#import-selector').hasClass('selected')){
        $.ajax({
            type: 'post',
            url: url,
            data: {conveyor: conveyorId},
            success: function(response) {
                response = $.parseJSON(response);
                $('input[name="date-install"]').val('');
                if (!$.isEmptyObject(response)) {
                    if(response.Conveyor.banda_fecha_instalacion != '0000-00-00'){
                        var date = response.Conveyor.banda_fecha_instalacion.split('-');
                        var install_date = jsVars.systemLanguage=='es' ? date[2]+'/'+date[1]+'/'+date[0] : date[1]+'/'+date[2]+'/'+date[0];
                        $('#date_install_ok').val(install_date);
                        var fixed_date = timestampToUsDate(install_date);
                        $('input[name="date_install"]').val(fixed_date)
                        calcYearsElapsed();
                        $('input[name="years_system"]').focus();
                    }

                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    }
}

function unitTensionHasChanged(tension, widths){
    var unitTension = $("#tension_unit option:selected").val();
    var genericUnit = unitTension.split('_');
    var fabricType = $("#fabric_type option:selected").val();
    var options = '';
    if(unitTension == 'imperial_fabric' || unitTension == 'metric_fabric'){
        var fabricTypeIndicator = unitTension == 'metric_fabric' ? fabricType:'';
        $.each(tension[unitTension], function(index, value){
            options += index>0 ? '<option value="'+index+'">'+fabricTypeIndicator+value+'</option>' : '<option value="'+index+'">'+value+'</option>';
        })
        $('#tension').html(options);
        $('#tension').attr('disabled', false);
        $('#tension').trigger("chosen:updated");

        //If its update
        if($('#tension_saved').size()>0){
            $('#tension').val($('#tension_saved').val()).trigger("chosen:updated");
        }
        $('#tension_chosen').show();
        $('input[name="tension_steel"]').val('').addClass('hidden');
    }else if(unitTension!=0){
        $('#tension').val(0).trigger("chosen:updated");
        $('#tension_chosen').hide();
        $('input[name="tension_steel"]').removeClass('hidden');
        //If its update
        if($('#tension_steel_saved').size()>0){
            $('input[name="tension_steel"]').val($('#tension_steel_saved').val());
        }
    }

    if(genericUnit[0]!=''){
        options = '';
        if(typeof(widths[genericUnit[0]])!='undefined'){
            $.each(widths[genericUnit[0]], function(index, value){
                options += '<option value="'+index+'">'+value+'</option>';
            })
            $('#width').html(options);
            $('#width').attr('disabled', false);
            $('#width').trigger("chosen:updated");
            //If its update
            if($('#width_saved').size()>0){
                $('#width').val($('#width_saved').val()).trigger("chosen:updated");

                //If its update
                if($('#other_width_saved').size()>0 && $('#width_saved').val()=='other'){
                    $('input[name="other_width"]').val($('#other_width_saved').val());
                    $('input[name="other_width"]').removeClass('hidden');
                    $('input[name="other_width"]').next().removeClass('hidden');
                    $('#width').val(0).trigger("chosen:updated");
                    $('#width_chosen').hide();
                }
            }
        }

        if(genericUnit[0]=='metric'){
            $('#top_cover').val(0).trigger("chosen:updated");
            $('#pulley_cover').val(0).trigger("chosen:updated");
            $('#top_cover_chosen').hide();
            $('#pulley_cover_chosen').hide();
            $('input[name="top_cover_metric"]').removeClass('hidden');
            $('input[name="pulley_cover_metric"]').removeClass('hidden');

            //If its update
            if($('#top_cover_metric_saved').size()>0){
                $('input[name="top_cover_metric"]').val($('#top_cover_metric_saved').val());
            }
            //If its update
            if($('#pulley_cover_metric_saved').size()>0){
                $('input[name="pulley_cover_metric"]').val($('#pulley_cover_metric_saved').val());
            }
        }else{
            $('#top_cover').attr('disabled', false).trigger("chosen:updated");
            $('#pulley_cover').attr('disabled', false).trigger("chosen:updated");
            $('#top_cover_chosen').show();
            $('#pulley_cover_chosen').show();
            $('input[name="top_cover_metric"]').val('').addClass('hidden');
            $('input[name="pulley_cover_metric"]').val('').addClass('hidden');

            //If its update
            if($('#top_cover_saved').size()>0) {
                $('#top_cover').val($('#top_cover_saved').val()).trigger("chosen:updated");
            }
            //If its update
            if($('#pulley_cover_saved').size()>0) {
                $('#pulley_cover').val($('#pulley_cover_saved').val()).trigger("chosen:updated");
            }
        }
    }

}