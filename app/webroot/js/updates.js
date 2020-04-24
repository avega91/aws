/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file updates.js
 *     js updates implementations
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */
nbOptions = 0;
angleStart = -360;

if (!Array.prototype.remove) {
    Array.prototype.remove = function(val) {
        var i = this.indexOf(val);
        return i>-1 ? this.splice(i, 1) : [];
    };
}


$( window ).load( function(){
    checkSecurityQuestion();
/*
    $.each(jsVars.assets, function(key, value){
        setTimeout(function(){
            var img = $("<img />").attr('src', value)
                .on('load', function() {
                    if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                        console.log('broken image =>' + value);
                    } else {
                        $("#preloader_imgs").append(img);
                    }
                });
        }, 1000);

    });*/
});


$(document).ready(function(){
   checkuserlogin();
    $('a.logout').click(function(e){
        //e.stopPropagation();
        $('body').cocoblock();
        //window.location = jsVars.site+"access/logout";
        //return false;
    });

    $("#fast_menu li a, #main_logo, #menu > a").click(function(){
        //$('body').cocoblock();
        //setTimeout(function(){ $('body').cocoblock('unblock');}, 1500)
        //return false;
    });

/*
    $(document).on('click',"a",function (){
        var link = $(this);
        var href = link.attr('href');
        if($().ajaxq){
            $.ajaxq.clear("gaugesQueue");
            $.ajaxq.abort("gaugesQueue");
        }

        if(typeof(href)=="undefined" || href=='#' || href[0]=='#'){
            $('body').data("clicked", false);
            //return true;
        }else{
            if( $('body').data("clicked") ){
               // return false;
            }
            $('body').data("clicked", true);


            //return true;
        }
    });*/

});

function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
}

function activeOverlay(){
    return $('div.cocoblocker').size() > 0;
}

function checkuserlogin() {
    loginMonitor = setInterval('checkloginstatus()',5000);
}
function checkloginstatus(){
    if(!getCookie('USER_UNIQ') || !getCookie('USERTYP')){
        //window.location.href = HTTP_ROOT + 'users/logout/';
        clearInterval(loginMonitor);
        //window.top.location.reload();
        //$('body').cocoblock();
        //window.location = jsVars.site+"access/logout";
    }
}

    // jquery rotate animation
function rotate(li,d) {
    $({d:angleStart}).animate({d:d}, {
        step: function(now) {
            $(li).css({ transform: 'rotate('+now+'deg)' })//rotamos el li
               .find('a').css({ transform: 'rotate('+(-now)+'deg)' });//quitamos rotacion al link
        }, duration: 0
    });
}

// show / hide the options
function toggleOptions(s) {
    $(s).toggleClass('open');
    var li = $(s).find('li');
    var deg = $(s).hasClass('half') ? 180/(li.length-1) : 360/li.length;
    for(var i=0; i<li.length; i++) {
        var d = $(s).hasClass('half') ? (i*deg)-90 : i*deg;
        $(s).hasClass('open') ? rotate(li[i],d) : rotate(li[i],angleStart);
    }
    //$(s).hasClass('open') ? rotate($('.add-item-dashboard-circular button'),45) : rotate($('.add-item-dashboard-circular button'),0);
}

function initEventsNewsOnView(){
    $(document).on('click','.ui-widget-overlay.ui-front', function(e){
        e.stopPropagation();
        if($('#single-news').size()>0 && $('#single-news').is(':visible')){
            var $dialog = $('#single-news').parents('.ui-dialog-content');
            $dialog.dialog("close");
        }
        return false;
    });


    $('#close-new-dialog').click(function(e){
        e.stopPropagation();
        var $dialog = $(this).parents('.ui-dialog-content');
        $dialog.dialog("close");
        return false;
    })
}

function drawGaugeChart(container_id, ticksDisabled) {
        container_id = 'gauge_div'+container_id;
        if($('#'+container_id).size()>0){
            var $percent = $('#'+container_id).data('reference');
            var data_gauge = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['', 0],

            ]);

            var ticks = typeof (ticksDisabled) != "undefined" ? ["","","","","","","","","","", ""] : ["","10%","","30%","","","","","","", ""];
            var options_gauge = {
                fontName: "sanslight",
                titleTextStyle: { color:'#707571'},
                //width: 100,
                redFrom: 0, redTo: 10,
                yellowFrom:10, yellowTo: 30,
                greenFrom:20, greenTo: 100,
                minorTicks: 10,
                'majorTicks': ticks,
            };
            var chart_gauge = new google.visualization.Gauge(document.getElementById(container_id));
            chart_gauge.draw(data_gauge, options_gauge);

            setTimeout(function(){
                data_gauge.setValue(0, 1, data_gauge.getValue(0, 1) + $percent);
                chart_gauge.draw(data_gauge, options_gauge);
            }, 1000);
        }
}

function isNumber(evt, element) {
    var charCode = (evt.which) ? evt.which : evt.keyCode

    if ((charCode != 45 || $(element).val().indexOf('-') != -1) && (charCode != 46 || $(element).val().indexOf('.') != -1) && ((charCode < 48 && charCode != 8) || charCode > 57)){
        return false;
    }
    else {
        return true;
    }
}

function metadataUpdate(dist_id, client_id, active_tab){
    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;
    active_tab = typeof (active_tab) !== 'undefined' ? active_tab : 0;

    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    $('.scrollable-tab').parent().css('height', '340px');
    $('.scrollable-tab').css({'overflow-x': 'hidden', 'overflow-y': 'scroll'});
    $('.scrollable-tab').perfectScrollbar({suppressScrollX: true});

    $('#slide_form a').tipsy({gravity: 's', fade: true});

    $("#add_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen',
        onFieldFailure: function () {
            $('.active_section').css('position', 'static');
            $('.ps-container .ps-scrollbar-y-rail').css('position', 'static');
        }
    });

    $.datepicker.setDefaults({
            //changeMonth: true,
            dateFormat: 'dd/mm/yy',
            changeYear: true,
            changeMonth: true,
            yearRange: "-40:+0",
            showOtherMonths: true,
            selectOtherMonths: true
        }, $.datepicker.regional[ "" ]);
        $('#revision_date').datepicker();

    $('#save_conveyor').loadingButton({
        onClick: function (e, btn) {
            e.stopPropagation();

            if ($("#add_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_conveyor_form").attr('action'),
                    data: {formdata: $("#add_conveyor_form").serialize(), overwrite: 1},
                    success: function (response) {
                        try {
                            response = $.parseJSON(response);

                            /*$.coconotif.add({
                                text: response.msg,
                                time: response.time
                            });*/
                            $.cookie("conti_notification", response.msg, {path: '/'}); // Sample 1

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_conveyor = true;
                                processed_operation = true;
                                number_saved_conveyor = response.conveyor_number;
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
                    error: function (xhr, ajaxOptions, thrownError) {
                        btn.loadingButton('stop', -1);
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                })
            } else {
                btn.loadingButton('stop', -1);
            };
            return false;
        }
    });
}
function callAddConveyorUs(dist_id, client_id, active_tab){

    if(meta_unit_fields!=''){
        var complementary_units = getComplementaryUnits();
        var fieldsInfo = meta_unit_fields.split('||');
        $.each(fieldsInfo, function(index, fieldUnit){
           var fieldData = fieldUnit.split('=');
           var field_name = fieldData[0];
           var field_unit = fieldData[1];
           if($("[name='"+field_name+"']").size()>0){ //field exists
               var $field = $("[name='"+field_name+"']");
               var $containerField = $field.closest('.column-ctrl');
               var extraClass = jsVars.rxc != 'master' ? ' disabled':'';
               $containerField.append('<a href="#" class="unit-indicator '+extraClass+'" data-units="'+field_unit+'|'+complementary_units[field_unit]+'" title="'+complementary_units[field_unit]+'">'+field_unit+'</a>');
           }
        });
    }



    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;
    active_tab = typeof (active_tab) !== 'undefined' ? active_tab : 0;

    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    $('.scrollable-tab').parent().css('height', '340px');
    $('.scrollable-tab').css({'overflow-x': 'hidden', 'overflow-y': 'scroll'});
    $('.scrollable-tab').perfectScrollbar({suppressScrollX: true});

    $('#slide_form > a,#slide_form > div > a').click(function (e) {
        $('.column-ctrl .chosen-container').css('width', '105%');
        $('.active_section').css('position', 'absolute');
        $('.active_section').css('padding-right', '20px');
        $('.ps-container .ps-scrollbar-y-rail').css('position', 'absolute');
    });

    $('#logo_transportador').tipsy({gravity: 's', fade: true, offset: -30});
    $('#slide_form a').tipsy({gravity: 's', fade: true});
    $('.tooltiped').tipsy({gravity: 's', fade: true});

    $(":input[placeholder]").placeholder();

    $("#add_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen',
        onFieldFailure: function () {
            $('.active_section').css('position', 'static');
            $('.ps-container .ps-scrollbar-y-rail').css('position', 'static');
        }
    });


    $("#add_conveyor_form select:not(.image-chosen):not(.multiple)").chosen({
        search_contains: true
    });
    $("#add_conveyor_form select.image-chosen").chosen({
        disable_search: true,
        width: "100%",
    });

    //Events nuevos campos
    initEventsUsConveyor();

    var drives = $("#stations optgroup:eq(1)");
    var takeups = $("#stations optgroup:eq(2)");

    $('#capture_units').change(function(e){
        var unitType = $(this).val();
        var indexSelectedUnit = ($('#capture_units option').index($('#capture_units option:selected')))-1;
        var indexOtherUnit = indexSelectedUnit==0 ? 1 : 0;

        $('.unit-indicator:not(.disabled)').removeClass('active inactive');
        $('.unit-indicator:not(.disabled)').each(function(){
            var $unitSelector = $(this);
            var units = $unitSelector.data('units')
            units = units.split('|');
            var selectedUnit = units[indexSelectedUnit];
            var otherUnit = units[indexOtherUnit];
            $unitSelector.attr('title', otherUnit);
            $unitSelector.html(selectedUnit);
        });
    });

    $('.ui-dialog').click(function(e){
        $('.unit-indicator').removeClass('active inactive');
    });

    $('.unit-indicator').click(function(e){
        e.stopPropagation();
        var $link = $(this);

        $('.unit-indicator').not(this).removeClass('active inactive');

        if( $link.hasClass('active')){//select
            $link.addClass('inactive').removeClass('active');
        }else{
            $link.addClass('active').removeClass('inactive');
            $('div.active_section').css('overflow','visible !important');
        }

        if(e.pageY<$(this).offset().top){
            var newTooltipValue = $link.html();
            var newValueLink = $link.attr('title');
            $link.attr('title', newTooltipValue);
            $link.html(newValueLink);
        }else{
            //console.log("click on link");
        };

       return false;
    });

    $("#stations").multiselect({
        header: false,
        minWidth: 300,
        selectedList: 4, // 0-based index
        click: function (event, ui) {

            var $widget = $(this).multiselect("widget");
            var option = $widget.find('.ui-multiselect-optgrp-child').find('input[value="' + ui.value + '"]');
            var option_li = option.closest('li');

            if (option_li.hasClass('stations')) {
                var value = parseInt(ui.value);
                option_li.siblings('.stations').find('input').not(this).prop('checked', false);
                if (value >= 2) {
                    drives.find('option.removable').remove();
                    takeups.find('option.removable').remove();
                    for (var i = 2; i < value; i++) {
                        opt = $('<option />', {
                            value: parseInt(i + 1) + 'd',
                            text: parseInt(i + 1),
                            class: 'drives removable'
                        });
                        opt.appendTo(drives);
                    }
                    for (var j = 2; j < value; j++) {
                        opt = $('<option />', {
                            value: parseInt(j + 1) + 't',
                            text: parseInt(j + 1),
                            class: 'takeups removable'
                        });
                        opt.appendTo(takeups);
                    }
                    $("#stations").multiselect('refresh');

                    option = $widget.find('.ui-multiselect-optgrp-child').find('input[value="' + ui.value + '"]');
                    option_li = option.closest('li');
                    option_li.siblings('.stations').find('input').not(option).prop('checked', false);
                    option.prop('checked', true);

                    $('.drives.ui-multiselect-optgrp-child').find('input').prop('checked', false);
                    $('.drives.ui-multiselect-optgrp-child').find('input[value="1d"]').prop('checked', true);
                    $('.takeups.ui-multiselect-optgrp-child').find('input').prop('checked', false);
                    $('.takeups.ui-multiselect-optgrp-child').find('input[value="1t"]').prop('checked', true);
                }
            }


            if (option_li.hasClass('drives')) {
                option_li.siblings('.drives').find('input').not(this).prop('checked', false);
                option.prop('checked', true);
            } else if (option_li.hasClass('takeups')) {
                option_li.siblings('.takeups').find('input').not(this).prop('checked', false);
                option.prop('checked', true);
            }
        }
    });

    /**Cuando se abran los selects q estan dentro de container con scroll **/
    $("#add_conveyor_form .scrollable-tab select:not(.in-form)").on('chosen:showing_dropdown', function (evt, params) {

        /*var width_form = parseInt($(this).closest('.fancy_form').css('width'));
        $('.active_section').css('width', parseInt(width_form) + 'px');
        $('.active_section').css('position', 'static');
        $('.active_section').css('padding-right', '0px');
        $('.active_section').perfectScrollbar('destroy');

        var pos_dropdown = $(this).next().position();
        $(this).next().addClass('chosen-open-with-scroll');
        $(this).next().css('top', pos_dropdown.top + 'px');*/
    });

    $("#add_conveyor_form select:not(.in-form)").on('chosen:showing_dropdown', function (evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });

    $("#add_conveyor_form select:not(.in-form)").on('chosen:hiding_dropdown', function (evt, params) {
       /* $('.chosen-results').perfectScrollbar('destroy');
        $(this).next().removeClass('chosen-open-with-scroll');
        $(this).next().css('top', 'inherit');

        active_chosens = $('.active_section').find('.chosen-with-drop').size();
        if ($('.active_section').hasClass('scrollable-tab') && active_chosens <= 0) {
            $('.active_section').css('position', 'absolute');
            $('.active_section').css('padding-right', '20px');
            $('.active_section').perfectScrollbar({suppressScrollX: true});
        }*/
    });


    if (dist_id > 0 && client_id > 0 || dist_id > 0) {
        $('#distributor').val(dist_id).attr('disabled', true).trigger("chosen:updated");
        changeDistributorConveyor(client_id);
    }

    $('#distributor').change(function () {
        changeDistributorConveyor(client_id);
    });

    $('#client').change(function () {
        $(this).prev().val($(this).val());
    });


    $('#material_desc').change(function() {
        if($(this).val()==54){
            $('.other-material-txt').removeClass('hidden');
        }else{
            $('.other-material-txt').addClass('hidden');
            $('.other-material-txt input').val('');
        }
    });
    if($('#material_desc_saved').size()>0 && $('#material_desc_saved').val()!=""){
        $('#material_desc').val($('#material_desc_saved').val()).trigger("chosen:updated");
        $('#material_desc').trigger("change");
    }

    $('textarea[name="remarks"]').limitCharsTxt(300);


    if (active_tab > 0) {
        $('#slide_form a').eq(active_tab).trigger('click');
        $('.column-ctrl .chosen-container').css('width', '105%');
    }

    $('input[name="lump_size"]').blur(function(){
        if ($.isNumeric($(this).val()) && parseFloat($(this).val()) < 6.0) {
            $('.lump_size-fields').removeClass("hidden");
        }else{
            $('input[name="percent_fines"]').val("");
            $('.lump_size-fields').addClass("hidden");
        }
    })
    //if update show/hide lump_size


    var image_portada = $('#reference_bg_uploader').size() > 0 && $('#reference_bg_uploader').val() != '' ? $('#reference_bg_uploader').val() : 'img/img_conveyor_off.png';
    $('#logo_transportador').contiUploader({
        icon: jsVars.site + image_portada,
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function (obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function (obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function (response, obj) {
            obj.cocoblock('unblock');
            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-usuario',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function (btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function (cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_transportador').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'user-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $('#save_conveyor').loadingButton({
        onClick: function (e, btn) {
            e.stopPropagation();

            //if($.trim($("input[name='installation_date']").val())!="" && $.trim($("input[name='installation_date']").val())!="0000-00-00"
             //   && $.trim($("input[name='date_belt_failed']").val())!="" && $.trim($("input[name='date_belt_failed']").val())!="0000-00-00"){
            if(1==2){
                $.conti.confirm({msg: jsVars.systemMsgs.confirmExportConveyorHistory, type: 'notification-dialog', callbackOk: 'processSaveConveyorUS', paramsOk: {btn: btn}, callbackCancel: 'cancelSaveConveyor', paramsCancel: {btn: btn}});
                var $dialog = $("#confirm-dialog").closest(".ui-dialog");
                $dialog.prev().attr('style', 'z-index: 1002 !important');
            }else{
                processSaveConveyorUS(btn);
            }


            /*var fieldUnitsData = getMetaUnitFieldsData();
            $('#data-field-units').val(fieldUnitsData);

            if ($("#add_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section
                var number_stations = $("#stations_ms > span:last-child").html();
                $('input[name="number_stations"]').val(number_stations);
                $.ajax({
                    type: 'post',
                    url: $("#add_conveyor_form").attr('action'),
                    data: {formdata: $("#add_conveyor_form").serialize()},
                    success: function (response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_conveyor = true;
                                processed_operation = true;
                                number_saved_conveyor = response.conveyor_number;
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
                    error: function (xhr, ajaxOptions, thrownError) {
                        btn.loadingButton('stop', -1);
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                })
            } else {
                btn.loadingButton('stop', -1);
            };*/
            return false;
        }
    });

    /**init tutorial **/
        //initTutorialSection(6);
    displayTutorialOnDialog(6);
}

function cancelSaveConveyor(btn){
    btn.loadingButton('stop', 1);
}
function processSaveConveyorUS(btn){
    var fieldUnitsData = getMetaUnitFieldsData();
    $('#data-field-units').val(fieldUnitsData);

    if ($("#add_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section
        var number_stations = $("#stations_ms > span:last-child").html();
        $('input[name="number_stations"]').val(number_stations);
        $.ajax({
            type: 'post',
            url: $("#add_conveyor_form").attr('action'),
            data: {formdata: $("#add_conveyor_form").serialize()},
            success: function (response) {
                try {
                    response = $.parseJSON(response);

                    /*$.coconotif.add({
                        text: response.msg,
                        time: response.time
                    });*/
                    $.cookie("conti_notification", response.msg, {path: '/'}); // Sample 1

                    if (response.success) {
                        btn.loadingButton('stop', 1);
                        saved_conveyor = true;
                        processed_operation = true;
                        number_saved_conveyor = response.conveyor_number;
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
            error: function (xhr, ajaxOptions, thrownError) {
                btn.loadingButton('stop', -1);
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        })
    } else {
        btn.loadingButton('stop', -1);
    };
}

function getMetaUnitFieldsData(){
    var fieldUnitsData = "";
    $('.unit-indicator:not(.disabled)').each(function(){
        var $unitSelector = $(this);
        var $field = $unitSelector.prev()
        var input_name = "";

        if($field.is("input")){
            input_name =  $field.attr('name');
        }else if($field.is("span")) {
            $field = $unitSelector.closest('.closable-input-ctrl');
            input_name = $field.find('input').attr('name');
        }else{
            //input_name = $field.find('select').attr('name');
            $field = $unitSelector.closest('.column-ctrl');
            input_name = $field.find('select').attr('name');
        }
        fieldUnitsData += input_name+'='+$unitSelector.html()+'||';
    });

    fieldUnitsData = fieldUnitsData=='' ? '' : fieldUnitsData.slice(0,-2);
    return fieldUnitsData;
}

function initEventsUsConveyor(){

    var families = jsVars.manufacturerFamiliesUS;
    var top_covers = jsVars.compoundsUS;
    /*
    $.each(top_covers,function(index, compound){
        if($.isArray(compound)){
            top_covers[index] = asort(compound.sort();
        }

    });
     console.log(top_covers);
     */



    $("#failure_mode").multiselect({
        header: false,
        minWidth: 195,
        selectedList: 5,
        beforeopen: function(event, ui){
            $('.active_section').css('position', 'static');
            $('.active_section').css('padding-right', '0px');
            $('.active_section').perfectScrollbar('destroy');

            var pos_dropdown = $("input[name='durometer_failed']").position();
            $('.ui-multiselect-menu').addClass('chosen-open-with-scroll');
            $('.ui-multiselect-menu').css('top', pos_dropdown.top + 'px');
        },
        close: function(event, ui){
            $('.ui-multiselect-menu').removeClass('chosen-open-with-scroll');
            $('.ui-multiselect-menu').css('top', 'inherit');

            if ($('.active_section').hasClass('scrollable-tab')) {
                $('.active_section').css('position', 'absolute');
                $('.active_section').css('padding-right', '20px');
                $('.active_section').perfectScrollbar({suppressScrollX: true});
            }
        }
    });

    $("#clear_installed_date, #clear_date_failed").click(function(e){
        e.stopPropagation();
        var $inputPicker = $(this).prev();
        $inputPicker.attr('value', '');
        $inputPicker.datepicker('setDate', null);
        return false;
    });

    jsVars.systemLanguage = "en";

    $.datepicker.setDefaults({
        showOtherMonths: true,
        selectOtherMonths: true,
        changeYear: true,
        //yearRange: "c-6:c+6",
        yearRange: "-30:+0"
    }, $.datepicker.regional[ "" ]);


    $('input[name="installation_date"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    $('input[name="date_belt_failed"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);

    //If its update
    if($('#installation_date_saved').size()>0){
        $('input[name="installation_date"]').datepicker( "setDate", $('#installation_date_saved').val() );
    }
    if($('#date_belt_failed_saved').size()>0){
        $('input[name="date_belt_failed"]').datepicker( "setDate", $('#date_belt_failed_saved').val() );
    }

    $('#belt_manufacturer').change(function() {
        var beltManufacturer = $(this).val();
        if(beltManufacturer<13){

            $('#belt_family_chosen').show();
            $('#belt_compound_chosen').show();
            $('input[name="open_belt_family"]').val('').addClass('hidden');
            $('input[name="open_belt_compound"]').val('').addClass('hidden');
            var options = '';
            $.each(families[beltManufacturer], function(index, value){
                options += '<option value="'+index+'">'+value+'</option>'
            });
            $('#belt_family').html(options);
            $('#belt_family').attr('disabled', false);
            $('#belt_family').trigger("chosen:updated");

            //If its update
            if($('#belt_family_saved').size()>0){
                $('#belt_family').val($('#belt_family_saved').val()).trigger("chosen:updated");
            }

            //Order alphabetically

            var arr_compounds = top_covers[beltManufacturer];
            var temp_compounds = [];
            $.each(arr_compounds, function(key, value) {
                temp_compounds.push({v:value, k: key});
            });
            temp_compounds.sort(function(a,b){
                if(a.v > b.v){ return 1}
                if(a.v < b.v){ return -1}
                return 0;
            });
            $.each(temp_compounds, function(key, obj) {

                $('#belt_compound').append($("<option></option>")
                    .attr("value", obj.k).text(obj.v));
            });


            options = '';
            /*$.each(top_covers[beltManufacturer], function(index, value){
                options += '<option value="'+index+'">'+value+'</option>'
            });
            $('#belt_compound').html(options);*/
            $('#belt_compound').attr('disabled', false);
            $('#belt_compound').trigger("chosen:updated");
            //If its update
            if($('#belt_compound_saved').size()>0){
                $('#belt_compound').val($('#belt_compound_saved').val()).trigger("chosen:updated");
            }
        }else{
            $('input[name="open_belt_manufacturer"]').removeClass('hidden');
            $('input[name="open_belt_manufacturer"]').next().removeClass('hidden');
            //$('#belt_manufacturer').val(0).trigger("chosen:updated");
            $('#belt_manufacturer_chosen').hide();

            $('input[name="open_belt_family"]').removeClass('hidden');
            $('#belt_family').val(0).trigger("chosen:updated");
            $('#belt_family_chosen').hide();

            $('input[name="open_belt_compound"]').removeClass('hidden');
            $('#belt_compound').val(0).trigger("chosen:updated");
            $('#belt_compound_chosen').hide();
        }
    });

    $('#close_other_manufacturer').click(function(e){
        e.stopPropagation();
        $('#belt_manufacturer_chosen').show();
        $('input[name="open_belt_manufacturer"]').val('').addClass('hidden');
        $('input[name="open_belt_manufacturer"]').next().addClass('hidden');
        $('#belt_manufacturer').val('').trigger("chosen:updated");
        $('#belt_manufacturer').trigger("change");
        return false;
    });

    //If its update
    if($('#belt_manufacturer_saved').size()>0 && $('#belt_manufacturer_saved').val()!=""){
        $('#belt_manufacturer').val($('#belt_manufacturer_saved').val()).trigger("chosen:updated");
        $('#belt_manufacturer').trigger("change");
    }


    //If its update
    if($('#belt_family_saved').size()>0){
        $('#belt_family').val($('#belt_family_saved').val()).trigger("chosen:updated");
    }

    //if it is update
    if($('#belt_compound_saved').size()>0) {
        $('#belt_compound').val($('#belt_compound_saved').val()).trigger("chosen:updated");
    }

    $('#carcass').change(function() {
        tensionHasChanged();
    });
    //If its update
    if($('#carcass_saved').size()>0 && $('#carcass_saved').val()!=""){
        $('#carcass').val($('#carcass_saved').val()).trigger("chosen:updated");
        $('#carcass').trigger("change");
    }

    $('#tension_unit').change(function() {
        tensionHasChanged();
    });
    //If its update
    if($('#tension_unit_saved').size()>0 && $('#tension_unit_saved').val()!=""){
        $('#tension_unit').val($('#tension_unit_saved').val()).trigger("chosen:updated");
        $('#tension_unit').trigger("change");
    }

    $('#width').change(function() {
        if($(this).val()=='other' || $(this).val()==0){
            $('input[name="other_width"]').removeClass('hidden');
            $('input[name="other_width"]').next().removeClass('hidden');
            $('#width').val(0).trigger("chosen:updated");
            $('#width_chosen').hide();
        }
    });
    //If its update
    if($('#width_saved').size()>0 && $('#width_saved').val()!=""){
        $('#width').val($('#width_saved').val()).trigger("chosen:updated");
        $('#width').trigger("change");
    }

    $('#with_turnovers').change(function() {
        if($(this).val()=='no'){
            $('.turnover-length-txt input').val('');
            $('.turnover-length-txt').addClass('hidden');
        }else{
            $('.turnover-length-txt').removeClass('hidden');
        }
    });
    //If its update
    if($('#with_turnovers_saved').size()>0 && $('#with_turnovers_saved').val()!=""){
        $('#with_turnovers').val($('#with_turnovers_saved').val()).trigger("chosen:updated");
        $('#with_turnovers').trigger("change");
    }

    $('#takeup_type').change(function() {
        if($(this).val()=='3'){
            $('.counterweight-txt input').val('');
            $('.counterweight-txt').addClass('hidden');
        }else{
            $('.counterweight-txt').removeClass('hidden');
        }
    });

    //If its update
    if($('#takeup_type_saved').size()>0 && $('#takeup_type_saved').val()!=""){
        $('#takeup_type').val($('#takeup_type_saved').val()).trigger("chosen:updated");
        $('#takeup_type').trigger("change");
    }



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
    if($('#other_special_saved').size()>0 && $('#other_special_saved').val()!=""){
        $('#other_special').val($('#other_special_saved').val()).trigger("chosen:updated");
        $('#other_special').trigger("change");
    }

    //If its update
    if($('#top_cover_saved').size()>0){
        $('#top_cover').val($('#top_cover_saved').val()).trigger("chosen:updated");
    }
    if($('#pulley_cover_saved').size()>0){
        $('#pulley_cover').val($('#pulley_cover_saved').val()).trigger("chosen:updated");
    }
    if($('#splice_type_saved').size()>0){
        $('#splice_type').val($('#splice_type_saved').val()).trigger("chosen:updated");
    }
    if($('#splice_quantity_saved').size()>0){
        $('#splice_quantity').val($('#splice_quantity_saved').val()).trigger("chosen:updated");
    }
    if($('#splice_condition_saved').size()>0){
        $('#splice_condition').val($('#splice_condition_saved').val()).trigger("chosen:updated");
    }

}

function tensionHasChanged(){
    var tension = jsVars.tensionUS;
    var widths = jsVars.widthsUS;

    var unitTension = $("#tension_unit option:selected").val();
    var carcass = $("#carcass option:selected").val();

    var options = '';
    if(carcass != "0" && unitTension != "0") {
        //Set Tension, Plies and open fiels depending carcass
        if (carcass != "ST" && carcass != "UNKW") {
            var fabricTypeIndicator = unitTension == 'metric' ? carcass:'';
            $.each(tension[unitTension], function (index, value) {
                options += index > 0 ? '<option value="' + index + '">' + fabricTypeIndicator + value + '</option>' : '<option value="' + index + '">' + value + '</option>';
            })

            $('#tension').html(options);
            $('#tension').attr('disabled', false);
            //If its update
            if($('#tension_saved').size()>0){
                $('#tension').val($('#tension_saved').val()).trigger("chosen:updated");
            }
            $('#tension_chosen').show();
            $('input[name="open_tension"]').val('').addClass('hidden');
        } else {
            $('#tension').val(0);
            $('#tension_chosen').hide();
            $('input[name="open_tension"]').removeClass('hidden');
            //If its update
        }

        //refresh chosens
        $('#tension').trigger("chosen:updated");
    }

    //Toggle Plies
    if(carcass != "0" && carcass != "ST" && carcass != "UNKW"){
        //enable plies if NO selected ST or Unknow
        $('#plies').attr('disabled', false);
        //If its update
        if($('#plies_saved').size()>0){
            $('#plies').val($('#plies_saved').val());
        }
    }else{
        //disable plies if selected ST or Unknow
        $('#plies').attr('disabled', true);
        $('#plies').val(0);
    }
    $('#plies').trigger("chosen:updated");

    //Toggle Widths
    if(unitTension != "0") {
        //SET WIDTHs Depending Unit Tension
        options = '';
        $.each(widths[unitTension], function (index, value) {
            options += '<option value="' + index + '">' + value + '</option>';
        })
        $('#width').html(options);
        $('#width').attr('disabled', false);

        //If its update
        if($('#width_saved').size()>0){
            $('#width').val($('#width_saved').val());
        }

        //refresh chosens
        $('#width').trigger("chosen:updated");
    }
}

function checkSecurityQuestion(){
    if($('#user_security_question_unsetted').size()>0){ //no setted security section
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.setSecurityQuestion,
            success: function(response) {
                $('body').cocoblock('unblock');
                $.conti.dialog({html: response, style: 'warning-dialog', modal: true, callbackOpen: 'callAddSecurityQuestion'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    }
}

function callAddSecurityQuestion(){
    $("#set_security_question_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen',
        onFieldFailure: function () {
            $('.active_section').css('position', 'static');
            $('.ps-container .ps-scrollbar-y-rail').css('position', 'static');
        }
    });


    $("#set_security_question_form select").chosen({search_contains: true});

    $('#save_security').loadingButton({
        onClick: function (e, btn) {
            e.stopPropagation();
            if ($("#set_security_question_form").validationEngine('validate')) {
                $.ajax({
                    type: 'post',
                    url: $("#set_security_question_form").attr('action'),
                    data: {formdata: $("#set_security_question_form").serialize()},
                    success: function (response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                $('#dialog_wrap').dialog('close');
                            } else {
                                btn.loadingButton('stop', -1);
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
}

function callNewColaborator(){
    $("#add_user_form select").chosen({disable_search: true });
    $('#logo_usuario').tipsy({gravity: 's', fade: true, offset: -50});
    $(":input[placeholder]").placeholder();

    $("#add_user_form").validationEngine({
        validationEventTrigger: 'click',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $('#pass_gen').passgenerator({
        displayOn: '#password',
    });

    $('#is_professional').click(function(e) {
        e.stopPropagation();
        if (!$(this).hasClass('inactive')) {
            $(this).toggleClass('active');
        }
        return false;
    });

    $('#logo_usuario').contiUploader({
        icon: jsVars.site + 'img/img_user_off.png',
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-usuario',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_usuario').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'user-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $('#save_user').loadingButton({
        disabled: false,
        class_disabled: 'disabled-btn',
        time: 0.01,
        onClick: function(e, btn) {
            e.stopPropagation();
            var isProfessional = $('#is_professional').hasClass('active') && $('#is_professional').is(':visible') ? 1 : 0;
            if ($("#add_user_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_user_form").attr('action'),
                    data: {formdata: $("#add_user_form").serialize(), professional_user: isProfessional},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                updated_colaborator = true;
                                $('#dialog_wrap').dialog('close');
                            } else {
                                btn.loadingButton('stop', -1);
                            }
                        } catch (e) {
                            //Send data to sentry
                            Sentry.captureException(new Error(response));
                            $.coconotif.add({
                                text: "An unexpected error was detected, please review the data provided.",
                                time: jsVars.timeNotifications
                            });
                            btn.loadingButton('stop', -1);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
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

}

function callNewCompany() {

    //$("#add_user_form select:not(#type)").chosen({search_contains: true});
    //$("#type").chosen({disable_search: true });
    //$('.tooltiped').tipsy({gravity: 's', fade: true});
    $("#add_user_form select:not(#corporate)").chosen({disable_search: false, search_contains: true});
    $("#add_user_form select#corporate").chosen({disable_search: true});
    $("#corporate").val('').attr('disabled', true).trigger("chosen:updated");

    $('#logo_empresa').tipsy({gravity: 's', fade: true, offset: -50});
    $(":input[placeholder]").placeholder();

    $("#add_user_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen',
    });

    $('#type').change(function () {
        var selectedVal = $(this).val();
        $("#country").val('').attr('disabled', false).trigger("chosen:updated");
        $("#corporate option").show();//show all options for corporate

        if(selectedVal==20){
            $('.dealer-field').addClass('hidden');
            $('.client-field').removeClass('hidden');
            $('.client-field .chosen-container').css('width', '102%');

            $("#region, #territory").attr('disabled', true).trigger("chosen:updated");

            //$("#corporate option[data-type!='client']").hide();
            //$("#corporate option[data-type='client']").show();
        }else{
            $('.dealer-field').removeClass('hidden');
            $('.client-field').addClass('hidden');
            $('.dealer-field .chosen-container').css('width', '102%');

            $("#distributor").attr('disabled', true).trigger("chosen:updated");

            //$("#corporate option[data-type!='distributor']").hide();
            //$("#corporate option[data-type='distributor']").show();
        }

        //always show new option
        $("#corporate option[value='new']").show();
        $('#close_new_corp:not(.hidden)').trigger('click'); //for hide append corp if showed

        $("#salesperson").val('').attr('disabled', true).trigger("chosen:updated");
        $("#corporate").val('').attr('disabled', true).trigger("chosen:updated");
    });

    $('#country').change(function () {
        var countrySelected = $(this).val();
        var typeCompany = $("#type option:selected").val();
        var typeCompanyStr = typeCompany==20 ? 'client' : 'distributor';
        console.log(typeCompanyStr);

        $("#distributor, #region, #territory, #salesperson").val('').attr('disabled', true).trigger("chosen:updated");

        $("#corporate option").show();//show all options for corporate
        // $("#corporate option[data-country!=" + countrySelected + "]").hide();
        $("#corporate option[data-type!="+typeCompanyStr+"]").hide();
        //$("#corporate option[data-country=" + countrySelected + "]").show();
        //$("#corporate option[data-type="+typeCompany+"]").show();


        if(jsVars.rxc=='admin' && typeCompany==20) {
            $("#distributor").attr('disabled', false).trigger("chosen:updated");
        }else{
            if(typeCompany==20){
                //$("#distributor option[data-country!=" + countrySelected + "]").hide();
                //$("#distributor option[data-country=" + countrySelected + "]").show();
                $("#distributor").attr('disabled', false).trigger("chosen:updated");
            }else{
                $("#region option[data-country!=" + countrySelected + "]").hide();
                $("#region option[data-country=" + countrySelected + "]").show();
                //Autoselect option
                $("#region option[data-country=" + countrySelected + "]").prop('selected', true).change();
                $("#region").attr('disabled', false).trigger("chosen:updated");
                $("#region_chosen").addClass('chosen-disabled');
            }
        }

        //always show new option
        $("#corporate option[value='new']").show();
        $("#corporate").val('').attr('disabled', false).trigger("chosen:updated");

        var pais_id = countrySelected;
        if(pais_id<1000){ //Is Diff to CENAM
            $('#state').attr('disabled', true).trigger("chosen:updated");
            $.post(jsVars.updateStatesForCountryAx, {country: pais_id}, function(states) {
                $('#state').html(states);
                $('#state').removeAttr('disabled').trigger("chosen:updated");
            });
        }
    });

    $('#region').change(function () {
        var regionSelected = $(this).val();
        $("#territory option[data-region!=" + regionSelected + "]").hide();
        $("#territory option[data-region=" + regionSelected + "]").show();
        //Autoselect option
        $("#territory option[data-region=" + regionSelected + "]").prop('selected', true).change();
        $("#territory").attr('disabled', false).trigger("chosen:updated");
        $("#territory_chosen").addClass('chosen-disabled');
        //$("#territory").val('').attr('disabled', false).trigger("chosen:updated");

        var countrySelected = $("#country option:selected").val();
        var pais_id = countrySelected;
        if(countrySelected==1000) { //Is CENAM
            $('#state').attr('disabled', true).trigger("chosen:updated");
            $.post(jsVars.updateStatesForCountryAx, {country: pais_id, region: regionSelected}, function(states) {
                $('#state').html(states);
                $('#state').removeAttr('disabled').trigger("chosen:updated");
            });
        }
    });

    $('#territory').change(function () {
        var nameTerritory = $("#territory option:selected").data('territory');
        $('#name_territory').val(nameTerritory);
        if($.trim($('#nombre').val())!=''){
            $('#save_company').loadingButton('enabled');//Habilitamos el boton de guardar
        }else{
            $('#save_company').loadingButton('disabled');
        }
        $('#nombre').focus();
    });

    $('#nombre').blur(function(){
        if($.trim($('#nombre').val())!=''){
            $('#save_company').loadingButton('enabled');//Habilitamos el boton de guardar
        }else{
            $('#save_company').loadingButton('disabled');
        }
    });


    $('#distributor').change(function () {
        var selectedDist = $(this).val();
        $('#salesperson').attr('disabled', true).trigger("chosen:updated");
        $.post(jsVars.getSalespersonDistributorAx, {dist: selectedDist}, function(options) {
            $('#salesperson').html(options);
            $('#salesperson').removeAttr('disabled').trigger("chosen:updated");
        });
    });

    $('#corporate').change(function () {
        var corpSelected = $(this).val()
        if(corpSelected=='new'){
            //$('input[name="new-corporate"]').removeClass('hidden').addClass('validate[required]');
            $('input[name="new-corporate"]').removeClass('hidden');
            $('input[name="new-corporate"]').next().removeClass('hidden');
            $('#corporate').val('').trigger("chosen:updated");
            $('#corporate_chosen').hide();

            $('input[name="new-corporate"]').focus();
            //$("#add_user_form").validationEngine('attach');
        }
    });

    $('#close_new_corp').click(function(e){
        e.stopPropagation();
        $('#corporate_chosen').show();
        $('input[name="new-corporate"]').val('').addClass('hidden').removeClass('validate[required]');
        $('input[name="new-corporate"]').next().addClass('hidden');
        $("#add_user_form").validationEngine('detach');
        $("#add_user_form").validationEngine('attach');
        return false;
    });


    $('#logo_empresa').contiUploader({
        icon: jsVars.site + 'img/img_company_off.png',
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');

            $('#aux-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-empresa',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function(btn) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_empresa').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#aux-dialog').dialog('close');
                }
            });


            $.conti.dialog({
                wrapper: '#aux-dialog',
                style: 'user-dialog',
                modal: true,
                width: 920
            });

        }
    });

    $('#save_company').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        time: 0.01,
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#add_user_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_user_form").attr('action'),
                    data: {formdata: $("#add_user_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {

                                btn.loadingButton('stop', 1);
                                updated_colaborator = true;
                                $('#dialog_wrap').dialog('close');

                                if(response.typeCompany=='client'){
                                    saved_company_client = response.companyName;
                                    if (typeof (jsVars.clientsDataReload) !== 'undefined') {//just in clients section exist var and function
                                        callUpdateClientsDataTable();
                                    }
                                }

                            } else {
                                btn.loadingButton('stop', -1);
                            }
                        } catch (e) {
                            //Send data to sentry
                            Sentry.captureException(new Error(response));
                            $.coconotif.add({
                                text: "An unexpected error was detected, please review the data provided.",
                                time: jsVars.timeNotifications
                            });
                            btn.loadingButton('stop', -1);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
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
}

function floorFigure(figure, decimals){
    if (!decimals) decimals = 2;
    var d = Math.pow(10,decimals);
    return (parseInt(figure*d)/d).toFixed(decimals);
};

function getComplementaryUnits(){
    var units = {
        'in' : 'mm',
        'mm' : 'in',
        'PIW' : 'N/mm',
        'N/mm' : 'PIW',
        'ft' : 'm',
        'm' : 'ft',
        '(lb/ft3)' : '(kg/m3)',
        '(kg/m3)' : '(lb/ft3)',
        '°F' : '°C',
        '°C' : '°F',
        'hp' : 'kW',
        'kW' : 'hp',
        'fpm' : 'm/s',
        'm/s' : 'fpm',
        'lbs' : 'kg',
        'kg' : 'lbs',
    };
    return units;
}

function initEventsExportHistory(){

    $('#cancel_export_btn').click(function(e){
        e.stopPropagation();
        $('#dialog_wrap').dialog('close');
        return false;
    })

    $('#export_history_btn').click(function(e){
        e.stopPropagation();
        var l = Ladda.create(this);
        l.start();

        //generacion del archivo pdf
        $.ajax({
            type: 'post',
            url: datasheetPdfUrl,
            data: {},
            success: function(response) {

                try {
                    response = $.parseJSON(response);


                    //Crear peticion de exportacion de datos
                    $.ajax({
                        type: 'post',
                        url: exportHistoryUrl,
                        data: {filePath: response.file},
                        success: function (responseExport) {
                            try {
                                responseExport = $.parseJSON(responseExport);
                                if (responseExport.success) {//desplegar el mensaje despues de recargar
                                    l.stop();
                                    $('#dialog_wrap').dialog('close');

                                    $.cookie("conti_notification", responseExport.msg, {path: '/'});
                                    $('body').cocoblock();
                                    location.reload();
                                } else {
                                    $.coconotif.add({
                                        text: responseExport.msg,
                                        time: jsVars.timeNotifications
                                    });
                                    l.stop();
                                }
                            } catch (e) {
                                l.stop();
                            }
                        }
                    });
                } catch (e) {
                    l.stop();
                }

            },
            error: function(xhr, ajaxOptions, thrownError) {
                l.stop();
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    })
}

function initEventsImportFileClient() {

    $("#dialog_wrap").dialog("option", "position", {my: "center", at: "top + 300", of: window});

    $('#file_upload_item').contiUploader({
        icon: null,
        class: 'genericBtn',
        text: $('#file_upload_item').attr('title'),
        uploadUrl: jsVars.uploadGenericFileAx,
        width: 170,
        height: 35,
        type: '*',
        maxSizeUpload: jsVars.contiUploader.maxSizeVideoUpload,
        pathUpload: 'uploads/tmpfiles/',
        onCreate: function(obj){
            obj.removeAttr('title');
        },
        onUploadProgress: function(obj, progress) {
            //obj.find('.cocoblocker .blockMsg').html(progress);
            var parent = obj.closest('.ui-dialog');
            parent.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            //obj.cocoblock({message: '0%'});
            obj.closest('.ui-dialog').cocoblock({message: '0%'})
        },
        onError: function(errorStr, obj) {
            //obj.cocoblock('unblock');
            obj.closest('.ui-dialog').cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            //obj.cocoblock('unblock');

            obj.closest('.ui-dialog').cocoblock('unblock');
            obj.tipsy("hide");
            $('#path_item').val(response.relative_path);
            $('#name_file').html(response.name_file)    ;
            $('#item_name').val(response.name_file);
            //$('#item_name').parent().removeClass('hidden');

            //obj.hide();
            //obj.prev().hide().remove();
            $('#save_file_conveyor').loadingButton('enabled');//Habilitamos el boton de guardar
        },
        onInvalidUpload:  function(response, obj) {
            obj.closest('.ui-dialog').cocoblock('unblock');
            obj.tipsy("hide");
            $('#dialog_wrap').dialog('close');
            displayError(response.reason_fail+'.<br> '+jsVars.jqueryUploader.customTypeFiles);
        }
    });

    $('#save_file_conveyor').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();

            $('#path_item').val($('#path_item').val() == '' ? 'required-field' : $('#path_item').val());
            processExportData(btn, -1);
            
            return false;
        }
    });
}

function processExportData(btn, overwrite){
    $('#dialog_wrap').closest('.ui-dialog').show();
    if ($("#add_file_conveyor_form").validationEngine('validate')) { //if ok current section, go to other section
        $.ajax({
            type: 'post',
            url: $("#add_file_conveyor_form").attr('action'),
            data: {
                formdata: $("#add_file_conveyor_form").serialize(),
                overwrite: overwrite
            },
            success: function(response) {
                try {
                    response = $.parseJSON(response);
                    if(response.error){
                        $.coconotif.add({
                            text: response.error,
                            time: jsVars.timeNotifications
                        });
                        btn.loadingButton('stop', -1);
                        $('#dialog_wrap').dialog('close');
                    } else if(!response.duplicated){//No hubo duplicados, se importaron todos los bs del excel
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {// Se guardaron los datos
                            btn.loadingButton('stop', 1);
                            saved_conveyor = true;
                            $('#dialog_wrap').dialog('close');
                            callUpdateConveyorsDataTable();
                        } else {
                            btn.loadingButton('stop', -1);
                        }
                    } else {
                        $('#dialog_wrap').closest('.ui-dialog').hide();
                        $.conti.confirm({msg: response.msg, type: 'metadata-dialog', confirmBtnText: 'Yes, overwrite', cancelBtnText: 'No, keep data', isModal: false, callbackOk: 'processExportData', callbackCancel:'processExportData',paramsOk: {btn: btn, overwrite: 1},paramsCancel: {btn: btn, overwrite: 0}});
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
    } else {
        btn.loadingButton('stop', -1);
    }
}

function cancelImportXls(){
    $('#dialog_wrap').dialog('close');
}