$(document).ready(function() {
    init_toggle_full_menu();
    fix_screen_items();
    manage_dual_language_sections();
    init_notifications_panel();
    init_common_items();
    
    if ($.idleTimer) {
        initIdleTimer();
    }
    /********Security****/    
   
});

function init_notifications_panel() {
    $(document).on('click', '#notifications_nav ul li > a', function(e) {
        e.stopPropagation();
        var notification_to_active = $(this).attr('rel');

        $('#notifications_nav ul li > a').removeClass('active');
        $(this).addClass('active');

        $('#' + notification_to_active).siblings().fadeOut('fast', function() {
            $('#' + notification_to_active).removeClass('not-visible').show();
        });
        return false;
    });

    $('#panel_notifications a').click(function(e) {
        e.stopPropagation();
        return false;
    });

    $(document).click(function(e) {
        var target = $(e.target);
        var is_element_panel = target.parents('#panel_notifications').size()>0 ? true : false;                
        if (!is_element_panel && !target.is('.ui-button') && !target.is('div#ui_notifIt.info')) {
            if ($('#panel_notifications').hasClass('active')) {
                $('#panel_notifications').stop().animate({
                    width: '0px'
                }, 2000, 'easeOutBounce', function() {
                    $('#panel_notifications').removeClass('active');
                });
            }
        }
    });
    
    $('#accept_notifications_ctrl input').click(function(e) {
        $.ajax({
            type: 'post',
            url: jsVars.setMailNotificationsUser,
            data:{activate:$(this).is(':checked') ? 'SI':'NO'},
            success: function(response) {},
            error: function(xhr, ajaxOptions, thrownError) {
                errorAjax(xhr, ajaxOptions, thrownError);
            }
        });
    });

    $('#notifications_ring a').click(function() {
        $("#panel_notifications #notifications_wrapper").html('');
        $('#panel_notifications').cocoblock();
        $("#panel_notifications #notifications_wrapper").load(jsVars.refreshNotifications, function(data) {
            $('#panel_notifications').cocoblock('unblock');
            $('#notifications_ring div').html('0');
            initEventsPanelNotifications();
        });

        $('#panel_notifications').stop().animate({
            width: '400px'
        }, 2000, 'easeOutBounce', function() {
            $('#panel_notifications').addClass('active');
        });
    });
    
    $('#contact_msg').click(function(e) {
        e.stopPropagation();
        ga('send', 'event', 'Clicks', 'Tracking', 'Contact Form');
        var url = $(this).attr('rel');
        setTimeout(function(){
            $('body').cocoblock();
            $.ajax({
                type: 'post',
                url: url,
                success: function(response) {
                    $.conti.dialog({html: response, style: 'contact-dialog', modal: true, callbackOpen: 'callContactEvents', callbackClose: 'asdsad'});
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $('body').cocoblock('unblock');
                    errorAjax(xhr, ajaxOptions, thrownError);
                }
            });
        },500);
        
        return false;
    });
    
    
    
}
function init_common_items() {
    if ($().tipsy) {
        $('#toolbar a').tipsy({gravity: 's', fade: true});
        $('#topmenu a').tipsy({gravity: 'n', fade: true});
    }
    if ($().perfectScrollbar) {
        $('#comments_item_conveyor').perfectScrollbar({suppressScrollX: true});
    }
}
function manage_dual_language_sections() {

    $(document).on('click', '.collapsible-panels h1', function(e) {
        e.stopPropagation();
        var data = $(this).next();
        data.slideToggle("slow", function() {});
        return false;
    });

    $(document).on('click', '#slide_navigation a', function(e) {
        e.stopPropagation();

        var active_tab = $('#slide_form').find('.active');
        var avance = $(this).attr('rel');
        var index_item = $('#slide_form a').index(active_tab);
        var next_item = avance == 'prev' ? index_item - 1 : index_item + 1;
        if (next_item >= $('#slide_form a').length) {
            next_item = 0;
        }
        $('#slide_form a').eq(next_item).trigger('click');

        return false;
    });

    $(document).on('click', '#slide_form a', function(e) {
        e.stopPropagation();
        var ref_section = $('#' + $(this).attr('rel'));
        var title = typeof ($(this).attr('title')) != 'undefined' ? $(this).attr('title') : $(this).attr('original-title');
        ref_section.find('.slide-form-title').html(title);

        var form_ref = ref_section.parents('form');
        form_ref.css('overflow', 'hidden');
        //if (!$(this).hasClass('active')) {
        if ($('#slide_form a.active').size() < 2 && !$(this).hasClass('active')) {
            var active_opt = $('#slide_form a.active');
            var active_section = $('#' + active_opt.attr('rel'));

            $(this).addClass('active');

            active_section.animate({
                left: "-=" + active_section.width()
            }, 1000, function() {
                active_section.css({display: 'none', left: active_section.width()});
                active_section.removeClass('active_section');
            });

            ref_section.removeClass('hidden').css({display: 'block', left: active_section.width()});
            ref_section.animate({
                left: 0
            }, 1000, function() {
                form_ref.css('overflow', 'visible');
                ref_section.addClass('active_section');

                active_opt.removeClass('active');
            });

        }
        return false;
    });
}

function fix_screen_items() {

    /* if($('#footer').position().top<400){
     $('#footer').css('top',400);
     }*/
}

/**
 * Require perfectScroll plugin enabled
 * @returns {undefined}
 */
function fix_style_wysiwyg() {
    var referenceEditor = $('.active_section').find('.nicEdit-panelContain');
    $('.nicEdit-panelContain').parent().attr('style', referenceEditor.parent().attr('style'));

    $('.nicEdit-panelContain').parent().next().attr('style', referenceEditor.parent().next().attr('style'));
    $('.nicEdit-main').attr('style', referenceEditor.parent().next().find('.nicEdit-main').attr('style'));
    $('.nicEdit-main').parent().css({border: 'none', position: 'relative', overflow: 'hidden'});

    $('.nicEdit-main').css('min-height', parseInt($('.nicEdit-main').parent().css('max-height')) - 10);


    if (!$('.nicEdit-main').parent().hasClass('ps-container')) {
        $('.nicEdit-main').parent().perfectScrollbar({suppressScrollX: true});
        //$('.nicEdit-main').parent().css('overflow-y', 'hidden');        
        //$('.nicEdit-main').parent().css('overflow-x', 'visible');        
    }

}

function init_toggle_full_menu() {

    /**Cookie que controla si el menu lateral permanece cerrado o abierto **/

    $('#menu a').mouseenter(function() {
        $(this).animate({
            width: '100px'
        }, {
            queue: false,
            duration: 1,
            specialEasing: {
                width: "linear",
                height: "easeOutBounce"
            },
            complete: function() {
            }
        });
    }).mouseleave(function() {
        $(this).animate({
            width: '0px'
        }, {
            queue: false,
            duration: 1,
            specialEasing: {
                width: "linear",
                height: "easeOutBounce"
            },
            complete: function() {

            }
        });
    });

    /*
     if (typeof ($.cookie("toggle-conti-menu")) == 'undefined') {
     $.cookie("toggle-conti-menu", "open", {expires: 1});
     }
     
     if ($.cookie("toggle-conti-menu") == 'open') {
     $('#menu-toggle').addClass('open');
     $("#toggler_menu").show();
     } else {
     $("#menu").addClass('closed');
     $("#toggler_menu").addClass('closed');
     $('#menu-toggle').removeClass('open');
     }
     
     
     
     $('#menu-toggle,#toggler_menu').click(function() {
     
     if ($("#toggler_menu").hasClass('closed')) {
     $("#toggler_menu").toggleClass('closed');
     $('#menu-toggle').addClass('showing');
     }
     
     
     
     $("#menu").animate({
     width: $('#menu-toggle').hasClass('open') ? '50px' : '220px'
     }, 400, function() {
     
     $('#menu-toggle').removeClass('showing');
     if ($('#menu-toggle').hasClass('open')) {
     $.cookie("toggle-conti-menu", "closed", {expires: 1});
     $("#toggler_menu").toggleClass('closed');
     $('#menu-toggle').removeClass('open');
     } else {
     $.cookie("toggle-conti-menu", "open", {expires: 1});
     $('#menu-toggle').addClass('open');
     }
     });
     });*/


    /**Menu de acceso rapido **/
    var $el, leftPos, newWidth,
            $mainNav = $("#fast_menu");
    $mainNav.append("<li id='magic-line'></li>");
    var $magicLine = $("#magic-line");

//console.log($.isEmptyObject($mainNav));
    if ($magicLine.length) {
        $magicLine
                .width($(".current_page_item").width())
                .css("left", $(".current_page_item a").position().left)
                .data("origLeft", $magicLine.position().left)
                .data("origWidth", $magicLine.width());

        $("#fast_menu li a").hover(function() {
            $el = $(this);
            leftPos = $el.position().left;
            newWidth = $el.parent().width();
            $magicLine.stop().animate({
                left: leftPos,
                width: newWidth
            });
        }, function() {
            $magicLine.stop().animate({
                left: $magicLine.data("origLeft"),
                width: $magicLine.data("origWidth")
            });
        });
    }
    /**End menu acceso rapido **/

    //para controles de texto en el toolbar
    $(document).on('focus', '#toolbar .texteable-option input', function(e) {
        var menu = $(this).parent();

        menu.find('ul').css('width', '50px');
        $(this).css('background-color', '#FFF');
        menu.animate({
            width: '300px'
        }, 1000, function() {
        });
        return false;
    });

    //Click on close button for closable toolbar menu
    $(document).on('click', '#toolbar .closable-menu .close', function(e) {
        e.stopPropagation();
        var full_parent = $(this).parents('.closable-menu');
        if (full_parent.find('input').size() > 0) {//Si tiene un input
            var input = full_parent.find('input');
            var menu = input.parent();
            menu.find('ul').css('width', '0px');
            menu.animate({
                width: '100px'
            }, 1000, function() {
                input.css('background-color', 'transparent');
                input.val('');
            });
        }
        return false;
    });

    //click on option for collapsible menu with block
    $(document).on('click', '#toolbar .collapsible-menu', function(e) {
        e.stopPropagation();
        
        $('#toolbar > ul,#content .title-page').addClass('fix-z-index-toolbar');
        //Recalculamos el max zIndex
        var maxZIndex = Math.max.apply(null, $.map($('body > *:not(div.tipsy)'), function(e, n) {
                    if ($(e).css('position') != 'static')
                        return parseInt($(e).css('z-index')) || 1;
                }));
                
        $('#toolbar #generic_add ul').css('z-index',parseInt(maxZIndex+1));
        
        $('body').cocoblock({spinner: false});
        var menu = $(this).find('ul');
        var total_width = 0;
        menu.find('li').each(function() {
            total_width += parseInt($(this).css('width'));
        });

        menu.animate({
            width: parseInt(total_width + 1) + 'px'
        }, 1000, function() {
        });
        return false;
    });

    //Clic on option for collapsible menu with block
    $(document).on('click', '#toolbar .collapsible-menu ul a', function(e) {
        e.stopPropagation();
        $('#toolbar > ul,#content .title-page').removeClass('fix-z-index-toolbar');
        var menu = $(this).parent().parent();
        menu.animate({
            width: 0
        }, 1000, function() {
            $('body').cocoblock('unblock');
        });
        return false;
    });
}

function loadCommentsItem(container, item, type_item) {
    container = $("#" + container);
    container.cocoblock();
    $('#comments_item_conveyor').perfectScrollbar('destroy');
    container.load(jsVars.refreshCommentItemsAx + '/' + item + '/' + type_item, function(data) {
        $('body').cocoblock('unblock');
        //$('#Demo').perfectScrollbar('update');
        $('#comments_item_conveyor').perfectScrollbar({suppressScrollX: true});
    });
}

function initAutocompleteCategory() {
    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
        },
        _renderMenu: function(ul, items) {
            var that = this,
                    currentCategory = "";
            $.each(items, function(index, item) {
                var li;
                var cat = item.category.split('|');
                item.category = cat[1];
                item['categoryid'] = cat[0];

                if (item.category != currentCategory) {
                    ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                    currentCategory = item.category;
                }
                li = that._renderItemData(ul, item);
                if (item.category) {
                    li.attr("aria-label", item.category + " : " + item.label);
                }
            });
        }
    });
}

/**
 * Utilizada para limpiar de caracteres html y espacios &nbsp; una cadena y asi validar que no este vacia
 */
function get_plain_data(string) {
    var plain_data = string.replace(/<[^>]*>/g, '');
    plain_data = plain_data.replace(/&nbsp;/g, '');
    return plain_data;
}

function is_empty_editor(editor_string) {
    editor_string = get_plain_data(editor_string);
    return is_empty_string(editor_string);
}
function is_empty_string(string) {
    return string.length <= 0 ? true : false;
}

function timestampToUsDate(date_to_transform){
    var datetime = date_to_transform.split(' ');
    var date = datetime[0].split('/');
    if(date.length==3){//Convertir
        var transformed = '';
        if(jsVars.systemLanguage=='es'){
            transformed = getMonthName(date[1]) + ' ' + date[0] + ', ' +date[2];
        }else{
            transformed = getMonthName(date[0]) + ' ' + date[1] + ', ' +date[2];
        }
        return transformed;
    }else{//ya esta convertido, es actualizacion
        return date_to_transform;
    }
}

function getMonthName(month_index) {
        var months = [];
        months['01'] = 'Ene';
        months['02'] = 'Feb';
        months['03'] = 'Mar';
        months['04'] = 'Abr';
        months['05'] = 'May';
        months['06'] = 'Jun';
        months['07'] = 'Jul';
        months['08'] = 'Ago';
        months['09'] = 'Sep';
        months['10'] = 'Oct';
        months['11'] = 'Nov';
        months['12'] = 'Dic';

        var months_en = [];
        months_en['01'] = 'Jan';
        months_en['02'] = 'Feb';
        months_en['03'] = 'Mar';
        months_en['04'] = 'Apr';
        months_en['05'] = 'May';
        months_en['06'] = 'Jun';
        months_en['07'] = 'Jul';
        months_en['08'] = 'Aug';
        months_en['09'] = 'Sep';
        months_en['10'] = 'Oct';
        months_en['11'] = 'Nov';
        months_en['12'] = 'Dec';

        var lang = jsVars.systemLanguage;
        return lang == 'es' ? months[month_index] : months_en[month_index];
    }