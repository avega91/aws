<?php

$available_regions = '';
 if(in_array($role_user_checking, array('admin'))){
     $available_regions .= '<option value=""></option>';
}
$regions_user_array = explode('|',$region_user);
foreach ($regions AS $region){
   $region = $region['Region'];
   //Se ponen solo las regiones que tiene el seleccionado
   if(in_array($role_user_checking, array('admin','distributor'))){        
        if(in_array($region['short_identificator'], $regions_user_array)){
            $available_regions .= '<option value="'.$region['short_identificator'].'">'.utf8_encode($region['name']).'</option>';
        }
   }else{
       $available_regions .= '<option value="'.$region['short_identificator'].'">'.utf8_encode($region['name']).'</option>';
   }
}

if(!empty($salespersons)){
    $available_regions .= '|';
    foreach ($salespersons AS $salesperson){
        $available_regions .= '<option value="'.$salesperson['id'].'">'.utf8_encode($salesperson['name']).'</option>';
    }
}

echo $available_regions;