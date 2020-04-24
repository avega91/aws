<?php
/**
 * Created by PhpStorm.
 * User: humannair
 * Date: 3/8/18
 * Time: 2:28 PM
 */
?>
<div class="title-page clients-section" title="<?php echo $empresa['name']; ?>">
    <?php echo $empresa['name']; ?>
</div>
<div class="full-page">
    <div class="data-page">
        <div class="info-section">
        </div>
        <div class="data-section" id="client_data">
            <?php
            $secureClientParams = $this->Utilities->encodeParams($empresa['id']);
            $urlAddItemClient = $this->Html->url(array('controller' => 'Companies', 'action' => 'addItemClient', $secureClientParams['item_id'], $secureClientParams['digest']));
            $addFileUrl = $this->Html->url(array('controller' => 'Companies', 'action' => 'addFileClient', $secureClientParams['item_id'], $secureClientParams['digest']));

            $bucket_data = '<ul class="dashboard-list items-dashboard">';

            $bucket_data .= '<li class="circular-menu add-item-dashboard-circular" >'
                . '<ul>';
            $bucket_data .= '<li><a href="#" alt="' . Item::IMAGE . '" class="add-image add-mediaitem-conveyor-link" location-tool="s" dialog-style="photo-dialog" rel="' . $urlAddItemClient . '" title="' . __('Foto', true) . '"></a></li>';
            $bucket_data .= '<li><a href="#" alt="' . Item::VIDEO . '" class="add-video add-mediaitem-conveyor-link" location-tool="s" dialog-style="video-dialog" rel="' . $urlAddItemClient . '" title="' . __('Video', true) . '"></a></li>';
            $bucket_data .= '<li><a href="#" alt="' . Item::REPORT . '" class="add-report add-mediaitem-conveyor-link" location-tool="n" dialog-style="report-dialog" rel="' . $urlAddItemClient . '" title="' . __('Reporte', true) . '"></a></li>';
            $bucket_data .= '<li><a href="#" alt="' . Item::NOTE . '" class="add-note add-mediaitem-conveyor-link" location-tool="n" dialog-style="note-dialog" rel="' . $urlAddItemClient . '" title="' . __('Nota', true) . '"></a></li>';
            $bucket_data .= '<li><a href="#" alt="' . Item::FOLDER . '" class="add-folder add-mediaitem-conveyor-link" location-tool="n" dialog-style="folder-dialog" rel="' . $urlAddItemClient . '" title="' . __('Folder', true) . '"></a></li>';
            $bucket_data .= '<li><a href="#" alt="' . Item::FILE . '" class="add-new-file add-mediaitem-conveyor-link add-file-conveyor" location-tool="n" dialog-style="file-dialog" rel="' . $addFileUrl . '" title="' . __('new_file', true) . '"></a></li>';
            $bucket_data .=  '</ul>'
                . '<button class="add-button" title="' . __("add_item_conveyor", true) . '"></button>'
                . '</li>';



            if (!empty($items)) {
                foreach ($items AS $item) {
                    $clientItem = $item['ClientItem'];

                    $secureItem = $this->Utilities->encodeParams($clientItem['id']);
                    $uniqid_item_dropped_item = $clientItem['type'] . '@' . $secureItem['item_id'] . '@' . $secureItem['digest'];
                    $urlEditItem = $this->Html->url(array('controller' => 'Companies', 'action' => 'editItem', $clientItem['type'], $secureItem['item_id'], $secureItem['digest']));
                    $urlRemoveItem = $this->Html->url(array('controller' => 'Companies', 'action' => 'removeItem', $clientItem['type'], $secureItem['item_id'], $secureItem['digest']));
                    $urlViewItemConveyor = $this->Html->url(array('controller' => 'Companies', 'action' => 'Item', $clientItem['type'], $secureItem['item_id'], $secureItem['digest']));

                    $media_item = '';
                    $class_item = '';
                    $target = '';
                    $confirmMsg = '';
                    $image_lightbox_option = '';
                    $item_link = 'item-dashboard-link';

                    $canEditItem = $canDeleteItem = false;
                    $private_selector = "";
                    switch ($clientItem['type']) {
                        case Item::IS_CLIENT_IMAGE:
                            $image_lightbox_option = '<li><a href="' . $site . $clientItem['path'] . '" class="preview-item-dashboard preview-item-link" title="' . __('Previsualizar', true) . '"></a></li>';
                            $confirmMsg = __('Realmente desea eliminar la imagen seleccionada', $clientItem['name']);
                            $class_item = 'image-item';
                            $media_item = '<div>';
                            if (trim($clientItem['path']) != '') {

                                //$media_item .= $cover_img.'<img src="' . $this->_site . $conveyorItem['path'] . '"/>';
                                $media_item .= $this->ImageSize->resize($clientItem['path'], 130, 100);
                            }
                            $media_item .= '</div>';

                            $canEditItem = true;
                            $canDeleteItem = true;
                            break;

                        case Item::IS_CLIENT_VIDEO:
                            $confirmMsg = __('Realmente desea eliminar el video seleccionado', $clientItem['name']);
                            $class_item = 'video-item';
                            $media_item = '<div><div class="play-indicator"></div>';
                            if (trim($clientItem['thumbnail_path_video']) != '') {
                                $media_item .= '<img src="' . $site . $clientItem['thumbnail_path_video'] . '"/>';
                            } else {
                                $media_item .= '<img src="' . $site . 'img/gallery/thumbnail_video242x125_black.gif"/>';
                            }
                            $media_item .= '</div>';

                            $canEditItem = true;
                            $canDeleteItem = true;
                            break;

                        case Item::IS_CLIENT_FOLDER:
                            $confirmMsg = __('Realmente desea eliminar el folder seleccionado. Los elementos en el folder seran inaccesibles', $clientItem['name']);
                            $class_item = 'folder-item';

                            $canEditItem = true;
                            $canDeleteItem = true;

                            break;

                        case Item::IS_CLIENT_REPORT:
                            $confirmMsg = __('Realmente desea eliminar el reporte seleccionado', $clientItem['name']);
                            $class_item = 'report-item';
                            $target = '_blank';

                            $canEditItem = true;
                            $canDeleteItem = true;
                            break;

                        case Item::IS_CLIENT_FILE:
                            $confirmMsg = __('Realmente desea eliminar el archivo seleccionado', $clientItem['name']);
                            $class_item = 'file-item';
                            $target = '_blank';

                            $canEditItem = true;
                            $canDeleteItem = true;
                            break;


                        case Item::IS_CLIENT_NOTE:
                            $confirmMsg = __('Realmente desea eliminar la nota seleccionada', $clientItem['name']);
                            $class_item = 'note-item';

                            $urlViewItemConveyor = $urlEditItem;
                            $item_link = 'edit-item-link';

                            $canEditItem = true;
                            $canDeleteItem = true;
                            break;
                    }


                    $actions_item = '<ul class="actions-item-dashboard">';
                    //if($this->_credentials['role_company']!=UsuariosEmpresa::IS_CLIENT && !in_array($role, array(UsuariosEmpresa::IS_CLIENT))) {
                    if ($canEditItem) {
                        $actions_item .= '<li><a href="#" class="edit-item-dashboard edit-item-link" title="' . __('Editar', true) . '" rel="' . $urlEditItem . '"></a></li>';
                    }
                    if ($canDeleteItem) {
                        $actions_item .= '<li><a href="#" class="delete-item-dashboard delete-item-link" title="' . __('Eliminar', true) . '" rel="' . $urlRemoveItem . '" conf-msg="' . $confirmMsg . '"></a></li>';
                    }

                    $actions_item .= $image_lightbox_option;
                    $actions_item .= '</ul>';

                    $actions_item .= $private_selector;

                    $fecha_actualizacion = $this->Utilities->timestampToUsDate($clientItem['updated_at']);
                    $bucket_data .= '<li class="item-dashboard ' . $class_item . ' ' . $item_link . '" rel="' . $urlViewItemConveyor . '" item-info="' . $uniqid_item_dropped_item . '" target-link="' . $target . '">' . $actions_item . '<div>';
                    $bucket_data .= $media_item;
                    $bucket_data .= '<p class="title">' . $clientItem['name'] . '</p>';
                    if ($clientItem['type'] == Item::IS_CLIENT_IMAGE || $clientItem['type'] == Item::IS_CLIENT_VIDEO) {
                        //$fecha_visita = $this->Utilities->timestampToCorrectFormatLanguage($conveyorItem['taken_at']);
                        //$fecha_visita = $conveyorItem['taken_at'] != '0000-00-00 00:00:00' ? $this->Utilities->timestampToUsDate($conveyorItem['taken_at']) : '-';
                        $bucket_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion . '</p>';
                       // $conveyors_data .= '<p class="normal-text">' . __('item_date_capture', true) . ': ' . $fecha_visita . '</p>';
                    } else {
                        //$conveyors_data .= '<p class="normal-text">' . $conveyorItem['desc_item'] . '</p>';
                        $bucket_data .= '<p class="normal-text">' . __('Ultima edicion', true) . ': ' . $fecha_actualizacion . '</p>';
                    }
                    $bucket_data .= '</div></li>';
                }
            }

            $bucket_data .= '</ul>';
            echo $bucket_data;
            ?>
        </div>
    </div>
</div>
