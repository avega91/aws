<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file view.php
 *     View layer for action view of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

$secureItemParams = $this->Utilities->encodeParams($ultrasonic['id']);
$ultrasonicDataUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'ultrasonicData', $secureItemParams['item_id'], $secureItemParams['digest']));
$conveyor_update_ultra = $ultrasonic['install_update_ultra']=='0000-00-00 00:00:00' ? $conveyor['banda_fecha_instalacion'] : $ultrasonic['install_update_ultra'];

$savedInstallUltra = $this->Utilities->timestampToCorrectFormatLanguage($conveyor_update_ultra);

//$date_installed = $this->Utilities->timestampToCorrectFormat($conveyor_update_ultra);
$date_installed = $this->Utilities->timestampToUsDate($conveyor_update_ultra);
$banda_marca = $ultrasonic['conveyor_brand_ultra']=='' ? '' : $ultrasonic['conveyor_brand_ultra'];//Se quita marca de banda $conveyor['banda_marca']->Por Virginie 16/03/18
$ultrasonic_activate = floatval($ultrasonic['original_top_cover'])<=0 || floatval($ultrasonic['durometer_new_belt'])<= 0 ? false : true;
$disabled_add_readings = !$ultrasonic_activate ? 'disabled-btn':'';
echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart','gauge']}]}");

$isNewUltrasonic = $ultrasonic['conveyor_brand_ultra']=='' ? true : false;
$addUltraDataAllow = isset($credentials['permissions'][IElement::Is_UltrasonicData]) && in_array('add', $credentials['permissions'][IElement::Is_UltrasonicData]['allows']) ? true : false;
$editUltraDataAllow = isset($credentials['permissions'][IElement::Is_UltrasonicData]) && in_array('edit', $credentials['permissions'][IElement::Is_UltrasonicData]['allows']) ? true : false;
$deleteUltraDataAllow = isset($credentials['permissions'][IElement::Is_UltrasonicData]) && in_array('delete', $credentials['permissions'][IElement::Is_UltrasonicData]['allows']) ? true : false;

$is_disabled_field = ($isNewUltrasonic && !$addUltraDataAllow) || (!$isNewUltrasonic && !$editUltraDataAllow) ? "disabled='disabled'" : "";
?>
<script type="text/javascript">
    var ultrasonic_widths_imperial = <?php echo json_encode($ultrasonic_widths); ?>;
    var ultrasonic_widths_metric = <?php echo json_encode($ultrasonic_widths_metric); ?>;
    var units_ultrasonic = "<?php echo addslashes($ultrasonic["units"]); ?>";
    var meta_units_ultra = "<?php echo addslashes($ultrasonic["meta_units"]); ?>";
</script>

<input type="hidden" id="conveyor_assoc" value="<?php echo $conveyor['id']; ?>"/>
<div class="title-page conveyors-section" title="<?php echo $conveyor['numero']; ?>">
    <?php echo $conveyor['numero']; ?>
</div>
<div class="full-page">
    <div class="data-page">
        <div class="info-section ultrasonic-section">
            <div class="button-page-menu">
                <ul id="conveyor_menu" data-section="12" data-intro="<?php echo __('tutorial_especificaciones_ultrasonic',true);?>" data-position="top">

                    <?php if(!empty($abrasionLifeData)): ?>
                    <li>
                        <a class="simple-btn clickable-btn toggle-btn-link"><?php echo __("Abrasion life", true); ?></a>
                        <div class="gauge-container">
                            <?php echo $this->Utilities->printAbrasionLife($abrasionLifeData); ?>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li>
                        <?php echo $this->Html->link(__('Specifications', true), '#', array('id' => 'conveyor_details_link', 'rel' => 'client', 'class' => 'simple-btn')); ?>
                        <div <?php if(!empty($abrasionLifeData)): ?> style="display:none" <?php endif; ?>>
                            <table>
                                <tr>
                                    <td>
                                        <?php echo __('Units', true); ?>
                                    </td>
                                    <td>
                                        <?php
                                            $disabled_units = $isNewUltrasonic ? 'enabled':'disabled';
                                            echo $this->Form->input('units_ultrasonic', array('name'=>'units_ultrasonic',$disabled_units,'type' => 'select','label' => false,'options' => $units_conveyor, 'default'=>$ultrasonic["units"],'data-placeholder'=>__('Seleccione',true)));
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Belt Specification', true); ?></td>
                                    <td><input id="conveyor_brand_ultra" type="text" <?php echo $is_disabled_field; ?> value="<?php echo $banda_marca; ?>"/></td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Date Installed', true); ?></td>
                                    <td>
                                        <input type="hidden" value="<?php echo $savedInstallUltra; ?>" id="saved_date_ultra"/>
                                        <input id="install_update_ultra" readonly type="text" <?php echo $is_disabled_field; ?> value="<?php echo $date_installed; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <?php echo __('Compound name', true); ?><span class="required_field">*</span>
                                    </td>
                                    <td>
                                        <select id="compound_name" name="compound_name" class="validate[required]" <?php echo $is_disabled_field; ?>>
                                        <?php 
                                            foreach ($ultrasonic_compounds AS $compound_id => $compound){ 
                                                $selected = $ultrasonic['compound_name']==$compound_id ? 'selected':'';
                                                echo '<option value="'.$compound_id.'" '.$selected.'>'.$compound[3].'</option>';
                                            }
                                        ?>
                                        </select>                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="label-field"><?php echo __('Original Top Cover Thickness', true); ?></span><span class="required_field">*</span></td>
                                    <td><input id="original_top_cover" name="original_top_cover" <?php echo $is_disabled_field; ?> type="text" value="<?php echo $ultrasonic['original_top_cover']; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="label-field"><?php echo __('Ultrasonic width', true); ?></span><span class="required_field">*</span>
                                    </td>
                                    <td>
                                        <select id="ultrasonic_width" name="ultrasonic_width" class="validate[required]" <?php echo $is_disabled_field; ?>>
                                        <?php
                                            $ultrasonic_widths = $ultrasonic["units"]=='imperial' ? $ultrasonic_widths:$ultrasonic_widths_metric;
                                            foreach ($ultrasonic_widths AS $width_id => $ultrasonic_width){ 
                                                $selected = $ultrasonic['ultrasonic_width']==(int)$ultrasonic_width ? 'selected':'';
                                                //echo '<option value="'.$width_id.'" '.$selected.'>'.$width_id.'</option>';
                                                echo '<option value="'.$ultrasonic_width.'" '.$selected.'>'.__($ultrasonic_width, true).'</option>';
                                            }
                                        ?>
                                        </select>
                                        <div class="closable-input-ctrl">
                                            <?php
                                            $is_other_option = array_search($ultrasonic['ultrasonic_width'], $ultrasonic_widths)===false ? true:false;
                                            ?>
                                            <input type="text" id="other_width" name="other_width" value="<?php echo $is_other_option ? $ultrasonic['ultrasonic_width']:''; ?>" class='hidden' class='validate[custom[positive_number]]'/>
                                            <span id="close_other_width" class="close-stick hidden"></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Price of belt installed', true); ?> </td>
                                    <td><input id="conveyor_price" type="text" <?php echo $is_disabled_field; ?> value="<?php echo $ultrasonic['conveyor_price']; ?>"/></td>
                                </tr>  
                                <tr>
                                    <td><?php echo __('Durometer New Belt', true); ?><span class="required_field">*</span></td>
                                    <td><input id="durometer_new_belt" <?php echo $is_disabled_field; ?> type="text" value="<?php echo $ultrasonic['durometer_new_belt']; ?>"/></td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Position Ultrasonic', true); ?></td>
                                    <td><input id="position_ultra" type="text" value="<?php echo $ultrasonic['position']; ?>" maxlength="200"/></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?php


                                            //if(!in_array($credentials['role'], [UsuariosEmpresa::IS_CLIENT])) :
                                            if(($isNewUltrasonic && $addUltraDataAllow) || (!$isNewUltrasonic && $editUltraDataAllow)) :
                                          ?>
                                            <section data-section="12" data-intro="<?php echo __('tutorial_guardar_ultrasonic',true);?>" data-position="right">
                                                <button type="button" id="save_ultrasonic_conveyor_data" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
                                                <!--<button type="button" id="save_ultrasonic_conveyor_data2" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>-->
                                            </section>
                                        <?php else: ?>
                                        &nbsp;
                                        <?php endif; ?>
                                    </td>
                                </tr>  
                            </table>
                        </div>
                    </li>       
                    <?php
                    if (!empty($ultrasonic_data)) {
                        foreach ($ultrasonic_data AS $ultrasonic_reading) {
                            $ultrasonic_reading = $ultrasonic_reading['UltrasonicReading'];
                            //$date_reading = $this->Utilities->timestampToCorrectFormat($ultrasonic_reading['reading_date']);
                            $date_reading = $this->Utilities->timestampToUsDate($ultrasonic_reading['reading_date']);     
                            $secureUltrasonicParams = $this->Utilities->encodeParams($ultrasonic_reading['id']);
                            $ultrasonicDataUpdateUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'ultrasonicData', $secureItemParams['item_id'], $secureItemParams['digest'], $secureUltrasonicParams['item_id'], $secureUltrasonicParams['digest']));
                            $ultrasonicReadingRemoveUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'removeReading', $secureUltrasonicParams['item_id'], $secureUltrasonicParams['digest']));

                            $ultrasonicDataUpdateUrl = !$editUltraDataAllow ? "" : $ultrasonicDataUpdateUrl;
                            $class_link = !$editUltraDataAllow ? "disabled-btn" : "conveyor-opt-link";
                            $link_ultrasonic = $this->Html->link(__('Readings %s', array($date_reading)), '#', array('rel' => $ultrasonicDataUpdateUrl, 'class' => $class_link.' simple-btn', 'assoc-callback' => 'initEventsUltrasonicData', 'dialog-style' => 'ultrasonic-dialog'));

                            $deleteLink = !$deleteUltraDataAllow ? "":"<span class='delete delete-reading-link' data-remove='$ultrasonicReadingRemoveUrl'></span>";
                            echo "<li>$link_ultrasonic $deleteLink</li>";
                        }
                    }
                    ?>
                    <?php
                    //if(!in_array($credentials['role'], [UsuariosEmpresa::IS_CLIENT])) :
                    if(($isNewUltrasonic && $addUltraDataAllow) || (!$isNewUltrasonic && $editUltraDataAllow)) :
                        ?>
                    <li>
                        <?php echo $this->Html->link(__('Add readings', true), '#', array('data-section'=>12,'data-intro'=>__('tutorial_lecturas_ultrasonic',true),'data-position'=>'right','rel' => $ultrasonicDataUrl, 'class' => 'conveyor-opt-link '.$disabled_add_readings, 'assoc-callback' => 'initEventsUltrasonicData', 'dialog-style' => 'ultrasonic-dialog')); ?>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div id="items_conveyors_wrapper" class="data-section ultrasonics">
            <?php if(!$ultrasonic_activate){ ?>
                <div class="alert-box notice"><?php echo __('require_fields_ultrasonic',true); ?></div>
            <?php } ?>
            <div id="chart_div" style="width: 850px; height: 500px;" data-section="12" data-intro="<?php echo __('tutorial_grafica_ultrasonic',true);?>" data-position="top"></div>
            <div id="ultrasonic_resume">
                <h4><?php echo __('Temperature Adjusted Readings',true); ?></h4>
                <table class="readings_summary">
                    <tr class="header-table">
                        <?php
                        $label_table = $ultrasonic['units'] == 'imperial' ? __('Location (inches) across belt width', true): __('Location (mm) across belt width', true);
                        ?>
                        <td colspan="2" rowspan="3"><?php echo __('Dates installed & Measured', true); ?></td>
                        <td colspan="8" class="no-border"><?php echo $label_table; ?></td>
                    </tr>
                    <tr class="no-border no-padding center-aligned">
                        <td colspan="7"></td>
                        <td><?php echo __('Shore-A', true); ?></td>
                    </tr>
                    <tr class="no-border no-padding center-aligned">
                        <?php
                        $ultrasonic_widths = isset($ultrasonic_widths[$ultrasonic['ultrasonic_width']]) ? $ultrasonic_widths[$ultrasonic['ultrasonic_width']]:$ultrasonic['other_width'];
                        $widths = explode(',',$ultrasonic_widths);
                        foreach ($widths AS $width){
                            echo '<td>'.$width.'</td>';
                        }
                        ?>                        
                        <td><?php echo __('Durometer', true); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo __('Install Date', true); ?></td>
                        <td><?php echo $date_installed; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo (double) $ultrasonic['original_top_cover']; ?></td>
                        <td><?php echo $ultrasonic['durometer_new_belt']; ?></td>
                    </tr>
                    <?php
                    $units_ultrasonic = $ultrasonic["units"];
                    if (!empty($ultrasonic_data)) {
                        foreach ($ultrasonic_data AS $index => $ultrasonic_reading) {
                            $ultrasonic_reading = $ultrasonic_reading['UltrasonicReading'];
                            //$reading_title = __('%s date read', array($this->Utilities->addOrdinalNumberSuffix($index + 1)));
                            $reading_title = __('%s date read', array($index + 1));
                            //$date_reading = $this->Utilities->timestampToCorrectFormat($ultrasonic_reading['reading_date']);
                            $date_reading = $this->Utilities->timestampToUsDate($ultrasonic_reading['reading_date']);
                            $durometer = $ultrasonic_reading['durometer'];
                            $readings = $ultrasonic_reading['avgs'];
                            $readings = explode('||', $readings); // get rows                            
                            $readings = explode(',', $readings[1]);
                            //Fix table
                            if($units_ultrasonic=='metric'){
                                $readings = array_map("convertToMM",$readings);//readings se calculo en inches, regresar a mm
                            }


                            $row = '<tr>';
                            $row .= '<td>' . $reading_title . '</td>';
                            $row .= '<td>' . $date_reading . '</td>';
                            $row .= '<td>' . round($readings[0],3) . '</td>';
                            $row .= '<td>' . round($readings[1],3) . '</td>';
                            $row .= '<td>' . round($readings[2],3) . '</td>';
                            $row .= '<td>' . round($readings[3],3) . '</td>';
                            $row .= '<td>' . round($readings[4],3) . '</td>';
                            $row .= '<td>' . round($readings[5],3) . '</td>';
                            $row .= '<td>' . round($readings[6],3) . '</td>';
                            $row .= '<td>' . $durometer . '</td>';
                            $row .= '</tr>';
                            echo $row;
                        }
                    }
                    ?>               
                </table>
                <h4><?php echo __('Wear Rate Statistics & Projections',true); ?></h4>
                <table class="statistics_projections">
                    <tr class="header-table">
                        <td><?php echo __('Tons Conveyed',true); ?></td>
                        <td><?php echo __('#months',true); ?></td>
                        <td><?php echo __('Wear rate',true); ?></td>
                        <td><?php echo __('Projected Future Tons',true); ?></td>
                        <td><?php echo __('Projected Future Life',true); ?></td>
                        <td><?php echo __('Estimated Total Life',true); ?></td>
                        <td><?php echo __('Estimated Total Life',true); ?></td>
                        <td><?php echo __('Est. Total Cost per Ton',true); ?></td>
                        <td><?php echo __('Est. Total Cost per Month',true); ?></td>                        
                    </tr>
                    <tr class="header-table">
                        <td><?php echo __('(tons)',true); ?></td>
                        <td><?php echo __('(months)',true); ?></td>
                        <td><?php echo __('tons/0.001*',true); ?></td>
                        <td><?php echo __('(tons)',true); ?></td>
                        <td><?php echo __('(months)',true); ?></td>
                        <td><?php echo __('(tons)',true); ?></td>
                        <td><?php echo __('(months)',true); ?></td>
                        <td><?php echo __('($/ton)',true); ?></td>
                        <td><?php echo __('($/month)',true); ?></td>                        
                    </tr>
                     <?php
                    if (!empty($ultrasonic_data)) {
                        foreach ($ultrasonic_data AS $index => $ultrasonic_reading) {                            
                            $ultrasonic_reading = $ultrasonic_reading['UltrasonicReading'];                            
                            $conveyed_tons = $ultrasonic_reading['conveyed_tons'];
                            $install_update_ultra = $conveyor_update_ultra;

                            $months_reading = $this->Utilities->diffInMonths($install_update_ultra,$ultrasonic_reading['reading_date']);
                            $avgs = explode('||',$ultrasonic_reading['avgs']);
                            $avgs = explode(',',$avgs[1]);

                            $ultrasonic['original_top_cover'] = $ultrasonic["units"]=='metric' ? $ultrasonic['original_top_cover']*0.0393701 : $ultrasonic['original_top_cover'];
                            $wear_rate = $conveyed_tons / ($ultrasonic['original_top_cover'] - (min($avgs))) / 1000;
                            $future_tons = $wear_rate * min($avgs) * 1000;
                            $future_life = $conveyed_tons<=0 ? 0 : $months_reading*$future_tons/$conveyed_tons;
                            $estimated_total_life_tons = $ultrasonic['original_top_cover']*$wear_rate*1000;
                            $estimated_total_life_months = $months_reading+$future_life;
                            $total_cost_per_ton = $estimated_total_life_tons<= 0 ? 0 : $ultrasonic['conveyor_price']/$estimated_total_life_tons;
                            
                            $total_cost_per_month = $estimated_total_life_months<= 0 ? 0 : $ultrasonic['conveyor_price']/$estimated_total_life_months;
                            
                            $row = '<tr>';
                            $row .= '<td>' . number_format($conveyed_tons,0,'',',') . '</td>';
                            $row .= '<td>' . $months_reading . '</td>';
                            $row .= '<td>' . number_format($wear_rate,0,'',',') . '</td>';
                            $row .= '<td>' . number_format($future_tons,0,'',',') . '</td>';
                            $row .= '<td>' . number_format($future_life,0,'',',') . '</td>';
                            $row .= '<td>' . number_format($estimated_total_life_tons,0,'',',') . '</td>';
                            $row .= '<td>' . number_format($estimated_total_life_months,0,'',',') . '</td>';
                            $row .= '<td>$' . number_format($total_cost_per_ton,4,'.',',') . '</td>';
                            $row .= '<td>$' . number_format($total_cost_per_month,2,'.',',') . '</td>';
                            $row .= '</tr>';
                            echo $row;
                        }
                    }
                    ?>     
                </table>
            </div>
        </div>
    </div>
</div>
<?php
function convertToMM($readingAvgIn){
    return($readingAvgIn*25.4);
}
