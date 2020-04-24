<?php
$text_editar = __('Editar', true);
$text_nuevo = __('Nuevo', true);
$states_corps = '<option value=""></option>';
if($estados){    
    foreach ($estados AS $estado){
        $estado_pais = $estado['estado_pais'];
        $pais = $estado['pais'];
        $states_corps .= '<option value="'.utf8_encode($estado_pais['asociated_state']).'" state_id="'. $estado_pais['id'] .'" rel="' . $estado_pais['asociated_country_id'] . '" alt="'.utf8_encode(__($pais['name'],true)).'">'.__(utf8_encode($estado_pais['asociated_state']),true).'</option>';
    }
}

$states_corps .= '|';
if(!$isAppAdd){
    $states_corps .= '<option value=""></option>';
    if($credentials["role"]==UsuariosEmpresa::IS_MASTER) {
        $states_corps .= '<option value="new" rel="' . $text_editar . '">' . $text_nuevo . '</option>';
    }
}


$corps = array();
if(!empty($corporativos)){
    foreach ($corporativos AS $corporate){
        $corporate = $corporate['Corporativo'];
        $corporativo_name = $corporate['name'];
        $states_corps .= '<option value="' . $corporate['id'] . '" rel="' . $corporate['id'] . '" data-region="'.$corporate['region'].'">' . $corporativo_name . '</option>';
    }
}
echo $states_corps;