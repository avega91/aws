<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file item.php
 *     View layer for action Item of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
$secureItemParams = $this->Utilities->encodeParams($item['id']);
$urlSaveComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'saveCommentItem', $secureItemParams['item_id'], $secureItemParams['digest']));
?>
<div class="title-page conveyors-section">
    <?php echo $item['nombre']; ?>
</div>
<div class="full-page">
    <div class="data-page">
        <div class="info-section">
            <div id="description_item">
                 <?php
                    switch ($type_item) {
                        case Item::IMAGE: case Item::VIDEO:   
                            echo $item['descripcion'];
                        break;
                    }
                ?>
            </div>
            <div class="fancy_textarea" data-section="9" data-intro="<?php echo __('tutorial_comentario_carpeta',true);?>" data-position="bottom">
                <input type="hidden" value="comments_item_conveyor"/>
                <textarea placeholder="<?php echo __('Agregar comentario', true); ?>"></textarea>                                
                <button type="button" class="contiButton" rel="<?php echo $urlSaveComment; ?>" alt="<?php echo $type_item; ?>"><?php echo __('Guardar', true); ?></button>            
                <button type="button" class="contiButton cancel"><?php echo __('Cancelar', true); ?></button>            
            </div>
            <div id="comments_item_conveyor" class="comments-item-container">                
                <?php
                $this->Content->printCommentsItem($comments_item);
                ?>
            </div>
        </div>
        <div class="data-section conveyors" id="items_folder_wrapper">            
            <?php
            switch ($type_item) {
                case Item::FOLDER:
                    //$this->Content->printGraphicFolderItems($folder_items, $item['id']);
                    break;
                case Item::IMAGE:                    
                    echo '<div class="item-single-image"><img src="' . $site . $item['path'] . '"/></div>';
                break;
                case Item::VIDEO:
                    if($oldPathVideo!=""):
                    ?>
                        <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="800" height="600" poster="<?php echo $site . $item['thumbnail_path']; ?>">
                            <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                        </video>

                    <?php else: ?>
                    <video
                            id="my-player"
                            class="video-js vjs-default-skin"
                            controls
                            preload="none"
                            width="800" height="600"
                            poster="<?php echo $site . $item['thumbnail_path']; ?>"
                            data-setup='{}'>
                        <source src="<?php echo $pathVideo; ?>" type="video/mp4"></source>
                        <!--<source src="//vjs.zencdn.net/v/oceans.webm" type="video/webm"></source>
                        <source src="//vjs.zencdn.net/v/oceans.ogv" type="video/ogg"></source>-->
                        <p class="vjs-no-js">
                            To view this video please enable JavaScript, and consider upgrading to a
                            web browser that
                            <a href="http://videojs.com/html5-video-support/" target="_blank">
                                supports HTML5 video
                            </a>
                        </p>
                    </video>
                    <?php endif; ?>
                    <!--
                    <video id="example_video_1" class="video-js vjs-default-skin" controls preload="none" width="800" height="600" poster="<?php echo $site . $item['thumbnail_path']; ?>">
                        <p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
                    </video>-->

                    <?php
                    break;
            }
            ?>    
        </div>
    </div>
</div>
