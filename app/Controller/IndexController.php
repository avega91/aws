<?php

/*
 * The Continental License
 * Copyright 2014 Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file IndexController.php
 * Management of actions for main page
 *
 * @project Contiplus
 * @author toc-toc@cocothink.com,ieialbertogd@gmail.com
 * @date 2014
 */
App::import ( 'Vendor', 'VideoEncoder', array (
        'file' => 'VideoEncoder/VideoEncoder.php'
) );
App::uses ( 'HttpSocket', 'Network/Http' );
App::import("vendors", "autoload", array("file" => "autoload.php'"));

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class IndexController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter ();
        $this->uses [] = 'FolderApp';
        $this->uses [] = 'Archive';
    }

    public function xyz() {
        $this->autoRender = false;
        $this->layout = false;
        echo Configure::version ();
        echo '<pre>';
        var_dump ( $_SERVER );
        echo '</pre><br><br>';

        $usuario = $this->UsuariosEmpresa->isAuth ( 'master_405D', '78240' );
        var_dump ( $usuario );

        var_dump ( WWW_ROOT );
    }

    public function index() {
        // Obtener las empresas tipo cliente que aplican segun el tipo de usuario logueado
        $filterCompanies = $this->Core->getCompaniesFilterAccordingUserLogged ();
        $clientCompanies = $this->Empresa->findClientCompaniesByIdsWithTeam ( $filterCompanies );
        $filterCompanyIds = explode ( ',', $filterCompanies );

        // Get all BS of account
        $totalBuoySystemsRows = $this->FolderApp->find ( 'all', [ 
                'fields' => [ 
                        'id'
                ],
                'conditions' => [ 
                        'FolderApp.deleted' => 0,
                        'FolderApp.type' => 'buoy_system',
                        'FolderApp.client_id' => $filterCompanyIds
                ]
        ] );

        $totalBuoySystemsRows = ! empty ( $totalBuoySystemsRows ) ? Set::extract ( '/FolderApp/.', $totalBuoySystemsRows ) : [ ];
        $buoySystemIds = array_map ( "array_shift", $totalBuoySystemsRows );
        $totalBuoySystems = count ( $totalBuoySystemsRows );

        // Get all archives of BSs of account
        $totalArchives = $this->Archive->find ( 'count', [ 
                'conditions' => [ 
                        'Archive.deleted' => 0,
                        'Archive.buoy_system_id' => $buoySystemIds
                ]
        ] );

        $totalRunning = $this->FolderApp->find ( 'count', [ 
                'conditions' => [ 
                        'FolderApp.deleted' => 0,
                        'FolderApp.type' => 'buoy_system',
                        'FolderApp.status' => BuoySystem::IsWorking
                ]
        ] );
        $totalMaintenance = $this->FolderApp->find ( 'count', [ 
                'conditions' => [ 
                        'FolderApp.deleted' => 0,
                        'FolderApp.type' => 'buoy_system',
                        'FolderApp.status' => BuoySystem::IsInMaintenance
                ]
        ] );

        $lastBuoy = $this->FolderApp->find ( 'first', [ 
                'order' => [ 
                        'FolderApp.created_at DESC'
                ],
                'conditions' => [ 
                        'FolderApp.deleted' => 0,
                        'FolderApp.type' => 'buoy_system',
                        'FolderApp.client_id' => $filterCompanyIds
                ]
        ] );
        $lastBuoyName = ! empty ( $lastBuoy ) ? $lastBuoy ['FolderApp'] ['name'] : '-';
        $lastBuoyParams = ! empty ( $lastBuoy ) ? $this->Core->encodeParams ( $lastBuoy ['FolderApp'] ['id'] ) : [ ];

        $notifications_data = $this->Notifications->getAllByCompanyAndUser ();

        $this->set ( 'notifications', $notifications_data ['rows'] );

        $this->jsToInclude [] = 'application/Index/index';
        $this->set ( 'jsToInclude', $this->jsToInclude );

        $this->set ( 'lastBuoyName', $lastBuoyName );
        $this->set ( 'lastBuoyParams', $lastBuoyParams );
        $this->set ( 'archives', $totalArchives );
        $this->set ( 'buoySystems', $totalBuoySystems );
        $this->set ( 'buoysWorking', $totalRunning );
        $this->set ( 'buoysMaintenance', $totalMaintenance );
        $this->set ( 'customers', count ( $clientCompanies ) );
    }
}
