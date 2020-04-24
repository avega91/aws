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

    initItemsConveyorDatatableEvents();

    //Solo si es un folder
    if(typeof(jsVars.refreshItemsFolderAx)!="undefined"){
        searchItemsFolderData(); 
        
        $('#generic_sort ul a').click(function(e) {
            e.stopPropagation();
            $('#generic_sort > a > span').attr('rel', $(this).attr('rel'));
            $('#generic_sort > a > span').html($(this).html());
            searchItemsFolderData();
            return false;
        });
    }else{//es un folder con datatable de archives
        createDatatable();
    }
    
    $("#type_filter_chosen").chosen({
            disable_search: true,
            width: "100%"
        });
        
    $("#type_filter_chosen").on('change', function(evt, params) {
            var filter = $(this).val();
            $('#items_folder_wrapper ul li.item-dashboard:not(.smart-item)').hide();
            var item_selector = '';
            if(filter !== null){
                $.each(filter, function(index, item){
                    item_selector += '.'+item+'-item,';            
                });        
            }
            item_selector = item_selector=='' ? '.item-dashboard' : item_selector.slice(0,-1);
            $(item_selector).show();
        });

        $(document).on('click','.favorite-row', function(e) {
            e.stopPropagation();
            var $link = $(this);
            var urlPet = $link.data('url');
            $link.addClass('is-processing');
            $.ajax({
                type: 'post',
                url: urlPet,
                data: {},
                success: function(response) {
                    try {
                        //if no parse JSON is HTML
                        response = $.parseJSON(response);
                        if(response.success){
                            $('#archives_table').DataTable().ajax.reload();
                        }
                    } catch (e) {
                        $link.removeClass('is-processing');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $link.removeClass('is-processing');
                }
            });
            return false;
        });

        $(document).on('dblclick', 'td.cell-docname', function(e){
            $cell = $(this);
            if($cell.find('input').size()>0){
                $('tr td.active').removeClass('active');
                $cell.addClass('active');
                $cell.find('input').focus();
            }
            
        });

        $(document).on('keyup', '.text-filename', function(e){
            if (e.keyCode == 27) {
                $('tr td.active').removeClass('active');
             }
        });
        $(document).on('keypress', '.text-filename', function(e){
            if(e.which == 13) {
                $input = $(this);
                var urlPet = $input.data('update');
                $('#archives_table').cocoblock();
                $.ajax({
                    type: 'post',
                    url: urlPet,
                    data: {docname: $input.val()},
                    success: function(response) {
                        $('#archives_table').cocoblock('unblock');
                        try {
                            //if no parse JSON is HTML
                            response = $.parseJSON(response);
                            if(response.success){
                                $('#archives_table').DataTable().ajax.reload();
                            }
                        } catch (e) {
                            
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        $('#archives_table').cocoblock('unblock');
                    }
                });
            }
        });

        /*
    $(document).on('click','.add-mediaitem-conveyor-link', function() {
        var pet = $(this).attr('rel');
        var typeItem = $(this).attr('alt');
        var dialogStyle = $(this).attr('dialog-style');
        $('body').cocoblock({spinner: false});
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
                    $.conti.dialog({html: response, style: dialogStyle, modal: true, callbackOpen: 'initEventsAddFileToFolder', callbackClose: 'updateItemsIfRequired'});
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    });*/

    $(document).on('click', '.add_asset_folder', function(e) {
        e.stopPropagation();
        var $link = $(this);
        var linkPetition = $link.attr('rel');
        var folderName = $link.data('name');
        var folderType = $link.data('type');
        $('body').cocoblock();
        $.ajax({
            type: 'post',
            url: linkPetition,
            data: {folderName: folderName, folderType: folderType},
            success: function(response) {
                $('body').cocoblock('unblock');
                try {
                    response = $.parseJSON(response);
                    if(response.success){
                        /*var option = $link.closest('li');
                        var asset_links = option.siblings().size();//Opciones restantes
                        if(asset_links <= 0) {
                            var mainOption = option.parent().parent();//find the parent of current link
                            mainOption.hide().remove();
                        }
                        option.hide().remove();*/
                        searchItemsFolderData();
                    }
                } catch (e) {
                    
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('body').cocoblock('unblock');
            }
        });
        return false;
    });

    $(document).on('click', '.delete-file-link', function(e) {
        e.stopPropagation();
        var linkPetition = $(this).attr('rel');
        var confirmation_msg = $(this).attr('conf-msg');
        $.conti.confirm({msg: confirmation_msg, confirmBtnText: jsVars.dialogs.confirmDeletion.btnOk, type: 'delete-dialog', callbackOk: 'processRemoveFile', paramsOk: {petition: linkPetition, invoker: $(this)}});
        return false;
    });

    $(document).on('click','#download-selected-rows', function(e) {
        e.stopPropagation();
        processBulkDownload();
        //$.conti.confirm({msg: jsVars.dialogs.confirmDeletion.description, confirmBtnText: jsVars.dialogs.confirmDeletion.btnOk, type: 'delete-dialog', callbackOk: 'processBulkDeletion', paramsOk: {}});
        return false;
    });

    $(document).on('click','#delete-selected-rows', function(e) {
        e.stopPropagation();
        $.conti.confirm({msg: jsVars.dialogs.confirmDeletion.description, confirmBtnText: jsVars.dialogs.confirmDeletion.btnOk, type: 'delete-dialog', callbackOk: 'processBulkDeletion', paramsOk: {}});
        return false;
    });

    $(document).on('click','.checkbox-row', function(e) {
        toggleCheckboxRow($(this));
    });

    $(document).on('click','#select-all-rows', function(e) {
        var checked = false;
        if($(this).is(':checked')){
            checked = true;
        }

        $('.checkbox-row').each(function(index, item){
            $(item).prop('checked', checked);
            toggleCheckboxRow($(item));
        });
    });
});

function processBulkDownload(){
    var items = [];
    $('.checkbox-row').each(function(index, item){
        if($(item).is(':checked')){
            items.push({ signature: $(item).data('signature'), digest: $(item).data('digest')});
        }
    });

    console.log(items);

    if(items.length > 0){
        $('#archives_table').cocoblock();
        $.ajax({
            type: 'post',
            url: jsVars.downloadFilesAx,
            data: {fileItems: items},
            success: function(response) {
                $('#archives_table').cocoblock('unblock');
                try {
                    response = $.parseJSON(response);
                    if(response.success){
                        $('#archives_table').DataTable().ajax.reload();
                        //Cancelar opcion seleccionar todo, y como se recargan la tabla, esconder el boton eliminar
                        $('#select-all-rows').prop('checked', false);
                        $('#download-selected-rows').fadeOut('slow');
                        $('#delete-selected-rows').fadeOut('slow');
                        window.location.href = response.zip;
                    }
                } catch (e) {
                    
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                $('#archives_table').cocoblock('unblock');
            }
        });
}
}
function processBulkDeletion(){
    var itemsToRemove = [];
    $('.checkbox-row').each(function(index, item){
        if($(item).is(':checked')){
            itemsToRemove.push({ signature: $(item).data('signature'), digest: $(item).data('digest')});
        }
    });

    if(itemsToRemove.length > 0){
            $('#archives_table').cocoblock();
            $.ajax({
                type: 'post',
                url: jsVars.deleteFilesAx,
                data: {filesDelete: itemsToRemove},
                success: function(response) {
                    $('#archives_table').cocoblock('unblock');
                    try {
                        response = $.parseJSON(response);
                        if(response.success){
                            $('#archives_table').DataTable().ajax.reload();
                            //Cancelar opcion seleccionar todo, y como se recargan la tabla, esconder el boton eliminar
                            $('#select-all-rows').prop('checked', false);
                            $('#download-selected-rows').fadeOut('slow');
                            $('#delete-selected-rows').fadeOut('slow');
                        }
                    } catch (e) {
                        
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $('#archives_table').cocoblock('unblock');
                }
            });
    }
}

function toggleCheckboxRow(checkbox){
    if(checkbox.is(':checked')){
        checkbox.addClass('selected-row-file');
    }else{
        checkbox.removeClass('selected-row-file');
    }

    toggleToolbarDatatable();
}

function toggleToolbarDatatable(){
    var totalChecks = $('.checkbox-row').size();
    var selectedRows = $('.checkbox-row.selected-row-file').size();
    if(selectedRows > 0){
        $('#download-selected-rows').fadeIn('slow');
        $('#delete-selected-rows').fadeIn('slow');
    }else{
        $('#download-selected-rows').fadeOut('slow');
        $('#delete-selected-rows').fadeOut('slow');
    }

    if(totalChecks == selectedRows && selectedRows>0){
        $('#select-all-rows').prop('checked', true);
    }else{
        $('#select-all-rows').prop('checked', false);
    }
}

function processRemoveFile(encriptedUrlProcess, invoker) {
    $(invoker).addClass('is-processing');
    $.ajax({
        type: 'post',
        url: encriptedUrlProcess,
        data: {},
        success: function(response) {
            $(invoker).removeClass('is-processing');
            response = $.parseJSON(response);
            $.coconotif.add({
                text: response.msg,
                time: jsVars.timeNotifications
            });

            if (response.success) {
                $('#archives_table').DataTable().ajax.reload();
                //Cancelar opcion seleccionar todo, y como se recargan la tabla, esconder el boton eliminar
                $('#select-all-rows').prop('checked', false);
                $('#download-selected-rows').fadeOut('slow');
                $('#delete-selected-rows').fadeOut('slow');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $(invoker).removeClass('is-processing');
            errorAjax(xhr, ajaxOptions, thrownError);
        }
    });
}

function createDatatable(defaultSearch){
    let targets_styles = [0,1,7];
    var columns = [
        { "data": "action_row", "width": "2%" },
        { "data": "favorite", "width": "2%" },
        { "data": "doc_name", "width": "30%" },
        { "data": "type" },
        { "data": "size" },
        { "data": "uploaded", "width": "20%" },
        { "data": "user_upload", "width": "20%" },
        { "data": "actions", "width": "6%" },
    ];

    if(!jsVars.canDownloadItem && !jsVars.canDeleteItem){
        columns.shift();
        columns.pop();
        targets_styles.shift();
        targets_styles.pop();
    }

    
    var buttonDownload = jsVars.canDownloadItem ? '<button type="button" class="datatable-btn download-all" id="download-selected-rows">Download selected</button>' : '';
    var buttonDelete = jsVars.canDeleteItem ? '<button type="button" class="datatable-btn delete-all" id="delete-selected-rows">Remove selected</button>' : '';

    $('#archives_table').DataTable( {
        //"dom": '<lf<t>ip>',
        dom: '<"toolbar"><lf<t>ip>',
        drawCallback: function(){
            $('#select-all-rows').prop('checked', false);
            $('#download-selected-rows').fadeOut('slow');
            $('#delete-selected-rows').fadeOut('slow');
            $('.tooltiped').tipsy({gravity: 's', fade: true});
        },
        initComplete: function(){
            $("div.toolbar").html(buttonDownload+buttonDelete);     
         },
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": jsVars.refreshArchivesDatatableAx,
            "type": "POST"
        },
        "pageLength": 10,
        "language": {
            search: "_INPUT_",
            searchPlaceholder:  jsVars.datatable.search,
            "zeroRecords": jsVars.datatable.zeroRecords,
            "info": jsVars.datatable.info,
            "infoEmpty": jsVars.datatable.infoEmpty,
            "infoFiltered": jsVars.datatable.infoFiltered,
            "oPaginate": jsVars.datatable.oPaginate
        } ,
        "lengthChange": false,
        "columnDefs": [
            {
                "targets"  : 'no-sort', //Se aplica sobre las columna con class no-sort
                "orderable": false,
                "order": []
            },
            {//Apply class to x column
                targets: targets_styles,
                className: 'dt-body-center'
            },
             {//Apply class to x column
                targets: [2],
                className: 'cell-docname'
            }
        ],
        "columns": columns
    } );
}

function searchItemsFolderData() {
    var sort = $('#generic_sort > a > span').attr('rel');
    var sort_label = $('#generic_sort > a > span').html();

    if(!activeOverlay()){
        $('body').cocoblock();
    }
    
    $("#items_folder_wrapper").load(jsVars.refreshItemsFolderAx + '/' + sort, function(data) {
        $('body').cocoblock('unblock');
        initItemsConveyorDatatableEvents();
    });
}

function initItemsConveyorDatatableEvents() {
    
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
    $('a.preview-item-link').click(function(e) {
        e.stopPropagation();
        return false;
    });

    if ($().lightbox) {
        $('a.preview-item-link').lightbox();
    }
    

    $('.dashboard-list > li:not(.details-item-dashboard):not(.folder-item) > div').draggable({
        revert: "invalid",
        helper: "clone",
        opacity: 0.75,
        zIndex: 100,
        drag: function(event, ui) {
            var item = ui.helper.parent();
            ui.helper.addClass(item.attr('class'));
        }
    });

    $('.dashboard-list > li').tipsy({gravity: 's', fade: true});
    $('.multiple-add a').each(function() {
        $(this).tipsy({gravity: $(this).attr('location-tool'), fade: true});
    });
    $('.actions-item-dashboard a').tipsy({gravity: 's', fade: true});
}

function updateItemsIfRequired() {
    if(typeof(jsVars.refreshItemsFolderAx)!="undefined"){
        if (saved_item) {
            //$('body').cocoblock();
            //location.reload();
            searchItemsFolderData();
        }
    }else{
        $('#archives_table').DataTable().ajax.reload();
    }
    saved_item = false;
}