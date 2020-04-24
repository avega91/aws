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

ob_start();
?>
<!DOCTYPE html>
<html>
    <head>
	<?php echo $this->Html->charset(); ?>
        <title>hola</title>
	<style>
        body,html{
        color:#272727;
        font-size:14px;
        font-family: 'dejavu sans';
        line-height:0.9;
        font-size:14px;
        }
        #footer{
            color: #F00;
        }
        
        </style>
    </head>
    <body>                     
        <div id="letter">
            <div id="container">     
                <div id="content">
                    <?php 
                       echo $this->fetch('content');
                    ?>                     
                </div>            
                
            </div>                       
        </div>
        ?>        
    </body>
</html>
