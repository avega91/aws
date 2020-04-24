<?php
    $canDownloadItem = isset($credentials['permissions'][IElement::Is_File]) && in_array('download', $credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
    $canDeleteItem = isset($credentials['permissions'][IElement::Is_File]) && in_array('delete', $credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
?>
<table id="archives_table" class="table table-bordered table-striped dt-responsive nowrap" cellspacing="0" width="100%">
    <thead>
        <tr>
            <?php if($canDownloadItem || $canDeleteItem): ?> 
                <th class="no-sort"><input type="checkbox" id="select-all-rows"/></th>
            <?php endif; ?>
            <th class="no-sort"><span class="favorite-header"></span></th>
            <th>File name</th>
            <th>Type</th>
            <th>Size</th>
            <th>Uploaded</th>
            <th>By</th>
            <?php if($canDownloadItem || $canDeleteItem): ?> 
                <th class="no-sort">Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
</table>