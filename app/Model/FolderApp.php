<?php
class FolderApp extends AppModel {
    public $name = 'FolderApp';

    public $belongsTo = array(
        'Client' => array(
            'className' => 'Empresa',
            'foreignKey' => 'client_id',
            'fields' => ['Client.id', 'Client.name', 'Client.parent'],
            'conditions' => ['Client.deleted' => 0]
        )
    );

    public $hasOne = 'BsMetadata';
}