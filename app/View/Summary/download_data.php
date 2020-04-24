<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file download_data.php
 * @description
 *
 * @date 01, 2018
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
?>
    <html>
    <head>
        <style>
            *{
                font-family: "Continental Stag Sans";
                font-weight: lighter;
            }
            /*@page { margin: 70px 20px 130px 20px;}*/
            .page-content{ page-break-before: always; }

            .title-resume > div{
                display: inline;
                padding: 0;
                border-bottom: 1px solid #f0f5eb;
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
                font-family: "Continental Stag Sans";
                margin: 10px 0 10px 0;
                padding: 0;
                font-weight: lighter;
            }

            .title-conveyor-download-resume{
                color: #ffa500;
                font-size: 35px;
                font-family: "Continental Stag Sans";
                margin: 0 0 0 0;
                padding: 0;
                font-weight: lighter;
            }

            #content,.content{
                font-family: "Continental Stag Sans";
                color: #707571;
            }

            #items_history_wrapper h1{
                color: #969B96;
                margin: 0;
                font-size: 15px;
                font-weight: lighter;
                text-align: left;
                margin-top: 30px;
            }
            #items_history_wrapper h1 span{
                font-weight: bold;
            }
            table.history-data{
                background: #FFFFFF;
                border-collapse: collapse;
                width: 100%;
                margin-top: 10px;
            }
            .history-data thead tr td{
                background: #F0F5EB;
                color: #707571;
                font-size: 12px;
                padding: 5px;
                height: 30px;
                font-weight: lighter;
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
                color: #707571;
            }

            .history-data tbody tr:hover td{
                background: #F0F5EB;
            }

            thead { display: table-header-group; }
            tfoot { display: table-row-group; }
            tr { page-break-inside: avoid; }



            /* Page Breaks */
            .pb_before { page-break-before:always !important; }
            .pb_after  { page-break-after:always !important; }
            .pbi_avoid { page-break-inside:avoid !important; }

        </style>
    <body>


    <?php
        $market = $company['Empresa']["i_market_id"];
        $unit_width = $market == IMarket::Is_USCanada ? "(in)" : "(mm)";
        $unit_length = $market == IMarket::Is_USCanada ? "(ft)" : "(m)";
    ?>
    <div class="page-content"></div>
    <!--PONER RESUMEN -->
    <div class="center-aligned">
        <p class="title-download"><?php echo __("Summary Report",true); ?></p>
        <p class="title-conveyor-download-resume"><?php echo $company['Empresa']['name']; ?></p>
        <!--PONER RESUMEN -->
        <div id="items_history_wrapper">
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
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
        </div>
    </div>

    </body>
    </html>
<?php

$path_file = _PATH_GENERIC_TMP_FILES."data__history".'.html';
$path_header = _PATH_GENERIC_TMP_FILES."header_history".'.html';
$path_footer = _PATH_GENERIC_TMP_FILES."footer_history".'.html';

$html = ob_get_clean();
if (($fh = fopen($path_file, 'w'))) {
    fwrite($fh, $html);
    fclose($fh);
}
//Start Header
ob_start();
//Doctype is neccesary on header
?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            *{
                font-family: "dejavu sans condensed";
            }
            #header { position: relative; height: 50px; background-image: url('<?php echo $site . _LOGO_CONTINENTAL_PDF; ?>');background-repeat: no-repeat; background-position: 0px 5px;}
            #logo_contitech{ position: absolute; top: 0px; right: 0px; height: 50px; width:120px; display:block;background-image: url('<?php echo $site . _LOGO_CONTITECH_PDF; ?>');background-repeat: no-repeat; background-position: top right;}

        </style>
    </head>
    <body style="border:0; margin: 0;">
    <div id="header">
        <div id="logo_contitech">
        </div>
    </div>
    </body>
    </html>
<?php
$header_data = ob_get_clean();
if (($fh = fopen($path_header, 'w'))) {
    fwrite($fh, $header_data);
    fclose($fh);
}

//Start Footer
ob_start();
?>
    <html>
    <head>
        <style>
            *{
                font-family: "dejavu sans condensed";
            }
            #footer {  background-image: url('<?php echo $site . _LOGO_CONTIPLUS_PDF; ?>'); background-repeat: no-repeat; background-position: top right; height: 100px; position: relative;}
            #info_conveyor{
                color: #707571;
                position: absolute;
                top: 25px;
                left: 0px;
                text-align:left;
            }
            #info_conveyor div{
                line-height: 1.2em;
                font-size: 12px;
                font-family: "dejavu sans condensed";
            }
            #info_conveyor div b{
                font-weight:bold;
            }

            #pager{
                font-size: 12px;
                color: #707571;
                font-family: "dejavu sans condensed";
                text-align:right;
                padding-right:10px;
                right: 0;
                position: absolute;
                top: 85px;
            }
            #disclaimer{ font-size: 12px; color: #707571; font-family: "dejavu sans condensed"; text-align:center; width: 100%; position: absolute; top: 85px; }


        </style>
        <script>
            function subst() {
                var vars={};
                var x=document.location.search.substring(1).split('&');
                for(var i in x) {var z=x[i].split('=',2);vars[z[0]] = unescape(z[1]);}
                var x=['frompage','topage','page','webpage','section','subsection','subsubsection'];
                for(var i in x) {
                    var y = document.getElementsByClassName(x[i]);
                    for(var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
                }
            }
        </script>
    </head><body style="border:0; margin: 0;" onload="subst()">
    <div id="footer">
        <div id="info_conveyor">
            <div>
            <span>
                <?php
                $fechaGeneracion = $this->Utilities->transformVisualFormatDate(date('Y-m-d'), false);
                echo __('Generado', true);
                ?>:
            </span>
                <b><?php echo $fechaGeneracion; ?></b></div>
            <div><span><?php echo __('Cliente', true); ?>:</span> <b><?php echo $company['Empresa']['name']; ?></b></div>
            <div><span><?php echo __('Distribuidor', true); ?>:</span> <b><?php echo $company['Distribuidor']['name']; ?></b></div>
        </div>
        <div id="pager">
            <span class="page"></span>
        </div>
        <div id="disclaimer">
            &copy; <?php echo date('Y'); ?>
            <?php echo __('ContiTech, ALL RIGHTS RESERVED', true); ?>
        </div>
    </div>

    </body></html>
<?php
$footer_data = ob_get_clean();

if (($fh = fopen($path_footer, 'w'))) {
    fwrite($fh, $footer_data);
    fclose($fh);
}


$data = array('url' => $site.$path_file, 'header' => $site.$path_header, 'footer' => $site.$path_footer, 'margins'=>'25,10,30','o'=>'Landscape');
$response = $Socket->send(_API_PDF_WRITER,$data);

$name_file = 'SummaryReport.pdf';
$file = _PATH_GENERIC_TMP_FILES.$name_file;
file_put_contents($file, $response);

$this->response->file($file,array(
        'download' => false,
        'name' => $name_file
    )
);
return $this->response;