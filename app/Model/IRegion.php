<?php
class IRegion extends AppModel {
    public $name = 'IRegion';

/*
    public $hasMany = array(
        'Territories' => array(
            'className' => 'Empresa',
            'foreignKey' => 'region_id',
            'conditions' => ['Territories.group_bucket_id' => IGroup::TERRITORY_MANAGER]
        )
    );*/

    public $hasMany = array(
        'Territories' => array(
            'className' => 'ITerritory',
            'foreignKey' => 'region_id'
        )
    );


}