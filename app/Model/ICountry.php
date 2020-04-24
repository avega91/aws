<?php
class ICountry extends AppModel {
    public $name = 'ICountry';

    public $hasMany = array(
        'Regions' => array(
            'className' => 'IRegion',
            'foreignKey' => 'country_id'
        )
    );
}