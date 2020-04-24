<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Conveyor.php
 *     Model for bandas transportadoras table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class UsConveyor extends AppModel {

    public $name = 'UsConveyor';
    public $companies = null;
    public $useTable = false;

    const VERSION_1 = 'V1';
    const VERSION_2 = 'V2';
    const ITEM_IMAGE = 'Image';
    const ITEM_VIDEO = 'Video';
    const ITEM_FOLDER = 'Folder';
    const ITEM_DETAIL = 'ConveyorDetail';

    public function findAllWithCompany($filter_companies, $conveyor = '', $sort = '', $rows = 0, $from = 0) {
        $filter_companies = Sanitize::escape($filter_companies);
        $conveyor = Sanitize::escape($conveyor);
        $sort = Sanitize::escape($sort);
        $sort = $sort == '' || $sort == 'numero' ? 'numero ASC' : $sort;
        $sort = $sort == 'actualizada' ? 'actualizada DESC' : $sort;

        $pagination = $rows>0 ? "LIMIT $rows OFFSET $from":"";

        $filter_companies = $filter_companies==="" ? "" : " AND id_company IN ($filter_companies)";

        $query = "
                SELECT Conveyor.*, UltrasonicReading.ultrasonic_id, Empresa.id, Empresa.name, Empresa.path_image AS company_image,ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name,
                Image.path, TabInstalledBelt.failure_mode
                FROM conveyors AS Conveyor
                INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
                LEFT JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
                LEFT JOIN us_tab_installed_belts AS TabInstalledBelt ON Conveyor.id = TabInstalledBelt.conveyor_id
                LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                LEFT JOIN ultrasonics AS Ultrasonic ON Conveyor.id = Ultrasonic.conveyor_id
                LEFT JOIN ultrasonic_readings AS UltrasonicReading ON Ultrasonic.id = UltrasonicReading.ultrasonic_id
                LEFT JOIN images AS Image ON Conveyor.cover_img = Image.id
                WHERE Empresa.deleted = 0 AND Conveyor.numero LIKE '%$conveyor%' $filter_companies
                      AND Conveyor.eliminada = 0 AND Conveyor.is_us_conveyor = 1
                GROUP BY Conveyor.id
                ORDER BY Conveyor.$sort
                $pagination

                ";


        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findFullById($conveyor_id) {
        $conveyor_id = Sanitize::escape($conveyor_id);

        $query = "
            SELECT Conveyor.*, TabConveyor.*, TabIdler.*, TabInstalledBelt.*, TabMaterial.*, TabPulley.*, TabRemark.*, TabWearLife.*, TabTransitionZone.*
            FROM conveyors AS Conveyor
            LEFT JOIN us_tab_conveyors AS TabConveyor ON Conveyor.id = TabConveyor.conveyor_id
            LEFT JOIN us_tab_idlers AS TabIdler ON Conveyor.id = TabIdler.conveyor_id
            LEFT JOIN us_tab_installed_belts AS TabInstalledBelt ON Conveyor.id = TabInstalledBelt.conveyor_id
            LEFT JOIN us_tab_materials AS TabMaterial ON Conveyor.id = TabMaterial.conveyor_id
            LEFT JOIN us_tab_pulleys AS TabPulley ON Conveyor.id = TabPulley.conveyor_id
            LEFT JOIN us_tab_remarks AS TabRemark ON Conveyor.id = TabRemark.conveyor_id
            LEFT JOIN us_tab_transition_zones AS TabTransitionZone ON Conveyor.id = TabTransitionZone.conveyor_id
            LEFT JOIN us_tab_wear_lives AS TabWearLife ON Conveyor.id = TabWearLife.conveyor_id

            WHERE Conveyor.id = '$conveyor_id' AND eliminada = 0 AND aprobada = 'SI' AND Conveyor.is_us_conveyor = 1
                ";
        $result = $this->query($query);

        //LEFT JOIN config_transporter AS UbicacionTransportador ON Conveyor.trans_ubicacion = UbicacionTransportador.id

        return count($result) > 0 ? $result[0] : array();
    }

    public function findByIdWithCompany($conveyor_id) {
        $conveyor_id = Sanitize::escape($conveyor_id);
        $query = "
            SELECT Conveyor.*, Ultrasonic.*,UltrasonicReading.ultrasonic_id, Empresa.id, Empresa.name, Empresa.parent, Empresa.path_image AS company_image,
            ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name, Material.*, TipoTensor.*,Aceite.*,Ubicacion.*,RodilloAngImpacto.*,
            TabConveyor.*, TabIdler.*, TabInstalledBelt.*, TabPulley.*, TabRemark.*, TabTransitionZone.*, TabMaterial.*,TabWearLife.*
            FROM conveyors AS Conveyor
            INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
            INNER JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
            LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
            LEFT JOIN ultrasonics AS Ultrasonic ON Conveyor.id = Ultrasonic.conveyor_id
            LEFT JOIN ultrasonic_readings AS UltrasonicReading ON Ultrasonic.id = UltrasonicReading.ultrasonic_id

            LEFT JOIN us_tab_conveyors AS TabConveyor ON Conveyor.id = TabConveyor.conveyor_id
            LEFT JOIN us_tab_idlers AS TabIdler ON Conveyor.id = TabIdler.conveyor_id
            LEFT JOIN us_tab_installed_belts AS TabInstalledBelt ON Conveyor.id = TabInstalledBelt.conveyor_id
            LEFT JOIN us_tab_materials AS TabMaterial ON Conveyor.id = TabMaterial.conveyor_id
            LEFT JOIN us_tab_pulleys AS TabPulley ON Conveyor.id = TabPulley.conveyor_id
            LEFT JOIN us_tab_remarks AS TabRemark ON Conveyor.id = TabRemark.conveyor_id
            LEFT JOIN us_tab_transition_zones AS TabTransitionZone ON Conveyor.id = TabTransitionZone.conveyor_id
            LEFT JOIN us_tab_wear_lives AS TabWearLife ON Conveyor.id = TabWearLife.conveyor_id

            LEFT JOIN config_transporter AS Material ON TabMaterial.material = Material.id
            LEFT JOIN config_transporter AS TipoTensor ON TabConveyor.takeup_type = TipoTensor.id
            LEFT JOIN config_transporter AS Aceite ON TabMaterial.oil_presence = Aceite.id
            LEFT JOIN config_transporter AS Ubicacion ON TabConveyor.location = Ubicacion.id
            LEFT JOIN config_transporter AS RodilloAngImpacto ON TabIdler.impact_angle = RodilloAngImpacto.id

            WHERE Conveyor.id = '$conveyor_id' AND eliminada = 0 AND aprobada = 'SI' AND Conveyor.is_us_conveyor = 1
                ";
        $result = $this->query($query);
        //LEFT JOIN config_transporter AS UbicacionTransportador ON Conveyor.trans_ubicacion = UbicacionTransportador.id           
        
        return count($result) > 0 ? $result[0] : array();
    }
    
    public function findByIdsWithCompany($conveyor_ids) {
        $conveyor_ids = Sanitize::escape($conveyor_ids);

        $query = "
            SELECT Conveyor.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name, Material.*, TipoTensor.*, GradoMaterial.*,
            CondicionAlimentacion.*, CondicionCarga.*, FrecuenciaCarga.*, TamanioGranular.*, TipoDensidad.*, Agresividad.*,
            EspesorCubiertaSup.*,EspesorCubiertaInf.*, PoleaArcoContacto.*,
            RodilloAngImpacto.*, RodilloDiamCarga.*, RodilloAngCarga.*, RodilloDiamRetorno.*,
            TabConveyor.*, TabIdler.*, TabInstalledBelt.*, TabPulley.*, TabRemark.*, TabTransitionZone.*,
            Conveyor.banda_ancho AS summary_width, Conveyor.banda_desarrollo_total AS summary_feet, 
            Conveyor.banda_tension AS summary_rating, '-' AS summary_layers, Conveyor.banda_marca AS summary_brand,
            '1' AS summary_idlers
            FROM conveyors AS Conveyor
            INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
            INNER JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
            LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
            LEFT JOIN config_transporter AS Material ON Conveyor.mat_descripcion = Material.id
            LEFT JOIN config_transporter AS TipoTensor ON Conveyor.tensor_tipo = TipoTensor.id
            LEFT JOIN config_transporter AS GradoMaterial ON Conveyor.mat_grado_mat_transportado = GradoMaterial.id
            LEFT JOIN config_transporter AS CondicionAlimentacion ON Conveyor.mat_condicion_alimentacion = CondicionAlimentacion.id
            LEFT JOIN config_transporter AS CondicionCarga ON Conveyor.mat_condicion_carga = CondicionCarga.id
            LEFT JOIN config_transporter AS FrecuenciaCarga ON Conveyor.mat_frecuencia_carga = FrecuenciaCarga.id
            LEFT JOIN config_transporter AS TamanioGranular ON Conveyor.mat_tamanio_granular = TamanioGranular.id
            LEFT JOIN config_transporter AS TipoDensidad ON Conveyor.mat_tipo_densidad = TipoDensidad.id
            LEFT JOIN config_transporter AS Agresividad ON Conveyor.mat_agresividad = Agresividad.id
            
            LEFT JOIN config_transporter AS EspesorCubiertaSup ON Conveyor.id_espesor_cubierta_sup = EspesorCubiertaSup.id
            LEFT JOIN config_transporter AS EspesorCubiertaInf ON Conveyor.id_espesor_cubierta_inf = EspesorCubiertaInf.id
            LEFT JOIN config_transporter AS PoleaArcoContacto ON Conveyor.polea_arco_contacto = PoleaArcoContacto.id
            LEFT JOIN config_transporter AS RodilloAngImpacto ON Conveyor.rod_ang_impacto = RodilloAngImpacto.id
            LEFT JOIN config_transporter AS RodilloDiamCarga ON Conveyor.rod_diam_carga = RodilloDiamCarga.id
            LEFT JOIN config_transporter AS RodilloAngCarga ON Conveyor.rod_ang_carga = RodilloAngCarga.id
            LEFT JOIN config_transporter AS RodilloDiamRetorno ON Conveyor.rod_diam_retorno = RodilloDiamRetorno.id

            LEFT JOIN tab_conveyors AS TabConveyor ON Conveyor.id = TabConveyor.conveyor_id
            LEFT JOIN tab_idlers AS TabIdler ON Conveyor.id = TabIdler.conveyor_id
            LEFT JOIN tab_installed_belts AS TabInstalledBelt ON Conveyor.id = TabInstalledBelt.conveyor_id
            LEFT JOIN tab_pulleys AS TabPulley ON Conveyor.id = TabPulley.conveyor_id
            LEFT JOIN tab_remarks AS TabRemark ON Conveyor.id = TabRemark.conveyor_id
            LEFT JOIN tab_transition_zones AS TabTransitionZone ON Conveyor.id = TabTransitionZone.conveyor_id
            
            WHERE Conveyor.id IN ($conveyor_ids) AND eliminada = 0 AND aprobada = 'SI' AND Conveyor.is_us_conveyor = 1
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function getItemsConveyor($conveyor_id, $query = '', $sort = '') {
        $conveyor_id = Sanitize::escape($conveyor_id);
        $sort = Sanitize::escape($sort);
        $query = Sanitize::escape($query);
        
        $item_image = Item::IMAGE;
        $item_video = Item::VIDEO;
        $item_folder = Item::FOLDER;
        $item_note = Item::NOTE;
        $item_report = Item::REPORT;
        $item_file = Item::FILE;
        
        switch($sort){
            case '':case 'actualizada':
                $sort = 'ConveyorItem.updated_item DESC';
            break;
            case 'nombre':
                $sort = 'ConveyorItem.name_item ASC';
            break;
            case 'tipo':
                $sort = 'ConveyorItem.tipo ASC';
            break;
        }
        
        $query = "
                 SELECT * 
                FROM (
                    SELECT Item.id, Item.path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.taken_at as taken_at, 0 AS is_folder_year,
                    Item.descripcion AS desc_item, '$item_image' AS type_item, 3 AS tipo
                    FROM images AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0 AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION 
                    SELECT Item.id, Item.path, Item.thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item,
                    Item.taken_at as taken_at, 0 AS is_folder_year,
                    Item.descripcion AS desc_item, '$item_video' AS type_item, 3 AS tipo
                    FROM movies AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0 AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, Item.is_folder_year AS is_folder_year,
                    '' AS desc_item, '$item_folder' AS type_item, 1 AS tipo
                    FROM buckets AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0 AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, 0 AS is_folder_year,
                    Item.contenido AS desc_item, '$item_report' AS type_item, 2 AS tipo
                    FROM reports AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0  AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.updated_at AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, 0 AS is_folder_year,
                    '' AS desc_item, '$item_file' AS type_item, 2 AS tipo
                    FROM archives AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0  AND Item.eliminada = 0                    
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, 0 AS is_folder_year,
                    Item.contenido AS desc_item, '$item_note' AS type_item, 4 AS tipo
                    FROM notes AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0  AND Item.eliminada = 0
                        
                ) AS ConveyorItem
                WHERE ConveyorItem.name_item LIKE '%$query%'
                ORDER BY $sort
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function getItemsFolder($folder_id, $sort) {
        $folder_id = Sanitize::escape($folder_id);
        $sort = Sanitize::escape($sort);
        
        $item_image = Item::IMAGE;
        $item_video = Item::VIDEO;
        $item_folder = Item::FOLDER;
        $item_report = Item::REPORT;
        $item_note = Item::NOTE;
        $item_file = Item::FILE;
        
        switch($sort){
            case '':case 'actualizada':
                $sort = 'ConveyorItem.updated_item DESC';
            break;
            case 'nombre':
                $sort = 'ConveyorItem.name_item ASC';
            break;
            case 'tipo':
                $sort = 'ConveyorItem.tipo ASC';
            break;
        }
        
        $query = "
                 SELECT * 
                FROM (
                    SELECT Item.id, Item.path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.taken_at as taken_at, 0 AS is_folder_year,
                    Item.descripcion AS desc_item, '$item_image' AS type_item, Item.in_smartview, 3 AS tipo
                    FROM images AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION 
                    SELECT Item.id, Item.path, Item.thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item,
                    Item.taken_at as taken_at, 0 AS is_folder_year,
                    Item.descripcion AS desc_item, '$item_video' AS type_item,'' AS in_smartview, 4 AS tipo
                    FROM movies AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, Item.is_folder_year AS is_folder_year,
                    '' AS desc_item, '$item_folder' AS type_item, '' AS in_smartview, 6 AS tipo
                    FROM buckets AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, 0 AS is_folder_year,
                    Item.contenido AS desc_item, '$item_report' AS type_item,'' AS in_smartview, 1 AS tipo
                    FROM reports AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.updated_at AS updated_item, Item.nombre AS name_item, 
                    '' as taken_at, 0 AS is_folder_year,
                    '' AS desc_item, '$item_file' AS type_item,'' AS in_smartview, 5 AS tipo
                    FROM archives AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item,
                    '' as taken_at, 0 AS is_folder_year,
                    Item.contenido AS desc_item, '$item_note' AS type_item, Item.in_smartview, 2 AS tipo
                    FROM notes AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                ) AS ConveyorItem
                ORDER BY $sort
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function getSmartItemsByType($type_item, $conveyor_id, $folder_id) {
        $type_item = Sanitize::escape($type_item);
        $conveyor_id = Sanitize::escape($conveyor_id);
        $folder_id = Sanitize::escape($folder_id);

        $models = array('Image' => 'images', 'Movie' => 'movies', 'Note' => 'notes');

        $query = "
               SELECT * FROM(SELECT *, '$type_item' AS type_item
               FROM $models[$type_item] AS Item
               WHERE Item.parent_conveyor = '$conveyor_id' AND Item.parent_folder = '$folder_id' 
                     AND Item.eliminada = 0 AND Item.in_smartview = 1) AS ConveyorItem
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    /**
     * Get report rows filtering by id
     * @param int $conveyor_id conveyor filter
     * @return array
     */
    public function getReportsConveyor($conveyor_id) {
        $conveyor_id = Sanitize::escape($conveyor_id);
        $query = "
            SELECT Reporte.*, UsuarioEmpresa.name, Conveyor.numero
            FROM reports AS Reporte
            RIGHT JOIN conveyors AS Conveyor ON Reporte.parent_conveyor = Conveyor.id
            RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Reporte.owner_user = UsuarioEmpresa.id
            WHERE parent_conveyor = '$conveyor_id' AND Reporte.eliminada=0
            ORDER BY Reporte.creada DESC    
           ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    /**
     * find all conveyors checking if its company exists
     * @return array
     */
    public function getAllWithCompany() {
        $query = "
            SELECT Conveyor.* 
            FROM conveyors AS Conveyor
            INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id AND Empresa.deleted = 0
            WHERE Conveyor.eliminada = 0 AND Conveyor.is_us_conveyor = 1
            ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    public function getNotificationsLog($conveyor_id){
        $conveyor_id = Sanitize::escape($conveyor_id);
        $item_image = Item::IMAGE;
        $item_video = Item::VIDEO;
        $item_folder = Item::FOLDER;
        $item_report = Item::REPORT;
        $item_note = Item::NOTE;
        $item_file = Item::FILE;
        
        $item_ultrasonic = Item::ULTRASONIC;
        
        $query = " 
            SELECT *
            FROM (
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN images AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_image') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN movies AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_video') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN reports AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_report') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1    
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN notes AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_note') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN buckets AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_folder') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.nombre,Item.parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN archives AS Item ON Notification.id_item = Item.id AND Item.parent_conveyor = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_file') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                UNION
                SELECT Notification.id, Notification.content, Notification.creation_date AS date, Notification.type_item,
                Item.id as Item,Item.conveyor_brand_ultra AS nombre ,Item.conveyor_id AS parent_conveyor,
                UsuarioEmpresa.name as name_user
                FROM notifications AS Notification
                INNER JOIN ultrasonics AS Item ON Notification.id_item = Item.id AND Item.conveyor_id = '$conveyor_id'
                RIGHT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                WHERE type_item IN('$item_ultrasonic') AND Notification.is_programmed = 0 AND Notification.just_for_log = 1
                ) LogItem
            ORDER BY id DESC
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

}
