<h1>Usuarios en el sistema:</h1>
<?php
if(!empty($users)){
    foreach($users AS $user){
        $user = $user['User'];
        echo "<h2>$user[name] $user[lastname]</h2>";
    }
}else{
    echo 'No hay datos para mostrar';
}