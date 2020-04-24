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
                echo '<meta name="copyright" content="' . date('Y') . ' ContiTech AG, ALL RIGHTS RESERVED"/>';
                echo '<meta name="author" content="' . date('Y') . ' Cocothink"/>';
                echo '<link href="'.$webroot.'img/favicon.ico" type="image/x-icon" rel="icon" />';
                echo $this->fetch('meta');
                
                 //fetch opensource stylesheets
                echo $this->Core->css(array('plugins/Assets/css/jquery-ui/jquery-ui'));
                echo $this->Core->css(array('plugins/Assets/css/progressButton/component'));
                echo $this->Core->css(array('plugins/Assets/css/chosen/chosen','plugins/Assets/css/chosen/ImageSelect','plugins/Assets/css/notifIt/notifIt'));                
                echo $this->Core->css(array('plugins/Assets/css/tipsy/tipsy','plugins/Assets/css/tipsy/tipsy-docs'));
                echo $this->Core->css(array('plugins/Assets/css/perfectScrollbar/perfect-scrollbar'));
                echo $this->Core->css(array('plugins/Assets/css/EngineValidation/validationEngine'));
                echo $this->Core->css(array('plugins/Assets/css/labelPattern/style'));
                if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                echo $this->Html->css(array('site','menu','core','forms'));
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
                echo $this->Core->script(array('plugins/Assets/js/jquery/localize/datepicker-en','plugins/Assets/js/jquery/localize/datepicker-es'));
                echo $this->Core->script(array('plugins/Assets/js/modernizr/modernizr.custom'));
                echo $this->Core->script(array('plugins/Assets/js/blockUI/jquery.blockUI'));
                echo $this->Core->script(array('plugins/Assets/js/spinner/spin.min'));
                
                if (isset($openJsToInclude)) {
                       echo $this->Core->script($openJsToInclude);
                }                   
                
                //Fetch own js scripts        
                echo $this->Html->script(array('common_actions','core','common','conti_extensions','phpjs'));                                        
                echo $this->Html->script(array('ownPlugins/cocoBlock/cocoblock'));
                if (isset($jsToInclude)) {
                     echo $this->Html->script($jsToInclude);
                }                                             
		echo $this->fetch('script');       
	?>
    </head>
    <body>                     
        <div id="letter">
            <div id="container">     
                <div id="content">
                    <?php 
                       echo $this->fetch('content');
                    ?>                     
                </div>            
                <div id="footer">
                    &copy; <?php echo date('Y'); ?> 
                    <?php echo __('ContiTech AG, ALL RIGHTS RESERVED', true); ?>
                </div>    
            </div>                       
        </div>
	<?php 
            $this->Utilities->putSiteAssets();
            // echo $this->element('sql_dump'); 
        ?>        
    </body>
</html>
