/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file view.js
 *     Events for conveyor's dashboard
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
var areas="";
var failures = "";
$(document).ready(function() {
    $('.title-page').tipsy({gravity: 's', fade: true});

    $("#generic_search input").val('');
    //searchConveyorsData();
    $('#conveyors_data').scrolling({
                onScroll: function(regs, offset){
                  searchConveyorsData(regs,offset);                  
                },
		nop     : jsVars.rowsToShow,
		offset  : 0,
		error   : 'No More Posts!',
		delay   : 500,
		scroll  : false,
                labelMore: jsVars.textMoreRegs
	});
    
    $('#generic_search input').autocomplete({
        delay: 0,
        source: jsVars.autocompleteConveyors,
        select: function(event, ui) {
            $('#generic_search input').val(ui.item.label);
            search_processed = true;
            searchConveyorsData();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!='') {
            search_processed = false;
            $('#generic_search input').val('');
            searchConveyorsData(jsVars.rowsToShow, 0);
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
           searchConveyorsData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchConveyorsData(jsVars.rowsToShow,0);
        return false;
    });

    $('.add-colaborator-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'user-dialog', modal: false, callbackOpen: 'callOpenUser', callbackClose: 'none', paramsOpen: {user_type: type_user_company[0], company_selected: type_user_company[1], dist_company: type_user_company[2]}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $("#filter_conveyors").multipleSelect({
        width: 150,
        selectAll: false,
        onClick: function(view) {
            //var selecteds = $("#filter_conveyors").multipleSelect('getSelects');
            areas = $("#filter_conveyors").multipleSelect('getSelects');
            if($.trim(areas)==""){
                $('#removeSelected').addClass('hidden');
            }else{
                $('#removeSelected').removeClass('hidden');
            }
            areas = String(areas);
            toggleConveyors();
        },
        onOptgroupClick: function(view) {
            areas = $("#filter_conveyors").multipleSelect('getSelects');
            if($.trim(areas)==""){
                $('#removeSelected').addClass('hidden');
            }else{
                $('#removeSelected').removeClass('hidden');
            }
            areas = String(areas);
            toggleConveyors();
        }
    });
    $("#filter_conveyors").parent().append('<a kref="#" id="removeSelected" class="hidden" title="Clear">x</a>');
    $("#removeSelected").click(function(e){
        e.stopPropagation();
        $('#filter_conveyors').multipleSelect('uncheckAll');
        $('#removeSelected').addClass('hidden');
        areas = "";
        toggleConveyors();
        return false;
    });

    $("#filter_failures").multipleSelect({
        width: 100,
        selectAll: false,
        onClick: function(view) {
            failures = $("#filter_failures").multipleSelect('getSelects');
            if($.trim(failures)==""){
                $('#removeSelectedFailure').addClass('hidden');
            }else{
                $('#removeSelectedFailure').removeClass('hidden');
            }
            failures = String(failures);
            toggleConveyors();
        },
        onOptgroupClick: function(view) {
            failures = $("#filter_failures").multipleSelect('getSelects');
            if($.trim(failures)==""){
                $('#removeSelectedFailure').addClass('hidden');
            }else{
                $('#removeSelectedFailure').removeClass('hidden');
            }
            failures = String(failures);
            toggleConveyors();
        }
    });
    $("#filter_failures").parent().append('<a kref="#" id="removeSelectedFailure" class="hidden" title="Clear">x</a>');
    $("#removeSelectedFailure").click(function(e){
        e.stopPropagation();
        $('#filter_failures').multipleSelect('uncheckAll');
        $('#removeSelectedFailure').addClass('hidden');
        failures = "";
        toggleConveyors();
        return false;
    });

});

function toggleConveyors(){
    var selecteds = "";
    if($.trim(areas)=="" && $.trim(failures)==""){
        $('li.conveyor-item').show();
    }else{
        $('li.conveyor-item').hide();
        if($.trim(areas)!=""){
            selecteds = areas.split(',');
            $.each(selecteds, function(index, value){
                $('li.conveyor-item.'+value).show();
            })
        }
        if($.trim(failures)!=""){
            selecteds = failures.split(',');
            $.each(selecteds, function(index, value){
                $('li.conveyor-item.'+value).show();
            })
        }

    }
}