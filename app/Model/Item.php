<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Item.php
 *     Empty model
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class Item extends AppModel {
    const USCONVEYOR = 'UsConveyor';
    const CONVEYOR = 'Conveyor';
    const TRACKING_CONVEYOR = 'TrackingConveyor';
    const IMAGE = 'Image';
    const VIDEO = 'Movie';
    const FOLDER = 'Bucket';
    const FOLDER_FILE = 'BucketFile';
    const FOLDERYEAR = 'BucketYear';
    const REPORT = 'Report';
    const NOTE = 'Note';
    const FILE = 'Archive';
    const SMARTVIEW = 'Smartview';
    
    const ULTRASONIC = 'Ultrasonic';
    const DATASHEET = 'Datasheet';
    
    const LOG = 'Log';    
    
    const NEWS = 'Noticia';
    const COMPANY = 'Company';

    const IS_CLIENT_IMAGE = 'Image';
    const IS_CLIENT_VIDEO = 'Video';
    const IS_CLIENT_REPORT = 'Report';
    const IS_CLIENT_NOTE = 'Note';
    const IS_CLIENT_FOLDER = 'Folder';
    const IS_CLIENT_FILE = 'File';
    
    public $name = 'Item';
}
