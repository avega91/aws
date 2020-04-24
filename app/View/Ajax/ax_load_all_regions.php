<?php
echo '<option rel="0" value="" alt=""></option>';
if($regions){
    foreach ($regions AS $region){
        $region = $region['Region'];
        echo '<option value="'.$region['short_identificator'].'" data-zone="'.$region['zone'].'">'.utf8_encode(__($region['name'], true)).'</option>';
    }
}