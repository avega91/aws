<?php

class ContentHelper extends AppHelper {

    public $helpers = array('Html', 'Utilities', 'ImageSize');
    public $_lang;
    public $_site;
    public $_credentials;

    public function __construct(View $view, $settings = array()) {
        parent::__construct($view, $settings);
        $this->_init();
    }

    /**
     * Set helper vars
     */
    private function _init() {
        $this->_lang = $this->_View->getVar('language');
        $this->_site = $this->_View->getVar('site');
        $this->_credentials = $this->_View->getVar('credentials');
    }

    public function printBrowsingLog($browsing_log) {
        $locking_data = '';
        if (!empty($browsing_log)) {
            foreach ($browsing_log AS $log) {
                $info_log = $log['BrowsingLog'];
                $assoc_user = $log['UsuariosEmpresa'];
                $secureUserParams = $this->Utilities->encodeParams($info_log['user_id']);
                $secureCompanyParams = $this->Utilities->encodeParams($assoc_user['id_empresa']);
                $urlProfileCompany = $this->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlProfileUser = $this->Html->url(array('controller' => 'Users', 'action' => 'profile', $secureUserParams['item_id'], $secureUserParams['digest']));
                $urlUnlockUser = $this->Html->url(array('controller' => 'Users', 'action' => 'lockUnlock', $secureUserParams['item_id'], $secureUserParams['digest']));

                list($country, $country_code, $state, $state_code) = explode('|', $info_log['location']);
                $location = __('Desde %s, %s', array($country, $state));
                $action_btns = '<li class="company profile-company-link" title="' . __("Empresa", true) . '" rel="' . $urlProfileCompany . '"></li>';
                $action_btns .= '<li class="user-profile" title="' . __("Perfil", true) . '"><a href="#modal" class="profile user-profile-link" alt="profile-dialog|callOpenUProfile|callUpdateDataTable" rel="' . $urlProfileUser . '"></a></li>';
                $action_btns .= '<li class="location location-link" title="' . $location . '" rel=""></li>';

                $class_device = $info_log['device'] == BrowsingLog::FROM_DESKTOP ? 'from_desktop' : 'from_mobile';
                $device_action = '<li class="' . $class_device . '" title="" rel=""  alt=""></li>';


                $crud_operations = array(
                    BrowsingLog::ALTA => __('Agrego', true),
                    BrowsingLog::ACTUALIZACION => __('Actualizo', true),
                    BrowsingLog::ELIMINACION => __('Elimino', true)
                );

                $log_description = '';
                switch ($info_log['crud_action']) {
                    case BrowsingLog::ACCESO:
                        if ($info_log['success']) {
                            $log_description = __('Inicio session en el sistema a las %s', array($info_log['date']));
                        } else {
                            $log_description = __('Trato de iniciar session en el sistema a las %s', array($info_log['date']));
                        }
                        break;
                    case BrowsingLog::ALTA:case BrowsingLog::ACTUALIZACION:case BrowsingLog::ELIMINACION:
                        switch ($info_log['model']) {
                            case Item::CONVEYOR:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' el transportador %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case Item::IMAGE:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' la imagen %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case Item::VIDEO:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' el video %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case Item::REPORT:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' el reporte %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case Item::FOLDER:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' el folder %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case Item::NOTE:
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' la nota %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                            case "Empresa":
                                $log_description = $crud_operations[$info_log['crud_action']] . __(' la empresa %s a las %s', array($info_log['item_name'], $info_log['date']));
                                break;
                        }
                        break;
                }

                $fecha = $this->Utilities->transformVisualFormatDate($info_log['date'], true);

                $log_data = '<div class="log-date">' . $fecha . '</div><div class="log-name" title="' . $assoc_user['name'] . '">' . $assoc_user['name'] . '</div><div class="log-description" title="' . $log_description . '">' . $log_description . '</div><div class="log-actions"><nav class="action-bar-accord visible"><ul class="action-list">' . $device_action . '</ul></nav><nav class="action-bar-accord"><ul class="action-list">' . $action_btns . '</ul></nav></div>';
                $locking_data .= '<div class = "accordionButton row-data">' . $log_data . '</div>';
            }
        } else {
            $locking_data .= '<div class = "accordionButton">' . __('No se encontraron resultados', true) . '</div>';
        }
        echo $locking_data;
    }

    public function printLockingLog($locking_log) {
        $locking_data = '';
        if (!empty($locking_log)) {
            foreach ($locking_log AS $log) {
                $info_log = $log['LockingLog'];
                $assoc_user = $log['UsuariosEmpresa'];
                $secureUserParams = $this->Utilities->encodeParams($info_log['user_id']);
                $secureCompanyParams = $this->Utilities->encodeParams($assoc_user['id_empresa']);
                $urlProfileCompany = $this->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlProfileUser = $this->Html->url(array('controller' => 'Users', 'action' => 'profile', $secureUserParams['item_id'], $secureUserParams['digest']));
                $urlUnlockUser = $this->Html->url(array('controller' => 'Users', 'action' => 'lockUnlock', $secureUserParams['item_id'], $secureUserParams['digest']));

                list($country, $country_code, $state, $state_code) = explode('|', $info_log['location']);
                $location = __('Ultimo login %s, %s', array($country, $state));
                $action_btns = '<li class="company profile-company-link" title="' . __("Empresa", true) . '" rel="' . $urlProfileCompany . '"></li>';
                $action_btns .= '<li class="user-profile" title="' . __("Perfil", true) . '"><a href="#modal" class="profile user-profile-link" alt="profile-dialog|callOpenUProfile|callUpdateDataTable" rel="' . $urlProfileUser . '"></a></li>';
                $action_btns .= '<li class="location location-link" title="' . $location . '" rel=""></li>';

                $textLock = $assoc_user['lock_status'] == UsuariosEmpresa::IS_UNLOCKED ? __("Usuario desbloqueado", true) : __("Desbloquear usuario", true);
                $opositeLock = $assoc_user['lock_status'] == UsuariosEmpresa::IS_UNLOCKED ? __("Desbloquear usuario", true) : __("Usuario desbloqueado", true);
                $class_lock = $assoc_user['lock_status'] == UsuariosEmpresa::IS_UNLOCKED ? 'unlocked' : 'locked lock-unlock-link';
                $lock_action = '<li class="' . $class_lock . '" assoc-user="locked-'.$info_log['user_id'].'" title="' . $textLock . '" rel="' . $urlUnlockUser . '"  alt="' . $opositeLock . '"></li>';
                
                $returnValue = preg_match_all('|\%(.*)\%|U', $info_log['reason'], $dinamicVals, PREG_PATTERN_ORDER);                
                $string2translate = preg_replace('|\%(.*)\%|U', '%s', $info_log['reason'], -1);
                /**Fix to not translated msgs **/
                $dinamicVals[0] = array();
                foreach ($dinamicVals[1] AS $dinVal){
                    $dinamicVals[0][] = __($dinVal, true);
                }
                $dinamicVals[1] = $dinamicVals[0];
                        
                $log_description = __($string2translate, $dinamicVals[1]);
                $fecha = $this->Utilities->transformVisualFormatDate($info_log['date'], true);

                $log_data = '<div class="log-date">' . $fecha . '</div><div class="log-name" title="' . $assoc_user['name'] . '">' . $assoc_user['name'] . '</div><div class="log-description" title="' . $log_description . '">' . $log_description . '</div><div class="log-actions"><nav class="action-bar-accord visible"><ul class="action-list">' . $lock_action . '</ul></nav><nav class="action-bar-accord"><ul class="action-list">' . $action_btns . '</ul></nav></div>';
                $locking_data .= '<div class = "accordionButton row-data">' . $log_data . '</div>';
            }
        } else {
            $locking_data .= '<div class = "accordionButton">' . __('No se encontraron resultados', true) . '</div>';
        }
        echo $locking_data;
    }

    public function printCommentsItem($comments_item) {
        $credentials = $this->_View->getVar('credentials');
        $confirmationMsg = __('¿Realmente desea eliminar el elemento seleccionado?', true);
        if (!empty($comments_item)) {
            foreach ($comments_item as $comment_item) {
                $comment = $comment_item['Comment'];
                $user_comment = $comment_item['UserEmpresa'];

                $edit_btn = '';
                $edit_ctrl = '';
                if($credentials['id']==$comment['owner_user_id']){
                    $secureCommentParams = $this->Utilities->encodeParams($comment['id']);
                    $urlSaveComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'updateCommentItem',$secureCommentParams['item_id'], $secureCommentParams['digest']));
                    $urlDeleteComment = $this->Html->url(array('controller' => 'Ajax', 'action' => 'deleteCommentItem',$secureCommentParams['item_id'], $secureCommentParams['digest']));
                    $edit_btn = '<a class="edit-comment"></a>';
                    $edit_btn .= '<a class="delete-comment delete-item-link" rel="'.$urlDeleteComment.'" conf-msg="'.$confirmationMsg.'"></a>';
                    $edit_ctrl = '<div class="fancy_textarea in-comment"><textarea>' . $comment['comment'] . '</textarea>';
                    $edit_ctrl .= '<button type="button" class="contiButton update-comment" rel="'.$urlSaveComment.'">'. __('Guardar', true).'</button>';
                    $edit_ctrl .= '<button type="button" class="contiButton cancel cancel-comment">'.__('Cancelar', true).'</button>';
                    $edit_ctrl .= '</div>';
                }


                $imagen_perfil = $this->_site;
                $imagen_perfil .= trim($user_comment['path_image']) != '' ? trim($user_comment['path_image']) : _DEFAULT_DD_USER_IMG;
                $imagen_perfil = '<img src="' . $imagen_perfil . '"/>';

                $fecha = $this->Utilities->transformVisualFormatDate($comment['date'], true);

                $comment_row = '<div class="comment-item item-dashboard">';
                $comment_row .= $edit_btn;
                $comment_row .= '<div class="image-user">' . $imagen_perfil . '</div>';
                $comment_row .= '<div class="content-comment"><h1>' . utf8_encode($user_comment['name']) . ' <br/> <span>' . $fecha . '</span></h1><p>' . $comment['comment'] . '</p>'.$edit_ctrl.'</div>';

                $comment_row .= '</div>';
                echo $comment_row;
            }
        }
    }
    
    public function printLogConveyor($log_rows){
        if(!empty($log_rows)){
            foreach($log_rows AS $log_row){
                $notificacion = $log_row['Notification'];
                $dinamicVals = null;

                $returnValue = preg_match_all('|\%(.*)\%|U', $notificacion['content'], $dinamicVals, PREG_PATTERN_ORDER);
                $string2translate = preg_replace('|\%(.*)\%|U', '%s', $notificacion['content'], -1);
                $notificacion['content'] = __($string2translate, $dinamicVals[1]);
                echo '<p><span>'.$notificacion['creation_date'].'</span>'.$notificacion['content'].'</p>';
            }
        }else{
            echo '<p>'.__('No existe actividad reciente',true).'</p>';
        }
    }

    public function printDatasheetBuoySystemPreview($folderApp) {
        $buoySystem = $folderApp['FolderApp'];
        $metadata = $folderApp['BsMetadata'];
        $empresa = $this->_View->getVar('company');

        $datasheet = "";
        $datasheet .= '<table class="datasheet-list">';
        $datasheet .= '<tr>';
        $datasheet .= '<td>'
            //. '<h1>' . __('Banda actual', true) . '</h1>'
            . '<div>'
            . '<ul>'
            . '<li><div>' . __('Project number', true) . '</div><div>' . $metadata['project_number'].'</div></li>'
            . '<li><div>' . __('Engineering name', true) . '</div><div>' . $metadata['engineering_name'].'</div></li>'
            . '<li><div>' . __('SB relative number', true) . '</div><div>' . (float)$metadata['sb_relative_numbers'].'</div></li>'
            . '<li><div>' . __('Client name', true) . '</div><div>' . $metadata['client_name'].'</div></li>'
            . '<li><div>' . __('Country code', true) . '</div><div>' . $metadata['country_code'].'</div></li>'
            . '<li><div>' . __('Field name', true) . '</div><div>' . $metadata['field_name'].'</div></li>'
            . '<li><div>' . __('Longitude (mN or deg)', true) . '</div><div>' . $metadata['longitude'].'</div></li>'
            . '<li><div>' . __('Originator / Design center', true) . '</div><div>' . $metadata['originator'].'</div></li>'
            . '<li><div>' . __('System function', true) . '</div><div>' . $metadata['system_function'].'</div></li>'
            . '<li><div>' . __('Mooring system', true) . '</div><div>' . $metadata['mooring_system'].'</div></li>'
            . '<li><div>' . __('Related nb on L/O terminal brochure', true) . '</div><div>' . $metadata['related_nb'].'</div></li>'
            . '<li><div>' . __('Tanker DWT (tons)', true) . '</div><div>' . (float)$metadata['tanker_dwt'].'</div></li>'
            . '<li><div>' . __('Product type', true) . '</div><div>' . $metadata['product_type'].'</div></li>'
            . '<li><div>' . __('Product throughput (bbls/d)', true) . '</div><div>' . (float)$metadata['product_throughput'].'</div></li>'
            . '<li><div>' . __('Anchor type', true) . '</div><div>' . $metadata['anchor_type'].'</div></li>'
            . '<li><div>' . __('Design load no.', true) . '</div><div>' . (float)$metadata['design_load'].'</div></li>'
            . '<li><div>' . __('Certifying authority', true) . '</div><div>' . $metadata['certifying_authority'].'</div></li>'
            . '<li><div>' . __('Present status', true) . '</div><div>' . $metadata['present_status'].'</div></li>'
            . '<li><div>' . __('Present location', true) . '</div><div>' . $metadata['present_location'].'</div></li>'
            . '<li><div>' . __('Present owner', true) . '</div><div>' . $metadata['present_owner'].'</div></li>'
            . '<li><div>' . __('General comments', true) . '</div><div>' . $metadata['general_comments'].'</div></li>'
            . '<li><div>' . __('Original system ref.', true) . '</div><div>' . $metadata['original_system'].'</div></li>'
            . '<li><div>' . __('Project Scope', true) . '</div><div>' . $metadata['project_scope'].'</div></li>'
            . '<li><div>' . __('Year of Contract', true) . '</div><div>' . $metadata['year_contract'].'</div></li>'
            . '<li><div>' . __('Latitude (mE or deg)', true) . '</div><div>' . $metadata['latitude'].'</div></li>'
            . '</ul>'
            . '</div>'
            . '</td>';

        $datasheet .= '<td>'
            //. '<h1>' . __('Material', true) . '</h1>'
            . '<div>'
            . '<ul>'
            . '<li><div>' . __('Number of products', true) . '</div><div>' . (float)$metadata['number_products'].'</div></li>'
            . '<li><div>' . __('Anchor weight (tons)', true) . '</div><div>' . (float)$metadata['anchor_weight'].'</div></li>'
            . '<li><div>' . __('Model tests', true) . '</div><div>' . $metadata['model_tests'].'</div></li>'
            . '<li><div>' . __('Revision date', true) . '</div><div>' . date("d/m/Y", strtotime($metadata['revision_date'])).'</div></li>'
            . '<li><div>' . __('Water depth (meters)', true) . '</div><div>' . (float)$metadata['water_depth'].'</div></li>'
            . '<li><div>' . __('Return period', true) . '</div><div>' . (float)$metadata['return_period'].'</div></li>'
            . '<li><div>' . __('Directional conditions for design', true) . '</div><div>' . $metadata['directional_conds'].'</div></li>'
            . '<li><div>' . __('Survival Hs (meters)', true) . '</div><div>' . (float)$metadata['survival_hs'].'</div></li>'
            . '<li><div>' . __('Operationg Hs (meters)', true) . '</div><div>' . (float)$metadata['operating_hs'].'</div></li>'
            . '<li><div>' . __('Period type', true) . '</div><div>' . $metadata['period_type'].'</div></li>'
            . '<li><div>' . __('Survival period (seconds)', true) . '</div><div>' . (float)$metadata['survival_period'].'</div></li>'
            . '<li><div>' . __('Operating period (seconds)', true) . '</div><div>' . (float)$metadata['operating_period'].'</div></li>'
            . '<li><div>' . __('Spectrum', true) . '</div><div>' . $metadata['spectrum'].'</div></li>'
            . '<li><div>' . __('Gamma factor', true) . '</div><div>' . $metadata['gamma_factor'].'</div></li>'
            . '<li><div>' . __('Survival 1-min Vw (m/s)', true) . '</div><div>' . (float)$metadata['survival_1min'].'</div></li>'
            . '<li><div>' . __('Operating 1-min Vw (m/s)', true) . '</div><div>' . (float)$metadata['operating_1min'].'</div></li>'
            . '<li><div>' . __('Survival Vc (0-m) (m/s)', true) . '</div><div>' . (float)$metadata['survival_vc'].'</div></li>'
            . '<li><div>' . __('Operating Vc (0-m) (m/s)', true) . '</div><div>' . (float)$metadata['operating_vc'].'</div></li>'
            . '<li><div>' . __('Dimensional case', true) . '</div><div>' . $metadata['dimensional_case'].'</div></li>'
            . '<li><div>' . __('Ice layer thickness (cm)', true) . '</div><div>' . (float)$metadata['ice_layer_thickness'].'</div></li>'
            . '<li><div>' . __('Tidal range', true) . '</div><div>' . (float)$metadata['tidal_range'].'</div></li>'
            . '<li><div>' . __('2 e tidal max (meters)', true) . '</div><div>' . (float)$metadata['tidal_max'].'</div></li>'
            . '<li><div>' . __('Expected Terminal occupancy (%)', true) . '</div><div>' . (float)$metadata['expected_occupancy'].'</div></li>'
            . '<li><div>' . __('Environment comments', true) . '</div><div>' . $metadata['environment_comments'].'</div></li>'
            . '</ul>'
            . '</div>'
            . '</td>';
        $datasheet .= '</tr>';
        $datasheet .= '</table>';

        echo $datasheet;
    }

    public function printDatasheetAssetPreview($assetFolder) {
        $buoySystem = $assetFolder['FolderApp'];
        $metadata = $assetFolder['AssetMetadata'];
        $empresa = $this->_View->getVar('company');
        $metadataFields = $this->_View->getVar('metadataFields');

        $datasheetRows = array_map(function ($value, $key) use ($metadataFields){
            if(isset($metadataFields[$key])){
                $value = is_numeric($value) ?  (float)$value : $value;
                return '<li><div>'.$metadataFields[$key]['label'].'</div><div>' . $value . '</div></li>';
            }
        }, $metadata, array_keys($metadata));

        $datasheet = "";
        $datasheet .= '<table class="datasheet-list">';
        $datasheet .= '<tr>';
        $datasheet .= '<td>'
            //. '<h1>' . __('Banda actual', true) . '</h1>'
            . '<div>'
            . '<ul>';
        $datasheet .= implode('',$datasheetRows);

        $datasheet .= '</ul>'
            . '</div>'
            . '</td>';

        
        $datasheet .= '</tr>';
        $datasheet .= '</table>';

        echo $datasheet;
    }

    public function printDatasheetAsset($assetFolder) {
        $buoySystem = $assetFolder['FolderApp'];
        $metadata = $assetFolder['AssetMetadata'];
        $empresa = $this->_View->getVar('company');
        $metadataFields = $this->_View->getVar('metadataFields');

        
        //$distribuidor = $this->_View->getVar('dealer');
        $role = $this->_View->getVar('role');
        $class_edit = !in_array($role, array(UsuariosEmpresa::IS_CLIENT)) && 1==2 ? 'edit-conveyor edit-conveyor-link' : '';

        $secureConveyorParams = $this->Utilities->encodeParams($buoySystem['id']);
        $urlUpdateItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'updateMetadataAsset', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));


        $datasheetRows = array_map(function ($value, $key) use ($metadataFields){
            if(isset($metadataFields[$key])){
                $value = is_numeric($value) ?  (float)$value : $value;
                return '<li>'.$metadataFields[$key]['label'].'<span title="' . $value . '">' . $value . '</span></li>';
            }
            
        }, $metadata, array_keys($metadata));

        $datasheet = '';
        $datasheet .= '<ul class="collapsible-panels datasheet-list">';
        $datasheet .= '<li class="first">'
            . '<h1>' . __('Data', true) . '<a href="#" class="'.$class_edit.'" title="' . __('Editar', true) . '" rel="' . $urlUpdateItem . '" assoc-c="' . $empresa['id'] . '" assoc-d="' . $empresa['parent'] . '" assoc-label="1" assoc-callback="callUpdateSite"></a></h1>'
            . '<div>'
            . '<ul>';

        $datasheet .= implode('',$datasheetRows);
           
        $datasheet .= '</ul>'
            . '</div>'
            . '</li>';
        $datasheet .= '</ul>';
        echo $datasheet;
    }

    public function printDatasheetBuoySystem($folderApp) {
        $buoySystem = $folderApp['FolderApp'];
        $metadata = $folderApp['BsMetadata'];
        $empresa = $this->_View->getVar('company');
        //$distribuidor = $this->_View->getVar('dealer');
        $role = $this->_View->getVar('role');
        $class_edit = !in_array($role, array(UsuariosEmpresa::IS_CLIENT)) && 1==2 ? 'edit-conveyor edit-conveyor-link' : '';

        $secureConveyorParams = $this->Utilities->encodeParams($buoySystem['id']);
        $urlUpdateItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'update', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));

        $datasheet = '';
        $datasheet .= '<ul class="collapsible-panels datasheet-list">';
        $datasheet .= '<li class="first">'
            . '<h1>' . __('Data', true) . '<a href="#" class="'.$class_edit.'" title="' . __('Editar', true) . '" rel="' . $urlUpdateItem . '" assoc-c="' . $empresa['id'] . '" assoc-d="' . $empresa['parent'] . '" assoc-label="1" assoc-callback="callUpdateSite"></a></h1>'
            . '<div>'
            . '<ul>'
            . '<li>' . __('Project number', true) . '<span title="' . $metadata['project_number'] . '">' . $metadata['project_number'] . '</span></li>'
            . '<li>' . __('Engineering name', true) . '<span title="' . $metadata['engineering_name'] . '">' . $metadata['engineering_name'] . '</span></li>'
            . '<li>' . __('SB relative number', true) . '<span title="' . (float)$metadata['sb_relative_numbers'] . '">' . (float)$metadata['sb_relative_numbers'] . '</span></li>'
            . '<li>' . __('Client name', true) . '<span title="' . $metadata['client_name'] . '">' . $metadata['client_name'] . '</span></li>'
            . '<li>' . __('Country code', true) . '<span title="' . $metadata['country_code'] . '">' . $metadata['country_code'] . '</span></li>'
            . '<li>' . __('Field name', true) . '<span title="' . $metadata['field_name'] . '">' . $metadata['field_name'] . '</span></li>'
            . '<li>' . __('Longitude (mN or deg)', true) . '<span title="' . $metadata['longitude'] . '">' . $metadata['longitude'] . '</span></li>'
            . '<li>' . __('Originator / Design center', true) . '<span title="' . $metadata['originator'] . '">' . $metadata['originator'] . '</span></li>'
            . '<li>' . __('System function', true) . '<span title="' . $metadata['system_function'] . '">' . $metadata['system_function'] . '</span></li>'
            . '<li>' . __('Mooring system', true) . '<span title="' . $metadata['mooring_system'] . '">' . $metadata['mooring_system'] . '</span></li>'
            . '<li>' . __('Related nb on L/O terminal brochure', true) . '<span title="' . $metadata['related_nb'] . '">' . $metadata['related_nb'] . '</span></li>'
            . '<li>' . __('Tanker DWT (tons)', true) . '<span title="' . (float)$metadata['tanker_dwt'] . '">' . (float)$metadata['tanker_dwt'] . '</span></li>'
            . '<li>' . __('Product type', true) . '<span title="' . $metadata['product_type'] . '">' . $metadata['product_type'] . '</span></li>'
            . '<li>' . __('Product throughput (bbls/d)', true) . '<span title="' . (float)$metadata['product_throughput'] . '">' . (float)$metadata['product_throughput'] . '</span></li>'
            . '<li>' . __('Anchor type', true) . '<span title="' . $metadata['anchor_type'] . '">' . $metadata['anchor_type'] . '</span></li>'
            . '<li>' . __('Design load no.', true) . '<span title="' . (float)$metadata['design_load'] . '">' . (float)$metadata['design_load'] . '</span></li>'
            . '<li>' . __('Certifying authority', true) . '<span title="' . $metadata['certifying_authority'] . '">' . $metadata['certifying_authority'] . '</span></li>'
            . '<li>' . __('Present status', true) . '<span title="' . $metadata['present_status'] . '">' . $metadata['present_status'] . '</span></li>'
            . '<li>' . __('Present location', true) . '<span title="' . $metadata['present_location'] . '">' . $metadata['present_location'] . '</span></li>'
            . '<li>' . __('Present owner', true) . '<span title="' . $metadata['present_owner'] . '">' . $metadata['present_owner'] . '</span></li>'
            . '<li>' . __('General comments', true) . '<span title="' . $metadata['general_comments'] . '">' . $metadata['general_comments'] . '</span></li>'
            . '<li>' . __('Original system ref.', true) . '<span title="' . $metadata['original_system'] . '">' . $metadata['original_system'] . '</span></li>'
            . '<li>' . __('Project Scope', true) . '<span title="' . $metadata['project_scope'] . '">' . $metadata['project_scope'] . '</span></li>'
            . '<li>' . __('Year of Contract', true) . '<span title="' . $metadata['year_contract'] . '">' . $metadata['year_contract'] . '</span></li>'
            . '<li>' . __('Latitude (mE or deg)', true) . '<span title="' . $metadata['latitude'] . '">' . $metadata['latitude'] . '</span></li>'
            . '<li>' . __('Number of products', true) . '<span title="' . (float)$metadata['number_products'] . '">' . (float)$metadata['number_products'] . '</span></li>'
            . '<li>' . __('Anchor weight (tons)', true) . '<span title="' . (float)$metadata['anchor_weight'] . '">' . (float)$metadata['anchor_weight'] . '</span></li>'
            . '<li>' . __('Model tests', true) . '<span title="' . $metadata['model_tests'] . '">' . $metadata['model_tests'] . '</span></li>'
            . '<li>' . __('Revision date', true) . '<span title="' . date("d/m/Y", strtotime($metadata['revision_date'])) . '">' . date("d/m/Y", strtotime($metadata['revision_date'])) . '</span></li>'
            . '<li>' . __('Water depth (meters)', true) . '<span title="' . (float)$metadata['water_depth'] . '">' . (float)$metadata['water_depth'] . '</span></li>'
            . '<li>' . __('Return period', true) . '<span title="' .(float) $metadata['return_period'] . '">' . (float)$metadata['return_period'] . '</span></li>'
            . '<li>' . __('Directional conditions for design', true) . '<span title="' . $metadata['directional_conds'] . '">' . $metadata['directional_conds'] . '</span></li>'
            . '<li>' . __('Survival Hs (meters)', true) . '<span title="' . (float)$metadata['survival_hs'] . '">' . (float)$metadata['survival_hs'] . '</span></li>'
            . '<li>' . __('Operationg Hs (meters)', true) . '<span title="' . (float)$metadata['operating_hs'] . '">' . (float)$metadata['operating_hs'] . '</span></li>'
            . '<li>' . __('Period type', true) . '<span title="' . $metadata['period_type'] . '">' . $metadata['period_type'] . '</span></li>'
            . '<li>' . __('Survival period (seconds)', true) . '<span title="' . (float)$metadata['survival_period'] . '">' . (float)$metadata['survival_period'] . '</span></li>'
            . '<li>' . __('Operating period (seconds)', true) . '<span title="' . (float)$metadata['operating_period'] . '">' . (float)$metadata['operating_period'] . '</span></li>'
            . '<li>' . __('Spectrum', true) . '<span title="' . $metadata['spectrum'] . '">' . $metadata['spectrum'] . '</span></li>'
            . '<li>' . __('Gamma factor', true) . '<span title="' . $metadata['gamma_factor'] . '">' . $metadata['gamma_factor'] . '</span></li>'
            . '<li>' . __('Survival 1-min Vw (m/s)', true) . '<span title="' . (float)$metadata['survival_1min'] . '">' . (float)$metadata['survival_1min'] . '</span></li>'
            . '<li>' . __('Operating 1-min Vw (m/s)', true) . '<span title="' . (float)$metadata['operating_1min'] . '">' . (float)$metadata['operating_1min'] . '</span></li>'
            . '<li>' . __('Survival Vc (0-m) (m/s)', true) . '<span title="' . (float)$metadata['survival_vc'] . '">' . (float)$metadata['survival_vc'] . '</span></li>'
            . '<li>' . __('Operating Vc (0-m) (m/s)', true) . '<span title="' . (float)$metadata['operating_vc'] . '">' . (float)$metadata['operating_vc'] . '</span></li>'
            . '<li>' . __('Dimensional case', true) . '<span title="' . $metadata['dimensional_case'] . '">' . $metadata['dimensional_case'] . '</span></li>'
            . '<li>' . __('Ice layer thickness (cm)', true) . '<span title="' . (float)$metadata['ice_layer_thickness'] . '">' . (float)$metadata['ice_layer_thickness'] . '</span></li>'
            . '<li>' . __('Tidal range', true) . '<span title="' . (float)$metadata['tidal_range'] . '">' . (float)$metadata['tidal_range'] . '</span></li>'
            . '<li>' . __('2 e tidal max (meters)', true) . '<span title="' . (float)$metadata['tidal_max'] . '">' . (float)$metadata['tidal_max'] . '</span></li>'
            . '<li>' . __('Expected Terminal occupancy (%)', true) . '<span title="' . (float)$metadata['expected_occupancy'] . '">' . (float)$metadata['expected_occupancy'] . '</span></li>'
            . '<li>' . __('Environment comments', true) . '<span title="' . $metadata['environment_comments'] . '">' . $metadata['environment_comments'] . '</span></li>'
          
            . '</ul>'
            . '</div>'
            . '</li>';
        $datasheet .= '</ul>';
        echo $datasheet;
    }

    public function printSmartFolderItems($conveyor_items, $folder_id) {
        $conveyor = $this->_View->getVar('conveyor');
        $secureConveyorParams = $this->Utilities->encodeParams($conveyor['id']);
        $secureFolderParams = $this->Utilities->encodeParams($folder_id);

        $urlAddItemConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addItemConveyor', $secureConveyorParams['item_id'], $secureConveyorParams['digest'], $secureFolderParams['item_id'], $secureFolderParams['digest']));
        $conveyors_data = '';

        $imageTitle = __('Fotos', true);
        $videoTitle = __('Videos', true);
        if (!empty($conveyor_items)) {
            foreach ($conveyor_items AS $items) {
                $conveyors_data .= '<div class="smart-row-item">';
                foreach ($items AS $conveyor_item) {
                    $conveyorItem = $conveyor_item['ConveyorItem'];
                    $fecha_actualizacion = $this->Utilities->timestampToCorrectFormat($conveyorItem['actualizada'], '/');
                    switch ($conveyorItem['type_item']) {
                        case Item::NOTE:
                            $conveyorItem['contenido'] = stripslashes($conveyorItem['contenido']);
                            $conveyors_data .= '<div>' . $conveyorItem['contenido'] . '</div>';
                            break;
                        case Item::IMAGE:
                            if ($imageTitle != '') {
                                $conveyors_data .= '<h1>' . $imageTitle . '</h1>';
                                $imageTitle = '';
                            }
                            $conveyors_data .= '<div class="image">';
                            if (trim($conveyorItem['path']) != '') {
                                $conveyors_data .= '<img src="' . $this->_site . $conveyorItem['path'] . '"/>';
                            }
                            $conveyors_data .= '<p class="title">' . $conveyorItem['nombre'] . '</p>';
                            $conveyors_data .='</div>';
                            break;
                        case Item::VIDEO:
                            if ($videoTitle != '') {
                                $conveyors_data .= '<h1>' . $videoTitle . '</h1>';
                                $videoTitle = '';
                            }
                            $conveyors_data .= '<div class="image"><div class="play-indicator"></div>';
                            if (trim($conveyorItem['thumbnail_path']) != '') {
                                $conveyors_data .= '<img src="' . $this->_site . $conveyorItem['thumbnail_path'] . '"/>';
                            }else{
                                $conveyors_data .= '<img src="' . $this->_site . 'img/gallery/thumbnail_video242x125_black.gif"/>';
                            }
                            $conveyors_data .= '<p class="title">' . $conveyorItem['nombre'] . '</p>';
                            $conveyors_data .='</div>';
                            break;
                    }
                }
                $conveyors_data .= '</div>';
            }
        }

        echo $conveyors_data;
    }

/**
 * Mostrar la informacion de los folders
 *
 * @param [type] $conveyor_items
 * @param [type] $folder_id
 * @return void
 */
    public function printGraphicFolderItems($conveyor_items, $folder_id) {
        //$conveyor = $this->_View->getVar('folder');
        $role = $this->_View->getVar('role');
        
        $itemFolderShowed = $this->_View->getVar('folder');
        $metadataBs = $itemFolderShowed['FolderApp']['type']==='buoy_system' && !is_null($itemFolderShowed['BsMetadata']['id']) ? $itemFolderShowed['BsMetadata'] : null;
        $metadataAsset = $itemFolderShowed['FolderApp']['is_asset_folder'] && !is_null($itemFolderShowed['AssetMetadata']['id']) ? $itemFolderShowed['AssetMetadata'] : null;
        $itemFolderShowed = $itemFolderShowed['FolderApp'];
        $site = $this->_View->getVar('site');

        
        $secureFolderParams = $this->Utilities->encodeParams($itemFolderShowed['id']); 

        $urlAddItemFolder = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'addItemFolder', $secureFolderParams['item_id'], $secureFolderParams['digest']));
        
        $conveyors_data = '<ul class="dashboard-list items-dashboard">';

        //read permissions role user
        $addFolderAllow = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
       
        $viewFilesAllow = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('view', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $downloadFileAllow = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('download', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $downloadNormalReportAllow = isset($this->_credentials['permissions'][IElement::Is_Report]) && in_array('download', $this->_credentials['permissions'][IElement::Is_Report]['allows']) ? true : false;
        $downloadSmartReportAllow = isset($this->_credentials['permissions'][IElement::Is_SmartReport]) && in_array('download', $this->_credentials['permissions'][IElement::Is_SmartReport]['allows']) ? true : false;

        $folderOption = $addFolderAllow && $itemFolderShowed['allow_assets'] ? '<li><a href="#" alt="' . Item::FOLDER . '" class="add-folder add-mediaitem-conveyor-link" location-tool="n" dialog-style="folder-dialog" rel="' . $urlAddItemFolder . '" title="' . __('Folder', true) . '"></a></li>':'';

/*
        if($addFolderAllow && $itemFolderShowed['allow_assets']){

            $conveyors_data .= '<li class="circular-menu add-item-dashboard-circular" data-section="9" data-intro="'.__("tutorial_agregar_elemento_carpeta",true).'" data-position="bottom">'
                . '<ul>';
            $conveyors_data .= $folderOption;
            $conveyors_data .='</ul>'
                . '<button class="add-button" title="' . __("add_item_conveyor", true) . '"></button>'
                . '</li>';
        }*/

        $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
        $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
        //var_dump($canDeleteItem);
        if(!is_null($metadataBs) || !is_null($metadataAsset)){
            $uniqueIdTagFolder = '';
            if(!is_null($metadataBs)){
                $urlDownloadDatasheet = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'downloadDatasheet', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $urlUpdateConveyor = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'updateMetadata', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $urlDatasheetConveyor = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'dataSheet', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $fecha_actualizacion_ficha = $this->Utilities->timestampToUsDate($metadataBs['created_at']);
                $title = __('Metadata Buoy Project', true);
            }else{
                $urlDownloadDatasheet = $this->Html->url(array('controller' => 'BuoySystems', 'action' => 'downloadDatasheetAsset', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $urlUpdateConveyor = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'updateMetadataAsset', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $urlDatasheetConveyor = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'dataSheetAsset', $secureFolderParams['item_id'], $secureFolderParams['digest']));
                $fecha_actualizacion_ficha = $this->Utilities->timestampToUsDate($metadataAsset['created_at']);
                $title = __('Metadata %s', $itemFolderShowed['name']);
                $uniqueIdTagFolder = __('Unique id: %s', $metadataAsset['unique_id_tag']);
            }
            

            $actions_item = '<ul class="actions-item-dashboard">';
            
            if($canEditItem){
                $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-conveyor-link" title="' . __('Editar', true) . '" rel="' . $urlUpdateConveyor . '" assoc-c="1" assoc-d="2" assoc-callback="callUpdateSite" assoc-callback-open="metadataUpdate"></a></li>';
            }
            
            $actions_item .= '<li><a href="#" target-link="_blank" class="download-item-dashboard item-dashboard-link" title="' . __('Generar PDF', true) . '" rel="' . $urlDownloadDatasheet . '"></a></li>';
            $actions_item .= '</ul>';

            $conveyors_data .= '<li class="item-dashboard details-item-dashboard item-dashboard-link" rel="' . $urlDatasheetConveyor . '" alt="">' . $actions_item . '<div>';
            $conveyors_data .= '<p class="title">' . $title . '</p>';
            $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion_ficha . '</p>';
            if($uniqueIdTagFolder != ''){
                    $conveyors_data .= '<p class="normal-text">' . $uniqueIdTagFolder . '</p>';
            }
            $conveyors_data .= '</div></li>';
        }

        if (!empty($conveyor_items)) {
            foreach ($conveyor_items AS $conveyor_item) {
                $uniqueIdTagFolder = is_null($conveyor_item['AssetMetadata']['unique_id_tag']) ? '' : __('Unique id: %s', $conveyor_item['AssetMetadata']['unique_id_tag']);
                $conveyorItem = $conveyor_item['FolderApp'];

                $secureItemConveyor = $this->Utilities->encodeParams($conveyorItem['id']);
                $urlViewItemConveyor = $this->Html->url(array('controller' => '/', 'action' => '/buoy/data/', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                $urlEditItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'editItem', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                $urlEditAsset = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'editAssetFolder', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                $urlRemoveItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'remove', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                $urlToggleItemSmartview = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'toggleItemSmartview', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                $urlTogglePrivateFolder = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'togglePrivateFolder', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));

                $media_item = '';
                $class_item = '';
                $confirmMsg = '';
                $item_link = 'item-dashboard-link';
                $image_lightbox_option = '';
                $smarview_selector = '';

                $private_selector = "";
                $confirmMsg = __('Realmente desea eliminar el folder seleccionado. Los elementos en el folder seran inaccesibles', $conveyorItem['name']);

                $actions_item = '<ul class="actions-item-dashboard">';
                if ($canEditItem && $itemFolderShowed['allow_assets'] && $this->_credentials['i_group_id'] >= IGroup::CLIENT_MANAGER) {
                    $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-item-link" title="' . __('Editar', true) . '" rel="' . $urlEditAsset . '"></a></li>';
                }

                //if ($canDeleteItem) {
                if ($canDeleteItem && ($conveyorItem['is_asset_folder'] || $conveyorItem['is_file_folder'])) {
                    $actions_item .= '<li><a href="#" class="delete-item-dashboard delete-item-link" title="' . __('Eliminar', true) . '" rel="' . $urlRemoveItem . '" conf-msg="' . $confirmMsg . '"></a></li>';
                }
                
                $actions_item .= $image_lightbox_option;
                $actions_item .= '</ul>';

                $fecha_actualizacion = $this->Utilities->timestampToUsDate($conveyorItem['updated_at']);
                

                $class_folder = isset($conveyorItem['color']) ? 'custom-color-folder' : '';
                $folder_color_icon = isset($conveyorItem['color']) ? '<div class="folder-color" style="background-color: '.$conveyorItem['color'].'"></div>' : '';
                $content_item = '';
                $conveyors_data .= '<li class="item-dashboard item-in-folder folder-item ' . $item_link . ' ' . $class_folder . '" rel="' . $urlViewItemConveyor . '">' .$folder_color_icon. $actions_item . '<div>';
                $conveyors_data .= $media_item;
                $conveyors_data .= '<p class="title">' . $conveyorItem['name'] . '</p>';
                $conveyors_data .= '<p class="normal-text">' . $content_item . '</p>';
                $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion . '</p>';
                if($uniqueIdTagFolder != ''){
                    $conveyors_data .= '<p class="normal-text">' . $uniqueIdTagFolder . '</p>';
                }
              
                $conveyors_data .= '</div></li>';
            }
        }
        else{
            $conveyors_data = '<ul class="empty-list">';
            $conveyors_data .= '<li><div><h1>Empty folder</h1><h2>Start to add assets</h2></div></li>';
        }
        $conveyors_data .= '</ul>';
        echo $conveyors_data;
    }

    public function printGraphicConveyorItems($conveyor_items) {
        $conveyor = $this->_View->getVar('conveyor');
        $has_failed_date = $this->_View->getVar('has_failed_date');
        $company = $this->_View->getVar('company');
        $ultrasonic = $this->_View->getVar('ultrasonic');
        $ultrasonic_readings = $this->_View->getVar('ultrasonic_readings');
        $dealer = $this->_View->getVar('dealer');
        $site = $this->_View->getVar('site');
        $role = $this->_View->getVar('role');

        $secureConveyorParams = $this->Utilities->encodeParams($conveyor['id']);


        $viewGaugeConveyor = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'gauge', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        $urlAddItemConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addItemConveyor', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        $urlAddConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'add'));
        $addFileUrl = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addFileConveyor', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));



        $conveyors_data = '';
        if($has_failed_date){
            $conveyors_data .= '<div class="disclaimer-pages"><div class="alert-box notice closable">'.__('An installed belt failure date was registered, we suggest you export the data to History.',true).'</div></div>';
        }

        $conveyors_data .= '<ul class="dashboard-list items-dashboard">';


        $canEditConveyor = isset($this->_credentials['permissions'][IElement::Is_Conveyor]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
        $canDeleteConveyor = isset($this->_credentials['permissions'][IElement::Is_Conveyor]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;

        //read permissions role user
        $addPhotoAllow = isset($this->_credentials['permissions'][IElement::Is_Photo]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Photo]['allows']) ? true : false;
        $addVideoAllow = isset($this->_credentials['permissions'][IElement::Is_Video]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Video]['allows']) ? true : false;
        $addFolderAllow = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
        $addReportAllow = isset($this->_credentials['permissions'][IElement::Is_Report]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Report]['allows']) ? true : false;
        $addNoteAllow = isset($this->_credentials['permissions'][IElement::Is_Note]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Note]['allows']) ? true : false;
        $addFileAllow = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('add', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;

        $viewFilesAllow = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('view', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $viewUltrasonicGaugeAllow = isset($this->_credentials['permissions'][IElement::Is_UltrasonicWithGauge]) && in_array('view', $this->_credentials['permissions'][IElement::Is_UltrasonicWithGauge]['allows']) ? true : false;
        $downloadUltrasonicReportAllow = isset($this->_credentials['permissions'][IElement::Is_UltrasonicReport]) && in_array('download', $this->_credentials['permissions'][IElement::Is_UltrasonicReport]['allows']) ? true : false;
        $downloadFileAllow = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('download', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
        $downloadReportAllow = isset($this->_credentials['permissions'][IElement::Is_Report]) && in_array('download', $this->_credentials['permissions'][IElement::Is_Report]['allows']) ? true : false;

        $downloadTechnicalDataAllow = isset($this->_credentials['permissions'][IElement::Is_TechnicalData]) && in_array('download', $this->_credentials['permissions'][IElement::Is_TechnicalData]['allows']) ? true : false;

        //if can add some element
        if($addPhotoAllow || $addVideoAllow || $addFolderAllow || $addReportAllow || $addNoteAllow || $addFileAllow){
        $conveyors_data .= '<li class="circular-menu add-item-dashboard-circular" data-section="7" data-intro="'.__("tutorial_agregar_elemento_vista_banda",true).'" data-position="right">'
                . '<ul>';
        $conveyors_data .= $addPhotoAllow ? '<li><a href="#" alt="' . Item::IMAGE . '" class="add-image add-mediaitem-conveyor-link" location-tool="s" dialog-style="photo-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Foto', true) . '"></a></li>' : '';
        $conveyors_data .= $addVideoAllow ? '<li><a href="#" alt="' . Item::VIDEO . '" class="add-video add-mediaitem-conveyor-link" location-tool="s" dialog-style="video-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Video', true) . '"></a></li>' : '';
        $conveyors_data .= $addReportAllow ? '<li><a href="#" alt="' . Item::REPORT . '" class="add-report add-mediaitem-conveyor-link" location-tool="n" dialog-style="report-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Reporte', true) . '"></a></li>' : '';
        $conveyors_data .= $addNoteAllow ? '<li><a href="#" alt="' . Item::NOTE . '" class="add-note add-mediaitem-conveyor-link" location-tool="n" dialog-style="note-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Nota', true) . '"></a></li>' : '';
        $conveyors_data .= $addFolderAllow ? '<li><a href="#" alt="' . Item::FOLDER . '" class="add-folder add-mediaitem-conveyor-link" location-tool="n" dialog-style="folder-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Folder', true) . '"></a></li>' : '';
        $conveyors_data .= $addFolderAllow ? '<li><a href="#" alt="' . Item::FOLDERYEAR . '" class="add-folder-year add-mediaitem-conveyor-link" location-tool="n" dialog-style="folder-year-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('folder_anio', true) . '"></a></li>': '';
        $conveyors_data .= $addFileAllow ? '<li><a href="#" alt="' . Item::FILE . '" class="add-new-file add-mediaitem-conveyor-link add-file-conveyor" location-tool="n" dialog-style="file-dialog" rel="' . $addFileUrl . '" title="' . __('new_file', true) . '"></a></li>':'';
        $conveyors_data .=  '</ul>'
                . '<button class="add-button" title="' . __("add_item_conveyor", true) . '"></button>'
                . '</li>';
        }



                /*. '<li class="add-item-dashboard multiple-add add-conveyor-link" data-section="7" data-intro="'.__("tutorial_agregar_elemento_vista_banda",true).'" data-position="right">'
                . '<ul>'
                . '<li><a href="#" alt="' . Item::IMAGE . '" class="add-image add-mediaitem-conveyor-link" location-tool="s" dialog-style="photo-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Foto', true) . '"></a></li>'
                . '<li><a href="#" alt="' . Item::VIDEO . '" class="add-video add-mediaitem-conveyor-link" location-tool="s" dialog-style="video-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Video', true) . '"></a></li>'
                . '<li><a href="#" alt="' . Item::REPORT . '" class="add-report add-mediaitem-conveyor-link" location-tool="n" dialog-style="report-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Reporte', true) . '"></a></li>'
                . '<li><a href="#" alt="' . Item::FOLDER . '" class="add-folder add-mediaitem-conveyor-link" location-tool="n" dialog-style="folder-dialog" rel="' . $urlAddItemConveyor . '" title="' . __('Folder', true) . '"></a></li>'
                . '</ul>'
                . '</li>';*/
        

        //FICHA TECNICA

        
        //$fecha_actualizacion_ultra = !is_null($ultrasonic['id']) ? $this->Utilities->timestampToCorrectFormatLanguage($ultrasonic['updated_at']) : '-';
        $fecha_actualizacion_ultra = !is_null($ultrasonic['id']) ? $this->Utilities->timestampToUsDate($ultrasonic['updated_at']) : '-';

        //$fecha_actualizacion_ficha = $this->Utilities->timestampToCorrectFormatLanguage($conveyor['actualizada']);
        $fecha_actualizacion_ficha = $this->Utilities->timestampToUsDate($conveyor['actualizada']);
        $urlDatasheetConveyor = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'dataSheet', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        $urlUpdateConveyor = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'update', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));


        $actions_item = '<ul class="actions-item-dashboard">';
        $urlDownloadDatasheet = "";
        if($this->_credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT)) && $canEditConveyor) {
            $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-conveyor-link" title="' . __('Editar', true) . '" rel="' . $urlUpdateConveyor . '" assoc-c="' . $company['id'] . '"  assoc-d="' . $dealer['id'] . '" assoc-callback="callUpdateSite"></a></li>';
        }

        if(!$conveyor["is_us_conveyor"]) {
            $urlDownloadDatasheet = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'downloadDatasheet', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        }else{
            $urlDownloadDatasheet = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'downloadDatasheetUs', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        }
        if($downloadTechnicalDataAllow):
            $actions_item .= '<li><a href="#" target-link="_blank" class="download-item-dashboard item-dashboard-link" title="' . __('Generar PDF', true) . '" rel="' . $urlDownloadDatasheet . '"></a></li>';
        endif;
        $actions_item .= '</ul>';

        $conveyors_data .= '<li class="item-dashboard details-item-dashboard item-dashboard-link" rel="' . $urlDatasheetConveyor . '" alt="">' . $actions_item . '<div>';
        $conveyors_data .= '<p class="title">' . __('Ficha tecnica', true) . '</p>';
        $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion_ficha . '</p>';
        $conveyors_data .= '</div></li>';
       
        
        //ULTRASONIC    
        $urlUltrasonicConveyor = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'ultrasonic', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
        //$ultrasonic_activate = floatval($ultrasonic['original_top_cover'])<=0 || floatval($ultrasonic['conveyor_price'])<=0 || floatval($ultrasonic['durometer_new_belt'])<= 0 ? false : true;
        $has_ultrasonic = !is_null($ultrasonic_readings['ultrasonic_id']);
        $actions_item = '<ul class="actions-item-dashboard">';
        $class_ultra = '';
        if($has_ultrasonic) {
            $class_ultra = $viewUltrasonicGaugeAllow ? 'with-reading':'';
            $urlDownloadUltrasonicData = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'downloadUltrasonicConveyorData', $secureConveyorParams['item_id'], $secureConveyorParams['digest']));
            //if ($this->_credentials['group_id'] > Group::MANAGER) {
            if($downloadUltrasonicReportAllow) {
                //$actions_item .= '<li><a href="#" target-link="_blank" class="generic-download-item-dashboard item-dashboard-link" title="' . __('Descargar', true) . '" rel="' . $urlDownloadUltrasonicData . '"></a></li>';

            }
        }
        $actions_item .= '</ul>';

        if($viewUltrasonicGaugeAllow || $downloadUltrasonicReportAllow){
            $conveyors_data .= '<li class="item-dashboard ultrasonic-item-dashboard item-dashboard-link '.$class_ultra.'" rel="' . $urlUltrasonicConveyor . '" data-conveyor="'.$conveyor['id'].'" data-gauge="'.$viewGaugeConveyor.'" alt="">' . $actions_item . '<div>';
            $conveyors_data .= '<p class="title">' . __('Ultrasonic', true) . '</p>';
            $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion_ultra . '</p>';
            $conveyors_data .= '</div></li>';
        }

        $urlInspectionConveyor = $this->_View->Html->url('/conveyor/inspections/'.$secureConveyorParams['item_id'].'/'.$secureConveyorParams['digest']);
        $inspections_data = '<li class="item-dashboard inspection-item-dashboard item-dashboard-link" rel="' . $urlInspectionConveyor . '" alt="">' . $actions_item . '<div>';
        $inspections_data .= '<p class="title">' . __('Inspections', true) . '</p>';
        $inspections_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion_ultra . '</p>';
        $inspections_data .= '</div></li>';

        $conveyors_data .= $inspections_data;

        if (!empty($conveyor_items)) {
            foreach ($conveyor_items AS $conveyor_item) {
                $conveyorItem = $conveyor_item['ConveyorItem'];

                if(($conveyorItem['type_item']==Item::FILE && $viewFilesAllow) || $conveyorItem['type_item']!=Item::FILE) {


                    $secureItemConveyor = $this->Utilities->encodeParams($conveyorItem['id']);
                    $uniqid_item_dropped_item = $conveyorItem['type_item'] . '@' . $secureItemConveyor['item_id'] . '@' . $secureItemConveyor['digest'];
                    $urlEditItem = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'editItem', $conveyorItem['type_item'], $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                    $urlRemoveItem = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'removeItem', $conveyorItem['type_item'], $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                    $urlViewItemConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'Item', $conveyorItem['type_item'], $secureItemConveyor['item_id'], $secureItemConveyor['digest']));
                    $urlTogglePrivateFolder = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'togglePrivateFolder', $conveyorItem['type_item'], $secureItemConveyor['item_id'], $secureItemConveyor['digest']));

                    $media_item = '';
                    $class_item = '';
                    $target = '';
                    $confirmMsg = '';
                    $image_lightbox_option = '';
                    $item_link = 'item-dashboard-link';

                    $canEditItem = $canDeleteItem = false;
                    $private_selector = "";
                    switch ($conveyorItem['type_item']) {
                        case Item::IMAGE:
                            $image_lightbox_option = '<li><a href="' . $site . $conveyorItem['path'] . '" class="preview-item-dashboard preview-item-link" title="' . __('Previsualizar', true) . '"></a></li>';
                            $cover_img = $conveyor['cover_img'] == $conveyorItem['id'] ? '<div class="is-cover-img"></div>' : '';
                            $confirmMsg = __('Realmente desea eliminar la imagen seleccionada', $conveyorItem['name_item']);
                            $class_item = 'image-item';
                            $media_item = '<div>';
                            if (trim($conveyorItem['path']) != '') {

                                //$media_item .= $cover_img.'<img src="' . $this->_site . $conveyorItem['path'] . '"/>';
                                $media_item .= $cover_img . $this->ImageSize->resize($conveyorItem['path'], 130, 100);
                            }
                            $media_item .= '</div>';

                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Photo]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Photo]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Photo]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Photo]['allows']) ? true : false;
                            break;
                        case Item::VIDEO:
                            $confirmMsg = __('Realmente desea eliminar el video seleccionado', $conveyorItem['name_item']);
                            $class_item = 'video-item';
                            $media_item = '<div><div class="play-indicator"></div>';
                            if (trim($conveyorItem['thumbnail_path']) != '') {
                                $media_item .= '<img src="' . $this->_site . $conveyorItem['thumbnail_path'] . '"/>';
                            } else {
                                $media_item .= '<img src="' . $this->_site . 'img/gallery/thumbnail_video242x125_black.gif"/>';
                            }
                            $media_item .= '</div>';

                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Video]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Video]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Video]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Video]['allows']) ? true : false;
                            break;
                        case Item::FOLDER:
                            $class_private = $this->_credentials['i_group_id']<IGroup::ADMIN && $conveyorItem["is_private"] ? " hidden" : "";
                            $confirmMsg = __('Realmente desea eliminar el folder seleccionado. Los elementos en el folder seran inaccesibles', $conveyorItem['name_item']);
                            $class_item = $conveyorItem['is_folder_year'] ? 'folder-year-item' : 'folder-item';
                            $class_item .= $class_private;
                            //$urlConveyorItem = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'addItemToFolder', $secureItemConveyor['item_id'], $secureItemConveyor['digest']));

                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Folder]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Folder]['allows']) ? true : false;

                            if($this->_credentials['i_group_id']>=IGroup::ADMIN){
                                $private_selector = $conveyorItem["is_private"] ? "active" : "";
                                $private_selector = '<ul class="actions-item-dashboard left active"><li><a href="#" class="checkbox-item ' . $private_selector . ' toggle-private-link" title="'.__("Private folder",true).'" rel="' . $urlTogglePrivateFolder . '"></a></li></ul>';
                            }

                            break;
                        case Item::REPORT:
                            $confirmMsg = __('Realmente desea eliminar el reporte seleccionado', $conveyorItem['name_item']);
                            $class_item = 'report-item';
                            $target = '_blank';

                            $item_link = $downloadReportAllow ? $item_link : 'not-allowed';

                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Report]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Report]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Report]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Report]['allows']) ? true : false;
                            break;
                        case Item::FILE:
                            $confirmMsg = __('Realmente desea eliminar el archivo seleccionado', $conveyorItem['name_item']);
                            $class_item = 'file-item';
                            $target = '_blank';

                            $item_link = $downloadFileAllow ? $item_link : 'not-allowed';
                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_File]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_File]['allows']) ? true : false;
                            break;
                        case Item::NOTE:
                            $confirmMsg = __('Realmente desea eliminar la nota seleccionada', $conveyorItem['name_item']);
                            $class_item = 'note-item';

                            $urlViewItemConveyor = $urlEditItem;
                            $item_link = 'edit-item-link';

                            $canEditItem = isset($this->_credentials['permissions'][IElement::Is_Note]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Note]['allows']) ? true : false;
                            $canDeleteItem = isset($this->_credentials['permissions'][IElement::Is_Note]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Note]['allows']) ? true : false;
                            break;
                    }


                    $actions_item = '<ul class="actions-item-dashboard">';
                    //if($this->_credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT))) {
                    if ($canEditItem && $conveyor['cover_img'] != $conveyorItem['id'] && !$conveyorItem['is_folder_year']) {
                        $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-item-link" title="' . __('Editar', true) . '" rel="' . $urlEditItem . '"></a></li>';
                    }
                    if ($canDeleteItem) {
                        $actions_item .= '<li><a href="#" class="delete-item-dashboard delete-item-link" title="' . __('Eliminar', true) . '" rel="' . $urlRemoveItem . '" conf-msg="' . $confirmMsg . '"></a></li>';
                    }

                    $actions_item .= $image_lightbox_option;
                    $actions_item .= '</ul>';

                    $actions_item .= $private_selector;
                    /*
                                    $fecha_actualizacion = $this->Utilities->timestampToCorrectFormat($conveyorItem['updated_item'], '/');
                                    $conveyors_data .= '<li class="item-dashboard ' . $class_item . ' item-dashboard-link" rel="' . $urlViewItemConveyor . '" item-info="' . $uniqid_item_dropped_item . '" target-link="' . $target . '">' . $actions_item . '<div>';
                                    $conveyors_data .= $media_item;
                                    $conveyors_data .= '<p class="title">' . $conveyorItem['name_item'] . '</p>';
                                    $conveyors_data .= '<p class="normal-text">' . $conveyorItem['desc_item'] . '</p>';
                                    $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion . '</p>';*/


                    //$fecha_actualizacion = $this->Utilities->timestampToCorrectFormatLanguage($conveyorItem['updated_item']);
                    $fecha_actualizacion = $this->Utilities->timestampToUsDate($conveyorItem['updated_item']);
                    $conveyors_data .= '<li class="item-dashboard ' . $class_item . ' ' . $item_link . '" rel="' . $urlViewItemConveyor . '" item-info="' . $uniqid_item_dropped_item . '" target-link="' . $target . '">' . $actions_item . '<div>';
                    $conveyors_data .= $media_item;
                    $conveyors_data .= '<p class="title">' . $conveyorItem['name_item'] . '</p>';
                    if ($conveyorItem['type_item'] == Item::IMAGE || $conveyorItem['type_item'] == Item::VIDEO) {
                        //$fecha_visita = $this->Utilities->timestampToCorrectFormatLanguage($conveyorItem['taken_at']);
                        $fecha_visita = $conveyorItem['taken_at'] != '0000-00-00 00:00:00' ? $this->Utilities->timestampToUsDate($conveyorItem['taken_at']) : '-';
                        $conveyors_data .= '<p class="normal-text">' . __('item_date_uploaded', true) . ': ' . $fecha_actualizacion . '</p>';
                        $conveyors_data .= '<p class="normal-text">' . __('item_date_capture', true) . ': ' . $fecha_visita . '</p>';
                    } else {
                        $conveyors_data .= '<p class="normal-text">' . $conveyorItem['desc_item'] . '</p>';
                        $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion . '</p>';
                    }
                    $conveyors_data .= '</div></li>';
                }
            }
        }
        $conveyors_data .= '</ul>';
        echo $conveyors_data;
    }

    /**
     * Draw buoy systems
     * New function to draw
     * @param [array] $buoy_systems
     * @return void
     */
    public function printGraphicBuoySystems($buoy_systems) {

        $offset = $this->_View->getVar('offset');
        $role = $this->_View->getVar('role');
        $clientId = $this->_View->getVar('clientId');

        //check permissions role
        $canAdd = isset($this->_credentials['permissions'][IElement::Is_Conveyor]) && in_array('add', $this->_credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
        $canEdit = isset($this->_credentials['permissions'][IElement::Is_Conveyor]) && in_array('edit', $this->_credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;
        $canDelete = isset($this->_credentials['permissions'][IElement::Is_Conveyor]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Conveyor]['allows']) ? true : false;

        $viewSavingsAllow = isset($this->_credentials['permissions'][IElement::Is_Saving]) && in_array('view', $this->_credentials['permissions'][IElement::Is_Saving]['allows']) ? true : false;
        $viewHistoryAllow = isset($this->_credentials['permissions'][IElement::Is_History]) && in_array('view', $this->_credentials['permissions'][IElement::Is_History]['allows']) ? true : false;
        $viewUltrasonicWithGaugeAllow = isset($this->_credentials['permissions'][IElement::Is_UltrasonicWithGauge]) && in_array('view', $this->_credentials['permissions'][IElement::Is_UltrasonicWithGauge]['allows']) ? true : false;

        $urlAddConveyor = $this->Html->url(array('controller' => 'Conveyors', 'action' => 'add'));
        //$typeUser = 'client|'. $group;
        $conveyors_data = '';
        if ($offset <= 0) {

            //$conveyors_data = '<ul class="dashboard-list"><li class="add-item-dashboard add-conveyor-link" rel="' . $urlAddConveyor . '" alt="" title="' . __("Agregar transportador", true) . '" data-section="5" data-intro="'.__("tutorial_agregar_banda",true).'" data-position="right"></li>';
            $conveyors_data = '<ul class="dashboard-list">';
            //if($this->_credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT))){

            if($canAdd && 1==2){
                $conveyors_data .= '<li class="circular-menu add-item-dashboard-circular add-conveyor-link" rel="' . $urlAddConveyor . '" alt="" title="' . __("Agregar transportador", true) . '" data-section="5" data-intro="'.__("tutorial_agregar_banda",true).'" data-position="right">'
                    . '<button class="add-button"></button>'
                    . '</li>';
            }


            //Si esta en la vista de clientes
            if($clientId>0){
                $nameCompany = $this->_View->getVar('nameCompany');
                $secureClientParams = $this->Utilities->encodeParams($clientId);
/*
                if(1==1):
                    $urlGenericBucket = $this->_View->Html->url('/customer/bucket/'.$secureClientParams['item_id'].'/'.$secureClientParams['digest']);
                    $conveyors_data .= '<li class="item-dashboard generic-bucket-item-dashboard item-dashboard-link" rel="'.$urlGenericBucket.'" alt=""><div>';
                    $conveyors_data .= '<p class="title">' . __('v.2.7.1.GenericBucket', true) . '</p>';
                    $conveyors_data .= '<p class="normal-text">' . $nameCompany . '</p>';
                    $conveyors_data .= '</div></li>';
                endif;

                if($viewSavingsAllow && 1==2):
                    $urlSavings = $this->_View->Html->url(array('controller' => 'Savings', 'action' => 'main', $secureClientParams['item_id'], $secureClientParams['digest']));
                    $conveyors_data .= '<li class="item-dashboard savings-item-dashboard item-dashboard-link" rel="'.$urlSavings.'" alt=""><div>';
                    $conveyors_data .= '<p class="title">' . __('v.2.5.1.Savings', true) . '</p>';
                    $conveyors_data .= '<p class="normal-text">' . $nameCompany . '</p>';
                    $conveyors_data .= '</div></li>';
                endif;

                if($viewHistoryAllow && 1==2):
                    $urlHistoryBelt = $this->_View->Html->url(array('controller' => 'History', 'action' => 'view', $secureClientParams['item_id'], $secureClientParams['digest']));
                    $conveyors_data .= '<li class="item-dashboard history-belt-item-dashboard item-dashboard-link" rel="'.$urlHistoryBelt.'" alt=""><div>';
                    $conveyors_data .= '<p class="title">' . __('v.2.5.1.BeltLifeHistory', true) . '</p>';
                    $conveyors_data .= '<p class="normal-text">' . $nameCompany . '</p>';
                    $conveyors_data .= '</div></li>';
                endif;
*
                $urlSummaryReport = $this->_View->Html->url(array('controller' => 'Summary', 'action' => 'report', $secureClientParams['item_id'], $secureClientParams['digest']));
                $conveyors_data .= '<li class="item-dashboard summary-report-item-dashboard item-dashboard-link" rel="'.$urlSummaryReport.'" alt=""><div>';
                $conveyors_data .= '<p class="title">' . __('Summary report', true) . '</p>';
                $conveyors_data .= '<p class="normal-text">' . $nameCompany . '</p>';
                $conveyors_data .= '</div></li>';*/
            }
        }

        if (!empty($buoy_systems)) {
            foreach ($buoy_systems AS $buoy_system) {
                $client = $buoy_system['Client'];
                $buoy_system = $buoy_system['FolderApp'];
                /*$transportador = $buoy_system['Conveyor'];
                $installedBelt = $buoy_system['TabInstalledBelt'];
                $ultrasonic_readings = $buoy_system['UltrasonicReading'];
                $empresa = $buoy_system['Empresa'];
                $distribuidor = $buoy_system['Distribuidor'];
                $coverImage = $buoy_system['Image'];*/

                $secureBuoyParams = $this->Utilities->encodeParams($buoy_system['id']);
                $urlDetailsConveyor = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'details', $secureBuoyParams['item_id'], $secureBuoyParams['digest']));
                $viewConveyor = $this->_View->Html->url(array('controller' => '/', 'action' => '/buoy/data/', $secureBuoyParams['item_id'], $secureBuoyParams['digest']));
                $urlChangeStatusConveyor = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'changeStatus', $secureBuoyParams['item_id'], $secureBuoyParams['digest']));

                $urlUpdateItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'update', $secureBuoyParams['item_id'], $secureBuoyParams['digest']));
                $urlRemoveItem = $this->_View->Html->url(array('controller' => 'BuoySystems', 'action' => 'remove', $secureBuoyParams['item_id'], $secureBuoyParams['digest']));
                $urlCopyItem = $this->_View->Html->url(array('controller' => 'Conveyors', 'action' => 'copy', Item::CONVEYOR, $secureBuoyParams['item_id'], $secureBuoyParams['digest']));

                $estatus_items = array(
                    'OPERATING' => array(__('Operando', true), 'in-operation'),
                    'MAINTENANCE' => array(__('Mantenimiento', true), 'in-maintenance'),
                    'OUTOFSERVICE' => array(__('Fuera de servicio', true), 'out-of-service')
                );

                $operating = $buoy_system['status'] == 'OPERATING' ? "selected" : "";
                $maintenance = $buoy_system['status'] == 'MAINTENANCE' ? "selected" : "";
                $outservice = $buoy_system['status'] == 'OUTOFSERVICE' ? "selected" : "";
                $status_indicator = '<select rel="' . $urlChangeStatusConveyor . '">
                                        <option value="OPERATING" ' . $operating . '>' . $estatus_items['OPERATING'][0] . '</option>
                                        <option value="MAINTENANCE" ' . $maintenance . '>' . $estatus_items['MAINTENANCE'][0] . '</option>
                                        <option value="OUTOFSERVICE" ' . $outservice . '>' . $estatus_items['OUTOFSERVICE'][0] . '</option>
                                    </select>';

                $confirmationMsg = __('Do you really want to delete the selected buoy system?', true);
                $confirmationMsgCopy = __('Realmente desea copiar la banda seleccionada', true);
                $actions_item = '<ul class="actions-item-dashboard">';

                // No duplicar por lo pronto
                if ($this->_credentials['i_group_id'] >= IGroup::TERRITORY_MANAGER) {
                    // $actions_item .= '<li><a href="#" class="copy-item-dashboard copy-item-link" title="' . __('Duplicar', true) . '" rel="' . $urlCopyItem . '" conf-msg="' . $confirmationMsgCopy . '"></a></li>';
                }
                //if($this->_credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT))) {
                if($canEdit) {
                    $dealer_id = $client['parent'];
                    $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-conveyor-link" title="' . __('Editar', true) . '" rel="' . $urlUpdateItem . '" assoc-c="' . $buoy_system['client_id'] . '" assoc-d="' . $dealer_id . '" assoc-callback="callUpdateConveyorsDataTable"></a></li>';
                }

                //if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){
                if($canDelete) {
                    $actions_item .= '<li><a href="#" class="delete-item-dashboard delete-item-link" title="' . __('Eliminar', true) . '" rel="' . $urlRemoveItem . '" conf-msg="' . $confirmationMsg . '"></a></li>';
                }
                $actions_item .= '</ul>';

                $fecha_registro = $this->Utilities->timestampToCorrectFormat($buoy_system['updated_at'], '/');
                $fancy_date = $this->Utilities->timestampToUsDate($buoy_system['updated_at']);
                $fancy_date = $this->Utilities->timestampToUsDate($buoy_system['updated_at']);
                

                $conveyors_data .= '<li alt="cid-'.$buoy_system['client_id'].'" class="item-dashboard conveyor-item buoy-item item-dashboard-link" rel="' . $viewConveyor . '" data-conveyor="'.$buoy_system['id'].'">' . $actions_item . '<div>';
                $conveyors_data .= '<p class="title" title="' . $buoy_system['name'] . '">' . $buoy_system['name'] . '</p>';
                $conveyors_data .= '<p class="normal-text">' . $client['name'] . '</p>';
                $conveyors_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fancy_date . '</p>';
                $conveyors_data .= '<p class="conveyor-status ' . $estatus_items[$buoy_system['status']][1] . '">' . $status_indicator . '</p>';
                $conveyors_data .= '</div>';

                //$image_conveyor = !is_null($buoy_system['cover_img']) && file_exists($buoy_system['cover_img']) ? '<div class="cover-item"><img src="' . $this->_site . $buoy_system['cover_img'] . '"/></div>' : '';
                $image_conveyor = !is_null($buoy_system['image']) ? '<div class="cover-item"><img src="data:image/jpeg;base64,' . base64_encode($buoy_system['image']) . '"/></div>' : '';

                $conveyors_data .= $image_conveyor;

                $conveyors_data .= '</li>';
            }
        }else{
            $conveyors_data .= '<li class="empty-data">'.__('Empty, add your first buoy system.',true).'</li>';
        }

        if ($offset <= 0) {
            $conveyors_data .= '</ul>';
        }
        echo $conveyors_data;
    }

    public function printGraphicCompanies($companies, $group) {
        $role = $this->_View->getVar('role');
        $sharedClients = $this->_View->getVar('sharedClients');

        
        $urlAddUserCompany = $this->Html->url(array('controller' => 'Users', 'action' => 'add','company'));
        $typeUser = 'client|' . $group;
        //$companies_data = '<ul class="dashboard-list"><li class="add-item-dashboard add-client-link" rel="' . $urlAddUserCompany . '" alt="' . $typeUser . '@@" title="' . __("Agregar cliente", true) . '" data-section="10" data-intro="'.__("tutorial_agregar_cliente",true).'" data-position="right"></li>';

        $companies_data = '<ul class="dashboard-list">';
        /*$companies_data = '<ul class="dashboard-list">'
                                . '<li class="circular-menu add-item-dashboard-circular add-client-link" rel="' . $urlAddUserCompany . '" alt="' . $typeUser . '@@" title="' . __("Agregar cliente", true) . '" data-section="10" data-intro="'.__("tutorial_agregar_cliente",true).'" data-position="right">'
                                . '<button class="add-button"></button>'
                                . '</li>';*/
        if (!empty($companies)) {
            foreach ($companies AS $company) {
                $empresa = $company['Empresa'];

                $corporativo = $company['Corporativo'];
                $secureCompanyParams = $this->Utilities->encodeParams($empresa['id']);
                $urlProfileCompany = $this->_View->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $viewCompany = $this->_View->Html->url(array('controller' => '/', 'action' => '/customer/buoys/', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlDeleteCompany = $this->Html->url(array('controller' => 'Companies', 'action' => 'delete', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));

                $actions_company = '<ul class="actions-item-dashboard">';
                if($this->_credentials['i_group_id'] == IGroup::DISTRIBUTOR){
                    $actions_company .= '<li><a href="#" class="edit-item-dashboard edit-company-link" title="' . __('Editar', true) . '" rel="' . $urlProfileCompany . '"></a></li>';
                    if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){
                        if($empresa["id"]!=686) {//Cliente Pete
                            $actions_company .= '<li><a href="#" class="delete-item-dashboard delete-company-link" title="' . __('Eliminar', true) . '" rel="' . $urlDeleteCompany . '"></a></li>';
                        }
                    }
                }
                

                $class_company = "";
                if(!in_array($role, array(UsuariosEmpresa::IS_MASTER)) && $empresa['id']==686){//Cliente Pete
                    $class_company = 'hidden';
                }
                $actions_company .= '</ul>';
                $name_corporate = !is_null($corporativo['name']) ? $corporativo['name'] : '';
                $fecha_registro = $this->Utilities->timestampToCorrectFormat($empresa['created'], '/');
                $fancy_date = $this->Utilities->timestampToUsDate($empresa['created']);

                $styleEmpresa = in_array($empresa['id'], $sharedClients) && $empresa['region']!=$this->_credentials['company_region'] ? 'style="border: 1px solid #00A5DC;"':'';

                $companies_data .= '<li alt="cid-'.$empresa['id'].'" class="item-dashboard user-item item-dashboard-link '.$class_company.'" rel="' . $viewCompany . '"  '.$styleEmpresa.'>' . $actions_company . '<div>';
                $companies_data .= '<p class="title" title="' . $empresa['name'] . '">' . $empresa['name'] . '</p>';
                $companies_data .= '<p class="normal-text">' . $empresa['city'] . '</p>';
                $companies_data .= '<p class="normal-text">' . __('Registrado', true) . ': ' . $fancy_date . '</p>';

                //$image_company = $empresa['path_image'] != '' && file_exists($empresa['path_image']) ? '<div class="cover-item"><img src="' . $this->_site . $empresa['path_image'] . '"/></div>' : '';
                $image_company = !is_null($empresa['image']) ? '<div class="cover-item"><img src="data:image/jpeg;base64,' . base64_encode($empresa['image']) . '"/></div>' : '';
                $corporativo = $empresa['active'] == 0 ? '<span class="generic-disclaimer">' . __('Suspendida', true) . '</span>' : $name_corporate;
                $companies_data .= '<p class="corporate" title="' . $name_corporate . '">' . $corporativo . '</p>';
                $companies_data .= '</div>';

                $companies_data .= $image_company;
                $companies_data .= '</li>';
            }
        }
        $companies_data .= '</ul>';
        echo $companies_data;
    }

    public function printGraphicCompaniesMonitoring($companies) {
        $role = $this->_View->getVar('role');
        $sharedClients = $this->_View->getVar('sharedClients');


        $urlAddUserCompany = $this->Html->url(array('controller' => 'Users', 'action' => 'add','company'));
        $companies_data = '<ul class="dashboard-list">';

        if (!empty($companies)) {
            foreach ($companies AS $company) {
                $empresa = $company['Empresa'];

                $corporativo = $company['Corporativo'];
                $secureCompanyParams = $this->Utilities->encodeParams($empresa['id']);
                $urlProfileCompany = $this->_View->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $viewCompany = $this->_View->Html->url(array('controller' => '/', 'action' => '/monitoring/company/', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));


                $class_company = "";
                if(!in_array($role, array(UsuariosEmpresa::IS_MASTER)) && $empresa['id']==686){//Cliente Pete
                    $class_company = 'hidden';
                }

                $name_corporate = !is_null($corporativo['name']) ? $corporativo['name'] : '';
                $fecha_registro = $this->Utilities->timestampToCorrectFormat($empresa['created'], '/');
                $fancy_date = $this->Utilities->timestampToUsDate($empresa['created']);

                $styleEmpresa = in_array($empresa['id'], $sharedClients) && $empresa['region']!=$this->_credentials['company_region'] ? 'style="border: 1px solid #00A5DC;"':'';

                $companies_data .= '<li alt="cid-'.$empresa['id'].'" class="item-dashboard user-item item-dashboard-link '.$class_company.'" rel="' . $viewCompany . '"  '.$styleEmpresa.'><div>';
                $companies_data .= '<p class="title" title="' . $empresa['name'] . '">' . $empresa['name'] . '</p>';
                $companies_data .= '<p class="normal-text">' . $empresa['city'] . '</p>';
                $companies_data .= '<p class="normal-text">' . __('Registrado', true) . ': ' . $fancy_date . '</p>';

                $image_company = $empresa['path_image'] != '' && file_exists($empresa['path_image']) ? '<div class="cover-item"><img src="' . $this->_site . $empresa['path_image'] . '"/></div>' : '';

                $corporativo = $empresa['active'] == 0 ? '<span class="generic-disclaimer">' . __('Suspendida', true) . '</span>' : $name_corporate;
                $companies_data .= '<p class="corporate" title="' . $name_corporate . '">' . $corporativo . '</p>';
                $companies_data .= '</div>';

                $companies_data .= $image_company;
                $companies_data .= '</li>';
            }
        }
        $companies_data .= '</ul>';
        echo $companies_data;
    }

    /**
     * Function print company rows
     * @param array $companies
     * @param int $group group id for array companies
     */
    public function printCompanies($companies, $group) {
        $role = $this->_View->getVar('role');
        $sharedClients = $this->_View->getVar('sharedClients');
        $sharedDealers = $this->_View->getVar('sharedDealers');
        $allSharedDealers = $this->_View->getVar('allShareDealers');
        $companies_data = '';

        $deleteCompanyColaboratorAllow = isset($this->_credentials['permissions'][IElement::Is_CompanyColaborator]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_CompanyColaborator]['allows']) ? true : false;
        $deleteDistributorAllow = isset($this->_credentials['permissions'][IElement::Is_Distributor]) && in_array('delete', $this->_credentials['permissions'][IElement::Is_Distributor]['allows']) ? true : false;
        $deleteDistributorAllow = !isset($this->_credentials['permissions'][IElement::Is_Distributor]) ? true : $deleteDistributorAllow; //Si no esta definido el permiso (solo terr. manager se les definio), dejar abierto segun tipo

        $j = 0;
        if (!empty($companies)) {
            foreach ($companies AS $company) {
                $empresa = $company['Empresa'];

                $id_usuarios = $company[0]['user_ids'];
                $id_clientes = $company[0]['client_ids'];
                $id_usuarios_cliente = isset($company[0]['user_client_ids']) ? $company[0]['user_client_ids'] : null;

                $usersInCompany = [];
                $users_company = '<ul>';
                if (!is_null($id_usuarios)) {//Si tiene usuarios la empresa
                    $name_usuarios = $company[0]['user_names'];
                    $image_usuarios = $company[0]['user_images'];
                    $estatus_usuarios = $company[0]['user_estatus'];
                    $name_usuarios = explode(',', $name_usuarios);
                    $id_usuarios = explode(',', $id_usuarios);
                    $image_usuarios = explode(',', $image_usuarios);
                    $estatus_usuarios = explode(',', $estatus_usuarios);

                    for ($i = 0; $i < count($id_usuarios); $i++) {
                        $secureUserParams = $this->Utilities->encodeParams($id_usuarios[$i]);
                        $urlProfileUser = $this->Html->url(array('controller' => 'Users', 'action' => 'profile', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlSuspendUser = $this->Html->url(array('controller' => 'Users', 'action' => 'suspendUnsuspend', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlDeleteUser = $this->Html->url(array('controller' => 'Users', 'action' => 'delete', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlAccessDataUser = $this->Html->url(array('controller' => 'Users', 'action' => 'accessData', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlResendInvitation = $this->Html->url(array('controller' => 'Users', 'action' => 'resendAccountData', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlClearFingerPrint = $this->Html->url(array('controller' => 'Users', 'action' => 'clearFingerprint', $secureUserParams['item_id'], $secureUserParams['digest']));
                        $urlClearQuestion = $this->Html->url(array('controller' => 'Users', 'action' => 'clearQuestionUser', $secureUserParams['item_id'], $secureUserParams['digest']));

                        list($userx, $name_usuarios[$i]) = explode('_', $name_usuarios[$i]);
                        list($userx, $image_usuarios[$i]) = explode('_', $image_usuarios[$i], 2);
                        list($userx, $estatus_usuarios[$i]) = explode('_', $estatus_usuarios[$i]);

                        $textSuspend = $estatus_usuarios[$i] == 1 ? __("Suspender", true) : __("Reactivar", true);
                        $opositeSuspend = $estatus_usuarios[$i] == 1 ? __("Reactivar", true) : __("Suspender", true);

                        $classDeleteLink = $id_usuarios[$i] == $this->_credentials['id'] ? 'hidden' : '';
                        $menu_user = '<ul class="options-menu">
                                        <li><a href="#modal" class="profile user-profile-link" alt="profile-dialog|callOpenUProfile|callUpdateDataTable" rel="' . $urlProfileUser . '">' . __("Ver Perfil", true) . '</a></li>';

                        //$menu_user .= '<li><a href="#" class="resend_invitation resend-invitation-link" title="' . __("Reenviar invitacion", true) . '" data-confirmtxt="'.__("Si, reenviar",true).'"  rel="' . $urlResendInvitation . '" data-confirmmsg="'.__("Realmente desea reenviar el email de confirmacion?",true).'">' . __("Reenviar", true) . '</a></li>';
                        
                        if($this->_credentials['i_group_id'] >= IGroup::MASTER){
                            $menu_user .= '<li><a href="#" class="restore-fingerprint clear-fingerprint-link" title="' . __("Limpiar huella", true) . '" data-confirmtxt="'.__("Si, limpiar",true).'"  rel="' . $urlClearFingerPrint . '" data-confirmmsg="'.__("Realmente desea limpiar la huella de equipo del colaborador?",true).'">' . __("Huella", true) . '</a></li>
                                        <li><a href="#" class="restore-question clear-question-link" title="' . __("Limpiar pregunta de seguridad", true) . '" data-confirmtxt="'.__("Si, limpiar",true).'"  rel="' . $urlClearQuestion . '" data-confirmmsg="'.__("Realmente desea limpiar la pregunta de seguridad del colaborador?",true).'">' . __("Pregunta", true) . '</a></li>';
                        }

                        $menu_user .= '<li><a href="#" class="password change-accessdata-link" rel="' . $urlAccessDataUser . '">' . __("Contrasena", true) . '</a></li>
                                        <li><a href="#" class="suspend suspend-user-link" rel="' . $urlSuspendUser . '" alt="' . $opositeSuspend . '">' . $textSuspend . '</a></li>';
                                    //if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){
                                    if($deleteCompanyColaboratorAllow) {
                                        $menu_user .= '<li class="' . $classDeleteLink . '"><a href="#" class="delete delete-user-link" rel="' . $urlDeleteUser . '">' . __("Eliminar", true) . '</a></li>';
                                    }   
                                        
                                  $menu_user .= '</ul>';
                        $imagen_perfil = $this->_site;



                        $imagen_perfil .= trim($image_usuarios[$i]) != '' && file_exists(trim($image_usuarios[$i])) ? trim($image_usuarios[$i]) : _DEFAULT_DD_USER_IMG;
                        $imagen_perfil = '<img src="' . $imagen_perfil . '"/>';
                        //Append user company
                        $usersInCompany[] = ['id'=>$id_usuarios[$i], 'name'=>$name_usuarios[$i],'company_rm_admin'=>$empresa['rm_admin']];
                        $users_company .= '<li><a href="#" rel="' . $id_usuarios[$i] . '" class="user-item parent">' . $imagen_perfil . $name_usuarios[$i] . '</a>' . $menu_user . '</li>';
                    }
                }




                $clients_company = '';
                //Menu cuando se obtienen los clientes via ajax
                if (!is_null($id_clientes)) {//Si tiene usuarios la empresa

                    $clients_company = '<div class="clients-distributor">';
                    $name_clients = $company[0]['client_names'];
                    $name_clients = explode('||', $name_clients); /* FIX SEPARATOR * */
                    $id_clientes = explode(',', $id_clientes);
                    for ($i = 0; $i < count($id_clientes); $i++) { //Iteramos sobre cada uno de los clientes
                        $client_id = $id_clientes[$i];

                        $users_of_client_company = '<ul>';

                        if (!is_null($id_usuarios_cliente)) {//Si tiene usuarios la empresa cliente
                            $name_usuarios = $company[0]['user_client_names'];
                            $image_usuarios = $company[0]['user_client_images'];
                            $estatus_usuarios = $company[0]['user_client_estatus'];
                            $name_usuarios = explode(',', $name_usuarios);
                            $id_usuarios = explode(',', $id_usuarios_cliente);
                            $image_usuarios = explode(',', $image_usuarios);
                            $estatus_usuarios = explode(',', $estatus_usuarios);

                            //var_dump(count($id_usuarios));
                            for ($usersClientIterator = 0; $usersClientIterator < count($id_usuarios); $usersClientIterator++) {
                                $user_info = explode('_', $id_usuarios[$usersClientIterator]);
                                if($user_info[1]==$client_id) { //Si el usuario pertenece al cliente, se agrega a la lista
                                    $id_usuarios[$usersClientIterator] = $user_info[0]; //asignamos el id del usuario
                                    $secureUserParams = $this->Utilities->encodeParams($id_usuarios[$usersClientIterator]);
                                    $urlProfileUser = $this->Html->url(array('controller' => 'Users', 'action' => 'profile', $secureUserParams['item_id'], $secureUserParams['digest']));
                                    $urlSuspendUser = $this->Html->url(array('controller' => 'Users', 'action' => 'suspendUnsuspend', $secureUserParams['item_id'], $secureUserParams['digest']));
                                    $urlDeleteUser = $this->Html->url(array('controller' => 'Users', 'action' => 'delete', $secureUserParams['item_id'], $secureUserParams['digest']));
                                    $urlAccessDataUser = $this->Html->url(array('controller' => 'Users', 'action' => 'accessData', $secureUserParams['item_id'], $secureUserParams['digest']));
                                    $urlResendInvitation = $this->Html->url(array('controller' => 'Users', 'action' => 'resendAccountData', $secureUserParams['item_id'], $secureUserParams['digest']));

                                    list($userx, $name_usuarios[$usersClientIterator]) = explode('_', $name_usuarios[$usersClientIterator]);
                                    list($userx, $image_usuarios[$usersClientIterator]) = explode('_', $image_usuarios[$usersClientIterator], 2);
                                    list($userx, $estatus_usuarios[$usersClientIterator]) = explode('_', $estatus_usuarios[$usersClientIterator]);

                                    $textSuspend = $estatus_usuarios[$usersClientIterator] == 1 ? __("Suspender", true) : __("Reactivar", true);
                                    $opositeSuspend = $estatus_usuarios[$usersClientIterator] == 1 ? __("Reactivar", true) : __("Suspender", true);

                                    $classDeleteLink = $id_usuarios[$usersClientIterator] == $this->_credentials['id'] ? 'hidden' : '';
                                    $menu_user = '<ul class="options-menu">
                                                    <li><a href="#modal" class="profile user-profile-link" alt="profile-dialog|callOpenUProfile|callUpdateDataTable" rel="' . $urlProfileUser . '">' . __("Ver Perfil", true) . '</a></li>
                                                    <li><a href="#" class="resend_invitation resend-invitation-link" title="' . __("Reenviar invitacion", true) . '" data-confirmtxt="' . __("Si, reenviar", true) . '"  rel="' . $urlResendInvitation . '" data-confirmmsg="' . __("Realmente desea reenviar el email de confirmacion?", true) . '">' . __("Reenviar", true) . '</a></li>
                                                    <li><a href="#" class="password change-accessdata-link" rel="' . $urlAccessDataUser . '">' . __("Contrasena", true) . '</a></li>
                                                    <li><a href="#" class="suspend suspend-user-link" rel="' . $urlSuspendUser . '" alt="' . $opositeSuspend . '">' . $textSuspend . '</a></li>';
                                    //if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){
                                    if ($deleteCompanyColaboratorAllow) {
                                        $menu_user .= '<li class="' . $classDeleteLink . '"><a href="#" class="delete delete-user-link" rel="' . $urlDeleteUser . '">' . __("Eliminar", true) . '</a></li>';
                                    }

                                    $menu_user .= '</ul>';
                                    $imagen_perfil = $this->_site;


                                    $imagen_perfil .= trim($image_usuarios[$usersClientIterator]) != '' && file_exists(trim($image_usuarios[$usersClientIterator])) ? trim($image_usuarios[$usersClientIterator]) : _DEFAULT_DD_USER_IMG;
                                    $imagen_perfil = '<img src="' . $imagen_perfil . '"/>';
                                    //Append user company
                                    $users_of_client_company .= '<li><a href="#" rel="' . $id_usuarios[$usersClientIterator] . '" class="user-item parent">' . $imagen_perfil . $name_usuarios[$usersClientIterator] . '</a>' . $menu_user . '</li>';
                                }
                            }
                        }

                        $secureClientParams = $this->Utilities->encodeParams($id_clientes[$i]);
                        $urlProfileClient = $this->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureClientParams['item_id'], $secureClientParams['digest']));
                        $urlClientCompanyView = $this->Html->url(array('controller' => 'Companies', 'action' => 'view', $secureClientParams['item_id'], $secureClientParams['digest']));
                        $urlAddUserCompanyToClient = $this->Html->url(array('controller' => 'Companies', 'action' => 'appendColaborator', $secureClientParams['item_id'], $secureClientParams['digest']));

                        list($userx, $name_clients[$i]) = explode('_', $name_clients[$i]);
                        $action_clients = '<li class="company profile-company-link" title="' . __("Perfil", true) . '" rel="' . $urlProfileClient . '"></li>';
                        if ($this->_credentials['i_group_id'] > $group && $this->_credentials['i_group_id'] > IGroup::CLIENT) {
                            //$users_company_dist = '<ul>';
                            $users_company_dist = $users_of_client_company;
                            $users_company_dist .= '<li><a href="#" class="add-colaborator add-colaborator-link" rel="' . $urlAddUserCompanyToClient . '" alt="client@' . $client_id . '@' . $empresa['id'] . '">' . __("Agregar colaborador", true) . '</a></li>';
                            $users_company_dist .= '</ul>';

                            $action_clients .= '<li class="colaborators colaborators-client" title="' . __("Colaboradores", true) . '" rel="' . $client_id . '">' . $users_company_dist . '</li>';

                        }
                        $action_clients .= '<li class="conveyors-client item-dashboard-link" title="' . __("Buoy System", true) . '" rel="' . $urlClientCompanyView . '"></li>';
                        $clients_company .= '<div class = "accordionButton row-data"><nav class="action-bar-accord"><ul class="action-list">' . $action_clients . '</ul></nav>' . $name_clients[$i] . '</div>';
                    }
                    $clients_company .= '</div>';
                }


                $secureCompanyParams = $this->Utilities->encodeParams($empresa['id']);
                $urlAddUserCompany = $this->Html->url(array('controller' => 'Companies', 'action' => 'appendColaborator', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlProfileCompany = $this->_View->Html->url(array('controller' => 'Users', 'action' => 'companyProfile', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlSuspendCompany = $this->Html->url(array('controller' => 'Companies', 'action' => 'suspendUnsuspend', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlDeleteCompany = $this->Html->url(array('controller' => 'Companies', 'action' => 'delete', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlSetRegionManager = $this->Html->url(array('controller' => 'Users', 'action' => 'setRegionManager', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                $urlShareCompanyWithSP = $this->_View->Html->url(array('controller' => '/', 'action' => 'shareCompany', $secureCompanyParams['item_id'], $secureCompanyParams['digest']     ));

                $typeUser = $empresa['type'] . '|' . $group;
                if ($this->_credentials['i_group_id'] > $group && $this->_credentials['i_group_id'] > IGroup::CLIENT) {
                    $users_company .= '<li><a href="#" class="add-colaborator add-colaborator-link" rel="' . $urlAddUserCompany . '" alt="' . $typeUser . '@' . $empresa['id'] . '@' . $empresa['parent'] . '">' . __("Agregar colaborador", true) . '</a></li>';
                }
                $users_company .= '</ul>';

                $textSuspend = $empresa['active'] == 1 ? __("Suspender", true) : __("Reactivar", true);
                $opositeSuspend = $empresa['active'] == 1 ? __("Reactivar", true) : __("Suspender", true);


                $action_btns = "";
                if(!in_array($group, [IGroup::REGION_MANAGER, IGroup::COUNTRY_MANAGER, IGroup::MARKET_MANAGER])){
                    $action_btns .= '<li class="company profile-company-link" title="' . __("Perfil", true) . '" rel="' . $urlProfileCompany . '"></li>';
                }


                $action_btns .= '<li class="colaborators" title="' . __("Colaboradores", true) . '" rel="' . $empresa['id'] . '">' . $users_company . '</li>';
                if ($group == Group::DISTRIBUTOR) {
                    /*if($this->_credentials['group_id']>Group::ADMIN) { // && rol = master y nuevos roles
                        $action_btns .= '<li class="share share-company-link" title="' . __("Share Distributor", true) . '" rel="' . $urlShareCompanyWithSP . '">' . '</li>';
                    }*/
                    $deleteCompanyColaboratorAllow = $deleteDistributorAllow;
                }
                if ($group == Group::CLIENT) {
                    $urlClientCompanyView = $this->Html->url(array('controller' => 'Companies', 'action' => 'view', $secureCompanyParams['item_id'], $secureCompanyParams['digest']));
                    $action_btns .= '<li class="conveyors-client item-dashboard-link" title="' . __("Buoy System", true) . '" rel="' . $urlClientCompanyView . '"></li>';
                }

                if ($group == Group::DISTRIBUTOR && $clients_company != '') {
                    $action_btns .= '<li class="clients clients-company-link" title="' . __("Clientes", true) . '" rel="' . $empresa['id'] . '">' . '</li>';
                }

                $adminManagers = "";
                if ($group == Group::ADMIN){ // && $role > UsuariosEmpresa::IS_MASTER) {
                    $adminManagers = '<div class="info-admins"><ul class="row-menu h-menu">';
                    if(!empty($usersInCompany)){
                        if($usersInCompany[0]['company_rm_admin']==0){
                            $adminManagers .= '<li title="Territory manager" class="rm-admin" data-rmchange="'.$urlSetRegionManager.'"><a>'.$usersInCompany[0]['name'].'</a><ul>';
                        }else{
                            $key_user_setted = array_search($usersInCompany[0]['company_rm_admin'], array_column($usersInCompany, 'id'));
                            $key_user_setted = is_numeric($key_user_setted) ? $key_user_setted : 0;
                            $adminManagers .= '<li title="Territory manager" class="rm-admin" data-rmchange="'.$urlSetRegionManager.'"><a>'.$usersInCompany[$key_user_setted]['name'].'</a><ul>';
                        }

                        $selected = $usersInCompany[0]['company_rm_admin']==0 ? "selected" : "";
                        foreach($usersInCompany AS $userInCompany){
                            if($userInCompany['company_rm_admin']!=0 && $selected==""){
                                $selected = $userInCompany['company_rm_admin']==$userInCompany['id'] ? "selected" : "";
                            }
                            $adminManagers .= '<li class="rm-admin-option '.$selected.'" data-id="'.$userInCompany['id'].'"><a>'.$userInCompany['name'].'</a></li>';
                            $selected = "";
                        }
                        $adminManagers .= '</ul></li>';
                    }else{
                        $adminManagers .= '<li title="Territory manager" class="rm-admin"><a>'.__("None",true).'</a></li>';
                    }
                    $adminManagers .= '</ul></div>';


                    $urlLoadDistributorsRegion = $this->Html->url(array('controller' => 'Users', 'action' => 'getCompaniesDistributor', UsuariosEmpresa::IS_DIST));
                    $action_btns .= '<li class="distributors distributors-company-link" title="' . __("Distribuidores", true) . '" rel="' . $empresa['id'] . '" data-region="'.$empresa['region'].'" data-query="'.$urlLoadDistributorsRegion.'">' . '</li>';

                    $clients_company = '<div class="distributors-list">';
                    $clients_company .= '</div>';
                }

                if($group<IGroup::TERRITORY_MANAGER){
                    $action_btns .= '<li class="suspend suspend-company-link" title="' . $textSuspend . '" alt="' . $opositeSuspend . '" rel="' . $urlSuspendCompany . '"></li>';
                }

                //if(in_array($role, array(UsuariosEmpresa::IS_MASTER, UsuariosEmpresa::IS_ADMIN))){
                if($deleteCompanyColaboratorAllow && $group<IGroup::TERRITORY_MANAGER) { //Si el grupo de la empresa no es admin hacia arriba, permitir borrar la empresa
                    $action_btns .= '<li class="delete delete-company-link" title="' . __("Eliminar", true) . '" rel="' . $urlDeleteCompany . '"></li>';
                }

                $tutorial = $j==2 ? 'data-section="13" data-intro="'.__('tutorial_acciones_usuarios',true).'" data-position="bottom"' : '';
                $nombre_empresa = __($empresa['name'],true);
                $styleEmpresa = "";
                if(($group==Group::CLIENT || $group==Group::DISTRIBUTOR) && ( (!is_null($sharedClients) && !is_null($sharedDealers)) || !is_null($allSharedDealers))){
                    $styleEmpresa = (in_array($empresa['id'], $sharedClients) || in_array($empresa['id'], $sharedDealers) || in_array($empresa['id'], $allSharedDealers)) &&  $empresa['region']!=$this->_credentials['company_region'] ? 'style="color: #00A5DC;"':'';
                }

                $class_company = "";
                if(!in_array($role, array(UsuariosEmpresa::IS_MASTER)) && $empresa['id']==686){
                    $class_company = 'hidden';
                }

                $companies_data .= '<div class = "accordionButton row-data clients-company-link '.$class_company.'" rel="' . $empresa['id'] . '"><nav class="action-bar-accord" '.$tutorial.'><ul class="action-list">' . $action_btns . '</ul></nav><div class="company-name-accord" '.$styleEmpresa.'>' . $nombre_empresa .'</div>'. $adminManagers.'</div>' . $clients_company;
                
                $j++;
            }
        } else {
            $companies_data .= '<div class = "accordionButton empty-data">' . __('No se encontraron resultados', true) . '</div>';
        }
        echo $companies_data;
    }

   
    /**
     * print dropdown with the settings of conveyors
     * @param array $options options to show
     * @param string $id_dropdown id for drowdown
     * @param string $class_dropdown class for dropdown
     * @param string $first_label default value for dropdown
     */
    public function printConfigTranspDropDownCalculator($options, $id_dropdown, $class_dropdown, $first_label = '', $selected_value = '') {
        $lang = $this->_View->getVar('app_lang');
        $placeholder = $first_label != '' ? $first_label : '';
        ?>
        <select data-placeholder='<?php echo $placeholder; ?>' id="<?php echo $id_dropdown; ?>" name="<?php echo $id_dropdown; ?>" class="<?php echo $class_dropdown; ?>">
            <?php if ($first_label != '') { ?>
                <option value=""></option>
                <?php
            }


            foreach ($options AS $option) {
                $option = $option['ctransp'];
                $selected = $selected_value == utf8_encode($option['titulo']) || $selected_value == utf8_encode($option['titulo_en']) ? 'selected="selected"' : '';

                $valor = $option['valor'];
                $titulo = $lang == 'es' ? utf8_encode($option['titulo']) : utf8_encode($option['titulo_en']);
                echo '<option ' . $selected . ' value="' . $valor . '" rel="' . $option['mat_density'] . '">' . $titulo . '</option>';
            }
            ?>
        </select>
        <?php
    }

    /**
     * print dropdown with the settings of conveyors
     * @param array $options options to show
     * @param string $id_dropdown id for drowdown
     * @param string $class_dropdown class for dropdown
     * @param string $first_label default value for dropdown
     */
    public function printConfigTranspDropDownUs($options, $id_dropdown, $class_dropdown, $first_label = '', $selected_value = 0) {
        $lang = "en";
        $placeholder = $first_label != '' ? $first_label : '';
        $selected_value = $selected_value == '' ? 0 : $selected_value;
        ?>
        <select data-placeholder='<?php echo $placeholder; ?>' id="<?php echo $id_dropdown; ?>" name="<?php echo $id_dropdown; ?>" class="<?php echo $class_dropdown; ?>">
            <?php if ($first_label != '') { ?>
                <option value=""></option>
                <?php
            }
            foreach ($options AS $option) {
                $option = $option['ctransp'];
                //$selected = $selected_value == utf8_encode($option['titulo']) || $selected_value == utf8_encode($option['titulo_en']) ? 'selected="selected"' : '';
                $selected = $selected_value == $option['id'] ? 'selected="selected"' : '';

                $valor = $option['id'];
                $titulo = $lang == 'es' ? utf8_encode($option['titulo']) : utf8_encode($option['titulo_en']);
                echo '<option ' . $selected . ' value="' . $valor . '" rel="' . $option['mat_density'] . '">' . $titulo . '</option>';
            }
            ?>
        </select>
        <?php
    }

    /**
     * print dropdown with the settings of conveyors
     * @param array $options options to show
     * @param string $id_dropdown id for drowdown
     * @param string $class_dropdown class for dropdown
     * @param string $first_label default value for dropdown
     */
    public function printConfigTranspDropDown($options, $id_dropdown, $class_dropdown, $first_label = '', $selected_value = 0) {
        $lang = $this->_View->getVar('app_lang');
        $placeholder = $first_label != '' ? $first_label : '';
        $selected_value = $selected_value == '' ? 0 : $selected_value;
        ?>
        <select data-placeholder='<?php echo $placeholder; ?>' id="<?php echo $id_dropdown; ?>" name="<?php echo $id_dropdown; ?>" class="<?php echo $class_dropdown; ?>">
            <?php if ($first_label != '') { ?>
                <option value=""></option>
                <?php
            }
            foreach ($options AS $option) {
                $option = $option['ctransp'];
                //$selected = $selected_value == utf8_encode($option['titulo']) || $selected_value == utf8_encode($option['titulo_en']) ? 'selected="selected"' : '';
                $selected = $selected_value == $option['id'] ? 'selected="selected"' : '';

                $valor = $option['id'];
                $titulo = $lang == 'es' ? utf8_encode($option['titulo']) : utf8_encode($option['titulo_en']);
                echo '<option ' . $selected . ' value="' . $valor . '" rel="' . $option['mat_density'] . '">' . $titulo . '</option>';
            }
            ?>
        </select>
        <?php
    }

    public function putDropdownPerfilesBandas($perfiles, $id_dropdown, $class_dropdown, $first_label = '', $selected = 0) {
        ?>        
        <select id="<?php echo $id_dropdown; ?>" name="<?php echo $id_dropdown; ?>" class="<?php echo $class_dropdown; ?> image-chosen" data-placeholder="<?php echo $first_label; ?>">
            <option data-img-src="" value=""></option>
            <?php
            if (!empty($perfiles)) {
                foreach ($perfiles AS $perfil) {
                    $perfil = $perfil['PerfilesTransportadores'];
                    $selected_profile = $selected == $perfil['id'] ? 'selected="selected"' : '';
                    echo '<option data-img-src="' . $perfil['path'] . '" ' . $selected_profile . ' value="' . $perfil['id'] . '">&nbsp;</option>';
                }
            }
            ?>
        </select>
        <?php
    }

    /**
     * function print generic dropdown with name companies
     * @param string $label label text
     * @param string $id dropdown identificator
     * @param array $companies companies to print
     * @param bool $is_disabled indicates if dropdown is disabled or not
     */
    public function putDropdownCompanies($label, $id, $companies, $is_disabled, $with_label = true) {
        $disabled = $is_disabled ? 'disabled = "disabled"' : '';
        if ($with_label) {
            ?>        
            <div class="label-dropdown"><?php echo $label; ?></div>
        <?php } ?>
        <input type="hidden" name="<?php echo $id; ?>_txt" value=""/>
        <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="validate[required]" data-placeholder="<?php echo $label; ?>" <?php echo $disabled; ?>>
            <option rel="" value=""></option>
            <?php
            if (!empty($companies)) {
                foreach ($companies AS $company) {
                    $empresa = $company['Empresa'];
                    echo '<option value="' . $empresa['id'] . '">' . $empresa['name'] . '</option>';
                }
            }
            ?>
        </select>        
        <?php
    }

    public function putDropdownPlantillas($label, $id, $data, $is_disabled, $with_label = true) {
        $disabled = $is_disabled ? 'disabled = "disabled"' : '';
        if ($with_label) {
            ?>        
            <div class="label-dropdown"><?php echo $label; ?></div>
        <?php } ?>
            
        <input type="hidden" name="<?php echo $id; ?>_txt" value=""/>
        <select id="<?php echo $id; ?>" name="<?php echo $id; ?>" data-placeholder="<?php echo $label; ?>" <?php echo $disabled; ?>>
            <option rel="" value=""></option>            
            <option rel="<?php echo __('Editar'); ?>" value="new"><?php echo __('Nuevo'); ?></option>            
             <?php
            if (!empty($data)) {
                foreach ($data AS $dato) {
                    $item = $dato['ReportTemplate'];
                    echo '<option value="' . $item['id'] . '" rel="'.$item['fields'].'">' . $item['title'] . '</option>';
                }
            }
            ?>
        </select>        
        <?php
    }

    /**
     * Genera los dropdowns para la parte de agregar usuarios
     * @param array $available_regions las regiones disponibles del usuario logueado
     */
    public function putDropdowns_TipoUsuario($available_regions) {
        $role = $this->_View->getVar('role'); //Role del usuario logueado
        $region_user = $this->_View->getVar('region_user'); //Region del usuario logueado (pueden ser varias)
        $distributors = $this->_View->getVar('distributors');
        $regions_user_array = explode('|', $region_user);
        $credentials = $this->_View->getVar('credentials');
        $typeAdd = $this->_View->getVar('typeAdd');

        $usuario = $this->_View->getVar('usuario');

        $role_edit = $distributor_client_edit = $region_edit = $full_region_edit = '';
        $user_edit = 0;
        $disabled = '';
        if (!is_null($usuario)) {
            $role_edit = $usuario['role'];
            $distributor_client_edit = $usuario['parent'];
            $full_region_edit = $usuario['region'];
            $region_array_edit = explode('|', $usuario['region']);
            $user_edit = $usuario['id'];
        }

        switch ($role) {
            case 'master':
                ?>
                <div class="two-controls" data-section="14" data-intro="<?php echo __('tutorial_tipo_usuario_agregar_cliente_master',true);?>" data-position="top">
                    <select id="user_type" name="user_type" class="validate[required]" data-placeholder="<?php echo __('Tipo de usuario', true); ?>">                
                        <option rel="" value=""></option>
                        <?php if($typeAdd=="user" || $typeAdd==""): ?>
                            <option <?php echo $role_edit == 'admin' ? 'selected="selected"' : ''; ?> rel="admin" value="admin|60"><?php echo __('Administrador', true); ?></option>
                        <?php endif; ?>
                        <option <?php echo $role_edit == 'distributor' ? 'selected="selected"' : ''; ?> rel="distributor" value="distributor|40"><?php echo __('Distribuidor', true); ?></option>
                        <option <?php echo $role_edit == 'client' ? 'selected="selected"' : ''; ?> rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>                
                    </select>   
                    <input type="hidden" name="user_type_txt" id="user_type_txt"/>
                </div>

                <div class="two-controls  last-ctrl <?php echo $role_edit != 'client' ? 'hidden' : ''; ?>"  >
                    <input type="hidden" name="all_distributors_txt" id="all_distributors_txt"/>
                    <select id="all_distributors" name="all_distributors" disabled="disabled" class="validate[required]" data-placeholder="<?php echo __('Distribuidor', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if ($distributors) {
                            foreach ($distributors AS $distribuidor_reg) {
                                //$distribuidor = $distribuidor_reg['Usuario'];
                                $empresa = $distribuidor_reg['Empresa'];
                                echo '<option value="' . $empresa['id'] . '">' . utf8_encode($empresa['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="two-controls last-ctrl" data-section="14" data-intro="<?php echo __('tutorial_region_agregar_cliente_master',true);?>" data-position="top">
                    <input type="hidden" name="user_region_txt" id="user_region_txt"/>
                    <select id="user_region" name="user_region" class="validate[required] last-ctrl" disabled="disabled" data-placeholder="<?php echo __('Region', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if (is_null($usuario)) {//Si es null, no es edicion
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                            }
                        } else {
                            /* @TODO poner todas las regiones, un admin y master pueden cambiar las regiones de los usuarios * */
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                if ($role_edit == 'client') {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                } else {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    } else {
                                        echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                }
                            }
                        }
                        ?>
                    </select>    
                </div>
                <?php
                break;
            case 'admin':
                ?>
                <div class="two-controls">
                    <select id="user_type" name="user_type" class="validate[required]" data-placeholder="<?php echo __('Tipo de usuario', true); ?>">
                        <option rel="" value="" selected="selected"></option>
                        <option <?php echo $role_edit == 'distributor' ? 'selected="selected"' : ''; ?> rel="distributor" value="distributor|40"><?php echo __('Distribuidor', true); ?></option>
                        <option <?php echo $role_edit == 'client' ? 'selected="selected"' : ''; ?> rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>
                    </select>
                    <input type="hidden" name="user_type_txt" id="user_type_txt"/>
                </div>
                <div class="two-controls last-ctrl <?php echo $role_edit != 'client' ? 'hidden' : ''; ?>">
                    <input type="hidden" name="all_distributors_txt" id="all_distributors_txt"/>
                    <select id="all_distributors" name="all_distributors" class="validate[required]" data-placeholder="<?php echo __('Distribuidor', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if ($distributors) {
                            foreach ($distributors AS $distribuidor_reg) {
                                //$distribuidor = $distribuidor_reg['Usuario'];
                                $empresa = $distribuidor_reg['Empresa'];
                                echo '<option value="' . $empresa['id'] . '">' . utf8_encode($empresa['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="two-controls last-ctrl">
                    <input type="hidden" name="user_region_txt" id="user_region_txt"/>
                    <select id="user_region" name="user_region" class="validate[required] last-ctrl" data-placeholder="<?php echo __('Region', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if (is_null($usuario)) {//Si es null, no es edicion
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                            }
                        } else {
                            /* @TODO poner todas las regiones, un admin y master pueden cambiar las regiones de los usuarios * */
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                if ($role_edit == 'client') {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                } else {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    } else {
                                        echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                }
                            }
                        }
                        ?>
                    </select>     
                </div>
                <?php
                break;
                case 'manager':
                ?>
                <div class="two-controls">
                    <select id="user_type" name="user_type" class="validate[required]" data-placeholder="<?php echo __('Tipo de usuario', true); ?>">
                        <option rel="" value="" selected="selected"></option>
                        <option <?php echo $role_edit == 'client' ? 'selected="selected"' : ''; ?> rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>
                    </select>
                    <input type="hidden" name="user_type_txt" id="user_type_txt"/>
                </div>
                <div class="two-controls last-ctrl <?php echo $role_edit != 'client' ? 'hidden' : ''; ?>">
                    <input type="hidden" name="all_distributors_txt" id="all_distributors_txt"/>
                    <select id="all_distributors" name="all_distributors" class="validate[required]" data-placeholder="<?php echo __('Distribuidor', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if ($distributors) {
                            foreach ($distributors AS $distribuidor_reg) {
                                //$distribuidor = $distribuidor_reg['Usuario'];
                                $empresa = $distribuidor_reg['Empresa'];
                                echo '<option value="' . $empresa['id'] . '">' . utf8_encode($empresa['name']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="two-controls last-ctrl">
                    <input type="hidden" name="user_region_txt" id="user_region_txt"/>
                    <select id="user_region" name="user_region" class="validate[required] last-ctrl" data-placeholder="<?php echo __('Region', true); ?>">
                        <option value="" rel="0"></option>
                        <?php
                        if (is_null($usuario)) {//Si es null, no es edicion
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                            }
                        } else {
                            /* @TODO poner todas las regiones, un admin y master pueden cambiar las regiones de los usuarios * */
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                if ($role_edit == 'client') {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                } else {
                                    if ($region['short_identificator'] == $full_region_edit) {
                                        echo '<option selected="selected" value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    } else {
                                        echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                    }
                                }
                            }
                        }
                        ?>
                    </select>     
                </div>
                <?php
                break;
            case 'distributor':
                //var_dump($credentials);
                ?>
                <?php if ($user_edit != $credentials['id']) { ?>
                    <div class="two-controls">
                        <select id="user_type" name="user_type" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Tipo de usuario', true); ?>">                            
                            <option rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>                          
                        </select> 
                        <input type="hidden" name="user_type_txt" id="user_type_txt" value="client|20"/>
                    </div>
                    <div class="two-controls last-ctrl">
                        <input type="hidden" name="all_distributors_txt" id="all_distributors_txt" value="<?php echo $credentials['id_empresa']; ?>"/>
                        <select id="all_distributors" name="all_distributors" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Distribuidor', true); ?>">
                            <?php
                            echo '<option value="' . $credentials['id_empresa'] . '">' . utf8_encode($credentials['name_company']) . '</option>';
                            ?>
                        </select>
                    </div>
                    <div class="two-controls last-ctrl hidden">
                        <input type="hidden" name="user_region_txt" id="user_region_txt"/>
                        <select id="user_region" name="user_region" class="validate[required]">
                            <?php
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                //Se ponen solo las regiones que tiene el usuario logueado
                                if (in_array($region['short_identificator'], $regions_user_array)) {
                                    echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php } else { ?> 
                    <div class="two-controls">
                        <select id="user_type" name="user_type" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Tipo de usuario', true); ?>">                            
                            <option rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>                          
                        </select>    
                        <input type="hidden" name="user_type_txt" id="user_type_txt" value="client|20"/>
                    </div>
                    <div class="two-controls last-ctrl">
                        <select id="all_distributors" name="all_distributors" class="validate[required]" disabled="disabled" data-placeholder="<?php echo __('Distribuidor', true); ?>">
                            <option rel="distributor" value="distributor|40"><?php echo __('Distribuidor', true); ?></option>
                        </select>
                    </div>
                    <div class="two-controls last-ctrl hidden">
                        <select id="user_region" name="user_region" class="validate[required] last-ctrl">
                            <?php
                            foreach ($available_regions AS $region) {
                                $region = $region['Region'];
                                //Se ponen solo las regiones que tiene el usuario logueado
                                if (in_array($region['short_identificator'], $regions_user_array)) {
                                    echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php } ?>

                <?php
                break;
            case 'client':
                ?>
                <input type="hidden" name="user_type_txt" id="user_type_txt" value="client|20"/>
                <select id="user_type" name="user_type" class="validate[required]">
                    <option rel="client" value="client|20"><?php echo __('Cliente', true); ?></option>                       
                </select>                
                <select id="user_region" name="user_region" class="validate[required] last-ctrl">
                    <?php
                    foreach ($available_regions AS $region) {
                        $region = $region['Region'];
                        //Se ponen solo las regiones que tiene el usuario logueado
                        if (in_array($region['short_identificator'], $regions_user_array)) {
                            echo '<option value="' . $region['short_identificator'] . '">' . utf8_encode($region['name']) . '</option>';
                        }
                    }
                    ?>
                </select>

                <?php
                break;
        }
    }

}
