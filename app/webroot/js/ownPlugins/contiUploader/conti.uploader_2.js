/*!
 * jQuery contiUploader plugin
 * Original author: @ieialbertogd
 * Copyright (c) 2014 - 2018 Alberto Guerrero
 * Further changes, comments: ieialbertogd@gmail.com
 * Licensed under the MIT license
 * 
 */

// the semi-colon before the function invocation is a safety 
// net against concatenated scripts and/or other plugins 
// that are not closed properly.
;
(function($, window, document, undefined) {
    var pluginName = 'contiUploader',
            defaults = {
                uploadUrl: null,
                idCtrlFile: 'file',
                type: 'image',
                icon: null,
                width: 100,
                height: 40,
                onError: function(error) {
                    return false;
                },
                onProcess: function(obj) {
                    return false;
                },
                onCompleteUpload: function(e, obj) {
                    return false;
                }
            };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = 'contiUploader';
        this._obj = element;
        this.init();
    }

    Plugin.prototype = {
        init: function() {
            var obj = $(this._obj);
            var uploader_id = 'contiuploader' + Math.floor((Math.random() * 100000) + 1);
            obj.append('<form id="' + uploader_id + '" method="post" enctype="multipart/form-data"><input type="file" name="' + this.options.idCtrlFile + '"/><div class="contiuploader-msgs"></div></form>');
            if (typeof (this.options.sizeImg) !== 'undefined') {
                $('#' + uploader_id).append('<input type="hidden" name="width-image" value="' + this.options.sizeImg.w + '"/>');
                $('#' + uploader_id).append('<input type="hidden" name="height-image" value="' + this.options.sizeImg.h + '"/>');
            }
            if (typeof (this.options.pathUpload) !== 'undefined') {
                $('#' + uploader_id).append('<input type="hidden" name="path-upload" value="' + this.options.pathUpload + '"/>');
            }
            $('#' + uploader_id).append('<input type="hidden" name="type-file" value="' + this.options.type + '"/>');
            obj.css({
                cursor: 'pointer',
                display: 'block',
                width: this.options.width + 'px',
                height: this.options.height + 'px',
                overflow: 'hidden',
                zIndex: '1'
            });
            if (this.options.icon !== null) {
                obj.css('background', 'url("' + this.options.icon + '") no-repeat scroll center center transparent');
                obj.attr('rel', 'url("' + this.options.icon + '")');
            }


            obj.find('input[type=file]').css({
                cursor: 'pointer',
                display: 'block',
                width: this.options.width + 'px',
                height: this.options.height + 'px',
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

            var options = this.options;
            var _this = this;
            obj.find('input[type=file]').change(function() {
                var file = this.files[0];
                switch (options.type) {
                    case 'image':
                        if (!file.type.match(/image.*/)) {
                            $(this).next().html(_this.__('No es archivo de imagen')).show();
                            return false;
                        }
                    break;
                    case 'pdf':
                        if (!file.type.match(/pdf.*/)) {
                            $(this).next().html(_this.__('No es archivo pdf')).show();
                            return false;
                        }
                    break;
                    case 'video':
                        if (!file.type.match(/video.*/)) {
                            $(this).next().html(_this.__('No es archivo de video')).show();
                            return false;
                        } else {
                            var fileSize = 0;
                            try {
                                fileSize = file.size; // Size returned in bytes.
                            } catch (e) {
                                var objFSO = new ActiveXObject("Scripting.FileSystemObject");
                                var e = objFSO.getFile($(this).val());
                                fileSize = e.size;
                            }
                            if(fileSize > options.maxSizeUpload){                                
                                var megas = Math.round(options.maxSizeUpload / 1024 / 1024);
                                $(this).next().html(_this.__('Tamanio de archivo excede el minimo permitido. Maximo MBs:'+' '+megas)).show();
                                return false;
                            }
                        }
                        break;
                }
                $(this).parent().submit();
                return options.onProcess(obj);
            });

            obj.find('form').submit(function(e) {
                e.preventDefault();
                if (options.uploadUrl !== null) {
                    var formData = new FormData($(this)[0]);
                    $.ajax({
                        type: 'post',
                        url: options.uploadUrl,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            response = $.parseJSON(response);
                            switch (response.code_response) {
                                case 1:
                                    _this.__setError(obj, _this.__('No se definio la ruta donde se cargara el archivo'));                                    
                                break;
                                case 2:
                                    _this.__setError(obj, _this.__('Tipo de archivo incorrecto'));                                    
                                break;
                                case 3:
                                    var megas = Math.round(options.maxSizeUpload / 1024 / 1024);
                                    _this.__setError(obj, _this.__('Tamanio de archivo excede el minimo permitido. Maximo MBs:'+' '+megas));                                                                      
                                break;  
                           default:
                                return options.onCompleteUpload(response, obj);
                            break;
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            _this.__setError(obj, _this.__('Error al procesar la peticion'));
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
            var options = this.options;
            return options.onError(strError, obj);
        }
    };

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn['contiUploader'] = function(options) {
        var args = arguments;
        if (options === undefined || typeof options === 'object') {
            // Creates a new plugin instance, for each selected element, and
            // stores a reference withint the element's data
            return this.each(function() {
                //if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + 'contiUploader', new Plugin(this, options));
                //}
            });
        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
            // Call a public pluguin method (not starting with an underscore) for each 
            // selected element.
            if (Array.prototype.slice.call(args, 1).length == 0 && $.inArray(options, $.fn[pluginName].getters) != -1) {
                // If the user does not pass any arguments and the method allows to
                // work as a getter then break the chainability so we can return a value
                // instead the element reference.
                var instance = $.data(this[0], 'plugin_' + pluginName);
                return instance[options].apply(instance, Array.prototype.slice.call(args, 1));
            } else {
                // Invoke the speficied method on each selected element
                return this.each(function() {
                    var instance = $.data(this, 'plugin_' + pluginName);
                    if (instance instanceof Plugin && typeof instance[options] === 'function') {
                        instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                    }
                });
            }
        }
    }

})(jQuery, window, document);