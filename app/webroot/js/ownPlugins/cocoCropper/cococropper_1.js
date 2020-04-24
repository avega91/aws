/**
 * jquery cococropper plugin 
 * v1.0 2014-07-21
 * 
 * Copyright (c) 2014 - 2018 Alberto Guerrero
 * Licensed under the MIT license
 * 
 * depends of cropper plugin : 
 * https://github.com/fengyuanchen/cropper/blob/master/dist/cropper.js
 */

(function($) {
    $.fn.cococropper = function(method) {
        var methods = {
            init: function(options) {
                this.cococropper.settings = $.extend({}, this.cococropper.defaults, options);
                return this.each(function() {
                    var $element = $(this), // reference to the jQuery version of the current DOM element
                            element = this;      // reference to the actual DOM element
                    methods._initialize($element.cococropper, $element);
                });

            },
            _initialize: function(element, obj) {
                
                obj.html('<div class="avatar-wrapper"></div>');
                obj.append('<div class="avatar-preview ' + element.settings.classPreviewImg + '"></div>');

                var form = '<form><input class="avatar-src" name="avatar_src" type="hidden">';
                form += '<input class="avatar-data" name="avatar_data" type="hidden"><input class="avatar-size" name="avatar_size" type="hidden">';
                form += '<button type="button" class="' + element.settings.submitClass + '">' + element.settings.submitLabel + '</button></form>';
                obj.append(form);


                this.$avatarForm = obj.find("form");
                this.$avatarSave = this.$avatarForm.find("button");

                this.$avatarSrc = obj.find(".avatar-src");
                this.$avatarData = obj.find(".avatar-data");
                this.$avatarSize = obj.find(".avatar-size");
                
                this.$avatarWrapper = obj.find(".avatar-wrapper");
                this.$avatarPreview = obj.find(".avatar-preview");
                this.$avatarPreview.css('margin', '25px auto');
                
                
                if(typeof(element.settings.watermarkLogo)!=='undefined'){
                    this.$avatarForm.append('<input type="hidden" value="'+element.settings.watermarkLogo+'" name="avatar_logo">');
                }


                var url = element.settings.previewImg;
                this.$avatarPreview.empty().html('<img src="' + url + '">');
                obj.hide();
                this.$avatarSrc.val(element.settings.sourceImg);
                
                var sizeCrop = [
                                '{"w":' + element.settings.cropSize.w,
                                '"h":' + element.settings.cropSize.h + "}"
                            ].join();
                            
                this.$avatarSize.val(sizeCrop);

                var _this = this;
                _this.url = element.settings.previewDataImg;
                _this.crop(element);


                this.$avatarSave.click(function(e) {
                    e.stopPropagation();      
                    element.settings.onSaveCrop($(this));
                    $.ajax({
                        type: 'post',
                        url: element.settings.cropUrl,
                        data: { formdata : _this.$avatarForm.serialize()},
                        success: function(response) {
                             response = $.parseJSON(response);
                             element.settings.onCompleteCrop(response);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            _this.__setError(obj, _this.__('Error al procesar la peticion'));
                        }
                    });
                    return false;
                });
            },
            crop: function(element) {
                var _this = this;

                this.$img = $('<img src="' + this.url + '">');
                this.$avatarWrapper.empty().html(this.$img);
                this.startCropper(element);
            },
            startCropper: function(element) {
                var _this = this;
                var scale = parseFloat(element.settings.cropSize.w/element.settings.cropSize.h);
                if (!this.active) {
                    this.$img.cropper({
                        aspectRatio: scale,
                        preview: this.$avatarPreview.selector,
                        done: function(data) {
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
                }
            },
            __destroy: function(){
                //this.cococropper
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
            $.error('Method "' + method + '" does not exist in cococropper plugin!');
        }
    }
    $.fn.cococropper.defaults = {
        previewImg: null,
        sourceImg: null,
        previewDataImg: null,
        classPreviewImg: '',
        labelSave: 'Save',
        wrapper: null,
        cropSize:{w:100,h:100},
        onSaveCrop: function(btn){return false; },
        onCompleteCrop: function(response){ return false; }
    }

    $.fn.cococropper.settings = {}

})(jQuery);