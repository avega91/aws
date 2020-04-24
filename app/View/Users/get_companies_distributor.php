<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file get_companies_distributor.php
 * @description
 *
 * @date 05, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
?>
<style>
    .empty-data{
        margin-top: 0px;
        padding: 20px;
    }
    .distributors-list .clients-distributor{
        background-color: #ededed !important;
    }
    .distributors-list .clients-distributor .row-data{
        padding-left: 45px !important;
    }
</style>
<?php
$this->Content->printCompanies($dist_companies, Group::DISTRIBUTOR);
