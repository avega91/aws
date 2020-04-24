<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Statistic.php
 *     Model for statistics table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class Statistic extends AppModel {
    public $name = 'Statistic';
    
    const GO_SITE = 'SITE';
    const GO_CONVEYORS = 'CONVEYORS';
    const GO_CLIENTS = 'CLIENTS';
    const GO_TOOLS = 'TOOLS';
    const GO_NEWS = 'NEWS';
    const GO_HELP = 'HELP';

    const NEW_CUSTOMER = 'ADD_CUSTOMER';
    const NEW_CONVEYOR = 'ADD_CONVEYOR';
    const POPULATE_TECHNICAL_DATA = 'POP_TECH_DATA';
    const NEW_READING_ULTRA = 'ULTRA_GAUGE';
    const NEW_ITEM_CONVEYOR = 'NEW_ITEM_CONVEYOR';
    const NEW_SAVING_STANDBY = 'SAVING_STANDBY';
    const NEW_SAVING_APPROVED = 'SAVING_APPROVED';
    const NEW_BELT_HISTORY = 'BELT_HISTORY';
}
