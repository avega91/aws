<?php

$text_editar = __('Editar', true);
$text_nuevo = __('Nuevo', true);
$companies_corporates = '<option value=""></option>';

if($type_required!=UsuariosEmpresa::IS_ADMIN){
   // $companies_corporates .=  '<option value="new" rel="' . $text_editar . '">' . $text_nuevo . '</option>';
}
$corporativos = array();
$i = 0;
foreach ($companies AS $company) {
    $empresa = $company['Empresa'];
    $corporativo = $company['Corporativo'];
    $id_corporate = '';
    if (!is_null($corporativo['name'])) {        
        $id_corporate = $corporativo['id'];
        $corporativos[$corporativo['id']] = $corporativo['name'];
    }
    $name_company = $type_required==UsuariosEmpresa::IS_ADMIN ? "ContiTech" : utf8_encode($empresa['name']);
    $companies_corporates .= '<option value="' . $empresa['id'] . '" rel="' . $empresa['id'] . '" alt="' . $id_corporate . '" region-assoc="'.$empresa['region'].'" assoc-d="'.$empresa['parent'].'">' . $name_company . '</option>';
}
$companies_corporates .= '|';

$companies_corporates .= '<option value=""></option>';
if($credentials["role"]==UsuariosEmpresa::IS_MASTER){
    $companies_corporates .= '<option value="new" rel="' . $text_editar . '">' . $text_nuevo . '</option>';
}
if(!empty($corporates)){
    foreach ($corporates AS $corporate){
        $corporate = $corporate['Corporativo'];
        $corporativo_name = $corporate['name'];
        $companies_corporates .= '<option value="' . $corporate['id'] . '" rel="' . $corporate['id'] . '" data-region="'.$corporate['region'].'">' . $corporativo_name . '</option>';
    }
}
/*foreach ($corporativos AS $corporativo_id => $corporativo_name) {
    $corporativo_name = utf8_encode($corporativo_name);
    $companies_corporates .= '<option value="' . $corporativo_id . '" rel="' . $corporativo_id . '">' . $corporativo_name . '</option>';
}*/
echo $companies_corporates;