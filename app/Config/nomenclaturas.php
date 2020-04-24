<?php

/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file <file name eg: foo.c or foo.h>
 *     <Description of file>
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */

//LOS INDICES DEL ARRAY ACANALAMIENTO SON LOS ANGULOS
//LOS INDICES DE LOS ARRAYS DE LOS ANGULOS, EL PRIMER => ACHO MINIMO DE BANDA, LOS SIGUIENTES SON LOS ANCHOS MAXIMOS Y SE CALCULAN SEGUN LA DENSIDAD DEL MATERIAL
$config['App']['nomenclaturas'][400] = array(
            220 => array('acanalamiento' => array(20 => array(16,48,42,36,30),35=>array(18,42,36,30,24), 45=>array(24,36,36,24,'NR')),
                         'nombres_banda' => array('CX 3-330','CX EP630/3')),
            250 => array('acanalamiento' => array(20 => array(16,54,48,42,36),35=>array(18,48,42,36,30), 45=>array(24,42,36,30,'NR')),
                         'nombres_banda' => array('CX 3-330','CX EP630/3')),
            330 => array('acanalamiento' => array(20 => array(20,60,60,48,42),35=>array(24,54,48,42,42), 45=>array(30,48,42,36,36)),
                         'nombres_banda' => array('CX 3-330','CX EP630/3')),
            375 => array('acanalamiento' => array(20 => array(20,72,60,54,48),35=>array(24,60,60,54,42), 45=>array(30,60,54,48,36)),
                         'nombres_banda' => array('CX 3-375','CX EP750/3')),
            440 => array('acanalamiento' => array(20 => array(24,72,66,60,48),35=>array(30,72,60,54,42), 45=>array(36,60,54,48,36)),
                         'nombres_banda' => array('CX 4-440','CX EP800/4')),
            450 => array('acanalamiento' => array(20 => array(24,72,66,60,54),35=>array(30,66,60,54,48), 45=>array(36,60,54,48,42)),
                         'nombres_banda' => array('CX 3-450','CX EP800/4')),
            500 => array('acanalamiento' => array(20 => array(30,84,72,66,60),35=>array(36,72,60,60,54), 45=>array(42,72,54,54,48)),
                         'nombres_banda' => array('CX 4-500','CX EP800/4')),
            600 => array('acanalamiento' => array(20 => array(30,84,72,66,54),35=>array(30,72,60,54,48), 45=>array(36,66,54,48,42)),
                         'nombres_banda' => array('CX 3-600','CX EP1000/4')),
            750 => array('acanalamiento' => array(20 => array(30,84,72,66,54),35=>array(36,72,60,60,48), 45=>array(42,66,54,54,42)),
                         'nombres_banda' => array('CX 3-750','CX EP1250/4')),
            800 => array('acanalamiento' => array(20 => array(30,96,84,84,72),35=>array(36,84,72,72,60), 45=>array(42,72,66,60,54)),
                         'nombres_banda' => array('CX 4-800','CX EP 1400/4')),
            900 => array('acanalamiento' => array(20 => array(30,84,72,72,60),35=>array(36,72,72,60,54), 45=>array(42,66,66,54,48)),
                         'nombres_banda' => array('CX 3-900','CX EP1500/3')),
            1000 => array('acanalamiento' => array(20 => array(30,96,84,84,72),35=>array(42,84,72,72,60), 45=>array(48,84,72,60,48)),
                         'nombres_banda' => array('CX 4-1000','CX EP1600/3')),
            1200 => array('acanalamiento' => array(20 => array(42,108,96,84,72),35=>array(42,96,84,72,66), 45=>array(48,84,78,72,60)),
                         'nombres_banda' => array('CX 4-1200','CX EP2000/4')),
            1250 => array('acanalamiento' => array(20 => array(42,120,108,108,96),35=>array(48,108,96,96,84), 45=>array(54,96,84,84,72)),
                         'nombres_banda' => array('CX 5-1250','CX EP2250/5')),
            1500 => array('acanalamiento' => array(20 => array(42,108,96,96,84),35=>array(48,96,96,84,72), 45=>array(54,84,84,72,72)),
                         'nombres_banda' => array('CX 3-1500','CX EP3200/4')),
            1800 => array('acanalamiento' => array(20 => array(48,126,120,108,108),35=>array(54,126,108,108,96), 45=>array(60,120,108,96,84)),
                         'nombres_banda' => array('CX 4-1800','CX EP3500/4')),
            2000 => array('acanalamiento' => array(20 => array(54,126,126,126,114),35=>array(54,126,126,120,108), 45=>array(60,126,126,114,102)),
                         'nombres_banda' => array('CX 5-2000','CX EP3150/5'))
    );

$config['App']['nomenclaturas'][800] = array(
            180 => array('acanalamiento' => array(20 => array(24,84,72,72,60),35=>array(30,72,66,60,54), 45=>array(36,66,60,54,48)),
                         'nombres_banda' => array('Conti Titan II-350','CX EP630/3')),
            230 => array('acanalamiento' => array(20 => array(24,84,72,72,60),35=>array(30,72,66,60,54), 45=>array(36,66,60,54,48)),
                         'nombres_banda' => array('Conti Titan II-350','CX EP630/3')),
            285 => array('acanalamiento' => array(20 => array(24,84,72,72,60),35=>array(30,72,66,60,54), 45=>array(36,66,60,54,48)),
                         'nombres_banda' => array('Conti Titan II-350','CX EP630/3')),
            350 => array('acanalamiento' => array(20 => array(24,84,72,72,60),35=>array(30,72,66,60,54), 45=>array(36,66,60,54,48)),
                         'nombres_banda' => array('Conti Titan II-350','CX EP750/3')),
            450 => array('acanalamiento' => array(20 => array(24,84,84,84,72),35=>array(30,84,72,72,60), 45=>array(36,72,72,60,54)),
                         'nombres_banda' => array('Conti Titan II-450','CX EP800/4')),
            550 => array('acanalamiento' => array(20 => array(24,84,84,84,72),35=>array(30,84,72,72,60), 45=>array(36,72,72,60,54)),
                         'nombres_banda' => array('Conti Titan II-550','CX EP800/4 ')),
            700 => array('acanalamiento' => array(20 => array(30,84,84,84,84),35=>array(36,84,84,84,72), 45=>array(42,84,84,72,60)),
                         'nombres_banda' => array('Conti Titan II-700','CX EP800/4')),
            800 => array('acanalamiento' => array(20 => array(30,84,84,84,84),35=>array(36,84,84,84,78), 45=>array(42,84,84,84,72)),
                         'nombres_banda' => array('Conti Titan II-1000','CX EP1000/4')),
            900 => array('acanalamiento' => array(20 => array(30,84,84,84,84),35=>array(36,84,84,84,78), 45=>array(42,84,84,84,72)),
                         'nombres_banda' => array('Conti Titan II-1000','CX EP1250/4')),
            1000 => array('acanalamiento' => array(20 => array(30,84,84,84,84),35=>array(36,84,84,84,78), 45=>array(42,84,84,84,72)),
                         'nombres_banda' => array('Conti Titan II-1000','CX EP 1400/4')),
            1250 => array('acanalamiento' => array(20 => array(30,84,84,84,84),35=>array(36,84,84,84,84), 45=>array(42,84,84,84,84)),
                         'nombres_banda' => array('Conti Titan II-1250','CX EP1500/3')),
            1750 => array('acanalamiento' => array(20 => array(36,126,120,108,108),35=>array(42,126,120,108,96), 45=>array(48,108,108,96,84)),
                         'nombres_banda' => array('Conti Titan II-1750','CX EP1600/3')),
            2000 => array('acanalamiento' => array(20 => array(42,126,126,120,114),35=>array(48,126,120,114,102), 45=>array(54,114,114,102,96)),
                         'nombres_banda' => array('Conti Titan II-2000','CX EP2000/4')),
            2250 => array('acanalamiento' => array(20 => array(42,126,126,120,114),35=>array(48,126,120,114,108), 45=>array(54,114,114,102,96)),
                         'nombres_banda' => array('Conti Titan II-2250','CX EP2250/5'))
    );

$config['App']['nomenclaturas'][801] = array(
            220 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-500')),
            250 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-500')),
            330 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-500')),
            375 => array('acanalamiento' => array(30 => array(16,)),
                         'nombres_banda' => array('-','ST-630')),
            440 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-630')),
            450 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-630')),
            500 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-630')),
            600 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-800')),
            750 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1000')),
            800 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1000')),
            900 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1250')),
            1000 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1250')),
            1200 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1400')),
            1250 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-1600')),
            1500 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-2000')),
            1800 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-2000')),
            2000 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-2250')),
            2250 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-2500')),
            2500 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-2750')),
            3000 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-3250')),
            4000 => array('acanalamiento' => array(30 => array(16,0)),
                         'nombres_banda' => array('-','ST-4250'))
    );


/**Cuando se excede **/
   $config['App']['nomenclaturas_extra'][400] = array(
            array('acanalamiento' => array(20 => array(48,42,36,30),35=>array(42,36,30,24), 45=>array(36,36,24,'NR')),
                  'nombres_banda' => array('CX 2-220','CX EP400/2')),
            array('acanalamiento' => array(20 => array(54,48,42,36),35=>array(48,42,36,30), 45=>array(42,36,30,'NR')),
                  'nombres_banda' => array('CX 2-250','CX EP500/2')),
            array('acanalamiento' => array(20 => array(60,60,48,'en'),35=>array(54,48,42,42), 45=>array(48,42,36,36)),
                  'nombres_banda' => array('CX 3-330','CX EP630/3')),
            array('acanalamiento' => array(20 => array(72,60,54,48),35=>array(60,60,54,42), 45=>array(60,54,48,36)),
                  'nombres_banda' => array('CX 3-375','CX EP750/3')),
            array('acanalamiento' => array(20 => array(72,66,60,48),35=>array(72,60,54,42), 45=>array(60,54,48,36)),
                  'nombres_banda' => array('CX 4-440','CX EP800/4')),
            array('acanalamiento' => array(20 => array(72,66,60,54),35=>array(66,60,54,48), 45=>array(60,54,48,42)),
                  'nombres_banda' => array('CX 3-450','CX EP800/4')),
            array('acanalamiento' => array(20 => array(84,72,66,60),35=>array(72,60,60,54), 45=>array(72,54,54,48)),
                  'nombres_banda' => array('CX 4-500','CX EP800/4')),
            array('acanalamiento' => array(20 => array(84,72,66,54),35=>array(72,60,54,48), 45=>array(66,54,48,42)),
                  'nombres_banda' => array('CX 3-600','CX EP1000/4')),
            array('acanalamiento' => array(20 => array(84,72,66,54),35=>array(72,60,60,48), 45=>array(66,54,54,42)),
                  'nombres_banda' => array('CX 3-750','CX EP1250/4')),
            array('acanalamiento' => array(20 => array(96,84,84,72),35=>array(84,72,72,60), 45=>array(72,66,60,54)),
                  'nombres_banda' => array('CX 4-800','CX EP 1400/4')),
            array('acanalamiento' => array(20 => array(84,72,72,60),35=>array(72,72,60,54), 45=>array(66,66,54,48)),
                  'nombres_banda' => array('CX 3-900','CX EP1500/3')),
            array('acanalamiento' => array(20 => array(96,84,84,72),35=>array(84,72,72,60), 45=>array(84,72,60,48)),
                  'nombres_banda' => array('CX 4-1000','CX EP1600/3')),
            array('acanalamiento' => array(20 => array(108,96,84,72),35=>array(96,84,72,66), 45=>array(84,78,72,60)),
                  'nombres_banda' => array('CX 4-1200','CX EP2000/4')),
            array('acanalamiento' => array(20 => array(120,108,108,96),35=>array(108,96,96,84), 45=>array(96,84,84,72)),
                  'nombres_banda' => array('CX 5-1250','CX EP2250/5')),
            array('acanalamiento' => array(20 => array(108,96,96,84),35=>array(96,96,84,72), 45=>array(84,84,72,72)),
                  'nombres_banda' => array('CX 3-1500','CX EP3200/4')),
            array('acanalamiento' => array(20 => array(126,120,108,108),35=>array(126,108,108,96), 45=>array(120,108,96,84)),
                  'nombres_banda' => array('CX 4-1800','CX EP3500/4')),  
            array('acanalamiento' => array(20 => array(126,126,126,114),35=>array(126,126,120,108), 45=>array(126,126,114,102)),
                  'nombres_banda' => array('CX 5-2000','CX EP3150/5'))
    );

   
   $config['App']['nomenclaturas_extra'][800] = array(
            array('acanalamiento' => array(20 => array(84,72,72,60),35=>array(72,66,60,54), 45=>array(66,60,54,48)),
                  'nombres_banda' => array('Conti Titan II-350','Conti Titan EPP630/2')),
           array('acanalamiento' => array(20 => array(84,72,72,60),35=>array(72,66,60,54), 45=>array(66,60,54,48)),
                  'nombres_banda' => array('Conti Titan II-350','Conti Titan EPP630/2')),
           array('acanalamiento' => array(20 => array(84,72,72,60),35=>array(72,66,60,54), 45=>array(66,60,54,48)),
                  'nombres_banda' => array('Conti Titan II-350','Conti Titan EPP630/2')),
           array('acanalamiento' => array(20 => array(84,72,72,60),35=>array(72,66,60,54), 45=>array(66,60,54,48)),
                  'nombres_banda' => array('Conti Titan II-350','Conti Titan EPP630/2')),
           array('acanalamiento' => array(20 => array(84,84,84,72),35=>array(84,72,72,60), 45=>array(72,72,60,54)),
                  'nombres_banda' => array('Conti Titan II-450','Conti Titan EPP800/2')),
           array('acanalamiento' => array(20 => array(84,84,84,72),35=>array(84,72,72,60), 45=>array(72,72,60,54)),
                  'nombres_banda' => array('Conti Titan II-550','Conti Titan EPP1000/2')),
           array('acanalamiento' => array(20 => array(84,84,84,84),35=>array(84,84,84,72), 45=>array(84,84,72,60)),
                  'nombres_banda' => array('Conti Titan II-700','Conti Titan EPP1250/2')),
           array('acanalamiento' => array(20 => array(84,84,84,84),35=>array(84,84,84,78), 45=>array(84,84,84,72)),
                  'nombres_banda' => array('Conti Titan II-1000','Conti Titan EPP1600/2')),  
           array('acanalamiento' => array(20 => array(84,84,84,84),35=>array(84,84,84,78), 45=>array(84,84,84,72)),
                  'nombres_banda' => array('Conti Titan II-1000','Conti Titan EPP1600/2')),  
           array('acanalamiento' => array(20 => array(84,84,84,84),35=>array(84,84,84,78), 45=>array(84,84,84,72)),
                  'nombres_banda' => array('Conti Titan II-1000','Conti Titan EPP1600/2')),  
           array('acanalamiento' => array(20 => array(84,84,84,84),35=>array(84,84,84,84), 45=>array(84,84,84,84)),
                  'nombres_banda' => array('Conti Titan II-1250','Conti Titan EPP2000/2')),  
           array('acanalamiento' => array(20 => array(126,120,108,108),35=>array(126,120,108,96), 45=>array(108,108,96,84)),
                  'nombres_banda' => array('Conti Titan II-1750','Conti Titan EPP2500/2')),
           array('acanalamiento' => array(20 => array(126,126,120,114),35=>array(126,120,114,102), 45=>array(114,114,102,96)),
                  'nombres_banda' => array('Conti Titan II-2000','Conti Titan EPP2800/2')),
           array('acanalamiento' => array(20 => array(126,126,120,114),35=>array(126,120,114,108), 45=>array(114,114,102,96)),
                  'nombres_banda' => array('Conti Titan II-2250','Conti Titan EPP3150/2')),             
    );
   
   $config['App']['nomenclaturas_extra'][801] = array();