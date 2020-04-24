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
        <meta http-equiv="Access-Control-Allow-Origin" content="*">
	<?php         
            echo $this->Html->charset(); 
         ?>        
        <title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
        </title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="Contiplus getmore conti+">
        <meta name="author" content="Humann Technologies">
        <?php
        echo $this->Html->meta('icon');
        echo '<meta name="copyright" content="' . date('Y') . ' ContiTech AG, ALL RIGHTS RESERVED"/>';
        echo '<link href="'.$webroot.'img/favicon.ico" type="image/x-icon" rel="icon" />';
        ?>

         <?php

                echo $this->fetch('meta');                
                
                 //fetch opensource stylesheets

                //Stylesheets
                echo $this->Core->css(array('plugins/Template/css/bootstrap.min'));
                echo $this->Core->css(array('plugins/Template/css/bootstrap-extend.min'));
                echo $this->Core->css(array('plugins/Template/assets/css/site.min'));
                //Plugins
                echo $this->Core->css(array('plugins/Template/vendor/animsition/animsition'));
                echo $this->Core->css(array('plugins/Template/vendor/asscrollable/asScrollable'));
                echo $this->Core->css(array('plugins/Template/vendor/switchery/switchery'));
                echo $this->Core->css(array('plugins/Template/vendor/intro-js/introjs'));
                echo $this->Core->css(array('plugins/Template/vendor/slidepanel/slidePanel'));
                echo $this->Core->css(array('plugins/Template/vendor/flag-icon-css/flag-icon'));
                echo $this->Core->css(array('plugins/Template/vendor/waves/waves'));
                echo $this->Core->css(array('plugins/Template/assets/examples/css/pages/login'));

                echo $this->Core->css(array('plugins/Template/fonts/material-design/material-design.min'));
                echo $this->Core->css(array('plugins/Template/fonts/brand-icons/brand-icons.min'));
                echo $this->Core->css(array('plugins/Template/assets/skins/orange'));
                 if (isset($openCssToInclude)) {
                       echo $this->Core->css($openCssToInclude);
                }      
                
                //Fetch own stylesheets
                //echo $this->Minify->css(array('login','menu','core','forms'));

                if (isset($cssToInclude)) {
                    foreach ($cssToInclude as $css) {
                        echo $this->Minify->css($css);
                    }
                }                
		        echo $this->fetch('css');

         ?>
        <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>


        <?php

                /******************************CSS & SCRIPTS SEPARATOR*****************************************************/

                //fetch own plugin extensions and core jsVars
                echo $this->Html->scriptBlock('var jsVars = ' . $this->Js->object($jsVars) . ';');                
                echo $this->fetch('script');
               ?>
        <!--[if lt IE 9]>
        <?php echo $this->Core->script(array('plugins/Template/vendor/html5shiv/html5shiv.min')); ?>
        <![endif]-->
        <!--[if lt IE 10]>
        <?php echo $this->Core->script(array('plugins/Template/vendor/media-match/media.match.min')); ?>
        <?php echo $this->Core->script(array('plugins/Template/vendor/respond/respond.min')); ?>
        <![endif]-->
        <!-- Scripts -->
        <?php echo $this->Core->script(array('plugins/Template/vendor/breakpoints/breakpoints')); ?>
        <script>
            Breakpoints();
        </script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
    </head>
    <body class="animsition page-login layout-full page-dark">
    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->
    <!-- Page -->
    <div class="page vertical-align text-center" data-animsition-in="fade-in" data-animsition-out="fade-out">
        <div class="page-content vertical-align-middle">
            <div class="brand">
                <?php echo $this->Html->image('logos/logo_contiplus.png', array('id' => 'logo','class'=>'brand-img')); ?>
                <!--<h2 class="brand-text">Remark</h2>-->
            </div>
            <p></p>
            <?php echo $this->fetch('content'); ?>
            <footer class="page-copyright page-copyright-inverse">
                <p>&nbsp;</p>
                <p>&copy; <?php echo date('Y'); ?> <?php echo __('ContiTech Mexicana, ALL RIGHTS RESERVED', true); ?></p>
                <div class="social">
                    <!--<a class="btn btn-icon btn-pure" href="javascript:void(0)">
                        <i class="icon bd-twitter" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-icon btn-pure" href="javascript:void(0)">
                        <i class="icon bd-facebook" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-icon btn-pure" href="javascript:void(0)">
                        <i class="icon bd-google-plus" aria-hidden="true"></i>
                    </a>-->
                </div>
            </footer>
        </div>
    </div>
    <div id='main_easter' style="position: absolute; top: 0; display: none; background: transparent url('http://contiplus.net/img/eigth.png') no-repeat; width: 50px; height: 50px;"></div>
    <div id="bg_effect_grid"> </div>
    <div id="alert_wrap"></div>
    <div id="confirm-dialog"></div>
    <!-- End Page -->
    <?php
    //Core
    echo $this->Core->script(array('plugins/Template/vendor/babel-external-helpers/babel-external-helpers'));
    echo $this->Core->script(array('plugins/Template/vendor/jquery/jquery'));
    echo $this->Core->script(array('plugins/Template/vendor/tether/tether'));
    echo $this->Core->script(array('plugins/Template/vendor/bootstrap/bootstrap'));
    echo $this->Core->script(array('plugins/Template/vendor/animsition/animsition'));
    echo $this->Core->script(array('plugins/Template/vendor/mousewheel/jquery.mousewheel'));
    echo $this->Core->script(array('plugins/Template/vendor/asscrollbar/jquery-asScrollbar'));
    echo $this->Core->script(array('plugins/Template/vendor/asscrollable/jquery-asScrollable'));
    echo $this->Core->script(array('plugins/Template/vendor/ashoverscroll/jquery-asHoverScroll'));
    echo $this->Core->script(array('plugins/Template/vendor/waves/waves'));
    //Plugins
    //echo $this->Core->script(array('plugins/Template/vendor/switchery/switchery.min'));
    //echo $this->Core->script(array('plugins/Template/vendor/intro-js/intro'));
    //echo $this->Core->script(array('plugins/Template/vendor/screenfull/screenfull'));
    //echo $this->Core->script(array('plugins/Template/vendor/slidepanel/jquery-slidePanel'));
    //echo $this->Core->script(array('plugins/Template/vendor/jquery-placeholder/jquery.placeholder'));

    //Scripts
    echo $this->Core->script(array('plugins/Template/js/State'));
    echo $this->Core->script(array('plugins/Template/js/Component'));
    echo $this->Core->script(array('plugins/Template/js/Plugin'));
    echo $this->Core->script(array('plugins/Template/js/Base'));
    echo $this->Core->script(array('plugins/Template/js/Config'));

    echo $this->Core->script(array('plugins/Template/assets/js/Section/Menubar'));
    echo $this->Core->script(array('plugins/Template/assets/js/Section/GridMenu'));
    echo $this->Core->script(array('plugins/Template/assets/js/Section/Sidebar'));
    //echo $this->Core->script(array('plugins/Template/assets/js/Section/PageAside'));
    //echo $this->Core->script(array('plugins/Template/assets/js/Plugin/menu'));

    //echo $this->Core->script(array('plugins/Template/js/config/colors'));
    //echo $this->Core->script(array('plugins/Template/assets/js/config/tour'));

    //Page
    echo $this->Core->script(array('plugins/Template/assets/js/Site'));
    //echo $this->Core->script(array('plugins/Template/js/Plugin/asscrollable'));
    //echo $this->Core->script(array('plugins/Template/js/Plugin/slidepanel'));

    echo $this->Core->script(array('plugins/Template/vendor/formvalidation/formValidation.min'));
    echo $this->Core->script(array('plugins/Template/vendor/formvalidation/framework/bootstrap4.min'));

    //echo $this->Core->script(array('plugins/Template/js/Plugin/switchery'));
    //echo $this->Core->script(array('plugins/Template/js/Plugin/jquery-placeholder'));
    echo $this->Core->script(array('plugins/Template/js/Plugin/material'));

    if (isset($openJsToInclude)) {
        echo $this->Core->script($openJsToInclude);
    }
    ?>
    <script>
        (function(document, window, $) {
            'use strict';
            var Site = window.Site;
            $(document).ready(function() {
                Site.run();
            });
        })(document, window, jQuery);
    </script>
    <?php
    //Fetch own js scripts
    /*
           echo $this->Minify->script(array('core'));
           echo $this->Minify->script(array('ownPlugins/cocoBlock/cocoblock','conti_extensions'));
           echo $this->Minify->script(array('ownPlugins/loadingButton/loadingButton'));
    */
    if (isset($jsToInclude)) {
        echo $this->Minify->script($jsToInclude);
    }
    echo $this->fetch('script');

    ?>
    </body>
</html>
