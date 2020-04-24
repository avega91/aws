<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 4/11/18
 * Time: 4:46 PM
 */
echo $this->Html->script("https://www.gstatic.com/charts/loader.js");
echo $this->Html->script("https://momentjs.com/downloads/moment.js");
echo $this->Html->script("https://momentjs.com/downloads/moment-timezone-with-data.js");
//echo $this->Html->script("https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart']}]}");
?>
<style>
    .google-visualization-table-td {
     /*text-align: center !important;*/
    }
    .google-visualization-table-td div.priority{
        margin: 0 auto;
    }
    .google-visualization-table-td div.priority.hight{
        background: red; width: 20px; height: 20px; border-radius: 9999px; border: 1px solid red;
    }
    .google-visualization-table-td div.priority.medium{
        background: #FECD2F; width: 20px; height: 20px; border-radius: 9999px; border: 1px solid #FECD2F;
    }
    .google-visualization-table-td div.priority.low{
        background: green; width: 20px; height: 20px; border-radius: 9999px; border: 1px solid green;
    }
    #filter_period input{
        width: 100px !important;
    }
    div.chart-container{
        margin-bottom: 20px;
        width: 420px; height: 400px;
        float: left;
    }
    div.chart-container.last{
        margin-left: 20px;
    }
    #list_events_char{
        background-color: #FFFFFF;
    }
    /*
    #color_sensor_char{
        width: 420px; height: 400px;
        float: left;
        margin-right: 20px;
    }
    #color_total_ppt{
        width: 420px; height: 400px;
        float: left;
    }
    #measure_fill_char{
        width: 420px; height: 400px;
        float: left;
        margin-right: 20px;
    }
    #fill_level_char{
        width: 420px; height: 400px;
        float: left;
    }*/

    #panel_data{
        height: 50px;
        margin-bottom: 40px;
    }

    #panel_data div{
        width: 152px;
        text-align: right;
    }
    #panel_data div.red-monitor span,#panel_data div.red-monitor b{
        color: #FF2D37;
    }
    #panel_data div.green-monitor span,#panel_data div.green-monitor b{
        color: #2DB928;
    }
    #panel_data div{
        min-height: 55px;
    }
    #panel_data div.normal-monitor span{
        font-size: 22px;
    }
    #panel_data div > b{
        font-size: 25px;
    }
</style>
<?php
$secureClientParams = $this->Utilities->encodeParams($empresa['Empresa']['id']);
?>
<div class="title-page monitoring-section">
    <?php echo $this->Html->link(__d('inspections','Monitoring system'), array('controller' => '/', 'action' => 'monitoringsystem'), array('target' => '_self')); ?> /
    <?php echo $this->Html->link($empresa['Empresa']["name"], array('controller' => 'Companies', 'action' => 'view',$secureClientParams['item_id'], $secureClientParams['digest']), array('target' => '_self')); ?>
</div>
<div class="full-page">
    <div class="page-menu accordion">
            <ul id="conti_menu">
                <li>
                    <?php echo $this->Html->link(__d('monitoring','Pay Per Ton'), '#', array('rel'=>'','class' => '')); ?>
                    <ul class="submenu monitoring-options">
                        <li><?php echo $this->Html->link(__d('monitoring','Overview'), '#', array('rel' => 'overview')); ?></li>
                        <li><?php echo $this->Html->link(__d('monitoring','Color sensor'), '#', array('rel' => 'color-sensor')); ?></li>
                        <li><?php echo $this->Html->link(__d('monitoring','Volume sensor'), '#', array('rel' => 'volume-sensor')); ?></li>
                    </ul>
                </li>
                <!--<li><?php echo $this->Html->link(__d('monitoring','Service Level Agreement'), '#', array('class' => 'single')); ?></li>
                <li><?php echo $this->Html->link(__d('monitoring','Leasing Belt Monitoring'), '#', array('class' => 'single')); ?></li>-->
                <li><?php echo $this->Html->link(__d('monitoring','Predictive maintenance'), '#', array('rel'=>'predictive-maintenance','class' => 'single')); ?></li>
            </ul>
    </div>
    <div id="monitoring_info" class="wrapper-content">
        <div id="overview-wrapper" class="monitoring-tab hidden">
            <div id="panel_data">
                <div class="red-monitor">
                    <h1><?php echo __('Total red tons today', true); ?></h1>
                    <span><?php echo "5€/ton"; ?></span> <b>0</b>
                </div>
                <div class="green-monitor">
                    <h1><?php echo __('Total green tons today', true); ?></h1>
                    <span><?php echo "12€/ton"; ?></span> <b>0</b>
                </div>
                <div class="normal-monitor credit-today">
                    <h1><?php echo __('Credit today', true); ?></h1>
                    <span><?php echo "0€"; ?></span>
                </div>
                <div class="normal-monitor total-ppt-today">
                    <h1><?php echo __('Total PPT today', true); ?></h1>
                    <span><?php echo "0€"; ?></span>
                </div>
                <div class="normal-monitor total-ppt-month last">
                    <h1><?php echo __('Total PPT', true).' '.$this->Utilities->getFullMonthName(date('m')); ?></h1>
                    <span><?php echo "0€"; ?></span>
                </div>
            </div>
            <div id="charts-overview">

            </div>
        </div>
        <div id="color-sensor-wrapper" class="monitoring-tab hidden">

        </div>
        <div id="volume-sensor-wrapper" class="monitoring-tab hidden">

        </div>
        <div id="predictive-maintenance-wrapper" class="monitoring-tab hidden">

        </div>
    </div>
</div>
