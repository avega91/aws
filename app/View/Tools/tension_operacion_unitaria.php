<form class="calculador-form" id='tension_op_unitaria_form'>
    <h1><?php echo __('Tension de operacion unitaria',true); ?></h1>
    <div id="result_calc" class="form-row">0.00</div>
    <div class="form-row">
        <label for="potencia_motor"><?php echo __('Potencia de motor (HP)',true); ?></label>
        <input type='text' id='potencia_motor' class="validate[required,custom[positive_integer],maxSize[4]]"/>
    </div>
    <div class="form-row">
        <label for="ancho_banda"><?php echo __('Ancho de banda (Pulg.)',true); ?></label>
        <input type='text' id='ancho_banda' class="validate[required,custom[positive_integer],maxSize[4]]"/>
    </div>
    <div class="form-row">
        <label for="velocidad_banda"><?php echo __('Velocidad de bandas (pies/min)',true); ?></label>
        <input type='text' id='velocidad_banda' class="validate[required,custom[positive_integer],maxSize[4]]"/>
    </div>
    <div class="form-row">
        <label for="sel_angulo_contacto"><?php echo __('Angulo de contacto',true); ?></label>
        <?php $this->Content->printConfigTranspDropDownCalculator($angulos_contacto, 'sel_angulo_contacto', 'validate[required]', __('Seleccione angulo', true)); ?>
    </div>
    <div class="form-row">
        <label for="sel_tipo_tensor"><?php echo __('Tipo de tensor',true); ?></label>
        <?php $this->Content->printConfigTranspDropDownCalculator($tipos_tensor, 'sel_tipo_tensor', 'validate[required]', __('Seleccione tipo', true)); ?>
    </div>
    <div class="form-row">
        <label for="sel_tipo_polea"><?php echo __('Tipo de polea',true); ?></label>
        <?php $this->Content->printConfigTranspDropDownCalculator($tipos_polea, 'sel_tipo_polea', 'validate[required]', __('Seleccione tipo', true)); ?>
    </div>
    <div class="form-row toolbar-calc">
        <button type='button' id="reset_calc">&nbsp;</button>
        <button type='button' id="process_calc">&nbsp;</button>
    </div>
    <div class="form-row disclaimer-calc">
        <?php echo __('* Calculo metodo corto, para fines informativos, no incluye ni constituye de forma tacita ni expresa ninguna clase de garantia. Si requiere mayor informacion contacte a personal tecnico ',true); ?>
    </div>
</form>