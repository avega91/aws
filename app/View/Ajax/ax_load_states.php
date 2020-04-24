<?php
echo '<option value=""></option>';
if($estados){
    foreach ($estados AS $estado){
        $estado = $estado['estado'];
        echo '<option value="'.utf8_encode($estado['name']).'" state_id="'. $estado['id'] .'">'.utf8_encode($estado['name']).'</option>';
    }
}