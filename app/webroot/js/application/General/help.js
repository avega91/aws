$(document).ready(function() {
    /********************************************************************************************************************
     SIMPLE ACCORDIAN STYLE MENU FUNCTION
     ********************************************************************************************************************/
    $('div.accordionButton').click(function() {
        //cerramos los acordiones abiertos
        $('div.accordionContent').slideUp('normal');        
        $(this).siblings().removeClass('clicked-accordion');
        if(!$(this).hasClass('clicked-accordion')){
            $(this).addClass('clicked-accordion');
            $(this).next().slideDown('normal');
        }else{
            $(this).removeClass('clicked-accordion');
        }
    });

    /********************************************************************************************************************
     CLOSES ALL DIVS ON PAGE LOAD
     ********************************************************************************************************************/
    $("div.accordionContent").hide();

    $('#conti_menu a').click(function(e) {
        e.stopPropagation();
        $('#conti_menu a').removeClass('active');
        $(this).attr('class', 'active');
        var target = $(this).attr('rel');
        $('#' + target).siblings().hide();
        $('#' + target).show().removeClass('hidden');
        return false;
    });
});