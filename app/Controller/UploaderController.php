<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file UploaderController.php
 *     Management of actions for uploader
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

App::import('Vendor', 'VideoEncoder', array('file' => 'VideoEncoder/VideoEncoder.php'));

class UploaderController extends AppController {

    const NICUPLOAD = 'uploads/homemsg/';
    const NICUPLOAD_FILES = 'uploads/wysiwyg/';
    const NEWS_TMP_PORTADA = 'uploads/tmpnews/';
    const IS_GIF = 'image/gif';
    const IS_JPG = 'image/jpeg';
    const IS_PNG = 'image/png';

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = false;
    }

    public function index() {
        $this->redirect(array('controller' => 'Index', 'action' => 'index'));
    }

    public function createGenericCropImage() {
        if ($this->request->is('post')) {
            $response = array('success' => false);

            $params = $this->request->data; //Get another post values
            parse_str($params['formdata'], $data); //parseamos el parametro donde viene la info del form                
            $img_src = $data['avatar_src'];          
            $crop_data = json_decode(stripslashes($data['avatar_data']), true);
            $crop_size = json_decode(stripslashes($data['avatar_size']), true);
            $img = new File($img_src);
            if ($img->exists()) {
                $type_img = $img->mime();                
                $src_img=false;
                switch ($type_img) {
                    case self::IS_GIF:
                        $src_img = imagecreatefromgif($img_src);
                    break;
                    case self::IS_JPG:
                        $src_img = imagecreatefromjpeg($img_src);
                    break;
                    case self::IS_PNG:
                        $src_img = imagecreatefrompng($img_src);
                    break;
                }

                if ($src_img) {
                    //mail("elalbertgd@gmail.com","sizes 2", print_r(array($crop_size, $crop_data), true));
                    $dst_img = imagecreatetruecolor($crop_size['w'], $crop_size['h']); //generate edge with the required size (this create a black edge)
                    //$dst_img = imagecreate($crop_size['w'], $crop_size['h']); //generate edge with the required size (this create a black edge)
                    //$color_fondo = imagecolorallocate($dst_img, 255, 0, 255);
                    if($type_img==self::IS_PNG){
                        imagealphablending( $dst_img, false );
                        imagesavealpha( $dst_img, true );
                    }

/*
                    $blanco = imagecolorallocate($dst_img, 255, 0, 0);
                    imagefilledrectangle($dst_img, 0, 0, $crop_size['w'], 100, $blanco);*/

                    //$result = imagecopyresampled($dst_img, $src_img, 0, 0, $crop_data['x1'], $crop_data['y1'], $crop_size['w'], $crop_size['h'], $crop_data['width'], $crop_data['height']);
                            //imagecopyresampled($dst_image , $src_image , $dst_x , $dst_y , $src_x , $src_y , $dst_w , $dst_h , $src_w , $src_h )
                    //imagecopyresampled was generating black border
                    $result = imagecopyresized($dst_img, $src_img, 0, 0, $crop_data['x1'], $crop_data['y1'], $crop_size['w'], $crop_size['h'], round($crop_data['width']), round($crop_data['height']));

                    //background for zoommed images
                    //$bgcolor = imagecolorallocate($dst_img, 255, 255, 255);
                    //imagefill($dst_img, 0, 0, $bgcolor);

                    $color_fondo = imagecolorallocate($dst_img, 255, 255, 255);
                    imagefill($dst_img, 0, 0, $color_fondo);

                    //Water Mark logo
                    if(isset($data['avatar_logo']) && trim($data['avatar_logo'])!='' && file_exists($data['avatar_logo'])){
                        $stamp = imagecreatefrompng($data['avatar_logo']);
                        $marge_right = 10;
                        $marge_bottom = 10;
                        $sx = imagesx($stamp);
                        $sy = imagesy($stamp);
                        imagecopy($dst_img, $stamp, 0, imagesy($dst_img) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));
                        //End watermark
                    }

                    //path for new image
                    $relative_path = pathinfo($img_src);
                    $relative_path = $relative_path['dirname'];
                    $name_file = $this->Core->sanitize($img->name(), true, true);
                    $img_src = $relative_path . '/' . $name_file . 'crop.' . $img->ext();

                    if ($result) {
                        switch ($type_img) {
                            case self::IS_GIF:
                                $result = imagegif($dst_img, $img_src);
                                break;
                            case self::IS_JPG:
                                $result = imagejpeg($dst_img, $img_src);
                                break;
                            case self::IS_PNG:
                                $result = imagepng($dst_img, $img_src);
                                break;
                        }

                        if ($result) {
                            $img->delete();
                            imagedestroy($dst_img);
                            $response['success'] = true;
                            $response['link'] = $this->site . $img_src;
                            $response['relative_path'] = $img_src;
                        } else {
                            $response['msg'] = __('Error al guardar imagen', true);
                        }
                    } else {
                        $response['msg'] = __('Error al procesar imagen', true);
                    }
                } else {
                    $response['msg'] = __('Error al procesar imagen', true);
                }
            } else {
                $response['msg'] = __('El archivo no existe', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
    
    public function uploadNicEditReport() {
        $response = array();
        if (isset($_FILES['image'])) {
            //Get the image array of details
            $img = $_FILES['image'];
            //The new path of the uploaded image, rand is just used for the sake of it
            $path = self::NICUPLOAD_FILES . rand() . $img["name"];
            //Move the file to our new path
            move_uploaded_file($img['tmp_name'], $path);
            //Get image info, reuiqred to biuld the JSON object
            $data = getimagesize($path);
            //The direct link to the uploaded image, this might varyu depending on your script location    
            $link = $this->site . $path;
            //Here we are constructing the JSON Object
            $response = array("upload" => array(
                    "links" => array("original" => $link),
                    "image" => array("width" => $data[0],
                        "height" => $data[1]
                    )
            ));
        }
        $this->set('response', $response);
    }

    public function uploadGenericFile() {
        $response = array('code_response' => 0);
        $max_file = "34457280";                         // Approx 30MB 

        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $params = $this->request->data; //Get another post values
            if (isset($params['path-upload'])) {//if path upload defined
                $name_file = rand(). $file["name"];
                $original_name = $file["name"];
                $uploadTarget = $params['path-upload'] . $name_file;
                $typeFile = $params['type-file'];
                move_uploaded_file($file['tmp_name'], $uploadTarget);
                chmod($uploadTarget, 0777);
                if ($this->isValidFile($typeFile,$file)) {
                        $link = $this->site . $uploadTarget;
                        $response["relative_path"] = $uploadTarget;
                        $response["link"] = $link;
                        $response["name_file"] = $original_name;
                }else{
                    $response['code_response'] = 2;
                    $response['reason_fail'] = __('Tipo de archivo incorrecto',true);
                }
            } else {
                $response['code_response'] = 1;
                $response['reason_fail'] = __('Ruta de archivo no definida',true);
            }
        }else{//Si no viene es que el post_max_size no lo soporto
            $response['code_response'] = 3;
        }
        $this->set('response', $response);
    }
    
    public function uploadGenericVideo() {
        $response = array('code_response' => 0);
        $max_file = "34457280";                         // Approx 30MB 

        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $params = $this->request->data; //Get another post values
            if (isset($params['path-upload'])) {//if path upload defined
                $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
                $file["name"] = $this->Core->sanitize($file["name"]).'.'.$ext;

                /*var_dump($_FILES);
                var_dump($file);
                var_dump($params);*/
                $name_file = rand(). $file["name"];
                $uploadTarget = $params['path-upload'] . $name_file;
                $typeFile = $params['type-file'];
                move_uploaded_file($file['tmp_name'], $uploadTarget);
                chmod($uploadTarget, 0777);
                if ($this->isValidFile($typeFile,$file)) {
                        $link = $this->site . $uploadTarget;
                        $thumbnail_video = "";//$this->Core->get_thumbnail_video($params['path-upload'], $name_file);
                        $response["relative_path"] = $uploadTarget;
                        $response["link"] = $link;
                        $response["thumbvideo"] = $this->site.$thumbnail_video;
                }else{
                    $response['code_response'] = 2;                    
                }
            } else {
                $response['code_response'] = 1;                
            }
        }else{//Si no viene es que el post_max_size no lo soporto
            $response['code_response'] = 3;
        }
        $this->set('response', $response);
    }

    public function uploadGenericImg() {
        $response = array('code_response' => 0);
        $max_file = "34457280";                         // Approx 30MB 

        if (isset($_FILES['file'])) {
            $img = $_FILES['file'];
            $params = $this->request->data; //Get another post values
            if (isset($params['path-upload'])) {//if path upload defined
                $uploadTarget = $params['path-upload'] . rand() . $img["name"];
                move_uploaded_file($img['tmp_name'], $uploadTarget);
                chmod($uploadTarget, 0777);
                if ($this->isImageFile($img)) {
                        $link = $this->site . $uploadTarget;
                        $im64 = file_get_contents($uploadTarget);
                        $imdata = stripslashes('data:' . $img['type'] . ';base64,' . base64_encode($im64));
                        $response["relative_path"] = $uploadTarget;
                        $response["link"] = $link;
                        $response["image"] = array("data" => $imdata, "width" => $this->getWidth($uploadTarget), "height" => $this->getHeight($uploadTarget));
                }
            } else {
                $response['code_response'] = 1;
            }
        }
        $this->set('response', $response);
    }

    public function uploadNewsPortada() {
        $response = array();
        $max_file = "34457280";                         // Approx 30MB 
        $max_width = 800;

        if (isset($_FILES['file'])) {
            $img = $_FILES['file'];
            $path = self::NEWS_TMP_PORTADA . $this->usercode;
            $temp_dir = new Folder($path); //read directory
            $temp_dir->delete(); //remove tmp files

            $temp_dir = new Folder($path, true); //Create folder 
            $uploadTarget = $path . '/' . rand() . $img["name"];

            move_uploaded_file($img['tmp_name'], $uploadTarget);
            chmod($uploadTarget, 0777);

            $width = $this->getWidth($uploadTarget);
            $height = $this->getHeight($uploadTarget);

            // Scale the image if it is greater than the width set above 
            if ($width > $max_width) {
                $scale = $max_width / $width;
                $uploaded = $this->resizeImage($uploadTarget, $width, 230, 1);
            } else {
                $scale = 1;
                $uploaded = $this->resizeImage($uploadTarget, $width, $height, $scale);
            }

            $link = $this->site . $uploadTarget;
            $response = array(
                "relative_path" => $uploadTarget,
                "link" => $link,
                "image" => array("width" => $this->getWidth($uploadTarget), "height" => $this->getHeight($uploadTarget))
            );
        }
        $this->set('response', $response);
    }

    public function uploadNicEdit() {
        $response = array();
        if (isset($_FILES['image'])) {
            //Get the image array of details
            $img = $_FILES['image'];
            //The new path of the uploaded image, rand is just used for the sake of it
            $path = self::NICUPLOAD . rand() . $img["name"];
            //Move the file to our new path
            move_uploaded_file($img['tmp_name'], $path);
            //Get image info, reuiqred to biuld the JSON object
            $data = getimagesize($path);
            //The direct link to the uploaded image, this might varyu depending on your script location    
            $link = $this->site . $path;
            //Here we are constructing the JSON Object
            $response = array("upload" => array(
                    "links" => array("original" => $link),
                    "image" => array("width" => $data[0],
                        "height" => $data[1]
                    )
            ));
        }
        $this->set('response', $response);
    }

    function getHeight($image) {
        $sizes = getimagesize($image);
        $height = $sizes[1];
        return $height;
    }

    function getWidth($image) {
        $sizes = getimagesize($image);
        $width = $sizes[0];
        return $width;
    }

    function resizeImage($image, $width, $height, $scale) {

        $oriWidth = $this->getWidth($image);
        $oriHeight = $this->getHeight($image);

        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);

        $iniXCoord = ($oriWidth / 2) - (ceil($newImageWidth / 2));
        $iniYCoord = ($oriHeight / 2) - (ceil($newImageHeight / 2));

        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        $ext = strtolower(substr(basename($image), strrpos(basename($image), ".") + 1));
        $source = "";
        if ($ext == "png") {
            $source = imagecreatefrompng($image);
        } elseif ($ext == "jpg" || $ext == "jpeg") {
            $source = imagecreatefromjpeg($image);
        } elseif ($ext == "gif") {
            $source = imagecreatefromgif($image);
        }
        imagecopyresampled($newImage, $source, 0, 0, $iniXCoord, $iniYCoord, $newImageWidth, $newImageHeight, $width, $height);
        if ($ext == "png" || $ext == "PNG") {
            imagepng($newImage, $image, 0);
        } elseif ($ext == "jpg" || $ext == "jpeg" || $ext == "JPG" || $ext == "JPEG") {
            imagejpeg($newImage, $image, 90);
        } elseif ($ext == "gif" || $ext == "GIF") {
            imagegif($newImage, $image);
        }
        chmod($image, 0777);
        return $image;
    }

    function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        $ext = strtolower(substr(basename($image), strrpos(basename($image), ".") + 1));
        $source = "";
        if ($ext == "png") {
            $source = imagecreatefrompng($image);
        } elseif ($ext == "jpg" || $ext == "jpeg") {
            $source = imagecreatefromjpeg($image);
        } elseif ($ext == "gif") {
            $source = imagecreatefromgif($image);
        }
        imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);

        if ($ext == "png" || $ext == "PNG") {
            imagepng($newImage, $thumb_image_name, 0);
        } elseif ($ext == "jpg" || $ext == "jpeg" || $ext == "JPG" || $ext == "JPEG") {
            imagejpeg($newImage, $thumb_image_name, 90);
        } elseif ($ext == "gif" || $ext == "GIF") {
            imagegif($newImage, $thumb_image_name);
        }

        chmod($thumb_image_name, 0777);
        return $thumb_image_name;
    }

    function cropImage($thumb_width, $x1, $y1, $x2, $y2, $w, $h, $thumbLocation, $imageLocation) {
        $scale = $thumb_width / $w;
        $cropped = $this->resizeThumbnailImage(WWW_ROOT . str_replace("/", DS, $thumbLocation), WWW_ROOT . str_replace("/", DS, $imageLocation), $w, $h, $x1, $y1, $scale);
        return $cropped;
    }

    /**
     * Check if media source is image file
     * @param $_FILES $mediasource $_FILES['file']
     * @return bool
     */
    function isImageFile($mediasource) {
        return preg_match('/image.*/', $mediasource['type']) ? true : false;
    }
    
    /**
     * Check if media source is image file
     * @param $_FILES $mediasource $_FILES['file']
     * @param string $type file type to check
     * @return bool
     */
    function isValidFile($type,$mediasource) {
        $type_files = array();

        if($type=='*'){
            $type_files[] = 'doc';
            $type_files[] = 'docx';
            $type_files[] = 'xls';
            $type_files[] = 'xlsx';
            $type_files[] = 'ppt';
            $type_files[] = 'pptx';
            $type_files[] = 'pdf';
            $type_files[] = 'xml';
            $type_files[] = 'ms-excel';
            $type_files[] = 'msword';
            $type_files[] = 'ms-powerpoint';
            $type_files[] = 'presentation';
            $type_files[] = 'sheet';
            $type_files[] = 'document';
            $type_files[] = 'zip';
            $type_files[] = 'bsi';
            $type_files[] = 'idw';
            $type_files[] = 'iam';
            $type_files[] = 'ipt';
            $type_files[] = 'dwg';
            $type_files[] = 'dxf';
            $type_files[] = 'bin';
        }else if($type=='file_savings'){
            $type_files[] = 'pdf';
            $type_files[] = 'jpeg';
            $type_files[] = 'jpg';
            $type_files[] = 'png';
            $type_files[] = 'gif';
        }else{
            $type_files[] = $type;
        }

        $valid = false;
        foreach ($type_files AS $type_file){
            $valid = preg_match('/'.$type_file.'.*/', $mediasource['type']) ? true : false;
            if($valid){
                break;
            }
        }

        return  $valid;
    }


}
