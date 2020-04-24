homeMsgChanged = false;

$(document).ready(function() {
    //$("#content").sortable({revert: true});
    init_home_events();
});

function init_home_events() {
    $('.tipsy-item').tipsy({gravity: 's', offset: 25, fade: true, className: 'disclaimer'});
    $('.aux-btn').tipsy({gravity: 's', fade: true});
    $('.icon-block-content > div').tipsy({fade: true, className: 'premium'});
    $('#welcome_msg > div').perfectScrollbar({suppressScrollX: true});
    $('div.scrollable').perfectScrollbar({suppressScrollX: true});


    $('.panel-content.clickeable').click(function(e){
        e.stopPropagation();
        var link = $(this).data('url');
        window.location = link;
        return false;
    });
    
}
