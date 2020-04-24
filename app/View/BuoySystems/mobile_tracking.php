<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file track_info,php
 *     View layer for action trackInfo of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if (!empty($conveyor)) {
    ?>
<?php echo $this->Html->script("https://maps.googleapis.com/maps/api/js?sensor=true"); ?>            
<?php echo $this->Html->script("http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"); ?>            
<?php echo $this->Html->script("http://d3ra5e5xmvzawh.cloudfront.net/live-widget/2.0/spot-main-min.js"); ?>            
    <div id="conveyor_track_data" rel="<?php echo $conveyor['tracking_code']; ?>"></div>
<?php } else { ?>
    <div>Error</div>
<?php } ?>

