/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file usuarios.js
 *     Events for users view
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

updated_colaborator = false;
$(document).ready(function() {
    initAutocompleteCategory();
    initDatatableEvents();

    $("#generic_search input").val('');
    $('#generic_search input').catcomplete({
        delay: 0,
        source: jsVars.autocompleteCompanies,
        select: function(event, ui) {
            $('#generic_search input').attr('rel', ui.item.categoryid);
        }
    });

    if (typeof (jsVars.queryCompany) !== 'undefined') {
        $('#generic_search input').val(jsVars.queryCompany);
        $('#generic_search input').trigger('focus');
        $('#generic_search input').attr('rel', jsVars.activeTab);
        searchUsersData();
    }

    $('#generic_search a.close').click(function(e) {
        if(search_processed) {
            search_processed = false;
            $('#generic_search input').val('');
            searchUsersData();
        }
    });

    $('#generic_search input').keypress(function(e) {
        if (e.which == 13) {
            search_processed = true;
            searchUsersData();
        }
    });

    $('#generic_sort ul a').click(function(e) {
        e.stopPropagation();
        $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
        $('#generic_sort > a > span').html($(this).html());
        searchUsersData();
        return false;
    });



    $(document).on('click','#admins .clients-company-link',function(e){
        var container = $(this);
        container.find('.distributors-company-link').trigger('click');

        return false;
    });

    $(document).on('click','.distributors-company-link',function(e){
        e.stopPropagation();
        var link = $(this);
        var container = link.closest('.accordionButton');
        var distributor_list = container.next();
        var region = link.data('region');
        var url = link.data('query');


        if(link.hasClass('displayed')){
            $(distributor_list).slideUp('normal');
            $(distributor_list).html('');
            link.removeClass('displayed');
            container.removeClass('active');
        }else{
            container.addClass('active');
            link.addClass('loading');
            $(distributor_list).load( url, { region: region }, function() {
                link.removeClass('loading').addClass('displayed');
                $(distributor_list).slideDown('normal');
            });
        }

        return false;
    });

    $('.rm-admin').tipsy({gravity: 's', fade: true});
    $(document).on('click','.rm-admin',function(e) {
        e.stopPropagation();
        $('.rm-admin.active').not(this).toggleClass('active');
        $(this).toggleClass('active');
        return false;
    });
    $(document).on('click','.rm-admin-option:not(.selected)',function(e) {
        e.stopPropagation();
        var $option = $(this);
        var $parent = $option.closest('li.rm-admin');
        $parent.toggleClass('active');

        //set processing
        $parent.addClass('processing');
        var url = $parent.data('rmchange');
        $.ajax({
            type: 'post',
            url: url,
            data: {
                user_id: $option.data('id')
            },
            success: function(response) {
                $parent.removeClass('processing');
                try {
                    response = $.parseJSON(response);
                    if (response.success) {
                        $parent.find('> a').html($option.find('a').html());
                        $option.siblings().removeClass('selected');
                        $option.addClass('selected');
                    } else {

                    }
                } catch (e) {
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $parent.removeClass('processing');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $(document).on('click','.add-colaborator-link',function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $('body').cocoblock('unblock');
                $.conti.dialog({html: response, style: 'user-dialog', modal: true, callbackOpen: 'callNewColaborator', callbackClose: 'callUpdateDataTable', paramsOpen: {user_type: type_user_company[0], company_selected: type_user_company[1], dist_company: type_user_company[2]}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $(document).on('click','.add-company-link',function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $('body').cocoblock('unblock');
                $.conti.dialog({html: response, style: 'user-dialog', modal: true, callbackOpen: 'callNewCompany', callbackClose: 'callUpdateDataTable' });
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $(document).on('click','.resend-invitation-link',function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        var msgconfirm = $(this).data('confirmmsg');
        var txtConfirm = $(this).data('confirmtxt');

        $.conti.confirm({
            msg: msgconfirm,
            type: 'warning-dialog',
            callbackOk: 'processResendInvitation',
            paramsOk: {petition: url},
            confirmBtnText: txtConfirm
        });
    });
    $(document).on('click','.clear-fingerprint-link',function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        var msgconfirm = $(this).data('confirmmsg');
        var txtConfirm = $(this).data('confirmtxt');

        $.conti.confirm({
            msg: msgconfirm,
            type: 'warning-dialog',
            callbackOk: 'processOperColaborator',
            paramsOk: {petition: url},
            confirmBtnText: txtConfirm
        });
    });

    $(document).on('click','.clear-question-link',function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        var msgconfirm = $(this).data('confirmmsg');
        var txtConfirm = $(this).data('confirmtxt');

        $.conti.confirm({
            msg: msgconfirm,
            type: 'warning-dialog',
            callbackOk: 'processOperColaborator',
            paramsOk: {petition: url},
            confirmBtnText: txtConfirm
        });
    });



    $(document).on('click','.change-accessdata-link', function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        getAccessData(url);
    });

    $(document).on('click','.profile-company-link', function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $('body').cocoblock('unblock');
                $.conti.dialog({html: response, style: 'company-dialog', modal: true, callbackOpen: 'initEventsCompanyProfile', callbackClose: 'callUpdateDataTable'});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

    $(document).on('click','.suspend-user-link',function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'suspend-dialog', callbackOk: 'processSuspendUser', paramsOk: {petition: url, invoker: $(this)}});
    });

    $(document).on('click','.delete-user-link', function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'delete-dialog', callbackOk: 'processDeleteUser', paramsOk: {petition: url, invoker: $(this)}});
    });

    $(document).on('click', '.share-company-link', function(e) {
        e.stopPropagation();
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: $(this).attr('rel'),
            data: {},
            success: function(response) {
                $('body').cocoblock('unblock');
                try {
                    var result = $.parseJSON(response);  //is error, returned a json
                    $.conti.alert({msg: result.msg, type: 'error-dialog'});
                } catch (err) {//Exception error, is not json response, is html response, is a news row
                    $.conti.dialog({html: response, style: 'edit-dialog', modal: true, callbackOpen: 'initEventsShareCompanyWithSalesperson'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;

    });

    $(document).on('click', '.edit-corp-link', function(e) {
        e.stopPropagation();
        var item = $(this);
        $.conti.dialog({html: $('#edit_corporate_wrapper').html(), style: 'edit-dialog', modal: true, callbackOpen: 'initEventsEditCorporate', paramsOpen: {invoker: item}});
        return false;
    });

    $('.lock-unlock-link').click(function(e) {
        e.stopPropagation();
        var url = $(this).attr('rel');
        $.conti.confirm({type: 'suspend-dialog', callbackOk: 'processLockUser', paramsOk: {petition: url, invoker: $(this)}});
    });

});

function initEventsEditCorporate(invoker){
    $("#dialog_wrap #edit_corporate_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    var corporate_id = invoker.data('corpid');
    var corp_name = invoker.attr('data-name');

    $("#dialog_wrap #edit_corporate_form input#corporate_id").val(corporate_id);
    $("#dialog_wrap #edit_corporate_form input#corporate_name").val(corp_name);

    $('#dialog_wrap #save_corporate').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#dialog_wrap #edit_corporate_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#dialog_wrap #edit_corporate_form").attr('action'),
                    data: {formdata: $("#dialog_wrap #edit_corporate_form").serialize()},
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('stop', 1);
                            $('#dialog_wrap').dialog('close');
                            var itemUpdated = (invoker.closest('.action-bar-accord')).next();
                            invoker.attr('data-name', response.corp_name);
                            itemUpdated.html(response.corp_name);
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

function initEventsShareCompanyWithSalesperson(){
    var initialState = $('#salesperson_list').val();
    $('#salesperson_list').multiSelect({
        selectableHeader: "<div class='custom-header'>Available Salesperson</div>",
        selectionHeader: "<div class='custom-header'>Selected Salesperson</div>",
        afterSelect: function(values){
            if($.trim(initialState)==$.trim($('#salesperson_list').val())){
                $('#process_assoc_clients').loadingButton('disabled');//Habilitamos el boton de guardar
            }else{
                $('#process_assoc_clients').loadingButton('enabled');//Habilitamos el boton de guardar
            }

        },
        afterDeselect: function(values){
            if($.trim(initialState)==$.trim($('#salesperson_list').val())){
                $('#process_assoc_clients').loadingButton('disabled');//Habilitamos el boton de guardar
            }else{
                $('#process_assoc_clients').loadingButton('enabled');//Habilitamos el boton de guardar
            }
        }
    });
    $('#ms-salesperson_list').append('<div class="ms-legend">Click to move</div>');

    $('#process_assoc_clients').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();

                $.ajax({
                    type: 'post',
                    url: $("#share_salesperson_form").attr('action'),
                    data: { formdata: $("#share_salesperson_form").serialize() },
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
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
            return false;
        }
    });
}

function searchUsersData() {
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    //var active_tab = $('#conti_menu a.active').attr('rel');
    var active_tab = typeof ($('#generic_search input').attr('rel')) !== 'undefined' ? $('#generic_search input').attr('rel') : $('#conti_menu a.active').attr('rel');
    $('body').cocoblock();
    $("#content").load(jsVars.usersDataReload + '/' + active_tab + '/' + sort + '/' + urlencode(search), function(data) {
        $('body').cocoblock('unblock');
        initDatatableEvents();
    });
}
function initDatatableEvents() {
    $('.action-list li').tipsy({gravity: 's', fade: true});

    $('#conti_menu a').click(function(e) {
        e.stopPropagation();
        $('#conti_menu a').removeClass('active');
        $(this).attr('class', 'active');
        var target = $(this).attr('rel') + 's';
        $('#' + target).siblings().hide();
        $('#' + target).show().removeClass('hidden');

        $('#generic_search input').attr('rel', target);

        return false;
    });







    $('.resend_invitation, .restore-fingerprint, .restore-question').tipsy({gravity: 's', fade: true, className: 'tipsy-float'});


}

function callUpdateDataTable() {
    $('#logo_empresa,#logo_usuario').tipsy('hide');
    if (updated_colaborator || saved_company) {
        searchUsersData();
        updated_colaborator = false;
        saved_company = false;
    }
}

function processResendInvitation(encriptedUrlProcess) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        success: function(response) {
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processOperColaborator(encriptedUrlProcess) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        success: function(response) {
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

