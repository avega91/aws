/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file conveyor_view.js
 *     actions for conveyor view
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

var saved_item = false;
$(document).ready(function() {

    if (typeof (jsVars.pathVideo) !== 'undefined') {
        var player = null;
            player = videojs('my-player');
    }


    //initItemsConveyorDatatableEvents();

    //Solo si es un folder
    if(typeof(jsVars.refreshItemsFolderAx)!="undefined"){

        $("#generic_search input").val('');

        searchItemsFolderData();

        $('#generic_search input').autocomplete({
            delay: 0,
            source: [],
            select: function(event, ui) {
                $('#generic_search input').val(ui.item.label);
                search_processed = true;
                searchItemsFolderData();
            }
        });

        $('#generic_search a.close').click(function(e) {
            var input_search = $('#generic_search input');
            if(search_processed && $.trim(input_search.val())!='') {
                search_processed = false;
                $('#generic_search input').val('');
                searchItemsFolderData();
            }
        });

        $('#generic_search input').keypress(function(e) {
            if (e.which == 13) {
                search_processed = true;
                searchItemsFolderData();
            }
        });

        $('#generic_sort ul a').click(function(e) {
            e.stopPropagation();
            $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
            $('#generic_sort > a > span').html($(this).html());
            searchItemsFolderData();
            return false;
        });
    }

});

function searchItemsFolderData() {
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    $('body').cocoblock();
    $("#items_folder_wrapper").load(jsVars.refreshItemsFolderAx + '/' + sort+ '/' +urlencode(search) , function(data) {
        $('body').cocoblock('unblock');
        initItemsFolderEvents();
    });
}

function initItemsFolderEvents() {


    $('a.preview-item-link').click(function(e) {
        e.stopPropagation();
        return false;
    });

    if ($().lightbox) {
        $('a.preview-item-link').lightbox();
    }



    nbOptions = $('.add-item-dashboard-circular > ul > li').size()
    $('.add-item-dashboard-circular button').click(function(e) {
        toggleOptions($(this).parent());//el li parent
        $(this).toggleClass('close-button');
        if($(this).hasClass('close-button')){
            $('.add-item-dashboard-circular button.add-button').tipsy('hide');        
            $('.add-item-dashboard-circular button.add-button').attr('old-title', $('.add-item-dashboard-circular button.add-button').attr('original-title'));
            $('.add-item-dashboard-circular button.add-button').removeAttr('original-title');
        }else{
            $('.add-item-dashboard-circular button.add-button').attr('title', $('.add-item-dashboard-circular button.add-button').attr('old-title'));
            $('.add-item-dashboard-circular button.add-button').tipsy({gravity: 's', fade: true});
        }
    });
    $('.add-item-dashboard-circular button.add-button').tipsy({gravity: 's', fade: true});
    $('.add-item-dashboard-circular a').tipsy({gravity: 's', fade: true});
    
    updateSizePanelNotifications();


    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.multiple-add a').each(function() {
        $(this).tipsy({gravity: $(this).attr('location-tool'), fade: true});
    });
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});

    $('.add-mediaitem-conveyor-link').click(function() {
        var pet = $(this).attr('rel');
        var typeItem = $(this).attr('alt');
        var dialogStyle = $(this).attr('dialog-style');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            data: {type_item: typeItem},
            success: function(response) {
                try {
                    //if no parse JSON is HTML
                    response = $.parseJSON(response);
                    //if parse JSON, is error
                    $('body').cocoblock('unblock');
                    $.conti.alert({msg: response.msg, type: 'error-dialog'});
                } catch (e) {
                    $.conti.dialog({html: response, style: dialogStyle, modal: true, callbackOpen: 'initEventsAddItemConveyor', callbackClose: 'updateItemsIfRequired'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    });
}

function updateItemsIfRequired() {
    if (saved_item) {
        //$('body').cocoblock();
        //location.reload();
        $('#type_filter_chosen').val('').trigger("chosen:updated");
        searchItemsFolderData();
    }
}