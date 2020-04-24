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
                //echo $this->Core->css(array('plugins/Assets/css/jquery-ui/jquery-ui'));
                if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                //echo $this->Html->css(array('site','menu','core'));
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
                echo $this->Core->script(array('plugins/Assets/js/jquery/jquery-1.8.3'));
                if (isset($openJsToInclude)) {
                       echo $this->Core->script($openJsToInclude);
                }                   
                
                //Fetch own js scripts        
                if (isset($jsToInclude)) {
                     echo $this->Html->script($jsToInclude);
                }                                             
		echo $this->fetch('script');       
	?>
        <style>
            html {
                min-width: 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            body{
                margin: 0 auto !important;
                padding: 0 !important;
                min-width: 0px !important;
                max-width: none !important;
            }
            .slw-banner img{
                display: none !important;
            }
            .top-bar-content a{
                display: none !important;
            }
        </style>
    </head>
    <body>                     
        <div id="container" class="responsive-page">    
            <div id="content">
                <?php 
                   echo $this->fetch('content');
                ?>                     
            </div>            
        </div>
    </body>
</html>
