/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ultrasonic.js
 *     actions for ultrasonic view
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$(document).ready(function() {
    initEventsUltrasonic();

    google.setOnLoadCallback(drawChart);
    function drawChart() {
        toggleSaveUltrasonic();                
        var data = google.visualization.arrayToDataTable(jsVars.chartData);
        var colors = [];
        var items = jsVars.chartData[0].length - 1;
        //colors.push("blue");
        for (var i = 0; i < (items - 1); i++) { //last item no
            colors.push("#FF9900");
        }
        colors.push("green");

        var options = {
            chartArea: {width: 500},
            curveType: 'function',
            colors: colors,
            legend: {
                position: 'right',
                textStyle: {color: '#707571', fontSize: 13}
            },            
            width: 800,
            backgroundColor: 'transparent',
            title: jsVars.chartTitle,
            fontName: "sanslight",
            titleTextStyle: { color:'#707571'},
            hAxis: {minValue: 1, maxValue: 47, textColor: '#707571', textStyle: {fontSize: 13}},
            vAxis: {minValue: 0, textColor: '#707571', textStyle: {fontSize: 13},title: jsVars.vAxisLabel, titleTextStyle: {bold: false, italic: false, color:"#707571"}},
            tooltip: {textStyle: {color: '#707571'}, trigger: 'selection'},
            //selectionMode: 'multiple',
            //aggregationTarget: 'category',
            //focusTarget: 'category',                        
            pointShape: 'square',
            pointSize: 7,
            lineWidth: 3
        };        
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        
        google.visualization.events.addListener(chart,'ready',function (){
            initTutorialSection(12);
        });        
        chart.draw(data, options);

        //Draw gauge
        if(!$.isEmptyObject(jsVars.abrasionLifeData)){
            var data_gauge = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['', 0],

            ]);
            var options_gauge = {
                fontName: "sanslight",
                titleTextStyle: { color:'#707571'},
                width: 600,
                redFrom: 0, redTo: 10,
                yellowFrom:10, yellowTo: 30,
                greenFrom:20, greenTo: 100,
                minorTicks: 10,
                'majorTicks': ["","10%","","30%","","","","","","", ""],
            };
            var chart_gauge = new google.visualization.Gauge(document.getElementById('gauge_div'));
            chart_gauge.draw(data_gauge, options_gauge);
            //show data gauge after draw
            $('#gauge_div').next().show();

            setTimeout(function(){
                var available = 100 - jsVars.abrasionLifeData.percent_cover_used;
                data_gauge.setValue(0, 1, data_gauge.getValue(0, 1) + available);
                chart_gauge.draw(data_gauge, options_gauge);
            }, 1000);

        }

    }
});

function toggleSaveUltrasonic() {
    //if ($.trim($('#conveyor_price').val()).length > 1 && $('#original_top_cover').val() > 0 && $('#durometer_new_belt').val() > 0) {
    if ($('#original_top_cover').val() > 0 && $('#durometer_new_belt').val() > 0) {
        $('#save_ultrasonic_conveyor_data').loadingButton('enabled');//Habilitamos el boton de guardar    
    } else {
        $('#save_ultrasonic_conveyor_data').loadingButton('disabled');//Deshabilitamos el boton de guardar
    }
}

function changeDateUltra(){
    var selected_date = $('#install_update_ultra').val();
    var fixed_date = timestampToUsDate($('#install_update_ultra').val());
    $('input#saved_date_ultra').val(selected_date);
    $('#install_update_ultra').val(fixed_date);
    $('#install_update_ultra').datepicker( "hide" );
}

function getMetaUnitFieldsUltra(){
    var fieldUnitsData = "";
    $('.unit-indicator:not(.disabled)').each(function(){
        var $unitSelector = $(this);
        var input_name = $(this).data('field');

        fieldUnitsData += input_name+'='+$unitSelector.html()+'||';
    });

    fieldUnitsData = fieldUnitsData=='' ? '' : fieldUnitsData.slice(0,-2);
    return fieldUnitsData;
}

function initEventsUltrasonic() {

    if(meta_units_ultra!=''){
        var complementary_units = getComplementaryUnits();
        var fieldsInfo = meta_units_ultra.split('||');
        $.each(fieldsInfo, function(index, fieldUnit){
            var fieldData = fieldUnit.split('=');
            var field_name = fieldData[0];
            var field_unit = fieldData[1];

            if($("[name='"+field_name+"']").size()>0){ //field exists
                var $field = $("[name='"+field_name+"']");
                var $containerField = $field.closest('tr');
                $containerField.find('.label-field').after(' (<a href="#" data-field="'+field_name+'" class="unit-indicator" data-units="'+field_unit+'|'+complementary_units[field_unit]+'">'+field_unit+'</a>)');
            }
        });
    }

    $.datepicker.setDefaults({
        showOtherMonths: true,
        selectOtherMonths: true,
        changeYear: true,
        yearRange: "-30:+0",
        onClose: function(dateText,inst){
            changeDateUltra();
        },
        beforeShow: function () {
            $('input#install_update_ultra').datepicker( "setDate", $('input#saved_date_ultra').val() );
        }
    }, $.datepicker.regional[ "" ]);
    
    $('input#install_update_ultra').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    //$('input#install_update_ultra').datepicker( "setDate", $('#saved_date_ultra').val() );

    /*$('#install_update_ultra').change(function(e){
        changeDateUltra();
    });*/

    //$('input#install_update_ultra').val($('#saved_date_ultra').val());
    //$('input#install_update_ultra').trigger("change");
    //changeDateUltra();
    
    $('.title-page').tipsy({gravity: 's', fade: true});
    
    $("#compound_name,#ultrasonic_width,#units_ultrasonic").chosen({
        disable_search: true,
        width: "120px"
    });

    $('#ultrasonic_width').change(function(){
        var selected = $(this).val();
        if(selected=='Otro' || selected=='Other'){
            var container_items = $('input[name="other_width"]').closest('.closable-input-ctrl');
            container_items.find('*').removeClass('hidden');
            $('#ultrasonic_width_chosen').hide();
        }
    })

    $('#close_other_width').click(function(e){
        e.stopPropagation();
        $('#ultrasonic_width_chosen').show();
        $('input[name="other_width"]').val('');
        var container_items = $('input[name="other_width"]').closest('.closable-input-ctrl');
        container_items.find('*').addClass('hidden');
        $('#ultrasonic_width').val('').trigger("chosen:updated");
        $('#ultrasonic_width').trigger("change");
        return false;
    });

    if($('input[name="other_width"]').val()!=''){
        $("#ultrasonic_width option:last").attr("selected", "selected");
        $('#ultrasonic_width').trigger("change");
    }

    $('#units_ultrasonic').change(function(e){
        var unitType = $(this).val();


        //Cambiamos el label de los campos
        var indexSelectedUnit = ($('#units_ultrasonic option').index($('#units_ultrasonic option:selected')))-1;
        var indexOtherUnit = indexSelectedUnit==0 ? 1 : 0;

        $('.unit-indicator').each(function(){
            var $unitSelector = $(this);
            var units = $unitSelector.data('units')
            units = units.split('|');
            var selectedUnit = units[indexSelectedUnit];
            //var otherUnit = units[indexOtherUnit];
            //$unitSelector.attr('title', otherUnit);
            $unitSelector.html(selectedUnit);
        });

        var widths = unitType=='metric' ? ultrasonic_widths_metric:ultrasonic_widths_imperial;
        $('#ultrasonic_width').html('');
        $.each(widths, function(width, points){
            $('#ultrasonic_width').append('<option value="' + points + '">' + points + '</option>');
        })
        $("#ultrasonic_width").trigger("chosen:updated");;
    });

    $("select#compound_name,select#ultrasonic_width").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("select#compound_name,select#ultrasonic_width").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');        
    });

    $("#original_top_cover").inputmask("decimal", {placeholder: '0.000', digits: 3, });
    $("#durometer_new_belt").inputmask("numeric", {placeholder: '0'});
    $("#conveyor_price").inputmask("decimal", {
        radixPoint: ".",
        groupSeparator: ",",
        digits: 2,
        autoGroup: true,
        prefix: '$',
    });

    //$('#conveyor_price, #original_top_cover, #durometer_new_belt').keyup(function(e) {
    $('#original_top_cover, #durometer_new_belt').keyup(function(e) {
        toggleSaveUltrasonic();
    });
    
    
    $('#save_ultrasonic_conveyor_data').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            var metaUnits = getMetaUnitFieldsUltra();
            var price = $.trim($('#conveyor_price').val()).length > 1 ? $('#conveyor_price').val() :  '0';
            var width = $('input[name="other_width"]').is(':visible') ? $('input[name="other_width"]').val() : $("#ultrasonic_width option:selected").val();
            var other_width = $('input[name="other_width"]').is(':visible') ? "yes" : "no";

            if(other_width=='yes' && $.trim(width)==''){
                $.coconotif.add({
                    text: jsVars.otherWidthNeeded,
                    time: jsVars.timeNotifications
                });
                btn.loadingButton('stop', -1);
            }else{
                $.ajax({
                    type: 'post',
                    url: jsVars.updateUltrasonicDataAx,
                    data: {
                        conveyor_brand_ultra: $('#conveyor_brand_ultra').val(),
                        install_update_ultra: $('#install_update_ultra').val(),
                        compound_name: $("#compound_name option:selected").val(),
                        original_top_cover: $('#original_top_cover').val(),
                        ultrasonic_width: width,
                        other_width: other_width,
                        conveyor_price: price,
                        units: $('#units_ultrasonic').val(),
                        meta_units: metaUnits,
                        durometer_new_belt: $('#durometer_new_belt').val(),
                        position: $('#position_ultra').val(),
                        conveyor_id: $('#conveyor_assoc').val()
                    },
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                processed_operation = true;
                                callUpdateSite();
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
            }


            return false;
        }
    });

    //toggleSaveUltrasonic();

    $('.delete-reading-link').click(function(e) {
        e.stopPropagation();
        $.conti.confirm({msg: jsVars.dialogs.confirmAction.description, type: 'conveyor-dialog', callbackOk: 'removeUltrasonicReading', paramsOk: {invoker: $(this)} });
        return false;
    });

    $('#conveyor_details_link,.toggle-btn-link').click(function(e) {
        e.stopPropagation();
        var $accordSection = $(this).next();
        if($accordSection.is(':visible')){
            $accordSection.find('.info-gauge').fadeOut();
        }
        $accordSection.slideToggle("slow", function() {
            if($accordSection.is(':visible')) {
                $accordSection.find('.info-gauge').fadeIn();
            }
        });
        return false;
    });

    $('.conveyor-opt-link:not(.disabled-btn)').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var callback = $(this).attr('assoc-callback');
        var style = $(this).attr('dialog-style');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            data: {conveyor: 1},
            success: function(response) {
                try {
                    //if no parse JSON is HTML
                    response = $.parseJSON(response);
                    //if parse JSON, is error
                    $('body').cocoblock('unblock');
                    $.conti.alert({msg: response.msg, type: 'error-dialog'});
                } catch (e) {
                    $.conti.dialog({html: response, style: style, modal: true, callbackOpen: callback});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });
}

function setTestData() {
    $('.grid_fields tbody tr:not(.result-rows)').each(function() {
        var cell_data = $(this).find('td');
        if (typeof (cell_data[0]) != 'undefined' && !cell_data.hasClass('separator-row')) {
            cell_data.find('input').val('0.239');
        }
    });
}
function calcAvgs() {
    //Solo las columnas de datos
    for (var column = 1; column <= 7; column++) {
        var sumatoria = 0;
        var columns_filled = 0;
        $('.grid_fields tbody tr:not(.result-rows)').each(function() {
            var cell_data = $(this).find('td').eq(column);
            if (typeof (cell_data[0]) != 'undefined' && !cell_data.hasClass('separator-row')) {
                var data = cell_data.find('input').val();
                if ($.trim(data) != '' && parseFloat(data)>0) {//se valida > 0
                    sumatoria += parseFloat(data);
                    columns_filled++;
                }
            }
        });

        var avgs = getAvgs(sumatoria, columns_filled);
        var row_avg = $('.result-rows.avg').find('td').eq(column);
        var row_avg_temp = $('.result-rows.temp-avg').find('td').eq(column);
        row_avg.find('input').attr('data-realvalue',avgs[0]);
        row_avg.find('input').val(avgs[0].toFixed(3));        
        row_avg_temp.find('input').attr('data-realvalue',avgs[1]);
        row_avg_temp.find('input').val(avgs[1].toFixed(3));
    }
}

/*Calcula los promedios y los regresa **/
function getAvgs(sumatoria, columns) {
    var avg = avg_temp = 0;
    if (sumatoria > 0) {
        //var compound_name = 'STACKER';
        var compound_name = $("#compound_name option:selected").val();
        
        avg = (sumatoria / columns);
        if(units_ultrasonic=='metric'){
            avg = avg * 0.0393701; //Convertir a inches, la formular de avg adj temp, usa inches
        }
        var temperature = $.trim($("input[name='temperature']").val()) != '' ? $.trim($("input[name='temperature']").val()) : 0;

        //console.log(units_ultrasonic);
        //if (jsVars.systemLanguage == 'es') {//Si el sistema esta en español se toma como que se esta capturando celsius
        if(units_ultrasonic=='metric'){
            temperature = parseFloat(temperature * 1.8) + 32;//Conversion a farenheit
        } else {
            temperature = temperature * 1;
        }

        avg_temp = 0;
        if (avg != 0) {
            avg_temp = (70 - temperature) * parseFloat(jsVars.compoundNames[compound_name][0] * (Math.pow(avg, 2)) + (jsVars.compoundNames[compound_name][1] * avg) + jsVars.compoundNames[compound_name][2]);
            avg_temp = parseFloat(avg_temp + avg);
        }
    }

    //avg = avg.toFixed(7);
    //avg_temp = avg_temp.toFixed(7);
    return [avg, avg_temp];
}

/**Obtiene los avgs previamente calculados de cada columna **/
function getAvgData() {
    var grid_data = '', tmp_data = '';
    $('.grid_fields tbody tr.result-rows').each(function() {
        tmp_data = '';
        $(this).find('td:not(:first-child)').each(function() {
            //var cell_data = $(this).find('input').val();
            var cell_data = $(this).find('input').data('realvalue');            
            tmp_data += $.trim(cell_data) + ',';
        });
        tmp_data = tmp_data != '' ? tmp_data.slice(0, -1) : tmp_data;
        grid_data += tmp_data + '||';
    });

    return grid_data.slice(0, -2);
}

function getGridData() {
    var grid_data = '', tmp_data = '';
    $('.grid_fields tbody tr:not(.result-rows)').each(function() {
        tmp_data = '';
        $(this).find('td:not(.empty):not(:first-child)').each(function() {
            var cell_data = $(this).find('input').val();
            tmp_data += $.trim(cell_data) + ',';
        });
        tmp_data = tmp_data != '' ? tmp_data.slice(0, -1) : tmp_data;
        grid_data += tmp_data + '||';
    });

    return grid_data.slice(0, -2);
}


function initEventsUltrasonicData() {
    //setTestData();
    calcAvgs();
    
    $('input[name="capture_date"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    $('input[name="capture_date"]').change(function(e){
        var fixed_date = timestampToUsDate($(this).val());
       $(this).val(fixed_date);
       $('input[name="capture_date"]').datepicker( "hide" );
    });
    
    $('.grid_fields input:not([readonly])').inputmask("decimal", {placeholder: '0.000', digits: 3});
    $("input[name='temperature'],input[name='shore_durometer']").inputmask("decimal", {placeholder: '0', digits: 2});
    $("input[name='tons_conveyed']").inputmask("decimal", {
        radixPoint: ".",
        groupSeparator: ",",
        digits: 2,
        autoGroup: true,
        prefix: ''
    });

    $('input[name="temperature"]').keyup(function(e) {
        calcAvgs();
    });
    
    $('.grid_fields input:not([readonly])').keyup(function(e) {
        var row = $(this).closest('tr');
        var cell = $(this).parent();
        var column = row.find('td').index(cell);

        var columns = 0;
        var sumatoria = 0;
        $('.grid_fields tbody tr:not(.result-rows)').each(function() {
            var cell_data = $(this).find('td').eq(column);
            if (typeof (cell_data[0]) != 'undefined' && !cell_data.hasClass('separator-row')) {
                var data = cell_data.find('input').val();
                if ($.trim(data) != '' && parseFloat(data)>0) {//se valida > 0
                    sumatoria += parseFloat(data);
                    columns++;
                }
            }
        });

        var avgs = getAvgs(sumatoria, columns);
        var row_avg = $('.result-rows.avg').find('td').eq(column);
        var row_avg_temp = $('.result-rows.temp-avg').find('td').eq(column);
        row_avg.find('input').attr('data-realvalue',avgs[0]);
        row_avg.find('input').val(avgs[0].toFixed(3));        
        row_avg_temp.find('input').attr('data-realvalue',avgs[1]);
        row_avg_temp.find('input').val(avgs[1].toFixed(3));

    });


    $('#save_ultrasonic').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            var readings = getGridData();
            var avgs = getAvgData();
            
            $.ajax({
                type: 'post',
                url: $("#ultrasonic_data_form").attr('action'),
                data: {
                    reading_id: $("input[name='ultrasonic_reading_id']").val(),
                    reading_date: $("input[name='capture_date']").val(),
                    temperature: $("input[name='temperature']").val(),
                    conveyed_tons: $("input[name='tons_conveyed']").val(),
                    durometer: $("input[name='shore_durometer']").val(),
                    readings: readings,
                    avgs: avgs
                },
                success: function(response) {
                    try {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('disabled');//Habilitamos el boton de guardar
                            btn.loadingButton('stop', 1);
                            $('#dialog_wrap').dialog('close');
                            processed_operation = true;
                            callUpdateSite();
                        } else {
                            btn.loadingButton('stop', -1);
                        }
                    } catch (e) {
                        btn.loadingButton('stop', -1);
                        $.conti.alert({msg: e.message, type: 'error-dialog'});
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
}

function removeUltrasonicReading(invoker) {
    var encriptedUrlProcess = invoker.data('remove');
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            response = $.parseJSON(response);

            if (response.success) {
                $.cookie("conti_notification", response.msg, {path: '/'}); // Sample 1
                window.location.reload();
            }else{
                $('body').cocoblock('unblock');
                $.coconotif.add({
                    text: response.msg,
                    time: jsVars.timeNotifications
                });
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}