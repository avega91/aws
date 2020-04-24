<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Comment.php
 *     Model for comments table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class Comment extends AppModel {
    public $name = 'Comment';
    
    /**
     * function to get comment for an specific item
     * @param int $id_item item id
     * @param string $type_item enum type item (Conveyor, Image, Video, Folder)
     * @return array
     */
    public function getCommentsItemByType($id_item, $type_item){
         $id_item = Sanitize::escape($id_item);
         $type_item = Sanitize::escape($type_item);
         
         $query = "
                SELECT Comment.*, UserEmpresa.name, UserEmpresa.path_image
                FROM comments AS Comment
                INNER JOIN usuarios_empresas AS UserEmpresa ON Comment.owner_user_id = UserEmpresa.id
                WHERE type_item = '$type_item' AND id_item = '$id_item' AND Comment.deleted = '0'
                ORDER BY date DESC
                ";
         $result = $this->query($query);
         return count($result) > 0 ? $result : array();         
    }
}
