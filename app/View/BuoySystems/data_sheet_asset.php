<?php /*
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
$securFolderParams = $this->Utilities->encodeParams($assetFolder['FolderApp']['id']);
$urlDownloadDatasheet = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'downloadDatasheetAsset', $securFolderParams['item_id'], $securFolderParams['digest']));

$canEditConveyor = isset($credentials['permissions'][IElement::Is_Conveyor]) && in_array('edit', $credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
?>
<style>
    <?php if(!$canEditConveyor): ?>
    .edit-conveyor{
        width: 0 !important;
        height: 0 !important;
        z-index: -1;
    }
    <?php endif; ?>
    
    .info-section #conveyor_menu li > div {
        display: block;
    }
    .info-section #conveyor_menu li > div table{
        border-collapse: collapse;
        padding: 0;
        width: 100%;
    }
    .info-section #conveyor_menu li > div table tr > td:first-child{
        width: 70%;
    }
    .info-section #conveyor_menu li > div table tr > td:last-child{
        padding: 0px 10px 5px;
    }

    [type="radio"]:checked,
    [type="radio"]:not(:checked) {
        position: absolute;
        left: -9999px;
    }
    [type="radio"]:checked + label,
    [type="radio"]:not(:checked) + label
    {
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        line-height: 20px;
        display: inline-block;
        color: #FFA500;
    }
    [type="radio"]:checked + label:before,
    [type="radio"]:not(:checked) + label:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 18px;
        height: 18px;
        border: 1px solid #ddd;
        border-radius: 100%;
        background: #fff;
    }
    [type="radio"]:checked + label:before{
        border: 1px solid #FFA500;
    }

    [type="radio"]:checked + label:after,
    [type="radio"]:not(:checked) + label:after {
        content: '';
        width: 12px;
        height: 12px;
        background: #FFA500;
        position: absolute;
        top: 4px;
        left: 4px;
        border-radius: 100%;
        -webkit-transition: all 0.2s ease;
        transition: all 0.2s ease;
    }
    [type="radio"]:not(:checked) + label:after {
        opacity: 0;
        -webkit-transform: scale(0);
        transform: scale(0);
    }
    [type="radio"]:checked + label:after {
        opacity: 1;
        -webkit-transform: scale(1);
        transform: scale(1);
    }

    .data-section .disclaimer-pages .alert-box{
        margin-left: 0px !important;
    }

    .button-page-menu ul li {
        overflow: inherit;
    }
    .button-page-menu ul li a {
        width: 180px;
    }

</style>
<div class="title-page buoys-section tooltiped" title="<?php echo __('Metadata %s',$assetFolder['FolderApp']['name']); ?>">
    <?php echo __('Metadata %s',$assetFolder['FolderApp']['name']); ?>
</div>
<div class="breacrum-page">
    <?php $this->Utilities->createFolderBreadcrum($folderBreadcrum); ?>
</div>
<div class="full-page">
    <div class="data-page">
        <input type="hidden" id="urlDownloadDatasheet" value="<?php echo $urlDownloadDatasheet; ?>"/>
        <div class="data-section">
            <?php
            $this->Content->printDatasheetAsset($assetFolder);
            ?>
        </div>
    </div>