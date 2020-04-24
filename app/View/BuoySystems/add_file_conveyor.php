<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add_file_conveyor.php
 *     View layer for action addFileConveyor of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $urlSave = null;
    if(count($secure_params)>2){
        $urlSave = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'saveFileConveyor', $secure_params[0], $secure_params[1],$secure_params[2], $secure_params[3]));
    }else{
        $urlSave = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'saveFileConveyor', $secure_params[0], $secure_params[1]));
    }
?>
<style>
#add_file_conveyor_form{    
    width: 450px;
    height: 500px;
}
.disclaimer-multiupload{
    font-family: "sanslight";
    margin-bottom: 10px;
}
.disclaimer-multiupload h1{
    font-size: 20px;
    font-family: "sansbook" !important;
    margin: 0;
    font-weight: 300;
}
.ajax-upload-dragdrop{
    border: 1px solid #FFA500 !important;
    color: #FFA500 !important;
    font-size: 15px !important;
    width: 420px !important;
}
.ajax-upload-dragdrop + div {
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
}
.ajax-file-upload{
    font-family: "sanslight" !important;
    background: transparent !important;
    border: 1px solid #FFA500 !important;
    color: #FFA500 !important;
    box-shadow: none !important;
    margin-right: 50px !important;
    width: 150px !important;
    text-align: center;
}
.ajax-file-upload-error{
    font-size: 13px !important;
    font-family: "sanslight" !important;
    color: red;
    margin-top: 5px;
    background: #E1E4E1;
    padding: 7px;
    width: 430px;
}

.ajax-file-upload-container{
    overflow-y: auto !important;
    height: 210px;
    overflow-x: hidden;
    transition: height 0.3s ease-out;
}

.ajax-file-upload-progress{
    width: 96% !important;
}
.ajax-file-upload-statusbar{
    position: relative !important;
    background: #E1E4E1;
    border: none !important;
    margin-left: 0px !important;
}
.ajax-file-upload-filename{
    font-size: 12px !important;
}
.ajax-file-upload-bar{
    background-color: #999 !important;
    height: 3px !important;
}

.ajax-file-upload-bar.successfull-load{
    background-color: #2DB928 !important;
}

.custom-cancel{
    position: absolute !important;
    right: 10px !important;
    top: 5px !important;
    font-family: "sansbook";
    cursor: pointer;
}
.custom-abort{
    font-family: "sanslight";
    cursor: pointer;
    color: red;
    font-size: 14px;
}

.state-hover{
    background: #EDEDED !important;
    border: 1px dotted !important;
}



</style>
    <form id="add_file_conveyor_form" action="<?php echo $urlSave; ?>" class="fancy_form">
        <div class="fancy-content">
            <div class="disclaimer-multiupload">
                <h1>Select files</h1>
                <?php echo __('only .jpg .png .pdf .doc .xls and .ppt. Limited to 5 MB per file and video files are not allowed.', true); ?>
            </div>
             <div id="fileuploader">Upload</div>
        </div>
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="save_file_conveyor" class="progress-button uploader-btn" data-style="shrink" data-horizontal><?php echo __('Subir archivo', true); ?></button>            
            </section>
        </div> 
    </form>

    <?php
} else {
    echo json_encode($response);
}
