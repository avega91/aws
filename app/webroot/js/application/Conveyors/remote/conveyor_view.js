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
var loaded_filters = false;
$(document).ready(function() {

    $("#generic_search input").val('');
    setTimeout(function() {
        searchItemsConveyorsData();
    }, 1000);

    
    $('#generic_search a.close').click(function(e) {
        $('#generic_search input').val('');
        searchItemsConveyorsData();
    });
    
    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            searchItemsConveyorsData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchItemsConveyorsData();
        return false;
    });
    
     $("#type_filter_chosen").chosen({
        disable_search: true,
        width: "100%"
    });
    
    $("#type_filter_chosen").on('change', function(evt, params) {
        var filter = $(this).val();
        $('#items_conveyors_wrapper ul li.item-dashboard:not(.details-item-dashboard)').hide();
        var item_selector = '';
        if(filter !== null){
            $.each(filter, function(index, item){
                item_selector += '.'+item+'-item,';            
            });        
        }
        item_selector = item_selector=='' ? '.item-dashboard' : item_selector.slice(0,-1);
        $(item_selector).show();
        
    });
    
    //initItemsConveyorDatatableEvents();

    $(document).on('click', '.revert-drop-item-link', function(e) {
        e.stopPropagation();
        var item = $(this).attr('rel');

        $('.item-dashboard[item-info="' + item + '"]').show();
        $.ajax({
            type: 'post',
            url: $(this).attr('href'),
            data: {conveyor: jsVars.secureConveyor},
            success: function(response) {
                try {
                    response = $.parseJSON(response);
                    $.coconotif.add({
                        text: response.msg,
                        time: jsVars.timeDragNotifications,
                        position: 'center'
                    });
                    if (!response.success) {
                        $('.item-dashboard[rel="' + item + '"]').hide();
                    }
                } catch (e) {
                    $.conti.alert({msg: response.msg, type: 'error-dialog'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });

        return false;
    });

    $(document).on('click','.add-mediaitem-conveyor-link',function(e) {

        e.stopPropagation();

        var pet = $(this).attr('rel');
        var typeItem = $(this).attr('alt');
        var dialogStyle = $(this).attr('dialog-style');
        saved_item = false;
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
                    //$.conti.dialog({html: response, style: dialogStyle, modal: true, callbackOpen: 'initEventsAddItemConveyor', callbackClose: 'searchItemsConveyorsData'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });

        return false;
    });

    loadFilterAreasSubAreas();
});

function loadFilterAreasSubAreas(){
    if(!loaded_filters){
        //load areas and subareas using ajax
        $('#filter_areas').load( jsVars.filterAreasData, function() {

            //INIT EVENTS FOR TAGS OF CONVEYORS

            loaded_filters = true;

            $('.clear-tag').click(function(e){
                e.stopPropagation();
                var $btn = $(this);
                var filterSelect = $btn.data('type');
                $btn.addClass('processing');
                $.ajax({
                    type: 'post',
                    url: $btn.data('url'),
                    data: {invoker: filterSelect},
                    success: function(response) {
                        response = $.parseJSON(response);
                        $btn.removeClass('processing').addClass('hidden');
                        if(response.success){
                            $('#'+filterSelect).val('').trigger("chosen:updated");
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
                return false;
            });

            $("#area_select, #subarea_select").chosen({
                disable_search: true,
                width: "100%"
            });

            var selected_area = $("#area_select option:selected").val();
            var selected_subarea = $("#subarea_select option:selected").val();

            $("#area_select, #subarea_select").on('change', function(evt, params) {
                var filter = $(this).val();
                var invoker = $(this).attr('id');
                if(filter=='new'){
                    $("#area_select").val(selected_area).trigger("chosen:updated");
                    $("#subarea_select").val(selected_subarea).trigger("chosen:updated");

                    $.conti.dialog({html: $('#append_area_subarea_wrapper').html(), style: 'edit-dialog', modal: true, callbackOpen: 'initEventsAddAreaSubarea', callbackClose:'loadFilterAreasSubAreas', paramsOpen: {invoker: invoker}});
                }else{//save area or subarea for current conveyor

                    $.ajax({
                        type: 'post',
                        url: jsVars.setAreaSubAreaUrl,
                        data: {filterId: filter, invoker: invoker},
                        success: function(response) {
                            response = $.parseJSON(response);
                            if(response.success){
                                if(invoker=="area_select"){
                                    selected_area = filter;
                                }else{
                                    selected_subarea = filter
                                }
                                //show close btn to remove association
                                $('#'+invoker+'_chosen').next().removeClass('hidden');
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            errorAjax(xhr, ajaxOptions, thrownError);
                        }
                    });
                }
            });
        });
    }

}

function initEventsAddAreaSubarea(invoker){
    $("#dialog_wrap #area_subarea_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });


    $('#dialog_wrap #save_area_subarea').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#dialog_wrap #area_subarea_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#dialog_wrap #area_subarea_form").attr('action'),
                    data: {invoker: invoker, formdata: $("#dialog_wrap #area_subarea_form").serialize()},
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            loaded_filters = false;
                            btn.loadingButton('stop', 1);
                            setTimeout(function() {
                                $('#dialog_wrap').dialog('close');
                            }, 1000);
                        } else {
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

function searchItemsConveyorsData() {
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    $('body').cocoblock();

    /*
    $("#items_conveyors_wrapper").load(jsVars.refreshItemsConveyorAx + '/' + sort + '/' + urlencode(search), function(data) {
        $('body').cocoblock('unblock');
        initItemsConveyorDatatableEvents();
        if($.trim($("#items_conveyors_wrapper").html())==""){
            window.location.reload(true);
        }
    });*/
    $.ajax({
        type: 'get',
        url: jsVars.refreshItemsConveyorAx + '/' + sort + '/' + urlencode(search),
        success: function(response) {
            $('body').cocoblock('unblock');
            $("#items_conveyors_wrapper").html(response);
            initEventsConveyor();
            initItemsConveyorDatatableEvents();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            window.location.reload(true);
        }
    });
}

function initEventsConveyor(){

    $('.title-page').tipsy({gravity: 's', fade: true});
    
    $('#conveyor_details_link, #conveyor_activity').click(function(e) {
        e.stopPropagation();
        $(this).next().slideToggle("slow");
        return false;
    });
    
    $('.conveyor-opt-link:not(.disabled-btn)').click(function() {
        var pet = $(this).attr('rel');
        var callback = $(this).attr('assoc-callback');
        var layer = $(this).attr('assoc-layer');
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
                    $.conti.dialog({html: response, style: style, modal: true, callbackOpen: callback, paramsOpen:{layer: layer}});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    });
    

}

function loadLogConveyors() {
    var container = $("#activity_feed_conveyors");
    //container.cocoblock();
    $('#activity_feed_conveyors').perfectScrollbar('destroy');
    container.load(jsVars.refreshLogConveyorAx + '/' + $("#activity_feed_conveyors").attr('rel'), function(data) {
      //  $('body').cocoblock('unblock');
        $('#activity_feed_conveyors').perfectScrollbar({suppressScrollX: true});
    });
}

function initItemsConveyorDatatableEvents() {


    var gaugeCalculationUrl = "";
    var conveyorIds = [];
    $('.item-dashboard.with-reading:not(.calculated-gauge)').each(function(){
        var $conveyorItem = $(this);
        var $conveyorId = $(this).data('conveyor');
        var $gaugeContainer = $conveyorItem.find('> div');
        conveyorIds.push($conveyorId);
        gaugeCalculationUrl = gaugeCalculationUrl == "" ? $conveyorItem.data('gauge') : gaugeCalculationUrl;
    });

    if(!$.isEmptyObject(conveyorIds) && gaugeCalculationUrl!=""){
        var jqXHR = $.ajaxq ("gaugesQueue", {
            url: gaugeCalculationUrl,
            type: 'post',
            dataType: 'json',
            data: {
                gaugeConveyors: conveyorIds
            },
            error: function() {
                //$conveyorItem.addClass('calculated-gauge with-error-gauge');
            },
            complete: function() {
                //clearInterval(interval);
                //$(node).addClass("complete").find("b").html("&#x2713");
            },
            success: function(gauges) {
                $('body').cocoblock("unblock");
                if(gauges.success){
                    $.each(gauges.charts, function(conveyor_id, gauge_html){
                        var $conveyorItem = $('li[data-conveyor="'+conveyor_id+'"]');
                        var $gaugeContainer = $conveyorItem.find('> div');
                        $conveyorItem.addClass('calculated-gauge');
                        $conveyorItem.find('div.cover-item').hide().remove();
                        var uniqid = $.now();
                        $conveyorItem.addClass('conveyor-gauge-item');
                        $gaugeContainer.prepend('<div id="'+uniqid+'"></div>');
                        $('#'+uniqid).html(gauge_html);
                        var div_gauge = $('#'+uniqid).find('div#gauge_div');
                        div_gauge.attr('id', div_gauge.attr('id')+uniqid);
                        drawGaugeChart(uniqid);
                    })
                }
            }
        });
    }


    $('#activity_feed_conveyors').perfectScrollbar({suppressScrollX: true});
    loadLogConveyors();
    
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
    
    $('.add-item-dashboard-circular a').click(function(e){
        $('.add-item-dashboard-circular button').trigger('click');
    });
    
    updateSizePanelNotifications();
    
    $('a.preview-item-link').click(function(e){
        e.stopPropagation();
        return false;
    });
    
    if($().lightbox){
        $('a.preview-item-link').lightbox(); 
    }
    
    $('.dashboard-list > li:not(.details-item-dashboard):not(.folder-year-item):not(.ultrasonic-item-dashboard):not(.item-in-folder) > div').draggable({
        revert: "invalid",
        helper: "clone",
        opacity: 0.75,
        zIndex: 100,
        cursor: 'crosshair',
        drag: function(event, ui) {
            var item = ui.helper.parent();
            ui.helper.addClass(item.attr('class'));
        },
        start: function(event, ui) {
            var item = ui.helper.parent();
            item.addClass('dragging');
        },
        stop: function(event, ui) {
            var item = ui.helper.parent();
            setTimeout(function() {
                item.removeClass('dragging');
            }, 500);
        }
    });

    $('.dashboard-list > li.folder-year-item').droppable({
        activeClass: "ready-to-drop",
        //accept: 'li.folder-item > div',
        drop: function(event, ui) {
            var item = ui.helper.parent();
            $(this).removeClass('ready-to-drop');
            $(this).animate({
                backgroundColor: '#FFA500'
            }, 1000, function() {
                $(this).css('background-color', '#FFF');

                var folder_drop = $(this).attr('item-info');
                var item_dropped = item.attr('item-info');
                item.hide();

                $.ajax({
                    type: 'post',
                    url: jsVars.dropItemToFolderAx,
                    data: {folder: folder_drop, dropped_item: item_dropped},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeDragNotifications,
                                position: 'center'
                            });
                            if (!response.success) {
                                item.show();
                            }
                        } catch (e) {
                            $.conti.alert({msg: response.msg, type: 'error-dialog'});
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
            });
        }
    });
    
    $('.dashboard-list > li.folder-item').droppable({
        activeClass: "ready-to-drop",
        accept: 'li:not(.folder-item) > div',
        drop: function(event, ui) {
            var item = ui.helper.parent();
            $(this).removeClass('ready-to-drop');
            $(this).animate({
                backgroundColor: '#FFA500'
            }, 1000, function() {
                $(this).css('background-color', '#FFF');

                var folder_drop = $(this).attr('item-info');
                var item_dropped = item.attr('item-info');
                item.hide();

                $.ajax({
                    type: 'post',
                    url: jsVars.dropItemToFolderAx,
                    data: {folder: folder_drop, dropped_item: item_dropped},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeDragNotifications,
                                position: 'center'
                            });
                            if (!response.success) {
                                item.show();
                            }
                        } catch (e) {
                            $.conti.alert({msg: response.msg, type: 'error-dialog'});
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
            });
        }
    });

    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.multiple-add a').each(function() {
        $(this).tipsy({gravity: $(this).attr('location-tool'), fade: true});
    });
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
    
}

function initEventsBeltLifeCalc(layer) {
    
    $('.not-valid-recommended').tipsy({gravity: 's', fade: true});
    $('.tooltip-ctrl').tipsy({gravity: 's', fade: true, html: true, className: 'disclaimer-belt-width'});
    
    if(layer=='recommended-conveyor'){
        $('#life_estimation_wrapper').hide();
        $('#recommended_conveyor_wrapper').show();
    }
    
    $('#belt_recommended').click(function() {
        $('#life_estimation_wrapper').toggle('slide', {direction: 'left'}, 'slow', function() {
            $('#recommended_conveyor_wrapper').toggle('slide', {direction: 'right'}, 'slow');
            $('#recommended_conveyor_wrapper').parents('.ui-dialog-content').removeClass('life-estimation-dialog').addClass('belt-recommended-dialog');
        });
    });

    $('#life_estimation').click(function() {
        $('#recommended_conveyor_wrapper').toggle('slide', {direction: 'right'}, 'slow', function() {
            $('#life_estimation_wrapper').toggle('slide', {direction: 'left'}, 'slow');
            $('#recommended_conveyor_wrapper').parents('.ui-dialog-content').removeClass('belt-recommended-dialog').addClass('life-estimation-dialog');
        });
    });

    var disabled_quote = typeof($('#quote_request').attr('disabled')) !== 'undefined' && $('#quote_request').attr('disabled') == 'disabled' ? true : false;
    $('#quote_request').loadingButton({
        disabled: disabled_quote,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();
            var quoteRequest = btn.attr('rel');
            $.ajax({
                type: 'post',
                url: quoteRequest,
                success: function(response) {
                    $('body').cocoblock('unblock');
                    try {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });
                        if (response.success) {
                            btn.loadingButton('disabled');//Habilitamos el boton de guardar
                            btn.loadingButton('stop', 1);                            
                        }else{
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

function updateItemsIfRequired() {
    if (saved_item) {
        //$('body').cocoblock();
        //location.reload();
        $('#type_filter_chosen').val('').trigger("chosen:updated");
        searchItemsConveyorsData();
    }
}