/*!
 * jQuery cococropper plugin
 * Original author: @ieialbertogd
 * Copyright (c) 2014 - 2018 Alberto Guerrero
 * Further changes, comments: ieialbertogd@gmail.com
 * Licensed under the MIT license
 * 
 * 
 * depends of cropper plugin :
 * https://github.com/fengyuanchen/cropper/blob/master/dist/cropper.js
 */

// the semi-colon before the function invocation is a safety 
// net against concatenated scripts and/or other plugins 
// that are not closed properly.
;
(function($, window, document, undefined) {
    var pluginName = 'cococropper',
            defaults = {
                previewImg: null,
                sourceImg: null,
                previewDataImg: null,
                classPreviewImg: '',
                labelSave: 'Save',
                wrapper: null,
                cropSize: {w: 100, h: 100},
                onSaveCrop: function(btn) {
                    return false;
                },
                onCompleteCrop: function(response) {
                    return false;
                }
            };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = 'cococropper';
        this._obj = element;
        this.init();        
    }

    Plugin.prototype = {
        init: function() {
            var obj = $(this._obj);
            obj.html('<div class="avatar-wrapper" style="width: 800px;"></div>');
            obj.append('<div class="avatar-preview ' + this.options.classPreviewImg + '"></div>');

            var form = '<form><input class="avatar-src" name="avatar_src" type="hidden">';
            form += '<input class="avatar-data" name="avatar_data" type="hidden"><input class="avatar-size" name="avatar_size" type="hidden">';
            form += '<button type="button" class="' + this.options.submitClass + '">' + this.options.submitLabel + '</button></form>';
            obj.append(form);


            this.$avatarForm = obj.find("form");
            this.$avatarSave = this.$avatarForm.find("button");

            this.$avatarSrc = obj.find(".avatar-src");
            this.$avatarData = obj.find(".avatar-data");
            this.$avatarSize = obj.find(".avatar-size");

            this.$avatarWrapper = obj.find(".avatar-wrapper");
            this.$avatarPreview = obj.find(".avatar-preview");
            this.$avatarPreview.css('margin', '25px auto');


            if (typeof (this.options.watermarkLogo) !== 'undefined') {
                this.$avatarForm.append('<input type="hidden" value="' + this.options.watermarkLogo + '" name="avatar_logo">');
            }


            var url = this.options.previewImg;
            this.$avatarPreview.empty().html('<img src="' + url + '">');
            obj.hide();
            this.$avatarSrc.val(this.options.sourceImg);

            var sizeCrop = [
                '{"w":' + this.options.cropSize.w,
                '"h":' + this.options.cropSize.h + "}"
            ].join();

            this.$avatarSize.val(sizeCrop);

            var _this = this;
            _this.url = this.options.previewDataImg;
            _this.crop();

            var options = this.options;
            this.$avatarSave.click(function(e) {
                e.stopPropagation();

                var data = _this.$img.cropper("getData");
                var json = [
                    '{"x1":' + data.x,
                    '"y1":' + data.y,
                    '"height":' + data.height,
                    '"width":' + data.width + "}"
                ].join();
                _this.$avatarData.val(json);

                options.onSaveCrop($(this));
                $.ajax({
                    type: 'post',
                    url: options.cropUrl,
                    data: {formdata: _this.$avatarForm.serialize()},
                    success: function(response) {
                        response = $.parseJSON(response);
                        options.onCompleteCrop(response);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        _this.__setError(obj, _this.__('Error al procesar la peticion'));
                    }
                });
                return false;
            });
        },
        crop: function() {
            var _this = this;

            this.$img = $('<img src="' + this.url + '">');
            this.$avatarWrapper.empty().html(this.$img);
            this.startCropper();
        },
        startCropper: function() {
            var _this = this;
            var scale = parseFloat(this.options.cropSize.w / this.options.cropSize.h);
            //if (!this.active) {
                this.$img.cropper({
                    movable: false,
                    zoomable: true,
                    rotatable: false,
                    scalable: false,
                    aspectRatio: scale,
                    preview: this.$avatarPreview.selector,
                    done: function(data) {
                        console.log(data);
                        var json = [
                            '{"x1":' + data.x1,
                            '"y1":' + data.y1,
                            '"height":' + data.height,
                            '"width":' + data.width,
                            '"x2":' + data.x2,
                            '"y2":' + data.y2 + "}"
                        ].join();
                        _this.$avatarData.val(json);
                    }
                });

                this.active = true;
           // }
        }

    };

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn['cococropper'] = function(options) {
        var args = arguments;
        if (options === undefined || typeof options === 'object') {
            // Creates a new plugin instance, for each selected element, and
            // stores a reference withint the element's data
            return this.each(function() {
                //if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + 'cococropper', new Plugin(this, options));
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