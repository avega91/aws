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
        <meta http-equiv="Access-Control-Allow-Origin" content="*">
	<?php         
            echo $this->Html->charset(); 
         ?>        
        <title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
        </title>
	<?php
		echo $this->Html->meta('icon');
                echo '<meta name="copyright" content="' . date('Y') . ' ContiTech AG, ALL RIGHTS RESERVED"/>';
                echo '<meta name="author" content="' . date('Y') . ' Cocothink"/>';
                echo '<link href="'.$webroot.'img/favicon.ico" type="image/x-icon" rel="icon" />';
                echo $this->fetch('meta');                
                
                 //fetch opensource stylesheets
                echo $this->Core->css(array('plugins/Assets/css/jquery-ui/jquery-ui'));
                echo $this->Core->css(array('plugins/Assets/css/EngineValidation/validationEngine'));
                echo $this->Core->css(array('plugins/Assets/css/tipsy/tipsy','plugins/Assets/css/tipsy/tipsy-docs'));
                echo $this->Core->css(array('plugins/Assets/css/progressButton/component'));
                echo $this->Core->css(array('plugins/Assets/css/notifIt/notifIt'));
    if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                 echo $this->Html->css(array('login','menu','core','forms'));

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
                echo $this->Core->script(array('plugins/Assets/js/jquery/jquery-1.8.3','plugins/Assets/js/jquery/ext/jquery.cookie','plugins/Assets/js/jquery/ui/jquery-ui.min.1.10.1'));
                //echo $this->Core->script(array('plugins/Assets/js/modernizr/modernizr.min'));
                 echo $this->Core->script(array('plugins/Assets/js/modernizr/modernizr.custom'));
                //echo $this->Core->script(array('plugins/Assets/js/nicescroll/jquery.nicescroll.min'));
                echo $this->Core->script(array('plugins/Assets/js/blockUI/jquery.blockUI','plugins/Assets/js/EngineValidation/validationEngine'));
                echo $this->Core->script(array('plugins/Assets/js/EngineValidation/validationEngine_'.strtoupper($language)));
                echo $this->Core->script(array('plugins/Assets/js/bgResize/jquery.ez-bg-resize'));
                //echo $this->Core->script(array('plugins/Assets/js/s3captcha/s3Capcha'));
                echo $this->Core->script(array('plugins/Assets/js/tipsy/jquery.tipsy','plugins/Assets/js/notifIt/notifIt'));
                echo $this->Core->script(array('plugins/Assets/js/placeholder/jquery.placeholder'));
                echo $this->Core->script(array('plugins/Assets/js/spinner/spin.min'));
                echo $this->Core->script(array('plugins/Assets/js/progressButton/classie','plugins/Assets/js/progressButton/progressButton'));
                if (isset($openJsToInclude)) {
                       echo $this->Core->script($openJsToInclude);
                }                   
                
                //Fetch own js scripts                
                //echo $this->Html->script(array('core'));
                echo $this->Html->script(array('core'));
                echo $this->Html->script(array('ownPlugins/cocoBlock/cocoblock','conti_extensions'));
                echo $this->Html->script(array('ownPlugins/loadingButton/loadingButton'));
                if (isset($jsToInclude)) {
                     echo $this->Html->script($jsToInclude);
                }                             
		        echo $this->fetch('script');
                
	?> 
        <script>
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

        </script>
    </head>
    <body>        
        <div id="container">            
            <div class="container">
                <div id="wrapper">
                    <div id="access_form">                    
                        <div id="language_login_bar">
                            <div id="topmenu">
                                <ul>
                                    <li id="language_trigger" class="icon dropdown_menu">
                                <?php echo $this->Html->link('','#',array('id'=>'change_language','class'=>$language.'_flag country_flag')); ?>                                                        
                                        <ul>
                                            <span>&nbsp;</span>
                                            <li><?php echo $this->Html->link(__('Espanol',true),$language!='es' ? array('controller'=>'Settings','action'=>'setLang','es'):'#',array('class'=>'es_flag country_flag')); ?></li>
                                            <li><?php echo $this->Html->link(__('Ingles',true), $language!='en' ? array('controller'=>'Settings','action'=>'setLang','en'):'#',array('class'=>'en_flag country_flag')); ?></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                <?php 
                    echo $this->fetch('content');
                    ?>                                        
                    </div>
                </div>
            </div>                        
        </div>
        <div id="footer">
            <div id='main_easter' style="position: absolute; top: 0; display: none; background: transparent url('https://contiplus.net/img/eigth.png') no-repeat; width: 50px; height: 50px;"></div>
            <div>
                &copy; <?php echo date('Y'); ?> 
                    <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?>
            </div>
        </div>
        <div id="bg_effect_grid"> </div>
        <div id="alert_wrap"></div>
        <div id="confirm-dialog"></div>
	<?php // echo $this->element('sql_dump'); ?>         
    </body>
</html>
