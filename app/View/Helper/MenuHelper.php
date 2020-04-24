<?php

class MenuHelper extends AppHelper {

    public $helpers = array('Html', 'Utilities', 'Content');

    public function programmed_notifications($notifications) {
        $credentials = $this->_View->getVar('credentials');
        $deleteNotificationAllow = isset($credentials['permissions'][IElement::Is_Notification]) && in_array('delete', $credentials['permissions'][IElement::Is_Notification]['allows']) ? true : false;
        ?>        
        <div class="notifications_wrapper">                
            <?php
            $not_viewed_notifications = '';
            foreach ($notifications AS $notification) {
                $notificacion = $notification['Notification'];
                $fecha_creacion = $this->Utilities->transformVisualFormatDate($notificacion['creation_date'], true);
                $fecha_activacion = $this->Utilities->transformVisualFormatDate($notificacion['activation_date'], true);
                $username = utf8_encode($notificacion['user_name']);

                $secureNotificationConveyor = $this->Utilities->encodeParams($notificacion['id']);
                $urlRemoveNotification = $this->_View->Html->url(array('controller' => 'Notifications', 'action' => 'remove', $secureNotificationConveyor['item_id'], $secureNotificationConveyor['digest']));

                $action_list = '<ul class="conti-list-actions">';
                if ($notificacion['viewed'] == 1) {
                    $action_list .= '<li>' . __('Enviada', true) . '</li>';
                } else {
                    $confirmMsg = __('Realmente desea eliminar la notificacion seleccionada?', true);
                    if($deleteNotificationAllow):
                        $action_list .= '<li><a class="delete-action delete-notification-link" title="' . __('Eliminar', true) . '" rel="' . $urlRemoveNotification . '" conf-msg="' . $confirmMsg . '"></a></li>';
                    endif;
                }
                $action_list .= '</ul>';


                $class_notif = $notificacion['viewed'] == 0 ? 'unreaded' : 'readed';
                $notif_row = '<div class="notification-row ' . $class_notif . '">';
                $notif_row .= '<div class="header-row-notif">' . __('Para: %s', $notificacion['company_name']) . '<div class="notification-action-ctrl">' . $fecha_activacion . $action_list . '</div></div>';
                $notif_row .= $notificacion['content'];
                $notif_row .= '<div class="footer-row-notif">' . __('Creada por: %s en %s', array($username, $fecha_creacion)) . '</div>';
                $notif_row .= '</div>';

                echo $notif_row;
            }
            ?>
        </div>
        <?php
    }

    public function automatic_notifications($notifications) {
        $language = $this->_View->getVar('language');
        $usercode = $this->_View->getVar('user_code');
        ?>        
        <div class="notifications_wrapper">                
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
                                    $class_link = 'item-dashboard-link';                                    
                                    $target = '_blank';
                                    break;
                                case Item::FOLDER: case Item::FOLDER_FILE: case Item::FILE:
                                    $urlViewItemConveyor = $this->Html->url(array('controller' => '/', 'action' => '/buoy/data/', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                                    $class_link = 'item-dashboard-link';                                    
                                    $target = '_self';
                                break;  
                                case Item::TRACKING_CONVEYOR:
                                    $this->TrackingConveyor = ClassRegistry::init('TrackingConveyor');
                                    $trackingConveyor = $this->TrackingConveyor->findById($notificacion['id_item']);
                                    if(!empty($trackingConveyor)){
                                        $trackingConveyor = $trackingConveyor['TrackingConveyor'];
                                        $secureTrackingParams = $this->Utilities->encodeParams(trim($trackingConveyor['tracking_code']));
                                        $urlViewItemConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'trackInfo', $secureTrackingParams['item_id'], $secureTrackingParams['digest']));
                                        $class_link = 'generic-conveyor-link';
                                        $extra_attribs = 'assoc-callback="initEventsTracking" dialog-style="trac-dialog"';
                                        $target = '';
                                    }
                                    break;
                                case Item::NEWS:
                                    list($titulo_es, $titulo_en) = explode('||', $dinamicVals[1][0]);
                                    $dinamicVals[1][0] = $this->Content->_lang == IS_ESPANIOL ? $titulo_es : $titulo_en;
                                    $class_link = 'row-news-link';
                                    $urlViewItemConveyor = $notificacion['id_item'];
                                    break;
                                case UsuariosEmpresa::IS_CLIENT:case UsuariosEmpresa::IS_DIST:
                                    $class_link = 'item-dashboard-link';
                                    $urlViewItemConveyor = $this->Html->url(array('controller' => 'Users', 'action' => 'viewCompany', $usercode, $notificacion['type_item'], $notificacion['id_item']));
                                    break;
                                case UsuariosEmpresa::IS_ADMIN:case UsuariosEmpresa::IS_MASTER:
                                break;    
                                default:
                                    $class_link = 'item-dashboard-link';
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
                                $notificacion['content'] = __($string2translate, $dinamicVals[1]).' <a href="#" rel="' . $urlViewItemConveyor . '" class="' . $class_link . '"  target-link="' . $target . '" '.$extra_attribs.'>' . __('Check out',true) . '</a>';
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
        <?php
    }

    public function toolbar() {
        $credentials = $this->_View->getVar('credentials');
        $options_toolbar = $this->_View->getVar('options_toolbar');
        $company_conveyor = $this->_View->getVar('company');
        $urlQrCodeConveyor = $this->_View->getVar('urlQrCodeConveyor');
        $urlreportingHistoryConveyor = $this->_View->getVar('urlreportingHistoryConveyor');
        $role = $this->_View->getVar('role');

        $areas = $this->_View->getVar('areas');
        $subareas = $this->_View->getVar('subareas');

        ?>    

        <ul>            
           <?php /*
            <li class="collapsible-menu" id="generic_add" data-section="1" data-intro="<?php echo __("tutorial_agregar_elementos_btn",true); ?>" data-position="bottom">
                <?php echo $this->Html->link('', '#', array('title' => __('Nuevo elemento', true))); ?>
                <ul>
                    <?php if($credentials['group_id']>Group::CLIENT && $credentials['role_company']!=UsuariosEmpresa::IS_CLIENT){ ?>
                    <li><?php echo $this->Html->link(__('Empresa', true), '#modal', array('class' => 'add_user', 'alt' => 'company-dialog|callOpenUser', 'rel' => $this->Html->url(array('controller' => 'Users', 'action' => 'add')))); ?></li>
                    <?php } ?>
                    <li><?php echo $this->Html->link(__('Transportador', true), '#modal', array('class' => 'add_conveyor', 'alt' => 'conveyor-dialog|callAddConveyor|callUpdateConveyorsDataTable', 'rel' => $this->Html->url(array('controller' => 'Conveyors', 'action' => 'add')))); ?></li>
                    <?php if($credentials['group_id']>Group::ADMIN){ ?>
                    <li><?php echo $this->Html->link(__('Noticia', true), '#', array('class' => 'add_news', 'alt' => 'news-dialog|callOpenNews|callUpdateSite', 'rel' => $this->Html->url(array('controller' => 'News', 'action' => 'add')))); ?></li>
                    <?php } ?>
                    <?php if($credentials['group_id']>Group::CLIENT){ 
                            $dist_id = $credentials['role']==UsuariosEmpresa::IS_DIST ? $credentials['id_empresa']:0;
                     ?>
                        <?php if($credentials['role_company']!=UsuariosEmpresa::IS_CLIENT){ ?>
                        <li><?php echo $this->Html->link(__('Notificación', true), '#modal', array('class' => 'add_notification', 'alt' => 'notification-dialog|callAddNotification|callUpdateSites', 'rel' => $this->Html->url(array('controller' => 'Notifications', 'action' => 'add')),'assoc-d'=>$dist_id)); ?></li>
                         <?php } ?>
                    <?php } ?>
                    <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                </ul>
            </li> */
 ?>
            <?php
            switch ($options_toolbar) {
                //if (in_array('search-users', $options_toolbar)) {
                case 'search-users':
                    ?>                
                    <!--<li class="dropdown_menu" id="generic_sort" data-section="13" data-intro="<?php echo __('tutorial_ordenar_usuarios',true);?>" data-position="bottom">-->
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="name">A&nbsp;-&nbsp;Z</span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="name">A&nbsp;-&nbsp;Z</a></li>
                            <li><a class="text-link" rel="last_update"><?php echo __('Fecha', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search" style="width: 150px !important; min-width: 150px;">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Search company', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>    
                    <?php break; ?>
                <?php case 'search-users-clients':?>                
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="name">A&nbsp;-&nbsp;Z</span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="name">A&nbsp;-&nbsp;Z</a></li>
                            <li><a class="text-link" rel="last_update"><?php echo __('Fecha', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Search customer', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>    
                    <?php if($credentials['role']==UsuariosEmpresa::IS_MANAGER && 1==2){ ?>
                        <li id="generic_filter"><a title="<?php echo __('Filtrar por empresa',true); ?>" class="tool-icon text filter tool-link" rel="<?php echo $this->Html->url(array('controller'=>'Ajax','action'=>'getClientsManager')); ?>" assoc-callbacks="initEventsClientsManager|getSelectedClientsManagerClients" dialog-style=""><?php echo __('Filtrar',true); ?></a></li>
                    <?php } ?>
                    <?php break; ?>                        
                <?php case 'search-conveyors': 
                    $addConveyorAllow = isset($credentials['permissions'][IElement::Is_Conveyor]) && in_array('add', $credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
                    $secureClient = $this->_View->getVar('secureClient');;
                ?>                
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="title"><?php echo __('A - Z', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="title"><?php echo "A - Z"; ?></a></li>
                            <li><a class="text-link" rel="update"><?php echo __('Fecha', true); ?></a></li>
                            <li><a class="text-link" rel="status"><?php echo __('Estatus', true); ?></a></li>
                        </ul>
                    </li>
                    <?php if($addConveyorAllow): ?>
                    <li class="generic-tool-menu">
                        <a href="#modal" id="import_bs_excel" class="generic-link full-upload-btn" alt="metadata-dialog|initEventsImportFileClient|updateClientItemsIfRequired" rel="<?php echo $this->Html->url(array('controller' => 'Companies', 'action' => 'importBsFromXls', $secureClient['item_id'], $secureClient['digest'])); ?>"><span><?php echo __('Upload buoy metadata', true); ?></span></a>           
                    </li>
                    <?php endif; ?>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Search buoy system by name', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li> 
                    <?php if($credentials['role']==UsuariosEmpresa::IS_MANAGER && 1==2  ){ ?>
                        <li id="generic_filter"><a title="<?php echo __('Filtrar por empresa',true); ?>" class="tool-icon text filter tool-link" rel="<?php echo $this->Html->url(array('controller'=>'Ajax','action'=>'getClientsManager')); ?>" assoc-callbacks="initEventsClientsManager|getSelectedClientsManager" dialog-style=""><?php echo __('Filtrar',true); ?></a></li>
                    <?php } ?>
                    <?php break; ?>
                <?php
                case 'company-buoy-system-root':
                    $addConveyorAllow = isset($credentials['permissions'][IElement::Is_Conveyor]) && in_array('add', $credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
                    $urlProfileCompany = $this->_View->getVar('urlProfileCompany');
                    $urlSuspendActivateCompany = $this->_View->getVar('urlSuspendActivateCompany');
                    $textSuspendActivate = $this->_View->getVar('textSuspendActivate');
                    $urlDeleteCompany = $this->_View->getVar('urlDeleteCompany');
                    $deleteReferer = $this->_View->getVar('deleteReferer');
                    $urlreportingListConveyors = $this->_View->getVar('urlreportingListConveyors');
                    $secureClient = $this->_View->getVar('secureClient');
                    
                    //$urlreportingListConveyors = '#';
                    $urlAddColaborator = $this->Html->url(array('controller'=>'users','action'=>'add','user'));
                    $empresaCliente = $this->_View->getVar('empresa');
                    $empresaDistribuidor = $this->_View->getVar('empresa_dealer');
                    $client_id = $empresaCliente["id"];
                    $dealer_id = $empresaDistribuidor["Empresa"]["id"];

                    $failures_mx = $this->_View->getVar('failures_mx');
                    $failures_us = $this->_View->getVar('failures_us');
                    
                    ?>
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="title"><?php echo __('A - Z', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="title"><?php echo "A - Z"; ?></a></li>
                            <li><a class="text-link" rel="update"><?php echo __('Fecha', true); ?></a></li>
                            <li><a class="text-link" rel="status"><?php echo __('Estatus', true); ?></a></li>
                        </ul>
                    </li>
                    <?php if($addConveyorAllow): ?>
                    <li class="generic-tool-menu">
                        <a href="#modal" id="import_bs_excel" class="generic-link full-upload-btn" alt="metadata-dialog|initEventsImportFileClient|updateClientItemsIfRequired" rel="<?php echo $this->Html->url(array('controller' => 'Companies', 'action' => 'importBsFromXls', $secureClient['item_id'], $secureClient['digest'])); ?>"><span><?php echo __('Upload buoy metadata', true); ?></span></a>           
                    </li>
                    <?php endif; ?>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Search buoy system by name', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>    
                    <?php break; ?>
                <?php
                case 'items-conveyors':
                    $urlRemoveItem = $this->_View->getVar('urlRemoveItem');
                    $urlReturnRemove = $this->_View->getVar('urlReturnRemove');
                    $urlEditItem = $this->_View->getVar('urlEditItem');
                    $assocDealerConveyor = $this->_View->getVar('assocDealerConveyor');
                    $assocClientConveyor = $this->_View->getVar('assocClientConveyor');
                    $urlDownloadReportingHistoryConveyor = $this->_View->getVar('urlDownloadReportingHistoryConveyor');
                    $urlFullReportConveyor = $this->_View->getVar('urlFullReportConveyor');
                    $companyConveyorLink = $this->_View->getVar('companyConveyorLink');

                    $viewGeneralReportAllow = isset($credentials['permissions'][IElement::Is_GeneralReportPerConveyor]) && in_array('download', $credentials['permissions'][IElement::Is_GeneralReportPerConveyor]['allows']) ? true : false;
                    $viewQrCodeAllow = isset($credentials['permissions'][IElement::Is_QRCode]) && in_array('download', $credentials['permissions'][IElement::Is_QRCode]['allows']) ? true : false;
                    $viewReporsCustomerAllow = isset($credentials['permissions'][IElement::Is_ReportsPerCustomer]) && in_array('download', $credentials['permissions'][IElement::Is_ReportsPerCustomer]['allows']) ? true : false;
                    ?>                
                    <li class="generic-tool-menu">
                        <a href="#" class="more-actions" data-section="7" data-intro="<?php echo __('tutorial_acciones_vista_banda',true);?>" data-position="left"><?php echo __('More actions',true); ?></a>
                        <ul>
                            <?php if($viewQrCodeAllow): ?>
                            <li><a href="<?php echo $urlQrCodeConveyor; ?>" target="_blank" class="qr-code-option"><?php echo __('Generar codigo QR', true); ?></a></li>
                            <?php endif; ?>

                            <?php if($viewReporsCustomerAllow): ?>
                            <li><a href="<?php echo $urlreportingHistoryConveyor; ?>" target="_blank" class="list-reports"><?php echo __('Ver lista de reportes', true); ?></a></li>
                            <?php endif; ?>

                            <?php if($viewGeneralReportAllow): ?>
                            <li><a href="<?php echo $urlFullReportConveyor; ?>" target="_blank" class="general-report"><?php echo __('Reporte general', true); ?></a></li>
                            <?php endif; ?>

                            <?php if($urlDownloadReportingHistoryConveyor!=''){?>
                            <li><a href="<?php echo $urlDownloadReportingHistoryConveyor; ?>" class="download-option"><?php echo __('Descargar reportes', true); ?></a></li>                        
                            <?php } ?>
                            <?php if($credentials['role_company']!=UsuariosEmpresa::IS_CLIENT &&!in_array($role, array(UsuariosEmpresa::IS_CLIENT))): ?>
                                <li><a href="#" class="edit-option edit-conveyor-link" assoc-callback="callUpdateSite" assoc-d="<?php echo $assocDealerConveyor; ?>" assoc-c="<?php echo $assocClientConveyor; ?>" rel="<?php echo $urlEditItem; ?>"><?php echo __('Editar transportador', true); ?></a></li>
                            <?php endif; ?>
                            <?php if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){?>
                                <li><a href="#" class="delete-option delete-item-link" rel="<?php echo $urlRemoveItem; ?>" referer = "<?php echo $urlReturnRemove; ?>" conf-msg="<?php echo __('Realmente desea eliminar el transportador seleccionado', true); ?>"><?php echo __('Eliminar transportador', true); ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php if($credentials['role_company']!=UsuariosEmpresa::IS_CLIENT &&!in_array($role, array(UsuariosEmpresa::IS_CLIENT))): ?>
                        <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                    <?php endif; ?>
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="actualizada"><?php echo __('Fecha', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="actualizada"><?php echo __('Fecha', true); ?></a></li>
                            <li><a class="text-link" rel="nombre">A&nbsp;-&nbsp;Z</a></li>
                            <li><a class="text-link" rel="tipo"><?php echo __('Tipo', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>               
                    <?php break; ?>                    
                <?php
                case 'items-folder':
                    $canAddFolder = isset($credentials['permissions'][IElement::Is_Folder]) && in_array('add', $credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
                    $urlEditItem = $this->_View->getVar('urlEditItem');
                    $urlRemoveItem = $this->_View->getVar('urlRemoveItem');
                    $urlReturnRemove = $this->_View->getVar('urlReturnRemove');
                    $is_folder = $this->_View->getVar('is_folder');
                    $isBuoySystem = $this->_View->getVar('isBuoySystem');
                    
                    $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                    $secureFolder = $this->_View->getVar('secureFolder');
                    ?>                
                    <!--<li class="generic-tool-menu">
                        <?php if($credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($credentials['role'], [UsuariosEmpresa::IS_CLIENT])) : ?>
                        <a href="#" class="more-actions" data-section="9" data-intro="<?php echo __('tutorial_acciones_carpeta',true);?>" data-position="left"><?php echo __('More actions',true); ?></a>
                        <?php endif; ?>
                        <ul>
                            <li><a href="#" class="edit-option edit-item-link" rel="<?php echo $urlEditItem; ?>"><?php echo __('Editar', true); ?></a></li>
                            <?php if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){?>
                                <li><a href="#" class="delete-option delete-item-link" rel="<?php echo $urlRemoveItem; ?>" referer = "<?php echo $urlReturnRemove; ?>" conf-msg="<?php echo __('Realmente desea eliminar el elemento seleccionado', true); ?>"><?php echo __('Eliminar', true); ?></a></li>
                            <?php } ?>
                        </ul>
                    </li>-->
                    <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                    <?php if($canAddFolder && $isBuoySystem): ?>
                    <li class="generic-tool-menu"> <a href="#modal" id="import_asset_bs_excel" class="generic-link full-upload-btn" alt="metadata-dialog|initEventsImportFileClient|updateClientItemsIfRequired" rel="<?php echo $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'importAssetsFromXls', $secureFolder['item_id'], $secureFolder['digest'])); ?>"><span><?php echo __('Upload assets metadata', true); ?></span></a> </li>
                    <?php endif; ?>
                    <?php if(!is_null($is_folder)){ ?>
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="name">A&nbsp;-&nbsp;Z</span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="name">A&nbsp;-&nbsp;Z</a></li>
                            <li><a class="text-link" rel="updated_at"><?php echo __('Fecha', true); ?></a></li>
                        </ul>
                    </li>
                    <?php } ?>
                    <?php break; ?>
                <?php
                case 'advanced-dashboard':
                    //$urlRemoveItem = $this->_View->getVar('urlRemoveItem');
                    //$urlReturnRemove = $this->_View->getVar('urlReturnRemove');
                    $periodo = $this->_View->getVar('periodo');
                    $fecha_ini = $this->_View->getVar('fecha_ini');
                    $fecha_fin = $this->_View->getVar('fecha_fin');
                    $markets = $this->_View->getVar('markets');
                    ?>

                    <li class="dropdown_menu market-list dropdown-list full-hidden" id="market_list">
                        <a href="#" class="selected-market"><?php echo __('Choose market', true); ?>:
                            <span rel="<?php echo $credentials['assoc_market']; ?>"><?php echo $markets[$credentials['assoc_market']]; ?></span><!-- assoc_master, para saber que mercado predefinir-->
                        </a>
                        <ul>
                            <span> </span>
                            <?php foreach ($markets AS $market_id => $market): ?>
                            <li><a class="text-link market-option" data-mktid="<?php echo $market_id; ?>"><?php echo $market; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li>
                        <div class="period-filter" id="filter_period">
                            <input type="hidden" id="title_btn_picker" value="<?php echo __('security_filter_date'); ?>"/>
                            <input type="hidden" id="last_week_period_hidden" value="<?php echo $periodo; ?>"/>
                            <input type="hidden" id="last_week_period" value="<?php echo $periodo; ?>"/>
                            <input type="hidden" id="input1" readonly="readonly" value="<?php echo $fecha_ini; ?>">
                            <input type="hidden" id="input2" readonly="readonly" value="<?php echo $fecha_fin; ?>">
                            <input type="text" id="datepicker_stats" class="hidden"/>
                            <input type="text" placeholder="Ultima semana" value="" readonly="readonly" id="period_selected" name="period_selected"/>
                            <!--<button type="button"></button>-->
                        </div>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>
                    <?php break; ?>
            <?php case 'smart-view':
                $downloadSmartReportAllow = isset($credentials['permissions'][IElement::Is_SmartReport]) && in_array('download', $credentials['permissions'][IElement::Is_SmartReport]['allows']) ? true : false;
                ?>
                    <li class="generic-tool-text"><a href="#" class="text-link no-link"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                    <?php if($downloadSmartReportAllow): ?>
                    <li class="generic-tool-menu">
                        <a href="#" id="download_datasheet" class="download-btn" target="_blank"><span><?php echo __('Generar PDF', true); ?></span></a>                
                    </li>
                    <?php endif; ?>
                    <?php break; ?>
            <?php case 'datasheet-conveyor':
                $downloadTechnicalDataAllow = isset($credentials['permissions'][IElement::Is_TechnicalData]) && in_array('download', $credentials['permissions'][IElement::Is_TechnicalData]['allows']) ? true : false;
                ?>
                    <?php if($downloadTechnicalDataAllow): ?>
                    <li class="generic-tool-menu">
                        <a href="#" id="download_datasheet" class="download-btn" target="_blank"><span><?php echo __('Generar PDF', true); ?></span></a>                
                    </li>
                    <?php endif; ?>
                    <?php break; ?>
                    <?php
                case 'premium-section':
                    ?>
                    <li class="dropdown_menu" id="generic_sort" data-section="2" data-intro="<?php echo __('tutorial_ordenar_premium',true);?>" data-position="bottom">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="actualizada"><?php echo __('Fecha', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="actualizada"><?php echo __('Fecha', true); ?></a></li>
                            <li><a class="text-link" rel="title"><?php echo __('A - Z', true); ?></a></li>
                            <li><a class="text-link" rel="status"><?php echo __('Estatus', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search" data-section="2" data-intro="<?php echo __('tutorial_buscar_premium',true);?>" data-position="top">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>    
                    <?php break; ?>
                    <?php
                    case 'custom-reports-section':
                    ?>
                    <li class="dropdown_menu" id="generic_sort" data-section="3" data-intro="<?php echo __('tutorial_ordenar_reporte',true);?>" data-position="bottom">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="title"><?php echo __('A - Z', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="title"><?php echo __('A - Z', true); ?></a></li>
                            <li><a class="text-link" rel="actualizada"><?php echo __('Fecha', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search" data-section="3" data-intro="<?php echo __('tutorial_buscar_reporte',true);?>" data-position="top">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>                       
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>    
                    <?php break; ?>

                    <?php
                    case 'savings-section':
                    ?>
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="title"><?php echo __('A - Z', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="title"><?php echo __('A - Z', true); ?></a></li>
                            <li><a class="text-link" rel="updated_at"><?php echo __('Fecha', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>
                    <?php break; ?>
                    
                    <?php 
                    case 'ultrasonic-section':
                        $urlDownloadUltrasonicData = $this->_View->getVar('urlDownloadUltrasonicData');
                        $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                        $downloadUltraReportAllow = isset($credentials['permissions'][IElement::Is_UltrasonicReport]) && in_array('download', $credentials['permissions'][IElement::Is_UltrasonicReport]['allows']) ? true : false;
                    ?>                      
                    <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                         <li class="generic-tool-menu">
                        <?php if($urlDownloadUltrasonicData!='' && $downloadUltraReportAllow) { //in_array($credentials['role'], array(UsuariosEmpresa::IS_ADMIN, UsuariosEmpresa::IS_MASTER))){ ?>
                        <a href="<?php echo $urlDownloadUltrasonicData; ?>" id="download_datasheet" class="full-download-btn" target="_blank"><span><?php echo __('Descargar', true); ?></span></a>
                        <?php } ?>
                        </li>
                    <?php break; ?>

                        <?php
                    case 'items-inpections':
                        $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                    ?>
                        <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>

                    <?php break; ?>

                        <?php
                        case 'inpection-section':
                        $urlDownloadInspectionData = $this->_View->getVar('urlDownloadInspectionData');
                        $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                        ?>
                        <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                        <li class="generic-tool-menu">
                            <a href="<?php echo $urlDownloadInspectionData; ?>" id="download_datasheet" class="full-download-btn" target="_blank"><span><?php echo __('Descargar', true); ?></span></a>
                        </li>
                    <?php break; ?>

                    <?php
                    case 'history-belt-section':
                        $urlDownloadData = $this->_View->getVar('urlDownloadData');
                        $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                        $downloadHistoryAllow = isset($credentials['permissions'][IElement::Is_History]) && in_array('download', $credentials['permissions'][IElement::Is_History]['allows']) ? true : false;
                        ?>
                        <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                        <?php if($downloadHistoryAllow): ?>
                        <li class="generic-tool-menu">
                             <a href="<?php echo $urlDownloadData; ?>" id="download_datasheet" class="create-pdf" target="_blank"><span><?php echo __('v.2.5.1.CreatePDF', true); ?></span></a>
                        </li>
                        <?php endif; ?>
                        <?php break; ?>

                        <?php
                case 'summary-report-section':
                    $urlDownloadData = $this->_View->getVar('urlDownloadData');
                    $companyConveyorLink = $this->_View->getVar('companyConveyorLink');
                    $downloadSummaryAllow = true
                    ?>
                    <li class="generic-tool-text"><a href="<?php echo $companyConveyorLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $company_conveyor['name']; ?></span></a></li>
                    <?php if($downloadSummaryAllow): ?>
                    <li class="generic-tool-menu">
                        <a href="<?php echo $urlDownloadData; ?>" id="download_datasheet" class="create-pdf" target="_blank"><span><?php echo __('v.2.5.1.CreatePDF', true); ?></span></a>
                    </li>
                <?php endif; ?>
                    <?php break; ?>

                    <?php
                case 'monitoring-company-section':
                    $currentDate = $this->_View->getVar('currentDate');
                    ?>
                    <li class="">
                        <div class="period-filter hidden" id="filter_period">
                            <input type="hidden" id="current_date" value="<?php echo $currentDate; ?>"/>
                            <input type="text" id="datepicker_stats" class="hidden"/>
                            <input type="text" value="<?php echo $currentDate; ?>" readonly="readonly" id="date_selected" name="date_selected"/>
                            <!--<button type="button"></button>-->
                        </div>
                    </li>
                    <?php break; ?>


                    <?php
                case 'items-client':
                    $companyLink = $this->_View->getVar('companyLink');
                    $empresa = $this->_View->getVar('empresa');
                    ?>
                    <?php if($credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT))): ?>
                         <li class="generic-tool-text"><a href="<?php echo $companyLink; ?>" class="text-link client-view"><?php echo __('Cliente', true); ?>: <span><?php echo $empresa['name']; ?></span></a></li>
                    <?php endif; ?>
                    <li class="dropdown_menu" id="generic_sort">
                        <a href="#"><?php echo __('Ordenar por', true); ?>
                            <span rel="updated_at"><?php echo __('Fecha', true); ?></span>
                        </a>
                        <ul>
                            <span> </span>
                            <li><a class="text-link" rel="updated_at"><?php echo __('Fecha', true); ?></a></li>
                            <li><a class="text-link" rel="name">A&nbsp;-&nbsp;Z</a></li>
                            <li><a class="text-link" rel="type"><?php echo __('Tipo', true); ?></a></li>
                        </ul>
                    </li>
                    <li class="closable-menu texteable-option" id="generic_search">
                        <input type="text" name="search-txt" placeholder="<?php echo __('Buscar', true); ?>"/>
                        <ul>
                            <li><?php echo $this->Html->link('', '#', array('class' => 'close')); ?></li>
                        </ul>
                    </li>
                    <?php break; ?>


                <?php } ?>
        </ul>    
        <?php
    }

    public function toggler() {
        $credentials = $this->_View->getVar('credentials');
        $viewNewsAllow = isset($credentials['permissions'][IElement::Is_News]) && in_array('view', $credentials['permissions'][IElement::Is_News]['allows']) ? true : false;
        $viewUserAllow = isset($credentials['permissions'][IElement::Is_UsersSection]) && in_array('view', $credentials['permissions'][IElement::Is_UsersSection]['allows']) ? true : false;
        $viewAdvancedAllow = isset($credentials['permissions'][IElement::Is_AdvancedSection]) && in_array('view', $credentials['permissions'][IElement::Is_AdvancedSection]['allows']) ? true : false;
        $viewSavingsAllow = isset($credentials['permissions'][IElement::Is_Saving]) && in_array('view', $credentials['permissions'][IElement::Is_Saving]['allows']) ? true : false;
        ?>
        <?php /*
        <nav id="menu" data-section="1" data-intro="<?php echo __('tutorial_menu_principal',true);?>" data-position="right">
            <?php echo $this->Html->link(__('Home', true), array('controller'=>'Index','action'=>'index'), array('class' => 'fixed_home')); ?>
            <?php
            //if (in_array($credentials['role'], array(UsuariosEmpresa::IS_DIST,UsuariosEmpresa::IS_ADMIN, UsuariosEmpresa::IS_MASTER)) || (in_array($credentials['role'], array(UsuariosEmpresa::IS_MANAGER)) && $credentials['role_company']==UsuariosEmpresa::IS_DIST)) {
            if($viewUserAllow) {
                echo $this->Html->link(__('Usuarios', true), array('controller' => 'Users', 'action' => 'all'), array('class' => 'fixed_users'));
            }
            //if (in_array($credentials['role'], array(UsuariosEmpresa::IS_MASTER))) {
            if($viewAdvancedAllow) {
                echo $this->Html->link(__('Avanzado', true), array('controller' => 'Advanced', 'action' => 'dashboard'), array('class' => 'fixed_adv'));
            }
            ?>
        </nav>
        */ ?>
        <?php
    }

    public function top($just_language = false) {
        $language = $this->_View->getVar('language');
        $credentials = $this->_View->getVar('credentials');
        $unreaded_notifications = $this->_View->getVar('unreaded_notifications');
        $viewNotificationsAllow = isset($credentials['permissions'][IElement::Is_Notification]) && in_array('view', $credentials['permissions'][IElement::Is_Notification]['allows']) ? true : false;
        $viewProfileAllow = isset($credentials['permissions'][IElement::Is_Profile]) && in_array('view', $credentials['permissions'][IElement::Is_Profile]['allows']) ? true : false;
        $viewHelpAllow = isset($credentials['permissions'][IElement::Is_HelpSection]) && in_array('view', $credentials['permissions'][IElement::Is_HelpSection]['allows']) ? true : false;
        $viewTermsAllow = isset($credentials['permissions'][IElement::Is_TermsSection]) && in_array('view', $credentials['permissions'][IElement::Is_TermsSection]['allows']) ? true : false;
        $viewAdvancedAllow = isset($credentials['permissions'][IElement::Is_AdvancedSection]) && in_array('view', $credentials['permissions'][IElement::Is_AdvancedSection]['allows']) ? true : false;
        $viewTutorialAllow = isset($credentials['permissions'][IElement::Is_Tutorial]) && in_array('view', $credentials['permissions'][IElement::Is_Tutorial]['allows']) ? true : false;
        ?>        

        <div id="topmenu">
            <ul>
                <?php if (!$just_language) { ?>
                    <?php if($viewTutorialAllow): ?>
                    <!-- <li class="icon dropdown_menu" id="tutorial_toggle" data-section="1" data-intro="<?php echo __("tutorial_abrir_tutorial",true); ?>" data-position="left">
                            <?php echo $this->Html->link('', '#', array('class' => 'tutorial_opt', 'title' => __('Ver tutorial', true))); ?>
                    </li> -->
                    <?php endif; ?>
                    <?php if($viewAdvancedAllow): ?>
                    <li class="icon dropdown_menu">
                            <?php echo $this->Html->link('', '/settings', array('class' => 'settings_opt', 'title' => __('Admin settings', true))); ?>
                    </li>
                    <?php endif; ?>
                <?php } ?>
                <!--<li id="language_trigger" class="icon dropdown_menu" data-section="1" data-intro="<?php echo __('tutorial_cambiar_idioma',true);?>" data-position="bottom">
        <?php echo $this->Html->link('', '#', array('id' => 'change_language', 'class' => $language . '_flag country_flag')); ?>                                                        
                    <ul>
                        <span>&nbsp;</span>
                        <li><?php echo $this->Html->link(__('Espanol', true), $language != 'es' ? array('controller' => 'Settings', 'action' => 'setLang', 'es') : '#', array('class' => 'es_flag country_flag')); ?></li>
                        <li><?php echo $this->Html->link(__('Ingles', true), $language != 'en' ? array('controller' => 'Settings', 'action' => 'setLang', 'en') : '#', array('class' => 'en_flag country_flag')); ?></li>
                    </ul>
                </li>-->
                    <?php if (!$just_language) { ?>

                     <?php if($viewNotificationsAllow) : ?>
                    <li class="icon" id="notifications_ring">
                        <?php echo $this->Html->link('', '#', array('class' => 'notifications_opt', 'title' => __('Ver notificaciones', true))); ?>                        
                        <div><?php echo $unreaded_notifications; ?></div>
                    </li>
                    <?php endif; ?>
                    <li class="dropdown_menu">
                        <?php
                        echo $this->Html->link(__('Usuario_Account', true), '#', array('id' => 'users', 'class' => 'user_options text-link'));
                        $secureUserParams = $this->Utilities->encodeParams($credentials['id']);
                        $urlGetProfile = $this->Html->url(array('controller' => 'Users', 'action' => 'profile', $secureUserParams['item_id'], $secureUserParams['digest']));
                        ?>                            
                        <ul style="min-width: 150px;">
                            <span>&nbsp;</span>
                            <?php if($viewProfileAllow) : ?>
                                <li><?php echo $this->Html->link(__('Perfil', true), '#modal', array('id' => 'user_profile', 'class' => 'profile-user user-profile-link', 'alt' => 'profile-dialog|callOpenUProfile|callCloseUProfile', 'rel' => $urlGetProfile)); ?></li>
                            <?php endif; ?>

                            <?php if($viewHelpAllow && 1==2) : ?>
                            <li>
                                <a href="https://support.contiplus.net/" class="help" target="_blank"><?php echo __('Ayuda', true); ?></a>
                            </li>
                            <?php endif; ?>

                            <?php if($viewTermsAllow) : ?>
                            <li><?php echo $this->Html->link(__('Terminos', true), '/terms', array('class' => 'terminos', 'target'=>'_blank')); ?></li>
                            <?php endif; ?>

                            <?php if($language=='en'): ?>
                                <li><?php echo $this->Html->link(__('Privacy', true), '/privacy', array('class' => 'policy', 'target'=>'_blank')); ?></li>
                            <?php endif; ?>
                            <li><?php echo $this->Html->link(__('Cerrar Sesión', true), '/logout', array('class' => 'logout')); ?></li>
                        </ul>
                    </li>
        <?php } ?>
            </ul>
        </div>    
        <?php
    }

    public function fastMenu($just_language = false) {
        $credentials = $this->_View->getVar('credentials');
        $name_controller = $this->_View->getVar('name_controller');
        $type_manager = Configure::read('type_manager');
        $viewClientsAllow = isset($credentials['permissions'][IElement::Is_Customer]) && in_array('view', $credentials['permissions'][IElement::Is_Customer]['allows']) ? true : false;
        $viewConveyorsAllow = isset($credentials['permissions'][IElement::Is_Conveyor]) && in_array('view', $credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
        ?>
        <ul id="fast_menu">
            <li class="home_opt <?php if (in_array(strtolower($name_controller), array('index'))) { ?>current_page_item<?php } ?>"><?php echo $this->Html->link("Home",$this->Html->url('/', true)); ?></li>
            <?php 
            /*
            <?php if($viewConveyorsAllow): ?>
            <li class="conveyorts_opt <?php if (in_array(strtolower($name_controller), array('conveyors'))) { ?>current_page_item<?php } ?>"><?php echo $this->Html->link(__('Transportadores', true), array('controller' => 'Conveyors', 'action' => 'dashboard')); ?></li>
            <?php endif; ?>
            <?php //if (($credentials['role'] != UsuariosEmpresa::IS_CLIENT && is_null($type_manager))  || (!is_null($type_manager) && $type_manager!=UsuariosEmpresa::IS_CLIENT)) {
                if($viewClientsAllow) {
            ?>
                <li class="clients_opt <?php if (in_array(strtolower($name_controller), array('users', 'companies'))) { ?>current_page_item<?php } ?>"><?php echo $this->Html->link(__('Clientes', true), array('controller' => 'Users', 'action' => 'clients')); ?></li>
            <?php } ?>
            */
            ?>
            
            <li class="buoys_opt <?php if (!in_array(strtolower($name_controller), array('index','users'))) { ?>current_page_item<?php } ?>"><?php echo $this->Html->link(__('Buoy System', true), array('controller' => '/', 'action' => '/buoys/dashboard/')); ?></li>
            <?php  if($credentials['i_group_id'] > IGroup::CLIENT_MANAGER ): ?>
                <li class="clients_opt <?php if (!in_array(strtolower($name_controller), array('index','buoysystems','companies'))) { ?>current_page_item<?php } ?>"><?php echo $this->Html->link(__('Customers', true), array('controller' => '/', 'action' => '/customers')); ?></li>
            <?php endif; ?>
        </ul>
        <?php
    }

}
