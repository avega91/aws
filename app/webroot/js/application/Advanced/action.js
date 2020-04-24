/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file action.js
 *     Events for advanced section dashboard
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$(document).ready(function() {
    initAdvancedDatatableEvents();
/*
    google.setOnLoadCallback(drawChart);
    function drawChart() {

        //Grafica de visitas totales
        var data = google.visualization.arrayToDataTable([
            ['Visitas', 'Total'],
            [jsVars.visitas.regresan[0], jsVars.visitas.regresan[1]],
            [jsVars.visitas.nuevos[0], jsVars.visitas.nuevos[1]]
        ]);

        var options = {
            title: jsVars.visitas.total[0],
            backgroundColor: 'transparent',
            titleTextStyle: {color: '#707571', fontSize: 17, bold: false},
            legend: {
                position: 'left',
                textStyle: {color: '#707571', fontSize: 15}
            },
            slices: {
                0: {color: '#00a5dc'},
                1: {color: '#2db928'}
            },
            height: 350,
            chartArea: {width: 450},
            tooltip: {textStyle: {color: '#707571'}}
        };
        var chart = new google.visualization.PieChart(document.getElementById('visits_graphic'));
        chart.draw(data, options);



        //Grafica de visitas por pais
        var array_data = [['Visitas', 'Total']];
        $.each(jsVars.visitas_por_pais, function(index, value) {
            array_data.push([value[0], value[1]]);
        });
        data = google.visualization.arrayToDataTable(array_data);
        var options = {
            title: jsVars.tituloPorPais,
            backgroundColor: 'transparent',
            titleTextStyle: {color: '#707571', fontSize: 17, bold: false},
            legend: {
                position: 'left',
                textStyle: {color: '#707571', fontSize: 15}
            },
            slices: {
                0: {color: '#00a5dc'},
                1: {color: '#2db928'},
                2: {color: '#ff2d37'},
                3: {color: '#ffa500'},
                4: {color: '#004eaf'}

            },
            height: 350,
            chartArea: {width: 450},
            tooltip: {textStyle: {color: '#707571'}}
        };

        var chart_countries = new google.visualization.PieChart(document.getElementById('country_visits_graphic'));
        chart_countries.draw(data, options);


        //Grafica de visitas por navegador
        var colorStyles = ['#00a5dc', '#2db928', '#ff2d37', '#ffa500', '#004eaf']
        array_data = [['Browser', 'Visitas', {role: 'style'}, {role: 'annotation'}]];
        $.each(jsVars.visitas_por_browser, function(index, value) {
            array_data.push([value[0], value[1], colorStyles[index], value[2]]);
        });
        data = google.visualization.arrayToDataTable(array_data);


        var options = {
            backgroundColor: 'transparent',
            title: jsVars.tituloPorBrowser,
            titleTextStyle: {color: '#707571', fontSize: 17, bold: false},
            legend: {position: "none"},
            chartArea: {width: 150},
            vAxis: {textColor: '#707571', textStyle: {fontSize: 15}},
            tooltip: {textStyle: {color: '#707571'}}
        };

        var chart = new google.visualization.BarChart(document.getElementById('browser_visits_graphic'));

        chart.draw(data, options);

        //Grafica de visitas por sistema operativo
        var colorStyles = ['#00a5dc', '#2db928', '#ff2d37', '#ffa500', '#004eaf']
        array_data = [['Os', 'Visitas', {role: 'style'}, {role: 'annotation'}]];
        $.each(jsVars.visitas_por_os, function(index, value) {
            array_data.push([value[0], value[1], colorStyles[index], value[2]]);
        });
        data = google.visualization.arrayToDataTable(array_data);


        var options = {
            backgroundColor: 'transparent',
            title: jsVars.tituloPorOs,
            titleTextStyle: {color: '#707571', fontSize: 17, bold: false},
            legend: {position: "none"},
            chartArea: {width: 180},
            vAxis: {textColor: '#707571', textStyle: {fontSize: 15}},
            tooltip: {textStyle: {color: '#707571'}}
        };

        var chart = new google.visualization.BarChart(document.getElementById('os_visits_graphic'));

        chart.draw(data, options);

        if (jsVars.visitas_por_seccion.length) {
            //Grafica de visitas por seccion
            var colorStyles = ['#00a5dc', '#2db928', '#ff2d37', '#ffa500', '#004eaf']
            array_data = [['Seccion', 'Visitas', {role: 'style'}, {role: 'annotation'}]];
            $.each(jsVars.visitas_por_seccion, function(index, value) {
                array_data.push([value[0], value[1], colorStyles[index], value[2]]);
            });
            data = google.visualization.arrayToDataTable(array_data);


            var options = {
                backgroundColor: 'transparent',
                title: jsVars.tituloPorSeccion,
                titleTextStyle: {color: '#707571', fontSize: 17, bold: false},
                legend: {position: "none"},
                chartArea: {width: 180},
                vAxis: {textColor: '#707571', textStyle: {fontSize: 15}},
                tooltip: {textStyle: {color: '#707571'}}
            };

            var chart = new google.visualization.BarChart(document.getElementById('section_visits_graphic'));

            chart.draw(data, options);
        }
    }*/
});

function processSearch(){
    var active_tab = $('#conti_menu a.active').attr('rel');
            $('#filter_date_form input[name="active_tab"]').val(active_tab);
            $('#filter_date_form input[name="query_user"]').val($("#generic_search input").val());
            $('#filter_date_form input[name="ini_date"]').val($("#input1").val());
            $('#filter_date_form input[name="end_date"]').val($("#input2").val());
            $('body').cocoblock();
            $('#filter_date_form').submit();
}

function setDataSearch() {
    if (typeof (jsVars.query) !== 'undefined' && jsVars.query!='') {
        $('#generic_search input').val(jsVars.query);
        $('#generic_search input').trigger('focus');
    }
}

function initAdvancedDatatableEvents() {
    $(":input[placeholder]").placeholder();

    $("#generic_search input").val('');
    $('#generic_search a.close').click(function(e) {
        $('#generic_search input').val('');
        processSearch();
    });

    if ($('#conti_menu a.active').attr('rel') == 'statistics') {
        $('#generic_search').hide();
    } else {
        setDataSearch();
    }

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            processSearch();
        }
    });

    /*var last_week_period = $('#last_week_period_hidden').val();
    last_week_period = last_week_period.split(' - ');
    $('#last_week_period').val($('#last_week_period_hidden').val());
    $("#input1").val(last_week_period[0]);
    $("#input2").val(last_week_period[1]);*/

    var formatDate = jsVars.systemLanguage == 'es' ? 'dd/mm/yy' : 'mm/dd/yy';
    $.datepicker.setDefaults({
        dateFormat: formatDate,
        numberOfMonths: [1, 2],
        showOn: "button",
        showOtherMonths: true,
        selectOtherMonths: true,
        buttonImage: jsVars.datePickerIcon,
        buttonImageOnly: true,
        maxDate: 0,
        beforeShow: function(input, inst) {
            inst.dpDiv.css({
                marginTop: '40px',
                marginLeft: input.offsetWidth + 'px'
            });
        },
        beforeShowDay: function(date) {
            var date1 = $.datepicker.parseDate(formatDate, $("#input1").val());
            var date2 = $.datepicker.parseDate(formatDate, $("#input2").val());
            return [true, date1 && ((date.getTime() == date1.getTime()) || (date2 && date >= date1 && date <= date2)) ? "dp-highlight" : ""];
        },
        onSelect: function(dateText, inst) {
            $(this).data('datepicker').inline = true;

            var date1 = $.datepicker.parseDate(formatDate, $('#input1').val());
            var date2 = $.datepicker.parseDate(formatDate, $('#input2').val());
            var selectedDate = $.datepicker.parseDate(formatDate, dateText);

            if (!date1 || date2) {//Si ambos vacios o ambos llenos
                $("#input1").val(dateText);
                $("#input2").val("");
            } else if (selectedDate < date1) {
                $("#input2").val($("#input1").val());
                $("#input1").val(dateText);
            } else {
                $("#input2").val(dateText);
            }

            var new_period = $("#input1").val() + ' - ' + $("#input2").val();
            $('#period_selected').val(new_period);

            if ($("#input1").val() != '' && $("#input2").val() != '') {
                $(this).datepicker("hide");
            }
        },
        onClose: function(dateText, inst) {
            $(this).data('datepicker').inline = false;
            var period_select = $('#period_selected').val();
            var last_week_period = $('#last_week_period').val();
            period_select = period_select.split(' - ');
            last_week_period = last_week_period.split(' - ');
            var search = true;
            if (period_select[1] == '') {
                search = false;
                $("#period_selected").val($('#last_week_period').val());
                $("#input1").val(last_week_period[0]);
                $("#input2").val(last_week_period[1]);
            }

            if(search) { processSearch(); }
        }
    }, $.datepicker.regional[ "" ]);

    $("#datepicker_stats").datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    $("#period_selected").val($('#last_week_period').val());

    $("#period_selected").click(function(e) {
        e.stopPropagation();
        $('#datepicker_stats').datepicker("show");
        return false;
    });

    //$('.ui-datepicker-trigger').attr('title', $('#title_btn_picker').val());

    $('.action-list li').tipsy({gravity: 's', fade: true});
    $('.row-data > div').tipsy({gravity: 's', fade: true});

    $('.market-option').on('click',function(e){
        e.stopPropagation();
        var $optionSelected = $(this);
        var marketName = $optionSelected.html();
        var market_id = $optionSelected.data('mktid');
        var $selectedOption = $('#market_list .selected-market > span');
        if($selectedOption.attr("rel")!=market_id){
            $selectedOption.html(marketName);
            $selectedOption.attr("rel", market_id);

            $('.tab-permissions-market').addClass('hidden');
            $('.tab-permissions-market[data-mktid="'+market_id+'"]').removeClass('hidden');
            //$('.tab-permissions-market[data-mktid="'+market_id+'"] > h1 > span').html(marketName);
        }

        return false;
    });

    $('#conti_menu a').click(function(e) {
        e.stopPropagation();
        $('#conti_menu a').removeClass('active');
        $(this).attr('class', 'active');
        var target = $(this).attr('rel');
        if(target == 'championship' || target == 'permissions'){
            $('#toolbar > ul > li').addClass('full-hidden');
            if(target == 'championship'){
                $('#championship > div').show();
            }else{
                $('#toolbar ul > li.market-list').removeClass('full-hidden');
            }
        }else{
            $('#toolbar > ul > li').removeClass('full-hidden');
            $('#toolbar ul > li.market-list').addClass('full-hidden');

            if (target == 'statistics') {
                $('#generic_search').hide();
            } else {
                $('#generic_search').show();
                setDataSearch();
            }
        }

        $('#' + target).siblings().hide();
        $('#' + target).show().removeClass('hidden');
        return false;
    });

    $('.refresh-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $('body').cocoblock();
        $('#table_salesperson_activity').load( url, { oper: 'refresh' }, function() {
            $('body').cocoblock('unblock');
        });
        return false;
    });

    $('.save-role-link').click(function(e) {
        e.stopPropagation();
        var $btn = $(this);
        var $wrapperRow = $(this).closest('.permissions-row');
        var $permissionsTableWrapper = $wrapperRow.next();
        var $permissionsTable = $permissionsTableWrapper.find('table');
        var url = $btn.attr('rel');

        var rolePermissions = { group: $wrapperRow.data('group'), permissions: []};
        $permissionsTable.find('tbody tr').each(function () {
           var $row = $(this);
           var sectionId = $row.data('sectionid');
           var section = $row.data('section');
           var version = $row.data('version');
           var market = $row.data('market');
           var permissionString = "";
           $row.find('td:not(:first-child)').each(function(){
               var $cell = $(this);
               var $checkbox = $cell.find('input');
               permissionString = $checkbox.is(':checked') ? permissionString + '1' : permissionString + '0';
           });

            var permissionObject = {id: sectionId,  section: section, version: version, market: market, permission: permissionString}
            rolePermissions.permissions.push(permissionObject);
        });


        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: url,
            data: rolePermissions,
            success: function(response) {
                $('body').cocoblock('unblock');
                response = $.parseJSON(response);
                $.coconotif.add({
                    text: response.msg,
                    time: jsVars.timeNotifications
                });

                if (response.success) {
                    //hide actions
                    $btn.parent().addClass('hidden');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });


        return false;
    });



    $('.permissions-table input[type="checkbox"]').click(function(e){
        var $wrapper = $(this).closest('.permissions-table');
        var $barActions = $wrapper.prev();
        $barActions.find('ul.action-list').removeClass('hidden');
    })


    $('.profile-company-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'company-dialog', modal: true, callbackOpen: 'initEventsCompanyProfile', callbackClose: 'callUpdateDataTable'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $('.lock-unlock-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'suspend-dialog', callbackOk: 'processLockUser', paramsOk: {petition: url, invoker: $(this)}});
    });
}