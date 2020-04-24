<?php

/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$cakeDescription = __d('cake_dev', 'Contiplus IFS');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
    <head>
	<?php echo $this->Html->charset(); ?>
        <title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
        </title>        
	<?php
                echo '<meta name="copyright" content="' . date('Y') . ' ContiTech AG, ALL RIGHTS RESERVED"/>';
                echo '<meta name="author" content="' . date('Y') . ' Humann Tech"/>';
                echo '<link href="'.$webroot.'img/favicon.ico" type="image/x-icon" rel="icon" />';
                echo $this->fetch('meta');
                
                
                 //fetch opensource stylesheets
                echo $this->Core->css(array('plugins/Assets/css/jquery-ui/jquery-ui'));
                echo $this->Core->css(array('plugins/Assets/css/clockPicker/jquery-clockpicker.min'));
                echo $this->Core->css(array('plugins/Assets/css/progressButton/component'));
                echo $this->Core->css(array('plugins/Assets/css/chosen/chosen','plugins/Assets/css/chosen/ImageSelect','plugins/Assets/css/notifIt/notifIt'));                
                echo $this->Core->css(array('plugins/Assets/css/tipsy/tipsy','plugins/Assets/css/tipsy/tipsy-docs'));
                echo $this->Core->css(array('plugins/Assets/css/perfectScrollbar/perfect-scrollbar'));
                echo $this->Core->css(array('plugins/Assets/css/EngineValidation/validationEngine'));                
                echo $this->Core->css(array('plugins/Assets/css/labelPattern/style'));
                echo $this->Core->css(array('plugins/Assets/css/materialRipple/jquery.materialripple'));
                echo $this->Core->css(array('plugins/Assets/css/smallipop/jquery.smallipop'));
                echo $this->Core->css(array('plugins/Assets/css/chardinjs/chardinjs'));

                echo $this->Core->css(array('plugins/Assets/plugins/prettify/prettify'));

                echo $this->Core->css(array('plugins/Assets/css/multiselect/jquery.multiselect'));
                echo $this->Core->css(array('plugins/Assets/css/multiple-select/multiple-select'));
                
                if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                echo $this->Html->css(array('bootstrap/bootstrap'));
                echo $this->Html->css(array('datatables'));
                echo $this->Html->css(array('site.css','menu','core','forms','reset'));
                echo $this->Html->css(array('ownPlugins/cocoCropper/cococropper'));
                echo $this->Html->css(array('ownPlugins/circularMenu/style'));
                if (isset($cssToInclude)) {
                    foreach ($cssToInclude as $css) {
                        echo $this->Html->css($css);
                    }
                }                
		        echo $this->fetch('css');

                /*
                echo "<pre>";
                var_dump($jsVars);
                echo "</pre>";*/
                /******************************CSS & SCRIPTS SEPARATOR*****************************************************/
                //fetch own plugin extensions and core jsVars
                echo $this->Html->scriptBlock('var jsVars = ' . $this->Js->object($jsVars) . ';');
                echo $this->Html->script(array('ownPlugins/editorLanguage/nicEditLang'));
                echo $this->fetch('script');

                //fetch opensource scripts
                echo $this->Core->script(array('plugins/Assets/js/jquery/jquery-1.8.3','plugins/Assets/js/jquery/ext/jquery.browser.min','plugins/Assets/js/jquery/ext/jquery.cookie','plugins/Assets/js/jquery/ui/jquery-ui.min.1.10.1','plugins/Assets/js/jquery/ext/jquery.form'));
                echo $this->Core->script(array('plugins/Assets/js/jquery/localize/datepicker-en','plugins/Assets/js/jquery/localize/datepicker-es'));
                echo $this->Core->script(array('plugins/Assets/js/modernizr/modernizr.custom'));
                echo $this->Core->script(array('plugins/Assets/js/disablescroll/disableScroll'));
                //echo $this->Core->script(array('plugins/Assets/js/idleTimer/jquery.idletimer'));
                echo $this->Core->script(array('plugins/Assets/js/idleTimer/idle-timer'));
                echo $this->Core->script(array('plugins/Assets/js/respond/respond.src'));
                echo $this->Core->script(array('plugins/Assets/js/blockUI/jquery.blockUI'));
                echo $this->Core->script(array('plugins/Assets/js/nicEdit/nicEdit'));
                echo $this->Core->script(array('plugins/Assets/js/nicescroll/jquery.nicescroll.min'));
                //echo $this->Core->script(array('plugins/Assets/js/labelPattern/prefixfree.min'));
                echo $this->Core->script(array('plugins/Assets/js/cropper/cropper'));
                echo $this->Core->script(array('plugins/Assets/js/chosen/chosen.jquery','plugins/Assets/js/chosen/ImageSelect.jquery'));
                echo $this->Core->script(array('plugins/Assets/js/placeholder/jquery.placeholder'));
                echo $this->Core->script(array('plugins/Assets/js/EngineValidation/validationEngine','plugins/Assets/js/EngineValidation/validationEngine_'.strtoupper($language)));
                echo $this->Core->script(array('plugins/Assets/js/clockPicker/jquery-clockpicker.min'));
                echo $this->Core->script(array('plugins/Assets/js/tipsy/jquery.tipsy','plugins/Assets/js/notifIt/notifIt'));

                echo $this->Core->script(array('plugins/Assets/js/spinner/spin.min'));
                echo $this->Core->script(array('plugins/Assets/js/progressButton/classie','plugins/Assets/js/progressButton/progressButton'));
                echo $this->Core->script(array('plugins/Assets/js/perfectScrollbar/jquery.mousewheel','plugins/Assets/js/perfectScrollbar/perfect-scrollbar'));

                echo $this->Core->script(array('plugins/Assets/js/materialRipple/jquery.materialripple'));
                echo $this->Core->script(array('plugins/Assets/js/smallipop/jquery.smallipop'));

                echo $this->Core->script(array('plugins/Assets/js/chardinjs/chardinjs.min'));

                echo $this->Core->script(array('plugins/Assets/plugins/prettify/prettify'));
                //echo $this->Core->script(array('plugins/Assets/plugins/slimscroll/jquery.slimscroll'));
                echo $this->Core->script(array('plugins/Assets/js/multiselect/jquery.multiselect'));
                echo $this->Core->script(array('plugins/Assets/js/multiple-select/multiple-select'));

                if (isset($openJsToInclude)) {
                       echo $this->Core->script($openJsToInclude);
                }

                //Fetch own js scripts
                echo $this->Html->script(array('common_actions','core','common','conti_extensions','phpjs','updates'));
                echo $this->Html->script(array('ownPlugins/loadingButton/loadingButton','ownPlugins/contiUploader/conti_l10n','ownPlugins/contiUploader/conti.uploader','ownPlugins/cocoCropper/cococropper','ownPlugins/cocoBlock/cocoblock'));
                echo $this->Html->script(array('ownPlugins/cocoPassGen/cocopass.generator'));
                if (isset($jsToInclude)) {
                     echo $this->Html->script($jsToInclude);
                }
		echo $this->fetch('script');
	?>
        <script>
            /*
            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-40475486-1', 'auto');
            ga('send', 'pageview');
*/
        </script>
    </head>
    <body>                     
        <?php $this->Menu->toggler();?>
        <div id="panel_notifications">
            <!--<div id="accept_notifications_ctrl">
                <div class="onoffswitch-text"><?php echo __('Notificaciones via email',true); ?></div>
                <div class="onoffswitch">
                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" <?php if($credentials['accept_mail_notif']==_ACCEPT_MAIL_NOTIFICATIONS){?> checked <?php } ?>>
                    <label class="onoffswitch-label" for="myonoffswitch">
                        <span class="onoffswitch-inner">
                            <span class="onoffswitch-active"><span class="onoffswitch-switch">ON</span></span>
                            <span class="onoffswitch-inactive"><span class="onoffswitch-switch">OFF</span></span>
                        </span>
                    </label>
                </div>
            </div>-->
            <div id="notifications_wrapper"></div>            
        </div>
        <div id="container" class="<?php if(isset($responsive) && $responsive){ ?>responsive-page <?php } ?>">    
            <div id="header">                
                <?php echo $this->Html->link('','/',array('id'=>'main_logo')); ?>
                <?php $this->Menu->top(); ?>
            </div>
            <div id="direct_menu">
                <div id="fast_menu_nav" data-section="1" data-intro="<?php echo __('tutorial_acceso_directo',true);?>" data-position="bottom">
                    <?php $this->Menu->fastMenu(); ?>
                </div>
                <!--add items menu -->
                <?php
                    $addCompanyColaboratorAllow = isset($credentials['permissions'][IElement::Is_CompanyColaborator]) && in_array('add', $credentials['permissions'][IElement::Is_CompanyColaborator]['allows']) ? true : false;
                    $addConveyorAllow = isset($credentials['permissions'][IElement::Is_Conveyor]) && in_array('add', $credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
                    $addNewsAllow = isset($credentials['permissions'][IElement::Is_News]) && in_array('add', $credentials['permissions'][IElement::Is_News]['allows']) ? true : false;
                    $addNotificationsAllow = isset($credentials['permissions'][IElement::Is_Notification]) && in_array('add', $credentials['permissions'][IElement::Is_Notification]['allows']) ? true : false;
                    $canAddFiles = isset($credentials['permissions'][IElement::Is_File]) && in_array('add', $credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
               
                    $canAddFolder = isset($credentials['permissions'][IElement::Is_Folder]) && in_array('add', $credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
                    $canAddCustomer = isset($credentials['permissions'][IElement::Is_Customer]) && in_array('add', $credentials['permissions'][IElement::Is_Customer]['allows']) ? true : false;

               ?>
                <?php //if($addCompanyColaboratorAllow || $addConveyorAllow || $addNewsAllow || $addNotificationsAllow){ ?>

                <?php $availableAssets = isset($available_asset_folders) && !empty($available_asset_folders); ?>
                <ul id="main_saver" class=<?php echo $availableAssets ? 'action-list' : ''; ?>>
                    <?php if($availableAssets && $canAddFolder): ?>
                        <li class="is-text">
                            <a><?php echo __("Add asset",true); ?></a>
                            <ul>
                                <?php foreach($available_asset_folders AS $nodeData): ?>
                                    <?php if(!empty($nodeData['nodes'])): ?>
                                            <li>
                                                <a class="user-item parent"><?php echo $nodeData['name']; ?></a>
                                                <ul class="options-menu">
                                                    <?php foreach($nodeData['nodes'] AS $nodeId => $node): ?>
                                                        <li><?php echo $this->Html->link($node, '#', array('class' => 'add_asset_folder', 'data-type' => $nodeId,'data-name'=>$node, 'rel' => $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'saveItemFolder', $secure_params[0], $secure_params[1])))); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                             </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                            <?php if ($name_action === 'clients' && $canAddCustomer) { ?>
                                <li class="dropdown_menu text-menu main-saver">
                                    <?php echo $this->Html->link(__('Add customer', true), '#modal', array('class' => 'text-link add_user', 'alt' => 'company-dialog|callNewCompany', 'rel' => $this->Html->url(array('controller' => 'Companies', 'action' => 'append','20')))); ?>
                                </li>
                            <?php } else if ( $addConveyorAllow && (($name_controller === 'companies' && $name_action === 'view') || ($name_controller === 'buoysystems' && $name_action === 'dashboard'))){ ?>
                                <li class="dropdown_menu text-menu main-saver">
                                    <?php echo $this->Html->link(__('Add buoy system', true), '#modal', array('class' => 'text-link add_conveyor', 'alt' => 'conveyor-dialog|callAddConveyor|callUpdateConveyorsDataTable', 'rel' => $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'add')))); ?>
                                </li>
                            <?php }else if(isset($addFileFolderUrl) && $canAddFolder) { ?>
                                <li class="dropdown_menu text-menu main-saver">
                                    <?php echo $this->Html->link(__('Add new folder', true), '#modal', array('class' => 'text-link add-file-conveyor add-mediaitem-conveyor-link', 'dialog-style' => 'folder-dialog', 'title' => __('New folder', true), 'alt' => 'folder-dialog|initEventsAddItemConveyor|updateItemsIfRequired', 'rel' => $addFileFolderUrl)); ?>
                                </li>
                            <?php } ?>

                            <?php if(isset($add_file_url) && $canAddFiles){ ?>
                                <li class="dropdown_menu text-menu main-saver">
                                <?php echo $this->Html->link(__('Add files', true), '#modal', array('class' => 'text-link add-file-conveyor add-mediaitem-conveyor-link', 'dialog-style' => 'file-dialog', 'title' => __('New file', true), 'alt' => 'file-dialog|initEventsAddFileToFolder|updateItemsIfRequired', 'rel' => $add_file_url)); ?>
                                </li>
                            <?php } ?>
                </ul>
                <?php // } ?>
            </div>
            <div id="toolbar" class="<?php if(strtolower($name_controller)=='index'): echo 'home'; endif; ?>">
                <?php $this->Menu->toolbar(); ?>
            </div>
            <div id="content">
                <?php 
                   echo $this->fetch('content');
                ?>                     
            </div>            
        </div>
        <div id="footer">
            &copy; <?php echo date('Y'); ?> 
            <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
        </div>
        <?php if($credentials['security_question']<=0): ?>
            <input type="hidden" id="user_security_question_unsetted" value="0"/>
        <?php endif; ?>

        <?php
           // $this->Utilities->putSiteAssets();
        ?>
        <div id="fixed_overlay"></div>
        <div id="dialog_wrap"></div>
        <div id="alert_wrap"></div>        
        <div id="aux-dialog"></div>
        <div id="temp-dialog"></div>
        <div id="confirm-dialog"></div>
        <div id="sessionTimeoutWarning"></div>        
        <div id="aux_overlay" class="fixed-overlay"></div>
        <!--<div id="cco_license" class="hidden">
            <a href="http://www.bannerflow.com/blog/free-flat-flag-icons" target="_blank">Free Flat Flag Icons by BannerFlow is licensed under a Creative Commons Attribution-ShareAlike 4.0 International License.</a>
            <a href="http://www.entypo.com/" target="_blank">Free Entypo Icons by Daniel Bruce is licensed under a Creative Commons Attribution-ShareAlike 4.0 International License. </a>
            <a href="http://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Creative Commons License</a>
        </div>-->
        <?php //echo $this->Html->script("https://maps.googleapis.com/maps/api/js?sensor=true"); ?>
        <?php //echo $this->Html->script("http://d3ra5e5xmvzawh.cloudfront.net/live-widget/2.0/spot-main-min.js"); ?>
        <script src="https://browser.sentry-cdn.com/4.6.4/bundle.min.js" crossorigin="anonymous"></script>
        <script>
            Sentry.init({ dsn: 'https://7f0a78cc21754cfb80245b1237109ed9@sentry.io/1413118' });
        </script>
    </body>
</html>