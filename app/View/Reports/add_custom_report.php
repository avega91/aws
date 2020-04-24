<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file add.php
 *     View layer for action add of controller
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
?>
<div id='slide_form' class="iconized">    
    <div data-section="4" data-intro="<?php echo __('tutorial_slide_agregar_reporte',true);?>" data-position="right">
        <a rel="detalle" class="details active" title="<?php echo __('Detalles',true); ?>"></a>
        <a rel="transportador" class="conveyor" title="<?php echo __('Transportador',true); ?>"></a>
        <a rel="tipo_tensor" class="tensor" title="<?php echo __('Tipo de tensor',true); ?>"></a>
        <a rel="material" class="material" title="<?php echo __('Material',true); ?>"></a>
        <a rel="banda_actual" class="beltin" title="<?php echo __('Banda actual',true); ?>"></a>
        <a rel="poleas" class="pulley" title="<?php echo __('Poleas',true); ?>"></a>
        <a rel="rodillos" class="idler" title="<?php echo __('Rodillos',true); ?>"></a>
        <a rel="observaciones" class="remark" title="<?php echo __('Observaciones',true); ?>"></a>
    </div>
</div>
<form id="add_custom_report_form" action="<?php echo $this->Html->url(array('controller'=>'Reports','action'=>'saveCustomReport')); ?>" class="fancy_form">
    <div class='slide-form-section'>        
        <div id="detalle" class="active_section">
            <div id="conveyor_list">
                <h1><?php echo __('Transportadores',true); ?><span class="conveyors-selector unselected"></span></h1>
                <div data-section="4" data-intro="<?php echo __('tutorial_lista_transportadores_agregar_reporte',true);?>" data-position="right"></div>
            </div>
            <div class="space"></div>
            <div class="full-controls" data-section="4" data-intro="<?php echo __('tutorial_titulo_agregar_reporte',true);?>" data-position="left">
                    <input type="text" placeholder="<?php echo __('Titulo de reporte', true); ?>" name="titulo_reporte" id="titulo_reporte" class="validate[required] main-input"/>
            </div>
            <div class="form-data" data-section="4" data-intro="<?php echo __('tutorial_dist_cte_agregar_reporte',true);?>" data-position="left">                
                <div class="full-controls">
                    <?php $this->Content->putDropdownCompanies(__('Distribuidor', true),'distributor',$distribuidores, $is_disabled = false);?>
                </div>
                <div class="full-controls">
                    <?php $this->Content->putDropdownCompanies(__('Cliente', true),'client',array(), $is_disabled = true);?>
                </div>
           </div>
           <div class="form-data">  
                <div class="full-controls actions-in-select" data-section="4" data-intro="<?php echo __('tutorial_plantilla_agregar_reporte',true);?>" data-position="bottom">
                    <?php $this->Content->putDropdownPlantillas(__('Plantilla', true),'templates',$plantillas, $is_disabled = false);?>
                </div>                
            </div>
        </div>
        <div id="transportador" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field"><div class="conveyor-label" assoc-label="Distancia entre centros (m)" rel="trans_distancia_centros"><?php echo __('Distancia entre centros (m)',true); ?></div></div>
                <div class="column-field last"><div class="conveyor-label" assoc-label="RPM motor" rel="trans_rpm_motor"><?php echo __('RPM motor',true); ?></div></div>
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Elevacion (m)" rel="trans_elevacion"><?php echo __('Elevacion (m)',true); ?></div>
                </div>                
                <div class="column-field last"><div class="conveyor-label" assoc-label="Angulo de inclinacion (°)" rel="trans_angulo_inclinacion"><?php echo __('Angulo de inclinación (°)',true); ?></div></div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="HP Motor" rel="trans_hp_motor"><?php echo __('HP Motor',true); ?></div>
                </div>            
                <div class="column-field last"><div class="conveyor-label" assoc-label="Capacidad (t/h)" rel="trans_capacidad"><?php echo __('Capacidad (t/h)',true); ?></div></div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Relacion reductor" rel="trans_relacion_reductor"><?php echo __('Relacion reductor',true); ?></div>
                </div>
                <div class="column-field last"><div class="conveyor-label" assoc-label="Carga (percent)" rel="trans_carga"><?php echo __('Carga (percent)',true); ?></div></div>                
            </div>
        </div>
        <div id="tipo_tensor" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Tipo de tensor" rel="tensor_tipo"><?php echo __('Tipo de tensor',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Carrera (m)" rel="tensor_carrera"><?php echo __('Carrera (m)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Peso estimado (lbs)" rel="tensor_peso_estimado"><?php echo __('Peso estimado (lbs)',true); ?></div>
                </div>                
                <div class="column-field last"></div>                
            </div>
        </div>
        <div id="material" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Descripcion" rel="mat_descripcion"><?php echo __('Descripcion',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Densidad material" rel="mat_densidad"><?php echo __('Densidad material',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Tamano max terron" rel="mat_tam_terron"><?php echo __('Tamano max terron',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Temperatura" rel="mat_temperatura"><?php echo __('Temperatura',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Altura de caida" rel="mat_altura_caida"><?php echo __('Altura de caida',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Finos" rel="mat_porcentaje_finos"><?php echo __('Finos',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Material transportado" rel="mat_grado_mat_transportado"><?php echo __('Material transportado',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Condiciones de alimentacion" rel="mat_condicion_alimentacion"><?php echo __('Condiciones de alimentacion',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Condiciones de carga" rel="mat_condicion_carga"><?php echo __('Condiciones de carga',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Frecuencia de carga" rel="mat_frecuencia_carga"><?php echo __('Frecuencia de carga',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Tamanio granular" rel="mat_tamanio_granular"><?php echo __('Tamanio granular',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Tipo densidad" rel="mat_tipo_densidad"><?php echo __('Tipo densidad',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Agresividad" rel="mat_agresividad"><?php echo __('Agresividad',true); ?></div>
                </div>                
                <div class="column-field last">
                </div>                
            </div>
        </div>
        <div id="banda_actual" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="banda_ancho"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Tension Banda" rel="banda_tension"><?php echo __('Tension Banda',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Espesor cubierta superior" rel="id_espesor_cubierta_sup"><?php echo __('Espesor cubierta superior',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Espesor cubierta inferior" rel="id_espesor_cubierta_inf"><?php echo __('Espesor cubierta inferior',true); ?></div>                    
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Fecha de instalacion" rel="banda_fecha_instalacion"><?php echo __('Fecha de instalacion',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Marca" rel="banda_marca"><?php echo __('Marca',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Desarrollo total" rel="banda_desarrollo_total"><?php echo __('Desarrollo total',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Operacion hrs por anio" rel="banda_operacion"><?php echo __('Operacion hrs por anio',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Velocidad banda" rel="banda_velocidad"><?php echo __('Velocidad banda',true); ?></div>
                </div>                
                <div class="column-field last">
                </div>                
            </div>            
        </div>
        <div id="poleas" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea motriz" rel="polea_motriz"><?php echo __('Polea motriz',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_polea_motriz"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Recubrimiento (plg)" rel="polea_recubrimiento"><?php echo __('Recubrimiento (plg)',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Arco de contacto" rel="polea_arco_contacto"><?php echo __('Arco de contacto',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea de cabeza" rel="polea_cabeza"><?php echo __('Polea de cabeza',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_cabeza"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea de cola" rel="polea_cola"><?php echo __('Polea de cola',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_cola"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea de contacto" rel="polea_contacto"><?php echo __('Polea de contacto',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_contacto"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea de doblez" rel="polea_doblez"><?php echo __('Polea de doblez',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_doblez"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea tensora" rel="polea_tensora"><?php echo __('Polea tensora',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_tensora"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea adicional1" rel="polea_uno_adicional"><?php echo __('Polea adicional1',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_polea_uno_adicional"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Polea adicional2" rel="polea_dos_adicional"><?php echo __('Polea adicional2',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Ancho (plg)" rel="ancho_pol_dos_adicional"><?php echo __('Ancho (plg)',true); ?></div>
                </div>                
            </div>
        </div>
        <div id="rodillos" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Impacto Ø (plg)" rel="rod_diam_impacto"><?php echo __('Impacto Ø (plg)',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Impacto (°)" rel="rod_ang_impacto"><?php echo __('Impacto (°)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Carga Ø (plg)" rel="rod_diam_carga"><?php echo __('Carga Ø (plg)',true); ?></div>
                </div>                                               
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Carga (°)" rel="rod_ang_carga"><?php echo __('Carga (°)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Retorno Ø (plg)" rel="rod_diam_retorno"><?php echo __('Retorno Ø (plg)',true); ?></div>
                </div>                
                 <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Retorno (°)" rel="rod_ang_retorno"><?php echo __('Retorno (°)',true); ?></div>
                </div>                
            </div>
            <div class="conveyor-ctrls no-margin">                
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Espacio(LC) (m)" rel="rod_espacio_ldc"><?php echo __('Espacio(LC) (m)',true); ?></div>
                </div>                
                <div class="column-field last">
                    <div class="conveyor-label" assoc-label="Espacio(LR) (m)'" rel="rod_espacio_ldr"><?php echo __('Espacio(LR) (m)',true); ?></div>
                </div>                
            </div>            
            <div class="conveyor-ctrls no-margin">                
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Partes por artesa" rel="rod_partes_artesa"><?php echo __('Partes por artesa',true); ?></div>
                </div>                
                <div class="column-field last"></div>                
            </div>
        </div>
        <div id="observaciones" class="hidden">
            <div class="slide-form-title"></div>
            <div class="conveyor-ctrls no-margin">     
                <div class="column-field">
                    <div class="conveyor-label" assoc-label="Observaciones" rel="observaciones"><?php echo __('Observaciones',true); ?></div>
                </div>                
                <div class="column-field last"></div>
            </div>            
        </div>
    </div>
    <div id="slide_navigation" class="slide-form-navigation"><a rel="prev"><?php echo __('Anterior',true); ?></a><a rel="next"><?php echo __('Siguiente',true); ?></a></div>
    <div class="dialog-buttons">  
        <section>
            <button type="button" id="save_custom_report" class="progress-button conveyor-btn" data-style="shrink" data-horizontal><?php echo __('Guardar y Cerrar', true); ?></button>
        </section>
    </div> 
</form>