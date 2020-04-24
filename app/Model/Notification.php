<?php

/* 
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file Notification.php
 *     Model for notifications table
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */

class Notification extends AppModel {
    public $name = 'Notification';
    
    /**
     * Function to get all notifications [viewed and not viewed] created by an specific company
     * if current date is bigger than activation_date field then the notification has been viewed
     * @param int $company_id the company id
     * @return array
     */
    public function findallByProgrammedCompany($company_id){
        $company_id = Sanitize::escape($company_id);
        $query = "
                SELECT * 
                FROM (
                        SELECT Notification.*, IF(NOW() > activation_date,1,0) AS viewed,
                        Company.name AS company_name, UsuarioEmpresa.name AS user_name
                        FROM notifications AS Notification
                        LEFT JOIN empresas AS Company ON Notification.id_company_dest = Company.id
                        LEFT JOIN usuarios_empresas AS UsuarioEmpresa ON Notification.generated_by = UsuarioEmpresa.id
                        WHERE id_company_ori = '$company_id' AND is_programmed = 1 AND just_for_log = 0
                 ) AS Notification
                 ORDER BY Notification.viewed ASC, Notification.activation_date ASC
                ";        
         $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    /**
     * Function to get all notifications [viewed and not viewed] for an specif user and company
     * if the notification has not been viewed, ViewedNotification fields are null
     * @param int $company_id the company id
     * @param int $user_id the user id
     * @return array
     */
    public function findallByCompanyAndUser($company_id, $user_id){
        $company_id = Sanitize::escape($company_id);
        $user_id = Sanitize::escape($user_id);
        $query = "
                SELECT Notification.id,Notification.id_company_dest, Notification.content, Notification.activation_date, Notification.is_programmed, Notification.type_item, Notification.id_item,
                        ViewedNotification.id_notification, ViewedNotification.viewed_date
                FROM notifications AS Notification
                LEFT JOIN viewed_notifications AS ViewedNotification ON Notification.id = ViewedNotification.id_notification 
        	AND ViewedNotification.id_user = '$user_id'
                WHERE id_company_dest = '$company_id' AND NOW() > activation_date AND creation_date > DATE_SUB(DATE(now()), INTERVAL 15 DAY) AND just_for_log = 0
                ORDER BY activation_date DESC    
                ";
         $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
    
    public function findReady(){
        $query = "
                SELECT Notification.*, Empresa.*
                FROM notifications AS Notification
                INNER JOIN empresas AS Empresa ON Notification.id_company_dest = Empresa.id
                WHERE NOW() > activation_date AND is_programmed = 1 AND sended = 0 AND mails != '' AND just_for_log = 0
                ORDER BY activation_date DESC    
                ";        
         $result = $this->query($query);
        return count($result) > 0 ? $result : array();
    }
}
