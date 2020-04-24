<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file User.php
 *     Model for usuarios table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class User extends AppModel {
    public $name = 'User';
    public $useTable = false;

    /**
     * Verifica si un usuario esta autentificado
     * @param int $user id de usuario
     * @param string $password password
     * @return mixed
     */
    public function isAuth($user, $password) {
        $encriptacion = _TYPECRYPT;
        $user = Sanitize::escape($user);
        $password = $encriptacion(Sanitize::escape($password));
        $sql = '
                SELECT * FROM ' . _MODEL_AUTH_BD . ' 
                WHERE ' . _NAME_FIELD_MODEL . ' = \'' . $user . '\' AND  ' . _PASSWORD_FIELD_MODEL . ' = \'' . $password . '\' ';
        $resUser = $this->query($sql);
        return $this->getNumRows() > 0 ? $resUser : null;
    }

    

    /**
     * Obtiene un usuario segun su id
     * @param int $user_id el id del usuario a obtener
     * @return mixed
     */
    public function getUserById($user_id){
        $user_id = Sanitize::escape($user_id);
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE id = '$user_id'
            ";
         $result = $this->query($query);
         return count($result)>0 ? $result[0]['user'] : false;
    }

    /**
     * Obtiene un usuario segun su id
     * @param int $user_id el id del usuario a obtener
     * @return mixed
     */
    public function getUserByIdFull($user_id){
        $user_id = Sanitize::escape($user_id);
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE id = '$user_id'
            ";
         $result = $this->query($query);
         return count($result)>0 ? $result : false;
    }

    

    /**
     * Obtiene un usuario segun su username
     * @param string $username el username del usuari
     * @return mixed
     */
    public function getUserByUsername($username){
        $username = Sanitize::escape($username);
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE username = '$username'
            ";
         $result = $this->query($query);
         return count($result)>0 ? $result[0]['user'] : false;
    }

    /**
     * Obtiene un usuario segun su id
     * @param int $user_id el id del usuario a obtener
     * @return mixed
     */
    public function findById($user_id){
        $user_id = Sanitize::escape($user_id);
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE id = '$user_id'
            ";
         $result = $this->query($query);
         return count($result)>0 ? $result[0]['user'] : false;
    }
    
    /**
     * Obtiene una coleccion de todos los usuarios de tipo cliente
     * @return mixed array|bool
     */
    public function findClients(){
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE group_id = '20' AND role = 'client' AND deleted='0' AND active='1'
            ORDER BY id DESC
            ";
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * Obtiene una coleccion de todos los usuarios de tipo distribuidor
     * @return mixed array|bool
     */
    public function findDistributors($region = ''){
        $region = Sanitize::escape($region);
        $region = $region!='' ? " AND (region = '$region')":"";
        
        $query = "
            SELECT *
            FROM "._MODEL_AUTH_BD." AS user
            WHERE group_id = '40' AND role = 'distributor' AND deleted='0' AND active='1' $region
            ORDER BY id DESC
            ";
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * function for add a new user row in database
     * @param string $username the username for login
     * @param string $password the password for login     
     * @param int $id_empresa associated enterprise for user
     * @param string $nombre full name for user
     * @param string $email email account of user
     * @param string $telefono phone number of user
     * @param string $puesto user position in the enterprise
     * @param string $no_empleado_a employee id in the enterprise for admin user
     * @param string $unidad_negocio_a business unit for admin user
     * @param string $zona_d zone for distributor user
     * @param string $id_atiende_d id empleyee id that attends to distributor user
     * @param string $area_c area for client user
     * @param int $id_tipoindustria_c industry id of the client user 
     * @param string $imagen_perfil path for image profile of the user
     * @param string $role role for new user
     * @param int $grupo group id for new user
     * @param int $parent user associated user for client user
     * @param string $region user region
     * @param string $pais user country
     * @param string $estado user state
     * @param int $active flag to control user status
     * @param enum $aprobado indicates if user is approved or not
     * @return bool
     */
    public function add($username, $password, $id_empresa, $nombre, $email, $telefono, 
            $puesto, $no_empleado_a, $unidad_negocio_a, $zona_d, $id_atiende_d, $area_c, $id_tipoindustria_c,
            $imagen_perfil, $role, $grupo, $parent, $region, $pais, $estado, $active = 0, $aprobado = 'NO'){
        
        $username = Sanitize::escape($username);
        $password = Sanitize::escape($password);                
        $id_empresa = Sanitize::escape($id_empresa);
        $nombre = Sanitize::escape($nombre);
        $email = Sanitize::escape($email);
        $telefono = Sanitize::escape($telefono);
        $puesto = Sanitize::escape($puesto);
        $no_empleado_a = Sanitize::escape($no_empleado_a);
        $unidad_negocio_a = Sanitize::escape($unidad_negocio_a);
        $zona_d = Sanitize::escape($zona_d);
        $id_atiende_d = Sanitize::escape($id_atiende_d);
        $area_c = Sanitize::escape($area_c);
        $id_tipoindustria_c = Sanitize::escape($id_tipoindustria_c);
        $imagen_perfil = Sanitize::escape($imagen_perfil);
        $role = Sanitize::escape($role);
        $grupo = Sanitize::escape($grupo);
        $parent = Sanitize::escape($parent);
        $region = Sanitize::escape($region);
        $pais = Sanitize::escape($pais);
        $estado = Sanitize::escape($estado);
        $active = Sanitize::escape($active);
        $aprobado = Sanitize::escape($aprobado);
        
        $query = "
            INSERT INTO users_empresas (username, password, id_empresa, name, email, phone, puesto, 
                                       no_empleado_a, unidad_negocio_a, zona_d, id_atiende_d, area_c, id_tipoindustria_c,
                                       path_image, role, group_id, parent, region, country, state, active, aprobado)
            VALUES ('$username','$password', '$id_empresa','$nombre','$email','$telefono',
                    '$puesto','$no_empleado_a','$unidad_negocio_a','$zona_d','$id_atiende_d','$area_c','$id_tipoindustria_c',
                    '$imagen_perfil','$role','$grupo','$parent','$region','$pais','$estado','$active','$aprobado')
            ";
        
        $this->query($query);
        return $this->getAffectedRows() > 0 ? $this->getLastInsertID() : 0;
    }
    
    /**
     * Update field path image for user id
     * @param int $user_id the user id
     * @param string $path_image path to image
     * @return bool
     */
    public function updateImageProfile($user_id, $path_image){
        $user_id = Sanitize::escape($user_id);
        $path_image = Sanitize::escape($path_image);
        
        $query = "UPDATE users_empresas SET path_image='$path_image' WHERE id='$user_id'";
        $this->query($query);
        return $this->getAffectedRows() > 0 ? true : false;
    }

}