<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ultrasonic_data.php
 *     View layer for action ultrasonicData of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $secureUltrasonicParams = $this->Utilities->encodeParams($ultrasonic['Ultrasonic']['id']);
    $saveUltrasonicDataUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'saveUltrasonicData', $secureUltrasonicParams['item_id'], $secureUltrasonicParams['digest']));
    //$capture_date = !empty($ultrasonicData) ? $this->Utilities->timestampToCorrectFormat($ultrasonicData['UltrasonicReading']['reading_date']) : date('d/m/Y');    
    $capture_date = !empty($ultrasonicData) ? $this->Utilities->timestampToUsDate($ultrasonicData['UltrasonicReading']['reading_date']) : $this->Utilities->timestampToUsDate(date('Y-m-d'));
    $temperature = !empty($ultrasonicData) ? $ultrasonicData['UltrasonicReading']['temperature'] : 0;
    $tons = !empty($ultrasonicData) ? $ultrasonicData['UltrasonicReading']['conveyed_tons'] : 0;
    $shore_durometer = !empty($ultrasonicData) ? $ultrasonicData['UltrasonicReading']['durometer'] : 0;

    $readings =  array();
    if(!empty($ultrasonicData)){
        $readings = $ultrasonicData['UltrasonicReading']['readings'];
        $row_readings = explode('||', $readings);// get rows
        for($i=0;$i<5;$i++){//Solo son 5 rows
            if(isset($row_readings[$i])){
                $row_readings[$i] = explode(',',$row_readings[$i]);
            }else{
                $row_readings[$i] = array('','','','','','','');//creamos un array con valores vacios por la api
            }
        }
        $readings = $row_readings;
    }else{
        for($i=0;$i<5;$i++){//Solo son 5 rows
            $row_readings[$i] = array('','','','','','','');
        }
        $readings = $row_readings;
    }

    $units = $ultrasonic['Ultrasonic']["units"];
    $ultrasonicReadingId = !empty($ultrasonicData) ? $ultrasonicData['UltrasonicReading']['id'] : 0;

    $units_fields = [
            'metric' => [
                    'temperature' => '°C'
            ],
            'imperial' => [
                'temperature' => '°F'
            ]
    ];
    ?>
    <form id="ultrasonic_data_form" action="<?php echo $saveUltrasonicDataUrl; ?>" class="fancy_form">
        <input type="hidden" name="ultrasonic_reading_id" value="<?php echo $ultrasonicReadingId; ?>"/>
        <div class="header_fields">
            <div class="conveyor-ctrls">
                <div class="column-label"><div class="conveyor-label"><?php echo __('Date measured', true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="capture_date" readonly="readonly" value="<?php echo $capture_date; ?>" class='validate[custom[positive_number]]'/>
                </div>
                <div class="column-label"><div class="conveyor-label"><?php echo __('Temperatura Reading', true); ?> (<?php echo $units_fields[$units]['temperature']; ?>)</div></div>
                <div class="column-ctrl last">
                    <input type="text" name="temperature" class='validate[custom[positive_number]]' value="<?php echo $temperature; ?>"/>
                </div>
            </div>
            <div class="conveyor-ctrls">
                <div class="column-label"><div class="conveyor-label"><?php echo __('Tons Conveyed', true); ?></div></div>
                <div class="column-ctrl">
                    <input type="text" name="tons_conveyed" class='validate[custom[positive_number]]' value="<?php echo $tons; ?>"/>
                </div>
                <div class="column-label"><div class="conveyor-label"><?php echo __('Shore A Durometer', true); ?></div></div>
                <div class="column-ctrl last">
                    <input type="text" name="shore_durometer" class='validate[custom[positive_number]]' value="<?php echo $shore_durometer; ?>"/>
                </div>
            </div>
        </div>        
        <div class="grid_fields">
            <h3>
                <?php
                $label_table = $units == 'imperial' ? __('Location (inches) across belt width', true): __('Location (mm) across belt width', true);
                echo $label_table;
                ?>
            </h3>
            <table>
                <thead>
                    <tr>
                        <td></td>
                         <?php
                         //$ultrasonic_widths = $units == 'imperial' ? $ultrasonic_widths:$ultrasonic_widths_metric;
                         //$ultrasonic_widths = isset($ultrasonic_widths[$ultrasonic['Ultrasonic']['ultrasonic_width']]) ? $ultrasonic_widths[$ultrasonic['Ultrasonic']['ultrasonic_width']]:$ultrasonic['Ultrasonic']['other_width'];
                         $ultrasonic_widths = $ultrasonic['Ultrasonic']['other_width'];
                         $widths = explode(',',$ultrasonic_widths);
                         foreach ($widths AS $width){
                             $unit_inidicator = $units == 'imperial' ? '"':'';
                             echo '<td>'.$width.$unit_inidicator.'</td>';
                         }
                        ?>                        
                    </tr>                    
                </thead>
                <tbody>
                    <?php 
                    for($i=0;$i<5;$i++){
                        $row = '<tr>';
                        $row .= '<td>'.__('Reading %s',array($i+1)).'</td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][0].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][1].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][2].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][3].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][4].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][5].'"/></td>';
                        $row .= '<td><input type="text" class="validate[custom[positive_number]]" value="'.$readings[$i][6].'"/></td>';
                        $row .= '</tr>';
                        
                        echo $row;
                    }
                    ?>                    
                    <tr>
                        <td class="empty"></td>
                        <td colspan="7" class="empty separator-row"></td>
                    </tr>
                    <tr class="result-rows avg">
                        <td><?php echo __('AVERAGE', true); ?></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                    </tr>
                    <tr class="result-rows temp-avg">
                        <td><?php echo __('Temp Avg', true); ?></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                        <td><input type="text" value="0.00" class='validate[custom[positive_number]]' readonly="readonly"/></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="dialog-buttons">  
            <section>
                <button type="button" id="save_ultrasonic" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar y Cerrar', true); ?></button>            
            </section>
        </div>
    </form>
    <?php
} else {
    echo json_encode($response);
}


