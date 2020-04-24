<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file export_smartview.php
 *     View layer for action exportSmartview of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$header_right_image = trim($distribuidor['Empresa']['path_image']) != '' ? '' : $site . _LOGO_CONTITECH_PDF;
$image_dist = $header_right_image == '' ? '<img id="dist_image" src="'.$site . $distribuidor['Empresa']['path_image'].'"/>' : '';
?>
<html>
    <head>
        <style>
            @page { margin: 100px 20px 80px 20px;}
            .page-content{ page-break-before: always; }
            #header { position: fixed; left: 0px; top: -90px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}            
            #logo_contitech{ position: fixed; left: 440px; top: -90px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $header_right_image; ?>'); background-repeat: no-repeat; background-position: 0px 5px;}            
            #footer { position: fixed; left: 0px; bottom: -80px; right: 0px; height: 80px; background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 440px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -55px; text-align:center; }
            
            #dist_image{ width: 50px; height: 50px; position: fixed; top: -80px; right:0; }
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
            .title-download{
                color: #707571;
                font-size: 25px;
                font-family: "dejavu sans condensed";
                margin: 10px 0 10px 0;
                padding: 0;
            }

            #content{
                font-family: "dejavu sans condensed";
                color: #707571;
            }

            h1{
                font-size: 20px;
                font-family: "dejavu sans condensed";
                color: #ff2d37;
                font-weight: normal;
                clear: both;
            }
            div.note{
                margin-bottom: 10px;
                clear: both;
            }
            div.image{
                width: 185px;
                background-color: #FFF;
            }
            
            td{
                vertical-align: top;
            }
            img{
                width: 185px;
                height: 139px;
            }
            div.image div{
                text-align: center;
                font-family: "dejavu sans condensed";
                color: #707571;
                font-size: 13px;
                height: 30px;
                overflow: hidden;
                line-height: 10px;
                padding-top: 10px;
            }


        </style>
    <body>
        <div id="header">            
            <div id="logo_contitech"></div>
            <?php echo $image_dist; ?>
        </div>
        <div id="footer">
            <div id="info_conveyor">
                <div>
                    <span><?php
                        $fechaGeneracion = $this->Utilities->transformVisualFormatDate(date('Y-m-d'), false);
                        echo __('Generado', true);
                        ?>:
                    </span>
                    <b><?php echo $fechaGeneracion; ?></b></div>
                <div><span><?php echo __('Cliente', true); ?>: </span><b><?php echo $conveyor['Empresa']['name']; ?></b></div>
                <div><span><?php echo __('Distribuidor', true); ?>: </span><b><?php echo $distribuidor['Empresa']['name']; ?></b></div>
            </div>
            <p class="page"></p>
            <div id="disclaimer">
                &copy; <?php echo date('Y'); ?> 
                <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>
        <div id="content">            
            <div class="center-aligned">
                <p class="title-download"><?php echo $item['nombre']; ?></p>
            </div>
            <?php
            $conveyors_data = '';
            $imageTitle = __('Fotos', true);
            $videoTitle = __('Videos', true);
            $items_by_line = 2;
            $table_closed = false;

            if (!empty($folder_items)) {
                foreach ($folder_items AS $items) {
                    $drawed_items = 0;
                    foreach ($items AS $conveyor_item) {
                        $conveyorItem = $conveyor_item['ConveyorItem'];
                        $fecha_actualizacion = $this->Utilities->timestampToCorrectFormat($conveyorItem['actualizada'], '/');
                        switch ($conveyorItem['type_item']) {
                            case Item::NOTE:
                                $conveyorItem['contenido'] = stripslashes($conveyorItem['contenido']);
                                $conveyors_data .= '<div class="note">' . $conveyorItem['contenido'] . '</div><br/>';
                                $table_closed = true;
                            break;
                            case Item::IMAGE:
                                if ($imageTitle != '') {
                                    $conveyors_data .= '<h1>' . $imageTitle . '</h1>';
                                    $imageTitle = '';
                                }

                                if ($drawed_items == 0) {
                                    $conveyors_data .= '<table><tr>';
                                    $table_closed = false;
                                }

                                $conveyors_data .= '<td>';
                                    $conveyors_data .= '<div class="image">';
                                if (trim($conveyorItem['path']) != '') {
                                        $conveyors_data .= '<img src="' . $site . $conveyorItem['path'] . '"/>';
                                }
                                        $conveyors_data .= '<div>' . $conveyorItem['nombre'] . '</div>';
                                    $conveyors_data .='</div>';
                                $conveyors_data .= '</td>';

                                if ($drawed_items >= $items_by_line) {
                                    $conveyors_data .= '</tr></table>';
                                    $table_closed = true;
                                    $drawed_items = -1;
                                }

                                $drawed_items++;
                                break;
                            case Item::VIDEO:
                                if ($videoTitle != '') {
                                    $conveyors_data .= '<h1>' . $videoTitle . '</h1>';
                                    $videoTitle = '';
                                }

                                if ($drawed_items == 0) {
                                    $conveyors_data .= '<table><tr>';
                                    $table_closed = false;
                                }

                                $conveyors_data .= '<td>';
                                    $conveyors_data .= '<div class="image">';
                                if (trim($conveyorItem['thumbnail_path']) != '') {
                                            $conveyors_data .= '<img src="' . $site . $conveyorItem['thumbnail_path'] . '"/>';
                                }
                                            $conveyors_data .= '<div>' . $conveyorItem['nombre'] . '</div>';
                                    $conveyors_data .='</div>';
                                $conveyors_data .= '</td>';

                                if ($drawed_items >= $items_by_line) {
                                    $conveyors_data .= '</tr></table>';
                                    $table_closed = true;
                                    $drawed_items = -1;                                    
                                }
                                $drawed_items++;
                                break;
                        }
                    }
                    
                    if(!$table_closed){
                        $conveyors_data .= '</tr></table>';
                    }
                }
            }
            echo $conveyors_data;
            ?>    
        </div>
    </body>
</html
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('letter', 'portrait');
$dompdf->load_html($html);
$dompdf->render();
//$dompdf->stream($conveyor['Conveyor']['numero'] . ".pdf", array('Attachment' => 0));
$output = $dompdf->output();

$sha_company = sha1($conveyor['Conveyor']['id_company']);
$sha_conveyor = sha1($conveyor['Conveyor']['id']);
$path_file = _ABSOLUTE_PATH._COMPANY_DATA.$sha_company.'/'._CONVEYORS_FOLDER.'/'.$sha_conveyor.'/'._REPORTS_FOLDER;
new Folder($path_file, true); //true para crearlo sino existe el folder

$file_report = '';
if(!isset($_COOKIE['Reportwrited'])){//Para que no se genere 2 veces
    $file_report = $path_file.'/'.$this->Utilities->sanitize($item['nombre']).'_'.date('mdY').'_'.uniqid().'.pdf';
    file_put_contents($file_report, $output);
    $this->Utilities->saveViewReport('SmartReport', $file_report, $conveyor['Conveyor']['id'], $item['id']);
    setcookie("Reportwrited", $file_report,time()+5); //expira en 10 segundos
}else{
    $file_report = $_COOKIE['Reportwrited'];
}
    


//print the pdf file to the screen for saving
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="file.pdf"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($file_report));
header('Accept-Ranges: bytes');
readfile($file_report);