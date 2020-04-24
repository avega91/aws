<?php /*
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

echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['gauge']}]}");
?>
<style>
    .conveyor-gauge-item .info-gauge{
        width: 85% !important;
    }
    .info-gauge {
        display: block !important;
    }
    .select-sidebar + div.chosen-container .chosen-single {
        line-height: 2.5em !important;
        color: #969b96 !important;
        font-family: sans-serif !important;
        font-size: 13px !important;
        text-align: left !important;
        padding-left: 5px;
    }
    .select-sidebar + div.chosen-container .chosen-single > div{ /* arrow **/
        display: none !important;
    }

    .select-sidebar + div.chosen-container{
        height: 35px !important;
    }
    #area_subarea_form{
        width: 480px;
        height: 100px;
    }

    .button-page-menu ul li {
        overflow: inherit;
    }
    .button-page-menu ul li a {
        width: 180px;
    }

    #recommended_belt_info_form{
        width: 300px;
        height: 370px;
    }

    .belt-rec-info{

    }
    .belt-rec-note h2{
        color:#EB7524;
        font-size:14px;
    }

    input[name="info"]{
        font-family: "sanslight" !important;
        margin-top: 5px;
    }

    textarea[name="reason"]{
        min-width: auto !important;
        min-height: 150px !important;
        border: none !important;
        border-bottom: 1px solid #e1e4e1 !important;
        color:#ed760a !important;
        font-size:15px !important;
        margin-top: 5px;
    }
    
</style>
<div class="title-page conveyors-section" title="<?php echo $conveyor['numero']; ?>">
    <?php echo $conveyor['numero']; ?>
</div>
<?php 


$trackingUrl='';
if(!$isUsConveyor){
    if(trim($conveyor['tracking_code'])!=''){
        $secureItemParams = $this->Utilities->encodeParams(trim($conveyor['tracking_code']));
        $trackingUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'trackInfo', $secureItemParams['item_id'], $secureItemParams['digest']));
    }
}

$class_tracking = $trackingUrl=='' ? 'disabled-btn':'';

$secureItemParams = $this->Utilities->encodeParams($conveyor['id']);
$addFileUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addFileConveyor', $secureItemParams['item_id'], $secureItemParams['digest']));

$conveyorSecureParams = $secureItemParams['item_id'].'/'.$secureItemParams['digest'];

$lifeEstimationUrl = '';
if($sePuedeCalcularVidaEstimada){    
    $lifeEstimationUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'lifeEstimation', $secureItemParams['item_id'], $secureItemParams['digest']));
}
$lifeEstimationUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'lifeEstimation', $secureItemParams['item_id'], $secureItemParams['digest']));

$class_life_estimation = !$sePuedeCalcularVidaEstimada ? 'disabled-btn':'';
$class_recommended_conveyor = !$sePuedeCalcularBandaRecomendada ? 'disabled-btn':'';

//$whiteList = ['MX1','MX2','CHILE'];
//$classFilterAreas = in_array($companyRegion['region'], $whiteList) ? '' : 'hidden';
$classFilterAreas = '';

$viewLifetimeAllow = isset($credentials['permissions'][IElement::Is_LifetimeEstimate]) && in_array('view', $credentials['permissions'][IElement::Is_LifetimeEstimate]['allows']) ? true : false;
$viewRecommendedBeltAllow = isset($credentials['permissions'][IElement::Is_RecommendedBelt]) && in_array('view', $credentials['permissions'][IElement::Is_RecommendedBelt]['allows']) ? true : false;

$exportToHistory = $has_failed_date;
$class_history_btn = !$exportToHistory ? 'disabled-btn':'';
$exportHistoryUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'exportToHistory', $secureItemParams['item_id'], $secureItemParams['digest']));

?>
<div class="full-page">
    <div class="data-page">
        <div class="info-section">
            <div class="button-page-menu">
                <ul id="conveyor_menu" data-section="7" data-intro="<?php echo __('tutorial_menu_vista_banda',true);?>" data-position="top">
                    <?php if(!$isUsConveyor): ?>

                    <?php /*
                    <?php if($viewLifetimeAllow): ?>
                    <li><?php echo $this->Html->link(__('Tiempo de vida', true), '#', array('rel' => $lifeEstimationUrl, 'class' => $class_life_estimation.' conveyor-opt-link', 'assoc-callback'=>'initEventsBeltLifeCalc','assoc-layer'=>'life-estimation','dialog-style'=>'life-estimation-dialog')); ?></li>
                    <?php endif; ?>

                    
                    <?php if($viewRecommendedBeltAllow): ?>
                    <li><?php echo $this->Html->link(__('Banda recomendada', true), '#', array('rel' => $lifeEstimationUrl, 'class' => $class_recommended_conveyor.' conveyor-opt-link', 'assoc-callback'=>'initEventsBeltLifeCalc','assoc-layer'=>'recommended-conveyor','dialog-style'=>'life-estimation-dialog')); ?></li>
                    <?php endif; ?> */
                    ?>
                        <!--<li><?php echo $this->Html->link(__('Rastreo de banda', true), '#', array('rel' => $trackingUrl, 'class' => $class_tracking.' conveyor-opt-link', 'assoc-callback'=>'initEventsTracking','dialog-style'=>'trac-dialog')); ?></li>   -->
                    <?php endif; ?>

                    <li>
                        <?php echo $this->Html->link(__('Export data to history', true), '#', array('rel' => $exportHistoryUrl, 'class' => $class_history_btn.' conveyor-opt-link', 'assoc-callback'=>'initEventsExportHistory','dialog-style'=>'')); ?>
                        <span class="left-btn-info tooltiped" title="<?php echo __("disclaimer_export_to_history_btn",true); ?>">?</span>
                    </li>

                    <li>
                        <?php if($credentials['i_group_id']>=IGroup::ADMIN): ?>
                            <span class="simple-btn-action edit disabled" id="edit_recommended_belt_info"></span>
                        <?php endif; ?>
                        <?php echo $this->Html->link(__('Banda recomendada', true), '#', array('id'=>'recommended_belt_details_link','rel' => 'client', 'class' => 'simple-btn')); ?>        
                        <div id="recommended_belt_info_conveyor" class="recommended_belt_info_conveyor" rel="<?php echo $conveyorSecureParams; ?>">
                                <?php
                                    $this->Content->printRecommendedBeltInfoConveyor($recommended_info_assoc);                                    
                                ?>
                        </div>
                    </li>

                    <li>
                        <?php echo $this->Html->link(__('Detalles', true), '#', array('id'=>'conveyor_details_link','rel' => 'client', 'class' => 'simple-btn')); ?>
                        <div>
                            <span><?php echo __('Perfil transportador', true); ?></span>
                            <img src="<?php echo $perfil_transportador['path']; ?>"/>
                            <table>
                                <tr>
                                    <td><?php echo __('Distribuidor',true); ?> </td>
                                    <td><?php echo $dealer['name'];?></td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Cliente', true); ?> </td>
                                    <td><?php echo $company['name'];?></td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Modificado', true); ?> </td>
                                    <td><?php echo $this->Utilities->transformVisualFormatDate($conveyor['actualizada'], true);?></td>
                                </tr>
                                <tr>
                                    <td><?php echo __('Creado', true); ?> </td>
                                    <td><?php echo $this->Utilities->transformVisualFormatDate($conveyor['creada'], true);?></td>
                                </tr>
                            </table>
                        </div>
                    </li> 
                    <li>
                            <?php echo $this->Html->link(__('Registro de actividad', true), '#', array('id'=>'conveyor_activity','rel' => 'client', 'class' => 'simple-btn')); ?>
                            <div id="activity_feed_conveyors" class="activity_feed_conveyors" rel="<?php echo $conveyorSecureParams; ?>">
                                <?php
                                    $this->Content->printLogConveyor($log_rows);                                    
                                ?>
                            </div>
                    </li>
                    <!--<li><?php echo $this->Html->link(__('Agregar archivo', true), '#', array('rel' => $addFileUrl, 'alt' => Item::FILE ,'class' => 'add-mediaitem-conveyor-link add-file-conveyor', 'dialog-style'=>'file-dialog')); ?></li>-->
                </ul>
            </div>
            <div id="filter_areas" class="<?php echo $classFilterAreas; ?>"></div>
            <div data-section="7" data-intro="<?php echo __('tutorial_filter_items_vista_banda',true);?>" data-position="bottom">
                <select multiple="true" id="type_filter_chosen" data-placeholder="<?php echo __('Filtrar por', true); ?>">
                    <option value="folder"><?php echo __('Carpetas', true); ?></option>
                    <option value="image"><?php echo __('Imagenes', true); ?></option>
                    <option value="video"><?php echo __('Videos', true); ?></option>
                    <option value="report"><?php echo __('Reportes', true); ?></option>
                </select>
            </div>
        </div>

        <div id="items_conveyors_wrapper" class="data-section conveyors">
            <?php //$this->Content->printGraphicConveyorItems($conveyor_items); ?>            
        </div>
    </div>
</div>
<div id="append_area_subarea_wrapper" class="hidden">
    <form id="area_subarea_form" action="<?php echo $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addAreaSubarea', $secureItemParams['item_id'], $secureItemParams['digest'])); ?>" class="fancy_form">
        <div class="fancy-content">
            <div class="full-controls">
                <input type="text" placeholder="<?php echo __('Nombre', true); ?>" name="item_area_subarea" id="item_area_subarea" class="validate[required,maxSize[30]] main-input" maxlength="30"/>
            </div>
            <div class="space"></div>
        </div>
        <div class="dialog-buttons">
            <section>
                <button type="button" id="save_area_subarea" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
            </section>
        </div>
    </form>
</div>

<div id="append_recommended_belt_info_wrapper" class="hidden">
    <form id="recommended_belt_info_form" action="<?php echo $this->Html->url(array('controller' => 'Conveyors', 'action' => 'saveRecommendedBeltInfo', $secureItemParams['item_id'], $secureItemParams['digest'])); ?>" class="fancy_form">
        <div class="fancy-content">
            <div class="full-controls">
                <label for="info"><?php echo __('Recommended Belt', true); ?></label>
                <input type="text" name="info" id="belt_information" class="validate[required]"/>
            </div>
            <div class="space"></div>
            <div class="full-controls">
                <label for="reason"><?php echo __('Reason to recommend this belt', true); ?></label>
                <textarea type="text" placeholder="" name="reason" id="reason" class="validate[required]"></textarea>
            </div>
        </div>

        <div class="dialog-buttons">
            <section>
                <button type="button" id="save_conveyor_belt_info" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>
            </section>
        </div>
    </form>
</div>
