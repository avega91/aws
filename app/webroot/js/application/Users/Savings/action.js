/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file
 * @description
 *
 * @date 11, 2016
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */

var saved_item = false;
$(function () {
    $('.title-page').tipsy({gravity: 's', fade: true});
    $("#generic_search input").val('');

    $(document).on('click', '.edit-savings-link', function(e) {
        e.stopPropagation();
        var linkPetition = $(this).attr('rel');
        var client = $(this).attr('assoc-c');
        var dealer = $(this).attr('assoc-d');
        var active_tab = $(this).attr('assoc-label');
        var callback_after_update = $(this).attr('assoc-callback');

        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: linkPetition,
            data: {ancla: active_tab},
            success: function(response) {
                try {
                    var result = $.parseJSON(response);  //is error, returned a json
                    $.conti.alert({msg: result.msg, type: 'error-dialog'});
                    $('body').cocoblock('unblock');
                } catch (err) {//Exception error, is not json response, is html response, is a news row
                    //$.conti.dialog({html: response, style: 'conveyor-dialog', modal: true, callbackOpen: 'initEventsEditConveyor', callbackClose: 'updateItemsIfRequired'});
                    $.conti.dialog({html: response, style: 'conveyor-dialog', modal: true, callbackOpen: 'callAddSavings', paramsOpen: {dist_id: dealer, client_id: client, active_tab: active_tab}, callbackClose: callback_after_update});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $(document).on('click','.upload-savings-link',function(e) {

        e.stopPropagation();

        var pet = $(this).attr('rel');
        var dialogStyle = $(this).attr('dialog-style');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            data: {},
            success: function(response) {
                try {
                    //if no parse JSON is HTML
                    response = $.parseJSON(response);
                    //if parse JSON, is error
                    $('body').cocoblock('unblock');
                    $.conti.alert({msg: response.msg, type: 'error-dialog'});
                } catch (e) {
                    $.conti.dialog({html: response, style: dialogStyle, modal: true, callbackOpen: 'initEventsAddFile', callbackClose: 'updateItemsIfRequired'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });

        return false;
    });

    //searchConveyorsData();
    $('#savings_data').scrolling({
        onScroll: function(regs, offset) {
            searchSavingsData(regs, offset);
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
            searchSavingsData();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!='') {
            search_processed = false;
            $('#generic_search input').val('');
            searchSavingsData(jsVars.rowsToShow, 0);
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchSavingsData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchSavingsData(jsVars.rowsToShow, 0);
        return false;
    });
});

function initEventsAddFile() {
    $('#remove_file').tipsy({gravity: 's', fade: true});

    $('#remove_file').click(function(e){
        e.stopPropagation();
        $('#file_upload_item').show();
        $('#save_file_conveyor').loadingButton('disabled');//Habilitamos el boton de guardar
        $('#item_name').parent().addClass('hidden');
        return false;
    });

    $(":input[placeholder]").placeholder();
    $('#file_upload_item').contiUploader({
        icon: null,
        class: 'genericBtn',
        text: $('#file_upload_item').attr('title'),
        uploadUrl: jsVars.uploadGenericFileAx,
        width: 170,
        height: 35,
        type: 'file_savings',
        maxSizeUpload: jsVars.contiUploader.maxSizeVideoUpload,
        pathUpload: 'uploads/tmpfiles/',
        onCreate: function(obj){
            obj.removeAttr('title');
        },
        onUploadProgress: function(obj, progress) {
            var parent = obj.closest('.ui-dialog');
            parent.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.closest('.ui-dialog').cocoblock({message: '0%'})
        },
        onError: function(errorStr, obj) {
            obj.closest('.ui-dialog').cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            obj.closest('.ui-dialog').cocoblock('unblock');
            obj.tipsy("hide");
            $('#path_item').val(response.relative_path);
            $('#name_file').html(response.name_file);
            $('#item_name').val(response.name_file);
            $('#item_name').parent().removeClass('hidden');
            obj.hide();

            obj.prev().hide().remove();
            $('#save_file_conveyor').loadingButton('enabled');//Habilitamos el boton de guardar
        },
        onInvalidUpload:  function(response, obj) {
            obj.closest('.ui-dialog').cocoblock('unblock');
            obj.tipsy("hide");
            $('#dialog_wrap').dialog('close');
            displayError(response.reason_fail+'.<br> Files allowed: image or pdf');
        }
    });

    $('#save_file_conveyor').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();

            $('#path_item').val($('#path_item').val() == '' ? 'required-field' : $('#path_item').val());

            if ($("#add_file_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_file_conveyor_form").attr('action'),
                    data: {formdata: $("#add_file_conveyor_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_item = true;
                                $('#dialog_wrap').dialog('close');
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
            } else {
                btn.loadingButton('stop', -1);
            }
            return false;
        }
    });
}

function updateItemsIfRequired() {
    if (saved_item) {
        searchSavingsData();
    }
}