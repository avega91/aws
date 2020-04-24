/*
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file monitoring.js
 *     Events for monitoring section
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2018
 */
var row_data_color = null;
var row_data_ppt = null;
var row_data_fill_level = null;
//var min_hour = max_hour = null;
var chart_measure_data = null;
var chart_events_data = chart_events_list = null;
var section_selected = '';
var chart_week_color_data = null;
var chart_week_color_data_ppt = null;

$(document).ready(function() {
    var alemania = moment.tz("Europe/Berlin").toDate();
    var alemania_6minus = moment.tz("Europe/Berlin").subtract(6, 'hours').toDate();
    alemania = getDateGermany(alemania);
    alemania_6minus = getDateGermany(alemania_6minus);

    google.charts.load('current', {'packages':['corechart','table','bar','line']});

    $('#conti_menu > li > a').click(function(e) {
        e.stopPropagation();
        var $option = $(this);
        var $parentLi = $option.parent();
        var $indexLiSelected = $('#conti_menu > li').index($parentLi);
        $('#filter_period').addClass('hidden');
        $('.monitoring-tab').addClass('hidden');

        $('.submenu a').removeClass('active');
        $('#conti_menu > li > a.active + ul').slideToggle("slow");
        if($option.hasClass('active')){
            $('#conti_menu > li > a').removeClass('active');
        }else{
            $('#conti_menu > li > a').removeClass('active');
            $option.addClass('active');
            $option.next().slideToggle("slow");

            $option.next().find('li:first-child > a').trigger('click');

            var assocSection = $option.attr('rel');
            if(assocSection!=''){
                section_selected = assocSection;
                drawChartsForSection();
            }

        }

        return false;
    });

    $('.submenu a').click(function(e) {
        e.stopPropagation();
        var $option = $(this);
        $('.submenu a').removeClass('active');
        $option.addClass('active');

        var assocSection = $option.attr('rel');
        section_selected = assocSection;

        drawChartsForSection();

        return false;
    });

    var formatDate = jsVars.systemLanguage == 'es' ? 'dd/mm/yy' : 'mm/dd/yy';
    $.datepicker.setDefaults({
        dateFormat: formatDate,
        //numberOfMonths: [1, 2],
        showOn: "both",
        showOtherMonths: true,
        selectOtherMonths: true,
        buttonImage: jsVars.datePickerIcon,
        buttonImageOnly: true,
        onSelect: function(dateText, inst) {
            $("#date_selected").val(dateText);
            //$('.monitoring-options a.active').trigger('click');
            //getColorSensorData();
            drawChartsForSection();
        }

    }, $.datepicker.regional[ "" ]);

    $("#date_selected").datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    //$("#date_selected").val($('#current_date').val());

    function drawChartsForSection(){
        $('#filter_period').removeClass('hidden');

        $('.monitoring-tab').addClass('hidden');
        $('#volume-sensor-wrapper').html('');
        $('#color-sensor-wrapper').html('');
        $('#charts-overview').html('');
        $('#predictive-maintenance-wrapper').html('');
        switch (section_selected){
            case 'volume-sensor':
                $('#volume-sensor-wrapper').removeClass('hidden');
                $('#volume-sensor-wrapper').html('<div id="measure_fill_char" class="chart-container"></div>' +
                    '<div id="fill_level_char" class="chart-container last"></div>');

                getFillLevelData(section_selected);
                break;

            case 'color-sensor':
                $('#color-sensor-wrapper').removeClass('hidden');
                $('#color-sensor-wrapper').html('<div id="color_sensor_char" class="chart-container"></div>' +
                    '<div id="color_total_ppt" class="chart-container last"></div>');

                getColorSensorData(section_selected);
                break;

            case 'overview':
                $('#filter_period').addClass('hidden');
                $('#overview-wrapper').removeClass('hidden');
                $("#date_selected").datepicker("setDate", $("#current_date").val());

                $('#charts-overview').html('<div id="color_sensor_char" class="chart-container"></div>' +
                    '<div id="color_total_ppt" class="chart-container last"></div>' +
                    '<div id="event_day_char" class="chart-container"></div>' +
                    '<div id="list_events_char" class="chart-container last"></div>' +
                    '<div id="color_sensor_week" class="chart-container"></div>' +
                    '<div id="color_total_ppt_week" class="chart-container last"></div>' +
                    '<div id="measure_fill_char" class="chart-container"></div>');
                getColorSensorData(section_selected);
                break;
            case 'predictive-maintenance':
                $('#predictive-maintenance-wrapper').removeClass('hidden');

                $('#predictive-maintenance-wrapper').html('<div id="event_day_char" class="chart-container"></div>' +
                    '<div id="list_events_char" class="chart-container last"></div>');
                getMaintenanceData(section_selected);
            break;
        }
    }


    function drawTableEvents(){
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Priority');
        data.addColumn('string', 'Time');
        data.addColumn('string', 'Part');
        data.addColumn('string', 'Event');
        data.addRows(chart_events_list);

        var options = {
            allowHtml: true,
            showRowNumber: false,
            width: '100%',
            height: '100%',
            page: 'enable',
            pageSize: 5
        };

        var table = new google.visualization.Table(document.getElementById('list_events_char'));

        table.draw(data, options);
    }
    function drawChartEvents() {
        var headers = ['Time', 'Event', { role: 'style' } ];

        $.each(chart_events_data,function (index, data) {
            data[0] = new Date(data[0]*1000);
            chart_events_data[index] = data;
        });
        chart_events_data.unshift(headers);
        var data = google.visualization.arrayToDataTable(chart_events_data);
        var empty_data = chart_events_data.length==2 && chart_events_data[1][1]==0 ? true: false;

        var options = {
            title: 'Events today',
            titleTextStyle: {color: '#969B96'},
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:40, right:20, top:80,width:'90%'},
            legend: { position: 'none' },
            bar: {
                groupWidth: 2
            },
            hAxis: {
                title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                textPosition: 'bottom',
                format: 'H:mm',
                showTextEvery: 1,
                gridlines: {
                    color: '#FFFFFF',
                },
                viewWindow: section_selected=='overview' || empty_data ? {
                    min: alemania_6minus,
                    max: alemania
                }:{},
            },
            vAxis: {
                minValue: 0,
                maxValue:1,
                baselineColor: '#1041FB',
                gridlines: {
                    count: 2,
                },
                textStyle: {
                    color: '#cccccc'
                },
            },
            enableInteractivity: true,
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },

        };
        var chart = new google.visualization.ColumnChart(document.getElementById('event_day_char'));
        chart.draw(data, options);
    }

    function drawChartColorPPT() {
        var headers = ['Time', 'Σ'];
        var initial_data = google.visualization.arrayToDataTable([
            headers,
            ['0', 0],
        ]);

        $.each(row_data_ppt,function (index, data) {
            data[0] = new Date(data[0]*1000);
            row_data_ppt[index] = data;
        })

        row_data_ppt.unshift(headers);
        var data = google.visualization.arrayToDataTable(row_data_ppt);

        var empty_data = row_data_ppt.length==2 && row_data_ppt[1][1]==0 ? true: false;

        var options = {
            title: 'Total PPT (€)',
            titleTextStyle: {color: '#969B96'},
            series: {
                0: { color: '#000000' },
            },
            curveType: 'function',
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:40, right:20, top:80,width:'90%'},
            legend: { position: 'none', textStyle: { fontSize: 10 } },
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                baselineColor: '#cccccc',
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            hAxis: {
                title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                gridlines:{
                    color: '#FFFFFF',
                },
                textPosition: 'bottom',
                format: 'H:mm',
                showTextEvery: 1,
                viewWindow: section_selected=='overview' || empty_data ? {
                    min: alemania_6minus,
                    max: alemania
                }:{},
            },
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },
        };

        var chart = new google.visualization.LineChart(document.getElementById('color_total_ppt'));
        chart.draw(initial_data, options);
        chart.draw(data, options);
    }


    function drawWeekColorDataPPT() {
        var headers = ['day', 'alls'];
        var initial_data = google.visualization.arrayToDataTable([
            headers,
            [0, 0],
        ]);

        $.each(chart_week_color_data_ppt,function (index, data) {
            data[0] = new Date(data[0]*1000);//javascrit manage time in miliseconds unix
            chart_week_color_data_ppt[index] = data;
        })

        chart_week_color_data_ppt.unshift(headers);
        var data = google.visualization.arrayToDataTable(chart_week_color_data_ppt);


        var options = {
            title: 'Total PPT per day (€)',
            titleTextStyle: {color: '#969B96', fontSize: 12},
            series: {
                0: { color: '#FFA500' },
            },
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:50, top:80, right: 20, width:'100%'},
            legend: { position: 'none'},
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                baselineColor: '#cccccc',
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            hAxis: {
                //title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                textPosition: 'bottom',
                format: 'dd/MMM',
                showTextEvery: 1,
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },
        };

        var chart = new google.visualization.LineChart(document.getElementById('color_total_ppt_week'));
        chart.draw(initial_data, options);
        chart.draw(data, options);

    }

    function drawWeekColorData() {
        var headers = ['day', 'red', 'green'];
        var initial_data = google.visualization.arrayToDataTable([
            headers,
            [0, 0, 0],
        ]);

        $.each(chart_week_color_data,function (index, data) {
                data[0] = new Date(data[0]*1000);//javascrit manage time in miliseconds unix
            chart_week_color_data[index] = data;
        })

        chart_week_color_data.unshift(headers);
        var data = google.visualization.arrayToDataTable(chart_week_color_data);


        var options = {
            title: 'Material conveyed per day (tons)',
            titleTextStyle: {color: '#969B96', fontSize: 12},
            series: {
                0: { color: '#ED7D31' },
                1: { color: '#00FF00' },
            },
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:50, top:80, right: 20, width:'100%'},
            legend: { position: 'none'},
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                baselineColor: '#cccccc',
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            hAxis: {
                //title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                textPosition: 'bottom',
                format: 'dd/MMM',
                showTextEvery: 1,
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('color_sensor_week'));
        chart.draw(initial_data, options);
        chart.draw(data, options);

    }

    function drawChartColorSensor() {
        var headers_colors = ['Time', 'Σ yellow', 'Σ blue', 'Σ green', 'Σ red'];
        var initial_data = google.visualization.arrayToDataTable([
            headers_colors,
            ['0', 0, 0 , 0, 0],
        ]);

        $.each(row_data_color,function (index, data) {
            if(index>0){
                data[0] = new Date(data[0]*1000);//javascrit manage time in miliseconds unix
                row_data_color[index] = data;
            }
        })

        //row_data_color.unshift(headers_colors);
        var data = google.visualization.arrayToDataTable(row_data_color);
        var colors = (row_data_color[0]).length<=3 ? {0:{color: '#00FF00'},1:{color: '#ED7D31'}}:{
            0: { color: '#FFD700' },//era black
            1: { color: '#5B9BD5' },
            2: { color: '#00FF00' },
            3: { color: '#ED7D31' },
        };

        var empty_data = row_data_color.length==2 && row_data_color[1][1]==0 ? true: false;

        var options = {
            title: 'Material conveyed (tons)',
            titleTextStyle: {color: '#969B96', fontSize: 12},
            series: colors,
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:30,top:80,width:'68%'},
            legend: { position: 'right', textStyle: { fontSize: 12 } },
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                baselineColor: '#cccccc',
                gridlines:{
                    color: '#FFFFFF',
                },

            },
            hAxis: {
                title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                textPosition: 'bottom',
                format: 'H:mm',
                showTextEvery: 1,
                gridlines:{
                    color: '#FFFFFF'
                },
                viewWindow: section_selected=='overview' || empty_data ? {
                    min: alemania_6minus,
                    max: alemania
                }:{},
            },
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },
        };

        var chart = new google.visualization.LineChart(document.getElementById('color_sensor_char'));
        chart.draw(initial_data, options);
        chart.draw(data, options);

    }

    function drawChartMeasure(){
        var headers = ['', ''];
        var initial_data = google.visualization.arrayToDataTable([
            headers,
            ['', 0],
        ]);

        headers = chart_measure_data.headers
        var data = google.visualization.arrayToDataTable([
            headers,
            chart_measure_data.values
        ]);
        var options = {
            title: 'Last measured fill level',
            titleTextStyle: {color: '#969B96'},
            enableInteractivity: false,
            chartArea: {left:120,right:120, top:80, width:'100%'},
            legend: { position: 'none', maxLines: 3 },
            bar: { groupWidth: '100%' },
            isStacked: true,
            animation: {
                startup: true,
                duration: 2000,
                easing: 'linear'
            },
            series: {
                0: { color: '#000000' },
            },
            hAxis: {
                titleTextStyle: {
                    fontSize: 13
                },
                textStyle: {
                    color: '#cccccc'
                },
            },
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                showTextEvery: 1,
                baselineColor: '#cccccc',
                gridlines: {
                    count: 11,
                    color: '#FFFFFF'
                },
                minValue: 0,
                maxValue:100,
            },
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('measure_fill_char'));
        options.series = chart_measure_data.colors;
        options.vAxis.maxValue = chart_measure_data.maxPercent;
        chart.draw(data, options);
    }

    function drawChartFillLevel() {
        var headers = ['Time', '%'];
        var initial_data = google.visualization.arrayToDataTable([
            headers,
            ['0', 0],
        ]);

        $.each(row_data_fill_level,function (index, data) {
            data[0] = new Date(data[0]*1000);
            row_data_fill_level[index] = data;
        })

        row_data_fill_level.unshift(headers);
        var data = google.visualization.arrayToDataTable(row_data_fill_level);

        var empty_data = row_data_fill_level.length==2 && row_data_fill_level[1][1]==0 ? true: false;

        var options = {
            title: 'Fill level',
            titleTextStyle: {color: '#969B96'},
            series: {
                0: { color: '#000000' },
            },
            animation: {
                duration: 2000,
                easing: 'in'
            },
            chartArea: {left:50,right:20, top:80,width:'100%'},
            legend: { position: 'none', textStyle: { fontSize: 10 } },
            vAxis: {
                textStyle: {
                    color: '#cccccc'
                },
                baselineColor: '#cccccc',
                gridlines:{
                    color: '#FFFFFF',
                },
            },
            hAxis: {
                title:"Germany Time (GMT+2)",
                titleTextStyle: {
                    color: '#FFA500'
                },
                textStyle: {
                    color: '#cccccc'
                },
                gridlines:{
                    color: '#FFFFFF',
                },
                textPosition: 'bottom',
                format: 'H:mm',
                showTextEvery: 1,
                viewWindow: section_selected=='overview' || empty_data ? {
                    min: alemania_6minus,
                    max: alemania
                }:{},
            },
            explorer: {
                actions: ['dragToZoom', 'rightClickToReset'],
                axis: 'horizontal',
                keepInBounds: true,
                maxZoomIn: 4.0
            },
        };

        var chart = new google.visualization.LineChart(document.getElementById('fill_level_char'));
        chart.draw(initial_data, options);
        chart.draw(data, options);
    }

    function getFillLevelData(section){
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.fillLevelInfoUrl,
            data: { date: $("#date_selected").val(),section: section},
            success: function(response) {
                $('body').cocoblock('unblock');
                response = $.parseJSON(response);

                chart_measure_data = response.chart_data.measure_data;
                row_data_fill_level = response.chart_data.fill_level;
                google.charts.setOnLoadCallback(drawChartMeasure);
                google.charts.setOnLoadCallback(drawChartFillLevel);

            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    }

    function getMaintenanceData(section){
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.predictiveMaintenanceInfoUrl,
            data: { date: $("#date_selected").val(),section: section},
            success: function(response) {
                $('body').cocoblock('unblock');
                response = $.parseJSON(response);

                chart_events_data = response.chart_data.events_day_data;
                chart_events_list = response.chart_data.events_day_list;

                google.charts.setOnLoadCallback(drawChartEvents);
                google.charts.setOnLoadCallback(drawTableEvents);

            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    }

    function getColorSensorData(section){
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.colorSensorInfoUrl,
            data: { date: $("#date_selected").val(),section: section},
            success: function(response) {
                $('body').cocoblock('unblock');
                response = $.parseJSON(response);

                row_data_color = response.chart_data.color_data;
                row_data_ppt = response.chart_data.ppt_data;

                google.charts.setOnLoadCallback(drawChartColorSensor);
                google.charts.setOnLoadCallback(drawChartColorPPT);

                if(section == 'overview'){
                    chart_measure_data = response.chart_data.measure_data;
                    chart_events_data = response.chart_data.events_day_data;
                    chart_events_list = response.chart_data.events_day_list;
                    chart_week_color_data = response.chart_data.color_data_week;
                    chart_week_color_data_ppt = response.chart_data.color_data_week_ppt;
                    google.charts.setOnLoadCallback(drawChartMeasure);
                    google.charts.setOnLoadCallback(drawChartEvents);
                    google.charts.setOnLoadCallback(drawTableEvents);
                    google.charts.setOnLoadCallback(drawWeekColorData);
                    google.charts.setOnLoadCallback(drawWeekColorDataPPT);

                    $('#panel_data .red-monitor > b').html(response.overview_data.red_tons);
                    $('#panel_data .green-monitor > b').html(response.overview_data.green_tons);
                    $('#panel_data .credit-today > span').html(response.overview_data.credit_today);
                    $('#panel_data .total-ppt-today > span').html(response.overview_data.total_ppt_today);
                    $('#panel_data .total-ppt-month > span').html(response.overview_data.total_ppt_month);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    }

    function getDateGermany(localDate)
    {
        var secondstoGermany = 3600*2;
        /*
        timestamp = timestamp + (3600*2);//2 horas dif GMT
// Multiply by 1000 because JS works in milliseconds instead of the UNIX seconds
        var date = new Date(timestamp * 1000);

        var year = date.getUTCFullYear();
        var month = date.getUTCMonth() + 1; // getMonth() is zero-indexed, so we'll increment to get the correct month number
        var day = date.getUTCDate();
        var hours = date.getUTCHours();
        var minutes = date.getUTCMinutes();
        var seconds = date.getUTCSeconds();

        month = (month < 10) ? '0' + month : month;
        day = (day < 10) ? '0' + day : day;
        hours = (hours < 10) ? '0' + hours : hours;
        minutes = (minutes < 10) ? '0' + minutes : minutes;
        seconds = (seconds < 10) ? '0' + seconds: seconds;

        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;*/
        return new Date(localDate.getTime() + (localDate.getTimezoneOffset() * 60 * 1000) + (secondstoGermany*1000));
    }

});


