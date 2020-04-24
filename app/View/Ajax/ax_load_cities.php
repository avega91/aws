<?php
echo '<option value=""></option>';
if($cities){
    foreach ($cities AS $city){
        $city = $city['City'];
        echo '<option value="'.utf8_encode($city['name']).'">'.utf8_encode($city['name']).'</option>';
    }
}