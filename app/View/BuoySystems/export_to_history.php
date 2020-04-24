<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file export_to_history.php
 *     View layer for action of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2018
 */
?>
<style>
    div.confirm-export-history{
        width: 350px;
        height: 200px;
    }
</style>
<?php
if ($response['success']) {

    $secureConveyorParams = $this->Utilities->encodeParams($conveyor['Conveyor']['id']);
    $datasheetPdfUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'createDatasheetExport', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
    $exportHistoryUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'processExportHistory', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
    ?>
    <script type="text/javascript">
        var datasheetPdfUrl = "<?php echo addslashes($datasheetPdfUrl); ?>";
        var exportHistoryUrl = "<?php echo addslashes($exportHistoryUrl); ?>";
    </script>
    <div class="confirm-export-history">
        <div>
            <?php echo __("confirm_export_history",true); ?>
        </div>
        <div class="space"></div>
        <div class="space"></div>
        <div class="dialog-buttons">
            <a href="#" id="cancel_export_btn" class="default-button ladda-button" data-style="expand-left" data-size="l"><span class="ladda-label"><?php echo __('No, Cancel', true); ?></span></a>
            <a href="#" id="export_history_btn" class="link-button ladda-button" data-style="expand-left" data-size="l"><span class="ladda-label"><?php echo __('Yes, Export', true); ?></span></a>
        </div>
    </div>
    <?php
} else {
    echo json_encode($response);
}


