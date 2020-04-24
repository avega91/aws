<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Group.php
 *     Model for groups table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class Group extends AppModel {
    const OPEN = 0;
    const CLIENT = 20;
    const DISTRIBUTOR = 40;
    const MANAGER = 50;
    const ADMIN = 60;
    const REGION_MANAGER = 80;
    const COUNTRY_MANAGER = 90;
    const MARKET_MANAGER = 95;
    const MASTER = 100;
    
    public $name = 'Group';
    public $useTable = false;

}
