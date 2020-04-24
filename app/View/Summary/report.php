<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file view.php
 * @description
 *
 * @date 12, 2016
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['gauge']}]}");
$secureClientParams = $this->Utilities->encodeParams($clientId);

$market = $company["i_market_id"];
$unit_width = $market == IMarket::Is_USCanada ? "(in)" : "(mm)";
$unit_length = $market == IMarket::Is_USCanada ? "(ft)" : "(m)";

$urlSaveComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'saveCommentItem', $secureClientParams['item_id'], $secureClientParams['digest']));
$addHistory = $this->Html->url(array('controller' => 'History', 'action' => 'add', $secureClientParams['item_id'], $secureClientParams['digest']));

$addHistoryAllow = isset($credentials['permissions'][IElement::Is_History]) && in_array('add', $credentials['permissions'][IElement::Is_History]['allows']) ? true : false;
$editHistoryAllow = isset($credentials['permissions'][IElement::Is_History]) && in_array('edit', $credentials['permissions'][IElement::Is_History]['allows']) ? true : false;
$deleteHistoryAllow = isset($credentials['permissions'][IElement::Is_History]) && in_array('delete', $credentials['permissions'][IElement::Is_History]['allows']) ? true : false;
?>
<style>
    #items_history_wrapper h1{
        color: #969B96;
        margin: 0;
        font-size: 15px;
        font-weight: normal;
    }
    #items_history_wrapper h1 span{
        font-weight: bold;
    }
    table.history-data{
        background: #F0F5EB;
        border-collapse: collapse;
        width: 100%;
        margin-top: 10px;
        margin-bottom: 30px;
    }
    .history-data thead tr td{
        background: #F0F5EB;
        color: #707571;
        font-size: 12px;
        padding: 5px;
        height: 30px;
        font-weight: bold;
        text-align: center;

    }
    .history-data tbody tr{
        height: 60px;
    }
    .history-data tbody tr td{
        background: #FFF;
        font-size: 12px;
        text-align: center;
        padding: 5px 3px 5px;
        border-bottom: 1px solid #F0F5EB;
        position: relative;
    }

    .history-data tbody tr:hover td{
        background: #F0F5EB;
    }

    .history-data tbody tr td .actions{
        position: absolute;
        right: -60px;
        top: 0;
        visibility:hidden;
        opacity:0;
        filter:alpha(opacity=0);
        -webkit-transition:500ms ease;
        -moz-transition:500ms ease;
        -o-transition:500ms ease;
        transition:500ms ease;
    }
    .history-data tbody tr:hover td .actions{
        visibility:visible;
        opacity:1;
        filter:alpha(opacity=100);
    }
    .history-data tbody tr td .actions a{
        cursor: pointer;
    }


    .info-section #conveyor_menu li > div {
        display: block;
    }
    .info-section #conveyor_menu li > div table{
        border-collapse: collapse;
        padding: 0;
        width: 100%;
    }
    .info-section #conveyor_menu li > div table tr > td:first-child{
        width: 70%;
    }
    .info-section #conveyor_menu li > div table tr > td:last-child{
        padding: 0px 10px 5px;
    }

    [type="radio"]:checked,
    [type="radio"]:not(:checked) {
        position: absolute;
        left: -9999px;
    }
    [type="radio"]:checked + label,
    [type="radio"]:not(:checked) + label
    {
        position: relative;
        padding-left: 28px;
        cursor: pointer;
        line-height: 20px;
        display: inline-block;
        color: #FFA500;
    }
    [type="radio"]:checked + label:before,
    [type="radio"]:not(:checked) + label:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 18px;
        height: 18px;
        border: 1px solid #ddd;
        border-radius: 100%;
        background: #fff;
    }
    [type="radio"]:checked + label:before{
        border: 1px solid #FFA500;
    }

    [type="radio"]:checked + label:after,
    [type="radio"]:not(:checked) + label:after {
        content: '';
        width: 12px;
        height: 12px;
        background: #FFA500;
        position: absolute;
        top: 4px;
        left: 4px;
        border-radius: 100%;
        -webkit-transition: all 0.2s ease;
        transition: all 0.2s ease;
    }
    [type="radio"]:not(:checked) + label:after {
        opacity: 0;
        -webkit-transform: scale(0);
        transform: scale(0);
    }
    [type="radio"]:checked + label:after {
        opacity: 1;
        -webkit-transform: scale(1);
        transform: scale(1);
    }


    .gauge-chart {
        width: 100% !important;
    }
    .gauge-container{
        padding-bottom: 0% !important;
    }

</style>
<div class="title-page summary-section" title="<?php echo __("Summary report",true); ?>">
    <?php echo __("Summary report",true); ?>
</div>
<div class="full-page">
    <div class="data-page">
        <div class="info-section">

            <div class="button-page-menu">
                <ul id="conveyor_menu" data-section="7" data-intro="<?php echo __('tutorial_menu_vista_banda',true);?>" data-position="top">
                    <li>
                        <a class="simple-btn clickable-btn toggle-btn-link"><?php echo __d("summary","Sort by", true); ?></a>
                        <div>
                            <table>
                                <tr>
                                    <td><?php echo __d("summary",'Conveyors alphabetically',true); ?></td>
                                    <td>
                                        <p>
                                            <input type="radio" id="alphabetically" name="order-group" <?php if($selected_order=="alphabetically"): ?> checked <?php endif; ?>>
                                            <label for="alphabetically">&nbsp;</label>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo __d("summary",'Shortest abrasion life left', true); ?> </td>
                                    <td>
                                        <p>
                                            <input type="radio" id="abrasion" name="order-group" <?php if($selected_order=="abrasion"): ?> checked <?php endif; ?>>
                                            <label for="abrasion">&nbsp;</label>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo __d("summary",'Specifications', true); ?> </td>
                                    <td>
                                        <p>
                                            <input type="radio" id="specification" name="order-group" <?php if($selected_order=="specification"): ?> checked <?php endif; ?>>
                                            <label for="specification">&nbsp;</label>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?php echo __d("summary",'Width (lower to higher)', true); ?> </td>
                                    <td>
                                        <p>
                                            <input type="radio" id="width" name="order-group" <?php if($selected_order=="width"): ?> checked <?php endif; ?>>
                                            <label for="width">&nbsp;</label>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </li>

                </ul>
            </div>


        </div>
        <div id="items_history_wrapper" class="data-section conveyors">
            <table class="history-data">
                <thead>
                <tr>
                    <td><?php echo __d("summary",'Conveyor name', true); ?></td>
                    <td><?php echo __d("summary",'Manufacturer', true); ?></td>
                    <td><?php echo __d("summary",'Family', true); ?></td>
                    <td><?php echo __d("summary",'Compounds', true); ?></td>
                    <td width="70px"><?php echo __d("summary",'Specification', true); ?></td>
                    <td width="70px"><?php echo __d("summary",'Width Summary', true); ?> <? echo $unit_width; ?></td>
                    <td><?php echo __d("summary",'Length Summary', true); ?> <? echo $unit_length; ?></td>
                    <td width="50px"><?php echo __d("summary",'Installed date', true); ?></td>
                    <td width="50px"><?php echo __d("summary",'Years on system', true); ?></td>
                    <td width="100px"><?php echo __d("summary",'Estimated remaining lifetime (years)', true); ?></td>
                    <td width="50px"><?php echo __d("summary",'Ultrasonic gauge', true); ?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($tableData AS $data):
                    $gauge = $data["gauge"];
                    $installation_belt = $data["installation_date"];
                    $fancy_inst_date = "-";
                    if(!is_null($installation_belt) && $installation_belt!="0000-00-00"){
                        $fancy_inst_date = $this->Utilities->timestampToUsDate($installation_belt);
                    }
                    $remain_lifetime_years = $data["remain_lifetime_years"] > 15 ? "15+" : $data["remain_lifetime_years"];
                    ?>
                    <tr>
                        <td><?php echo $data["number"]; ?></td>
                        <td><?php echo $data["manufacturer"]; ?></td>
                        <td><?php echo $data["family"]; ?></td>
                        <td><?php echo $data["compound"]; ?></td>
                        <td><?php echo $data["recommended"]; ?></td>
                        <td><?php echo $data["width"]; ?></td>
                        <td><?php echo $data["length"]; ?></td>
                        <td><?php echo $fancy_inst_date; ?></td>
                        <td><?php echo $data["years_on_system"]; ?></td>
                        <td><?php echo $remain_lifetime_years; ?></td>
                        <td>
                            <?php if(!empty($gauge)): ?>
                            <div class="gauge-container">
                                <?php echo $this->Utilities->printAbrasionLife($gauge, $id = $data["conveyor_id"]); ?>
                            </div>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>