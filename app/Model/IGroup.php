<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 8/3/17
 * Time: 6:26 PM
 */
class IGroup extends AppModel {
    const OPEN = 0;
    const CLIENT = 20;
    const CLIENT_MANAGER = 30;
    const DISTRIBUTOR = 40;
    const RUBBER_DISTRIBUTOR = 45;
    const DISTRIBUTOR_MANAGER = 50;

    const MANAGER = 50;

    const ADMIN = 60;
    const TERRITORY_MANAGER = 60;
    const REGION_MANAGER = 80;
    const COUNTRY_MANAGER = 90;
    const MARKET_MANAGER = 95;
    const MASTER = 100;

    public $name = 'IGroup';

    public $hasMany = array(
        'PermissionsForGroup' => array(
            'className' => 'IPermissionGroup',
            'foreignKey' => 'group_id'
        )
    );
}