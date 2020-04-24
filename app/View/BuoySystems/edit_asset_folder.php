<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file edit_item.php
 *     View layer for action editItem of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<style>
    #edit_item_conveyor_form{    
        width: 320px;
        height: 210px;
    }
</style>
<?php
if ($response['success']) {
    $secureItemParams = $this->Utilities->encodeParams($item_id);
    $urlEdit = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'processEditAssetFolder', $secureItemParams['item_id'], $secureItemParams['digest']));
    ?>
    <form id="edit_item_conveyor_form" action="<?php echo $urlEdit; ?>" class="fancy_form">
        <input type="hidden" name="assetId" value="<?php echo $item['AssetMetadata']['id']; ?>" class=''/>
        <div class="fancy-content">
            <div class="full-controls">
                <div class="conveyor-label"><?php echo __('Name', true); ?></div>
            </div>
            <div class="full-controls">
                <input type="text" value="<?php echo $item['FolderApp']['name']; ?>" name="item_name" id="item_name" class="validate[required,maxSize[50]] main-input" maxlength="50"/>
            </div>
            <div class="full-controls">
                <div class="conveyor-label"><?php echo __('Unique id tag', true); ?></div>
            </div>
            <div class="full-controls">
                <input type="text" value="<?php echo $item['AssetMetadata']['unique_id_tag']; ?>" name="unique_id" id="unique_id" class="validate[required,maxSize[50]] main-input" maxlength="50"/>
            </div>
        </div>
        <?php if(in_array($credentials['role'], [UsuariosEmpresa::IS_CLIENT])): ?>
        <?php else: ?>
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="update_item_conveyor" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
            </section>
        </div>
        <?php endif; ?>
    </form>
    <?php
} else {
    echo json_encode($response);
}