/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file mobile_tracking.js
 *     Events for tracking webview 
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$(document).ready(function(){
    initTrackWidget();
   $(window).resize(function() {
        initTrackWidget();
   });    
});

function initTrackWidget(){
    var trackingCode = $('#conveyor_track_data').attr('rel');
    $('#conveyor_track_data').html('');
    $('#conveyor_track_data').spotLiveWidget({
        feedId: trackingCode,
        width: $(window).width(),
        height: $(window).height()-10
    }); 
}