<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file get_gauge_chart.php
 *     View layer for action Item of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2016
 */
?>
<?php
$gaugeHtml = ['success'=>false,'charts'=>[]];
if(!empty($gaugeCalculationResults)):

    foreach($gaugeCalculationResults AS $conveyor_id => $gaugeCalculationResult):
        if(!empty($gaugeCalculationResult)){
            ob_start();
            $this->Utilities->printAbrasionLife($gaugeCalculationResult);
            $gaugeChartData = ob_get_clean();
            $gaugeHtml['charts'][$conveyor_id] = '<div class="gauge-container">'.$gaugeChartData.'</div>';
        }
    endforeach;
    if(!empty($gaugeHtml['charts'])){
        $gaugeHtml['success'] = true;
    }
    echo json_encode($gaugeHtml);
endif; ?>
