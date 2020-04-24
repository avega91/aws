$(document).ready(function() {
    if(jsVars.systemLanguage=='es'){
        $('#content .full-page').hide();
    }
    $('#content .title-page').css('cursor','pointer');
    $('#content .title-page').click(function(){
        //cerramos los acordiones abiertos
        $('div.full-page').slideUp('normal');       
        $(this).siblings().removeClass('clicked-title');
        if(!$(this).hasClass('clicked-title')){
            $(this).addClass('clicked-title');
            $(this).next().slideDown('normal');
        }else{
            $(this).removeClass('clicked-title');
        }
    });
    
});