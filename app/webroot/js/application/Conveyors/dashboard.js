/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file dashboard.js
 *     Events for conveyor's dashboard
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$(document).ready(function() {
    $("#generic_search input").val('');
    //searchConveyorsData();
    $('#buoys_data').scrolling({
                onScroll: function(regs, offset){
                    searchBuoysData(regs,offset);                  
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
            searchBuoysData();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!=''){
            search_processed = false;
            $('#generic_search input').val('');
            searchBuoysData(jsVars.rowsToShow,0);
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchBuoysData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchBuoysData(jsVars.rowsToShow,0);
        return false;
    });

    if ($().multipleSelect) {
        $("#filter_conveyors").multipleSelect({
            width: 220,
            selectAll: false,
            onClick: function (view) {
                var selecteds = $("#filter_conveyors").multipleSelect('getSelects');
                if ($.trim(selecteds) == "") {
                    $('#removeSelected').addClass('hidden');
                } else {
                    $('#removeSelected').removeClass('hidden');
                }
                toggleConveyors(String(selecteds));
            },
            onOptgroupClick: function (view) {
                var selecteds = $("#filter_conveyors").multipleSelect('getSelects');
                if ($.trim(selecteds) == "") {
                    $('#removeSelected').addClass('hidden');
                } else {
                    $('#removeSelected').removeClass('hidden');
                }
                toggleConveyors(String(selecteds));
            }
        });
        $("#filter_conveyors").parent().append('<a kref="#" id="removeSelected" class="hidden" title="Clear">x</a>');
        $("#removeSelected").click(function (e) {
            e.stopPropagation();
            $('#filter_conveyors').multipleSelect('uncheckAll');
            $('#removeSelected').addClass('hidden');
            toggleConveyors("");
            return false;
        });
    }
});

function toggleConveyors(selecteds){
    if($.trim(selecteds)==""){
        $('li.conveyor-item').show();
    }else{
        selecteds = selecteds.split(',');
        $('li.conveyor-item').hide();
        $.each(selecteds, function(index, value){
            $('li.conveyor-item.'+value).show();
        })
    }
}
