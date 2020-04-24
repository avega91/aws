(function($) {
    $.fn.contiUploader = function(method) {
        var methods = {
            init: function(options) {
                this.contiUploader.settings = $.extend({}, this.contiUploader.defaults, options);
                return this.each(function() {
                    var $element = $(this), // reference to the jQuery version of the current DOM element
                            element = this;      // reference to the actual DOM element
                    if (typeof window.FormData === "undefined") {
                        return alert(__("Image uploads are not supported in this browser, use Chrome, Firefox, or Safari instead."));
                        //$element.contiUploader.settings.onError(__("Image uploads are not supported in this browser, use Chrome, Firefox, or Safari instead."));
                    }else{
                        methods._initialize($element.contiUploader, $element);
                    }
                });

            },
            _initialize: function(element, obj) {
                var uploader_id = 'contiuploader'+Math.floor((Math.random() * 100000) + 1); 
                obj.append('<form id="'+uploader_id+'" method="post" enctype="multipart/form-data"><input type="file" name="'+element.settings.idCtrlFile+'"/><div class="contiuploader-msgs"></div></form>');
                if(typeof(element.settings.sizeImg)!=='undefined'){
                    $('#'+uploader_id).append('<input type="hidden" name="width-image" value="'+element.settings.sizeImg.w+'"/>');
                    $('#'+uploader_id).append('<input type="hidden" name="height-image" value="'+element.settings.sizeImg.h+'"/>');
                }
                if(typeof(element.settings.pathUpload)!=='undefined'){
                    $('#'+uploader_id).append('<input type="hidden" name="path-upload" value="'+element.settings.pathUpload+'"/>');
                }
                obj.css({
                    cursor: 'pointer',
                    display: 'block',
                    width: element.settings.width + 'px',
                    height: element.settings.height + 'px',
                    overflow: 'hidden',
                    zIndex: '1'
                });
                if (element.settings.icon !== null) {
                    obj.css('background', 'url("' + element.settings.icon + '") no-repeat scroll center center transparent');
                }

                
                obj.find('input[type=file]').css({
                    cursor: 'pointer',
                    display: 'block',
                    width: element.settings.width + 'px',
                    height: element.settings.height + 'px',
                    overflow: 'hidden',
                    opacity: 0,
                    filter: 'alpha(opacity=0)'
                });
                obj.find('div').css({
                    color: '#F00',
                    fontSize: '12px',
                    textAlign: 'center',
                    position: 'relative',
                    bottom: '30px',
                    width: '100%'
                });

                obj.find('input[type=file]').click(function(e) {
                    obj.find('div').hide();
                });
                obj.find('input[type=file]').change(function() {
                    var file = this.files[0];
                    switch (element.settings.type) {
                        case 'image':
                            if (!file.type.match(/image.*/)) {
                                $(this).next().html(methods.__('No es archivo de imagen')).show();
                                return false;
                            }
                            break;
                    }
                    $(this).parent().submit();
                    return element.settings.onProcess(obj);
                    
                });

                obj.find('form').submit(function(e) {
                    e.preventDefault();
                    if (element.settings.uploadUrl !== null) {
                        var formData = new FormData($(this)[0]);
                        $.ajax({
                            type: 'post',
                            url: element.settings.uploadUrl,
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                 response = $.parseJSON(response);
                                 switch(response.code_response){
                                     case 1:
                                        methods.__setError(obj,__('No se definio la ruta donde se cargara el archivo'));
                                     break;  
                                    default:
                                        return element.settings.onCompleteUpload(response,obj);
                                    break;
                                 }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                methods.__setError(obj,__('Error al procesar la peticion'));
                            }
                        });
                    }
                    return false;
                });

            },
            __: function(s) {
                return typeof (conti_l10n) != 'undefined' && typeof (conti_l10n[s]) != 'undefined' ? conti_l10n[s] : s;
            },
            __setError: function(obj, strError) {
                obj.find('form div.contiuploader-msgs').html(strError).show();
            }

        }

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method "' + method + '" does not exist in contiUploader plugin!');
        }
    }

    $.fn.contiUploader.defaults = {
        uploadUrl: null,
        idCtrlFile: 'file',
        type: 'image',
        icon: null,
        width: 100,
        height: 40,
        onError: function(error){ return false;},
        onProcess: function(obj){ return false; },
        onCompleteUpload: function(e, obj) { return false; }
    }

    $.fn.contiUploader.settings = {}

})(jQuery);
