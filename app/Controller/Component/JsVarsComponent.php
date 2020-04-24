<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file JsVarsComponent.php
 *     Component to manage common jsVars system
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class JsVarsComponent extends Component {

    private $jsVars = array();

    public function _getVars() {
        return $this->jsVars;
    }

    public function initMsgsUploader() {
        $this->jsVars['jqueryUploader'] = array(
            'maxNumberOfFiles' => __('Se ha excedido el número de archivos permitido', true),
            'fileNotAllowed' => __('Tipo de archivo no permitido', true),
            'maxFileSize' => __('Archivo demasiado grande', true),
            'minFileSize' => __('Archivo demasiado pequeño', true),
            'customTypeFiles' => __('Solo se permiten archivos: excel, word, powerpoint y pdf', true),
        );
    }

    public function initSystemsVars($language = 'es', $html) {

        $this->jsVars['ajaxPetition'] = array(
            'error' => __('Operación no encontrada', true),
        );

        $this->jsVars['systemLanguage'] = $language;
        $this->jsVars['editorLanguage'] = $language;
        $this->jsVars['timeNotifications'] = '5000';
        $this->jsVars['timeErrorValidation'] = '5000';
        $this->jsVars['timeDragNotifications'] = '30000';
        
        $this->jsVars['location_required'] = __('share_location_required', true);

        $this->jsVars['NextToUserBtn'] = __('Next to User',true);
        $this->jsVars['SaveUserBtn'] = __('Guardar Usuario',true);

        $this->jsVars['textMoreRegs'] = __('Ver mas',true);
        $this->jsVars['rowsToShow'] = 50;
        $this->jsVars['textInactiveManagerCtrl'] = __('Funcion exclusiva para usuarios asociados a un corporativo',true);
        $this->jsVars['textActiveManagerCtrl'] = __('Habilita permisos para ver, editar y agregar transportadores a las empresas del corporativo',true);
        

        $this->jsVars['imagesIsNeededMsg'] = __('Es necesario seleccionar al menos un archivo de imagen', true);
        $this->jsVars['videoIsNeededMsg'] = __('Es necesario seleccionar un archivo de video', true);
        $this->jsVars['pdfIsNeededMsg'] = __('new_needed_pdf', true);
        $this->jsVars['fieldRequiredMsg'] = __('* Este campo es requerido', true);
        $this->jsVars['perfilImageRequiredMsg'] = __('* Es necesario cargar una foto de perfil', true);

        //'message'=>__('Tu sesión expirará en <span id="sessionTimeoutCountdown"></span> segundos.<br /><br />Presiona <b>OK</b> para continuar activo.',true),
        $this->jsVars['idleTimer'] = array('idleTime' => 900000,//1800000/* 900000 milisegundos = 15 min */,
            'message' => __('session_expiring_msg', true),
            'redirect' => $html->url(array('controller' => 'Access', 'action' => 'logout')),
            'setMessageUrl' => $html->url(array('controller' => 'Access', 'action' => 'ax_set_message')),
            'redirectAfter' => 30,
            'keepAlive' => $html->url(array('controller' => 'Access', 'action' => 'refresh')),
            'expiredMessage' => __('session_expired_msg', true),
            'running' => false,
            'timer' => null,
            'titleModalDialog' => __('Aviso de seguridad.', true)
        );
    }

    public function initGlobalAjaxVars($html, $user_code) {
        $this->jsVars['setUnSetMailNotifAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_set_unset_notifications_mail'));
        $this->jsVars['changeLanguageAx'] = $html->url(array('controller' => 'Actions', 'action' => 'ax_change_language'));
        $this->jsVars['saveManualNotificationAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_save_manual_notification'));
        $this->jsVars['sendContactFormAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_send_contact_form'));
        $this->jsVars['updateDDRegionsContactAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_dd_regions_for_contact'));


        $this->jsVars['datatable'] = array(
            'zeroRecords' => __('No results',true),
            'info' => __('Showing %s to %s of %s entries',array('_START_','_END_','_TOTAL_')),
            'search' => __('Search...',true),
            'infoEmpty' => __('No results founded',true),
            'infoFiltered' => __('(Filteres of %s entries)',array('_MAX_')),
            'oPaginate' => array(
                'sFirst' => __('First',true),
                'sPrevious' => __('Prev',true),
                'sNext' => __('Next',true),
                'sLast' => __('Last',true),
            ),
            'exportExcel' => __('Export to Excel',true),
            'exportPdf' => __('Export PDF',true),
        );
    
        //For Buoys
        $this->jsVars['refreshBuoysAx'] = $html->url(array('controller' => 'BuoySystems', 'action' => 'refreshBuoys',$user_code));
        
        $this->jsVars['dropItemToFolderAx'] = $html->url(array('controller' => 'Conveyors', 'action' => 'dropItemToFolder'));
        $this->jsVars['refreshTrackingConveyorsAx'] = $html->url(array('controller' => 'Premium', 'action' => 'refreshTrackingConveyors',$user_code));
        $this->jsVars['refreshCustomReportsAx'] = $html->url(array('controller' => 'Reports', 'action' => 'refreshCustomReports',$user_code));
        $this->jsVars['refreshSavingsAx'] = $html->url(array('controller' => 'Savings', 'action' => 'refreshSavings',$user_code));
        
        //Generic
        $this->jsVars['refreshRecommendedInfoAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'getRecommendedBeltInfoConveyor')); 
        $this->jsVars['refreshLogConveyorAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'refreshLogConveyor'));
        $this->jsVars['refreshCommentItemsAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'refreshCommentsItem'));
        $this->jsVars['getRegionsContactByArea'] = $html->url(array('controller'=>'Ajax','action'=>'getRegionsContactByArea'));

        //For uploader
        $this->jsVars['uploadImgNewsPortadaAx'] = $html->url(array('controller' => 'Uploader', 'action' => 'uploadNewsPortada'));
        $this->jsVars['uploadGenericImgAx'] = $html->url(array('controller' => 'Uploader', 'action' => 'uploadGenericImg'));
        $this->jsVars['uploadGenericVideoAx'] = $html->url(array('controller' => 'Uploader', 'action' => 'uploadGenericVideo'));
        $this->jsVars['uploadGenericFileAx'] = $html->url(array('controller' => 'Uploader', 'action' => 'uploadGenericFile'));
        $this->jsVars['getCroppedImgAx'] = $html->url(array('controller' => 'Uploader', 'action' => 'createGenericCropImage'));

        //For News
        //$this->jsVars['saveRegNewsAx'] = $html->url(array('controller' => 'News', 'action' => 'axProcessAdd'));
        $this->jsVars['getNewsInfoAx'] = $html->url(array('controller' => 'News', 'action' => 'axGetNewsInfo'));

        //For users
        $this->jsVars['updateUserProfile'] = $html->url(array('controller' => 'Users', 'action' => 'axUpdateUserProfile'));
        $this->jsVars['updateCitiesForCountryAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_cities'));
        $this->jsVars['updateStatesForCountryAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_states'));
        $this->jsVars['updateStatesForRegionAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_states_regions'));
        $this->jsVars['updateAllRegionsAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_all_regions'));
        $this->jsVars['updateRegionsForUserAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_load_dd_regions_user'));
        $this->jsVars['getCompaniesByTypeUserAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_get_companies_by_type'));
        $this->jsVars['getClientsDistributorAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_get_clients_distributor'));
        $this->jsVars['getSalespersonDistributorAx'] = $html->url(array('controller' => 'Ajax', 'action' => 'ax_get_salesperson_distributor'));
        
        $this->jsVars['assocClientsAx'] = $html->url(array('controller' => 'Companies', 'action' => 'assocClientsDistributor'));
        
        $this->jsVars['countryLabel'] = __('Pais',true);
        $this->jsVars['stateLabel'] = __('Estado',true);
        
        //For notifications
        $this->jsVars['refreshNotifications'] = $html->url(array('controller' => 'Notifications', 'action' => 'refresh'));
        $this->jsVars['setMailNotificationsUser'] = $html->url(array('controller' => 'Notifications', 'action' => 'setByMail'));

        Configure::load('conveyor_us');
        $this->jsVars['manufacturerFamiliesUS'] = Configure::read('ConveyorUS')['installed_belt']['family'];
        $compounds = Configure::read('ConveyorUS')['installed_belt']['compounds'];
        asort($compounds);
        $this->jsVars['compoundsUS'] = Configure::read('ConveyorUS')['installed_belt']['compounds'];
        $this->jsVars['tensionUS'] = Configure::read('ConveyorUS')['installed_belt']['tension'];
        $this->jsVars['widthsUS'] = Configure::read('ConveyorUS')['installed_belt']['widths'];


        $this->jsVars['setSecurityQuestion'] = $html->url(array('controller' => 'Users', 'action' => 'securityQuestion'));
        $this->jsVars['answerSecurityQuestion'] = $html->url(array('controller' => 'Users', 'action' => 'answerSecurityQuestion'));
        $this->jsVars['getEulaUrl'] = $html->url(array('controller' => 'General', 'action' => 'eulaTermsLogin'));
    }

    public function initSystemMsgs($html) {

        $this->jsVars['systemNotifications'] = array(
            'title' => __('Contiplus', true),
            'uploadProfileImage' => array(
                'titleSuccess' => __('Imagen cargada', true),
                'descriptionSuccess' => __('La imagen seleccionada ha sido cargada', true),
                'titleErrorFileType' => __('Error', true),
                'descriptionErrorFileType' => __('El tipo de archivo no es permitido, solo JPG, GIF ó PNG', true),
                'titleErrorFileSize' => __('Error', true),
                'descriptionErrorFileSize' => __('El archivo es demasiado grande, seleccione uno menor o igual a 2MB', true),
            )
        );

        $this->jsVars['systemMsgs'] = array(
            'genericLoading' => __('Cargando...', true),
            'genericProcessing' => __('Procesando...', true),
            'genericWait' => __('Espere un momento...', true),
            'conveyorsAndFieldsNeeded' => __('Seleccione al menos un transportador y alguno de los campos de transportador', true),
            'confirmExportConveyorHistory' => __('Fecha de falla ha sido llenado. Al llenar este dato la informacion de banda instalada se exportara a history y los campos respaldados se limpiaran. ¿Está de acuerdo?', true),
            'improvementsCenterNeeded' => __('v.2.5.1.ImprovementsCenterNeeded', true),
            'searchText' => __('Buscar', true),
        );
        $this->jsVars['dialogSystems'] = array(
            'settingsDialog' => array(
                'loadingMsg' => __('Procesando...', true),
                'successProcess' => __('La operación ha sido procesada.', true)
            )
        );


        $this->jsVars['dialogs'] = array(
            'confirmLeavePage' => array(
                'title' => __('Salir', true),
                'description' => __('¿Realmente desea salir de la página actual? Se perderán todos los cambios', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmPostNews' => array(
                'title' => __('Publicación de noticia', true),
                'description' => __('¿Realmente desea publicar la noticia seleccionada?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmUnPostNews' => array(
                'title' => __('Despublicar noticia', true),
                'description' => __('¿Realmente desea despublicar la noticia seleccionada?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmAction' => array(
                'title' => __('Atencion!!!', true),
                'description' => __('¿Realmente desea proceder con la accion seleccionada?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmSuspendUser' => array(
                'title' => __('Suspensión de usuario', true),
                'description' => __('¿Realmente desea suspender el usuario seleccionado?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmUnsuspendUser' => array(
                'title' => __('Reactivación de usuario', true),
                'description' => __('¿Realmente desea reactivar el usuario seleccionado?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmDeleteUser' => array(
                'title' => __('Eliminación de usuario', true),
                'description' => __('¿Realmente desea eliminar el usuario seleccionado?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmDeleteItem' => array(
                'title' => __('Eliminación de elemento', true),
                'description' => __('¿Realmente desea eliminar el elemento seleccionado?', true),
                'descriptionPin' => __('¿Realmente desea quitar el elemento de la tabla?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmMovilDeleteItem' => array(
                'title' => __('Eliminación de elemento', true),
                'description' => __('new_confirm_remove_item', true),
                'btnOk' => __('new_ok_btn_dialog', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmApproveItem' => array(
                'title' => __('Aprobar elemento', true),
                'description' => __('¿Realmente desea aprobar el elemento seleccionado?', true),
                'btnOk' => __('Confirmar', true),
                'btnCancel' => __('Cancelar', true)
            ),
            'confirmDeletion' => array(
                'title' => __('Eliminación de elemento', true),
                'description' => __('Are you sure you want to delete this item?', true),
                'btnOk' => __('Yes, delete', true),
                'btnCancel' => __('Cancelar', true)
            ),
        );
    }

    public function initLangWidgets() {
        $this->jsVars['contiUploader'] = array(
            'maxSizeVideoUpload' => _SIZE_UPLOAD_VIDEO,
            'notValidImgFile' => __('No es un archivo de imagen valido', true),
            'notValidPdfFile' => __('No es un archivo pdf valido', true),
            'notValidVideoFile' => __('No es un archivo de video valido', true),
            'uploadPathNotDefined' => __('No se definio la ruta donde se cargara el archivo', true),
            'notValidFileType' => __('El tipo de archivo es incorrecto', true),
            'fizeSizeExceeded' => __('Tamanio de archivo excede el minimo permitido. Maximo %s Mb', _SIZE_UPLOAD_VIDEO_MB),
            'notSupportedUpload' => __('La carga de imagenes no esta soportada para este explorador, utilice Firefox, Chrome o Safary.', true)
        );

        $this->jsVars['cropper'] = array(
            'saveBtn' => __('Guardar', true)
        );

        $this->jsVars['nicEdit'] = array(
            'urlLabel' => __('Url', true),
            'titleLink' => __('Titulo', true),
            'labelOpen' => __('Abrir en', true),
            'openSameWindow' => __('Misma Ventana', true),
            'openNewWindow' => __('Nueva Ventana', true),
            'linkNeededMsg' => __('Proporcione una url valida', true),
            'boldButton' => __('Negrita', true),
            'italicButton' => __('Cursiva', true),
            'underlineButton' => __('Subrayado', true),
            'leftAlignedButton' => __('Alinear a la izquierda', true),
            'rightAlignedButton' => __('Alinear a la derecha', true),
            'centerAlignedButton' => __('Centrar texto', true),
            'justifyAlignedButton' => __('Justificar texto', true),
            'insertOlButton' => __('Insertar lista ordenada', true),
            'insertUlButton' => __('Insertar lista desordenada', true),
            'insertSupButton' => __('Superindice', true),
            'insertSubButton' => __('Subindice', true),
            'strikeButton' => __('Tachado', true),
            'delFormatButton' => __('Eliminar formato', true),
            'identTextButton' => __('Indentar texto', true),
            'delIdentTextButton' => __('Quitar Indentacion de texto', true),
            'addHrButton' => __('Agregar separador horizontal', true),
            'addLinkButton' => __('Agregar link', true),
            'delLinkButton' => __('Eliminar link', true),
            'uploadImgBtn' => __('Cargar imagen', true),
            'addImgBtn' => __('Agregar imagen', true),
            'submitBtnLabel' => __('Procesar', true),
            'notSupportedUpload' => __('La carga de imagenes no esta soportada para este explorador, utilice Firefox, Chrome o Safary.', true),
        );
    }

}
