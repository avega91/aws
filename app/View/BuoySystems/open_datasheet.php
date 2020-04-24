<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file data_sheet.php
 *     View layer for action dataSheet of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
$secureConveyorParams = $this->Utilities->encodeParams($conveyor['Conveyor']['id']);
$urlSaveComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'saveCommentItem', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
$urlDownloadDatasheet = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'downloadDatasheet', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
?>
<div class="title-page conveyors-section">
<?php echo $conveyor['Conveyor']['numero']; ?>
</div>
<div class="full-page">
    <div class="data-page">        
        <div class="data-section">
<?php $this->Content->printResponsiveDatasheetConveyor($conveyor); ?>
        </div>
    </div>
</div>
