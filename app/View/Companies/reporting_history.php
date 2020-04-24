<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file reporting_history.php
 *     View layer for action reportingHistory of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

?>
<html>
    <head>
        <style>
            @page { margin: 100px 20px 80px 20px;}
            .page-content{ page-break-before: always; }
            #header { position: fixed; left: 0px; top: -100px; right: 0px; height: 100px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}            
            #logo_contitech{ position: fixed; left: 440px; top: -100px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}
            #footer { position: fixed; left: 0px; bottom: -80px; right: 0px; height: 80px; background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 440px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -55px; text-align:center; }

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
                font-weight: normal;
            }
            .title-conveyor-download{
                color: #ffa500;
                font-size: 35px;
                font-family: "dejavu sans condensed";
                margin: 0 0 20px 0;
                padding: 0;
                font-weight: normal;
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
            
            table{
                border-collapse: collapse;
            }
            th{
                background: #f0f5eb;
                font-family: "dejavu sans condensed";
                font-weight: normal;
                font-size: 12px;
                padding: 10px;
            }
            th.created{
                width: 80px;
                text-align: center;
            }
            th.created_by{
                width: 150px;
                text-align: center;
            }
            th.conveyor{
                width: 80px;
                text-align: center;
            }
            th.title{
                width: 190px;
                text-align:left;
            }
            td{
                padding: 10px;
                vertical-align: top;
                border-bottom: 1px solid #f0f5eb;
                font-size: 12px;
            }
            
            

        </style>
    <body>
        <div id="header">            
            <div id="logo_contitech"></div>
        </div>
        <div id="footer">
            <div id="info_conveyor">
                <div>
                    <span><?php 
                                $fechaGeneracion = $this->Utilities->transformVisualFormatDate(date('Y-m-d'),false);
                                echo __('Generado',true); 
                                ?>:
                    </span>
                    <b><?php echo $fechaGeneracion; ?></b></div>
                <div><span><?php echo __('Cliente',true); ?>:</span><b><?php echo $company['Empresa']['name']; ?></b></div>
                <div><span><?php echo __('Distribuidor',true); ?>:</span><b><?php echo $company['Distribuidor']['name']; ?></b></div>
            </div>
            <p class="page"></p>
            <div id="disclaimer">
                &copy; <?php echo date('Y'); ?> 
                <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>
        <div id="content">            
            <div class="center-aligned">
                <p class="title-download"><?php echo __('Lista de reportes generados'); ?></p>
                <p class="title-conveyor-download"><?php echo $company['Empresa']['name']; ?></p>
                <table>
                <?php 
                    $data = '<thead><tr><th class="created">'.__('Creado', true).'</th><th class="conveyor">'.__('Transportador', true).'</th><th class="title">'.__('Titulo', true).'</th><th class="created_by">'.__('Creado por', true).'</th></tr></thead>';
                    $data .= '<tbody>';
                    if(!empty($reports)){
                        foreach($reports AS $report_row){
                            $reporte = $report_row['Reporte'];
                            $conveyor = $report_row['Conveyor'];
                            $usuario = $report_row['UsuarioEmpresa'];
                            
                            $fecha_creacion = $this->Utilities->transformVisualFormatDate($reporte['creada'], true);
                            $data .= '<tr><td class="center-aligned">'.$fecha_creacion.'</td><td class="center-aligned">'.$conveyor['numero'].'</td><td>'.$reporte['nombre'].'</td><td class="center-aligned">'.$usuario['name'].'</td></tr>';
                        }
                    }else{
                        $data .= '<tr><td colspan="4">'.__('No se encontraron reportes generados para este cliente',true).'</td></tr>';
                    }
                    
                    $data .= '</tbody>';
                    echo $data;
                ?>
                    </table>
            </div>
            <div></div>  
        </div>
    </body>
</html
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('letter', 'portrait');
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream($conveyor['Conveyor']['numero'] . ".pdf", array('Attachment' => 0));
