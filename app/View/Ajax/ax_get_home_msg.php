<div id='slide_form'>    
    <a rel="en_form_section" class="<?php echo $language == 'en' ? 'active' : '' ?>"><?php echo __('Ingles',true); ?></a>
    <a rel="es_form_section" class="<?php echo $language == 'es' ? 'active' : '' ?>"><?php echo __('Espanol',true); ?></a>
</div>
<form id="change_home_msg_form" action="">    
    <div class='slide-form-section'>
        <div id='es_form_section' class='<?php echo $language == 'es' ? 'active_section' : 'hidden'; ?>'>  
            <textarea id="desc_home_msg_es" class="validate[required]"><?php echo $homeMsgEs; ?></textarea>
        </div>
        <div id='en_form_section' class='<?php echo $language == 'en' ? 'active_section' : 'hidden'; ?>'>
            <textarea id="desc_home_msg_en" class="validate[required]"><?php echo $homeMsgEn; ?></textarea>
        </div>        
    </div>

    <div class="dialog-buttons">  
        <section>
            <button type="button" id="save_btn" class="progress-button" data-style="shrink" data-horizontal><?php echo __('Guardar', true); ?></button>            
        </section>
    </div> 
</form>