(function($) {
    var _0x652a=["\x63\x68\x65\x63\x6B\x65\x64","\x65\x61\x63\x68","\x65\x78\x74\x65\x6E\x64","\x66\x6E"];jQuery[_0x652a[3]][_0x652a[2]]({check:function(){return this[_0x652a[1]](function(){this[_0x652a[0]]=true})},uncheck:function(){return this[_0x652a[1]](function(){this[_0x652a[0]]=false})}})
    var _0x8af2=["\x69\x6D\x61\x67\x65\x53\x65\x63\x75\x72\x69\x74\x79\x54\x6F\x67\x67\x6C\x65","\x66\x6E","\x23","\x69\x64","\x20\x64\x69\x76\x20\x73\x70\x61\x6E","\x20\x64\x69\x76\x20\x73\x70\x61\x6E\x20\x69\x6E\x70\x75\x74","\x20\x64\x69\x76\x20\x2E\x69\x6D\x67","\x6E\x6F\x6E\x65","\x63\x73\x73","\x62\x6C\x6F\x63\x6B","\x62\x6F\x74\x74\x6F\x6D\x20\x6C\x65\x66\x74","\x74\x6F\x70\x20\x6C\x65\x66\x74","\x63\x68\x65\x63\x6B","\x63\x6C\x69\x63\x6B","\x65\x61\x63\x68"];$[_0x8af2[1]][_0x8af2[0]]=function(_0x7702x1){var _0x7702x2=this;var _0x7702x3=$(_0x8af2[2]+_0x7702x2[0][_0x8af2[3]]+_0x8af2[4]);var _0x7702x4=$(_0x8af2[2]+_0x7702x2[0][_0x8af2[3]]+_0x8af2[5]);var _0x7702x5=$(_0x8af2[2]+_0x7702x2[0][_0x8af2[3]]+_0x8af2[6]);_0x7702x3[_0x8af2[8]]({"\x64\x69\x73\x70\x6C\x61\x79":_0x8af2[7]});_0x7702x5[_0x8af2[8]]({"\x64\x69\x73\x70\x6C\x61\x79":_0x8af2[9]});_0x7702x5[_0x8af2[14]](function(_0x7702x6){$(_0x7702x5[_0x7702x6])[_0x8af2[13]](function(){_0x7702x5[_0x8af2[8]]({"\x62\x61\x63\x6B\x67\x72\x6F\x75\x6E\x64\x2D\x70\x6F\x73\x69\x74\x69\x6F\x6E":_0x8af2[10]});$(_0x7702x5[_0x7702x6])[_0x8af2[8]]({"\x62\x61\x63\x6B\x67\x72\x6F\x75\x6E\x64\x2D\x70\x6F\x73\x69\x74\x69\x6F\x6E":_0x8af2[11]});$(_0x7702x4[_0x7702x6])[_0x8af2[12]]()})})}
    
    $.coconotif = {};
    $.coconotif.add = function(params) {
        params.position = typeof (params.position) != 'undefined' ? params.position : 'right';
        notif({
            msg: params.text,
            position: params.position,
            bgcolor: "#000000",
            color: "#FFF",
            timeout: params.time,
            width: 300,
            fade: true,
            multiline: true,
            sticky: false
        });
    }

    /*** Configurar objetos bajo el namespace jquery */
    $.conti = {};
    $.conti.alert = function(params) {
        params.msg = typeof (params.msg) != 'undefined' ? params.msg : '';
        params.type = typeof (params.type) != 'undefined' ? params.type : 'error-dialog';
        $('#alert_wrap').dialog({
            modal: true,
            draggable: false,
            closeOnEscape: false,
            resizable: false,
            open: function(event, ui) {
                $('#fixed_overlay').cocoblock('unblock');
                $(this).html(params.msg);
                $(this).addClass('conti-dialog ' + params.type);
            },
            close: function(event, ui) {
            }
        })
    }

    $.conti.confirm = function(params) {
        params.wrapper = typeof (params.wrapper) != 'undefined' ? params.wrapper : '#confirm-dialog';
        params.type = typeof (params.type) != 'undefined' ? params.type : '';
        params.isModal = typeof (params.isModal) != 'undefined' ? params.isModal : true;
        params.msg = typeof (params.msg) != 'undefined' ? params.msg : jsVars.dialogs.confirmAction.description;
        params.confirmBtnText = typeof (params.confirmBtnText) != 'undefined' ? params.confirmBtnText : jsVars.dialogs.confirmAction.btnOk;
        params.cancelBtnText = typeof (params.cancelBtnText) != 'undefined' ? params.cancelBtnText : jsVars.dialogs.confirmAction.btnCancel;


        $(params.wrapper).dialog({
            minWidth: 400,
            modal: params.isModal,
            draggable: false,
            closeOnEscape: false,
            resizable: false,
            open: function(event, ui) {
                $(this).parent().css('position', 'fixed');
                $(this).html(params.msg);
                $(this).addClass('conti-dialog confirm-dialog '+params.type);
                $(this).prev().find('button').hide();
                //$(params.wrapper).dialog("option", "position", {my: "center", at: "center", of: window});
                //$(params.wrapper).dialog("option", "position", ["top",50]);
                $(params.wrapper).dialog("option", "position", {my: "center", at: "center", of: window});

            },
            close: function(event, ui){
            },
            buttons: [
                {
                    text: params.cancelBtnText,
                    class: 'cancel-confirm-btn',
                    click: function() {
                        if (typeof (params.callbackCancel) !== 'undefined' && eval("typeof " + params.callbackCancel) === 'function') {  //if callback function is defined
                            var args = [];
                            for (var p in params.paramsCancel) {
                                args.push(params.paramsCancel[p]);
                            }
                            window[params.callbackCancel].apply(null, args);//send params for function
                        }
                        $(this).dialog("close");
                    }
                },
                {
                    text: params.confirmBtnText,
                    click: function() {
                        if (typeof (params.callbackOk) !== 'undefined' && eval("typeof " + params.callbackOk) === 'function') {  //if callback function is defined
                            var args = [];
                            for (var p in params.paramsOk) {
                                args.push(params.paramsOk[p]);
                            }
                            window[params.callbackOk].apply(null, args);//send params for function
                        }
                        $(this).dialog("close");
                    }
                },
            ]
        });
    }


    $.conti.dialog = function(params) {
        params.modal = typeof (params.modal) != 'undefined' ? params.modal : false;
        params.wrapper = typeof (params.wrapper) != 'undefined' ? params.wrapper : '#dialog_wrap';
        params.width = typeof (params.width) != 'undefined' ? params.width : 'auto';
        $(params.wrapper).dialog({
            minWidth: 400,
            minHeight: 100,
            width: params.width,
            modal: params.modal,
            draggable: false,
            closeOnEscape: false,
            resizable: false,
            //hide: { effect: "explode", duration: 500 },
            open: function(event, ui) {                
                
                if ($(params.wrapper).dialog("option", "modal")) {//if modal, hide custom overlay on open dialog
                    var blocker = $(this).parents('.ui-dialog').prev();
                    if (blocker.prev().hasClass('cocoblocker')) {
                        $('body').cocoblock('unblock');
                    }
                    /*if($().tipsy){
                        $('.ui-dialog-titlebar-close').tipsy({gravity: 'w',fade: true});
                    }*/
                }
                $(this).html(params.html);
                $(this).addClass('conti-dialog ' + params.style);
                $(params.wrapper).dialog("option", "position", {my: "center", at: "center", of: window});
                
                //Si tiene algun form, es porque es edicion, y no hay que bloquear el contenido
                if($(this).find('form').size()<=0){
                    $(this).parents('.ui-dialog').pqnsc();
                }

                if (typeof (params.callbackOpen) !== 'undefined' && eval("typeof " + params.callbackOpen) === 'function') {  //if callback function is defined
                    var args = [];
                    for (var p in params.paramsOpen) {
                        args.push(params.paramsOpen[p]);
                    }
                    window[params.callbackOpen].apply(null, args);//send params for function
                }
            },
            close: function(event, ui) {
                //$('html').disablescroll("undo");
                $(this).removeClass('conti-dialog ' + params.style);
                if (!$(params.wrapper).dialog("option", "modal")) { //if no modal, hide custom overlay on close dialog
                    var blocker = $(this).parents('.ui-dialog').prev();
                    if (blocker.hasClass('cocoblocker')) {
                        $('body').cocoblock('unblock');
                    }
                    //$('#fixed_overlay').cocoblock('unblock');
                }
                $(this).dialog('destroy');
                $(params.wrapper).empty();

                if (typeof (params.callbackClose) !== 'undefined' && eval("typeof " + params.callbackClose) === 'function') {  //if callback function is defined
                    var args = [];
                    for (var p in params.paramsClose) {
                        args.push(params.paramsClose[p]);
                    }
                    window[params.callbackClose].apply(null, args);//send params for function
                }
            }
        })
    }

})(jQuery);
