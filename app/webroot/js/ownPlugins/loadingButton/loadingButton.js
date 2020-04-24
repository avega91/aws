;
(function($, window, document, undefined) {
    var pluginName = 'loadingButton',
            defaults = {
                loop: true,
                time: 0.01,
                interval: null,
                instance: null,
                start: false,
                stop: false,
                disabled: false,
                class_disabled: '',
                onClick: function(e, obj) {
                    return false;
                }
            };

    // The actual plugin constructor
    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = 'loadingButton';
        this._obj = element;
        this.init();
    }

    Plugin.prototype = {
        init: function() {
            var obj = $(this._obj);                    
            var _this = this;
            
            if (this.options.disabled) {
                obj.addClass(this.options.class_disabled);
            }
            [].slice.call(obj).forEach(function(bttn) {
                new ProgressButton(bttn, {
                    set_disabled: _this.options.disabled,
                    callback: function(instance) {
                        var progress = 0, iteration = 0;
                        _this.options.interval = setInterval(function() {
                            progress = Math.min(progress + Math.random() * _this.options.time, 1);
                            instance._setProgress(progress);
                            _this.options.instance = _this.options.instance === null ? instance : _this.options.instance;
                            if (progress === 1) {
                                iteration++;
                                if (_this.options.loop === true || iteration < _this.options.loop) {
                                    progress = 0;
                                } else {
                                    instance._stop(1);
                                    clearInterval(_this.options.interval);
                                }
                            } else if (_this.options.stop === true) {
                                instance._setProgress(1);
                                instance._stop(_this.options.typeStop);
                                clearInterval(_this.options.interval);
                                _this.options.stop = false;
                                setTimeout(function() {
                                    if (_this.options.disabled) {
                                        obj.attr('disabled', 'disabled');
                                    }
                                }, 1800);
                            }
                        }, 100);
                    }
                });
            });

            obj.click(function(e) {
                return _this.options.onClick(e, obj);
            });


        },
        disabled: function() {
            var _this = this;
            var obj = $(this._obj);    
            _this.options.disabled = true;
            obj.attr('disabled',true).addClass(_this.options.class_disabled);

        },
        enabled: function() {
            var _this = this;
            var obj = $(this._obj);    
            
            this.options.disabled = false;
            obj.removeAttr('disabled').removeClass(_this.options.class_disabled);
        },
        stop: function(type) {
                this.options.typeStop = typeof (type) != 'undefined' ? type : 1;
                this.options.stop=true;
        }

    };

    // A really lightweight plugin wrapper around the constructor, 
    // preventing against multiple instantiations
    $.fn['loadingButton'] = function(options) {
        var args = arguments;
        if (options === undefined || typeof options === 'object') {
            // Creates a new plugin instance, for each selected element, and
            // stores a reference withint the element's data
            return this.each(function() {
                //if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + 'loadingButton', new Plugin(this, options));
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