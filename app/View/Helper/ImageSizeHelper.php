<?php

/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ImageSizeHelper.php
 *     Manage image size
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */

class ImageSizeHelper extends AppHelper {
    /*
     * Array of all available image types 
     */

    var $type = array(
        1 => 'gif',
        2 => 'jpg',
        3 => 'png'
    );

    
    /*
     * Tmp folder location for thumbs 
     */
    PUBLIC $tmpLocation = null;
    PUBLIC $tmpPath = null;

    /* Construct 
     * Link $tmpLocation to the appropriate location as well as check if folder exists and is writable, if not 
     * create folder and change permissions 
     *  
     */

    function __construct() {
        //parent::__construct();

        $path = 'files' . DS . 'small_renders' . DS;
        $dir = WWW_ROOT . $path;
        if (!is_dir($dir)) {
            @mkdir(WWW_ROOT . $path, 0774, true);
            @chmod(WWW_ROOT . $path, 0777);
        }

        $this->tmpPath = $path ;
        $this->tmpLocation = WWW_ROOT . $path ;
    }

    /* Crop 
     * crop image passed through, if no image is passed return false  
     *  
     */

    function crop($obj = null, $width = 100, $height = 100) {

        $obj = str_replace('../', '', $obj);
        $file = WWW_ROOT . $obj;
        $name = substr($obj, strrpos($obj, '/') + 1);

        // assure that file exists 
        if (is_file($file)) {

            list($w, $h, $type) = getimagesize($file);
            // if the file is an image and not a swf or undetermined file 
            if ($type) {

                $name = $width . 'x' . $height . '_' . $name;
                // check that file does not exist, if it does return image otherwise proceed 
                if ($this->checkFile($name)) {

                    // get file ext for ease of use 
                    $fileType = $this->type[$type];

                    //loop through file type and prepare image for cropping 
                    switch ($fileType) {
                        case 'gif':
                            $img = imagecreatefromgif($file);
                            break;
                        case 'jpg':
                            $img = imagecreatefromjpeg($file);
                            break;
                        case 'png':
                            $img = imagecreatefrompng($file);
                            break;
                    }

                    // determine larger side and size both appropriately 
                    if ($w > $h) {
                        if ($width > $height) {
                            $ratio = $h / $width;
                        } else {
                            $ratio = $h / $height;
                        }
                    } else {
                        if ($width > $height) {
                            $ratio = $w / $width;
                        } else {
                            $ratio = $w / $height;
                        }
                    }
                    $new_width = round($w / $ratio);
                    $new_height = round($h / $ratio);

                    // determine how far in to middle the crop should begin 
                    $src_x = ($new_width - $width) / 2;
                    $src_y = ($new_height - $height) / 2;

                    // create thumb placeholder and then create image 
                    $thumb = imagecreatetruecolor($width, $height);
                    imagecopyresized($thumb, $img, 0, 0, $src_x, $src_y, $new_width, $new_height, $w, $h);

                    imagejpeg($thumb, $this->tmpLocation . $name, 100);
                }

                return '<img src="/' .  $this->tmpPath. $name . '" rel="notprocessed">';
            } else {
                $fileType = substr($file, strrpos($file, '.') + 1);
                return 'There is no preview for file ' . $name;
            }
        } else {
            return false;
        }
    }

    /* Check File 
     * Check if file exists, if it does NOT then return true, else, return false 
     *  
     */

    function checkFile($name) {
        if (is_file($this->tmpLocation . $name)) {
            return false;
        } else {
            return true;
        }
    }
    
    /* Crop 
     * crop image passed through, if no image is passed return false  
     *  
     */

    function resize($obj = null, $width = 100, $height = 100) {
        $path = $obj;
        $obj = str_replace('../', '', $obj);
        $file = WWW_ROOT . $obj;
        $name = substr($obj, strrpos($obj, '/') + 1);

        // assure that file exists 
        if (is_file($file)) {

            list($w, $h, $type) = getimagesize($file);
            // if the file is an image and not a swf or undetermined file 
            if ($type) {

                $name = $width . 'x' . $height . '_' . $name;
                // check that file does not exist, if it does return image otherwise proceed 
                if ($this->checkFile($name)) {

                    // get file ext for ease of use 
                    $fileType = $this->type[$type];

                    //loop through file type and prepare image for cropping 
                    switch ($fileType) {
                        case 'gif':
                            $img = imagecreatefromgif($file);
                            break;
                        case 'jpg':
                            $img = imagecreatefromjpeg($file);
                            break;
                        case 'png':
                            $img = imagecreatefrompng($file);
                            break;
                    }

                    imagejpeg($img, $this->tmpLocation . $name, 10);
                }

                return '<img src="/' .  $this->tmpPath. $name . '" rel="notprocessed">';
            } else {
                $fileType = substr($file, strrpos($file, '.') + 1);
                return 'There is no preview for file ' . $name;
            }
        } else {
            return false;
        }
    }
    

}
