<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file NewsController.php
 *     Management of actions for news
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
class NewsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        if (!$this->Session->check(Statistic::GO_NEWS)) {
            /*
             * Se guarda el registro de acceso
             * Save statistic browsing data
             */
            $this->Secure->saveStatisticData($this->credentials['id'], Statistic::GO_NEWS);
            $this->Session->write(Statistic::GO_NEWS, Statistic::GO_NEWS);
        }
    }

    /**
     * Index action for index view
     */
    public function index() {
        $noticias = $this->Noticia->findAll(0, 0);
        $this->set('noticias', $noticias);
    }

    /**
     * Get Html for add form dialog
     */
    public function add() {
        $this->layout = false;
        if ($this->request->is('post')) {
            
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Get html info for a specific news row
     */
    public function axGetNewsInfo() {
        if ($this->request->is('post')) {
            $params = $this->request->data; //get data
            $noticia = $this->Noticia->findById($params['rowid']);
            $this->set('noticia', $noticia);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    /**
     * Function process save / update news row
     */
    public function axProcessAdd() {
        $this->layout = false;
        $response = array();
        $response['success'] = false;
        $response['msg'] = '';

        if ($this->request->is('post')) {
            $params = $this->request->data; //get data
            parse_str($params['formdata'], $data); //parse params
            $wysiwyg_es = $params['wysiwyg_es'];
            $wysiwyg_en = $params['wysiwyg_en'];

            if ((isset($data['news_title_es']) && $data['news_title_es'] != '' && $wysiwyg_es != '<br>') || (isset($data['news_title_en']) && $data['news_title_en'] != '' && $wysiwyg_en != '<br>')) {//its spanish
                $titulo = $data['news_title_es'] . '||' . $data['news_title_en'];
                $contenido = $wysiwyg_es . '||' . $wysiwyg_en;
                $path_image = $data['path_img_portada'];

                $file_img = new File($path_image);
                if ($file_img->exists()) {
                    $path_image = _PATH_COVER_NEWS . $file_img->name;
                    $file_img_loaded = new File($path_image);
                    if (!$file_img_loaded->exists()) {
                        $file_img->copy($path_image, false);
                        $file_img->delete();
                    }
                }

                $inserted_new = (int) $data['last_insert_new'];
                if ($inserted_new <= 0) {//there is not news for update, its save operation
                    $inserted_new = $this->Noticia->save_reg($titulo, $contenido, $path_image, $this->credentials['id']);
                    /*
                     * Guardamos la notificacion *
                     * ************************** */
                    $this->Notifications->newsSaved($inserted_new);
                } else {
                    $this->Noticia->update_reg($inserted_new, $titulo, $contenido, $path_image);
                }

                if ($inserted_new) {
                    $response['success'] = true;
                    $response['inserted_new'] = $inserted_new;
                    $response['path_img_portada'] = $path_image;

                    $response['msg'] = __('La noticia ha sido guardada con exito', true);
                } else {
                    $response['msg'] = __('Ocurrio un problema al guardar la noticia, intente nuevamente', true);
                }
            } else {
                $response['msg'] = __('Para guardar la noticia es necesario capturar al menos un idioma');
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function update() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedNewsParams = $this->Core->decodePairParams($params);
                if ($decodedNewsParams['isOk']) {
                    $news_received = $decodedNewsParams['item_id'];
                    $NewsForUpdate = $this->Noticia->findById($news_received);
                    if (!empty($NewsForUpdate)) {
                        $response['success'] = true;
                        $this->set('noticia', $NewsForUpdate);
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function processUpdate() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $formdata = $this->request->data; //get data
            parse_str($formdata['formdata'], $data); //parseamos el parametro donde viene la info del form    
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedNewsParams = $this->Core->decodePairParams($params);
                if ($decodedNewsParams['isOk']) {
                    $news_received = $decodedNewsParams['item_id'];
                    $noticia = $this->Noticia->findById($news_received);
                    if (!empty($noticia)) {
                        $wysiwyg_es = $formdata['wysiwyg_es'];
                        $wysiwyg_en = $formdata['wysiwyg_en'];
                        if ((isset($data['news_title_es']) && $data['news_title_es'] != '' && $wysiwyg_es != '<br>') || (isset($data['news_title_en']) && $data['news_title_en'] != '' && $wysiwyg_en != '<br>')) {//its spanish
                            $titulo = $data['news_title_es'] . '||' . $data['news_title_en'];
                            $contenido = $wysiwyg_es . '||' . $wysiwyg_en;
                            $noticia['titulo'] = $titulo;
                            $noticia['descripcion'] = $contenido;

                            $path_image = $data['path_img_portada'];

                            $file_img = new File($path_image);
                            if ($file_img->exists()) {//Si la imagen subida existe
                                $path_image = _PATH_COVER_NEWS . $file_img->name; //nueva ruta donde quedara
                                $file_img_loaded = new File($path_image);
                                if (!$file_img_loaded->exists()) {
                                    $file_img->copy($path_image, false);
                                    $file_img->delete();
                                    $noticia['img_portada'] = $path_image;
                                }
                            }


                            if ($this->Noticia->save($noticia)) {
                                $response['msg'] = __('La noticia se actualizo correctamente', true);
                                $response['success'] = true;
                            } else {
                                $response['msg'] = __('Ocurrio un problema al procesar la operacion, intentelo nuevamente', true);
                            }
                            //$path_image = $data['path_img_portada'];
                        } else {
                            $response['msg'] = __('Para guardar la noticia es necesario capturar al menos un idioma');
                        }
                    } else {
                        $response['msg'] = __('Error, el elemento a editar no fue encontrado', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }

            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

    public function delete() {
        $params = isset($this->request->params['pass']) ? $this->request->params['pass'] : array();
        if ($this->request->is('post')) {
            $response = array('success' => false, 'msg' => '');
            if (!empty($params) && count($params) == 2) {
                $decodedNewsParams = $this->Core->decodePairParams($params);
                if ($decodedNewsParams['isOk']) {
                    $news_received = $decodedNewsParams['item_id'];
                    $NewsForDelete = $this->Noticia->findById($news_received);
                    if (!empty($NewsForDelete)) {
                        $this->Noticia->id = $news_received;
                        if ($this->Noticia->saveField('eliminada', 1)) {
                            $response['msg'] = __("La noticia fue eliminada exitosamente", true);
                            $response['success'] = true;
                        } else {
                            $response['msg'] = __('Ocurrio un problema al procesar la operacion, intentelo nuevamente', true);
                        }
                    } else {
                        $response['msg'] = __('No se encontro la informacion asociada', true);
                    }
                } else {
                    $response['msg'] = __('Acceso no autorizado', true);
                }
            } else {
                $response['msg'] = __('Los datos proporcionados son incorrectos', true);
            }
            $this->set('response', $response);
        } else {
            $this->redirect(array('controller' => 'Index', 'action' => 'index'));
        }
    }

}
