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

class BandasTransportadora extends AppModel {

    public $name = 'BandasTransportadora';
    public $companies = null;

    const VERSION_1 = 'V1';
    const VERSION_2 = 'V2';
    const ITEM_IMAGE = 'Image';
    const ITEM_VIDEO = 'Video';
    const ITEM_FOLDER = 'Folder';
    const ITEM_DETAIL = 'ConveyorDetail';
    
    public function getAll(){
        $query = " 
            SELECT *
            FROM bandas_transportadoras AS BandaTransportadora
            INNER JOIN users AS User ON BandaTransportadora.id_user = User.id
            WHERE User.deleted = 0
            ORDER BY BandaTransportadora.id ASC
            ";
        
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findAllWithCompany($filter_companies, $conveyor = '', $sort = '') {
        $filter_companies = Sanitize::escape($filter_companies);
        $conveyor = Sanitize::escape($conveyor);
        $sort = Sanitize::escape($sort);
        $sort = $sort == '' || $sort == 'actualizada' ? 'actualizada DESC' : $sort . ' ASC';

        /* $query = "
          SELECT Conveyor.*, Empresa.name
          FROM conveyors AS Conveyor
          LEFT JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
          WHERE Conveyor.numero LIKE '%$conveyor%' AND id_company IN ($filter_companies)
          AND Conveyor.eliminada = 0
          ORDER BY Conveyor.$sort
          "; */
        $query = "
                SELECT Conveyor.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name
                FROM conveyors AS Conveyor
                INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
                INNER JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
                LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
                WHERE Conveyor.numero LIKE '%$conveyor%' AND id_company IN ($filter_companies)
                      AND Conveyor.eliminada = 0
                ORDER BY Conveyor.$sort
                ";

        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function findByIdWithCompany($conveyor_id) {
        $conveyor_id = Sanitize::escape($conveyor_id);
        /* $query = "
          SELECT Conveyor.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name
          FROM conveyors AS Conveyor
          INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
          INNER JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
          LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
          WHERE Conveyor.id = '$conveyor_id' AND eliminada = 0 AND aprobada = 'SI'
          "; */

        $query = "
            SELECT Conveyor.*, Empresa.id, Empresa.name, Empresa.path_image AS company_image,ProfileConveyor.path AS image_profile, Distribuidor.id, Distribuidor.name, Material.*, TipoTensor.*, GradoMaterial.*,
            CondicionAlimentacion.*, EspesorCubiertaSup.*,EspesorCubiertaInf.*, PoleaArcoContacto.*,
            RodilloAngImpacto.*, RodilloDiamCarga.*, RodilloAngCarga.*, RodilloDiamRetorno.*
            FROM conveyors AS Conveyor
            INNER JOIN empresas AS Empresa ON Conveyor.id_company = Empresa.id
            INNER JOIN perfiles_transportadores AS ProfileConveyor ON Conveyor.perfil = ProfileConveyor.id
            LEFT JOIN empresas AS Distribuidor ON Empresa.parent = Distribuidor.id
            LEFT JOIN config_transporter AS Material ON Conveyor.mat_descripcion = Material.id
            LEFT JOIN config_transporter AS TipoTensor ON Conveyor.tensor_tipo = TipoTensor.id
            LEFT JOIN config_transporter AS GradoMaterial ON Conveyor.mat_grado_mat_transportado = GradoMaterial.id
            LEFT JOIN config_transporter AS CondicionAlimentacion ON Conveyor.mat_condicion_alimentacion = CondicionAlimentacion.id
            LEFT JOIN config_transporter AS EspesorCubiertaSup ON Conveyor.id_espesor_cubierta_sup = EspesorCubiertaSup.id
            LEFT JOIN config_transporter AS EspesorCubiertaInf ON Conveyor.id_espesor_cubierta_inf = EspesorCubiertaInf.id
            LEFT JOIN config_transporter AS PoleaArcoContacto ON Conveyor.polea_arco_contacto = PoleaArcoContacto.id
            LEFT JOIN config_transporter AS RodilloAngImpacto ON Conveyor.rod_ang_impacto = RodilloAngImpacto.id
            LEFT JOIN config_transporter AS RodilloDiamCarga ON Conveyor.rod_diam_carga = RodilloDiamCarga.id
            LEFT JOIN config_transporter AS RodilloAngCarga ON Conveyor.rod_ang_carga = RodilloAngCarga.id
            LEFT JOIN config_transporter AS RodilloDiamRetorno ON Conveyor.rod_diam_retorno = RodilloDiamRetorno.id
            WHERE Conveyor.id = '$conveyor_id' AND eliminada = 0 AND aprobada = 'SI'
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result[0] : array();
    }

    public function getItemsConveyor($conveyor_id, $query = '', $sort = '') {
        $conveyor_id = Sanitize::escape($conveyor_id);
        $sort = Sanitize::escape($sort);
        $query = Sanitize::escape($query);
        
        $item_image = Item::IMAGE;
        $item_video = Item::VIDEO;
        $item_folder = Item::FOLDER;
        $item_report = Item::REPORT;
        
        $sort = $sort == '' || $sort == 'actualizada' ? 'ConveyorItem.updated_item DESC' : 'ConveyorItem.name_item ASC';
        $query = "
                 SELECT * 
                FROM (
                    SELECT Item.id, Item.path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.descripcion AS desc_item, '$item_image' AS type_item
                    FROM images AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0 AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION 
                    SELECT Item.id, Item.path, Item.thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item,
                    Item.descripcion AS desc_item, '$item_video' AS type_item
                    FROM movies AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0 AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    '' AS desc_item, '$item_folder' AS type_item
                    FROM buckets AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.contenido AS desc_item, '$item_report' AS type_item
                    FROM reports AS Item
                    WHERE Item.parent_conveyor = '$conveyor_id' AND parent_folder = 0
                ) AS ConveyorItem
                WHERE ConveyorItem.name_item LIKE '%$query%'
                ORDER BY $sort
                ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

    public function getItemsFolder($folder_id) {
        $folder_id = Sanitize::escape($folder_id);
        $item_image = Item::IMAGE;
        $item_video = Item::VIDEO;
        $item_report = Item::REPORT;
        $item_note = Item::NOTE;
        $query = "
                 SELECT * 
                FROM (
                    SELECT Item.id, Item.path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.descripcion AS desc_item, '$item_image' AS type_item
                    FROM images AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION 
                    SELECT Item.id, Item.path, Item.thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item,
                    Item.descripcion AS desc_item, '$item_video' AS type_item
                    FROM movies AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.aprobada = 'SI' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.contenido AS desc_item, '$item_report' AS type_item
                    FROM reports AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                    UNION
                    SELECT Item.id, '' AS path, '' AS thumbnail_path, Item.actualizada AS updated_item, Item.nombre AS name_item, 
                    Item.contenido AS desc_item, '$item_note' AS type_item
                    FROM notes AS Item
                    WHERE Item.parent_folder = '$folder_id' AND Item.eliminada = 0
                ) AS ConveyorItem
                ORDER BY ConveyorItem.updated_item DESC
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
               WHERE Item.parent_conveyor = '$conveyor_id' AND Item.parent_folder = '$folder_id' AND Item.aprobada = 'SI' AND Item.eliminada = 0) AS ConveyorItem
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
            WHERE Conveyor.eliminada = 0
            ";
        $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }

}
