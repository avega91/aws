<?php

class UtilitiesHelper extends AppHelper {

    public $helpers = array('Html');

    /**
     * Codifica el id de un usuario, ademas le agrega un digestivo para verificarlo en la decodificada
     * @param int $user_id el id del usuario
     * @return array
     */
    public function encodeUserParams($user_id) {
        $coded_params = array();
        $coded_params['user'] = base64_encode($user_id . '|' . _B64_SIGNATURE);
        $coded_params['digest'] = sha1($user_id . '|' . _SHA1_SIGNATURE);
        return $coded_params;
    }

    /**
     * Codifica el un identificador ya sea de album, user, photo, report etc
     * @param int $id_item el id del item
     * @return array
     */
    public function encodeParams($id_item) {
        $coded_params = array();
        $coded_params['item_id'] = base64_encode($id_item . '|' . _B64_SIGNATURE);
        $coded_params['digest'] = sha1($id_item . '|' . _SHA1_SIGNATURE);
        return $coded_params;
    }


    public function createFolderBreadcrum($folderList){
            $credentials = $this->_View->getVar('credentials');
            $client_link = $credentials['i_group_id'] > IGroup::CLIENT_MANAGER ? $this->Html->link(__('Customers', true), ['controller'=>'/','action'=>'customers']) : '<span class="tail">'.__('Customers', true).'</span>';
            echo $client_link;
            $breadcrum = ' <span class="separator">/</span> ';
            $client = false;
            $lastFolder = end($folderList);
            foreach($folderList AS $folderApp){
                if(!$client){
                    $client = $folderApp['Client'];
                    $secureClientParams = $this->encodeParams($client['id']);
                    $breadcrum .= $this->Html->link($client['name'], ['controller'=>'/','action'=>'/customer/buoys/', $secureClientParams['item_id'], $secureClientParams['digest']]);
                }
                $breadcrum .= ' <span class="separator">/</span> ';
                if($lastFolder != $folderApp){
                    $folderApp = $folderApp['FolderApp'];
                    $secureFolderParams = $this->encodeParams($folderApp['id']);
                    $breadcrum .= $this->Html->link($folderApp['name'], ['controller'=>'/','action'=>'/buoy/data/', $secureFolderParams['item_id'], $secureFolderParams['digest']]);
                }else{
                    $folderApp = $folderApp['FolderApp'];
                    $breadcrum .= '<span class="tail">'.$folderApp['name'].'</span>';
                }
                
            }
            


            echo $breadcrum;
    }

    /**
     * Acorta una cadena muy larga
     * @param string $string la cadena a cortar
     * @param int $limit el limite de caracteres
     * @return type
     */
    public function adjustText($string, $limit = 10) {
        $final_string = '';
        $len = strlen($string);
        if ($len > $limit) {
            $to_sub = $len - $limit;
            $crop_temp = substr($string, 0, -$to_sub);
            $final_string = $crop_len = $crop_temp . "...";
        } else {
            $final_string = $string;
        }
        return $final_string;
    }

    /**
     * Funcion evita que el texto se salga de un div
     * @param string $string el texto
     * @param int $width
     * @param caracter de salto $break
     * @return string
     */
    function smart_wordwrap($string, $width = 75, $break = "\n") {
        // split on problem words over the line length
        $pattern = sprintf('/([^ ]{%d,})/', $width);
        $output = '';
        $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($words as $word) {
            if (false !== strpos($word, ' ')) {
                // normal behaviour, rebuild the string
                $output .= $word;
            } else {
                // work out how many characters would be on the current line
                $wrapped = explode($break, wordwrap($output, $width, $break));
                $count = $width - (strlen(end($wrapped)) % $width);
                // fill the current line and add a break
                $output .= substr($word, 0, $count) . $break;
                // wrap any remaining characters from the problem word
                $output .= wordwrap(substr($word, $count), $width, $break, true);
            }
        }
        // wrap the final output
        return wordwrap($output, $width, $break);
    }

    /**
     * Transform date to format MES xx anio  -> ENERO 13 2013
     * @param date $date la fecha en formato normal (aaaa-mm-dd) o timestamp
     * @return string
     */
    public function transformVisualFormatDate($date, $isTimeStamp = false) {
        if ($isTimeStamp) {
            list($date, $time) = explode(' ', $date);
        }

        list($anio, $mes, $dia) = explode('-', $date);
        return $this->getMonthName($mes) . ' ' . $dia . ', ' . $anio;
    }

    /**
     * Transform date to format MES anio  -> ENERO 2013
     * @param date $date la fecha en formato normal (aaaa-mm-dd) o timestamp
     * @return string
     */
    public function transformVisualShortFormatDate($date, $isTimeStamp = false) {
        $transformed = '-';
        if (!is_null($date)) {
            if ($isTimeStamp) {
                list($date, $time) = explode(' ', $date);
            }

            list($anio, $mes, $dia) = explode('-', $date);
            $transformed = $this->getMonthName($mes) . ' ' . $anio;
        }

        return $transformed;
    }

    /**
     * Comvierte una fecha del formato timestamp al formato dd/mm/yyyy segun el separador de fecha
     * @param timestamp $date timestamp
     * @param string $separator cadena de separacion
     * @param string $separator cadena de final
     * @return date
     */
    public function timestampToCorrectFormat($date, $separator = '/', $glue_separator = '') {
        //$glue_separator = $glue_separator=='' ? $separator : $glue_separator;
        //list($fecha, $hora) = explode(' ', $date);
        $datetime = explode(' ', $date);
        $fecha = $datetime[0];
        $fecha = explode('-', $fecha);
        $fecha = array_reverse($fecha);
        $fecha = implode($separator, $fecha);
        return $fecha;
    }
    
    public function timestampToCorrectFormatLanguage($date, $original_separator = '-', $final_separator = '/') {
        $lang = $this->_View->getVar('app_lang');
        $datetime = explode(' ', $date);
        $fecha = $datetime[0];        
        $date_aux = explode($original_separator, $fecha);

        $date_aux = $lang == IS_ESPANIOL ? array($date_aux[2], $date_aux[1], $date_aux[0]) : array($date_aux[1], $date_aux[2], $date_aux[0]);
        $date = implode($final_separator, $date_aux);
        return $date;
    }
    
    /**
     * Transform to american/latin visual date
     * @param datetime $date
     * @return string
     */
   public function timestampToUsDate($date) {
       $datetime = explode(' ', $date);
       list($anio, $mes, $dia) = explode('-', $datetime[0]);
       $transformed = $this->getMonthName($mes).' '. $dia .', '. $anio;
       return $transformed;
    }


    /**
     * Regresa el nombre del mes
     * @param string $month_index index month
     * @return string month name
     */
    public function getMonthName($month_index) {
        $months = array();
        $months['00'] = '--';
        $months['01'] = 'Ene';
        $months['02'] = 'Feb';
        $months['03'] = 'Mar';
        $months['04'] = 'Abr';
        $months['05'] = 'May';
        $months['06'] = 'Jun';
        $months['07'] = 'Jul';
        $months['08'] = 'Ago';
        $months['09'] = 'Sep';
        $months['10'] = 'Oct';
        $months['11'] = 'Nov';
        $months['12'] = 'Dic';

        $months_en = array();
        $months_en['00'] = '-';
        $months_en['01'] = 'Jan';
        $months_en['02'] = 'Feb';
        $months_en['03'] = 'Mar';
        $months_en['04'] = 'Apr';
        $months_en['05'] = 'May';
        $months_en['06'] = 'Jun';
        $months_en['07'] = 'Jul';
        $months_en['08'] = 'Aug';
        $months_en['09'] = 'Sep';
        $months_en['10'] = 'Oct';
        $months_en['11'] = 'Nov';
        $months_en['12'] = 'Dec';

        $lang = $this->_View->getVar('app_lang');
        return $lang == 'es' ? $months[$month_index] : $months_en[$month_index];
    }

    /**
     * Regresa el nombre del mes
     * @param string $month_index index month
     * @return string month name
     */
    public function getFullMonthName($month_index) {
        $months = array();
        $months['00'] = '--';
        $months['01'] = 'Enero';
        $months['02'] = 'Febrero';
        $months['03'] = 'Marzo';
        $months['04'] = 'Abril';
        $months['05'] = 'Mayo';
        $months['06'] = 'Junio';
        $months['07'] = 'Julio';
        $months['08'] = 'Agosto';
        $months['09'] = 'Septiembre';
        $months['10'] = 'Octubre';
        $months['11'] = 'Noviembre';
        $months['12'] = 'Diciembre';

        $months_en = array();
        $months_en['00'] = '-';
        $months_en['01'] = 'January';
        $months_en['02'] = 'February';
        $months_en['03'] = 'March';
        $months_en['04'] = 'April';
        $months_en['05'] = 'May';
        $months_en['06'] = 'June';
        $months_en['07'] = 'July';
        $months_en['08'] = 'August';
        $months_en['09'] = 'September';
        $months_en['10'] = 'October';
        $months_en['11'] = 'November';
        $months_en['12'] = 'December';

        $lang = $this->_View->getVar('app_lang');
        return $lang == 'es' ? $months[$month_index] : $months_en[$month_index];
    }
    
    /**
     * Function: sanitize
     * Returns a sanitized string, typically for URLs.
     *
     * Parameters:
     *     $string - The string to sanitize.
     *     $force_lowercase - Force the string to lowercase?
     *     $anal - If set to *true*, will remove all non-alphanumeric characters.
     */
    function sanitize($string, $force_lowercase = true, $anal = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return ($force_lowercase) ?
                (function_exists('mb_strtolower')) ?
                        mb_strtolower($clean, 'UTF-8') :
                        strtolower($clean) :
                $clean;
    }

    public function putSiteAssets() {
        $image_files = $this->_View->getVar('site_assets'); //mandada desde appController
        ?>
        <div id="preloader_imgs" style="display: none !important;">
            <?php
            foreach ($image_files AS $image) {
                //echo '<img src="' . $image . '"/>';
                //echo "<img data-src='".$image."' class='lazyload'>";
            }
            ?>
        </div>
        <?php
    }

    public function sanitizeValuesArray($container){
        if(!empty($container)){
            foreach($container AS $index => $value){
                $container[$index] = $container[$index] == '' || is_null($container[$index]) ? '-' : $container[$index];
            }
        }
        return $container;
    }

    public function sanitizeEmptyValuesConveyor($conveyor) {
        $conveyor['trans_distancia_centros'] = $conveyor['trans_distancia_centros'] == '' ? '-' : $conveyor['trans_distancia_centros'];
        $conveyor['trans_elevacion'] = $conveyor['trans_elevacion'] == '' ? '-' : $conveyor['trans_elevacion'];
        $conveyor['trans_hp_motor'] = $conveyor['trans_hp_motor'] == '' ? '-' : $conveyor['trans_hp_motor'];
        $conveyor['trans_relacion_reductor'] = $conveyor['trans_relacion_reductor'] == '' ? '-' : $conveyor['trans_relacion_reductor'];
        $conveyor['trans_rpm_motor'] = $conveyor['trans_rpm_motor'] == '' ? '-' : $conveyor['trans_rpm_motor'];
        $conveyor['trans_angulo_inclinacion'] = $conveyor['trans_angulo_inclinacion'] == '' ? '-' : $conveyor['trans_angulo_inclinacion'];
        $conveyor['trans_capacidad'] = $conveyor['trans_capacidad'] == '' ? '-' : $conveyor['trans_capacidad'];
        $conveyor['trans_carga'] = $conveyor['trans_carga'] == '' ? '-' : $conveyor['trans_carga'];

        $conveyor['tensor_tipo'] = is_null($conveyor['tensor_tipo']) ? '-' : $conveyor['tensor_tipo'];
        $conveyor['tensor_peso_estimado'] = $conveyor['tensor_peso_estimado'] == '' ? '-' : $conveyor['tensor_peso_estimado'];
        $conveyor['tensor_carrera'] = $conveyor['tensor_carrera'] == '' ? '-' : $conveyor['tensor_carrera'];

        $conveyor['mat_descripcion'] = is_null($conveyor['mat_descripcion']) ? '-' : $conveyor['mat_descripcion'];
        $conveyor['mat_densidad'] = $conveyor['mat_densidad'] == '' ? '-' : $conveyor['mat_densidad'];
        $conveyor['mat_tam_terron'] = $conveyor['mat_tam_terron'] == '' ? '-' : $conveyor['mat_tam_terron'];
        $conveyor['mat_temperatura'] = $conveyor['mat_temperatura'] == '' ? '-' : $conveyor['mat_temperatura'];
        $conveyor['mat_altura_caida'] = $conveyor['mat_altura_caida'] == '' ? '-' : $conveyor['mat_altura_caida'];
        $conveyor['mat_porcentaje_finos'] = $conveyor['mat_porcentaje_finos'] == '' ? '-' : $conveyor['mat_porcentaje_finos'];

        $conveyor['banda_ancho'] = $conveyor['banda_ancho'] == '' ? '-' : $conveyor['banda_ancho'];
        $conveyor['banda_tension'] = $conveyor['banda_tension'] == '' ? '-' : $conveyor['banda_tension'];
        $conveyor['banda_espesor_cubiertas'] = $conveyor['banda_espesor_cubiertas'] == '' ? '-' : $conveyor['banda_espesor_cubiertas'];
        $conveyor['banda_velocidad'] = $conveyor['banda_velocidad'] == '' ? '-' : $conveyor['banda_velocidad'];
        $conveyor['banda_fecha_instalacion'] = $conveyor['banda_fecha_instalacion'] == '' ? '-' : $conveyor['banda_fecha_instalacion'];
        $conveyor['banda_marca'] = $conveyor['banda_marca'] == '' ? '-' : $conveyor['banda_marca'];
        $conveyor['banda_desarrollo_total'] = $conveyor['banda_desarrollo_total'] == '' ? '-' : $conveyor['banda_desarrollo_total'];
        $conveyor['banda_operacion'] = $conveyor['banda_operacion'] == '' ? '-' : $conveyor['banda_operacion'];

        $conveyor['polea_motriz'] = $conveyor['polea_motriz'] == '' ? '-' : $conveyor['polea_motriz'];
        $conveyor['ancho_polea_motriz'] = $conveyor['ancho_polea_motriz'] == '' ? '-' : $conveyor['ancho_polea_motriz'];
        $conveyor['polea_recubrimiento'] = $conveyor['polea_recubrimiento'] == '' ? '-' : $conveyor['polea_recubrimiento'];
        $conveyor['polea_arco_contacto'] = is_null($conveyor['polea_arco_contacto']) ? '-' : $conveyor['polea_arco_contacto'];
        $conveyor['polea_cabeza'] = $conveyor['polea_cabeza'] == '' ? '-' : $conveyor['polea_cabeza'];
        $conveyor['ancho_pol_cabeza'] = $conveyor['ancho_pol_cabeza'] == '' ? '-' : $conveyor['ancho_pol_cabeza'];
        $conveyor['polea_cola'] = $conveyor['polea_cola'] == '' ? '-' : $conveyor['polea_cola'];
        $conveyor['ancho_pol_cola'] = $conveyor['ancho_pol_cola'] == '' ? '-' : $conveyor['ancho_pol_cola'];
        $conveyor['polea_contacto'] = $conveyor['polea_contacto'] == '' ? '-' : $conveyor['polea_contacto'];
        $conveyor['ancho_pol_contacto'] = $conveyor['ancho_pol_contacto'] == '' ? '-' : $conveyor['ancho_pol_contacto'];
        $conveyor['polea_doblez'] = $conveyor['polea_doblez'] == '' ? '-' : $conveyor['polea_doblez'];
        $conveyor['ancho_pol_doblez'] = $conveyor['ancho_pol_doblez'] == '' ? '-' : $conveyor['ancho_pol_doblez'];
        $conveyor['polea_tensora'] = $conveyor['polea_tensora'] == '' ? '-' : $conveyor['polea_tensora'];
        $conveyor['ancho_pol_tensora'] = $conveyor['ancho_pol_tensora'] == '' ? '-' : $conveyor['ancho_pol_tensora'];
        $conveyor['polea_uno_adicional'] = $conveyor['polea_uno_adicional'] == '' ? '-' : $conveyor['polea_uno_adicional'];
        $conveyor['ancho_polea_uno_adicional'] = $conveyor['ancho_polea_uno_adicional'] == '' ? '-' : $conveyor['ancho_polea_uno_adicional'];
        $conveyor['polea_dos_adicional'] = $conveyor['polea_dos_adicional'] == '' ? '-' : $conveyor['polea_dos_adicional'];
        $conveyor['ancho_pol_dos_adicional'] = $conveyor['ancho_pol_dos_adicional'] == '' ? '-' : $conveyor['ancho_pol_dos_adicional'];

        $conveyor['rod_diam_impacto'] = $conveyor['rod_diam_impacto'] == '' ? '-' : $conveyor['rod_diam_impacto'];
        $conveyor['rod_ang_impacto'] = is_null($conveyor['rod_ang_impacto']) ? '-' : $conveyor['rod_ang_impacto'];
        $conveyor['rod_diam_carga'] = is_null($conveyor['rod_diam_carga']) ? '-' : $conveyor['rod_diam_carga'];
        $conveyor['rod_ang_carga'] = is_null($conveyor['rod_ang_carga']) ? '-' : $conveyor['rod_ang_carga'];
        $conveyor['rod_diam_retorno'] = is_null($conveyor['rod_diam_retorno']) ? '-' : $conveyor['rod_diam_retorno'];
        $conveyor['rod_ang_retorno'] = $conveyor['rod_ang_retorno'] == '' ? '-' : $conveyor['rod_ang_retorno'];
        $conveyor['rod_espacio_ldc'] = $conveyor['rod_espacio_ldc'] == '' ? '-' : $conveyor['rod_espacio_ldc'];
        $conveyor['rod_espacio_ldr'] = $conveyor['rod_espacio_ldr'] == '' ? '-' : $conveyor['rod_espacio_ldr'];

        return $conveyor;
    }

    function getTimeAgo($timestamp) {
        //calculate difference between server time and given timestamp
        $timestamp = time() - $timestamp;

        //if no time was passed return 0 seconds
        if ($timestamp < 1) {
            return '0 seconds';
        }

        //create multi-array with seconds and define values
        $values = array(
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hr',
            60 => 'min',
            1 => 'second'
        );

        //loop over the array
        foreach ($values as $secs => $point) {
            //check if timestamp is equal or bigger the array value
            $divRes = $timestamp / $secs;
            if ($divRes >= 1) {
                //if timestamp is bigger, round the divided value and return it
                $res = round($divRes);
                return $res . ' ' . $point . ($res > 1 ? 's' : '');
            }
        }
    }

    public function saveViewReport($name, $path_pdf, $parent_conveyor, $parent_folder) {
        $credentials = $this->_View->getVar('credentials');
        App::import('Model', 'Report');
        $this->Report = new Report();
        $this->Report->save(array(
            'nombre' => $name,
            'file' => $path_pdf,
            'parent_conveyor' => $parent_conveyor,
            'parent_folder' => $parent_folder,
            'owner_user' => $credentials['id'],
            'actualizada' => date('Y-m-d H:i:s')
        ));
    }

    public function addOrdinalNumberSuffix($num) {
        /*
        $lang = $this->_View->getVar('app_lang');
        if (!in_array(($num % 100), array(11, 12, 13, 14))) {
            if($lang == 'es'){
                switch ($num % 10) {
                    case 1: return $num . 'ra';
                    case 2: return $num . 'da';
                    case 3: return $num . 'rd';
                }
            }else{
                switch ($num % 10) {
                   // Handle 1st, 2nd, 3rd
                   case 1: return $num . 'st';
                   case 2: return $num . 'nd';
                   case 3: return $num . 'rd';
               }   
            }            
        }
        return $num . 'th';*/
        return $num . '&deg;';
    }

    public function diffInMonths($date1, $date2) {
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $diff = $date1->diff($date2);
        $months = $diff->y * 12 + $diff->m + $diff->d / 30;
        return (int) round($months);
    }

    public function printAbrasionLife($abrasionData, $container_id = ""){
        $class_durometer = '';
        $durometer = $abrasionData['durometer'];
        if(($durometer>=45 && $durometer<55) || ($durometer>73 && $durometer<83)){
            $class_durometer = 'attention';
        }else if($durometer>=55 && $durometer<=73){
            $class_durometer = 'ok';
        }else if($durometer<45 || $durometer>=83){
            $class_durometer = 'danger';
        }

        $projected_future_life = $abrasionData['projected_future_life'];
        if($projected_future_life>11){
            $years = $projected_future_life / 12;
            if($years>15) {
                $projected_future_life = __("15+ years left", true);
            }else{
                $projected_future_life = __("%s years left", array(number_format($years, 1)));
            }
        }else{
            $projected_future_life = __("%s months left", array($projected_future_life));
        }

        $percent_cover_available = 100 - $abrasionData['percent_cover_used'];
    ?>
        <div id="gauge_div<?php echo $container_id; ?>" class="gauge-chart" data-id="<?php echo $container_id; ?>" data-reference="<?php echo $percent_cover_available; ?>"></div>
        <div class="info-gauge">
            <div class="percent"><?php echo $percent_cover_available; ?>%</div>
            <div class="months"><?php echo $projected_future_life; ?></div>
            <div class="durometer <?php echo $class_durometer; ?>"><span><?php echo __("%s durometer", array($abrasionData['durometer'])); ?></span></div>
        </div>
    <?php
    }

    public function getRealRegion($region) {
        $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
        $region = in_array($region, array('MX1','MX2','CENAM','CHILE','BRASIL','AUSTRALIA')) ? 'MX' : $region;
        $region = in_array($region, array('NORTHEAST','MIDWEST','SOUTH','WEST','SOUTHEAST','US')) ? 'US' : $region;
        $region = preg_match("/\bCA-\b/i", $region) ? 'US' : $region;
        //$region = in_array($region, array('CHILE')) ? 'CL' : $region;
        //$region = in_array($region, array('BRASIL')) ? 'BR' : $region;
        //$region = in_array($region, array('AUSTRALIA')) ? 'AU' : $region;
        //$region = in_array($region, array('CENAM')) ? 'CENAM' : $region;

        return $region;
    }

    public function createAssocArrayFromCsvValues($values){
       $data = [];
       foreach ($values AS $value_arr){
           $data[$value_arr[0]] = [$value_arr[1],$value_arr[2]];
       }
       return $data;
    }

}
