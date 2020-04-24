/**
 * Created by humannair on 3/8/18.
 */

$(document).ready(function() {

    initClientItemsEvents();

    $("#generic_search input").val('');

    $('#generic_search input').autocomplete({
        delay: 0,
        source: [],
        select: function(event, ui) {
            $('#generic_search input').val(ui.item.label);
            search_processed = true;
            searchClientItems();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!='') {
            search_processed = false;
            $('#generic_search input').val('');
            searchClientItems();
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchClientItems();
        }
    });

    $('#generic_sort ul a').click(function(e){
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel',$(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchClientItems();
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
                    $.conti.dialog({html: response, style: dialogStyle, modal: true, callbackOpen: 'initEventsAddItemClient', callbackClose: 'updateClientItemsIfRequired'});
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

});


function searchClientItems() {
    //if data was saved, just update automplete, not search saved item
    /*if(saved_company_client){
        jsVars.autocompleteCompanies.push(saved_company_client);
        jsVars.autocompleteCompanies.sort();
        saved_company_client = ""; //not search recent data, just update autocomplete
        $('#generic_search input').autocomplete( "option", "source", jsVars.autocompleteCompanies);
    }*/

    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    $('body').cocoblock();
    $("#content").load(jsVars.clientItemsReload + '/' + sort + '/' +urlencode(search) , function(data) {
        $('body').cocoblock('unblock');
        initClientItemsEvents();
    });
}

function initClientItemsEvents(){
    $('.title-page').tipsy({gravity: 's', fade: true});

    $('a.preview-item-link').click(function(e) {
        e.stopPropagation();
        return false;
    });

    if ($().lightbox) {
        $('a.preview-item-link').lightbox();
    }

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
}

function updateClientItemsIfRequired() {
    if (saved_item) {
        //$('body').cocoblock();
        //location.reload();
        searchClientItems();
    }
}

function initEventsAddItemClient() {
    $('#folder_title').parents('form').css('height', '100px');
    $(":input[placeholder]").placeholder();
    $('#folder_title').keypress(function(event){
        if (event.which == '13') {
            event.preventDefault();
        }
    });

    if ($('#wysiwyg_description').size() > 0) {
        var height = $('#note_title').size() > 0 ? '410px' : '430px';
        $('#wysiwyg_description').parents('form').css('height', height);

        $('#wysiwyg_description').parents('form').css('width', '600px');
        new nicEditor({uploadURI: jsVars.uploadNicEditReportAx, iconsPath: jsVars.site + 'img/wysiwyg.png', minHeight: 220, maxHeight: 220, buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'upload', 'link', 'unlink']}).panelInstance('wysiwyg_description');
        fix_style_wysiwyg();
    }

    $("#dialog_wrap").dialog("option", "position", {my: "center", at: "top + 300", of: window});

    $("#add_item_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    if($('#item_taken_at').size()>0){
        $.datepicker.setDefaults({
            //changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            selectOtherMonths: true
        }, $.datepicker.regional[ "" ]);
        $('#item_taken_at').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
        //$("#item_taken_at").datepicker("setDate", new Date());
    }


    $('#image_upload_item').tipsy({gravity: 's', fade: true, offset: -30});
    $('#image_upload_item').contiUploader({
        icon: jsVars.site + 'css/images/uploader_covers/img_cover_off.png',
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 220,
        height: 220,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onError: function(errorStr, obj) {
            obj.cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-conveyor-image',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 800, h: 600},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    obj.addClass('process-completed');
                    $('#path_item').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                    $('#save_item_conveyor').removeClass('disabled-btn');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'conveyor-dialog',
                modal: true,
                width: 920
            });
        }
    });


    $('#video_upload_item').tipsy({gravity: 's', fade: true, offset: -30});
    $('#video_upload_item').contiUploader({
        icon: jsVars.site + 'css/images/uploader_covers/img_video_off.png',
        uploadUrl: jsVars.uploadGenericVideoAx,
        width: 220,
        height: 220,
        type: 'video',
        maxSizeUpload: jsVars.contiUploader.maxSizeVideoUpload,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress, percentComplete) {
            if(percentComplete>95){
                percentComplete = percentComplete-1;
                progress = percentComplete+"%";
            }

            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onError: function(errorStr, obj) {
            obj.cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            setTimeout(function() {
                obj.find('.cocoblocker .blockMsg').html("100%");
            }, 1500);
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#path_item').val(response.relative_path);
            //obj.css({backgroundImage: 'url(' + response.thumbvideo + ')'});
            obj.addClass('process-completed');
            $('#save_item_conveyor').removeClass('disabled-btn');
        }
    });

    $('#report_upload_item').tipsy({gravity: 's', fade: true});
    $('#report_upload_item').contiUploader({
        icon: jsVars.site + 'css/images/uploader_covers/button_upload_off.png',
        uploadUrl: jsVars.uploadGenericFileAx,
        width: 120,
        height: 35,
        type: 'pdf',
        maxSizeUpload: jsVars.contiUploader.maxSizeVideoUpload,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onError: function(errorStr, obj) {
            obj.cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");
            $('#path_item').val(response.relative_path);
            $('#name_report_file').html(response.name_file);
        }
    });

    $('#is_pdf_file').on('click', function() {
        if ($(this).is(':checked')) {
            $('#manual_report').hide();
            $('#wysiwyg_description').parents('form').animate({
                //width: '400px',
                height: '230px'
            }, function() {

            });
            /*$('#wysiwyg_description').parents('form').css('height', '230px');
             $('#wysiwyg_description').parents('form').css('width', '400px');            */
            $('#file_report').show().removeClass('hidden');
        } else {
            $('#file_report').hide();
            //$('#wysiwyg_description').parents('form').css('height', '430px');
            //$('#wysiwyg_description').parents('form').css('width', '600px');
            $('#wysiwyg_description').parents('form').animate({
                //width: '600px',
                height: '430px'
            }, function() {

            });
            $('#manual_report').show();
        }
    });

    $('#file_upload_item').contiUploader({
        icon: null,
        class: 'genericBtn',
        text: $('#file_upload_item').attr('title'),
        uploadUrl: jsVars.uploadGenericFileAx,
        width: 170,
        height: 35,
        type: '*',
        maxSizeUpload: jsVars.contiUploader.maxSizeVideoUpload,
        pathUpload: 'uploads/tmpfiles/',
        onCreate: function(obj){
            obj.removeAttr('title');
        },
        onUploadProgress: function(obj, progress) {
            //obj.find('.cocoblocker .blockMsg').html(progress);
            var parent = obj.closest('.ui-dialog');
            parent.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            //obj.cocoblock({message: '0%'});
            obj.closest('.ui-dialog').cocoblock({message: '0%'})
        },
        onError: function(errorStr, obj) {
            //obj.cocoblock('unblock');
            obj.closest('.ui-dialog').cocoblock('unblock');
        },
        onCompleteUpload: function(response, obj) {
            //obj.cocoblock('unblock');

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
            displayError(response.reason_fail+'.<br> '+jsVars.jqueryUploader.customTypeFiles);
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
                                $('#image_upload_item,#video_upload_item,#report_upload_item').tipsy("hide");
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


    //$('#save_item_conveyor:not(.disabled-btn)').click(function(e){
    //$(document).on('click','#save_item_conveyor:not(.disabled-btn)',function(e){
    $('#save_item_conveyor').click(function(e){
        e.stopPropagation();
        if($(this).hasClass('disabled-btn')){
            return false;
        }else{
            var l = Ladda.create(this);
            l.start();

            var editorError = null;
            if ($('#wysiwyg_description').size() > 0 && $('#manual_report').is(':visible')) {
                var editorText = nicEditors.findEditor('wysiwyg_description').getContent();
                $('#item_description').val(editorText);
                editorText = $.trim(editorText.replace(/&nbsp;/g, ' '));
                if (is_empty_editor(editorText)) {
                    editorError = $('#add_item_conveyor_form').find('.ps-container');
                }
            }

            $('#path_item').val($('#path_item').val() == '' ? 'required-field' : $('#path_item').val());
            $('#item_description').val($('#item_description').val() == '' ? 'required-field' : $('#item_description').val());

            if (editorError !== null) {
                editorError.attr('id', 'tmp' + $.now());
                editorError.validationEngine('showPrompt', jsVars.fieldRequiredMsg, 'error', "centerRigth", true);
                l.stop();
            }

            if ($("#add_item_conveyor_form").validationEngine('validate') && editorError === null) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_item_conveyor_form").attr('action'),
                    data: {formdata: $("#add_item_conveyor_form").serialize()},
                    success: function(response) {

                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                l.stop();
                                saved_item = true;
                                $('#image_upload_item,#video_upload_item,#report_upload_item').tipsy("hide");
                                $('#dialog_wrap').dialog('close');
                            } else {
                                l.stop();
                            }
                        } catch (e) {
                            l.stop();
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        l.stop();
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });
            } else {
                l.stop();
            }
        }

        return false;
    });

}