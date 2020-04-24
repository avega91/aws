<?php
class AssetMetadata extends AppModel {
    public $name = 'AssetMetadata';

    
    public $belongsTo = array(
        'Asset' => array(
            'className' => 'FolderApp',
            'foreignKey' => 'folder_app_id',
            'fields' => ['Asset.id', 'Asset.name', 'Asset.buoy_system_id', 'Asset.client_id'],
            'conditions' => ['Asset.deleted' => 0]
        )
    );
}
