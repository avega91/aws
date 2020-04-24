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

?>
<div class="title-page conveyors-section" title="<?php echo __d('inspections','Inspections'); ?>">
    <?php echo __d('inspections','Inspections'); ?>
</div>
<?php 
/*

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

$whiteList = ['MX1','MX2','CHILE'];
$classFilterAreas = in_array($companyRegion['region'], $whiteList) ? '' : 'hidden';

$viewLifetimeAllow = isset($credentials['permissions'][IElement::Is_LifetimeEstimate]) && in_array('view', $credentials['permissions'][IElement::Is_LifetimeEstimate]['allows']) ? true : false;
$viewRecommendedBeltAllow = isset($credentials['permissions'][IElement::Is_RecommendedBelt]) && in_array('view', $credentials['permissions'][IElement::Is_RecommendedBelt]['allows']) ? true : false;
*/
?>
<div class="full-page">
    <?php
        $inspections = [
                ['Inspection'=>['id'=>1,'conveyor'=>$conveyor['Conveyor']]],
                ['Inspection'=>['id'=>2,'conveyor'=>$conveyor['Conveyor']]],
                ['Inspection'=>['id'=>3,'conveyor'=>$conveyor['Conveyor']]]
        ];
        $this->Content->printGraphicInspections($inspections);
    ?>
</div>

