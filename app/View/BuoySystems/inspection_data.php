<?php /*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file view.php
 *     View layer for action view of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

?>
<style>
    #inspections_wrapper{
        width: 85%;
        margin: 0 auto;
    }
    #inspection_map{
        background: #FFF;
        text-align: center;
        padding: 20px;
    }

    #inspection_map h1, #inspection_map h2{
        text-align: left;
        padding: 0;
        margin: 0;
        font-family: "sanslight";
        font-weight: 500;
        color: #707571;
    }
    #inspection_map h1{
        font-size: 18px;
    }
    #inspection_map h2{
        font-size: 15px;
    }
    #inspection_map img{
        margin: 0 auto;
    }

    #inspection_table{
        margin-top: 20px;
    }

    #inspection_table table{
        border-collapse: collapse;
        padding: 0;
        width: 100%;
        background: #FFF;
    }
    #inspection_table table thead{
        background: #F0F5EB;
    }
    #inspection_table table thead td{
        padding: 5px;
        font-family: "sansbook";
        font-size: 15px;
    }
    #inspection_table table tbody td{
        font-size: 14px;
        padding: 5px 0 5px;
    }

    td.cost input{
        width: 50px;
        color: #707571;
    }
    .input-error{
        color: #FFF !important;
        background-color: red !important;
    }

    tr.total-cost td{
        font-size: 13px !important;
    }
    tr.total-cost td:first-child{
        text-align: right !important;
    }
    tr.total-cost td:last-child{
        text-align: center !important;
    }

    td.status-point{
        text-transform: capitalize;
    }

    td.assignation .chosen-single{
        color: #707571 !important;
        font-family: "sanslight" !important;
    }
    td.status-point .chosen-single{
        font-family: "sanslight" !important;
    }
    td.status-point.pending, td.status-point.pending .chosen-single{
        color:#FF2D37 !important;
    }
    td.status-point.fixed, td.status-point.fixed .chosen-single{
        color:#2DB928 !important;
    }
    td.status-point.doing, td.status-point.doing .chosen-single{
        color:#004EAF !important;
    }
    td .chosen-container .chosen-results {
        color: #707571 !important;
    }
    td .chosen-container{
        border: none !important;
    }
    #inspection_table td{
        text-align: center;
    }
    #inspection_table tbody tr:not(.total-cost):hover td{
        background-color: #EFEFEF;
    }

    .ui-tooltip{
        background: #000 !important;
        color: #FFF !important;
        font-family: Arial !important;
        box-shadow: none !important;
        border: none !important;
    }
    .footer-map{
        position: relative;
    }
    .footer-map .left, .footer-map .right{
        color: #707571;
        font-weight: 400 !important;
        font-size: 15px !important;
    }
    .footer-map .right{
        position: absolute;
        right: 0;
        top: 0;
    }
    #assigned_data_form{
        width: 480px;
        height: 100px;
    }
</style>
<?php
    $costos = isset($_COOKIE['selectedCosts']) ? $_COOKIE['selectedCosts'] : "0.00|0.00|5600.00|8600.00|0.00";
    $costos = array_map('intval', explode('|', $costos));
$inspection = [
    ['PointInspection'=>['id'=>1,'location'=>'22.155890, -100.992905','x'=>'0.1082', 'y'=>'0.88','failure'=>__d('inspections','Dust accumulation'),'part'=>__d('inspections','Idler'),'position'=>__d('inspections','Feeding zone'),'recommended'=>__d('inspections','Cleaning'),'area'=>__d('inspections','Dome'),'cost'=>$costos[0], 'status'=>'pending','status_value'=>'To Do', 'assigned_to'=>'Loren Chapell']],
    ['PointInspection'=>['id'=>2,'location'=>'22.155890, -100.992905','x'=>'0.2659', 'y'=>'0.70','failure'=>__d('inspections','Released stamp'),'part'=>__d('inspections','Bend pulley'),'position'=>__d('inspections','Loading zone'),'recommended'=>__d('inspections','Repair'),'area'=>__d('inspections','Primary'),'cost'=>$costos[1], 'status'=>'pending','status_value'=>'To Do',  'assigned_to'=>'None']],
    ['PointInspection'=>['id'=>3,'location'=>'22.155890, -100.992905','x'=>'0.5971', 'y'=>'0.56','failure'=>__d('inspections','Damaged coating'),'part'=>__d('inspections','Belt'),'position'=>__d('inspections','Carry'),'recommended'=>__d('inspections','Lagging'),'area'=>__d('inspections','Trituration'),'cost'=>$costos[2], 'status'=>'doing','status_value'=>'Doing',  'assigned_to'=>'None']],
    ['PointInspection'=>['id'=>4,'location'=>'22.155890, -100.992905','x'=>'0.8143', 'y'=>'0.41','failure'=>__d('inspections','Missing cleaning'),'part'=>__d('inspections','Drive pulley'),'position'=>__d('inspections','Chute'),'recommended'=>__d('inspections','Cleaning'),'area'=>__d('inspections','Hoppers'),'cost'=>$costos[3], 'status'=>'fixed','status_value'=>'Done', 'status_value'=>'To Do',  'assigned_to'=>'None']],
    ['PointInspection'=>['id'=>5,'location'=>'22.155890, -100.992905','x'=>'0.9386', 'y'=>'0.23','failure'=>__d('inspections','Damage carcass'),'part'=>__d('inspections','Belt'),'position'=>__d('inspections','Return'),'recommended'=>__d('inspections','Splice'),'area'=>__d('inspections','Secondary'),'cost'=>$costos[4], 'status'=>'pending','status_value'=>'To Do',  'assigned_to'=>'None']],
];
?>
<script type="text/javascript">
    var inspectionData = <?php echo json_encode($inspection); ?>;
</script>

<div class="title-page conveyors-section" title="<?php echo __d('inspections','Inspections'); ?>">
    <?php echo __d('inspections','Inspection'); ?>
</div>
<div class="full-page">
    <div id="inspections_wrapper">
        <div id="inspection_map">
            <div class="header-map">
                <h1><?php echo __d('inspections','INSPECTION MAP'); ?></h1>
                <h2><?php echo __d('inspections','Conveyor:'); ?> <?php echo $conveyor['Conveyor']['numero']; ?></h2>
                <h2><?php echo __d('inspections','Inspection:'); ?> <?php echo $this->Utilities->timestampToUsDate(date('Y-m-d H:i:s')); ?></h2>
            </div>
            <?php echo $this->Html->image('profile_maps/perfil_demo.png', array('alt' => 'map', 'id'=>'image')); ?>
            <div class="footer-map">
                <h1 class="left"><?php echo __d('inspections','TAIL'); ?></h1>
                <h1 class="right"><?php echo __d('inspections','HEAD'); ?></h1>
            </div>
        </div>
        <div id="inspection_table">
            <table>
                <thead>
                    <tr>
                        <td width="50px"><?php echo __d('inspections','Issue Number'); ?></td>
                        <td width="150px"><?php echo __d('inspections','Issue'); ?></td>
                        <td width="50px"><?php echo __d('inspections','Part'); ?></td>
                        <td width="100px"><?php echo __d('inspections','Position'); ?></td>
                        <td width="100px"><?php echo __d('inspections','Recommended action'); ?></td>
                        <td width="50px"><?php echo __d('inspections','Status'); ?></td>
                        <td width="50px"><?php echo __d('inspections','Cost'); ?></td>
                        <td width="50px"><?php echo __d('inspections','Area'); ?></td>
                        <td width="100px"><?php echo __d('inspections','Assigned to'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $urlChangeStatusConveyor = "";
                        $sum = 0;
                        foreach ($inspection AS $index => $pointInspection):
                            $pointInspection = $pointInspection['PointInspection'];
                            $number = str_pad(($index+1), 2, "0", STR_PAD_LEFT);

                            $pending = $pointInspection['status'] == 'pending' ? "selected" : "";
                            $doing = $pointInspection['status'] == 'doing' ? "selected" : "";
                            $fixed = $pointInspection['status'] == 'fixed' ? "selected" : "";
                            $status_indicator = '<select rel="' . $urlChangeStatusConveyor . '">
                                        <option value="pending" ' . $pending . '>To Do</option>
                                        <option value="doing" ' . $doing . '>Doing</option>
                                        <option value="fixed" ' . $fixed . '>Done</option>
                                    </select>';

                            $sum += $pointInspection['cost'];

                     ?>
                            <tr data-id="<?php echo $pointInspection['id']; ?>">
                                <td><?php echo $number; ?></td>
                                <td><?php echo $pointInspection['failure']; ?></td>
                                <td><?php echo $pointInspection['part']; ?></td>
                                <td><?php echo $pointInspection['position']; ?></td>
                                <td><?php echo $pointInspection['recommended']; ?></td>
                                <td class="status-point <?php echo $pointInspection['status']; ?>"><?php echo $status_indicator; ?></td>
                                <td class="cost">$<input class="input-cost" type="text" value="<?php echo number_format($pointInspection['cost'],2,'.',','); ?>"></td>
                                <td><?php echo $pointInspection['area']; ?></td>
                                <td class="assignation">
                                    <div style="position:relative;">
                                        <select id="assigned_to<?php echo $pointInspection['id']; ?>" class="assignation-select select-sidebar" data-placeholder="<?php echo __d('inspections','Add'); ?>">
                                            <option></option>
                                            <option value="new"><?php echo __d('inspections','New'); ?></option>
                                            <option value="<?php echo $pointInspection['assigned_to']; ?>" selected><?php echo $pointInspection['assigned_to']; ?></option>
                                        </select>
                                        <a href="#" class="clear-tag close-stick hidden" data-url="" data-type="area_select"></a>
                                    </div>
                                    <?php //echo $pointInspection['assigned_to']; ?>
                                </td>
                            </tr>
                    <?php endforeach; ?>
                    <tr class="total-cost"><td colspan="6"><?php echo __d('inspections','Total cost'); ?></td><td>$ <span id="sumTotal"><?php echo number_format($sum,2,'.',','); ?></span></td></tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<div id="append_assigned_wrapper" class="hidden">
    <form id="assigned_data_form" action="" class="fancy_form">
        <div class="fancy-content">
            <div class="full-controls">
                <input type="text" placeholder="<?php echo __d('inspections','Name', true); ?>" name="item_name_assigned" id="item_name_assigned" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
            </div>
            <div class="space"></div>
        </div>
        <div class="dialog-buttons">
            <section>
                <button type="button" id="save_assigned" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
            </section>
        </div>
    </form>
</div>