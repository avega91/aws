<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file generate_pdf.php
 *     View layer for action generatePdf of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

function clear_string ($string, $symbols){
    foreach ($symbols AS $symbol){
        $string_arr = explode($symbol, $string, 2);
        $string = $string_arr[0];
    }
    return $string;
}
            
?>
<html>
    <head>
        <style>
            *{
                font-family: "dejavu sans condensed";
            }
            @page { margin: 70px 20px 130px 20px;}
            .page-content{ page-break-before: always; }
            #header { position: fixed; left: -5px; top: -70px; right: 0px; height: 50px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 10px;}            
            #logo_contitech{ position: fixed; left: 645px; top: -70px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}
            #footer { position: fixed; left: 0px; bottom: -105px; right: 0px; height: 80px; background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: 630px 0px;}
            #disclaimer{ font-size: 9px; color: #707571; font-family: "dejavu sans condensed"; position: fixed;  bottom: -105px; text-align:center; }

            #info_conveyor{                
                color: #707571;                
                position: fixed; 
                bottom: -55px;
                left: 0px;
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
                bottom: -97px;
                text-align:right;   
                padding-right:10px;
            }
            #footer .page:after { content: counter(page, decimal); }

            .title-resume{
                margin: 0 auto;
                padding: 0 0 10px !important; 
                color: #707571;                                
            }
            .title-resume > div{
                display: inline;
                padding: 0; 
                border-bottom: 1px solid #f0f5eb;
            }
            .resume-conveyors{
                width:700px;                
            }
            
            .resume-idlers{
                width:425px;   
            }
            .resume-pulleys{
                width:540px;   
            }
            
            .right-aligned{
                text-align: right;
            }
            .center-aligned{
                text-align: center;
            }
            .center-aligned table{
                margin: 0 auto !important;
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
            
            .title-conveyor-download-resume{
                color: #ffa500;
                font-size: 35px;
                font-family: "dejavu sans condensed";
                margin: 0 0 0 0;
                padding: 0;
                font-weight: normal;
            }

            #content,.content{
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
                page-break-inside: auto;
            }            
            th,td{                
                font-family: "dejavu sans condensed";
                font-weight: normal;
                font-size: 11px;
                padding: 5px;
                width:97px;
                vertical-align: middle;
                page-break-inside: avoid;
                text-align: center;
                line-height: 10px;
                color: #707571;
            }
            th{
                background: #f0f5eb;
                font-weight: bold;
            }
            td{
                border-bottom: 1px solid #f0f5eb;                
                font-family: "dejavu sans condensed"
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
                        $fechaGeneracion = $this->Utilities->transformVisualFormatDate(date('Y-m-d'), false);
                        echo __('Generado', true);
                        ?>:
                    </span>
                    <b><?php echo $fechaGeneracion; ?></b></div>
                <div><span><?php echo __('Cliente', true); ?>:</span><b><?php echo $company['Empresa']['name']; ?></b></div>
                <div><span><?php echo __('Distribuidor', true); ?>:</span><b><?php echo $company['Distribuidor']['name']; ?></b></div>
            </div>
            <p class="page"></p>
            <div id="disclaimer">                
                &copy; <?php echo date('Y'); ?> 
                <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>        
        <?php
        $total_transportadores = 0;
        $total_rodillos = 0;
        $total_poleas = 0;
        
        $resumen_conveyors = array();
        $resumen_idlers = array();
        $resumen_pulleys = array();
        
        $index_config_transporter = $language == 'es' ? 'titulo' : 'titulo_en';


        $first_sheet_data = '<div id="content" ><div class="center-aligned"><p class="title-download">' . $custom_report['CustomReport']['title'] . '</p>';
        $first_sheet_data .= '<p class="title-conveyor-download">' . $company['Empresa']['name'] . '</p>';


        $data = '';
        $header_iter = 1;
        //$data_header = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
        $label_fields = utf8_encode($label_fields);
        $data_header = explode(',', $label_fields);
        $fields_selected = explode(',', $fields); //los campos que hay que poner en el reporte

        $data_body = $conveyors;

        $columns_page = 6;
        $pages_table = count($fields_selected) / $columns_page;
        $pages_table = count($fields_selected) % $columns_page == 0 ? $pages_table : (int) $pages_table + 1;
        for ($i = 0; $i < $pages_table; $i++) {
            $data .= $first_sheet_data;
            $data .= '<table>';

            //Escribimos los headers
            $data .= '<thead><tr><th>' . __('Transportador', true) . '</th>';
            for ($j = $header_iter; $j <= count($data_header); $j++) {
                $data .= '<th>' . __($data_header[$j - 1], true) . '</th>';
                if ($j % $columns_page == 0) {
                    break;
                }
            }
            $data .= '</tr></thead>';


            //escribimos los datos
            $data .= '<tbody>';
            for ($k = 0; $k < count($data_body); $k++) {//iteramos sobre los datos de body
                $conveyor = $data_body[$k]; //Obtenemos el registro                
                
                $dinamic_fields = array();
                $dinamic_fields['mat_descripcion'] = $conveyor['Material'];
                $dinamic_fields['tensor_tipo'] = $conveyor['TipoTensor'];
                $dinamic_fields['mat_grado_mat_transportado'] = $conveyor['GradoMaterial'];
                $dinamic_fields['mat_condicion_alimentacion'] = $conveyor['CondicionAlimentacion'];
                $dinamic_fields['mat_condicion_carga'] = $conveyor['CondicionCarga'];
                $dinamic_fields['mat_frecuencia_carga'] = $conveyor['FrecuenciaCarga'];
                $dinamic_fields['mat_tamanio_granular'] = $conveyor['TamanioGranular'];
                $dinamic_fields['mat_tipo_densidad'] = $conveyor['TipoDensidad'];
                $dinamic_fields['mat_agresividad'] = $conveyor['Agresividad'];

                $dinamic_fields['id_espesor_cubierta_sup'] = $conveyor['EspesorCubiertaSup'];
                $dinamic_fields['id_espesor_cubierta_inf'] = $conveyor['EspesorCubiertaInf'];
                $dinamic_fields['polea_arco_contacto'] = $conveyor['PoleaArcoContacto'];
                $dinamic_fields['rod_ang_impacto'] = $conveyor['RodilloAngImpacto'];
                $dinamic_fields['rod_diam_carga'] = $conveyor['RodilloDiamCarga'];
                $dinamic_fields['rod_ang_carga'] = $conveyor['RodilloAngCarga'];
                $dinamic_fields['rod_diam_retorno'] = $conveyor['RodilloDiamRetorno'];
                
                /**CARGAMOS DATOS DEL RESUMEN DE CONVEYORS**/
                if(!isset($resumen_conveyors[$conveyor['Conveyor']['id']])){                
                    $tension = $conveyor['Conveyor']['summary_rating'];
                    $simbolos = array('/','.',',');
                    $tension = clear_string($tension,$simbolos);
                    
                    $resumen_conveyors[$conveyor['Conveyor']['id']] = array(
                                            $conveyor['Conveyor']['summary_width'],
                                            $conveyor['Conveyor']['summary_feet'],
                                            $tension,
                                            $conveyor[0]['summary_layers'],
                                            $conveyor['Conveyor']['summary_brand'],
                                            strtolower($conveyor['Conveyor']['summary_brand'])
                                          );                
                    $total_transportadores++;
                    
                    /**CARGAMOS DATOS DEL RESUMEN DE RODILLOS**/
                    $partes_artesa = is_null($conveyor['Conveyor']['rod_partes_artesa']) ? '-':$conveyor['Conveyor']['rod_partes_artesa'];
                    $resumen_idlers[] = array($conveyor[0]['summary_idlers'],__('Impacto',true),$conveyor['Conveyor']['rod_diam_impacto'],'-');
                    $resumen_idlers[] = array($conveyor[0]['summary_idlers'],__('Carga',true),$dinamic_fields['rod_diam_carga'][$index_config_transporter],$partes_artesa);
                    $resumen_idlers[] = array($conveyor[0]['summary_idlers'],__('Retorno',true),$dinamic_fields['rod_diam_retorno'][$index_config_transporter],'-');
                    $total_rodillos += $conveyor[0]['summary_idlers']*3;
                    
                    /**CARGAMOS DATOS DEL RESUMEN DE POLEAS**/
                    $diametro_polea = trim($conveyor['Conveyor']['polea_motriz'])!='' ? trim($conveyor['Conveyor']['polea_motriz']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_polea_motriz'])!='' ? trim($conveyor['Conveyor']['ancho_polea_motriz']):'-';
                        $recubrimiento = trim($conveyor['Conveyor']['polea_recubrimiento'])!='' ? trim($conveyor['Conveyor']['polea_recubrimiento']):'-';
                        $resumen_pulleys[] = array(1,__('Motriz',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_cola'])!='' ? trim($conveyor['Conveyor']['polea_cola']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_cola'])!='' ? trim($conveyor['Conveyor']['ancho_pol_cola']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Cola',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_doblez'])!='' ? trim($conveyor['Conveyor']['polea_doblez']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_doblez'])!='' ? trim($conveyor['Conveyor']['ancho_pol_doblez']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Doblez',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_contacto'])!='' ? trim($conveyor['Conveyor']['polea_contacto']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_contacto'])!='' ? trim($conveyor['Conveyor']['ancho_pol_contacto']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Contacto',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_cabeza'])!='' ? trim($conveyor['Conveyor']['polea_cabeza']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_cabeza'])!='' ? trim($conveyor['Conveyor']['ancho_pol_cabeza']):'-';
                        $recubrimiento = trim($conveyor['Conveyor']['polea_recubrimiento'])!='' ? trim($conveyor['Conveyor']['polea_recubrimiento']):'-';
                        $resumen_pulleys[] = array(1,__('Cabeza',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_tensora'])!='' ? trim($conveyor['Conveyor']['polea_tensora']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_tensora'])!='' ? trim($conveyor['Conveyor']['ancho_pol_tensora']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Tensora',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_uno_adicional'])!='' ? trim($conveyor['Conveyor']['polea_uno_adicional']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_polea_uno_adicional'])!='' ? trim($conveyor['Conveyor']['ancho_polea_uno_adicional']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Adicional 1',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                    
                    $diametro_polea = trim($conveyor['Conveyor']['polea_dos_adicional'])!='' ? trim($conveyor['Conveyor']['polea_dos_adicional']):false;
                    if($diametro_polea!=false){
                        $ancho = trim($conveyor['Conveyor']['ancho_pol_dos_adicional'])!='' ? trim($conveyor['Conveyor']['ancho_pol_dos_adicional']):'-';
                        $recubrimiento = 'NA';
                        $resumen_pulleys[] = array(1,__('Adicional 2',true), $diametro_polea, $ancho, $recubrimiento);
                        $total_poleas++;
                    }
                }
                
                        
                $conveyor = $conveyor['Conveyor'];
                
                
                $data .= '<tr><td>' . $conveyor['numero'] . '</td>';
                for ($j = $header_iter; $j <= count($fields_selected); $j++) {//iteramos segun los campos seleccionados
                    $valor_campo = '';
                    if (isset($dinamic_fields[$fields_selected[$j - 1]])) {
                        $valor_campo = $dinamic_fields[$fields_selected[$j - 1]][$index_config_transporter];
                    } else {
                        $valor_campo = $conveyor[$fields_selected[$j - 1]];
                    }
                    $valor_campo = trim($valor_campo) == '' || is_null($valor_campo) ? '-' : $valor_campo;
                    //$data .= '<td>' . $data_body[$k][$j - 1] . '</td>';
                    $data .= '<td>' . $valor_campo . '</td>';
                    if ($j % $columns_page == 0) {
                        break;
                    }
                }
                $data .= '</tr>';
            }
            $data .= '</tbody>';

            $data .= '</table></div></div>';
            $data = $i + 1 < $pages_table ? $data . '<div class="page-content"></div>' : $data;
            $header_iter = $j + 1;
        }

        ?>
        
        <?php 
        $resume = array();
        foreach ($resumen_conveyors AS $index => $crude_data){
            //obtenemos la hash de los campos
            $hash_row = sha1($crude_data[0].$crude_data[2].$crude_data[5]);
            //sino existe la clave, insertamos el elementos tal cual
            if (!array_key_exists($hash_row, $resume)) {
                $resume[$hash_row] = array(1, $crude_data[0], $crude_data[1], $crude_data[2], $crude_data[3], $crude_data[4]);
            }else{
                //aumentamos las bandas
                $resume[$hash_row][0] = $resume[$hash_row][0] + 1;
                $resume[$hash_row][2] = $resume[$hash_row][2] + $crude_data[2];//sumamos los mts/feat de la actual fila
            }
        }
        $resumen_conveyors = $resume; 
        
        $resume = array();
        foreach ($resumen_idlers AS $index => $crude_data){
            //obtenemos la hash de los campos
            $hash_row = sha1($crude_data[1].$crude_data[2]);
            //sino existe la clave, insertamos el elementos tal cual
            if (!array_key_exists($hash_row, $resume)) {
                $resume[$hash_row] = array($crude_data[0], $crude_data[1], $crude_data[2], $crude_data[3]);
            }else{
                //aumentamos las bandas
                $resume[$hash_row][0] = $resume[$hash_row][0] + $crude_data[0];
            }
        }
        $resumen_idlers = $resume; 
        
        $resume = array();
        foreach ($resumen_pulleys AS $index => $crude_data){
            //obtenemos la hash de los campos
            $hash_row = sha1($crude_data[1].$crude_data[2].$crude_data[3].$crude_data[4]);
            //sino existe la clave, insertamos el elementos tal cual
            if (!array_key_exists($hash_row, $resume)) {
                $resume[$hash_row] = array($crude_data[0], $crude_data[1], $crude_data[2], $crude_data[3], $crude_data[4]);
            }else{
                //aumentamos las bandas
                $resume[$hash_row][0] = $resume[$hash_row][0] + $crude_data[0];
            }
        }
        $resumen_pulleys = $resume; 
        
        
        ?>
        <!--<div class="page-content"></div>-->
        <div class="center-aligned">
            <p class="title-download"><?php echo __('Resumen de bandas', true); ?></p>
            <p class="title-conveyor-download-resume"><?php echo $company['Empresa']['name']; ?></p>            
            <div class="right-aligned title-resume resume-conveyors"><div><?php echo __('Transportadores totales %s', array($total_transportadores)); ?></div></div>
            <!--PONER RESUMEN -->
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('No. Tranportadores', true); ?></th>
                        <th><?php echo __('Ancho (plg)', true); ?></th>
                        <th><?php echo __('Mts. Summary', true); ?></th>
                        <th><?php echo __('Tension Banda', true); ?></th>
                        <th><?php echo __('No. Capas', true); ?></th>
                        <th><?php echo __('Marca', true); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                            $resume_rows = '';
                            foreach ($resumen_conveyors AS $hash => $row){
                                $resume_rows .= '<tr>';
                                $resume_rows .= '<td>'.$row[0].'</td>';
                                $resume_rows .= '<td>'.$row[1].'</td>';
                                $resume_rows .= '<td>'.$row[2].'</td>';
                                $resume_rows .= '<td>'.$row[3].'</td>';
                                $resume_rows .= '<td>'.$row[4].'</td>';
                                $resume_rows .= '<td>'.$row[5].'</td>';
                                $resume_rows .= '</tr>';
                            }
                            echo $resume_rows;
                    ?>
                </tbody>
            </table>
        </div>        
        <div class="page-content"></div>
        <!--PONER RESUMEN -->
        <div class="center-aligned">
            <p class="title-download"><?php echo __('Resumen de poleas', true); ?></p>
            <p class="title-conveyor-download-resume"><?php echo $company['Empresa']['name']; ?></p>
            <div class="right-aligned title-resume resume-pulleys"><div><?php echo __('Poleas totales %s', array($total_poleas)); ?></div></div>
            <!--PONER RESUMEN -->
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('No. Poleas', true); ?></th>
                        <th><?php echo __('Tipo Summary', true); ?></th>
                        <th><?php echo __('Diametro', true); ?></th>
                        <th><?php echo __('Ancho (plg)', true); ?></th>
                        <th><?php echo __('Recubrimiento (plg)', true); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                            $resume_rows = '';
                            foreach ($resumen_pulleys AS $hash => $row){
                                $resume_rows .= '<tr>';
                                $resume_rows .= '<td>'.$row[0].'</td>';
                                $resume_rows .= '<td>'.$row[1].'</td>';
                                $resume_rows .= '<td>'.$row[2].'</td>';
                                $resume_rows .= '<td>'.$row[3].'</td>';
                                $resume_rows .= '<td>'.$row[4].'</td>';
                                $resume_rows .= '</tr>';
                            }
                            echo $resume_rows;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="page-content"></div>
        <!--PONER RESUMEN -->
        <div class="center-aligned">
            <p class="title-download"><?php echo __('Resumen de rodillos', true); ?></p>
            <p class="title-conveyor-download-resume"><?php echo $company['Empresa']['name']; ?></p>
            <div class="right-aligned title-resume resume-idlers"><div><?php echo __('Rodillos totales %s', array($total_rodillos)); ?></div></div>
            <!--PONER RESUMEN -->
            <table>
                <thead>
                    <tr>
                        <th><?php echo __('No. Rodillos', true); ?></th>
                        <th><?php echo __('Tipo Summary', true); ?></th>
                        <th><?php echo __('Diametro', true); ?></th>
                        <th><?php echo __('Partes por artesa', true); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                            $resume_rows = '';
                            foreach ($resumen_idlers AS $hash => $row){
                                $resume_rows .= '<tr>';
                                $resume_rows .= '<td>'.$row[0].'</td>';
                                $resume_rows .= '<td>'.$row[1].'</td>';
                                $resume_rows .= '<td>'.$row[2].'</td>';
                                $resume_rows .= '<td>'.$row[3].'</td>';
                                $resume_rows .= '</tr>';
                            }
                            echo $resume_rows;
                    ?>
                </tbody>
            </table>
        </div>
        <div class="page-content"></div>
        <?php 
        echo $data;
        ?>
    </body>
</html>
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->set_paper('letter', 'landscape');
$dompdf->load_html($html);
$dompdf->render();
$dompdf->stream("tmp" . uniqid() . '.pdf', array('Attachment' => 0));
