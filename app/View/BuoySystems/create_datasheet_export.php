<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file download_datasheet.php
 *     View layer for action downloadDatasheet of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<html>
    <head>
        <style>
            @page { margin: 110px 20px 80px 20px;}
            .page-content{ page-break-before: always; }
            #header { position: fixed; left: 0px; top: -110px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}            
            #logo_contitech{ position: fixed; left: 440px; top: -110px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}            
            #footer { position: fixed; left: 0px; bottom: -80px; right: 0px; height: 80px; background-image: url('<?php echo $site._LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 440px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -55px; text-align:center; }

            p{
                margin:0;
                padding:0;
                line-height: 1.5em;
            }
            #info_conveyor{                
                color: #707571;                
                position: fixed; 
                bottom: -20px;
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
            
            .page{
                font-size: 9px;
                color: #707571;
                font-family: "dejavu sans condensed";
                position: fixed; 
                bottom: -30px;
                text-align:center;   
            }
            #footer .page:after { content: counter(page, decimal); }

            .center-aligned{
                text-align: center;
            }
            .title-datasheet-download{
                color: #707571;
                font-size: 25px;
                font-family: "dejavu sans condensed";
                margin: 40px 0 0 0;
                padding: 0;
            }
            .title-conveyor-download{
                color: #ffa500;
                font-size: 35px;
                font-family: "dejavu sans condensed";
                margin: 0;
                padding: 0;
            }
            
            .datasheet-list{
                border-spacing: 20px;
                border-collapse: separate;
            }
            .datasheet-list tr td ul{
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .datasheet-list tr td{
                vertical-align: top;
                width: 250px;                
            }
            
            .datasheet-list tr td ul li{
                font-family: "dejavu sans condensed";
                font-size: 9px;
                color: #707571;
                border-bottom:1px dotted #efefef;
                padding: 1px 0 1px 0;
            }
            
            .datasheet-list tr td ul li div:first-child{
                width: 130px;
                display: inline-block;
            }
            .datasheet-list tr td ul li div:last-child{
                width: 90px;
                display: inline-block;
                text-align: right;
                color: #ed760a;
                position: relative;
                padding-right: 30px;
            }

            .datasheet-list tr td ul li div:last-child a{
                position: absolute !important;
                top: 0 !important;
                right: 0 !important;
                width: 29px !important;
                text-align: left !important;
                display: block;
            }
            
            .datasheet-list tr td h1{
                font-family: "dejavu sans";
                font-weight: normal;
                margin: 0;
                padding: 0;
                font-size: 13px;
                border-bottom: 2px solid #ffa500;
                color: #707571;
                padding-top:20px;
            }
            
            
        </style>
    <body>
        <div id="header">
            <div class="center-aligned">
                <p class="title-datasheet-download"><?php echo __("Ficha tecnica transportador", true); ?></p>
            </div>
            <div class="center-aligned">
                <p class="title-conveyor-download"><?php echo $conveyor['Conveyor']['numero']; ?></p>
            </div>
            <div id="logo_contitech"></div>
        </div>
        <div id="footer">
            <div id="info_conveyor">
                <div>
                    <span><?php 
                                $fechaActualizacion = $this->Utilities->transformVisualFormatDate($conveyor['Conveyor']['actualizada'],true);
                                echo __('Ultima modificacion',true); 
                                ?>:
                    </span>
                    <b><?php echo $fechaActualizacion; ?></b></div>
                <div><span><?php echo __('Cliente',true); ?>: </span><b><?php echo $conveyor['Empresa']['name']; ?></b></div>
                <div><span><?php echo __('Distribuidor',true); ?>: </span><b><?php echo $conveyor['Distribuidor']['name']; ?></b></div>
            </div>
            <p class="page"></p>
            <div id="disclaimer">
                &copy; <?php echo date('Y'); ?> 
                <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>
        <div id="content">            
            <?php if($conveyor['Conveyor']['is_us_conveyor']): $this->Content->printDatasheetConveyorPreviewUs($conveyor); else: $this->Content->printDatasheetConveyorPreview($conveyor); endif; ?>
        </div>
    </body>
</html
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('letter', 'portrait');
$dompdf->load_html($html);
$dompdf->render();

$output = $dompdf->output();
$name_file = $this->Utilities->sanitize($conveyor['Conveyor']['numero']). '-' .time(). '.pdf';
$path = "uploads/tmp/";
$file = $path.$name_file;
file_put_contents($file, $output);
$response['file'] = $file;
echo json_encode($response);