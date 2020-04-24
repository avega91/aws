/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file clientes.js
 *     Events for clients view
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

saved_company_client = "";
updated_colaborator = false;
$(document).ready(function() {
    initAutocompleteCategory();
    initClientsDatatableEvents();
    
    $("#generic_search input").val('');
    /*
    $('#generic_search input').catcomplete({
        delay: 0,
        source: jsVars.autocompleteCompanies,
        select: function( event, ui ) {
            $('#generic_search input').attr('rel',ui.item.categoryid);
            $('#generic_search input').val(ui.item.label);
            search_processed = true;
            searchClientsData();
        }
    });
    */
    $('#generic_search input').autocomplete({
        delay: 0,
        source: jsVars.autocompleteCompanies,
        select: function(event, ui) {
            $('#generic_search input').val(ui.item.label);
            search_processed = true;
            searchClientsData();
        }
    });

    $('#generic_search a.close').click(function(e) {
        var input_search = $('#generic_search input');
        if(search_processed && $.trim(input_search.val())!='') {
            search_processed = false;
            $('#generic_search input').val('');
            searchClientsData();
        }
    });
    
    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchClientsData();
        }
    });
    
    $('#generic_sort ul a').click(function(e){
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel',$(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchClientsData();
        return false;
    });
    
});

function searchClientsData() {
    //if data was saved, just update automplete, not search saved item
    if(saved_company_client){
        jsVars.autocompleteCompanies.push(saved_company_client);
        jsVars.autocompleteCompanies.sort();
        saved_company_client = ""; //not search recent data, just update autocomplete
        $('#generic_search input').autocomplete( "option", "source", jsVars.autocompleteCompanies);
    }

    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();
    
    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());
    
    $('body').cocoblock();
    $("#content").load(jsVars.clientsDataReload + '/' + sort + '/' +urlencode(search) , function(data) {
        $('body').cocoblock('unblock');
        initClientsDatatableEvents();
    });
}
function initClientsDatatableEvents() {    
    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
    $('.item-dashboard p').tipsy({gravity: 's', fade: true});
    
    $('.add-client-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'user-dialog', modal: false, callbackOpen: 'callOpenUser', callbackClose: 'callUpdateClientsDataTable', paramsOpen: {user_type: type_user_company[0]}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });
    
    
    $('.edit-company-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'company-dialog', modal: true, callbackOpen: 'initEventsCompanyProfile',callbackClose: 'callUpdateClientsDataTable'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });
    
//    $('.item-dashboard-link').click(function(e){
//        e.stopPropagation();
//        var linkCompany = $(this).attr('rel');
//        $('body').cocoblock();
//        window.location = linkCompany;
//        return false;
//    });
    
    /*
    

    $('#conti_menu a').click(function(e) {
        e.stopPropagation();
        $('#conti_menu a').removeClass('active');
        $(this).attr('class', 'active');
        var target = $(this).attr('rel');
        $('#' + target).siblings().hide();
        $('#' + target).show().removeClass('hidden');
        
        $('#generic_search input').attr('rel',target);
        
        return false;
    });

    $('.profile-company-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'company-dialog', modal: true, callbackOpen: 'initEventsCompanyProfile',callbackClose: 'callUpdateDataTable'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $('.suspend-user-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'suspend-dialog', callbackOk: 'processSuspendUser', paramsOk: {petition: url, invoker: $(this)}});
    });

    $('.delete-user-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'delete-dialog', callbackOk: 'processDeleteUser', paramsOk: {petition: url, invoker: $(this)}});
    });

    $('.change-accessdata-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        getAccessData(url);
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
                $.conti.dialog({html: response, style: 'user-dialog', modal: false, callbackOpen: 'callOpenUser', callbackClose: 'callUpdateDataTable', paramsOpen: {user_type: type_user_company[0], company_selected: type_user_company[1], dist_company: type_user_company[2]}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });
*/
}

function callUpdateClientsDataTable(){
 $('#tutorial_toggle').removeClass('fulltop');   
 restoreInitialTutorial();
 if(updated_colaborator || saved_company){
    searchClientsData();
    updated_colaborator = false;
    saved_company = false;
 }
 
}

//function initEventsCompanyProfile() {
//    var original_hash = $('#company_profile_form').formhash();
//    $('#profile-company').tipsy({gravity: 's', fade: true});
//
//    $('#company_profile_form').keyup(function(e) {
//        if ($('#company_profile_form').formhash() != original_hash) {
//            $('#save_company_profile').loadingButton('enabled');//Habilitamos el boton de guardar
//        } else {
//            $('#save_company_profile').loadingButton('disabled');//Deshabilitamos el boton de guardar
//        }
//    });
//
//    $('#profile-company').contiUploader({
//        icon: jsVars.site + $('#path_logo_empresa').val(),
//        uploadUrl: jsVars.uploadGenericImgAx,
//        width: 175,
//        height: 175,
//        sizeImg: {w: 175, h: 175},
//        pathUpload: 'uploads/tmpfiles/',
//        onProcess: function(obj) {
//            obj.cocoblock({fixPosition: true});
//        },
//        onCompleteUpload: function(response, obj) {
//            obj.cocoblock('unblock');
//            obj.tipsy("hide");
//
//            $('#temp-dialog').cococropper({
//                previewImg: response.link,
//                sourceImg: response.relative_path,
//                previewDataImg: response.image.data,
//                classPreviewImg: 'preview-profile-user',
//                submitLabel: 'Guardar',
//                submitClass: 'contiButton',
//                cropUrl: jsVars.getCroppedImgAx,
//                cropSize: {w: 175, h: 175},
//                onSaveCrop: function(btn) {
//                    var container_form = $('#temp-dialog').parents('.ui-dialog');
//                    container_form.cocoblock();
//                },
//                onCompleteCrop: function(cropdata) {
//                    var container_form = $('#temp-dialog').parents('.ui-dialog');
//                    $('#logo_company_hidden').val(cropdata.relative_path);
//                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
//                    container_form.cocoblock('unblock');
//                    $('#temp-dialog').dialog('close');
//                    $('#save_company_profile').loadingButton('enabled');//Habilitamos el boton de guardar
//                }
//            });
//
//            $.conti.dialog({
//                wrapper: '#temp-dialog',
//                style: 'profile-dialog',
//                modal: true,
//                width: 600
//            });
//        }
//    });
//
//    $("#company_profile_form").validationEngine({
//        validationEventTrigger: '',
//        scroll: false,
//        promptPosition: "centerRight",
//        autoHidePrompt: true,
//        autoHideDelay: jsVars.timeErrorValidation
//    });
//
//    $('#save_company_profile').loadingButton({
//        disabled: true,
//        class_disabled: 'disabled-btn',
//        onClick: function(e, btn) {
//            e.stopPropagation();
//            if ($("#company_profile_form").validationEngine('validate')) {
//                $.ajax({
//                    type: 'post',
//                    url: $("#company_profile_form").attr('action'),
//                    data: {
//                        formdata: $("#company_profile_form").serialize()
//                    },
//                    success: function(response) {
//                        response = $.parseJSON(response);
//                        $.coconotif.add({
//                            text: response.msg,
//                            time: jsVars.timeNotifications
//                        });
//
//                        if (response.success) {
//                            btn.loadingButton('stop', 1);
//                            saved_company = true;
//                        } else {
//                            btn.loadingButton('stop', -1);
//                        }
//                    },
//                    error: function(xhr, ajaxOptions, thrownError) {
//                        btn.loadingButton('stop', -1);
//                        errorAjax(xhr, ajaxOptions, thrownError);
//                    }
//                });
//
//            } else {
//                btn.loadingButton('stop', -1);
//            }
//            return false;
//        }
//    });
//}
