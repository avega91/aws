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
$secureItemParams = $this->Utilities->encodeParams($buoy_data['id']);
$urlSaveComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'saveCommentItem', $secureItemParams['item_id'], $secureItemParams['digest']));

?>
<div class="title-page buoys-section">
    <?php echo $buoy_data['name']; ?>
</div>
<div class="breacrum-page">
    <?php $this->Utilities->createFolderBreadcrum($folderBreadcrum); ?>
</div>

<?php if($is_folder_sheet): ?>
<div class="full-page" id="items_folder_wrapper">
    <div class="datatable-page">
        <?php echo $this->element('Archives/list_items');?>
    </div>
</div>
<?php else: ?>
<div class="full-page" id="items_folder_wrapper">
    <!--<div class="data-page">
        <div class="info-section">
            <div id="description_item">
            </div>
            <div class="fancy_textarea">
                <input type="hidden" value="comments_item_conveyor"/>
                <textarea placeholder="<?php echo __('Agregar comentario', true); ?>"></textarea>                                
                <button type="button" class="contiButton" rel="<?php echo $urlSaveComment; ?>" alt="folder_app"><?php echo __('Guardar', true); ?></button>            
                <button type="button" class="contiButton cancel"><?php echo __('Cancelar', true); ?></button>            
            </div>
            <div id="comments_item_conveyor" class="comments-item-container">                
                <?php
                $this->Content->printCommentsItem($comments_item);
                ?>
            </div>
        </div>
        <div class="data-section conveyors" id="items_folder_wrapper"></div>
    </div>-->
</div>
<?php endif; ?>