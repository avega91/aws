<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file SummaryController.php
 * @description
 *
 * @date 12, 2016
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */

class SummaryController extends AppController {
    public function beforeFilter(){
        parent::beforeFilter();
        //$this->uses[] = 'History';
        //Configure::load('history');
        //$this->set('history_config', Configure :: read('History'));
    }

    public function report(){
        $this->set('title_for_layout', 'Summary Report');
        $this->set('options_toolbar', 'summary-report-section');

        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedCompanyParams = $this->Core->decodePairParams($params);
            if ($decodedCompanyParams['isOk']) {
                $company_received = $decodedCompanyParams['item_id'];
                $company = $this->Empresa->findById($company_received);
                $selected_order = !isset($_COOKIE["order_selected"]) ? 'alphabetically' : $_COOKIE["order_selected"];
                if (!empty($company)) {
                    $empresa = $company['Empresa'];
                    $secureClientConveyorParams = $this->Core->encodeParams($empresa['id']);

                    $urlDownloadData = $this->_html->url(array('controller' => 'Summary', 'action' => 'downloadData', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $urlClientConveyor = $this->_html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientConveyorParams['item_id'], $secureClientConveyorParams['digest']));
                    $this->set('companyConveyorLink', $urlClientConveyor);
                    $filter_companies = $company_received;
                    $conveyors = $this->Conveyor->findAllData($filter_companies);
                    $ultrasonic_compounds = $this->Core->getCompoundMatrixValues();
                    $conveyor_us_config = Configure :: read('ConveyorUS');
                    $espesor_cubiertas = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CUBIERTA_DESGASTE, $orderById = true);
                    $espesor_cubiertas = Set::extract('/ctransp/.', $espesor_cubiertas );

                    $tableData = [];

                    foreach ($conveyors AS $index => $conveyor){
                        $ultrasonic_readings = $conveyor['UltrasonicReading'];
                        /////////////////


                        $tension_unit = $conveyor["UsTabInstalledBelt"]["tension_unit"];
                        $years_on_system = "-";
                        $specification = "-";


                        if($conveyor["Conveyor"]["is_us_conveyor"]) {
                            $manufacturer_id = $conveyor["UsTabInstalledBelt"]["belt_manufacturer"];
                            $valid_manufacturer = !is_null($manufacturer_id) && isset($conveyor_us_config['installed_belt']['manufacturer'][$manufacturer_id]);
                            $manufacturer = $valid_manufacturer ? $conveyor_us_config['installed_belt']['manufacturer'][$manufacturer_id] : '-';

                            $family = $conveyor["UsTabInstalledBelt"]["belt_family"];
                            $family = $valid_manufacturer && isset($conveyor_us_config['installed_belt']['family'][$manufacturer_id][$family]) ? $conveyor_us_config['installed_belt']['family'][$manufacturer_id][$family] : '-';
                            $compound = $conveyor["UsTabInstalledBelt"]["belt_compound"];
                            $compound = $valid_manufacturer && isset($conveyor_us_config['installed_belt']['compounds'][$manufacturer_id][$compound]) ? $conveyor_us_config['installed_belt']['compounds'][$manufacturer_id][$compound] : '-';

                            //Get Specification
                            $carcass = $conveyor["UsTabInstalledBelt"]["carcass"];

                            if(isset($conveyor["UsTabInstalledBelt"]["top_cover"], $conveyor["UsTabInstalledBelt"]["pulley_cover"]) && $conveyor["UsTabInstalledBelt"]["top_cover"] > 0 && $conveyor["UsTabInstalledBelt"]["pulley_cover"] > 0){
                                if ($tension_unit == "imperial") {
                                    $topCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["imperial"][$conveyor["UsTabInstalledBelt"]["top_cover"]];
                                    $bottompCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["imperial"][$conveyor["UsTabInstalledBelt"]["pulley_cover"]];
                                }else{
                                    $topCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["metric"][$conveyor["UsTabInstalledBelt"]["top_cover"]];
                                    $bottompCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["metric"][$conveyor["UsTabInstalledBelt"]["pulley_cover"]];
                                }

                                if (in_array($carcass, ["ST", "UNKW"])) {
                                    if (isset($conveyor["UsTabInstalledBelt"]["open_tension"], $conveyor["UsTabInstalledBelt"]["width"])
                                        && $conveyor["UsTabInstalledBelt"]["open_tension"] > 0 && $conveyor["UsTabInstalledBelt"]["width"] > 0
                                    ) {
                                        $specification = $tension_unit == "imperial" ? $conveyor["UsTabInstalledBelt"]["open_tension"] . "/" . $conveyor["UsTabInstalledBelt"]["width"]." ". $topCover . "x" . $bottompCover : $conveyor["UsTabInstalledBelt"]["open_tension"] . "/" . $conveyor["UsTabInstalledBelt"]["width"]." ". $topCover . " + " . $bottompCover;
                                    }
                                } else {//@todo, checar width cuando se selecciona campo abierto -> other_width

                                    if (isset($conveyor["UsTabInstalledBelt"]["tension"], $conveyor["UsTabInstalledBelt"]["plies"], $conveyor["UsTabInstalledBelt"]["width"])
                                        && $conveyor["UsTabInstalledBelt"]["plies"] > 0 && $conveyor["UsTabInstalledBelt"]["width"] > 0
                                    ) {
                                        if ($tension_unit == "imperial") {
                                            $specification = $conveyor["UsTabInstalledBelt"]["tension"] . " piw/" . $conveyor["UsTabInstalledBelt"]["plies"] . "/" . $conveyor["UsTabInstalledBelt"]["width"] . " " . $topCover . "x" . $bottompCover;

                                        } else {
                                            $specification = $conveyor["UsTabInstalledBelt"]["width"] . " " . $carcass . " " . $conveyor["UsTabInstalledBelt"]["tension"] . " /" . $conveyor["UsTabInstalledBelt"]["plies"] . " " . $topCover . " + " . $bottompCover;
                                        }
                                    }
                                }

                            }

                            $selected_width = $conveyor["UsTabInstalledBelt"]["width"]!="" ? $conveyor["UsTabInstalledBelt"]["width"] : "-";

                            $widths_conversion = [
                                400 => '16',
                                450 => '18',
                                500 => '20',
                                600 => '24',
                                750 => '30',
                                900 => '32',
                                1050 => '36',
                                1200 => '42',
                                1350 => '48',
                                1500 => '54',
                                1650 => '60',
                                1800 => '66',
                                1950 => '72',
                                2100 => '78',
                                2400 => '84'
                            ];

                            $width = $selected_width;
                            $other_width = $conveyor["UsTabInstalledBelt"]["other_width"];
                            if($empresa["i_market_id"]==IMarket::Is_USCanada) { //poner unidades imperiales
                                if($tension_unit=="metric"){//La banda esta en unidad metrica
                                    //1mm = 0.0393701 in
                                    $width = isset($widths_conversion[$selected_width]) ? $widths_conversion[$selected_width] : $other_width*0.0393701;
                                    $width = number_format($width, 0, "","");
                                }else{//ya esta en imperial
                                    $mm_key = array_search($selected_width, $widths_conversion); // buscar si selecciono valor del dropdown
                                    $width = $mm_key!== false ? $selected_width : $other_width;// si no es una clave valida, es other width
                                }
                            }else{ //Poner unidades metricas
                                if($tension_unit=="imperial"){//La banda esta en unidad imperial
                                    $mm_key = array_search($selected_width, $widths_conversion); // get key of mm;
                                    //1mm = 0.0393701 in
                                    $width = $mm_key!== false ? $mm_key : $other_width*25.41;
                                    $width = number_format($width, 0, "","");
                                }else{ //ya esta en unidad metrica
                                    $width = isset($widths_conversion[$selected_width]) ? $selected_width : $other_width; // buscar si selecciono valor del dropdown, // si no es una clave valida, es other width
                                }
                            }


                            $length = $conveyor["UsTabInstalledBelt"]["belt_length_install"]!="" ? $conveyor["UsTabInstalledBelt"]["belt_length_install"] : "-";

                            $installation_belt = $conveyor["UsTabInstalledBelt"]["installation_date"];
                            if(!is_null($conveyor["UsTabInstalledBelt"]["installation_date"]) && $conveyor["UsTabInstalledBelt"]["installation_date"]!="0000-00-00"){
                                //$fancy_inst_date = $this->Utilities->timestampToUsDate($conveyor["UsTabInstalledBelt"]["installation_date"]);

                                $installed_date = date("Y-m-d", strtotime($installation_belt));
                                $installed_date = new DateTime($installed_date);
                                $now   = new DateTime('now');


                                $interval = $now->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_on_system = number_format($elapsed_years, "2",".", ",");
                            }

                        }else{
                            $manufacturer = !is_null($conveyor["Conveyor"]["banda_marca"]) && $conveyor["Conveyor"]["banda_marca"]!="" ? $conveyor["Conveyor"]["banda_marca"] : "-";
                            $family = !is_null($conveyor["TabInstalledBelt"]["trade_name"]) && $conveyor["TabInstalledBelt"]["trade_name"] != "" ? $conveyor["TabInstalledBelt"]["trade_name"] : "-";

                            $compound = "-";
                            /*if(!is_null($conveyor['Ultrasonic']["compound_id"])){
                                $compound = $ultrasonic_compounds[$conveyor['Ultrasonic']["compound_id"]][3];
                            }*/

                            if(!is_null($conveyor["TabInstalledBelt"]["used_belt_grade"]) && $conveyor["TabInstalledBelt"]["used_belt_grade"]!=""){
                                $compound = $conveyor["TabInstalledBelt"]["used_belt_grade"];
                            }

                            //$meta_units =  explode("||",$conveyor["Conveyor"]["meta_units"]);
                            //$units_width = explode("=",$meta_units[0]); //es el primer par

                            $meta_units = $this->Core->getPairsMetaUnits($conveyor["Conveyor"]["meta_units"]);
                            $units_width = $meta_units["ancho_banda"];
                            if(!isset($meta_units["ancho_banda"])){
                                mail("elalbertgd@gmail.com","Error de unidades en banda summary",print_r($conveyor["Conveyor"],true));
                            }

                            $width = "-";
                            if(!is_null($conveyor["Conveyor"]["banda_ancho"])){
                                if($units_width!="mm"){//$units_width[1] -> indice de ancho_banda
                                    $width = $conveyor["Conveyor"]["banda_ancho"]*25.4; //to mm
                                    $width = number_format($width, 0, "","");
                                }else{
                                    $width = $conveyor["Conveyor"]["banda_ancho"];
                                }
                            }


                            //$tension = explode("=",$meta_units[1]); //unidad de tension esta en segundo par
                            $tension = $meta_units["tension_banda"];
                            $units_tension = $tension == "PIW" ? "imperial" : "metric";


                            if(isset($conveyor["Conveyor"]["id_espesor_cubierta_sup"],$conveyor["Conveyor"]["id_espesor_cubierta_inf"], $conveyor["Conveyor"]["banda_ancho"], $conveyor["Conveyor"]["banda_tension"])
                                && $conveyor["Conveyor"]["banda_tension"]!="" && $conveyor["Conveyor"]["id_espesor_cubierta_sup"]>0 && $conveyor["Conveyor"]["id_espesor_cubierta_inf"]>0){
                                $key_top_cover = array_search($conveyor["Conveyor"]["id_espesor_cubierta_sup"], array_column($espesor_cubiertas, 'id'));
                                $key_bottom_cover = array_search($conveyor["Conveyor"]["id_espesor_cubierta_inf"], array_column($espesor_cubiertas, 'id'));
                                if($key_top_cover!==false && $key_bottom_cover!==false){
                                    if($conveyor["Conveyor"]["id_espesor_cubierta_sup"]==306){
                                        $unit_top_cover = $meta_units["open_espesor_cubierta_sup"];
                                        if($unit_top_cover=='mm'){
                                            $top_cover_mm = $conveyor["Conveyor"]["open_espesor_cubierta_sup"];//esta en mm
                                            $top_cover_in = $top_cover_mm * 0.0393701;
                                        }else{
                                            $top_cover_in = $conveyor["Conveyor"]["open_espesor_cubierta_sup"];//esta en mm
                                            $top_cover_mm = $top_cover_in * 25.4;
                                        }
                                    }else{
                                        list($top_cover_in, $top_cover_mm) = explode("-",$espesor_cubiertas[$key_top_cover]["titulo"]);
                                    }//

                                    if($conveyor["Conveyor"]["id_espesor_cubierta_inf"]==306){
                                        $unit_bottom_cover = $meta_units["open_espesor_cubierta_inf"];
                                        if($unit_bottom_cover=='mm'){
                                            $bottom_cover_mm = $conveyor["Conveyor"]["open_espesor_cubierta_inf"];//esta en mm
                                            $bottom_cover_in = $bottom_cover_mm * 0.0393701;
                                        }else{
                                            $bottom_cover_in = $conveyor["Conveyor"]["open_espesor_cubierta_inf"];//esta en mm
                                            $bottom_cover_mm = $bottom_cover_in * 25.4;
                                        }
                                    }else{
                                        list($bottom_cover_in, $bottom_cover_mm) = explode("-",$espesor_cubiertas[$key_bottom_cover]["titulo"]);
                                    }//


                                    $plies_number = isset($conveyor["TabInstalledBelt"]["plies_number"]) && $conveyor["TabInstalledBelt"]["plies_number"]!="" ? $conveyor["TabInstalledBelt"]["plies_number"]:0;
                                    if($units_tension == "imperial"){
                                        $top_cover_in = rtrim(str_replace("in","", $top_cover_in));
                                        $bottom_cover_in = rtrim(str_replace("in","", $bottom_cover_in));

                                        $width_spec = $units_width=="mm" ? $conveyor["Conveyor"]["banda_ancho"]*0.0393701 : $conveyor["Conveyor"]["banda_ancho"];//si esta en mm convetir a in
                                        $specification = $conveyor["Conveyor"]["banda_tension"]." piw/".$plies_number."/".$width_spec." ".$top_cover_in."x".$bottom_cover_in;
                                    }else{
                                        if(isset($conveyor["TabInstalledBelt"]["shell"]) && $conveyor["TabInstalledBelt"]["shell"]!=""){
                                            $top_cover_mm = ltrim(str_replace("mm","", $top_cover_mm));
                                            $bottom_cover_mm = ltrim(str_replace("mm","", $bottom_cover_mm));
                                            $plies = $conveyor["TabInstalledBelt"]["shell"]=="ST" || $conveyor["TabInstalledBelt"]["shell"]=="UNKW" ? 0:$plies_number;

                                            $width_spec = $units_width=="in" ? $conveyor["Conveyor"]["banda_ancho"]*25.4 : $conveyor["Conveyor"]["banda_ancho"];//si esta en in convetir a mm
                                            $specification = $width_spec." ".$conveyor["TabInstalledBelt"]["shell"]." ".$conveyor["Conveyor"]["banda_tension"]." / ".$plies." ".$top_cover_mm." + ".$bottom_cover_mm;;
                                        }

                                    }
                                }
                            }

                            //$units_length = explode("=",$meta_units[3]); // es el cuarto par
                            $units_length = $meta_units["desarrollo_banda"];
                            $length = "-";
                            if(!is_null($conveyor["Conveyor"]["banda_desarrollo_total"])){
                                if($units_length!="m"){ //esta en pies
                                    $length = $conveyor["Conveyor"]["banda_desarrollo_total"]*0.3048; //to m
                                    $length = number_format($length, 0, "","");
                                }else{
                                    $length = $conveyor["Conveyor"]["banda_desarrollo_total"]; //already m
                                }
                            }



                            $installation_belt = $conveyor["Conveyor"]["banda_fecha_instalacion"];
                            if(!is_null($conveyor["Conveyor"]["banda_fecha_instalacion"]) && $conveyor["Conveyor"]["banda_fecha_instalacion"]!="0000-00-00"){

                                //$fancy_inst_date = $this->Utilities->timestampToUsDate($conveyor["Conveyor"]["banda_fecha_instalacion"]);

                                $installed_date = date("Y-m-d", strtotime($conveyor["Conveyor"]["banda_fecha_instalacion"]));
                                $installed_date = new DateTime($installed_date);
                                $now   = new DateTime('now');


                                $interval = $now->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_on_system = number_format($elapsed_years, "2",".", ",");
                            }
                        }

                        $gauge = [];
                        $has_ultrasonic = !is_null($ultrasonic_readings['ultrasonic_id']);
                        if($has_ultrasonic){
                            $gauge = $this->Core->calcAbrasionLife($conveyor["Conveyor"]["id"]);
                        }

                        $remain_lifetime_years = "-";
                        if(!empty($gauge)){
                            $projected_future_life = $gauge['projected_future_life'];
                            $remain_lifetime_years = $projected_future_life / 12;
                            $remain_lifetime_years = number_format($remain_lifetime_years, "1",".", "");
                        }

                        /*$recommended_value = "-";
                        $hasRecommendedBelt = $this->Core->sePuedeCalcularBandaRecomendada($conveyor);
                        if($hasRecommendedBelt){
                            $recommended = $this->Core->calcLifeEstimationBanda($conveyor);
                            $recommended_value = !is_null($recommended["recommended_conveyor_in"]) ? $recommended["recommended_conveyor_in"] : $recommended_value;
                        }*/



                        $tableData[] = [
                            "conveyor_id" => $conveyor["Conveyor"]["id"],
                            "number" => $conveyor["Conveyor"]["numero"],
                            "manufacturer" => $manufacturer,
                            "family" => $family,
                            "compound" => $compound,
                            "recommended" => $specification,
                            "years_on_system" => $years_on_system,
                            "installation_date" => $installation_belt,
                            "remain_lifetime_years" => $remain_lifetime_years,
                            "length" => $length,
                            "width" => $width,
                            "gauge" => $gauge,
                        ];
                    }


                    switch ($selected_order){
                        case "alphabetically":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'number', SORT_ASC);
                            break;
                        case "abrasion":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'remain_lifetime_years', SORT_ASC);
                        break;
                        case "specification":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'recommended', SORT_ASC);
                        break;
                        case "width":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'width', SORT_ASC);
                        break;
                    }




                    $this->jsToInclude[] = 'application/Summary/report';
                    $this->set('jsToInclude', $this->jsToInclude);

                    $this->set('tableData', $tableData);
                    $this->set('clientId', $company_received);
                    $this->set('urlDownloadData', $urlDownloadData);
                    $this->set('company', $empresa);
                    $this->set('selected_order', $selected_order);
                }else{
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            }else{
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        }else{
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function downloadData(){
        $this->layout = false;
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if (!empty($params) && count($params) == 2) {
            $decodedItemParams = $this->Core->decodePairParams($params);
            if($decodedItemParams['isOk']) {
                $item_received = $decodedItemParams['item_id'];
                $company = $this->Empresa->findByIdWithCorporate($item_received);
                $selected_order = !isset($_COOKIE["order_selected"]) ? 'alphabetically' : $_COOKIE["order_selected"];
                if (!empty($company)) {
                    $empresa = $company['Empresa'];
                    $filter_companies = $item_received;
                    $conveyors = $this->Conveyor->findAllData($filter_companies, "", $sort="numero");
                    $conveyor_us_config = Configure :: read('ConveyorUS');

                    $espesor_cubiertas = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::CUBIERTA_DESGASTE, $orderById = true);
                    $espesor_cubiertas = Set::extract('/ctransp/.', $espesor_cubiertas );

                    $tableData = [];
                    $ultrasonic_compounds = $this->Core->getCompoundMatrixValues();

                    foreach ($conveyors AS $index => $conveyor){
                        $ultrasonic_readings = $conveyor['UltrasonicReading'];
                        /////////////////


                        $tension_unit = $conveyor["UsTabInstalledBelt"]["tension_unit"];
                        $years_on_system = "-";
                        $specification = "-";


                        if($conveyor["Conveyor"]["is_us_conveyor"]) {
                            $manufacturer_id = $conveyor["UsTabInstalledBelt"]["belt_manufacturer"];
                            $valid_manufacturer = !is_null($manufacturer_id) && isset($conveyor_us_config['installed_belt']['manufacturer'][$manufacturer_id]);
                            $manufacturer = $valid_manufacturer ? $conveyor_us_config['installed_belt']['manufacturer'][$manufacturer_id] : '-';

                            $family = $conveyor["UsTabInstalledBelt"]["belt_family"];
                            $family = $valid_manufacturer && isset($conveyor_us_config['installed_belt']['family'][$manufacturer_id][$family]) ? $conveyor_us_config['installed_belt']['family'][$manufacturer_id][$family] : '-';
                            $compound = $conveyor["UsTabInstalledBelt"]["belt_compound"];
                            $compound = $valid_manufacturer && isset($conveyor_us_config['installed_belt']['compounds'][$manufacturer_id][$compound]) ? $conveyor_us_config['installed_belt']['compounds'][$manufacturer_id][$compound] : '-';

                            //Get Specification
                            $carcass = $conveyor["UsTabInstalledBelt"]["carcass"];

                            if(isset($conveyor["UsTabInstalledBelt"]["top_cover"], $conveyor["UsTabInstalledBelt"]["pulley_cover"]) && $conveyor["UsTabInstalledBelt"]["top_cover"] > 0 && $conveyor["UsTabInstalledBelt"]["pulley_cover"] > 0){
                                if ($tension_unit == "imperial") {
                                    $topCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["imperial"][$conveyor["UsTabInstalledBelt"]["top_cover"]];
                                    $bottompCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["imperial"][$conveyor["UsTabInstalledBelt"]["pulley_cover"]];
                                }else{
                                    $topCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["metric"][$conveyor["UsTabInstalledBelt"]["top_cover"]];
                                    $bottompCover = $conveyor_us_config['installed_belt']['top_covers_duplex']["metric"][$conveyor["UsTabInstalledBelt"]["pulley_cover"]];
                                }

                                if (in_array($carcass, ["ST", "UNKW"])) {
                                    if (isset($conveyor["UsTabInstalledBelt"]["open_tension"], $conveyor["UsTabInstalledBelt"]["width"])
                                        && $conveyor["UsTabInstalledBelt"]["open_tension"] > 0 && $conveyor["UsTabInstalledBelt"]["width"] > 0
                                    ) {
                                        $specification = $tension_unit == "imperial" ? $conveyor["UsTabInstalledBelt"]["open_tension"] . "/" . $conveyor["UsTabInstalledBelt"]["width"]." ". $topCover . "x" . $bottompCover : $conveyor["UsTabInstalledBelt"]["open_tension"] . "/" . $conveyor["UsTabInstalledBelt"]["width"]." ". $topCover . " + " . $bottompCover;
                                    }
                                } else {//@todo, checar width cuando se selecciona campo abierto -> other_width

                                    if (isset($conveyor["UsTabInstalledBelt"]["tension"], $conveyor["UsTabInstalledBelt"]["plies"], $conveyor["UsTabInstalledBelt"]["width"])
                                        && $conveyor["UsTabInstalledBelt"]["plies"] > 0 && $conveyor["UsTabInstalledBelt"]["width"] > 0
                                    ) {
                                        if ($tension_unit == "imperial") {
                                            $specification = $conveyor["UsTabInstalledBelt"]["tension"] . " piw/" . $conveyor["UsTabInstalledBelt"]["plies"] . "/" . $conveyor["UsTabInstalledBelt"]["width"] . " " . $topCover . "x" . $bottompCover;

                                        } else {
                                            $specification = $conveyor["UsTabInstalledBelt"]["width"] . " " . $carcass . " " . $conveyor["UsTabInstalledBelt"]["tension"] . " /" . $conveyor["UsTabInstalledBelt"]["plies"] . " " . $topCover . " + " . $bottompCover;
                                        }
                                    }
                                }

                            }

                            $selected_width = $conveyor["UsTabInstalledBelt"]["width"]!="" ? $conveyor["UsTabInstalledBelt"]["width"] : "-";

                            $widths_conversion = [
                                400 => '16',
                                450 => '18',
                                500 => '20',
                                600 => '24',
                                750 => '30',
                                900 => '32',
                                1050 => '36',
                                1200 => '42',
                                1350 => '48',
                                1500 => '54',
                                1650 => '60',
                                1800 => '66',
                                1950 => '72',
                                2100 => '78',
                                2400 => '84'
                            ];

                            $width = $selected_width;
                            $other_width = $conveyor["UsTabInstalledBelt"]["other_width"];
                            if($empresa["i_market_id"]==IMarket::Is_USCanada) { //poner unidades imperiales
                                if($tension_unit=="metric"){//La banda esta en unidad metrica
                                    //1mm = 0.0393701 in
                                    $width = isset($widths_conversion[$selected_width]) ? $widths_conversion[$selected_width] : $other_width*0.0393701;
                                    $width = number_format($width, 0, "","");
                                }else{//ya esta en imperial
                                    $mm_key = array_search($selected_width, $widths_conversion); // buscar si selecciono valor del dropdown
                                    $width = $mm_key!== false ? $selected_width : $other_width;// si no es una clave valida, es other width
                                }
                            }else{ //Poner unidades metricas
                                if($tension_unit=="imperial"){//La banda esta en unidad imperial
                                    $mm_key = array_search($selected_width, $widths_conversion); // get key of mm;
                                    //1mm = 0.0393701 in
                                    $width = $mm_key!== false ? $mm_key : $other_width*25.41;
                                    $width = number_format($width, 0, "","");
                                }else{ //ya esta en unidad metrica
                                    $width = isset($widths_conversion[$selected_width]) ? $selected_width : $other_width; // buscar si selecciono valor del dropdown, // si no es una clave valida, es other width
                                }
                            }


                            $length = $conveyor["UsTabInstalledBelt"]["belt_length_install"]!="" ? $conveyor["UsTabInstalledBelt"]["belt_length_install"] : "-";

                            $installation_belt = $conveyor["UsTabInstalledBelt"]["installation_date"];
                            if(!is_null($conveyor["UsTabInstalledBelt"]["installation_date"]) && $conveyor["UsTabInstalledBelt"]["installation_date"]!="0000-00-00"){
                                //$fancy_inst_date = $this->Utilities->timestampToUsDate($conveyor["UsTabInstalledBelt"]["installation_date"]);

                                $installed_date = date("Y-m-d", strtotime($installation_belt));
                                $installed_date = new DateTime($installed_date);
                                $now   = new DateTime('now');


                                $interval = $now->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_on_system = number_format($elapsed_years, "2",".", ",");
                            }

                        }else{
                            $manufacturer = !is_null($conveyor["Conveyor"]["banda_marca"]) && $conveyor["Conveyor"]["banda_marca"]!="" ? $conveyor["Conveyor"]["banda_marca"] : "-";
                            $family = !is_null($conveyor["TabInstalledBelt"]["trade_name"]) && $conveyor["TabInstalledBelt"]["trade_name"] != "" ? $conveyor["TabInstalledBelt"]["trade_name"] : "-";

                            $compound = "-";
                            /*if(!is_null($conveyor['Ultrasonic']["compound_id"])){
                                $compound = $ultrasonic_compounds[$conveyor['Ultrasonic']["compound_id"]][3];
                            }*/

                            if(!is_null($conveyor["TabInstalledBelt"]["used_belt_grade"]) && $conveyor["TabInstalledBelt"]["used_belt_grade"]!=""){
                                $compound = $conveyor["TabInstalledBelt"]["used_belt_grade"];
                            }

                            //$meta_units =  explode("||",$conveyor["Conveyor"]["meta_units"]);
                            //$units_width = explode("=",$meta_units[0]); //es el primer par

                            $meta_units = $this->Core->getPairsMetaUnits($conveyor["Conveyor"]["meta_units"]);
                            $units_width = $meta_units["ancho_banda"];

                            $width = "-";
                            if(!is_null($conveyor["Conveyor"]["banda_ancho"])){
                                if($units_width!="mm"){//$units_width[1] -> indice de ancho_banda
                                    $width = $conveyor["Conveyor"]["banda_ancho"]*25.4; //to mm
                                    $width = number_format($width, 0, "","");
                                }else{
                                    $width = $conveyor["Conveyor"]["banda_ancho"];
                                }
                            }


                            //$tension = explode("=",$meta_units[1]); //unidad de tension esta en segundo par
                            $tension = $meta_units["tension_banda"];
                            $units_tension = $tension == "PIW" ? "imperial" : "metric";


                            if(isset($conveyor["Conveyor"]["id_espesor_cubierta_sup"],$conveyor["Conveyor"]["id_espesor_cubierta_inf"], $conveyor["Conveyor"]["banda_ancho"], $conveyor["Conveyor"]["banda_tension"])
                                && $conveyor["Conveyor"]["banda_tension"]!="" && $conveyor["Conveyor"]["id_espesor_cubierta_sup"]>0 && $conveyor["Conveyor"]["id_espesor_cubierta_inf"]>0){
                                $key_top_cover = array_search($conveyor["Conveyor"]["id_espesor_cubierta_sup"], array_column($espesor_cubiertas, 'id'));
                                $key_bottom_cover = array_search($conveyor["Conveyor"]["id_espesor_cubierta_inf"], array_column($espesor_cubiertas, 'id'));
                                if($key_top_cover!==false && $key_bottom_cover!==false){
                                    list($top_cover_in, $top_cover_mm) = explode("-",$espesor_cubiertas[$key_top_cover]["titulo"]);
                                    list($bottom_cover_in, $bottom_cover_mm) = explode("-",$espesor_cubiertas[$key_bottom_cover]["titulo"]);
                                    $plies_number = isset($conveyor["TabInstalledBelt"]["plies_number"]) && $conveyor["TabInstalledBelt"]["plies_number"]!="" ? $conveyor["TabInstalledBelt"]["plies_number"]:0;
                                    if($units_tension == "imperial"){
                                        $top_cover_in = rtrim(str_replace("in","", $top_cover_in));
                                        $bottom_cover_in = rtrim(str_replace("in","", $bottom_cover_in));

                                        $width_spec = $units_width=="mm" ? $conveyor["Conveyor"]["banda_ancho"]*0.0393701 : $conveyor["Conveyor"]["banda_ancho"];//si esta en mm convetir a in
                                        $specification = $conveyor["Conveyor"]["banda_tension"]." piw/".$plies_number."/".$width_spec." ".$top_cover_in."x".$bottom_cover_in;
                                    }else{
                                        if(isset($conveyor["TabInstalledBelt"]["shell"]) && $conveyor["TabInstalledBelt"]["shell"]!=""){
                                            $top_cover_mm = ltrim(str_replace("mm","", $top_cover_mm));
                                            $bottom_cover_mm = ltrim(str_replace("mm","", $bottom_cover_mm));
                                            $plies = $conveyor["TabInstalledBelt"]["shell"]=="ST" || $conveyor["TabInstalledBelt"]["shell"]=="UNKW" ? 0:$plies_number;

                                            $width_spec = $units_width=="in" ? $conveyor["Conveyor"]["banda_ancho"]*25.4 : $conveyor["Conveyor"]["banda_ancho"];//si esta en in convetir a mm
                                            $specification = $width_spec." ".$conveyor["TabInstalledBelt"]["shell"]." ".$conveyor["Conveyor"]["banda_tension"]." / ".$plies." ".$top_cover_mm." + ".$bottom_cover_mm;;
                                        }

                                    }
                                }
                            }

                            //$units_length = explode("=",$meta_units[3]); // es el cuarto par
                            $units_length = $meta_units["desarrollo_banda"];
                            $length = "-";
                            if(!is_null($conveyor["Conveyor"]["banda_desarrollo_total"])){
                                if($units_length!="m"){ //esta en pies
                                    $length = $conveyor["Conveyor"]["banda_desarrollo_total"]*0.3048; //to m
                                    $length = number_format($length, 0, "","");
                                }else{
                                    $length = $conveyor["Conveyor"]["banda_desarrollo_total"]; //already m
                                }
                            }



                            $installation_belt = $conveyor["Conveyor"]["banda_fecha_instalacion"];
                            if(!is_null($conveyor["Conveyor"]["banda_fecha_instalacion"]) && $conveyor["Conveyor"]["banda_fecha_instalacion"]!="0000-00-00"){

                                //$fancy_inst_date = $this->Utilities->timestampToUsDate($conveyor["Conveyor"]["banda_fecha_instalacion"]);

                                $installed_date = date("Y-m-d", strtotime($conveyor["Conveyor"]["banda_fecha_instalacion"]));
                                $installed_date = new DateTime($installed_date);
                                $now   = new DateTime('now');


                                $interval = $now->diff($installed_date);
                                $elapsed_days = $interval->days;
                                $elapsed_years = $elapsed_days / 365;
                                $years_on_system = number_format($elapsed_years, "2",".", ",");
                            }
                        }

                        $gauge = [];
                        $has_ultrasonic = !is_null($ultrasonic_readings['ultrasonic_id']);
                        if($has_ultrasonic){
                            $gauge = $this->Core->calcAbrasionLife($conveyor["Conveyor"]["id"]);
                        }

                        $remain_lifetime_years = "-";
                        if(!empty($gauge)){
                            $projected_future_life = $gauge['projected_future_life'];
                            $remain_lifetime_years = $projected_future_life / 12;
                            $remain_lifetime_years = number_format($remain_lifetime_years, "1",".", "");
                        }

                        /*$recommended_value = "-";
                        $hasRecommendedBelt = $this->Core->sePuedeCalcularBandaRecomendada($conveyor);
                        if($hasRecommendedBelt){
                            $recommended = $this->Core->calcLifeEstimationBanda($conveyor);
                            $recommended_value = !is_null($recommended["recommended_conveyor_in"]) ? $recommended["recommended_conveyor_in"] : $recommended_value;
                        }*/



                        $tableData[] = [
                            "conveyor_id" => $conveyor["Conveyor"]["id"],
                            "number" => $conveyor["Conveyor"]["numero"],
                            "manufacturer" => $manufacturer,
                            "family" => $family,
                            "compound" => $compound,
                            "recommended" => $specification,
                            "years_on_system" => $years_on_system,
                            "installation_date" => $installation_belt,
                            "remain_lifetime_years" => $remain_lifetime_years,
                            "length" => $length,
                            "width" => $width,
                            "gauge" => $gauge,
                        ];
                    }

                    switch ($selected_order){
                        case "alphabetically":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'number', SORT_ASC);
                            break;
                        case "abrasion":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'remain_lifetime_years', SORT_ASC);
                            break;
                        case "specification":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'recommended', SORT_ASC);
                            break;
                        case "width":
                            $tableData = $this->Core->multidimensionalSort($tableData, 'width', SORT_ASC);
                            break;
                    }

                    $this->set('company',$company);
                    $this->set('tableData', $tableData);
                    $this->set('Socket', $this->CustomSocket);
                } else {
                    $this->redirect(array('controller' => 'Index', 'action' => 'index'));
                }
            } else {
                $this->redirect(array('controller' => 'Index', 'action' => 'index'));
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function add(){
        if ($this->request->is('post')) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array(); //Params receive id of client for request conveyors about
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $company_received = $decodedCompanyParams['item_id'];
                    $company = $this->Empresa->findById($company_received);
                    if (!empty($company)) {
                        $filter_companies = $company_received;
                        $conveyors = $this->Core->getConveyorsUsingFilters($filter_companies);
                        $conveyors_array = [];
                        $conveyors_array[0] = '';
                        if(!empty($conveyors)){
                           foreach($conveyors AS $conveyor){
                               $conveyor = $conveyor['Conveyor'];
                               $conveyors_array[$conveyor['id']] = $conveyor['numero'];
                           }
                        }
                        $this->set('conveyors',$conveyors_array);
                        $this->set('clientId',$company_received);
                    }
                }
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function save(){
        $this->layout = false;
        if ($this->request->is('post')) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedCompanyParams = $this->Core->decodePairParams($params);
                if ($decodedCompanyParams['isOk']) {
                    $clientId = $decodedCompanyParams['item_id'];
                    $data = $this->request->data; //get data
                    parse_str($data['formdata'], $formdata);

                    if($formdata['conveyor']<=0 || $formdata['belt_manufacturer']<=0 || !isset($formdata['family'],$formdata['compounds_top_cover'])){
                        $response['code'] = 0; //Indice de la pestania en activar
                        $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                    } else if(!isset($formdata['tension_unit']) || (isset($formdata['plies']) && $formdata['plies']==0)
                        || (!isset($formdata['tension']) && $formdata['tension_steel']=='') || (isset($formdata['tension']) && $formdata['tension']==0 && $formdata['tension_steel']=='')
                        || (!isset($formdata['width']) && $formdata['other_width']=='') || (isset($formdata['width']) && $formdata['width']==0 && $formdata['other_width']=='')
                        || (!isset($formdata['top_cover']) && $formdata['top_cover_metric']=='') || (isset($formdata['top_cover']) && $formdata['top_cover']==0 && $formdata['top_cover_metric']=='')
                        || (!isset($formdata['pulley_cover']) && $formdata['pulley_cover_metric']=='') || (isset($formdata['pulley_cover']) && $formdata['pulley_cover']==0 && $formdata['pulley_cover_metric']=='')){
                        $response['code'] = 1; //Indice de la pestania en activar
                        $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                        $response['form'] = $formdata;
                    } else if($formdata['date_install']=='' || $formdata['date_failed']=='' || $formdata['years_system']=='' || $formdata['failure_mode']==''){
                        $response['code'] = 2; //Indice de la pestania en activar
                        $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                    }else{
                        $failure_mode = implode(',',$formdata['failure_mode']);
                        $plies = isset($formdata['plies']) ? $formdata['plies'] : 0;
                        $tension = isset($formdata['tension']) ? $formdata['tension'] : 0;
                        $date_install = $this->Core->transformUsDatetoMysqlFormat($formdata['date_install']);
                        $date_failed = $this->Core->transformUsDatetoMysqlFormat($formdata['date_failed']);

                        $history_reg = array(
                            'client_id' => $clientId,
                            'conveyor_id' => $formdata['conveyor'],
                            'belt_manufacturer' => $formdata['belt_manufacturer'],
                            'family' => $formdata['family'],
                            'compounds_top_cover' => $formdata['compounds_top_cover'],
                            'fabric_type' => $formdata['fabric_type'],
                            'tension_unit' => $formdata['tension_unit'],
                            'tension' => $tension,
                            'tension_steel' => $formdata['tension_steel'],
                            'plies' => $plies,
                            'width' => $formdata['width'],
                            'other_width' => $formdata['other_width'],
                            'top_cover' => $formdata['top_cover'],
                            'top_cover_metric' => $formdata['top_cover_metric'],
                            'pulley_cover' => $formdata['pulley_cover'],
                            'pulley_cover_metric' => $formdata['pulley_cover_metric'],
                            'other_special' => $formdata['other_special'],
                            'other_special_data' => $formdata['other_special_data'],
                            'date_install' => $date_install,
                            'date_failed' => $date_failed,
                            'years_system' => $formdata['years_system'],
                            'failure_mode' => $failure_mode,
                            'remarks' => $formdata['remarks']
                        );

                        if ($this->History->save($history_reg)) {
                            $response['success'] = true;
                            $response['msg'] = __('v.2.5.1.HistoryHasBeenSaved', true);
                            //save score card statistic

                            $salespersonAssoc = $this->Core->getSalespersonIfExists($clientId);
                            if($salespersonAssoc>0){
                                $this->Secure->saveStatisticData($salespersonAssoc, Statistic::NEW_BELT_HISTORY);
                            }
                        } else {
                            $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                        }
                    }
                }
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function edit(){
        if ($this->request->is('post')) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array(); //Params receive id of history
            if (!empty($params) && count($params) == 2) {
                $decodedHistoryParams = $this->Core->decodePairParams($params);
                if ($decodedHistoryParams['isOk']) {
                    $history_received = $decodedHistoryParams['item_id'];
                    $history = $this->History->findById($history_received);
                    if (!empty($history)) {
                        $company_received = $history['History']['client_id'];
                        $filter_companies = $company_received;
                        $conveyors = $this->Core->getConveyorsUsingFilters($filter_companies);
                        $conveyors_array = [];
                        $conveyors_array[0] = '';
                        if(!empty($conveyors)){
                            foreach($conveyors AS $conveyor){
                                $conveyor = $conveyor['Conveyor'];
                                $conveyors_array[$conveyor['id']] = $conveyor['numero'];
                            }
                        }
                        $this->set('conveyors',$conveyors_array);
                        $this->set('historyId',$history_received);
                        $this->set('history',$history['History']);
                    }
                }
            }
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function update() {
        $this->layout = false;
        if ($this->request->is('post')) {
            $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
            $response = array('success' => false, 'msg' => '', 'code' => -1);
            if (!empty($params) && count($params) == 2) {
                $decodedHistoryParams = $this->Core->decodePairParams($params);
                if ($decodedHistoryParams['isOk']) {
                    $item_received = $decodedHistoryParams['item_id'];
                    $history = $this->History->findById($item_received);
                    $data = $this->request->data; //get data
                    parse_str($data['formdata'], $formdata);

                    if (!empty($history)) {

                        if($formdata['conveyor']<=0 || $formdata['belt_manufacturer']<=0 || !isset($formdata['family'],$formdata['compounds_top_cover'])){
                            $response['code'] = 0; //Indice de la pestania en activar
                            $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                        } else if(!isset($formdata['tension_unit']) || (isset($formdata['plies']) && $formdata['plies']==0)
                            || (!isset($formdata['tension']) && $formdata['tension_steel']=='') || (isset($formdata['tension']) && $formdata['tension']==0 && $formdata['tension_steel']=='')
                            || (!isset($formdata['width']) && $formdata['other_width']=='') || (isset($formdata['width']) && $formdata['width']==0 && $formdata['other_width']=='')
                            || (!isset($formdata['top_cover']) && $formdata['top_cover_metric']=='') || (isset($formdata['top_cover']) && $formdata['top_cover']==0 && $formdata['top_cover_metric']=='')
                            || (!isset($formdata['pulley_cover']) && $formdata['pulley_cover_metric']=='') || (isset($formdata['pulley_cover']) && $formdata['pulley_cover']==0 && $formdata['pulley_cover_metric']=='')){
                            $response['code'] = 1; //Indice de la pestania en activar
                            $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                            $response['form'] = $formdata;
                        } else if($formdata['date_install']=='' || $formdata['date_failed']=='' || $formdata['years_system']=='' || $formdata['failure_mode']==''){
                            $response['code'] = 2; //Indice de la pestania en activar
                            $response['msg'] = __('v.2.5.1.PleaseFillRequiredData', true);
                        }else {
                            $failure_mode = implode(',',$formdata['failure_mode']);
                            $plies = isset($formdata['plies']) ? $formdata['plies'] : 0;
                            $tension = isset($formdata['tension']) ? $formdata['tension'] : 0;
                            $date_install = $this->Core->transformUsDatetoMysqlFormat($formdata['date_install']);
                            $date_failed = $this->Core->transformUsDatetoMysqlFormat($formdata['date_failed']);

                            $history['History']['conveyor_id'] = $formdata['conveyor'];
                            $history['History']['belt_manufacturer'] = $formdata['belt_manufacturer'];
                            $history['History']['family'] = $formdata['family'];
                            $history['History']['compounds_top_cover'] = $formdata['compounds_top_cover'];
                            $history['History']['fabric_type'] = $formdata['fabric_type'];
                            $history['History']['tension_unit'] = $formdata['tension_unit'];
                            $history['History']['tension'] = $tension;
                            $history['History']['tension_steel'] = $formdata['tension_steel'];
                            $history['History']['plies'] = $plies;
                            $history['History']['width'] = $formdata['width'];
                            $history['History']['other_width'] = $formdata['other_width'];
                            $history['History']['top_cover'] = $formdata['top_cover'];
                            $history['History']['top_cover_metric'] = $formdata['top_cover_metric'];
                            $history['History']['pulley_cover'] = $formdata['pulley_cover'];
                            $history['History']['pulley_cover_metric'] = $formdata['pulley_cover_metric'];
                            $history['History']['other_special'] = $formdata['other_special'];
                            $history['History']['other_special_data'] = $formdata['other_special_data'];
                            $history['History']['date_install'] = $date_install;
                            $history['History']['date_failed'] = $date_failed;
                            $history['History']['years_system'] = $formdata['years_system'];
                            $history['History']['failure_mode'] = $failure_mode;
                            $history['History']['remarks'] = $formdata['remarks'];

                                if ($this->History->save($history)) {
                                    $response['success'] = true;
                                    $response['msg'] = __('v.2.5.1.HistoryHasBeenSaved', true);
                                } else {
                                    $response['msg'] = __('Ocurrio un error al procesar la informacion, intentelo nuevamente', true);
                                }
                        }
                    } else {
                        $response['msg'] = __('Error al consultar elemento', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function getConveyorInfo(){
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            $data = $this->request->data; //get data
            $conveyorId = $data['conveyor'];
            $conveyor = $this->Conveyor->findFullById($conveyorId);
            echo json_encode($conveyor);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function remove() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedItemsParams = $this->Core->decodePairParams($params);
                if ($decodedItemsParams['isOk']) {
                    $item_received = $decodedItemsParams['item_id'];
                    $item = $this->History->findById($item_received);
                    if (!empty($item)) {
                        $this->History->id = $item_received;
                        $this->History->saveField('deleted', true);
                        $response['msg'] = __('El elemento fue eliminado exitosamente', true);
                        $response['success'] = true;
                    } else {
                        $response['msg'] = __('Error, el elemento a eliminar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }
}