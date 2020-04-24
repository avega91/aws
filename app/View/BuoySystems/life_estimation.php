<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file life_estimation.php
 *     View layer for action lifeEstimation of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
if ($response['success']) {
    $secureConveyorParams = $this->Utilities->encodeParams($conveyor['Conveyor']['id']);
    $quoteRequestUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'quoteRequest', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
    $fechaInstalacion = $conveyor['Conveyor']['banda_fecha_instalacion'] == '0000-00-00' ? '': __('desde %s', array($this->Utilities->transformVisualShortFormatDate($conveyor['Conveyor']['banda_fecha_instalacion'])));
    $disclaimer_life_estimation = __('El calculo de tension y espesor de banda esta basado en las especificaciones contenidas en la norma DIN 22101. No constituye ninguna garantia de calidad, no respondemos por danos y perjuicios que pudieran producirse del resultado de este calculo, sea cual fuera su motivo o causa jurídica.', true);
    $years = $language == 'es' ? '+15' : '15+';
    $estimation_months = (Int)$estimation_months > 15 ? $years : $estimation_months;
    ?>
    <div class="calcs-conveyors">
        <div id="life_estimation_wrapper">
            <div>
                <h1><?php echo __('Vida estimada en meses', true); ?></h1>
                <div><?php echo __('%s meses', array($estimation_months)); ?> <?php echo $fechaInstalacion; ?></div>
                <h1><?php echo __('Tonelaje esperado en MMt (MM: Million)', true); ?></h1>
                <div><?php echo __('%s toneladas', array($estimation_tons)); ?></div>
                <h1><?php echo __('Fecha aproximada de cambio', true); ?></h1>
                <div class="red-color">
                    <?php
                    if (!is_null($change_date_estimation)) {
                        echo $this->Utilities->transformVisualShortFormatDate($change_date_estimation);
                    } else {
                        echo '----';
                    }
                    ?>
                </div>
                <div class="space"></div>
                <div class="space"></div>
                <?php if($sePuedeCalcularBandaRecomendada): ?>
                    <button type="button" class="contiButton" id="belt_recommended"><?php echo __('Banda recomendada', true); ?></button>
                <?php endif; ?>
                <div class="space"></div>
                <div class="disclaimer-life-estimation">*
                    <?php echo $disclaimer_life_estimation; ?>
                </div>
            </div>
        </div>
        <div id="recommended_conveyor_wrapper">
            <div>
                <?php $not_valid_recommended = '<span class="not-valid-recommended" title="'.__('No se cuenta con informacion suficiente para realizar el calculo',true).'">----</span>'; ?>
                <h1><?php echo __('Banda recomendada en PIW', true); ?></h1><span title="<?php echo $disclaimer_min_width; ?>"></span>
                <div class="red-color"><?php echo is_null($banda_recomendada_piw) ? $not_valid_recommended:$banda_recomendada_piw; ?></div>
                <h1><?php echo __('Banda recomendada en N/mm', true); ?></h1>
                <div class="red-color"><?php echo is_null($banda_recomendada_mm) ? $not_valid_recommended:$banda_recomendada_mm; ?></div>
                <div class="space"></div>
                <?php 
                    $disclaimer_width_belt = '<p>*'.$disclaimer_min_width.'</p><p>*'.$disclaimer_max_width.'</p>';
                ?>
                <a class="advice-life-estimation tooltip-ctrl" title="<?php echo $disclaimer_width_belt; ?>"><?php echo __('Aviso'); ?></a>
                <div class="space"></div>
                <section class="fancy-buttons">
                    <?php $disable_quote = is_null($banda_recomendada_piw) ? 'disabled="disabled"':''; ?>
                    <button type="button" id="quote_request" rel="<?php echo $quoteRequestUrl; ?>" class="progress-button" data-style="shrink" data-horizontal <?php echo $disable_quote; ?>><?php echo __('Solicitar cotizacion', true); ?></button>            
                </section>
                <div class="space"></div>
                <?php if($sePuedeCalcularVidaEstimada): ?>
                    <button type="button" class="contiButton" id="life_estimation"><?php echo __('Vida estimada', true); ?></button>
                <?php endif; ?>

                <div class="disclaimer-life-estimation">                
                    *<?php echo $disclaimer_life_estimation; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    echo json_encode($response);
}


