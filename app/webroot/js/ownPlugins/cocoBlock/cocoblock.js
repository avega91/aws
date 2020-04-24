/*!
 * jQuery cocoblock plugin
 * Original author: @ieialbertogd
 * Copyright (c) 2014 - 2018 Alberto Guerrero
 * Further changes, comments: ieialbertogd@gmail.com
 * Licensed under the MIT license
 * 
 * depends for spinner plugin : fgnass.github.com/spin.js#v2.0.1 MIT license and
 * blockUI plugin : https://github.com/malsup/blockui/
 */

// the semi-colon before the function invocation is a safety 
// net against concatenated scripts and/or other plugins 
// that are not closed properly.
;
(function($, window, document, undefined) {
    var pluginName = 'cocoblock',
            defaults = {
                zIndexF: 10,
                spinner: null,
                obj: null,
                spinnerColor: '#FFFFFF',
                message: '',
                maxZindex: 0,
                overlayColor: '#FFF',
                opacity: 0.9,
            };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = 'cocoblock';
        this._obj = element;
        this.maxZindex = Math.max.apply(null,
                $.map($('body > *:not(div.tipsy):not(.smallipop-tour-overlay):not(.smallipop-instance)'), function(e, n) {
                    if ($(e).css('position') != 'static')
                        return Math.abs(parseInt($(e).css('z-index'))) || 1;
                }));

        this.init();
    }

    Plugin.prototype = {
        init: function() {

            var obj = $(this._obj);
            this._defaults.obj = obj;

            var block_id = 'cocoblock' + Math.floor((Math.random() * 100000) + 1);

            var height = obj.get(0).tagName=='BODY' || typeof(obj.css('height'))=='undefined' ? $(document).height()+'px': obj.css('height');
            obj.append('<div id="' + block_id + '"></div>');
            obj.find('#' + block_id).css({
                background: 'transparent',
                width: '100%',
                height: height,
                top: '0',
                left: '0',
                position: 'absolute',
                zIndex: this.maxZindex,
            });

            if (this.options.fixPosition) {
                obj.css('position', 'relative');
            }
            
            //obj.hide();
            obj.find('#' + block_id).block({message: this.options.message, overlayCSS: {backgroundColor: this.options.overlayColor, opacity: this.options.opacity} });
            obj.find('#' + block_id).addClass('cocoblocker');
            var msg_block = obj.find('#' + block_id).find('.blockMsg');
            if(msg_block.size()>0){
                var current_top = msg_block.css('top');
                current_top = parseInt(parseInt(current_top)+50)+'px';
                msg_block.css({
                        top:parseInt(current_top+40)+'px',
                        border: 'none',
                        background: 'transparent',
                        fontSize: '16px'
                    });
            }
            if (this.options.spinner !== false) {
                /*var opts = {color: this._defaults.spinnerColor};
                var target = obj.find('#' + block_id)[0];//Get a HTML DOM Object
                this._defaults.spinner = new Spinner(opts).spin(target);*/
            } else {
                obj.find('#' + block_id).find('.blockUI').css('cursor', 'default');
            }
            //obj.fadeIn();
        },
        unblock: function() {
            var invoker = $(this.element);
            var blocker = invoker.find('.cocoblocker').first();
            blocker.fadeOut('slow', function(){
                $(this).remove();
            });

        }
    };

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn['cocoblock'] = function(options) {
        var args = arguments;
        if (options === undefined || typeof options === 'object') {
            // Creates a new plugin instance, for each selected element, and
            // stores a reference withint the element's data
            return this.each(function() {
                //if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + 'cocoblock', new Plugin(this, options));
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