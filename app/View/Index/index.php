
<?php
$viewCalculatorAllow = isset($credentials['permissions'][IElement::Is_Calculator]) && in_array('view', $credentials['permissions'][IElement::Is_Calculator]['allows']) ? true : false;
$viewMinuteManAllow = isset($credentials['permissions'][IElement::Is_MinuteMan]) && in_array('view', $credentials['permissions'][IElement::Is_MinuteMan]['allows']) ? true : false;
$viewContiUniAllow = isset($credentials['permissions'][IElement::Is_ContiUniversity]) && in_array('view', $credentials['permissions'][IElement::Is_ContiUniversity]['allows']) ? true : false;
?>

<?php if(1==1 || $viewContiUniAllow || $viewMinuteManAllow): ?>
<div class="column-home">
    <div class="panel-content middle-height clickeable" data-url="<?php echo $this->Html->url(array('controller' => '/', 'action' => 'buoys/dashboard')); ?>">
        <h1 class="header-card card-buoy-systems">
            <?php echo __('Buoy systems total',true); ?>                    
        </h1>
        <div class="card-info blue">
            <div><?php echo $buoySystems; ?></div>
        </div>
    </div>
    <?php if($credentials['role_company'] !== UsuariosEmpresa::IS_CLIENT): ?>
        <div class="panel-content middle-height clickeable" data-url="<?php echo $this->Html->url(array('controller' => '/', 'action' => 'customers')); ?>">
            <h1 class="header-card card-customers">
                <?php echo __('Customers total',true); ?>                    
            </h1>
            <div class="card-info">
                <div><?php echo $customers; ?></div>
            </div>
        </div>
    <?php else: ?>
        <div class="panel-content middle-height">
            <h1 class="header-card card-login">
                <?php echo __('Last login',true); ?>                    
            </h1>
            <div class="card-info login">
                <div><?php echo $this->Utilities->timestampToCorrectFormat($credentials['last_access_attempt']); ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="column-home">
    <div class="panel-content middle-height">
        <h1 class="header-card card-files">
            <?php echo __('Files total',true); ?>                    
        </h1>
        <div class="card-info">
            <div><?php echo $archives; ?></div>
        </div>
    </div>
    <div class="panel-content middle-height clickeable" data-url="<?php echo !empty($lastBuoyParams) ? $this->Html->url(array('controller' => '/', 'action' => 'buoy/data', $lastBuoyParams['item_id'], $lastBuoyParams['digest'])) : '#'; ?>">
        <h1 class="header-card card-buoys-loaded">
            <?php echo __('Last buoy system uploaded',true); ?>                    
        </h1>
        <div class="card-info">
            <div class="tooltiped" title="<?php echo $lastBuoyName; ?>"><?php echo $lastBuoyName; ?></div>
        </div>
    </div>
</div>
<div class="column-home">
    <div class="panel-content full-height">
        <h1 class="header-card card-buoy-systems">
            <?php echo __('Today\'s Measurements IFS',true); ?>                    
        </h1>
        <div class="card-info blue">
            <div><?php 
            for ($i=0; $i < count($measurements); $i++) {
                echo $i+1 . '=  ' . $measurements[$i] . '<br>';
            }
            ?></div>
        </div>
    </div>
    <div class="panel-content full-height">
        <h1 class="header-card card-messages">
            <?php echo __('History',true); ?>                    
        </h1>
        <div class="full_content">
            <div id="dash-messages">
                <?php
if (!empty($notifications)) {
    foreach ($notifications AS $notification) {
        $notificacion = $notification['Notification'];
        $dinamicVals = null;
        if (!$notificacion['is_programmed']) {
            $returnValue = preg_match_all('|\%(.*)\%|U', $notificacion['content'], $dinamicVals, PREG_PATTERN_ORDER);
            $string2translate = preg_replace('|\%(.*)\%|U', '%s', $notificacion['content'], -1);
            //CakeLog::write('debug', $string2translate);                                        
            //CakeLog::write('debug', print_r($dinamicVals,true)); 
            
            /**Fix to not translated msgs **/
            $dinamicVals[0] = array();
            foreach ($dinamicVals[1] AS $dinVal){
                $dinamicVals[0][] = __($dinVal, true);
            }
            $dinamicVals[1] = $dinamicVals[0];
            
            if ($notificacion['id_item'] > 0) {
                $secureItemConveyor = $this->Utilities->encodeParams($notificacion['id_item']);
                $urlViewItemConveyor = $class_link = $target = '';
                $extra_attribs = '';
                switch ($notificacion['type_item']) {
                    case Item::CONVEYOR:
                        $urlViewItemConveyor = $this->Html->url(array('controller' => '/', 'action' => '/buoy/data/', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                        //$class_link = 'item-dashboard-link';                                    
                        $target = '_blank';
                        break;
                        case Item::FOLDER: case Item::FOLDER_FILE: case Item::FILE:
                        $urlViewItemConveyor = $this->Html->url(array('controller' => '/', 'action' => '/buoy/data/', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                        //$class_link = 'item-dashboard-link';                                    
                        $target = '_self';
                    break;  
                    
                    case UsuariosEmpresa::IS_CLIENT:case UsuariosEmpresa::IS_DIST:
                        
                        break;
                    case UsuariosEmpresa::IS_ADMIN:case UsuariosEmpresa::IS_MASTER:
                    break;    
                    default:
                        //$class_link = 'item-dashboard-link';
                        $class_link = '';
                        $urlViewItemConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'Item', $notificacion['type_item'], $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                        break;
                }
                if($urlViewItemConveyor=='' || 1==1){
                    $notificacion['content'] = __($string2translate, $dinamicVals[1]);
                }else{
                    if($language=='es'){
                        $dinamicVals[1] = json_decode( str_replace( 'months', 'meses', json_encode( $dinamicVals[1] ) ), true );
                        $dinamicVals[1] = array_map(function($str) { return str_replace('years', 'años', $str); }, $dinamicVals[1]);
                    }else{
                        $dinamicVals[1] = json_decode( str_replace( 'meses', 'months', json_encode( $dinamicVals[1] ) ), true );
                        $dinamicVals[1] = array_map(function($str) { return str_replace('años', 'years', $str); }, $dinamicVals[1]);
                    }
                    $notificacion['content'] = __($string2translate, $dinamicVals[1]).' <a href="#" rel="' . $urlViewItemConveyor . '" class="' . $class_link . '"  target-link="' . $target . '" '.$extra_attribs.'>';// . __('Check out', true) . '</a>';
                }
                
            } else {
                $notificacion['content'] = __($string2translate, $dinamicVals[1]);
            }
        }

        $vista_notificacion = $notification['ViewedNotification'];

        $fecha = $this->Utilities->transformVisualFormatDate($notificacion['activation_date'], true);

        $class_notif = is_null($vista_notificacion['id_notification']) ? 'unreaded' : 'readed';
        $notif_row = '<div class="notification-row ' . $class_notif . '">';
        $notif_row .= '<div class="header-row-notif"><span>' . $fecha . '</span></div>';
        $notif_row .= $notificacion['content'];
        $notif_row .= '</div>';
        echo $notif_row;
    }
} else {
    echo '<div class="notification-row unreaded">' . __('No se encontraron notificaciones', true) . '</div>';
}

?>
    
            </div>
        </div>
    </div>
</div> 
<!-- ..... -->
<h1 ><?php echo __('CBG Dashboard Links: ',true); ?></h1>
<br>
<h3><?php echo __('This link will open a new Tab: ',true); ?>
<a href="http://ec2-3-122-54-216.eu-central-1.compute.amazonaws.com:3000/d/kp5SfItWk/cbg_monitor?openVizPicker&orgId=1&kiosk=tv" target="_blank">CBG Dashboard on a new Tab</a></h3>
<br>
<h3><?php echo __('This link will open on this Tab: ',true); ?>
<a href="http://ec2-3-122-54-216.eu-central-1.compute.amazonaws.com:3000/d/kp5SfItWk/cbg_monitor?openVizPicker&orgId=1&kiosk=tv">CBG Dashboard on this Tab</a></h3>
<br>
<h3><?php echo __('CBG Dashboard using iframe: ',true); ?></h3>
<br>
<div class="iframe_wrapper">
    <iframe src="http://ec2-3-122-54-216.eu-central-1.compute.amazonaws.com:3000/d/kp5SfItWk/cbg_monitor?orgId=1&kiosk=tv" width="1450" height="600" frameborder="0"></iframe>    
</div>

<div class="iframe_wrapper">
    <iframe src="http://ec2-3-122-54-216.eu-central-1.compute.amazonaws.com:3000/d/kp5SfItWk/cbg_monitor?orgId=1&refresh=30s&from=1573116073820&to=1573137673820&panelId=2&fullscreen&kiosk" width="1450" height="600" frameborder="0"></iframe>    
</div> 

<!--<br>
<iframe src="https://serviciostest.principal.com.mx/sicap/Vistas/Usuario/Acceso.aspx" width="850" height="600" frameborder="0"></iframe>
<br> -->
<!--<iframe src="http://ec2-18-191-217-81.us-east-2.compute.amazonaws.com:3000/d/5mzSP6oWz/firsttest?orgId=1&from=1571833633141&to=1571855233141" width="850" height="600" frameborder="0"></iframe> -->

<?php endif;