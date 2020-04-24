<?php
class IPermissionGroup extends AppModel {
    public $name = 'IPermissionGroup';


    public $belongsTo = array(
        'Element' => array(
            'className' => 'IElement',
            'foreignKey' => 'element_id',
            'order' => ['Element.name ASC']
        ),
    );
}