<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Noticia.php
 *     Model for noticias table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class Noticia extends AppModel {
    public $name = 'Noticia';
//    public $useTable = false;
//    public $primaryKey = 'id';
    
    
    /**
     * Save news register
     * @param string $titulo news title
     * @param string $contenido news content
     * @param string $path_image cover image for news
     * @param int $cargado_por the user id that created the news
     * @param enum $publicada define if the news register if posted or not, values: SI, NO, default SI     
     * @return int last insert id
     */
    public function save_reg($titulo, $contenido, $path_image, $cargado_por, $publicada = 'SI'){
        $titulo = Sanitize::escape($titulo);
        $contenido = Sanitize::escape($contenido);
        $cargado_por = Sanitize::escape($cargado_por);
        $path_image = Sanitize::escape($path_image);
        $actualizada = date('Y-m-d H:i:s');
        $query = "
            INSERT INTO noticias(titulo, descripcion, img_portada, creada_por, publicada, actualizada)
            VALUES ('$titulo','$contenido','$path_image','$cargado_por','$publicada','$actualizada')
            ";
        
        $this->query($query);      
        return  $this->getAffectedRows() > 0 ? $this->getLastInsertID() : 0;
    }
    
    
    /**
     * Update some new identified by $id_noticia param
     * @param int $id_noticia row id for update
     * @param string $titulo news title
     * @param string $contenido news content
     * @param string $path_image cover image for news
     * @param enum $publicada define if the news register if posted or not, values: SI, NO, default SI     
     * @return bool
     */
    public function update_reg($id_noticia, $titulo, $contenido, $path_image, $publicada = 'SI'){
        $id_noticia = Sanitize::escape($id_noticia);
        $titulo = Sanitize::escape($titulo);
        $contenido = Sanitize::escape($contenido);
        $path_image = Sanitize::escape($path_image);
        
        $query = "
            UPDATE noticias SET titulo = '$titulo', descripcion = '$contenido', img_portada = '$path_image', publicada = '$publicada'
            WHERE id = '$id_noticia'
            ";
        $this->query($query);     
        return $this->getAffectedRows()>0 ? true : false;
    }
    
    /**
     * Get a news collection in the start limit range
     * @param int $start from row
     * @param int $limit number of rows
     * @param bool $todas flag for filter posted and not posted news
     * @return mixed
     */
    public function findAll($start=0, $limit=10, $todas = true){
        $start = Sanitize::escape($start);
        $limit = Sanitize::escape($limit);
        
        $where = '1=1';
        if(!$todas){//Si no todas, solo las publicadas
            $where = "publicada = 'SI'";
        }
        
        $limit = $limit > 0 ? "LIMIT $start, $limit" : "";
        
        $query = "
            SELECT *
            FROM noticias AS noticia
            WHERE $where AND eliminada = 0
            ORDER BY id DESC
            $limit
            ";
        $result = $this->query($query);
        return count($result)>0 ? $result : false;
    }
    
    /**
     * Get a news row
     * @param int $id_noticia news id
     * @return mixed
     */
    public function findById($id_noticia){
        $id_noticia = Sanitize::escape($id_noticia);
        $query = "
            SELECT *
            FROM noticias AS noticia
            WHERE id='$id_noticia'
            ";        
         $result = $this->query($query);
         return $this->getNumRows()>0 ? $result[0]['noticia'] : false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Suspende una noticia para que no pueda verse, es decir la despublica
     * @param int $id_noticia el id de la noticia
     * @return bool
     */
    public function suspend($id_noticia){
        $id_noticia = Sanitize::escape($id_noticia);
        $query = "
            UPDATE noticias SET publicada = 'NO'
            WHERE id='$id_noticia'
            ";
        $this->query($query);
        return $this->getAffectedRows() > 0 ? true : false;
    }
    
    /**
     * Desuspende una noticia para que pueda verse, es decir la republica
     * @param int $id_noticia el id de la noticia
     * @return bool
     */
    public function unsuspend($id_noticia){
        $id_noticia = Sanitize::escape($id_noticia);
        $query = "
            UPDATE noticias SET publicada = 'SI'
            WHERE id='$id_noticia'
            ";
        $this->query($query);
        return $this->getAffectedRows() > 0 ? true : false;
    }
    
     /**
     * Remueve un elemento segun su id y el propietario del elemento
     * @param int $id_item el id del elemento
     * @param int $id_user el id del usuario propietario del elemento
     * @return bool
     */
     public function removeByIdAndOwner($id_item, $id_user = 0){
         $id_item = Sanitize::escape($id_item);
         $id_user = Sanitize::escape($id_user);
        $query = "
            DELETE FROM noticias
            WHERE id='$id_item'
            LIMIT 1
            ";
        $this->query($query);
        return $this->getAffectedRows()>0 ? true:false;
    }

}

?>
