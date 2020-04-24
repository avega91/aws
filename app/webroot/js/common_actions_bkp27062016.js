$.fn.blindToggle = function(speed, easing, callback) {
    var h = this.height() + parseInt(this.css('paddingTop')) + parseInt(this.css('paddingBottom'));
    return this.animate({marginTop: parseInt(this.css('marginTop')) < 0 ? 0 : -h}, speed, easing, callback);
};
$.fn.changeAttr = function(original_attr, new_attr) { 
        this.each(function() {
            var $obj_with_attr = $(this);    
            $obj_with_attr.attr(new_attr, $obj_with_attr.attr(original_attr));
            $obj_with_attr.removeAttr(original_attr); 
        }); 
     return this; 
}; 
ajax_tutorial_executed = false;
tmp_hidden_tutorial_section = 1;

function restoreInitialTutorial(){
    var restore_tutorials = $("*[data-section='"+(tmp_hidden_tutorial_section)+"']");    
    restore_tutorials.changeAttr('tmp-data-intro', 'data-intro');
}

function displayTutorialOnDialog(section){    
    
        /*tmp_hidden_tutorial_section = section-1;    
        switch(section){
            case 14:
                tmp_hidden_tutorial_section = section-4;    
            break;
        }
        
        $("*[data-section]").not( "*[data-section='"+section+"']").changeAttr('data-intro','tmp-data-intro');    
        $('body').chardinJs();    
        if($.inArray(section, jsVars.tutorialOptions['viewed'])<0){
            $('body').chardinJs('toggle');  
        }*/
}

function initTutorialSection(section){    
    
    //Sino viene seccion, se checa la seccion del sistema en la cual navegamos
    if(typeof(section)=='undefined'){
        var isAjaxContent = false;
        section = jsVars.tutorialOptions['area']+'|'+jsVars.tutorialOptions['section'];
        switch(section){
            case 'index|index': section = 1; break;
            case 'premium|index': section = 2; isAjaxContent = true; break;
            case 'reports|custom': section = 3; isAjaxContent = true; break;
            case 'conveyors|dashboard': section = 5; isAjaxContent = true; break;
            case 'conveyors|view': section = 7; isAjaxContent = true; break;
            case 'conveyors|datasheet': 
                section = 8; 
                $( ".edit-conveyor-link.one" ).show();                
            break;
            case 'conveyors|item': 
                //Existe el el filtro de elementos, es un folder
                if($('#type_filter_chosen').size()>0){ section = 9; isAjaxContent = true; }else{ section = 0; }
            break;
            case 'users|clients': section = 10; break;
            case 'companies|view': 
                section = 11; isAjaxContent= true;                 
            break;
            case 'users|all': 
                section = 13;                 
                //$('.wrapper-content div .action-bar-accord[data-section]').addClass('visible');
            break;
            default: section = 0; break;
        }
    }       

    if(section>0){

            //SI es ajax hay que esperar que termine la peticion
            if(isAjaxContent){            
                $(document).ajaxStop(function() {
                    if(!ajax_tutorial_executed){
                        //Fixes for common tutoriales sections
                        if(section==11){
                            $('.add-conveyor-link').attr('data-section',section);
                        }

                        //Si hay elementos de tutorial correspondientes a la seccion
                        if($("*[data-section='"+section+"']").size()>0){                        
                                //Removemos todos los tutoriales de otras secciones
                                $("*[data-section]").not( "*[data-section='"+section+"']").changeAttr('data-intro','tmp-data-intro');

                                //Inicializamos el tutorial
                                $('body').chardinJs();    
                                if($.inArray(section, jsVars.tutorialOptions['viewed'])<0){
                                    $('body').chardinJs('toggle');  
                                }
                        }

                        disable_tutorial_if_noexist_help(section);
                        ajax_tutorial_executed = true;
                    }
                    
                });
            }else{//Sino lanzar el tutorial
                //Si hay elementos de tutorial correspondientes a la seccion
                if($("*[data-section='"+section+"']").size()>0){                            
                        //Removemos todos los tutoriales de otras secciones
                        $("*[data-section]").not( "*[data-section='"+section+"']").changeAttr('data-intro','tmp-data-intro');
                        
                        //Inicializamos el tutorial
                        $('body').chardinJs();  
                        if($.inArray(section, jsVars.tutorialOptions['viewed'])<0){
                            $('body').chardinJs('toggle');                          
                        }
                }                
            }
        

            $('body *').click(function(e){
                $('body').chardinJs('stop');  
            });

            //On stop tutorial
            $('body').on('chardinJs:stop', function() {
                if($(".edit-conveyor-link.one").size()>0){
                    $(".edit-conveyor-link.one").hide();      
                }
                if($('.wrapper-content div .action-bar-accord[data-section="'+section+'"]').size()>0){
                    $('.wrapper-content div .action-bar-accord[data-section="'+section+'"]').removeClass('visible');
                }
            });
            
            $('#tutorial_toggle').click(function(e){
                e.stopPropagation();            
                
                if(section==8){
                    $( ".edit-conveyor-link.one" ).show();            
                }else if(section==13){
                    $('.wrapper-content div .action-bar-accord[data-section]').addClass('visible');
                }
                
                $('body').chardinJs('toggle');  
                return false;
            });
        }else{
            disable_tutorial_if_noexist_help(section);
        }
    
}

function disable_tutorial_if_noexist_help(section){      
    if($("*[data-section='"+section+"']").size()<=0 && section>0){
        $('#tutorial_toggle').hide().remove();
    }
}

/*common callback functions **/
function callUpdateSite() {
    if (processed_operation || saved_company) {
        //$.conti.block({container: $('#fixed_overlay')});
        $('body').cocoblock();
        location.reload();
    }
}

function getAccessData(encriptedUrlProcess) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        success: function(response) {
            $.conti.dialog({html: response, style: 'password-dialog', modal: true, callbackOpen: 'callUserAccessData'});
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}
function callUserAccessData() {
    var original_hash = $('#user_access_data_form').formhash();
    $(":input[placeholder]").placeholder();

    $('#user_access_data_form').keyup(function(e) {
        if ($('#user_access_data_form').formhash() != original_hash) {
            $('#update_access_data').loadingButton('enabled');//Habilitamos el boton de guardar
        } else {
            $('#update_access_data').loadingButton('disabled');//Deshabilitamos el boton de guardar
        }
    });

    $("#user_access_data_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    $('#pass_gen').passgenerator({
        displayOn: '#password'
    });

    $('#update_access_data').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#user_access_data_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#user_access_data_form").attr('action'),
                    data: {formdata: $("#user_access_data_form").serialize()},
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('stop', 1);
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
function processDeleteUser(encriptedUrlProcess, invoker) {
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

            if (response.success) {
                var item_menu = invoker.parents('.options-menu');
                item_menu = item_menu.parent();
                item_menu.hide().remove();
            }
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}
function processSuspendUser(encriptedUrlProcess, invoker) {
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
            if (response.success) {
                var currentLinkText = invoker.html();
                invoker.html(invoker.attr('alt'));
                invoker.attr('alt', currentLinkText);
            }
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processLockUser(encriptedUrlProcess, invoker) {
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
            if (response.success) {
                var invoker_assoc = invoker.attr('assoc-user');
                var currentLinkText = invoker.attr('original-title');
                
                var invokers = $('li[assoc-user='+invoker_assoc+']');
                invokers.attr('original-title', invoker.attr('alt'));
                invokers.attr('alt', currentLinkText);
                invokers.toggleClass('locked unlocked');

                invokers.removeClass('lock-unlock-link');
                invokers.unbind("click");
            }
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processDeleteCompany(encriptedUrlProcess, invoker) {
    var item_menu = invoker.parents('.row-data').size() > 0 ? invoker.parents('.row-data') : invoker.parents('.item-dashboard');
    var company_viewing = typeof (invoker.attr('referer')) !== 'undefined' ? true : false;
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        success: function(response) {
            response = $.parseJSON(response);
            if (!company_viewing) {
                $.coconotif.add({
                    text: response.msg,
                    time: jsVars.timeNotifications
                });
                $('body').cocoblock('unblock');
            }

            if (response.success) {
                if (company_viewing) {//Si trae un referer es porque esta se esta viendo la empresa
                    $.cookie("conti_notification", response.msg, {path: '/'});
                    window.location = invoker.attr('referer');
                } else {
                    item_menu.hide().remove();
                    if (response.deletedCompany != 0) {
                        $.conti.confirm({msg: response.assocMsg, type: 'company-dialog', callbackOk: 'processAssocClientsDistributor', paramsOk: {company_deleted: response.deletedCompany}});
                    }
                }
            }

        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processAssocClientsDistributor(deletedDistributor) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: jsVars.assocClientsAx,
        data: {deleted_company: deletedDistributor},
        success: function(response) {
            $.conti.dialog({html: response, style: 'company-dialog', modal: true, callbackOpen: 'callAssocClients', callbackClose: 'as', paramsOpen: {}});
            /*response = $.parseJSON(response);
             $.coconotif.add({
             text: response.msg,
             time: jsVars.timeNotifications
             });
             
             if (response.success) {
             var item_menu = invoker.parents('.row-data');
             item_menu.hide().remove();
             if(response.deletedCompany!=0){
             $.conti.confirm({msg: response.assocMsg, type: 'company-dialog', callbackOk: 'processAssocClientsDistributor', paramsOk: {company_deleted : response.deletedCompany}});
             }
             }*/
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}
function processSuspendCompany(encriptedUrlProcess, invoker, graphic_suspend) {
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
            if (response.success) {
                if (typeof (graphic_suspend) === 'undefined') {
                    var currentLinkText = invoker.attr('original-title');
                    invoker.attr('original-title', invoker.attr('alt'));
                    invoker.attr('alt', currentLinkText);
                } else {
                    processed_operation = true;
                    callUpdateSite();
                }
            }
            $('body').cocoblock('unblock');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function user_update_countries_and_states_values(selected_region, company_selected, dist_selected) {
    var user_type = $("#user_type option:selected").attr('rel');
    $('#country_user,#state_user').val('').attr('disabled', true).trigger("chosen:updated");
    $('#user_region_txt').val(selected_region);
    $('#zona').val($("#user_region option:selected").html());


    /**Load companies and corporates according user type **/
     $('#empresa,#corporativo').attr('disabled', true).trigger("chosen:updated");//Disabled select
    $.post(jsVars.getCompaniesByTypeUserAx, {type: user_type, region:selected_region}, function(response) {
        var companies_corporates = response.split('|');
        $('#empresa').html(companies_corporates[0]).trigger("chosen:updated"); 
        $('#corporativo').html(companies_corporates[1]).trigger("chosen:updated");

        if (company_selected != '') { 
            $('#empresa').val(company_selected).attr('disabled', true).trigger("chosen:updated"); 
            $('#empresa').trigger('change'); 
        }
        if (user_type == 'client' && (company_selected == '' || company_selected <= 0)) { 
            $('#all_distributors').val('').removeAttr('disabled').trigger("chosen:updated"); 
        }
        if (dist_selected > 0) { 
            $('#all_distributors').val(dist_selected).attr('disabled', true).trigger("chosen:updated"); 
            $('#all_distributors').trigger('change'); 
        }

        //Set country and states
        $.post(jsVars.updateStatesForRegionAx, {region: selected_region, type: user_type}, function(response) {
            var states_corps = response.split('|');
            var states = states_corps[0];
            var corps = states_corps[1];

            //SE agrega esto
            $('#empresa').removeAttr('disabled').trigger("chosen:updated");

            $('#corporativo').html(corps).trigger("chosen:updated");
            //aqui que onda
            if (selected_region != 'CENAM' && selected_region != 'SURAM' &&
                selected_region != 'NORTHEAST' && selected_region != 'MIDWEST' && selected_region != 'SOUTH' &&
                selected_region != 'WEST' && selected_region != 'SOUTHEAST' && selected_region != 'AUSTRALIA' && selected_region.search("UST-") < 0 && selected_region.search("CA-") < 0) { //y no es de las nuevas regiones
                $('#state_user').html(states);
                var pais = $('#state_user option:last-child').attr('alt');
                var country_id = $('#state_user option:last-child').attr('rel');
                $('#country_user').html('<option rel="' + country_id + '" value="' + pais + '">' + pais + '</option>');

                $('#country_user').removeAttr('disabled').trigger("chosen:updated");
                $('#state_user').val('').removeAttr('disabled').trigger("chosen:updated");
            } else {
                $('#country_user').html(states);
                $('#country_user').prepend('<option val=""></option>');

                $('#country_user').removeAttr('disabled').trigger("chosen:updated");
            }
        });

    });









    /**
     * Fix bbto on 16/05/2016
     */

/*
    if(user_type=="admin"){
        $('select#empresa').find('option[region-assoc="'+selected_region+'"]').attr("selected",true);
        $('select#empresa').attr('disabled', true).trigger("chosen:updated");
        $('#corporativo').removeAttr('disabled').trigger("chosen:updated");

        var selected_zone = $("#user_region option:selected").data("zone");
        if(selected_zone!=''){
            $('#zone_admin').html(selected_zone).show();
        }else{
            $('#zone_admin').hide();
        }

        company_was_selected();
    }else{
        $('#zone_admin').hide();
    }

    $.post(jsVars.updateStatesForRegionAx, {region: selected_region, type: user_type}, function(response) {
        var states_corps = response.split('|');
        var states = states_corps[0];
        var corps = states_corps[1];

        $('#corporativo').html(corps).trigger("chosen:updated");
        //aqui que onda
        if (selected_region != 'CENAM' && selected_region != 'SURAM' && 
          selected_region != 'NORTHEAST' && selected_region != 'MIDWEST' && selected_region != 'SOUTH' &&
          selected_region != 'WEST' && selected_region != 'SOUTHEAST' && selected_region != 'AUSTRALIA' && selected_region.search("UST-") < 0 && selected_region.search("CA-") < 0) { //y no es de las nuevas regiones
            $('#state_user').html(states);
            var pais = $('#state_user option:last-child').attr('alt');
            var country_id = $('#state_user option:last-child').attr('rel');
            $('#country_user').html('<option rel="' + country_id + '" value="' + pais + '">' + pais + '</option>');

            $('#country_user').removeAttr('disabled').trigger("chosen:updated");
            $('#state_user').val('').removeAttr('disabled').trigger("chosen:updated");
        } else {
            $('#country_user').html(states);
            $('#country_user').prepend('<option val=""></option>');

            $('#country_user').removeAttr('disabled').trigger("chosen:updated");
        }
    });*/
}

function user_showFields(user_type) {
    $('.user-field').hide();
    $('.' + user_type + '-field > select').val('').trigger("chosen:updated");
    $('.' + user_type + '-field').removeClass('hidden').show();
    $('.' + user_type + '-field > div').css('width', '100%');    
}

function eventSelectUserType(company_selected, dist_selected) {
    var user_type = $("#user_type option:selected").attr('rel');
    $('#country_user,#state_user').val('').trigger("chosen:updated");//Disabled select country and states
    $('#country_user').html('<option val=""></option>');
    $('#country_user,#state_user').val('').attr('disabled', true).trigger("chosen:updated");//Disabled select country and states

    user_showFields(user_type);
    $('#user_type_txt').val($("#user_type option:selected").val());

    //always block company
    $('#empresa').attr('disabled',true).trigger("chosen:updated");

    if (user_type == 'admin') {//Si no se selecciono un usuario cliente
        $('#brand').parent().removeClass('hidden');
        $('#brand_chosen').css('width', '101.6%');

    }else{
        $('#zone_admin').hide();
        $('#brand').parent().addClass('hidden');
        //Si selecciona otro tipo de usuario, hay que seleccionar la marca del admin
        $("#brand option:eq(1)").attr('selected','selected').trigger("chosen:updated");        
    }
    
    if (user_type != 'client') {//Si no se selecciono un usuario cliente
        $('#all_distributors').attr('disabled');
        $('#all_distributors').val('').trigger("chosen:updated");
        $('#all_distributors').parent().hide();

        $('#user_region').parent().show();
        $('#user_region').val('').attr('disabled', true).trigger("chosen:updated");
        var peticion = jsVars.rxc != 'admin' ? jsVars.updateAllRegionsAx : jsVars.updateRegionsForUserAx;
        $.post(peticion, {dist: jsVars.cid}, function(regions) {
            $('#user_region').html(regions);
            $('#user_region').removeAttr('disabled').trigger("chosen:updated");

            //@TODO
            //setDefaultCompany(user_type, company_selected, dist_selected);
        });
    } else { //@TODO
        $('#user_region').parent().hide();
        $('#all_distributors').parent().removeClass('hidden').show();
        //$('#all_distributors').val('').removeAttr('disabled').trigger("chosen:updated");
        $('#all_distributors').next().css('width', '100%');
        setDefaultCompany(user_type, company_selected, dist_selected);
    }
    activateManagerCtrl(user_type);
}

function activateManagerCtrl(user_type) {
    var parent_ctrl = $('#user_manager_ctrl').closest('.two-controls');
    if (user_type == 'client' || user_type == 'distributor') {
        parent_ctrl.prev().attr('class', 'two-controls');
        parent_ctrl.removeClass('hidden');
    } else {
        parent_ctrl.prev().attr('class', 'full-controls');
        parent_ctrl.addClass('hidden');
        $('#user_manager_ctrl').addClass('inactive');
        $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
    }

}

function setDefaultCompany(user_type, company_selected, dist_selected) {
    /**Load companies and corporates according user type **/
    $('#empresa,#corporativo').attr('disabled', true).trigger("chosen:updated");//Disabled select
    $.post(jsVars.getCompaniesByTypeUserAx, {type: user_type}, function(response) {
        var companies_corporates = response.split('|');
        //enable regions until here
        if (user_type != 'client') {
            $('#user_region').removeAttr('disabled').trigger("chosen:updated");
        }
        $('#empresa').removeAttr('disabled').html(companies_corporates[0]).trigger("chosen:updated");
        $('#corporativo').html(companies_corporates[1]).trigger("chosen:updated");

        //Si viene vacio company_selected y si es admin el user seleccionado
        //if(company_selected=="" && user_type == 'admin'){
        //if(user_type == 'admin'){
        //always disabled
            $('#empresa').attr('disabled',true).trigger("chosen:updated");
        //}

        if (company_selected != '') {
            $('#empresa').val(company_selected).attr('disabled', true).trigger("chosen:updated");
            $('#empresa').trigger('change');
        }

        if (user_type == 'client' && (company_selected == '' || company_selected <= 0)) {
            $('#all_distributors').val('').removeAttr('disabled').trigger("chosen:updated");
        }

        if (dist_selected > 0) {
            $('#all_distributors').val(dist_selected).attr('disabled', true).trigger("chosen:updated");
            $('#all_distributors').trigger('change');
        }
    });
}

function hideOptionsCompanies(dist_id) {
    $('#empresa').val('').trigger("chosen:updated");
    $('#empresa option').each(function() {
        if ($(this).attr('assoc-d') != dist_id && $(this).attr('value') != 'new') {
            $(this).hide();
        } else {
            $(this).show();
        }
    });
    $('#empresa').trigger("chosen:updated");
}

function eventSelectDistributor(company_selected) {
    var dist_id = $("#all_distributors option:selected").val();
    if (company_selected == '' || company_selected <= 0) {
        hideOptionsCompanies(dist_id);
    }

    $('#user_region').attr('disabled', true).trigger("chosen:updated");
    $('#country_user,#state_user').val('').attr('disabled', true).trigger("chosen:updated");//Disabled select country and states
    $('#all_distributors_txt').val(dist_id);
    $.post(jsVars.updateRegionsForUserAx, {dist: dist_id}, function(regions) {
        $('#user_region').html(regions);
        $('#user_region').removeAttr('disabled').trigger("chosen:updated");
        $('#user_region').trigger('change');
    });
}

function changeClientCustomReport(client_update) {
    $('#client').attr('disabled', true).trigger("chosen:updated");
    var client_id = $("#client option:selected").val();
    $('#conveyor_list > div').cocoblock();
    $('.conveyors-selector').removeClass('selected').addClass('unselected');
    $.post(jsVars.conveyorsClientsAx, {client: client_id}, function(conveyors) {
        $('#conveyor_list > div').html(conveyors);
        $('#conveyor_list > div').perfectScrollbar('destroy');
        $('#conveyor_list > div').perfectScrollbar({suppressScrollX: true});
        $('#conveyor_list > div').cocoblock('unblock');
        $('#conveyor_list ul li').tipsy({gravity: 'w', fade: true});
        if (client_update > 0) {//es update
            if ($('#selected_conveyors').size() > 0) {
                var conveyors = $('#selected_conveyors').val().split(',');
                $.each(conveyors, function(index, id) {
                    $('#conveyor_list ul li#' + id).addClass('active');
                });
            }

            if ($('#selected_fields').size() > 0) {
                var fields = $('#selected_fields').val().split(',');
                $.each(fields, function(index, field) {
                    $('.conveyor-label[rel="' + field + '"]').addClass('active');
                });
            }
        } else {
            $('#client').removeAttr('disabled');
        }
        $('#client').trigger("chosen:updated");
    });
}

function changeDistributorCustomReport(client_id) {
    $('#client').attr('disabled', true).trigger("chosen:updated");
    $('#distributor').prev().val($('#distributor').val());
    $.post(jsVars.getClientsDistributorAx, {dist: $('#distributor').val()}, function(clientes) {
        $('#client').html(clientes);
        if (client_id > 0) {
            $('#client').val(client_id);
            $('#client').prev().val($('#client').val());
            changeClientCustomReport(client_id);
        } else {
            $('#client').removeAttr('disabled');
        }
        $('#client').trigger("chosen:updated");

    });
}


function changeDistributorConveyor(client_id) {
    $('#client').attr('disabled', true).trigger("chosen:updated");
    $('#distributor').prev().val($('#distributor').val());
    $.post(jsVars.getClientsDistributorAx, {dist: $('#distributor').val()}, function(clientes) {
        $('#client').html(clientes);
        if (client_id > 0) {
            $('#client').val(client_id);
            $('#client').prev().val($('#client').val());
        } else {
            $('#client').removeAttr('disabled');
        }
        $('#client').trigger("chosen:updated");

    });
}

function initConveyorsDatatableEvents() {
    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
    $('.item-dashboard p').tipsy({gravity: 's', fade: true});

    $(".conveyor-status select").chosen({
        disable_search: true,
        width: "100%"
    });

    $('.chosen-container').on('click', function(e) {
        e.stopPropagation();
        return false;
    });

    $(".conveyor-status select").on('change', function(evt, params) {
        var status = $(this).val();
        var petition = $(this).attr('rel');
        $.conti.confirm({type: 'conveyor-dialog', callbackOk: 'processChangeConveyorStatus', paramsOk: {petition: petition, new_status: status, function_update_events: 'callUpdateConveyorsDataTable'}});
    });

/*
    $('.add-conveyor-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');

        var dist = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : 0;
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            data: {did: dist},
            success: function(response) {
                $.conti.dialog({html: response, style: 'conveyor-dialog', modal: true, callbackOpen: 'callAddConveyor', callbackClose: 'callUpdateConveyorsDataTable', paramsOpen: {}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });
    
    */

}

function initTrackingConveyorsDatatableEvents() {
    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
    $('.item-dashboard p').tipsy({gravity: 's', fade: true});

    $(".conveyor-status select").chosen({
        disable_search: true,
        width: "100%"
    });

    $('.chosen-container').on('click', function(e) {
        e.stopPropagation();
        return false;
    });

    $(".conveyor-status select").on('change', function(evt, params) {
        var status = $(this).val();
        var petition = $(this).attr('rel');
        $.conti.confirm({type: 'conveyor-dialog', callbackOk: 'processChangeTrackingConveyorStatus', paramsOk: {petition: petition, new_status: status, function_update_events: 'callUpdateTrackingConveyorsDataTable'}});
    });

    $('.add-tracking-conveyor-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'trac-dialog', modal: true, callbackOpen: 'callAddTrackingConveyor', callbackClose: 'callUpdateTrackingConveyorsDataTable', paramsOpen: {}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

}

function initCustomReportsDatatableEvents() {
    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
    $('.item-dashboard p').tipsy({gravity: 's', fade: true});

    $(".conveyor-status select").chosen({
        disable_search: true,
        width: "100%"
    });

    $('.chosen-container').on('click', function(e) {
        e.stopPropagation();
        return false;
    });

    $(".conveyor-status select").on('change', function(evt, params) {
        var status = $(this).val();
        var petition = $(this).attr('rel');
        $.conti.confirm({type: 'conveyor-dialog', callbackOk: 'processChangeTrackingConveyorStatus', paramsOk: {petition: petition, new_status: status, function_update_events: 'callUpdateTrackingConveyorsDataTable'}});
    });

    $('.add-custom-report-link').click(function(e) {
        e.stopPropagation();
        var pet = $(this).attr('rel');
        var type_user_company = $(this).attr('alt');
        type_user_company = type_user_company.split('@');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: pet,
            success: function(response) {
                $.conti.dialog({html: response, style: 'crep-dialog', modal: true, callbackOpen: 'callAddCustomReport', callbackClose: 'callUpdateCustomReportDataTable', paramsOpen: {}});
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
        return false;
    });

}


function callUpdateConveyorsDataTable() {    
    restoreInitialTutorial();
    if (saved_conveyor) {
        searchConveyorsData(jsVars.rowsToShow, 0);
        saved_conveyor = false;
    }
}

function callUpdateTrackingConveyorsDataTable() {
    if (saved_tracking_conveyor) {
        searchTrackingConveyorsData(jsVars.rowsToShow, 0);
        saved_tracking_conveyor = false;
    }
}

function callUpdateCustomReportDataTable() {    
    restoreInitialTutorial();
    if (saved_custom_report) {
        searchCustomReportsData(jsVars.rowsToShow, 0);
        saved_custom_report = false;
    }
}

function searchConveyorsData(rows, from) {

    if (number_saved_conveyor != '') {
        $('#toolbar .texteable-option input').focus();
        $('#generic_search input').val(number_saved_conveyor);
        number_saved_conveyor = '';
    }
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    search = search == '' || search == 'Buscar' || search == 'Search' ? '-' : search;
    var pages = typeof (rows) !== 'undefined' && typeof (from) !== 'undefined' ? '/' + rows + '/' + from : '';
    var url = jsVars.refreshConveyorsAx + '/' + sort + '/' + urlencode(search) + pages;
    $('body').cocoblock();
    from = typeof (from) !== 'undefined' ? from : 0;
    rows = typeof (rows) !== 'undefined' ? rows : 0;
    $.get(url, function(data) {
        $('body').cocoblock('unblock');
        if ($("#conveyors_data").find('.dashboard-list').size() > 0 && from > 0) {
            $("#conveyors_data").find('.dashboard-list').append(data);
            if (from > jsVars.totConveyors) {
                $("#conveyors_data").find('> div:last-child').hide();
            } else {
                $("#conveyors_data").find('> div:last-child').show();
            }
        } else {
            var regs_found = $(data).find('> li').size();
            if (rows > jsVars.totConveyors || pages == '' || regs_found < rows) {
                $("#conveyors_data").find('> div:last-child').hide();
            } else {
                $("#conveyors_data").find('> div:last-child').show();
            }
            $("#conveyors_data").find('> div:first-child').html(data);
        }
        initConveyorsDatatableEvents();
    });
}

function searchTrackingConveyorsData(rows, from) {

    if (number_saved_tracking_conveyor != '') {
        $('#toolbar .texteable-option input').focus();
        $('#generic_search input').val(number_saved_tracking_conveyor);
        number_saved_tracking_conveyor = '';
    }
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    search = search == '' || search == 'Buscar' || search == 'Search' ? '-' : search;
    var pages = typeof (rows) !== 'undefined' && typeof (from) !== 'undefined' ? '/' + rows + '/' + from : '';
    var url = jsVars.refreshTrackingConveyorsAx + '/' + sort + '/' + urlencode(search) + pages;
    $('body').cocoblock();
    from = typeof (from) !== 'undefined' ? from : 0;
    rows = typeof (rows) !== 'undefined' ? rows : 0;
    $.get(url, function(data) {
        $('body').cocoblock('unblock');
        if ($("#conveyors_data").find('.dashboard-list').size() > 0 && from > 0) {
            $("#conveyors_data").find('.dashboard-list').append(data);
            if (from > jsVars.totConveyors) {
                $("#conveyors_data").find('> div:last-child').hide();
            } else {
                $("#conveyors_data").find('> div:last-child').show();
            }
        } else {
            var regs_found = $(data).find('> li').size();
            if (rows > jsVars.totConveyors || pages == '' || regs_found < rows) {
                $("#conveyors_data").find('> div:last-child').hide();
            } else {
                $("#conveyors_data").find('> div:last-child').show();
            }
            $("#conveyors_data").find('> div:first-child').html(data);
        }
        initTrackingConveyorsDatatableEvents();
    });
}

function searchCustomReportsData(rows, from) {

    if (number_saved_custom_report != '') {
        $('#toolbar .texteable-option input').focus();
        $('#generic_search input').val(number_saved_custom_report);
        number_saved_custom_report = '';
    }
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    var input_search = $('#generic_search input');
    var search = $.trim(input_search.val());

    search = search == '' || search == 'Buscar' || search == 'Search' ? '-' : search;
    var pages = typeof (rows) !== 'undefined' && typeof (from) !== 'undefined' ? '/' + rows + '/' + from : '';
    var url = jsVars.refreshCustomReportsAx + '/' + sort + '/' + urlencode(search) + pages;
    $('body').cocoblock();
    from = typeof (from) !== 'undefined' ? from : 0;
    rows = typeof (rows) !== 'undefined' ? rows : 0;
    $.get(url, function(data) {
        $('body').cocoblock('unblock');
        if ($("#custom_rep_data").find('.dashboard-list').size() > 0 && from > 0) {
            $("#custom_rep_data").find('.dashboard-list').append(data);
            if (from > jsVars.totItems) {
                $("#custom_rep_data").find('> div:last-child').hide();
            } else {
                $("#custom_rep_data").find('> div:last-child').show();
            }
        } else {
            var regs_found = $(data).find('> li').size();
            if (rows > jsVars.totItems || pages == '' || regs_found < rows) {
                $("#custom_rep_data").find('> div:last-child').hide();
            } else {
                $("#custom_rep_data").find('> div:last-child').show();
            }
            $("#custom_rep_data").find('> div:first-child').html(data);
        }
        initCustomReportsDatatableEvents();
    });
}

function callAddConveyor(dist_id, client_id, active_tab) {
    //$('html').disablescroll();
    
    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;
    active_tab = typeof (active_tab) !== 'undefined' ? active_tab : 0;


    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    $('#poleas,#material').parent().css('height', '340px');
    $('#poleas,#material').css({'overflow-x': 'hidden', 'overflow-y': 'scroll'});
    $('#poleas,#material').perfectScrollbar({suppressScrollX: true});
    $("#poleas,#material").click(function() {
        $(this).scroll();
    });

    $('#slide_form > a,#slide_form > div > a').click(function(e) {
        $('.column-ctrl .chosen-container').css('width', '105%');
        //$('.slide-form-section > div').css('position','absolute');//ponemos en absolute todos los divs
        $('.active_section').css('position','absolute');
        $('.ps-container .ps-scrollbar-y-rail').css('position','absolute');
    });


    $('#logo_transportador').tipsy({gravity: 's', fade: true, offset: -30});
    $('#slide_form a').tipsy({gravity: 's', fade: true});
    $(":input[placeholder]").placeholder();

    $("#add_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen',
        onFieldFailure: function(){
            $('.active_section').css('position','static');
            $('.ps-container .ps-scrollbar-y-rail').css('position','static');
        }
    });


    $("#add_conveyor_form select:not(.image-chosen)").chosen({search_contains: true});
    $("#add_conveyor_form select.image-chosen").chosen({
        disable_search: true,
        width: "100%"
    });

    /**Cuando se abran los selects q estan dentro de container con scroll **/
    $("#add_conveyor_form #poleas select,#add_conveyor_form #material select").on('chosen:showing_dropdown', function(evt, params) {
        
        var width_form = parseInt($(this).closest('.fancy_form').css('width'));
        $('.active_section').css('width', parseInt(width_form)+'px');
        $('.active_section').css('position', 'static');        
        $('.active_section').perfectScrollbar('destroy');

        var pos_dropdown = $(this).next().position();
        
        $(this).next().addClass('chosen-open-with-scroll');
        $(this).next().css('top', pos_dropdown.top + 'px');
    });

    $("#add_conveyor_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#add_conveyor_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
        $(this).next().removeClass('chosen-open-with-scroll');
        $(this).next().css('top', 'inherit');
        //$(this).next().css('height','inherit !important;');

        active_chosens = $('.active_section').find('.chosen-with-drop').size();
        if ($('.active_section').attr('id') == 'poleas' || $('.active_section').attr('id') == 'material' && active_chosens <= 0) {
            $('.active_section').css('position', 'absolute');
            $('.active_section').perfectScrollbar({suppressScrollX: true});
        }
    });


    if (dist_id > 0 && client_id > 0 || dist_id > 0) {
        $('#distributor').val(dist_id).attr('disabled', true).trigger("chosen:updated");
        changeDistributorConveyor(client_id);
    }

    $('#distributor').change(function() {
        changeDistributorConveyor(client_id);
    });

    $('#client').change(function() {
        $(this).prev().val($(this).val());
    });

    $("#sel_desc_material").change(function() {
        var densidad = $("#sel_desc_material option:selected").attr('rel');
        $('input[name="densidad_material"]').val(densidad);
    });

    $('textarea[name="observaciones"]').limitCharsTxt(300);

    $.datepicker.setDefaults({
        showOtherMonths: true,
        selectOtherMonths: true
    }, $.datepicker.regional[ "" ]);

    $('input[name="fecha_instalacion"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
    $('input[name="fecha_instalacion"]').val($('#fecha_instalacion_hidden').val());

    if (active_tab > 0) {
        $('#slide_form a').eq(active_tab).trigger('click');
        $('.column-ctrl .chosen-container').css('width', '105%');
    }


    var image_portada = $('#reference_bg_uploader').size() > 0 && $('#reference_bg_uploader').val() != '' ? $('#reference_bg_uploader').val() : 'img/img_conveyor_off.png';
    $('#logo_transportador').contiUploader({
        icon: jsVars.site + image_portada,
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-usuario',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_transportador').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'user-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $('#save_conveyor').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#add_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#add_conveyor_form").attr('action'),
                    data: {formdata: $("#add_conveyor_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_conveyor = true;
                                processed_operation = true;
                                number_saved_conveyor = response.conveyor_number;
                                /*if (dist_id <= 0 || client_id <= 0) {
                                    $('#dialog_wrap').dialog('close');
                                }*/
                                $('#dialog_wrap').dialog('close');
                            } else {
                                if (response.code >= 0) {
                                    $('#slide_form a').eq(response.code).trigger('click');
                                }

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
    
    /**init tutorial **/
    //initTutorialSection(6);
    displayTutorialOnDialog(6);
}


function callAddTrackingConveyor(dist_id, client_id, active_tab) {

    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;
    active_tab = typeof (active_tab) !== 'undefined' ? active_tab : 0;

    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    $(":input[placeholder]").placeholder();

    $("#add_tracking_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $("#add_tracking_conveyor_form select").chosen({search_contains: true});


    $("#add_tracking_conveyor_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#add_tracking_conveyor_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });


    if (dist_id > 0 && client_id > 0 || dist_id > 0) {
        $('#distributor').val(dist_id).attr('disabled', true).trigger("chosen:updated");
        changeDistributorConveyor(client_id);
    }

    $('#distributor').change(function() {
        changeDistributorConveyor(client_id);
    });

    $('#client').change(function() {
        $(this).prev().val($(this).val());
    });


    $('#save_conveyor').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#add_tracking_conveyor_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#add_tracking_conveyor_form").attr('action'),
                    data: {formdata: $("#add_tracking_conveyor_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_tracking_conveyor = true;
                                processed_operation = true;
                                number_saved_tracking_conveyor = response.conveyor_number;
                                if (dist_id <= 0 || client_id <= 0) {
                                    $('#dialog_wrap').dialog('close');
                                }
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

function callAddCustomReport(dist_id, client_id, active_tab) {    

    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;
    active_tab = typeof (active_tab) !== 'undefined' ? active_tab : 0;

    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    $('#slide_form a').tipsy({gravity: 's', fade: true});
    $(":input[placeholder]").placeholder();

    $('#poleas,#material').parent().css('height', '340px');
    $('#poleas,#material').css({'overflow-x': 'hidden', 'overflow-y': 'scroll'});
    $('#poleas,#material').perfectScrollbar({suppressScrollX: true});
    $("#poleas,#material").click(function() {
        $(this).scroll();
    });

    $('.conveyors-selector').click(function(e) {
        e.stopPropagation();

        if ($(this).hasClass('unselected')) {
            $(this).removeClass('unselected').addClass('selected');
            $('#conveyor_list ul li').attr('class', 'active');
        } else {
            $(this).removeClass('selected').addClass('unselected');
            $('#conveyor_list ul li').removeClass('active');
        }

        return false;
    });

    $('.conveyor-label').click(function(e) {
        e.stopPropagation();
        $(this).toggleClass('active');
        return false;
    });

    $('#conveyor_list').on('click', 'ul li', function(e) {
        e.stopPropagation();
        $(this).toggleClass('active');
        return false;
    });

    $("#add_custom_report_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $('#titulo_reporte').focus();
    $('#titulo_reporte').trigger('focus');

    $("#add_custom_report_form select").chosen({search_contains: true});


    $("#add_custom_report_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#add_custom_report_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });


    if (dist_id > 0 && client_id > 0 || dist_id > 0) {
        $('#distributor').val(dist_id).attr('disabled', true).trigger("chosen:updated");
        changeDistributorCustomReport(client_id);
    }

    $('#distributor').change(function() {
        changeDistributorCustomReport(client_id);
    });

    $('#client').change(function() {
        $(this).prev().val($(this).val());
        changeClientCustomReport(client_id);
    });

    $('#templates').change(function() {
        $('.conveyor-label').removeClass('active');
        if ($(this).val() == 'new') {
            var parent = $(this).parent();
            var select = parent.find('select');
            var val_to_input = "";
            if (select.find('option.new-option').size() > 0) {
                val_to_input = select.find('option.new-option').val();
            }
            parent.append('<a class="cancel-input action-input"></a><a class="accept-input action-input"></a><input type="text" placeholder="' + $(this).attr('data-placeholder') + '" class="trigger-new-option validate[required]" value="' + val_to_input + '"/>');
            parent.find('.chosen-container').hide();
            parent.find('input.trigger-new-option').focus();
        } else {
            var template_selected = $("#templates option:selected");
            var fields = template_selected.attr('rel');
            fields = fields.split(',');
            $.each(fields, function(i, field) {
                $('.conveyor-label[rel="' + field + '"]').addClass('active');
            });
        }
    });

    $(document).on('focus', '.trigger-new-option', function() {
        $(this).prev().removeClass('forgotten-link');
        $('#save_custom_report').loadingButton('disabled');//Deshabilitamos el boton de guardar
    });

    $(document).on('blur', '.trigger-new-option', function() {
        $(this).prev().addClass('forgotten-link');
    });

    $(document).on('click', '.cancel-input', function(e) {
        e.stopPropagation();
        var parent = $(this).parent();
        parent.find('select').val('').trigger("chosen:updated");

        parent.find('.chosen-container').show();
        parent.find('input.trigger-new-option').hide().remove();//hide input nuevo
        parent.find('.action-input').hide().remove();//hide action inputs

        if ($('.cancel-input').size() <= 0) {
            $('#save_custom_report').loadingButton('enabled');//Habilitamos el boton de guardar    
        }

        return false;
    });

    $(document).on('click', '.accept-input', function(e) {
        e.stopPropagation();
        $(this).removeClass('forgotten-link');

        var parent = $(this).parent();
        var input_val = parent.find('input.trigger-new-option').val();
        var select = parent.find('select');
        if ($.trim(input_val) != '') {
            select.find('option.new-option').hide().remove();//remove old new options added at the select
            select.append('<option class="new-option" value="' + input_val + '">' + input_val + '</option>');
            select.find('option:eq(1)').text(select.find('option:eq(1)').attr('rel'));//Text editar            
        }

        parent.find('.chosen-container').show();
        select.val(input_val).trigger("chosen:updated");

        parent.find('input.trigger-new-option').hide().remove();//hide input nuevo
        parent.find('.action-input').hide().remove();//hide action inputs

        if ($('.accept-input').size() <= 0) {
            $('#save_custom_report').loadingButton('enabled');//Habilitamos el boton de guardar    
        }

        return false;
    });


    $('#save_custom_report').loadingButton({
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();
            var conveyors = getCustomReportConveyorsSelected();
            var fields = getCustomReportFieldsSelected();
            var label_fields = fields[1];
            fields = fields[0];
            if ($("#add_custom_report_form").validationEngine('validate') && conveyors != '' && fields != '') {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#add_custom_report_form").attr('action'),
                    data: {formdata: $("#add_custom_report_form").serialize(), bandas: conveyors, campos: fields, titulos: label_fields},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                saved_custom_report = true;
                                processed_operation = true;
                                number_saved_custom_report = response.custom_report_title;
                                if (dist_id <= 0 || client_id <= 0) {
                                    $('#dialog_wrap').dialog('close');
                                }
                            } else {
                                if (response.code >= 0) {
                                    $('#slide_form a').eq(response.code).trigger('click');
                                }
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
                if (conveyors == '' || fields == '') {
                    $.coconotif.add({
                        text: jsVars.systemMsgs.conveyorsAndFieldsNeeded,
                        time: jsVars.timeNotifications
                    });
                }

                btn.loadingButton('stop', -1);
            }
            return false;
        }
    });
    
    /**init tutorial **/
    //initTutorialSection(4);
    displayTutorialOnDialog(4);
}

function getCustomReportConveyorsSelected() {
    var selected_conveyors = '';
    $('#conveyor_list ul li.active').each(function() {
        selected_conveyors += $(this).attr('id') + ',';
    });
    selected_conveyors = selected_conveyors != '' ? selected_conveyors.slice(0, -1) : selected_conveyors;
    return selected_conveyors;
}
function getCustomReportFieldsSelected() {
    var selected_fields = '';
    var selected_label_fields = '';
    $('.conveyor-label.active').each(function() {
        selected_fields += $(this).attr('rel') + ',';
        selected_label_fields += $(this).attr('assoc-label') + ',';
    });
    selected_fields = selected_fields != '' ? selected_fields.slice(0, -1) : selected_fields;
    selected_label_fields = selected_label_fields != '' ? selected_label_fields.slice(0, -1) : selected_label_fields;

    return [selected_fields, selected_label_fields];
}

function callAssocClients() {
    $("#assoc_clients_distributor_form select").chosen({search_contains: true});
    $("#assoc_clients_distributor_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#assoc_clients_distributor_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });

    $("#assoc_clients_distributor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $('#process_assoc_clients').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#assoc_clients_distributor_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#assoc_clients_distributor_form").attr('action'),
                    data: {formdata: $("#assoc_clients_distributor_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
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

function company_was_selected(){
    var company_selected = $("#empresa").val();

    //Se esta dando de alta un cliente
    if ($('#all_distributors_chosen').is(':visible')) {
        var parent_dist = $("#empresa option:selected").attr('assoc-d');
        $('#all_distributors').val(parent_dist).trigger("chosen:updated");
    }


    //Define associated corporate for company
    var corporativo_empresa = $("#empresa option:selected").attr('alt');
    $('#corporativo').val(corporativo_empresa);
    if (corporativo_empresa != '') {//Se selecciono una empresa y hay un corporativo
        //Siempre seleccionar corp (commented)
        // $('#corporativo').attr('disabled', true);

        //Activamos el control manager
        $('#user_manager_ctrl').removeClass('inactive active');
        $('#user_manager_ctrl').attr('original-title',jsVars.textActiveManagerCtrl);
    } else {
        //Desactivamos el control manager
        $('#user_manager_ctrl').addClass('inactive').removeClass('active');
        $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
        $('#corporativo').removeAttr('disabled');
    }
    $('#corporativo').trigger("chosen:updated");
    //End define associated corporate


    /*******************
     * @Fix bbto 16/05/2016
     * IF ADMIN DON'T DO ANYTHING on REGIONS
     */

    //Set region if admin
    var user_type = $("#user_type option:selected").attr('rel');
    if(user_type!='admin') {
        var region_empresa = $("#empresa option:selected").attr('region-assoc');
        $('#user_region').val(region_empresa);
        $('#user_region').attr('disabled', true);
        $('#user_region').trigger("chosen:updated");
        user_update_countries_and_states_values(region_empresa);
    }


    $('#save_user_nouser').loadingButton('enabled');//Habilitamos el boton de guardar

    $('#save_user').loadingButton('enabled');//Habilitamos el boton de guardar
    var pet = $("#empresa").attr('rel');
    $('#logo_empresa').cocoblock();
    $.ajax({
        type: 'post',
        url: pet,
        data: {company: company_selected},
        success: function(response) {
            var empresa = $.parseJSON(response);
            if (!$.isEmptyObject(empresa)) {
                $('#logo_empresa').cocoblock('unblock');
                if (empresa.path_image != '') {
                    $('#logo_empresa').css('background-image', 'url("' + jsVars.site + empresa.path_image + '")');
                    $('#path_logo_empresa').val(empresa.path_image);
                }

                $('#empresa_txt').val(empresa.id);
                $('#ciudad').val(empresa.city);
                $('#direccion').val(empresa.address);
                //$('#ciudad,#direccion,#logo_empresa input[type="file"]').attr('disabled', true);
                $('#logo_empresa input[type="file"]').attr('disabled', true);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

/**Funcion para eventos al agregar usuario empresa y regiones**/
function callOpenUser(user_type, company_selected, dist_selected) {
    $("#add_user_form select").chosen({search_contains: true});
    $('#puesto_chosen').css('width', '102%');
    
    /**Para cuando se da de alta un usuario desde la tabla de usuarios, no del boton de toolbar*/
    company_selected = typeof (company_selected) !== 'undefined' ? company_selected : '';
    dist_selected = typeof (dist_selected) !== 'undefined' ? dist_selected : 0;

    // && user_type>0
    if (typeof (user_type) !== 'undefined' && company_selected != '') {
        $('#user_type').val(user_type).attr('disabled', true).trigger("chosen:updated");
        eventSelectUserType(company_selected, dist_selected);
    }

    $('#logo_empresa,#logo_usuario').tipsy({gravity: 's', fade: true, offset: -50});
    $(":input[placeholder]").placeholder();
   
    $("#add_user_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });
    

    $("#add_user_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#add_user_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });

    if (jsVars.rxc == 'distributor') {
        //user_update_countries_and_states_values($('#user_region').val());
        if (company_selected == '' || company_selected <= 0) {
            eventSelectUserType('', $('#all_distributors').val());
        }

    }

    $('#user_type').change(function() {
        if (company_selected == '' || company_selected <= 0) {
            eventSelectUserType(company_selected, dist_selected);
        }
    });

    //Onchange value distributors, load regions distributor
    $('#all_distributors').change(function() {
        eventSelectDistributor(company_selected);
    });


    $('#user_region').change(function() {
        var selected_region = $(this).val();
        if (selected_region != '') {
            user_update_countries_and_states_values(selected_region, company_selected, dist_selected);
        } else {
            $('#country_user').removeAttr('disabled').trigger("chosen:updated");
        }
    });

    //Onchange value pais
    $('#country_user').change(function() {
        var pais_id = $("#country_user option:selected").attr('rel');
        $('#state_user').attr('disabled', true).trigger("chosen:updated");
        $.post(jsVars.updateStatesForCountryAx, {country: pais_id}, function(states) {
            $('#state_user').html(states);
            $('#state_user').removeAttr('disabled').trigger("chosen:updated");
        });
    });
    
    //Onchange value state
    $('#state_user').change(function() {
        var state_id = $("#state_user option:selected").attr('state_id');
        $('#ciudad').attr('disabled', true).trigger("chosen:updated");
        $.post(jsVars.updateCitiesForCountryAx, {state: state_id}, function(cities) {
            $('#ciudad').html(cities);
            $('#ciudad').removeAttr('disabled').trigger("chosen:updated");
        });
    });

    $('#corporativo,#empresa').change(function() {
        //Para que pueda seleccionar el corp siempre
        $('#corporativo').removeAttr('disabled').trigger("chosen:updated");
        if ($(this).val() == 'new') {
            var parent = $(this).parent();

           // $('#corporativo').val('').removeAttr('disabled').trigger("chosen:updated");

            //si esta deshabilitado es porque se selecciono una empresa. Hay que
            //permitir seleccionar una nueva region
            if($('#user_region').is(':disabled') && parent.find('#empresa').size() > 0){
                //$('#user_region').val('').removeAttr('disabled').trigger("chosen:updated");
                $('#user_region').removeAttr('disabled').trigger("chosen:updated");
            }




            var select = parent.find('select');
            var val_to_input = "";
            if (select.find('option.new-option').size() > 0) {
                val_to_input = select.find('option.new-option').val();
            }
            parent.append('<a class="cancel-input action-input"></a><a class="accept-input action-input"></a><input type="text" placeholder="' + $(this).attr('data-placeholder') + '" class="trigger-new-option validate[required]" value="' + val_to_input + '"/>');
            parent.find('.chosen-container').hide();
            parent.find('input.trigger-new-option').focus();

            if (parent.find('#empresa').size() > 0) {
                $('#user_manager_ctrl').addClass('inactive').removeClass('active');
                $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
            }

        } else if ($(this).attr('id') == 'empresa') {
            if ($.isNumeric($(this).val())) {
                //New function fix bbto
                company_was_selected();
            } else {
                $('#user_region').val('').removeAttr('disabled').trigger("chosen:updated");

                $('#ciudad,#direccion,#path_logo_empresa,#logo_empresa input[type="file"]').removeAttr('disabled');
                $('#logo_empresa').css('background-image', $('#logo_empresa').attr('rel'));

                //Desactivamos el control manager
                $('#user_manager_ctrl').addClass('inactive').removeClass('active');
                $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
            }
        } else {//Es Corporativo
            if ($.isNumeric($(this).val())) {
                if ($('#user_manager_ctrl').hasClass('inactive')) {
                    $('#user_manager_ctrl').removeClass('active');
                }
                //Activamos el control manager
                $('#user_manager_ctrl').removeClass('inactive');
                $('#user_manager_ctrl').attr('original-title',jsVars.textActiveManagerCtrl);
            } else {
                //Desactivamos el control manager
                $('#user_manager_ctrl').addClass('inactive').removeClass('active');
                $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
            }
        }
    });

    $(document).on('focus', '.trigger-new-option', function() {
        $(this).prev().removeClass('forgotten-link');
        $('#save_user').loadingButton('disabled');//Deshabilitamos el boton de guardar
        $('#save_user_nouser').loadingButton('disabled');//Deshabilitamos el boton de guardar
    });

    $(document).on('blur', '.trigger-new-option', function() {
        $(this).prev().addClass('forgotten-link');
    });

    $(document).on('click', '.cancel-input', function(e) {
        e.stopPropagation();
        var parent = $(this).parent();
        parent.find('select').val('').trigger("chosen:updated");

        parent.find('.chosen-container').show();
        parent.find('input.trigger-new-option').hide().remove();//hide input nuevo
        parent.find('.action-input').hide().remove();//hide action inputs
        $('#ciudad,#direccion,#path_logo_empresa,#logo_empresa input[type="file"]').removeAttr('disabled');
        $('#logo_empresa').css('background-image', $('#logo_empresa').attr('rel'));

        if ($('.cancel-input').size() <= 0) {
            $('#save_user').loadingButton('enabled');//Habilitamos el boton de guardar
            $('#save_user_nouser').loadingButton('enabled');
        }

        if (parent.find('#corporativo').size() > 0) {
            $('#user_manager_ctrl').addClass('inactive').removeClass('active');
            $('#user_manager_ctrl').attr('original-title',jsVars.textInactiveManagerCtrl);
        }

        return false;
    });

    $(document).on('click', '.accept-input', function(e) {
        e.stopPropagation();
        $(this).removeClass('forgotten-link');

        var parent = $(this).parent();
        var input_val = parent.find('input.trigger-new-option').val();
        var select = parent.find('select');
        if ($.trim(input_val) != '') {
            select.find('option.new-option').hide().remove();//remove old new options added at the select
            select.append('<option class="new-option" value="' + input_val + '">' + input_val + '</option>');
            select.find('option:eq(1)').text(select.find('option:eq(1)').attr('rel'));//Text editar            
        }

        parent.find('.chosen-container').show();
        select.val(input_val).trigger("chosen:updated");
        if (select.attr('id') == 'empresa') {
            $('#empresa_txt').val(input_val);
        }

        parent.find('input.trigger-new-option').hide().remove();//hide input nuevo
        parent.find('.action-input').hide().remove();//hide action inputs

        if (select.attr('id') == 'empresa') {
            $('#user_region').attr('disabled',true).trigger("chosen:updated");
            $('#ciudad,#direccion,#path_logo_empresa,#logo_empresa input[type="file"]').removeAttr('disabled');
            $('#logo_empresa').css('background-image', $('#logo_empresa').attr('rel'));
        }

        if ($('.accept-input').size() <= 0) {
            $('#save_user').loadingButton('enabled');//Habilitamos el boton de guardar
            $('#save_user_nouser').loadingButton('enabled');
        }

        return false;
    });

    $('#pass_gen').passgenerator({
        displayOn: '#password'
    });
    //$('#pass_gen').trigger('click');

    $('#user_manager_ctrl').click(function(e) {
        e.stopPropagation();
        if (!$(this).hasClass('inactive')) {
            $(this).toggleClass('active');
        }
        return false;
    });
    $('#user_manager_ctrl').tipsy({gravity: 's', fade: true});

    $('#logo_empresa').contiUploader({
        icon: jsVars.site + 'img/img_company_off.png',
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');

            $('#aux-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-empresa',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function(btn) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_empresa').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#aux-dialog').dialog('close');
                }
            });


                $.conti.dialog({
                    wrapper: '#aux-dialog',
                    style: 'user-dialog',
                    modal: true,
                    width: 920
                });

        }
    });

    $('#logo_usuario').contiUploader({
        icon: jsVars.site + 'img/img_user_off.png',
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 240,
        height: 190,
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-logo-usuario',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 240, h: 190},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_logo_usuario').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'user-dialog',
                modal: true,
                width: 920
            });
        }
    });


    $('#slide_form a').click(function(){
       if($('#slide_form a').index($(this)) == 1){
           $('#save_user_nouser').hide();
       }else{
           $('#save_user_nouser').show();
       }
    })
    $('#save_user_nouser').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        time: 0.01,
        onClick: function(e, btn) {
            e.stopPropagation();
            var isManager = $('#user_manager_ctrl').hasClass('active') && $('#user_manager_ctrl').is(':visible') ? 1 : 0;
            var country = $("#country_user option:selected").attr('rel');
            if ($("#add_user_form").validationEngine('validate')) {//if ok current section, go to other section
                $.ajax({
                    type: 'post',
                    url: $("#add_user_form").attr('action'),
                    data: {formdata: $("#add_user_form").serialize(), country_id: country, manager: isManager, nousers: 1},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                updated_colaborator = true;
                                $('#dialog_wrap').dialog('close');
                            } else {
                                if (response.code == 1) {
                                    var other_section = $('.active_section').siblings();
                                    other_section = other_section.attr('id');
                                    $('a[rel="' + other_section + '"]').trigger('click');
                                }
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
    $('#save_user').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        time: 0.01,
        onClick: function(e, btn) {
            e.stopPropagation();
            var isManager = $('#user_manager_ctrl').hasClass('active') && $('#user_manager_ctrl').is(':visible') ? 1 : 0;
            var country = $("#country_user option:selected").attr('rel');
            if ($("#add_user_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#add_user_form").attr('action'),
                    data: {formdata: $("#add_user_form").serialize(), country_id: country, manager: isManager},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                updated_colaborator = true;
                                $('#dialog_wrap').dialog('close');
                            } else {
                                if (response.code == 1) {
                                    var other_section = $('.active_section').siblings();
                                    other_section = other_section.attr('id');
                                    $('a[rel="' + other_section + '"]').trigger('click');
                                }
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
    
    $('#tutorial_toggle').addClass('fulltop');
    displayTutorialOnDialog(14);
    /*
    if($().smallipop){   
        
        if($('.smallipop-theme-dialog-instance').size()<=0){
            
            $('#add_user_form .conti-tour').smallipop({
               theme: 'black dialog-instance',
               triggerOnClick: true, 
               onTourClose: function(){
                   console.log('finish tour');
                   //$('.conti-tour').smallipop('destroy');
               },
               cssAnimations: {
                 enabled: true,
                 show: 'animated fadeIn',
                 hide: 'animated fadeOut'
               }
             });
             $('#add_user_form .conti-tour').smallipop('tour');
         }else{
             $('#add_user_form .conti-tour').smallipop('show');
         }
    }*/
    
}

function callOpenNews() {

    new nicEditor({uploadURI: jsVars.uploadNicEditAx, iconsPath: jsVars.site + 'img/wysiwyg.png', minHeight: 177, maxHeight: 177, buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'link', 'unlink']}).panelInstance('desc_news_es');
    new nicEditor({uploadURI: jsVars.uploadNicEditAx, iconsPath: jsVars.site + 'img/wysiwyg.png', minHeight: 177, maxHeight: 177, buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'link', 'unlink']}).panelInstance('desc_news_en');
    fix_style_wysiwyg();
    $('#image-news-portada').tipsy({gravity: 's', fade: true, offset: -50});

    $(":input[placeholder]").placeholder();

    $("#add_news_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    var image_portada = $('#reference_bg_uploader').size() > 0 ? $('#reference_bg_uploader').val() : 'img/icon_portada_off.png';
    $('#image-news-portada').contiUploader({
        icon: jsVars.site + image_portada,
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 242,
        height: 222,
        sizeImg: {w: 175, h: 175},
        pathUpload: 'uploads/tmpnews/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#aux-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-cover-news',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                watermarkLogo: 'img/logo_contiplus.png',
                cropSize: {w: 800, h: 230},
                onSaveCrop: function(btn) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    $('#path_img_portada').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#aux-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#aux-dialog',
                style: 'news-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $('#post_btn').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            var newsEditorEs = nicEditors.findEditor('desc_news_es').getContent();
            var newsEditorEn = nicEditors.findEditor('desc_news_en').getContent();

            newsEditorEs = $.trim(newsEditorEs.replace(/&nbsp;/g, ' '));
            newsEditorEn = $.trim(newsEditorEn.replace(/&nbsp;/g, ' '));

            var editor = null;
            if ($('#es_form_section').is(':visible') && is_empty_editor(newsEditorEs)) {
                editor = $('#es_form_section').find('.ps-container');
            } else if ($('#en_form_section').is(':visible') && is_empty_editor(newsEditorEn)) {
                editor = $('#en_form_section').find('.ps-container');
            }

            if (editor !== null) {
                editor.attr('id', 'tmp' + $.now());
                editor.validationEngine('showPrompt', jsVars.fieldRequiredMsg, 'error', "centerRigth", true);
                $('#post_btn').loadingButton('stop', -1);
            }

            if ($("#add_news_form").validationEngine('validate') && editor === null) {
                var container_form = btn.parents('.ui-dialog');
                container_form.cocoblock();

                $.ajax({
                    type: 'post',
                    url: $("#add_news_form").attr('action'),
                    data: {
                        formdata: $("#add_news_form").serialize(),
                        wysiwyg_es: newsEditorEs,
                        wysiwyg_en: newsEditorEn
                    },
                    success: function(response) {
                        container_form.cocoblock('unblock');
                        var result = $.parseJSON(response);
                        $.coconotif.add({
                            text: result.msg,
                            time: jsVars.timeNotifications
                        });

                        if (result.success) {
                            btn.loadingButton('stop', 1);
                            $('#last_insert_new').val(result.inserted_new);
                            $('#path_img_portada').val(result.path_img_portada);

                            processed_operation = true;
                        } else {
                            $.conti.alert({msg: result.msg});
                            $('#post_btn').loadingButton('stop', -1);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        errorAjax(xhr, ajaxOptions, thrownError);
                    }
                });


            } else {
                btn.loadingButton('stop', -1);
            }
        }
    });
}

function initEventsCalculator(calculador) {
    //Atach validator events form
    $("form#tension_op_unitaria_form").validationEngine({
        showOneMessage: true,
        focusFirstField: false,
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        scroll: false,
        validationEventTrigger: '',
        promptPosition: "topRight",
        prettySelect: true,
        useSuffix: '_chosen'
    });
    $("#sel_angulo_contacto,#sel_tipo_tensor,#sel_tipo_polea").chosen({search_contains: true});

    $('#sel_angulo_contacto').on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $('#sel_angulo_contacto').on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });

    $('#sel_angulo_contacto').on('change', function(evt, params) {
        var angle = $(this).val();
        if (parseInt(angle) > 420) {
            $("select#sel_tipo_tensor").val($("select#sel_tipo_tensor option:last-child").attr('value'));
            $('#sel_tipo_tensor').attr('disabled', true).trigger("chosen:updated");
        } else {
            $("select#sel_tipo_tensor").val('');
            $('#sel_tipo_tensor').removeAttr('disabled').trigger("chosen:updated");
        }
    });

    $('#reset_calc').click(function(e) {
        e.stopPropagation();
        $("select#sel_tipo_tensor option:last-child").removeAttr("selected");
        $('#tension_op_unitaria_form').reset();
        $('#sel_angulo_contacto,#sel_tipo_tensor,#sel_tipo_polea').removeAttr('disabled').trigger("chosen:updated");
        $('#result_calc').html('0.00');
        return false;
    });

    $('#process_calc').click(function(e) {
        e.stopPropagation();

        if ($('#tension_op_unitaria_form').validationEngine('validate')) {
            var P = $('#potencia_motor').val();
            var W = $('#ancho_banda').val();
            var S = $('#velocidad_banda').val();

            var angulo = $('#sel_angulo_contacto').val();
            var tensor = $('#sel_tipo_tensor').val();
            tensor = tensor == 'Manual' ? 'Tornillo' : tensor;
            tensor = tensor == 'Automatic' ? 'Gravedad' : tensor;

            var polea = $('#sel_tipo_polea').val();
            polea = polea == 'Bare' ? 'Lisa' : polea;
            polea = polea == 'Lined' ? 'Recubierta' : polea;

            var K = jsVars.tabla_k[angulo][tensor][polea]
            var TE = (0.9 * P * 33000) / S;
            var T2 = K * TE;
            var T1 = parseFloat(TE) + parseFloat(T2);

            var TU = T1 / W;
            TU = TU.toFixed(0);

            var nMM = parseFloat(TU * 1.75);
            nMM.toFixed(2);

            $('#result_calc').html(TU + '<span>PIW</span>&nbsp;&nbsp;&nbsp;' + nMM + '<span>N/mm</span>');
        }
        return false;
    });
}


function callOpenUProfile() {
    var original_hash = $('#info_profile_form').formhash();
    $('#profile-company,#profile-picture').tipsy({gravity: 's', fade: true});

    $('.inline-edit-txt').focus(function() {
    });
    $('.inline-edit-txt').blur(function() {
        if ($.trim($(this).val()) == '') {
            $(this).css('background', 'none');
        } else {
            $(this).css('background', '#f0f5eb');
        }
    });

    $('#info_profile_form').keyup(function(e) {
        if ($('#info_profile_form').formhash() != original_hash) {
            $('#save_own_profile').loadingButton('enabled');//Habilitamos el boton de guardar
        } else {
            $('#save_own_profile').loadingButton('disabled');//Deshabilitamos el boton de guardar
        }
    });

    $('#profile-company').contiUploader({
        icon: jsVars.site + $('#path_logo_empresa').val(),
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 175,
        height: 175,
        sizeImg: {w: 175, h: 175},
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({fixPosition: true, message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-profile-user',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 175, h: 175},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    $('#logo_company_hidden').val(cropdata.relative_path);
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'profile-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $('#profile-picture').contiUploader({
        icon: jsVars.site + $('#path_pic_profile').val(),
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 175,
        height: 175,
        sizeImg: {w: 175, h: 175},
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({fixPosition: true, message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#aux-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-profile-user',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 175, h: 175},
                onSaveCrop: function(btn) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#aux-dialog').parents('.ui-dialog');
                    $('#profile_picture_hidden').val(cropdata.relative_path);
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    container_form.cocoblock('unblock');
                    $('#aux-dialog').dialog('close');
                    $('#save_own_profile').loadingButton('enabled');//Habilitamos el boton de guardar
                }
            });

            $.conti.dialog({
                wrapper: '#aux-dialog',
                style: 'profile-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $("#info_profile_form").validationEngine({
        validationEventTrigger: '',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    $('#save_own_profile').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#info_profile_form").validationEngine('validate')) {
                $.ajax({
                    type: 'post',
                    url: $("#info_profile_form").attr('action'),
                    data: {
                        formdata: $("#info_profile_form").serialize()
                    },
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('stop', 1);
                            updated_colaborator = true;
                        } else {
                            if (response.code == 1) {
                                var other_section = $('.active_section').siblings();
                                other_section = other_section.attr('id');
                                $('a[rel="' + other_section + '"]').trigger('click');
                            }
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


function initEventsCompanyProfile() {
    var original_hash = $('#company_profile_form').formhash();
    $('#profile-company').tipsy({gravity: 's', fade: true});

    $('.inline-edit-txt').focus(function() {
    });
    $('.inline-edit-txt').blur(function() {
        if ($.trim($(this).val()) == '') {
            $(this).css('background', 'none');
        } else {
            $(this).css('background', '#f0f5eb');
        }
    });

    $('#company_profile_form').keyup(function(e) {
        if ($('#company_profile_form').formhash() != original_hash) {
            $('#save_company_profile').loadingButton('enabled');//Habilitamos el boton de guardar
        } else {
            $('#save_company_profile').loadingButton('disabled');//Deshabilitamos el boton de guardar
        }
    });

    $('#profile-company').contiUploader({
        icon: jsVars.site + $('#path_logo_empresa').val(),
        uploadUrl: jsVars.uploadGenericImgAx,
        width: 175,
        height: 175,
        sizeImg: {w: 175, h: 175},
        pathUpload: 'uploads/tmpfiles/',
        onUploadProgress: function(obj, progress) {
            obj.find('.cocoblocker .blockMsg').html(progress);
        },
        onProcess: function(obj) {
            obj.cocoblock({fixPosition: true, message: '0%'});
        },
        onCompleteUpload: function(response, obj) {
            obj.cocoblock('unblock');
            obj.tipsy("hide");

            $('#temp-dialog').cococropper({
                previewImg: response.link,
                sourceImg: response.relative_path,
                previewDataImg: response.image.data,
                classPreviewImg: 'preview-profile-user',
                submitLabel: jsVars.cropper.saveBtn,
                submitClass: 'contiButton',
                cropUrl: jsVars.getCroppedImgAx,
                cropSize: {w: 175, h: 175},
                onSaveCrop: function(btn) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    container_form.cocoblock();
                },
                onCompleteCrop: function(cropdata) {
                    var container_form = $('#temp-dialog').parents('.ui-dialog');
                    $('#logo_company_hidden').val(cropdata.relative_path);
                    obj.css({backgroundImage: 'url(' + cropdata.link + ')'});
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
                    $('#save_company_profile').loadingButton('enabled');//Habilitamos el boton de guardar
                }
            });

            $.conti.dialog({
                wrapper: '#temp-dialog',
                style: 'profile-dialog',
                modal: true,
                width: 920
            });
        }
    });

    $("#company_profile_form").validationEngine({
        validationEventTrigger: '',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    $('#save_company_profile').loadingButton({
        disabled: true,
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#company_profile_form").validationEngine('validate')) {
                $.ajax({
                    type: 'post',
                    url: $("#company_profile_form").attr('action'),
                    data: {
                        formdata: $("#company_profile_form").serialize()
                    },
                    success: function(response) {
                        response = $.parseJSON(response);
                        $.coconotif.add({
                            text: response.msg,
                            time: jsVars.timeNotifications
                        });

                        if (response.success) {
                            btn.loadingButton('stop', 1);
                            saved_company = true;
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


function processChangeConveyorStatus(encriptedUrlProcess, status, function_update_events) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {new_status: status},
        success: function(response) {
            $('body').cocoblock('unblock');
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });

            if (response.success) {
                saved_conveyor = true;
                number_saved_conveyor = response.conveyor_number;
                window[function_update_events].apply(null, []);//call generic function update events
                //callUpdateConveyorsDataTable();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processChangeTrackingConveyorStatus(encriptedUrlProcess, status, function_update_events) {
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {new_status: status},
        success: function(response) {
            $('body').cocoblock('unblock');
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });

            if (response.success) {
                saved_tracking_conveyor = true;
                number_saved_tracking_conveyor = response.conveyor_number;
                window[function_update_events].apply(null, []);//call generic function update events
                //callUpdateConveyorsDataTable();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function processRemoveNews(encriptedUrlProcess, invoker) {
    var item = invoker.parents('.row-news');
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            $('body').cocoblock('unblock');
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });
            if (response.success) {
                item.fadeOut('slow', function() {
                    item.remove();
                });
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });

}

function processRemoveItem(encriptedUrlProcess, invoker) {
    var item = invoker.parents('.item-dashboard');
    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            $('body').cocoblock('unblock');
            response = $.parseJSON(response);

            if (response.success) {
                if (item.size() > 0) {
                    $.coconotif.add({
                        text: response.msg,
                        time: jsVars.timeNotifications
                    });
                    item.fadeOut('slow', function() {
                        item.remove();
                    });
                } else {
                    $('body').cocoblock();
                    $.cookie("conti_notification", response.msg, {path: '/'}); // Sample 1
                    window.location = invoker.attr('referer');
                    //window.history.back();//Go back
                }
            } else {
                $.coconotif.add({
                    text: response.msg,
                    time: jsVars.timeNotifications
                });
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });

}

function processEditItem(encriptedUrlProcess, invoker) {
    var item = invoker.parents('.item-dashboard');

    $('body').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            $('body').cocoblock('unblock');
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });
            if (response.success) {
                item.fadeOut('slow', function() {
                    item.remove();
                });
                //saved_conveyor = true;
                //window[function_update_events].apply(null, []);//call generic function update events
                //callUpdateConveyorsDataTable();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });

}

function processRemoveNotification(encriptedUrlProcess, invoker) {
    var item = invoker.parents('.notification-row');
    $('#panel_notifications').cocoblock();
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            $('#panel_notifications').cocoblock('unblock');
            try {
                response = $.parseJSON(response);
                $.coconotif.add({
                    text: response.msg,
                    time: jsVars.timeNotifications
                });

                if (response.success) {
                    item.fadeOut('slow', function() {
                        item.remove();
                    });

                }
            } catch (e) {

                $.coconotif.add({
                    text: e,
                    time: jsVars.timeNotifications
                });
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('#panel_notifications').cocoblock('unblock');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });

}

function initEventsAddItemConveyor() {
    $('#folder_title').parents('form').css('height', '100px');
    $(":input[placeholder]").placeholder();
    
    if($('.folder-year').size()>0){
        $("#folder_title").chosen({search_contains: true});
        $("#folder_title").on('chosen:showing_dropdown', function(evt, params) {
            $('.chosen-results').perfectScrollbar({suppressScrollX: true});
        });
        $("#folder_title").on('chosen:hiding_dropdown', function(evt, params) {
            $('.chosen-results').perfectScrollbar('destroy');
        });
    }

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
                    $('#path_item').val(cropdata.relative_path);
                    container_form.cocoblock('unblock');
                    $('#temp-dialog').dialog('close');
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
            obj.css({backgroundImage: 'url(' + response.thumbvideo + ')'});
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
    

    $('#save_item_conveyor').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();

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
                btn.loadingButton('stop', -1);
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
}


function initEventsEditItemconveyor() {
    $('#rep_fol_title').parents('form').css('width', '480px');
    $('#rep_fol_title').parents('form').css('height', '100px');
    $(":input[placeholder]").placeholder();

    if ($('#wysiwyg_description').size() > 0) {
        $('#wysiwyg_description').parents('form').css('height', '410px');
        $('#wysiwyg_description').parents('form').css('width', '600px');
        new nicEditor({uploadURI: jsVars.uploadNicEditReportAx, iconsPath: jsVars.site + 'img/wysiwyg.png', minHeight: 220, maxHeight: 220, buttonList: ['bold', 'italic', 'underline', 'left', 'center', 'right', 'justify', 'upload', 'link', 'unlink']}).panelInstance('wysiwyg_description');
        fix_style_wysiwyg();
    }
    
    if($('#item_taken_at').size()>0){
        $.datepicker.setDefaults({
           showOtherMonths: true,
           selectOtherMonths: true
       }, $.datepicker.regional[ "" ]);
       $('#item_taken_at').datepicker($.datepicker.regional[ jsVars.systemLanguage]);
       //$('#item_taken_at').tipsy({gravity: 's', fade: true, offset: -30});
       $('#item_taken_at').tipsy({gravity: 'e', fade: true});
       
       $('#item_taken_at').click(function(e){
           e.stopPropagation();
           $('#item_taken_at').tipsy("hide");
           return false;
       })
       //$("#item_taken_at").datepicker("setDate", $("#item_taken_at").val());
   }
   
     $("#dialog_wrap").dialog("option", "position", {my: "center", at: "top + 300", of: window});

    $("#edit_item_conveyor_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });

    $('#update_item_conveyor').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();

            var editorError = null;
            if ($('#wysiwyg_description').size() > 0) {
                var editorText = nicEditors.findEditor('wysiwyg_description').getContent();
                $('#item_description').val(editorText);
                editorText = $.trim(editorText.replace(/&nbsp;/g, ' '));
                if (is_empty_editor(editorText)) {
                    editorError = $('#edit_item_conveyor_form').find('.ps-container');
                }
            }

            $('#item_description').val($('#item_description').val() == '' ? 'required-field' : $('#item_description').val());

            if (editorError !== null) {
                editorError.attr('id', 'tmp' + $.now());
                editorError.validationEngine('showPrompt', jsVars.fieldRequiredMsg, 'error', "centerRigth", true);
                btn.loadingButton('stop', -1);
            }

            if ($("#edit_item_conveyor_form").validationEngine('validate') && editorError === null) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#edit_item_conveyor_form").attr('action'),
                    data: {formdata: $("#edit_item_conveyor_form").serialize()},
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


function callAddNotification(dist_id, client_id) {

    dist_id = typeof (dist_id) !== 'undefined' ? dist_id : 0;
    client_id = typeof (client_id) !== 'undefined' ? client_id : 0;

    dist_id = typeof (jsVars.distCompanyId) !== 'undefined' ? jsVars.distCompanyId : dist_id;
    client_id = typeof (jsVars.clientCompanyId) !== 'undefined' ? jsVars.clientCompanyId : client_id;

    //$('#logo_transportador').tipsy({gravity: 's', fade: true, offset: -30});
    //$('#slide_form a').tipsy({gravity: 's', fade: true});
    $(":input[placeholder]").placeholder();

    $("#add_notification_form").validationEngine({
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $("#add_notification_form select").chosen({search_contains: true});

    $("#add_notification_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#add_notification_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });

    //if (dist_id > 0 && client_id > 0) {
    if (dist_id > 0) {
        $('#distributor').val(dist_id).attr('disabled', true).trigger("chosen:updated");
        changeDistributorConveyor(client_id);
    }
    $('#distributor').change(function() {
        changeDistributorConveyor(client_id);
    });

    $('#client').change(function() {
        $(this).prev().val($(this).val());
    });

    $('#send_copy_email').click(function(e) {
        e.stopPropagation();
        if ($('#wrapper_mails input').size() < 3) {
            $('#wrapper_mails').append('<div class="full-controls"><input type="text" placeholder="mail@mail.com" class="validate[custom[email]]"/><a class="input-ctrl btn-cancel cancel-link"></a></div>');
        }
        return false;
    });

    $('textarea[name="notification"]').limitCharsTxt(300);

    $('#wrapper_mails').on('click', '.cancel-link', function(e) {
        e.stopPropagation();
        var parent = $(this).parent();
        parent.fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });

    $.datepicker.setDefaults({
        showOtherMonths: true,
        selectOtherMonths: true,
        dateFormat: 'yy-mm-dd'
    }, $.datepicker.regional[ "" ]);

    $('input[name="fecha"]').datepicker($.datepicker.regional[ jsVars.systemLanguage]);

    $("#open_date_link").click(function(e) {
        e.stopPropagation();
        $('input[name="fecha"]').datepicker("show");
        return false;
    });

    $('input[name="hora"]').clockpicker({autoclose: true, placement: 'bottom'});

    $('#save_notification').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            var mails = '';
            $.each($('#wrapper_mails div input'), function(key, mail_ctrl) {
                mails += $(mail_ctrl).val() + ',';
            });

            if ($("#add_notification_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#add_notification_form").attr('action'),
                    data: {formdata: $("#add_notification_form").serialize(), send_mails: mails},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                $('#dialog_wrap').dialog('close');
                            } else {
                                btn.loadingButton('stop', -1);
                            }
                        } catch (e) {
                            $.coconotif.add({
                                text: e,
                                time: jsVars.timeNotifications
                            });
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


function initEventsPanelNotifications() {
    $('.conti-list-actions a').tipsy({gravity: 's', fade: true});
    $('#notifications_wrapper #notifications_container').perfectScrollbar({suppressScrollX: true});
    updateSizePanelNotifications();
}

function callContactEvents() {
    $(":input[placeholder]").placeholder();

    $("#contact_form").validationEngine({
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation,
        prettySelect: true,
        useSuffix: '_chosen'
    });

    $("#contact_form select").chosen({search_contains: true});

    $("#contact_form select").on('chosen:showing_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar({suppressScrollX: true});
    });
    $("#contact_form select").on('chosen:hiding_dropdown', function(evt, params) {
        $('.chosen-results').perfectScrollbar('destroy');
    });

    $('#area_dirigido').change(function() {
        $('#region_contacto').attr('disabled', true).trigger("chosen:updated");
        $.post(jsVars.getRegionsContactByArea, {area: $('#area_dirigido').val()}, function(regions) {
            $('#region_contacto').html(regions);
            $('#region_contacto').removeAttr('disabled');
            $('#region_contacto').trigger("chosen:updated");
        });
    });

    $('#send_mail').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#contact_form").validationEngine('validate')) {//if ok current section, go to other section                                
                $.ajax({
                    type: 'post',
                    url: $("#contact_form").attr('action'),
                    data: {formdata: $("#contact_form").serialize()},
                    success: function(response) {
                        try {
                            response = $.parseJSON(response);
                            $.coconotif.add({
                                text: response.msg,
                                time: jsVars.timeNotifications
                            });

                            if (response.success) {
                                btn.loadingButton('stop', 1);
                                $('#dialog_wrap').dialog('close');
                            } else {
                                btn.loadingButton('stop', -1);
                            }
                        } catch (e) {
                            $.coconotif.add({
                                text: e,
                                time: jsVars.timeNotifications
                            });
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

function initEventsTracking() {
    var trackingCode = $('#conveyor_track_data').attr('rel');
    $('#conveyor_track_data').spotLiveWidget({
        feedId: trackingCode,
        width: 600,
        height: 500
    });
}


function checkBehaviorFilter(){
     var tot_items = $('#filter_list ul li:not(.caption-item)').size();
     var tot_items_active = $('#filter_list ul li.active').size();
     if (tot_items == tot_items_active) {
            $('.filter-selector-all').removeClass('unselected').addClass('selected');
            $('#set_filters').loadingButton('enabled');//Habilitamos el boton de guardar
        } else {
            $('.filter-selector-all').removeClass('selected').addClass('unselected');
            if(tot_items_active>0){
                $('#set_filters').loadingButton('enabled');//Habilitamos el boton de guardar
            }else{
                $('#set_filters').loadingButton('disabled');//Deshabilitamos el boton de guardar
            }
        }
}
function initEventsClientsManager() {
    $('#filter_list > div').perfectScrollbar({suppressScrollX: true});
    $('#filter_list ul li').tipsy({gravity: 'w', fade: true});
    
    if(filter_string!=''){
        $('#filter_list ul li:not(.caption-item)').removeClass('active');//Desactivamos los items del filtro    
        var item_selector = '';
        var filter_string_data = filter_string.split(',');
        $.each(filter_string_data, function(index, filter){
            item_selector += '#filter_list li[id="'+filter+'"],';            
        }); 
        item_selector = item_selector=='' ? '' : item_selector.slice(0,-1);
        $(item_selector).addClass('active');
        checkBehaviorFilter();
    }

    $('.filter-selector-all').click(function(e) {
        e.stopPropagation();
        if ($(this).hasClass('unselected')) {
            $('#filter_list ul li:not(.caption-item)').attr('class', 'active');//Activamos los items del filtro            
        } else {            
            $('#filter_list ul li:not(.caption-item)').removeClass('active');//Desactivamos los items del filtro          
        }
        checkBehaviorFilter();
        return false;
    });

    $('#filter_list').on('click', 'ul li:not(.caption-item)', function(e) {
        e.stopPropagation();
        $(this).toggleClass('active');
        checkBehaviorFilter();
        return false;
    });
    
    $('#filter_list').on('click', 'ul li.caption-item', function(e) {
        e.stopPropagation();
        var caption = $(this);
        var rel_items = $(this).attr('id');            
        if(!caption.hasClass('clicked-caption')){
            $('#filter_list li[rel="'+rel_items+'"').removeClass('active');
            caption.addClass('clicked-caption');
        }else{            
            $('#filter_list li[rel="'+rel_items+'"').addClass('active');
            caption.removeClass('clicked-caption');
        }
        checkBehaviorFilter();
        return false;
    });
    
    $('#set_filters').loadingButton({
        class_disabled: 'disabled-btn',
        onClick: function(e, btn) {
            e.stopPropagation();      
            filter_string = '';
            $('#filter_list li.active').each(function(){
                filter_string += $(this).attr('id') + ',';
            });
            filter_string = filter_string != '' ? filter_string.slice(0, -1) : filter_string;
            btn.loadingButton('stop', 1);
            $('#dialog_wrap').dialog('close');            
            return false;
        }
    });
}

function getSelectedClientsManager() {    
    if(filter_string != ''){
        var item_selector = '';
        var filter_string_data = filter_string.split(',');
        $('.dashboard-list li:not(.add-conveyor-link)').hide();        
        $.each(filter_string_data, function(index, filter){
            item_selector += 'li[alt="cid-'+filter+'"],';            
        }); 
        item_selector = item_selector=='' ? '' : item_selector.slice(0,-1);
        $(item_selector).show();
    }
}

function getSelectedClientsManagerClients(){
    if(filter_string != ''){
        var item_selector = '';
        var filter_string_data = filter_string.split(',');
        $('.dashboard-list li:not(.add-client-link)').hide();        
        $.each(filter_string_data, function(index, filter){
            item_selector += 'li[alt="cid-'+filter+'"],';            
        }); 
        item_selector = item_selector=='' ? '' : item_selector.slice(0,-1);
        $(item_selector).show();
    }
}