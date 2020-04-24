/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ultrasonic.js
 *     actions for ultrasonic view
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

;(function($) {

   /* var notes = [{x: "0.1082", y:"0.94778", note:"Dust accumulation", type: 'pending'},
        {x: "0.2659", y:"0.7469", note: 'Poor system maintenance', type: 'pending'},
        {x: "0.5971", y: "0.5900", note: "Belt won't track", type: 'pending'},
        {x: "0.8043", y: "0.3844", note: "Dust accumulation", type: 'fixed'},
        {x: "0.9386", y: "0.1739", note: "Dust accumulation", type: 'pending'}
    ];*/




    $(window).load(function() {

        $("select.assignation-select").chosen({
            disable_search: true,
            width: "100px"
        });

        $(".status-point select").chosen({
            disable_search: true,
            width: "100px"
        });

        $('.input-cost').on("keydown keyup", function() {
            calculateSum();
        });

        $(".status-point select").on('change', function(evt, params) {
            var status = $(this).val();
            var $tr = $(this).closest('tr');
            var $td = $(this).closest('td');
            var trId = $tr.data('id');
            $('div.marker[data-id="'+trId+'"]').removeClass('pending fixed doing').addClass(status);
            $td.removeClass('pending fixed doing').addClass(status);
            //var petition = $(this).attr('rel');
            //$.conti.confirm({type: 'conveyor-dialog', callbackOk: 'processChangeConveyorStatus', paramsOk: {petition: petition, new_status: status, function_update_events: 'callUpdateConveyorsDataTable'}});
        });

        $("select.assignation-select").on('change', function(evt, params) {
            var filter = $(this).val();
            var invoker = $(this).attr('id');
            if(filter=='new'){
                //$("#assigned_to").val(selected_assigned).trigger("chosen:updated");
                $.conti.dialog({html: $('#append_assigned_wrapper').html(), style: 'edit-dialog', modal: true, callbackOpen: 'initEventsAddAssigned', callbackClose:'', paramsOpen: {invoker: invoker}});
            }
        });

        $('#inspection_table tbody tr').hover(function() {
            var trId = $(this).data('id');
            $('div.marker[data-id="'+trId+'"]').addClass('highlighted');
        }, function() {
            $('div.marker.highlighted').removeClass('highlighted');
        });

        var notes = [];
        $.each(inspectionData, function(index, data){
            //console.log(data.PointInspection);
            notes.push({id: data.PointInspection.id, x: data.PointInspection.x, y:data.PointInspection.y, note:data.PointInspection.failure, type: data.PointInspection.status})
        })

        cust_onEdit = function( ev, elem) {
            var $elem = $(elem);
            $('#NoteDialog').remove();
            return $('<div id="NoteDialog"></div>').dialog({
                title: "Note Editor",
                resizable: false,
                modal: true,
                height: "300",
                width: "450",
                position: { my: "left bottom", at: "right top", of: elem},
                buttons: {
                    "Save": function() {
                        var txt = $('textarea', this).val();
                        $elem.data("note").note = txt;
                        $(this).dialog("close");
                    },
                    "Delete": function() {
                        $elem.trigger("remove");
                        $(this).dialog("close");
                    },
                    Cancel: function() {
                        $(this).dialog("close");
                    }
                },
                open: function() {
                    $(this).css("overflow", "hidden");
                    var textarea = $('<textarea id="txt" style="height:100%; width:100%;">');
                    $(this).html(textarea);
                    textarea.val($elem.data("note").note);
                }
            });
        };

        var $img = $("#image").imgNotes({ onEdit: cust_onEdit });

        var widg = $img.data().wgmImgNotes;
        widg.zimg.on('contextmenu', function(ev) {
            ev.preventDefault();
            widg.options.onClick.call(widg, ev);
        });
        widg.zimg.off('tap');

        $img.imgNotes("import", notes);

        var $toggle = $("#toggleEdit");
        if ($img.imgNotes("option","canEdit")) {
            $toggle.text("View");
        } else {
            $toggle.text("Edit");
        }
        $toggle.on("click", function() {
            var $this = $(this);
            if ($this.text()=="Edit") {
                $this.text("View");
                $img.imgNotes("option", "canEdit", true);
            } else {
                $this.text('Edit');
                $img.imgNotes('option', 'canEdit', false);
            }
        });

        /*
        var $export = $("#export");
        $export.on("click", function() {
            var $table = $("<table/>").addClass("gridtable");
            var notes = $img.imgNotes('export');
            $table.append("<th>X</th><th>Y</th><th>NOTE</th>");
            $.each(notes, function(index, item) {
                $table.append("<tr><td>" + item.x + "</td><td>" + item.y + "</td><td>" + item.note + "</td></tr>");
            });
            $('#txt').html($table);
        });

        var $clear = $("#clear");
        $clear.on("click", function() {
            $img.imgNotes('clear');
        });

        var $toggleZ = $("#toggleZoom");
        if ($img.imgNotes("option","zoomable"))
            $toggleZ.text("Zoom Off");
        else
            $toggleZ.text("Zoom On");

        $toggleZ.on("click", function() {
            var $this = $(this);
            if ($this.text()=="Zoom On") {
                $this.text("Zoom Off");
                $img.imgNotes("option","zoomable", true);
            } else {
                $this.text("Zoom On");
                $img.imgNotes("option","zoomable", false);
            }
        });

        var $toggleD = $("#toggleDrag");
        if ($img.imgNotes("option","dragable"))
            $toggleD.text("Drag Off");
        else
            $toggleD.text("Drag On");

        $toggleD.on("click", function() {
            var $this = $(this);
            if ($this.text()=="Drag On") {
                $this.text("Drag Off");
                $img.imgNotes("option","dragable", true);
            } else {
                $this.text("Drag On");
                $img.imgNotes("option","dragable", false);
            }
        });

        var $toggleI = $("#toggleImage");
        $toggleI.on("click", function() {
            var $this = $(this);
            $img.imgNotes("destroy");
            var src = ($("#image").attr('src') === 'images/test_image.jpg') ? 'images/test_image_2.jpg' : 'images/test_image.jpg';
            $("#image").attr('src',src);
            $img = $("#image").imgNotes({ onEdit: cust_onEdit });
        });*/
    });
})(jQuery);

function calculateSum() {
    var sum = 0;
    var costs = "";
    //iterate through each textboxes and add the values
    $(".input-cost").each(function() {
        //add only if the value is number
        var value = this.value;

        value = value.replace(",","");
        costs += value+"|";
        if (!isNaN(value) && value.length != 0) {
            sum += parseFloat(value);

            $(this).removeClass('input-error');
        }
        else if (value.length != 0){
            $(this).addClass('input-error');
        }
    });

    costs = costs!="" ? costs.slice(0, -1) : costs;
    document.cookie = "costSelected="+costs;
    $.cookie("selectedCosts", costs, {path: '/'});
    sum = sum.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
    $("#sumTotal").html(sum);
}

function initEventsAddAssigned(invoker){
    $("#dialog_wrap #assigned_data_form").validationEngine({
        validationEventTrigger: 'blur',
        scroll: false,
        promptPosition: "centerRight",
        autoHidePrompt: true,
        autoHideDelay: jsVars.timeErrorValidation
    });


    $('#dialog_wrap #assigned_data_form').loadingButton({
        onClick: function(e, btn) {
            e.stopPropagation();
            if ($("#dialog_wrap #assigned_data_form").validationEngine('validate')) {//if ok current section, go to other section
                var newNameAssigned = $("#dialog_wrap #item_name_assigned").val();
                $("select#"+invoker).append('<option value="'+newNameAssigned+'">'+newNameAssigned+'</option>');
                $("select#"+invoker).val(newNameAssigned);
                $("select#"+invoker).trigger("chosen:updated");
                $('#dialog_wrap').dialog('close');
                /*$.ajax({
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
                });*/
            } else {
                btn.loadingButton('stop', -1);
            }
            return false;
        }
    });
}