/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file action.js
 *     Events for premium section
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */
$(document).ready(function() {    
    $('.title-page').tipsy({gravity: 's', fade: true});

    $("#generic_search input").val('');
    //searchConveyorsData();
    $('#custom_rep_data').scrolling({
        onScroll: function(regs, offset) {
            searchCustomReportsData(regs, offset);
        },
        nop: jsVars.rowsToShow,
        offset: 0,
        error: 'No More Posts!',
        delay: 500,
        scroll: false,
        labelMore: jsVars.textMoreRegs
    });

    $('#generic_search input').autocomplete({
        delay: 0,
        source: jsVars.autocompleteItems,
        select: function(event, ui) {
            $('#generic_search input').val(ui.item.label);
            search_processed = true;
            searchCustomReportsData();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!='') {
            search_processed = false;
            $('#generic_search input').val('');
            searchCustomReportsData(jsVars.rowsToShow, 0);
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchCustomReportsData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchCustomReportsData(jsVars.rowsToShow, 0);
        return false;
    });
});
