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
    
    $('#conti_menu a').click(function(e) {
        e.stopPropagation();
        $('#conti_menu a').removeClass('active');
        $(this).attr('class', 'active');
        var target = $(this).attr('rel');
        $('#' + target).siblings().hide();
        $('#' + target).show().removeClass('hidden');
        
        if(target=='training'){
            $('#toolbar > ul > li:not(#generic_add)').attr('style', 'display: none !important');
        }else{
            $('#toolbar > ul > li:not(#generic_add)').attr('style', 'display: block');
        }

        return false;
    });

    $(document).on('click', '.toggle-show-item-link', function(e) {
        e.stopPropagation();
        var link = $(this);
        link.addClass('processing');
        var linkPetition = link.attr('rel');
        $.ajax({
            type: 'post',
            url: linkPetition,
            success: function(response) {
                link.removeClass('processing');
                try {
                    response = $.parseJSON(response);
                    $.coconotif.add({
                        text: response.msg,
                        time: jsVars.timeNotifications
                    });
                    
                    if(response.success){
                        var currentLinkText = link.attr('original-title');
                        link.attr('original-title', link.attr('alt'));
                        link.attr('alt', currentLinkText);                        
                    }
                  
                } catch (e) {
                    $.coconotif.add({
                        text: e,
                        time: jsVars.timeNotifications
                    });
                }                
            },
            error: function(xhr, ajaxOptions, thrownError) {
                link.removeClass('processing');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });


    $('.title-page').tipsy({gravity: 's', fade: true});

    $("#generic_search input").val('');
    //searchConveyorsData();
    $('#conveyors_data').scrolling({
        onScroll: function(regs, offset) {
            searchTrackingConveyorsData(regs, offset);
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
        source: jsVars.autocompleteConveyors,
        select: function(event, ui) {
        }
    });

    $('#generic_search a.close').click(function(e) {
        $('#generic_search input').val('');
        searchTrackingConveyorsData(jsVars.rowsToShow, 0);
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            searchTrackingConveyorsData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchTrackingConveyorsData(jsVars.rowsToShow, 0);
        return false;
    });
});
