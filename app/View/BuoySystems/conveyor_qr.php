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
            @page { margin: 100px 20px 100px 20px;}
            #header { position: fixed; left: 0px; top: -100px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}            
            #logo_contitech{ position: fixed; left: 670px; top: -100px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}
            #footer { position: fixed; left: 0px; bottom: -100px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 670px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -50px; text-align:center; }

            #qr_code{
                position: fixed; left: 410px; top: 25px;
            }

            .qr-info{
                text-align: left;                
                font-family: "dejavu sans light";
                padding: 0 0 0 100px;
                width: 300px;
                text-align: center;
            }
            .title-datasheet-download{
                color: #000;
                font-size: 30px;
                margin: 40px 0 0 0;                
            }
            .title-conveyor-download{
                color: #000;
                font-size: 50px;
                margin: 30px 0 0 0;      
                line-height:35px;
                font-weight: 500;
                font-family: "dejavu sans condensed";
            }
            .disclaimer-qr{
                color: #000;
                font-size: 13px;
                margin: 50px 0 0 0;                     
                line-height:11px;
            }
            
            #info_conveyor{                
                color: #707571;                
                position: fixed; 
                bottom: -42px;
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

        </style>
    <body>
        <div id="header"> 
            <div id="logo_contitech"></div>
        </div>
        <div id="footer">
            <div id="info_conveyor">
                <div><span><?php echo __('Cliente',true); ?>:</span><b><?php echo $conveyor['Empresa']['name']; ?></b></div>
                <div><span><?php echo __('Distribuidor',true); ?>:</span><b><?php echo $conveyor['Distribuidor']['name']; ?></b></div>
            </div>
            <div id="disclaimer">
                &copy; <?php echo date('Y'); ?> 
                <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>
        <div id="content">
            <div class="qr-info">
                <div class="title-datasheet-download"><?php echo __("Ficha tecnica", true); ?></div>
            </div>
            <div class="qr-info">
                <div class="title-conveyor-download"><?php echo $conveyor['Conveyor']['numero']; ?></div>
            </div>
            <div class="qr-info">
                <div class="disclaimer-qr"><?php echo __('Para leer el codigo QR utilice la aplicacion movil de su preferencia. Si no cuenta con una, descarguela desde la tienda de aplicaciones disponible en su telefono inteligente.', true); ?></div>
            </div>
            <div id="qr_code">            
                <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo $urlViewConveyor;?>"/>
            </div>
        </div>
    </body>
</html
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('a4', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream($conveyor['Conveyor']['numero'] . ".pdf", array('Attachment' => 0));
