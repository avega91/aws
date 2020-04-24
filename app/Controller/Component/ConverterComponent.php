<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ConverterComponent.php
 *     Component to manage unit convertion methods
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class ConverterComponent extends Component {
    var $components = array('Core'); // the other component your component uses

    /**
     * Class constructor
     */
    public function __construct(ComponentCollection $collection, array $settings = array()) {
        parent::__construct($collection, $settings);
    }
    
    public function process_convertion_ultrasonic($ultrasonic, $language = null, $ultrasonic_row = null){ //$ultrasonic_row es el ultrasoni asociado
        $app_language = is_null($language) ? $this->Core->_app_language : $language;    
        $ultrasonic_language = $ultrasonic['UltrasonicReading']['filled_lang'];
        $units_ultrasonic = $ultrasonic_row["Ultrasonic"]["units"];
        $relation_convertions = array();

        if($ultrasonic_language=='es' && $units_ultrasonic=='imperial'){
            $app_language = 'es'; //para que haga automaticamente la conversion a Farenheit
        }else if($ultrasonic_language=='en' && $units_ultrasonic=='metric'){
            $app_language = 'en'; //para que haga automaticamente la conversion a Centigrados
        }else{
            $app_language = $ultrasonic_language;
        }
        //$app_language = !is_null($ultrasonic_row) && $ultrasonic_row["Ultrasonic"]["units"]!='imperial' ?
        $relation_convertions['temperature'] = array('es-es' => '*1)','es-en'=>'*1.8000)+32','en-en'=>'*1)','en-es'=>'-32)/1.8000');
        
        $ultrasonic['UltrasonicReading']['temperature'] = $ultrasonic['UltrasonicReading']['temperature']=='' ? '(0' : '('.$ultrasonic['UltrasonicReading']['temperature'];
        $ultrasonic['UltrasonicReading']['temperature'] .= $relation_convertions['temperature'][$ultrasonic_language.'-'.$app_language];
        $convertion = $ultrasonic['UltrasonicReading']['temperature'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $ultrasonic['UltrasonicReading']['temperature'] = round(eval("return ($convertion);"));
        
        return $ultrasonic;
    }
    public function process_convertion($conveyor, $language = null){
        return $conveyor;
    }
    public function process_convertion_old($conveyor, $language = null){
        $app_language = is_null($language) ? $this->Core->_app_language : $language;        
        $conveyor_language = $conveyor['Conveyor']['filled_lang'];
        
        //indices language-language = filled_lang - app_lang
        $relation_convertions = array();
        $relation_convertions['trans_distancia_centros'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['trans_elevacion'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['tensor_carrera'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['mat_temperatura'] = array('es-es' => '*1)','es-en'=>'*1.8000)+32','en-en'=>'*1)','en-es'=>'-32)/1.8000');
        $relation_convertions['mat_altura_caida'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['banda_desarrollo_total'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['rod_espacio_ldc'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        $relation_convertions['rod_espacio_ldr'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');

        //relaciones de conversion genericas
        //Centigrados/Farenheit
        $relation_convertions['temperature'] = array('es-es' => '*1)','es-en'=>'*1.8000)+32','en-en'=>'*1)','en-es'=>'-32)/1.8000');
        //Metros/Pies
        $relation_convertions['meters'] = array('es-es' => '*1','es-en'=>'/0.305','en-en'=>'*1','en-es'=>'*0.305');
        
        $conveyor['Conveyor']['trans_distancia_centros'] = $conveyor['Conveyor']['trans_distancia_centros']=='' ? 0 : $conveyor['Conveyor']['trans_distancia_centros'];
        $conveyor['Conveyor']['trans_distancia_centros'] .= $relation_convertions['trans_distancia_centros'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['trans_distancia_centros'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['trans_distancia_centros'] = round(eval("return ($convertion);"),2);
        
        $conveyor['Conveyor']['trans_elevacion'] = $conveyor['Conveyor']['trans_elevacion']=='' ? 0 : $conveyor['Conveyor']['trans_elevacion'];
        $conveyor['Conveyor']['trans_elevacion'] .= $relation_convertions['trans_elevacion'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['trans_elevacion'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['trans_elevacion'] = round(eval("return ($convertion);"));
        
        $conveyor['Conveyor']['tensor_carrera'] = $conveyor['Conveyor']['tensor_carrera']=='' ? 0 : $conveyor['Conveyor']['tensor_carrera'];
        $conveyor['Conveyor']['tensor_carrera'] .= $relation_convertions['tensor_carrera'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['tensor_carrera'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['tensor_carrera'] = round(eval("return ($convertion);"),2);
        
        $conveyor['Conveyor']['mat_temperatura'] = $conveyor['Conveyor']['mat_temperatura']=='' ? '(0' : '('.$conveyor['Conveyor']['mat_temperatura'];
        $conveyor['Conveyor']['mat_temperatura'] .= $relation_convertions['mat_temperatura'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['mat_temperatura'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['mat_temperatura'] = round(eval("return ($convertion);"));
        
        //Reemplazamos las comas por su respectivo punto decimal
        $conveyor['Conveyor']['mat_altura_caida'] = str_replace(',', '.', $conveyor['Conveyor']['mat_altura_caida']);        
        $conveyor['Conveyor']['mat_altura_caida'] = $conveyor['Conveyor']['mat_altura_caida']=='' ? 0 : $conveyor['Conveyor']['mat_altura_caida'];
        $conveyor['Conveyor']['mat_altura_caida'] .= $relation_convertions['mat_altura_caida'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['mat_altura_caida'];        
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['mat_altura_caida'] = round(eval("return ($convertion);"));
                
        $conveyor['Conveyor']['banda_desarrollo_total'] = $conveyor['Conveyor']['banda_desarrollo_total']=='' ? 0 : $conveyor['Conveyor']['banda_desarrollo_total'];
        $conveyor['Conveyor']['banda_desarrollo_total'] .= $relation_convertions['banda_desarrollo_total'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['banda_desarrollo_total'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['banda_desarrollo_total'] = round(eval("return ($convertion);"));
/*        
        if (error_get_last()){
            $error = error_get_last();
            if($error['type']==4){
        echo 'Show your custom error message';
        var_dump($conveyor['Conveyor']['id'],$convertion);
        var_dump(preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion));
        //Or you can 
        print_r(error_get_last()); die;
            }
}*/

        $conveyor['Conveyor']['rod_espacio_ldc'] = $conveyor['Conveyor']['rod_espacio_ldc']=='' ? 0 : $conveyor['Conveyor']['rod_espacio_ldc'];
        $conveyor['Conveyor']['rod_espacio_ldc'] .= $relation_convertions['rod_espacio_ldc'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['rod_espacio_ldc'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['rod_espacio_ldc'] = round(eval("return ($convertion);"));
        
        $conveyor['Conveyor']['rod_espacio_ldr'] = $conveyor['Conveyor']['rod_espacio_ldr']=='' ? 0 : $conveyor['Conveyor']['rod_espacio_ldr'];
        $conveyor['Conveyor']['rod_espacio_ldr'] .= $relation_convertions['rod_espacio_ldr'][$conveyor_language.'-'.$app_language];
        $convertion = $conveyor['Conveyor']['rod_espacio_ldr'];
        //limpiamos la todo caracter no numerico ni de operacion en la conversion
        $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
        $conveyor['Conveyor']['rod_espacio_ldr'] = round(eval("return ($convertion);"));
        
        /**PARA EL RESUMEN **/
        if(isset($conveyor['Conveyor']['summary_feet'])){
            $conveyor['Conveyor']['summary_feet'] = $conveyor['Conveyor']['summary_feet']=='' ? 0 : $conveyor['Conveyor']['summary_feet'];
            $conveyor['Conveyor']['summary_feet'] .= $relation_convertions['banda_desarrollo_total'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['Conveyor']['summary_feet'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['Conveyor']['summary_feet'] = round(eval("return ($convertion);"));
        }

        //Nuevas conversiones v2.5.0
        if(isset($conveyor['TabConveyor'])){
            $conveyor['TabConveyor']['min_temp'] = $conveyor['TabConveyor']['min_temp']=='' || is_null($conveyor['TabConveyor']['min_temp']) ? '(0' : '('.$conveyor['TabConveyor']['min_temp'];
            $conveyor['TabConveyor']['min_temp'] .= $relation_convertions['temperature'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabConveyor']['min_temp'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabConveyor']['min_temp'] = round(eval("return ($convertion);"));

            $conveyor['TabConveyor']['max_temp'] = $conveyor['TabConveyor']['max_temp']=='' || is_null($conveyor['TabConveyor']['max_temp']) ? '(0' : '('.$conveyor['TabConveyor']['max_temp'];
            $conveyor['TabConveyor']['max_temp'] .= $relation_convertions['temperature'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabConveyor']['max_temp'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabConveyor']['max_temp'] = round(eval("return ($convertion);"));

            $conveyor['TabConveyor']['sea_level'] = $conveyor['TabConveyor']['sea_level']=='' || is_null($conveyor['TabConveyor']['sea_level']) ? 0 : $conveyor['TabConveyor']['sea_level'];
            $conveyor['TabConveyor']['sea_level'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabConveyor']['sea_level'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabConveyor']['sea_level'] = round(eval("return ($convertion);"),2);

            $conveyor['TabConveyor']['length_curve'] = $conveyor['TabConveyor']['length_curve']=='' || is_null($conveyor['TabConveyor']['length_curve']) ? 0 : $conveyor['TabConveyor']['length_curve'];
            $conveyor['TabConveyor']['length_curve'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabConveyor']['length_curve'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabConveyor']['length_curve'] = round(eval("return ($convertion);"),2);
        }

        if(isset($conveyor['TabTransitionZone'])){
            $conveyor['TabTransitionZone']['flat_to_trough'] = $conveyor['TabTransitionZone']['flat_to_trough']=='' || is_null($conveyor['TabTransitionZone']['flat_to_trough']) ? 0 : $conveyor['TabTransitionZone']['flat_to_trough'];
            $conveyor['TabTransitionZone']['flat_to_trough'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabTransitionZone']['flat_to_trough'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabTransitionZone']['flat_to_trough'] = round(eval("return ($convertion);"),2);

            $conveyor['TabTransitionZone']['flat_pulley_lift'] = $conveyor['TabTransitionZone']['flat_pulley_lift']=='' || is_null($conveyor['TabTransitionZone']['flat_pulley_lift']) ? 0 : $conveyor['TabTransitionZone']['flat_pulley_lift'];
            $conveyor['TabTransitionZone']['flat_pulley_lift'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabTransitionZone']['flat_pulley_lift'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabTransitionZone']['flat_pulley_lift'] = round(eval("return ($convertion);"),2);

            $conveyor['TabTransitionZone']['trough_to_flat'] = $conveyor['TabTransitionZone']['trough_to_flat']=='' || is_null($conveyor['TabTransitionZone']['trough_to_flat']) ? 0 : $conveyor['TabTransitionZone']['trough_to_flat'];
            $conveyor['TabTransitionZone']['trough_to_flat'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabTransitionZone']['trough_to_flat'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabTransitionZone']['trough_to_flat'] = round(eval("return ($convertion);"),2);

            $conveyor['TabTransitionZone']['troughflat_pulley_lift'] = $conveyor['TabTransitionZone']['troughflat_pulley_lift']=='' || is_null($conveyor['TabTransitionZone']['troughflat_pulley_lift']) ? 0 : $conveyor['TabTransitionZone']['troughflat_pulley_lift'];
            $conveyor['TabTransitionZone']['troughflat_pulley_lift'] .= $relation_convertions['meters'][$conveyor_language.'-'.$app_language];
            $convertion = $conveyor['TabTransitionZone']['troughflat_pulley_lift'];
            //limpiamos la todo caracter no numerico ni de operacion en la conversion
            $convertion = preg_replace("/[^0-9.\/\*\+\-\)\(]+/","",$convertion);
            $conveyor['TabTransitionZone']['troughflat_pulley_lift'] = round(eval("return ($convertion);"),2);
        }

        
        return $conveyor;
    }
}
