<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 8/3/17
 * Time: 6:26 PM
 */
class SalespersonCompany extends AppModel {

    public $name = 'SalespersonCompany';
    public $useTable = 'salesperson_companies';

/*
    //in belongs to the fk field is in the same table
    public $belongsTo = array(
        'SharedDistributors' => array(
            'className' => 'Empresa',
            'foreignKey' => 'company_id'
        )
    );*/


/*
//good
    public $hasAndBelongsToMany = array (
        'Company' => array (
            'className'             => 'Empresa',
            'joinTable'             => 'salesperson_companies',
            'foreignKey'            => 'id',
            'associationForeignKey' => 'company_id',
            'unique'                => false
        )
    );
*/

/*
    public $hasMany = array(
        'PermissionsForGroup' => array(
            'className' => 'IPermissionGroup',
            'foreignKey' => 'group_id'
        )
    );*/
}