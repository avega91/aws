<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 4/11/18
 * Time: 4:46 PM
 */
?>
<div class="title-page monitoring-section">
    <?php echo __d('monitoring','Monitoring system'); ?>
</div>
<div class="full-page">
        <?php $this->Content->printGraphicCompaniesMonitoring($client_companies); ?>
</div>
