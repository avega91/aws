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

$cakeDescription = __d('cake_dev', 'Contiplus');
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
                echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
                echo '<meta name="copyright" content="' . date('Y') . ' ContiTech AG, ALL RIGHTS RESERVED"/>';
                echo '<meta name="author" content="' . date('Y') . ' Cocothink"/>';
                echo '<link href="'.$webroot.'img/favicon.ico" type="image/x-icon" rel="icon" />';
                echo $this->fetch('meta');
                
                 //fetch opensource stylesheets
                echo $this->Core->css(array('plugins/Assets/css/jquery-ui/jquery-ui','plugins/Assets/css/notifIt/notifIt'));
                if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                echo $this->Html->css(array('mobile/site','mobile/menu','mobile/core'));
                if (isset($cssToInclude)) {
                    foreach ($cssToInclude as $css) {
                        echo $this->Html->css($css);
                    }
                }                
		echo $this->fetch('css');
                
                /******************************CSS & SCRIPTS SEPARATOR*****************************************************/
                //fetch own plugin extensions and core jsVars
                echo $this->Html->scriptBlock('var jsVars = ' . $this->Js->object($jsVars) . ';');                
                echo $this->fetch('script');       
                //fetch opensource scripts
                echo $this->Core->script(array('plugins/Assets/js/jquery/jquery-1.8.3','plugins/Assets/js/jquery/ext/jquery.browser.min','plugins/Assets/js/jquery/ext/jquery.cookie','plugins/Assets/js/jquery/ui/jquery-ui.min.1.10.1'));
                echo $this->Core->script(array('plugins/Assets/js/blockUI/jquery.blockUI','plugins/Assets/js/notifIt/notifIt'));
                echo $this->Core->script(array('plugins/Assets/js/spinner/spin.min'));
                echo $this->Core->script(array('plugins/Assets/js/idleTimer/jquery.idletimer'));
                if (isset($openJsToInclude)) {
                       echo $this->Core->script($openJsToInclude);
                }                   
                
                //Fetch own js scripts        
                echo $this->Html->script(array('common_actions','core','common','conti_extensions'));
                                echo $this->Html->script(array('ownPlugins/cocoBlock/cocoblock'));
                if (isset($jsToInclude)) {
                     echo $this->Html->script($jsToInclude);
                }                                             
		echo $this->fetch('script');       
	?>
    </head>
    <body>        
        <?php 
            if(!is_null($is_android)){ 
                $url_app = $is_android ? 'https://play.google.com/store/apps/details?id=com.cocothink.contiplusv2':'http://goo.gl/3U3DDk';
         ?>
        <div id="app_wrapper" >
            <span>Contiplus</span><br/>
            <?php echo __('Download the app to your device',true); ?>
            <a href="<?php echo $url_app; ?>" target="_blank"></a>
        </div>
        <?php } ?>
        <div id="container" class="responsive-page">    
            <div id="header">                
                <?php echo $this->Html->link('','#',array('id'=>'main_logo')); ?>
            </div>
            <div id="content">
                <?php 
                   echo $this->fetch('content');
                ?>                     
            </div>            
        </div>
        <div id="footer" class="responsive-page movil-page">
            &copy; <?php echo date('Y'); ?> 
            <?php echo __('ContiTech, ALL RIGHTS RESERVED', true); ?>
        </div>               
        <div id="fixed_overlay"></div>
        <div id="dialog_wrap"></div>
        <div id="alert_wrap"></div>        
        <div id="aux-dialog"></div>
        <div id="temp-dialog"></div>
        <div id="confirm-dialog"></div>
        <div id="aux_overlay" class="fixed-overlay"></div>
    </body>
</html>
