/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file report.js
 *     Events for summary report
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2018
 */
$(document).ready(function() {
    $(".gauge-chart").each(function(){
       var gaugeId = $(this).data("id");
        drawGaugeChart(gaugeId, true);
    });

    $('input[name="order-group"]').click(function(e){
        var $input = $(this);
        var selected = $input.attr('id');
        $.cookie("order_selected", selected, {path: '/'});
        $('body').cocoblock();
        window.location.reload(true);
    });
});