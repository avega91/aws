/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file conveyor_datasheet.js
 *     actions for conveyor datasheet
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$(document).ready(function() {        
    $('ul.datasheet-list > li div').perfectScrollbar({suppressScrollX: true});
    /*$('.content-comment h1 span').each(function(){
        var timeAgo = moment($(this).html()).locale(jsVars.systemLanguage).fromNow();
        $(this).html(timeAgo);
    });*/
    $('.datasheet-list span').tipsy({gravity: 's', fade: true});
    $('#download_datasheet').attr('href',$('#urlDownloadDatasheet').val());
    
});


