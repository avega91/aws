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
?>
<form id="set_filter_form" action="#" class="fancy_form">
    <div class='slide-form-section'>        
        <div id="filter_list">
            <h1><?php echo __('Empresas', true); ?><span class="filter-selector-all selected"></span></h1>
            <div>
                <?php
                $type_manager = Configure::read('type_manager'); 
                $filter_list = '<ul>';
                if ($clients) {
                    $last_distributor = '';
                    foreach ($clients AS $client) {
                        $dist = $client['Distribuidor'];
                        $client = $client['Empresa'];                        
                        if($last_distributor!=$dist['name'] && !is_null($type_manager)){
                            $filter_list .= '<li title="' . $dist['name'] . '" id="dist-' . $dist['id'] . '" class="caption-item icon company">' . $dist['name'] . '</li>';
                        }
                        $filter_list .= '<li title="' . $client['name'] . '" id="' . $client['id'] . '" class="active" rel="dist-'.$dist['id'].'">' . $client['name'] . '</li>';                        
                        $last_distributor = $dist['name'];
                    }
                }
                $filter_list .= '</ul>';
                echo $filter_list;
                ?>
            </div>
        </div>
    </div>
    <div class="dialog-buttons">  
        <section>
            <button type="button" id="set_filters" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Filtrar', true); ?></button>            
        </section>
    </div> 
</form>
