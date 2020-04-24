<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file ToolsController.php
 *     Management of actions for system tools
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class ToolsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        if (!$this->Session->check(Statistic::GO_TOOLS)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_TOOLS);
            $this->Session->write(Statistic::GO_TOOLS, Statistic::GO_TOOLS);
        }
    }

    public function tensionOperacionUnitaria() {
        $angulos_contacto = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::ANGULO_CONTACTO);
        $tipos_tensor = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::TENSOR_TYPE);
        $tipos_polea = $this->ConfigTransporter->getAllByDescId(ConfigTransporter::POLEA_TYPE);
        
        $this->set('angulos_contacto', $angulos_contacto);
        $this->set('tipos_tensor', $tipos_tensor);
        $this->set('tipos_polea', $tipos_polea);
    }

}
