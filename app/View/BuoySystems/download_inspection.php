<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file conveyor_qr.php
 *     View layer for action conveyorQr of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
$secureConveyorParams = $this->Utilities->encodeParams($conveyor['Conveyor']['id']);
$urlViewConveyor = $mobile_host.$this->Html->url(array('controller' => 'Conveyors', 'action' => 'openDatasheet', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
?>
    <html>
    <head>
        <style>
            @page { margin: 70px 20px 100px 20px;}
            #header { position: fixed; left: 0px; top: -70px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}
            #logo_contitech{ position: fixed; left: 840px; top: -70px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}
            #footer { position: fixed; left: 0px; bottom: -130px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 840px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -70px; text-align:center; }


            #info_conveyor{
                color: #707571;
                position: fixed;
                bottom: -52px;
                left: 20px;
                text-align:left;
            }
            #info_conveyor div{
                line-height: 0.9em;
                font-size: 9px;
                font-family: "dejavu sans condensed";
            }
            #info_conveyor div b{
                font-weight:bold;
            }



            #inspections_wrapper{
                width: 100%;
                margin: 0 auto;
            }

            #inspection_map{
                background: #FFF;
                text-align: center;
                border: 1px solid;
                height: 300px;
            }
            #inspection_map_table{
                width: 100%;
                border-collapse: collapse;
                padding: 0;
            }


            .image-container{
                text-align: center;
            }
            .image-container img{
                height: 160px;
            }

            .header-map h1, .header-map  h2{
                text-align: left;
                padding: 0;
                margin: 0;
                font-family: "dejavu sans condensed";
                font-weight: 500;
                color: #707571;
            }
            .header-map  h1{
                font-size: 15px;
            }
            .header-map  h2{
                font-size: 13px;
            }

            #inspection_table{
                margin-top: 10px;
            }

            #inspection_table table{
                border-collapse: collapse;
                padding: 0;
                width: 100%;
            }
            #inspection_table table thead{
                background: #F0F5EB;
            }
            #inspection_table table thead td{
                padding: 2px;
                font-family: "dejavu sans condensed";
                font-size: 11px;
            }
            #inspection_table table tbody{
                background: #FFF;
            }
            #inspection_table table tbody td{
                font-size: 11px;
                padding: 2px 0 2px;
                font-family: "dejavu sans condensed";
                border-bottom: 1px solid #F0F5EB;
            }

            td.status-point{
                text-transform: capitalize;
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
            td.status-point .chosen-container .chosen-results {
                color: #707571 !important;
            }
            td.status-point .chosen-container{
                border: none !important;
            }
            #inspection_table td{
                text-align: center;
            }


            .footer-map{
                position: relative;
            }
            .footer-map .left, .footer-map .right{
                color: #707571;
                font-weight: 400 !important;
                font-size: 13px !important;
                font-family: "dejavu sans condensed";
            }
            .footer-map .right{
                right: 0;
                top: 10px;
                position: absolute;
            }

        </style>
    <body>
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
    <div id="header">
        <div id="logo_contitech"></div>
    </div>
    <div id="footer">
        <div id="info_conveyor">
            <div>
                    <span><?php
                        $fechaActualizacion = $this->Utilities->transformVisualFormatDate(date('Y-m-d H:i:s'),true);
                        echo __('Created',true);
                        ?>:
                    </span>
                <b><?php echo $fechaActualizacion; ?></b></div>
            <div><span><?php echo __('Cliente',true); ?>:</span><b><?php echo $conveyor['Empresa']['name']; ?></b></div>
            <div><span><?php echo __('Distribuidor',true); ?>:</span><b><?php echo $conveyor['Distribuidor']['name']; ?></b></div>
        </div>
        <div id="disclaimer">
            &copy; <?php echo date('Y'); ?>
            <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
        </div>
    </div>
    <div id="content">
        <div id="inspections_wrapper">
            <table id="inspection_map_table">
                <tr>
                    <td>
                        <div class="header-map">
                            <h1><?php echo __d('inspections','INSPECTION MAP'); ?></h1>
                            <h2><?php echo __d('inspections','Conveyor:'); ?> <?php echo $conveyor['Conveyor']['numero']; ?></h2>
                            <h2><?php echo __d('inspections','Inspection:'); ?> <?php echo $this->Utilities->timestampToUsDate(date('Y-m-d H:i:s')); ?></h2>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="image-container">
                            <img src="<?php echo $site;?>img/profile_maps/perfil_demo_full.png"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="footer-map">
                            <h1 class="left"><?php echo __d('inspections','TAIL'); ?></h1>
                            <h1 class="right"><?php echo __d('inspections','HEAD'); ?></h1>
                        </div>
                    </td>
                </tr>
            </table>
            <div id="inspection_table">
                <table>
                    <thead>
                    <tr>
                        <td width="10%"><?php echo __d('inspections','Issue Number'); ?></td>
                        <td width="20%"><?php echo __d('inspections','Issue'); ?></td>
                        <td width="10%"><?php echo __d('inspections','Part'); ?></td>
                        <td width="15%"><?php echo __d('inspections','Position'); ?></td>
                        <td width="15%"><?php echo __d('inspections','Recommended action'); ?></td>
                        <td width="5%"><?php echo __d('inspections','Status'); ?></td>
                        <td width="8%"><?php echo __d('inspections','Cost'); ?></td>
                        <td width="5%"><?php echo __d('inspections','Area'); ?></td>
                        <td width="10%"><?php echo __d('inspections','Assigned to'); ?></td>
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
                            <td class="status-point <?php echo $pointInspection['status']; ?>"><?php echo $pointInspection['status_value']; ?></td>
                            <td><?php echo $pointInspection['cost']; ?></td>
                            <td><?php echo $pointInspection['area']; ?></td>
                            <td><?php echo $pointInspection['assigned_to']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-cost"><td colspan="6" style="text-align: right"><?php echo __d('inspections','Total cost'); ?></td><td style="text-align: center">$ <span id="sumTotal"><?php echo number_format($sum,2,'.',','); ?></span></td></tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    </body>
    </html
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('legal', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream($conveyor['Conveyor']['numero'] . ".pdf", array('Attachment' => 0));