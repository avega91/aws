<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file UsuariosEmpresa.php
 *     Model for usuarios_empresas table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class UsuariosEmpresa extends AppModel {

    public $name = 'UsuariosEmpresa';

    const IS_MASTER = 'master';
    const IS_ADMIN = 'admin';
    const IS_MANAGER = 'manager';
    const IS_DIST = 'distributor';
    const IS_CLIENT = 'client';
    const IS_UNLOCKED = 'UNLOCKED';
    const IS_TEMPORARY_LOCKED = 'TEMPORARY';
    const IS_PERMANENTLY_LOCKED = 'PERMANENTLY';
    const IS_MISSED_GEO = 'MISSED_GEO';

    const IS_SALESPERSON = 7;

    public $belongsTo = array(
        /*'GroupRow' => array(
            'className' => 'IGroup',
            'foreignKey' => 'i_group_id'
        ),*/
        'CompanyUser' => [
            'className' => 'Empresa',
            'foreignKey' => 'id_empresa'
        ],
    );


    public $hasMany = array(
        'SharedDistributors' => array( //return salesperson_company_relation rows
            'className' => 'SalespersonCompany',
            'foreignKey' => 'user_sp_id',
        ),
        'SharedClients' => array( //return company rows shared with current usuario_empresa
            'className' => 'Empresa',
            'foreignKey' => 'salesperson_user_id',
            'conditions' => ['SharedClients.type'=>'client']
        ),
        'Statistics' => array( //return salesperson_company_relation rows
            'className' => 'Statistic',
            'foreignKey' => 'user_id',
            'conditions' => ['Statistics.is_retroactive' => 0]
        ),
    );

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
        $query = " SELECT UsuariosEmpresa.*,Empresa.name AS name_company, Empresa.id_corporativo, Empresa.type AS role_company
                   FROM usuarios_empresas AS UsuariosEmpresa
                   INNER JOIN empresas AS Empresa ON UsuariosEmpresa.id_empresa = Empresa.id
                   WHERE UsuariosEmpresa.username = '$user' AND UsuariosEmpresa.password = '$password' AND
                         UsuariosEmpresa.active = '1' AND UsuariosEmpresa.deleted = '0' AND UsuariosEmpresa.lock_status = '" . self::IS_UNLOCKED . "'
                         AND UsuariosEmpresa.logged_in = '0'
                         AND Empresa.deleted='0' AND Empresa.active='1'
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    /**
     * find user row by id
     * @param int $id
     * @return array
     */
    public function findFullInfoById($id) {
        $id = Sanitize::escape($id);
        $query = " SELECT UsuariosEmpresa.*,Empresa.parent, Empresa.name AS name_company, Empresa.id_corporativo, Empresa.type AS role_company, 
                  Empresa.salesperson_user_id AS salesperson,Empresa.i_market_id AS company_market_id, Empresa.i_country_id AS company_country_id,
                  Empresa.region_id AS company_region_id, Empresa.territory_id AS company_territory_id,Empresa.region AS company_region, Empresa.i_market_id AS market_id
                   FROM usuarios_empresas AS UsuariosEmpresa
                   INNER JOIN empresas AS Empresa ON UsuariosEmpresa.id_empresa = Empresa.id
                   WHERE UsuariosEmpresa.id = '$id'
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    /**
     * Obtiene una coleccion de todos los usuarios de tipo distribuidor
     * @return mixed array|bool
     */
    public function findDistributors($region = '') {
        $region = Sanitize::escape($region);
        $region = $region != '' ? " AND (region = '$region')" : "";

        $query = "
            SELECT *
            FROM " . _MODEL_AUTH_BD . " AS Usuario
            INNER JOIN empresas AS Empresa ON Usuario.id_empresa = Empresa.id
            WHERE Usuario.group_id = '40' AND Usuario.role = 'distributor' AND Usuario.deleted='0' AND Usuario.active='1' $region
            ORDER BY Usuario.id DESC
            ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : false;
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
     * @param string $region user region
     * @param string $pais user country
     * @param string $estado user state
     * @param int $active flag to control user status
     * @param enum $aprobado indicates if user is approved or not
     * @return bool
     */
    public function add($username, $password, $id_empresa, $nombre, $email, $telefono, $puesto, $no_empleado_a, $unidad_negocio_a, $zona_d, $id_atiende_d, $area_c, $id_tipoindustria_c, $imagen_perfil, $role, $grupo, $region, $pais, $estado, $active = 0, $aprobado = 'NO') {

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
        $region = Sanitize::escape($region);
        $pais = Sanitize::escape($pais);
        $estado = Sanitize::escape($estado);
        $active = Sanitize::escape($active);
        $aprobado = Sanitize::escape($aprobado);

        $query = "
            INSERT INTO usuarios_empresas (username, password, id_empresa, name, email, phone, puesto, 
                                       no_empleado_a, unidad_negocio_a, zona_d, id_atiende_d, area_c, id_tipoindustria_c,
                                       path_image, role, group_id, region, country, state, active, aprobado)
            VALUES ('$username','$password', '$id_empresa','$nombre','$email','$telefono',
                    '$puesto','$no_empleado_a','$unidad_negocio_a','$zona_d','$id_atiende_d','$area_c','$id_tipoindustria_c',
                    '$imagen_perfil','$role','$grupo','$region','$pais','$estado','$active','$aprobado')
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
    public function updateImageProfile($user_id, $path_image) {
        $user_id = Sanitize::escape($user_id);
        $path_image = Sanitize::escape($path_image);

        $query = "UPDATE usuarios_empresas SET path_image='$path_image' WHERE id='$user_id'";
        $this->query($query);
        return $this->getAffectedRows() > 0 ? true : false;
    }

    /**
     * Get full info (user + company) for an user id
     * @param int $user_id user id
     * @return array
     */
    public function findUserInfo($user_id) {
        $user_id = Sanitize::escape($user_id);

        $query = "SELECT * "
                . "FROM usuarios_empresas AS Usuario "
                . "INNER JOIN empresas AS Empresa ON Usuario.id_empresa = Empresa.id "
                . "LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id "
                . "LEFT JOIN regions AS Region ON Usuario.region = Region.short_identificator "
                . "LEFT JOIN atencion_personas AS AtencionPersona ON Usuario.id_atiende_d = AtencionPersona.id "
                . "LEFT JOIN tipo_industrias AS TipoIndustria ON Usuario.id_tipoindustria_c = TipoIndustria.id "
                . "WHERE Usuario.id = '$user_id'";

        $result = $this->query($query);
        return count($result) > 0 ? $result[0] : array();
    }

    /**
     * Get all user filtered by type
     * @param array $types array of types
     * @return array
     */
    public function getTotalUsersByType($types) {
        $types = implode("','", $types);
        $query = "
                SELECT UsuariosEmpresa.* 
                FROM usuarios_empresas AS UsuariosEmpresa
                INNER JOIN empresas AS Empresa ON UsuariosEmpresa.id_empresa = Empresa.id AND Empresa.deleted = 0
                WHERE UsuariosEmpresa.deleted = 0 AND UsuariosEmpresa.role IN ('$types')
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

}
