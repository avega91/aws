<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Empresa.php
 *     Model for empresas table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class Empresa extends AppModel {

    public $name = 'Empresa';
    public $useTable = 'empresas';


    /*
        //in belongs to the fk field is in the same table
        public $belongsTo = array(
            'SalespersonClient' => array( //if company is client, can get his salesperson
                'className' => 'UsuariosEmpresa',
                'foreignKey' => 'salesperson_user_id',
                'conditions' => ['not' => ['salesperson_user_id' => null]]
            ),
        );*/


    public $belongsTo = array(
        'CountryCompany' => array( //if company is client, can get his salesperson
            'className' => 'ICountry',
            'foreignKey' => 'i_country_id',
        ),
    );


    public $hasMany = array(
        'SalespersonShares' => array( //get relations in salesperson_companies
            'className' => 'SalespersonCompany',
            'foreignKey' => 'company_id'
        ),
        'SalespersonsTerritory' => array( //if company is admin, can get his salesperson colaborators
            'className' => 'UsuariosEmpresa',
            'conditions' => array('SalespersonsTerritory.deleted'=>0,'SalespersonsTerritory.puesto'=>7, 'SalespersonsTerritory.i_group_id'=>IGroup::TERRITORY_MANAGER),
            'foreignKey' => 'id_empresa'
        ),
        /*'Colaborators' => [
            'className' => 'UsuariosEmpresa',
            'conditions' => array('Colaborators.deleted'=>0),
            'foreignKey' => 'id_empresa'
        ],*/
        'Areas' => array(
            'className' => 'CompanyArea',
            'foreignKey' => 'company_id'
        ),
        'Subareas' => array(
            'className' => 'CompanySubarea',
            'foreignKey' => 'company_id'
        )
    );


    /**
     * Function get collection of Company and Associated Corporate according to id param 
     * @param string $id row id
     * @return array
     */
    public function findByIdWithCorporate($id, $corporate = 0) {
        $id = Sanitize::escape($id);
        $corporate = Sanitize::escape($corporate);
        
        $corporate_filter = $corporate > 0 ? "AND Empresa.id_corporativo = '$corporate'":"";
        // AND Empresa.active='1'
        $query = " SELECT Empresa.* , Corporativo.*, Distribuidor.name
                   FROM empresas AS Empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                   WHERE Empresa.id='$id' $corporate_filter AND Empresa.deleted='0'
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result[0] : array();
    }
    
    /**
     * Function get collection of Company and Associated Corporate according to type param 
     * @param string $type type company (client, distributor, admin, master)
     * @return array
     */
    public function findByTypeWithCorporate($type) {
        $type = Sanitize::escape($type);
        $query = " SELECT * 
                   FROM empresas AS Empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   WHERE Empresa.type='$type' AND Empresa.deleted='0' AND Empresa.active='1'
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    /**
     * Function get collection of Company and Associated Corporate according to type param and region
     * @param string $type type company (client, distributor, admin, master)
     * @param string $region region to filter
     * @return array
     */
    public function findByRegionAndTypeWithCorporate($type, $region = '', $corporativo = 0) {
        $type = Sanitize::escape($type);
        $region = Sanitize::escape($region);
        
        //$region_filter = $region!='' ? "AND Empresa.region = '$region'":"";
        $region_filter = "";
        if($region!=""){
            $region_received = explode(",", $region);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filter = "AND Empresa.region IN ($region_separated)";
        }

        $corporativo = $corporativo != 0 ? "AND Empresa.id_corporativo = '$corporativo'":"";
        $brand = '';
        if($type==UsuariosEmpresa::IS_ADMIN){
            $region_filter = '';
            $corporativo = '';
            $brand = "AND (Empresa.brand = 'ContiHeritage' OR Empresa.brand = 'ContiSelect')";
        }
        $query = " SELECT * 
                   FROM empresas AS Empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   WHERE Empresa.type='$type' $region_filter $corporativo $brand AND Empresa.deleted='0'
                   ORDER BY Empresa.name ASC
                ";
        //AND Empresa.active='1'
        //var_dump($query);
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }


    /**
     * Function get collection of Company and Associated Corporate according to type param and region
     * @param string $type type company (client, distributor, admin, master)
     * @param string $region region to filter
     * @return array
     */
    public function findByIdsWithCorporate($ids) {
        $ids = Sanitize::escape($ids);


        $query = " SELECT * 
                   FROM empresas AS Empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   WHERE Empresa.id IN ($ids)
                   ORDER BY Empresa.name ASC
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findByRegionAndTypeWithCorporateForNotifications($type, $region = '', $corporativo = 0) {
        $type = Sanitize::escape($type);
        $region = Sanitize::escape($region);

        //$region_filter = $region!='' ? "AND Empresa.region = '$region'":"";
        $region_filter = "";
        if($region!=""){
            $region_received = explode(",", $region);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filter = "AND Empresa.region IN ($region_separated)";
        }

        $corporativo = $corporativo != 0 ? "AND Empresa.id_corporativo = '$corporativo'":"";
        $brand = '';

        $query = " SELECT *
                   FROM empresas AS Empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   WHERE Empresa.type='$type' $region_filter $corporativo $brand AND Empresa.deleted='0'
                   ORDER BY Empresa.name ASC
                ";
        //AND Empresa.active='1'

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    /**
     * Function get collection of Company and Associated Team according to type param 
     * @param string $type type company (client, distributor, admin, master)
     * @param string $company query specific name company
     * @param string $sort field sort 
     * @param string $regionFilter region to filter query
     * @return array
     */
    public function findByTypeWithTeamAndClients($type, $company = '', $sort = '', $regionFilter = '', $parent = '', $region_id = 0, $country_id = 0) {
        $corp_manager = Configure::read('manager_corporate');
        $type_manager = Configure::read('type_manager');
                
        $type = Sanitize::escape($type);
        $company = Sanitize::escape($company);
        $sort = Sanitize::escape($sort);
        //$query = "SET GLOBAL group_concat_max_len=102400";
        //$this->query($query);
        $sort = $sort=='' ? 'name' : $sort;

        $region_filtered = "";
        if($regionFilter!=""){
            $region_received = explode(",", $regionFilter);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filtered = "AND Empresa.region IN ($region_separated)";
        }
        
        $filter_distributor = $parent != '' ? "AND Empresa.parent IN ($parent)" : '';
        
        $filter_corp = "";
        if(!is_null($corp_manager) && !is_null($type_manager) && $type_manager==UsuariosEmpresa::IS_CLIENT){
            $filter_corp = "AND Empresa.id_corporativo = '$corp_manager'";
        }


        $region_client_filter = $region_id > 0 && $type=='distributor' ? "AND Cliente.region_id IN ($region_id)":"";
        $region_filter_id = $region_id > 0 ? "AND Empresa.region_id IN ($region_id)":"";
        $country_filter_id = $country_id > 0 ? "AND Empresa.i_country_id IN ($country_id)":"";
        
        $this->query("set group_concat_max_len = 10000000");
        $query = "
                SELECT Empresa.*, Distribuidor.id, Distribuidor.name, Corporativo.*, GROUP_CONCAT(distinct Usuario.id) AS user_ids, 
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.name) AS user_names, GROUP_CONCAT(distinct Usuario.id,'_',Usuario.path_image) AS user_images,
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.active) AS user_estatus,
                GROUP_CONCAT(distinct Cliente.id) AS client_ids, GROUP_CONCAT(distinct Cliente.id,'_',Cliente.name SEPARATOR '||') AS client_names
                FROM empresas AS Empresa
                LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                LEFT JOIN usuarios_empresas AS Usuario ON Usuario.id_empresa = Empresa.id AND Usuario.deleted = 0
                LEFT JOIN empresas AS Cliente ON Cliente.parent = Empresa.id AND Cliente.deleted = 0 $region_client_filter
                LEFT JOIN empresas AS Distribuidor ON Distribuidor.id = Empresa.parent AND Distribuidor.deleted = 0
                WHERE Empresa.type='$type' AND Empresa.name LIKE '%$company%' AND Empresa.deleted = 0 
                     $filter_distributor $region_filtered $filter_corp $region_filter_id $country_filter_id
                GROUP BY Empresa.id
                ORDER BY Empresa.$sort ASC
                ";
        //mail("elalbertgd@gmail.com","test",print_r($query,true));
        //$filter_distributor AND Empresa.region LIKE '%$regionFilter%' $filter_corp
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findByTypeWithTeamAndClientsForSalesperson($type, $company = '', $sort = '', $regionFilter = '', $parent = '') {
        $corp_manager = Configure::read('manager_corporate');
        $type_manager = Configure::read('type_manager');

        $type = Sanitize::escape($type);
        $company = Sanitize::escape($company);
        $sort = Sanitize::escape($sort);
        //$query = "SET GLOBAL group_concat_max_len=102400";
        //$this->query($query);
        $sort = $sort=='' ? 'name' : $sort;

        $region_filtered = "";
        if($regionFilter!=""){
            $region_received = explode(",", $regionFilter);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filtered = "AND Empresa.region IN ($region_separated)";
        }

        $filter_distributor = $parent != '' ? "AND Empresa.parent IN ($parent)" : '';

        $filter_corp = "";
        if(!is_null($corp_manager) && !is_null($type_manager) && $type_manager==UsuariosEmpresa::IS_CLIENT){
            $filter_corp = "AND Empresa.id_corporativo = '$corp_manager'";
        }

        $this->query("set group_concat_max_len = 10000000");
        $query = "
                SELECT Empresa.*, Distribuidor.id, Distribuidor.name, Corporativo.*, GROUP_CONCAT(distinct Usuario.id) AS user_ids, 
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.name) AS user_names, GROUP_CONCAT(distinct Usuario.id,'_',Usuario.path_image) AS user_images,
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.active) AS user_estatus,
                GROUP_CONCAT(distinct Cliente.id) AS client_ids, GROUP_CONCAT(distinct Cliente.id,'_',Cliente.name SEPARATOR '||') AS client_names
                FROM empresas AS Empresa
                LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                LEFT JOIN usuarios_empresas AS Usuario ON Usuario.id_empresa = Empresa.id AND Usuario.deleted = 0
                LEFT JOIN empresas AS Cliente ON Cliente.parent = Empresa.id AND Cliente.deleted = 0
                LEFT JOIN empresas AS Distribuidor ON Distribuidor.id = Empresa.parent AND Distribuidor.deleted = 0
                LEFT JOIN usuarios_empresas AS SalespersonDist On Empresa.salesperson_user_id = SalespersonDist.id
                LEFT JOIN usuarios_empresas AS SalespersonClient On Cliente.salesperson_user_id = SalespersonClient.id
                WHERE Empresa.type='$type' AND Empresa.name LIKE '%$company%' AND Empresa.deleted = 0 
                     $filter_distributor $region_filtered $filter_corp AND (SalespersonDist.id IS NULL OR SalespersonDist.region=Empresa.region) AND (SalespersonClient.id IS NULL OR SalespersonClient.region=Cliente.region)
                GROUP BY Empresa.id
                ORDER BY Empresa.$sort ASC
                ";
        //$filter_distributor AND Empresa.region LIKE '%$regionFilter%' $filter_corp
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findByBucketTypeWithTeamAndClients($type, $company = '', $sort = '') {

        $type = Sanitize::escape($type);
        $company = Sanitize::escape($company);
        $sort = Sanitize::escape($sort);

        $sort = $sort=='' ? 'name' : $sort;



        $this->query("set group_concat_max_len = 10000000");
        $query = "
                SELECT Empresa.*, Distribuidor.id, Distribuidor.name, Corporativo.*, GROUP_CONCAT(distinct Usuario.id) AS user_ids, 
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.name) AS user_names, GROUP_CONCAT(distinct Usuario.id,'_',Usuario.path_image) AS user_images,
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.active) AS user_estatus,
                GROUP_CONCAT(distinct Cliente.id) AS client_ids, GROUP_CONCAT(distinct Cliente.id,'_',Cliente.name SEPARATOR '||') AS client_names
                FROM empresas AS Empresa
                LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                LEFT JOIN usuarios_empresas AS Usuario ON Usuario.id_empresa = Empresa.id AND Usuario.deleted = 0
                LEFT JOIN empresas AS Cliente ON Cliente.parent = Empresa.id AND Cliente.deleted = 0
                LEFT JOIN empresas AS Distribuidor ON Distribuidor.id = Empresa.parent AND Distribuidor.deleted = 0
                WHERE Empresa.type='$type' AND Empresa.name LIKE '%$company%' AND Empresa.deleted = 0
                GROUP BY Empresa.id
                ORDER BY Empresa.$sort ASC
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();

    }

    /**
     * Function to get shared distributors and clients for one salesperson
     * @param $type
     * @param string $company
     * @param string $sort
     * @param string $regionFilter
     * @param string $parent
     * @return array|mixed
     */
    public function findSharedDistributorsWithClientsByIds($distributorIds, $clientIds = "") {
        $distributorIds = Sanitize::escape($distributorIds);
        $clientIds = Sanitize::escape($clientIds);


        //if no clients, ids = 0
        $clientIds = $clientIds==="" ? "AND Cliente.id IN (0)" : "AND Cliente.id IN ($clientIds)";

        $this->query("set group_concat_max_len = 10000000");
        $query = "
                SELECT Empresa.*, Distribuidor.id, Distribuidor.name, Corporativo.*, GROUP_CONCAT(distinct Usuario.id) AS user_ids, 
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.name) AS user_names, GROUP_CONCAT(distinct Usuario.id,'_',Usuario.path_image) AS user_images,
                GROUP_CONCAT(distinct Usuario.id,'_',Usuario.active) AS user_estatus,
                GROUP_CONCAT(distinct Cliente.id) AS client_ids, GROUP_CONCAT(distinct Cliente.id,'_',Cliente.name SEPARATOR '||') AS client_names
                FROM empresas AS Empresa
                LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                LEFT JOIN usuarios_empresas AS Usuario ON Usuario.id_empresa = Empresa.id AND Usuario.deleted = 0
                LEFT JOIN empresas AS Cliente ON Cliente.parent = Empresa.id AND Cliente.deleted = 0 $clientIds
                LEFT JOIN empresas AS Distribuidor ON Distribuidor.id = Empresa.parent AND Distribuidor.deleted = 0
                WHERE Empresa.id IN($distributorIds) AND Empresa.deleted = 0 
                GROUP BY Empresa.id
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    
    
    /**
     * Function get collection of Companies for clients and Associated Team according to ids param 
     * @param string $ids_companies type company (client, distributor, admin, master)
     * @param string $company query specific name company
     * @param string $sort field sort 
     * @return array
     */
    public function findClientCompaniesByIdsWithTeam($ids_companies, $company = '', $sort = '') {
        $ids_companies = Sanitize::escape($ids_companies);
        $company = Sanitize::escape($company);
        $sort = Sanitize::escape($sort);
        //$query = "SET GLOBAL group_concat_max_len=102400";
        //$this->query($query);
        $sort = $sort=='' ? 'name' : $sort;
        $sort = $sort=="name" ? "Empresa.$sort ASC": "Empresa.id DESC, Empresa.$sort DESC";
        
        $this->query("set group_concat_max_len = 10000000");
        
        $query = "
                SELECT Empresa.*, Corporativo.*, GROUP_CONCAT(Usuario.id) AS user_ids, 
                GROUP_CONCAT(Usuario.name) AS user_names, GROUP_CONCAT(Usuario.path_image) AS user_images,
                GROUP_CONCAT(Usuario.active) AS user_estatus
                FROM empresas AS Empresa
                LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                LEFT JOIN usuarios_empresas AS Usuario ON Usuario.id_empresa = Empresa.id AND Usuario.deleted = 0
                WHERE Empresa.id IN($ids_companies) AND Empresa.name LIKE '%$company%'
                GROUP BY Empresa.id
                ORDER BY $sort
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    
    /**
     * @DEPRECATED
     * Function get collection of Company and Associated Corporate according to id param 
     * @param string $type type filter (client, distributor, admin)
     * @param string $region region filter
     * @return array
     */
    public function findByTypeAndRegionWithCorporate_DEPRECATED($type, $region) { 
        $type = Sanitize::escape($type);
        $region = Sanitize::escape($region);


        $query = " SELECT Empresa.* , Corporativo.*, Distribuidor.name
                   FROM empresas AS Empresa
                   INNER JOIN usuarios_empresas AS UsuarioEmpresa ON Empresa.id = UsuarioEmpresa.id_empresa
                   LEFT JOIN corporativos AS Corporativo ON Empresa.id_corporativo = Corporativo.id
                   LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                   WHERE Empresa.type='$type' AND Empresa.deleted='0' AND Empresa.active='1'
                        AND UsuarioEmpresa.region = '$region'
                   GROUP BY Empresa.id
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    /**
     * Function get collection of Company ids corresponding to region and type
     * @param string $type type filter (client, distributor, admin)
     * @param string $region region filter
     * @return array
     */
    public function getClientCompanyIdsByTypeAndRegion($type, $region='', $parent = '', $region_id = 0, $country_id = 0) {
        $type = Sanitize::escape($type);
        $region = Sanitize::escape($region);
        $parent = Sanitize::escape($parent);

        //AND Empresa.active='1'
        $region_filter = "";
        if($region!=""){
            $region_received = explode(",", $region);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filter = "AND Empresa.region IN ($region_separated)";
        }

        $parent_filter = $parent != '' ? " AND Empresa.parent IN ($parent)":'';

        $region_filter_id = $region_id > 0 ? "AND Empresa.region_id IN ($region_id)":"";
        $country_filter_id = $country_id > 0 ? "AND Empresa.i_country_id IN ($country_id)":"";

        $this->query("set group_concat_max_len = 10000000");
        
        $query = " SELECT group_concat(distinct Empresa.id) AS companies
                   FROM empresas AS Empresa
                   WHERE Empresa.type='$type' AND Empresa.deleted='0'
                        $region_filter $parent_filter $region_filter_id $country_filter_id
                ";


        $result = $this->query($query);

        return count($result) > 0 ? $result[0][0]['companies'] : array();
    }

    public function getClientCompanyIdsByTypeAndRegionForSalesperson($type, $region='', $parent = '') {
        $type = Sanitize::escape($type);
        $region = Sanitize::escape($region);
        $parent = Sanitize::escape($parent);

        //AND Empresa.active='1'
        $region_filter = "";
        if($region!=""){
            $region_received = explode(",", $region);//separate items
            $region_separated = "'".implode("','", $region_received)."'";

            //$region_filter = $region!='' ? "AND Empresa.region = '$region'" : '';
            $region_filter = "AND Empresa.region IN ($region_separated)";
        }

        $parent_filter = $parent != '' ? " AND Empresa.parent IN ($parent)":'';

        $this->query("set group_concat_max_len = 10000000");

        $query = " SELECT group_concat(distinct Empresa.id) AS companies
                   FROM empresas AS Empresa
                   LEFT JOIN usuarios_empresas AS Salesperson On Empresa.salesperson_user_id = Salesperson.id
                   WHERE Empresa.type='$type' AND Empresa.deleted='0'
                        $region_filter $parent_filter AND (Salesperson.id IS NULL OR Salesperson.region=Empresa.region)
                ";

        // AND (Salesperson.id IS NULL OR Salesperson.region=Empresa.region) is for get on

        $result = $this->query($query);

        return count($result) > 0 ? $result[0][0]['companies'] : array();
    }
    
    

}
