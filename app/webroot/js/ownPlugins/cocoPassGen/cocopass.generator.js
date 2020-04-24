/**
 * jquery cocopass.generator plugin 
 * v1.0 2014-07-23
 * 
 * Original author: @ieialbertogd
 * Copyright (c) 2014 - 2018 Alberto Guerrero
 * Licensed under the MIT license
 * 
 */

(function($) {
    $.fn.passgenerator = function(method) {
        var methods = {
            init: function(options) {
                this.passgenerator.settings = $.extend({}, this.passgenerator.defaults, options);
                return this.each(function() {
                    var $element = $(this), // reference to the jQuery version of the current DOM element
                            element = this;      // reference to the actual DOM element
                    methods._initialize($element.passgenerator, $element);
                });

            },
            _initialize: function(element, obj) {
                obj.click(function(e) {
                    e.stopPropagation();
                    var iteration = 0;
                    var password = "";
                    while (iteration < element.settings.length) {
                        var randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
                        if (!element.settings.specialChars) {
                            if ((randomNumber >= 33) && (randomNumber <= 47)){ continue; }
                            if ((randomNumber >= 58) && (randomNumber <= 64)){ continue; }
                            if ((randomNumber >= 91) && (randomNumber <= 96)){ continue; }
                            if ((randomNumber >= 123) && (randomNumber <= 126)){ continue; }
                        }
                        iteration++;
                        password += String.fromCharCode(randomNumber);
                    }
                    if(typeof(element.settings.displayOn)!==null){
                        if($(element.settings.displayOn).is("input")){
                            $(element.settings.displayOn).val(password);
                        }else{
                            $(element.settings.displayOn).html(password);
                        }
                        $(element.settings.displayOn).effect( 'highlight', {}, 500);
                    }
                   
                    element.settings.onGen(password);
                    return e;
                });
            }
        }

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method "' + method + '" does not exist in passgenerator plugin!');
        }
    }
    $.fn.passgenerator.defaults = {
        length: 8,
        specialChars: true,
        displayOn: null,
        onGen: function(password) {
            return false;
        }
    }

    $.fn.passgenerator.settings = {}

})(jQuery);