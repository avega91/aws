<?php

/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file TransactionsComponent.php
 *     Component to manage common operations
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2014
 */
App::import("vendors", "autoload", array("file" => "autoload.php'"));
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class TransactionsComponent extends Component {

    var $components = array('Core','Notifications'); // the other component your component uses

    /**
     * saving new report template if needed
     * @param string $report_template report template name
     * @return int last insert row id
     */

    public function addReportTemplate($report_template, $fields) {
        $this->ReportTemplate = ClassRegistry::init('ReportTemplate');
        $id_report_template = 0;
        if ($report_template != '') {//Se selecciono o proporciono una plantilla
            //Si el valor proporcionado es un id (es numerico)
            if (is_numeric($report_template)) {
                $id_report_template = $report_template;
                $this->ReportTemplate->id = $id_report_template;
                $this->ReportTemplate->saveField('fields', $fields);                
            } else if (!$this->ReportTemplate->findByTitle($report_template)) {//Se verifica si existe el tag del corporativo
                $this->ReportTemplate->save(array('title' => $report_template,'fields'=> $fields)); //Se guarda 
                $id_report_template = $this->ReportTemplate->getInsertID(); //obtenemos el ultimo registro insertado
            } else {
                $id_report_template = -1;
            }
        }
        return $id_report_template;
    }
    
    /**
     * saving new corporate
     * @param string $corporate corporate name
     * @return int last insert row id
     */

    public function addCorporate($corporate, $type, $region) {
        $this->Corporativo = ClassRegistry::init('Corporativo');
        $id_corporativo = 0;
        $region = preg_match("/\bUST-\b/i", $region) ? 'US' : $region;
        $region = in_array($region, array('MX1','MX2')) ? 'MX' : $region;
        $region = in_array($region, array('NORTH','NORTHEAST','WEST','MIDWEST','EAST','MIDEAST','SOUTH','SOUTHEAST','SOUTHWEST','US')) ? 'US' : $region;
        $region = preg_match("/\bCA-\b/i", $region) ? 'CA' : $region;
        $region = in_array($region, array('CANADA')) ? 'CA' : $region;

        if ($corporate != '') {//Se selecciono o proporciono un corporativo
            //Si el valor proporcionado es un id (es numerico)
            if (is_numeric($corporate)) {
                $id_corporativo = $corporate;
            } else if (!$this->Corporativo->findByNameAndRegion($corporate, $region)) {//Se verifica si existe el tag del corporativo
                $this->Corporativo->save(array('name' => $corporate, 'type' => $type, 'region' => $region)); //Se guarda
                $id_corporativo = $this->Corporativo->getInsertID(); //obtenemos el ultimo registro insertado
            } else {
                $id_corporativo = -1;
            }
        }
        return $id_corporativo;
    }

    /**
     * saving new company
     * @param int $corporativo id corporate to associate
     * @param string $empresa name of enterprise
     * @param string $ciudad city location of enterprise
     * @param string $direccion full address enterprise
     * @param string $logo_empresa path to enterprise logo
     * @param string $type type company (client, distributor, admin, master)
     * @param int $parent_dist for client company, indicate parent company distributor
     * @param int $region_usuario region for associated user for company
     * @param int $active flag for indicate active or not company
     * @param int $aprobado flag for indicate aproved or not company
     * @return int last insert row id
     */
    public function addCompany($corporativo, $empresa, $ciudad, $direccion, $logo_empresa, $type, $parent_dist, $region_usuario, $active, $aprobado) {
        $this->Empresa = ClassRegistry::init('Empresa');
        $id_empresa = 0;
        if (is_numeric($empresa)) {//Se selecciono una empresa existente
            $id_empresa = $empresa;
            if ($corporativo > 0) {//si el corporativo es mayor que 0, actualizar registro
                $this->Empresa->id = $id_empresa;                
                $this->Empresa->saveField('id_corporativo', $corporativo);
            }else{//Solo se selecciono la empresa para agregar un usuario, pero es posible que cambio la ciudad
                $this->Empresa->id = $id_empresa;    
                $this->Empresa->saveField('city', $ciudad);                
            }
        } else if (!$this->Empresa->findByNameAndDeleted($empresa,0)) {//Se verifica si existe el el nombre de esa empresa y que no este borrada
            $new_company = array('id_corporativo' => $corporativo, 'name' => $empresa, 'city' => $ciudad, 'address' => $direccion, 'path_image' => $logo_empresa, 'type' => $type, 'parent' => $parent_dist, 'region' => $region_usuario, 'active' => $active, 'aprobado' => $aprobado, 'last_update' => date('Y-m-d H:i:s'));
            $this->Empresa->save($new_company);
            $id_empresa = $this->Empresa->getInsertID(); //obtenemos la ultima empresa insertada
            /*
             * Guardamos la notificacion *
             * ************************** */
            $this->Notifications->companySaved($id_empresa);
        } else {
            $id_empresa = -1;
        }
        return $id_empresa;
    }

    /**
     * Update company logo for a company id
     * @param string $path_image path to image
     * @param int $company_id company id
     */
    public function updateCompanyLogo($path_image, $company_id) {
        $this->Empresa = ClassRegistry::init('Empresa');
        $nuevo_logo_empresa = new File($path_image);
        if ($nuevo_logo_empresa->exists()) {
            $empresa = $this->Empresa->findById($company_id);
            if ($empresa['Empresa']['path_image'] != $path_image) {//Si son diferentes es porque cambio la imagen
                $diferencial = time() . '_';
                $sha_company = sha1($company_id);
                $dest_path_img = _COMPANY_DATA . $sha_company;
                new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
                //new Folder($dest_path_img, true); //true para crearlo sino existe el folder (folder de la empresa)
                $name_file = $diferencial . $sha_company . '.' . $nuevo_logo_empresa->ext(); //nombre del archivo de la imagen de la empresa

                $old_img_empresa = new File($empresa['Empresa']['path_image']);
                if ($old_img_empresa->exists()) {//borramos la anterior imagen si es que existe
                    $old_img_empresa->delete();
                }

                $data = file_get_contents($path_image);

                $dest_path_img = $dest_path_img . '/' . $name_file;
                $nuevo_logo_empresa->copy($dest_path_img, true);
                $nuevo_logo_empresa->delete(); //Eliminamos el anterior
                $this->Empresa->id = $company_id;
                $this->Empresa->saveField('path_image', $dest_path_img);
                $this->Empresa->saveField('image', $data);
            }
        }
    }

    /**
     * Update user image profile for a user id 
     * @param string $path_image path to image
     * @param int $company_id id user company
     * @param int $user_id id user
     */
    public function updateUserLogo($path_image, $company_id, $user_id) {
        $this->UsuariosEmpresa = ClassRegistry::init('UsuariosEmpresa');
        $nueva_imagen_perfil = new File($path_image);
        if ($nueva_imagen_perfil->exists()) {//Movemos la imagen de la empresa a un folder privado
            $usuario = $this->UsuariosEmpresa->findById($user_id);
            if ($usuario['UsuariosEmpresa']['path_image'] != $path_image) {//Si son diferentes es porque cambio la imagen
                $diferencial = time() . '_';
                $sha_company = sha1($company_id);
                $sha_user = sha1($user_id);
                $dest_path_img = _ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _USERS_FOLDER;
                new Folder($dest_path_img, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
                $name_file = $diferencial . $sha_user . '.' . $nueva_imagen_perfil->ext(); //nombre del archivo de la imagen del usuario            

                $old_img_usuario = new File($usuario['UsuariosEmpresa']['path_image']);
                if ($old_img_usuario->exists()) {
                    $old_img_usuario->delete();
                }

                $data = file_get_contents($path_image);

                $dest_path_img = $dest_path_img . '/' . $name_file;
                $nueva_imagen_perfil->copy($dest_path_img, true);
                $nueva_imagen_perfil->delete(); //Eliminamos el archivo anterior

                $this->UsuariosEmpresa->id = $user_id;
                $this->UsuariosEmpresa->saveField('path_image', $dest_path_img);
                $this->UsuariosEmpresa->saveField('image', $data);
                //$this->UsuariosEmpresa->updateImageProfile($user_id, $dest_path_img);
            }
        }
    }

    public function addPictureForCompany($company_id, $conveyor_id, $path_image, $taken_at = false, $name = "", $description = "", $folder_id = 0) {
        $taken_at = !$taken_at ? date('Y-m-d H:i:s') : $taken_at;
        $taken_at = $this->Core->transformDateLanguagetoMysqlFormat($taken_at,'-');
        $id_image = 0;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->ClientItem = ClassRegistry::init('ClientItem');
        $conveyor_img = new File($path_image);
        if ($conveyor_img->exists()) {//Movemos la imagen de la empresa a un folder privado
            $empresa = $this->Empresa->findById($company_id);
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _IMGS_CONVEYOR_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_img = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _IMGS_CONVEYOR_FOLDER;

            /* new Folder(_COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder (folder de la empresa)
              $dest_path_img = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER;
              new Folder($dest_path_img, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa) */
            $dest_name_file = $conveyor_img->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);

            $name_file = $dest_name_file . '.' . $conveyor_img->ext(); //nombre del archivo de la imagen del usuario


            $dest_path_img = $dest_path_img . '/' . $name_file;
            $conveyor_img->copy($dest_path_img, true);
            $conveyor_img->delete(); //Eliminamos el archivo anterior

            $name = $name == "" ? __('Imagen principal', true) : $name;
            $description = $description == "" ? __('Imagen principal', true) : $description;
            $this->ClientItem->save(array(
                'type' => Item::IS_CLIENT_IMAGE,
                'name' => $name,
                'description' => $description,
                'path' => $dest_path_img,
                'client_id' => $company_id,
                'parent_folder' => $folder_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            $id_image = $this->ClientItem->getInsertID(); //obtenemos la ultima foto insertada
        }

        return $id_image;
    }

    /**
     * Add image to conveyor
     * @param int $company_id company id
     * @param int $conveyor_id conveyor id
     * @param string $path_image path to image
     * @return int
     */
    public function addPictureConveyorForCompany($company_id, $conveyor_id, $path_image, $taken_at = false, $name = "", $description = "", $folder_id = 0) {
        $taken_at = !$taken_at ? date('Y-m-d H:i:s') : $taken_at;
        $taken_at = $this->Core->transformDateLanguagetoMysqlFormat($taken_at,'-');
        $id_image = 0;  
        $path_cover_image = '';      
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Image = ClassRegistry::init('Image');
        $conveyor_img = new File($path_image);
        if ($conveyor_img->exists()) {//Movemos la imagen de la empresa a un folder privado
            $empresa = $this->Empresa->findById($company_id);
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _IMGS_CONVEYOR_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_img = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _IMGS_CONVEYOR_FOLDER;

            /* new Folder(_COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder (folder de la empresa)
              $dest_path_img = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER;
              new Folder($dest_path_img, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa) */
            $dest_name_file = $conveyor_img->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);
            
            $name_file = $dest_name_file . '.' . $conveyor_img->ext(); //nombre del archivo de la imagen del usuario            
            
            
            $dest_path_img = $dest_path_img . '/' . $name_file;
            $conveyor_img->copy($dest_path_img, true);
            $conveyor_img->delete(); //Eliminamos el archivo anterior

            $path_cover_image = $dest_path_img;

            /*
            $name = $name == "" ? __('Imagen principal', true) : $name;
            $description = $description == "" ? __('Imagen principal', true) : $description;
            $this->Image->save(array(
                'nombre' => $name,
                'descripcion' => $description,
                'path' => $dest_path_img,
                'parent_conveyor' => $conveyor_id,
                'parent_folder' => $folder_id,
                'actualizada' => date('Y-m-d H:i:s'),
                'taken_at' => $taken_at.' '.date('H:i:s')
            ));
            $id_image = $this->Image->getInsertID(); //obtenemos la ultima foto insertada*/
        }

        //return $id_image;
        return $path_cover_image;
    }

    public function addVideoForCompany($company_id, $conveyor_id, $path_video, $taken_at = false, $name = "", $description = "", $folder_id = 0) {
        $taken_at = !$taken_at ? date('Y-m-d H:i:s') : $taken_at;
        $taken_at = $this->Core->transformDateLanguagetoMysqlFormat($taken_at);
        $id_video = 0;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->ClientItem = ClassRegistry::init('ClientItem');

        $conveyor_video = new File($path_video);

        if ($conveyor_video->exists()) {//Movemos la imagen de la empresa a un folder privado
            $empresa = $this->Empresa->findById($company_id);
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _VIDEOS_CONVEYOR_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_video = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _VIDEOS_CONVEYOR_FOLDER;

            $path_video = $dest_path_video; //end path for video

            $dest_name_file = $conveyor_video->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file).'_c';//end name for video

            $name_file = $dest_name_file . '.' . $conveyor_video->ext(); //nombre del archivo de video del usuario

            $dest_path_video = $path_video . '/' . $name_file; //full path for user video

            $conveyor_video->copy($dest_path_video, true); //copy video from tmp to final path

            if($conveyor_video->ext()!='mp4'){
                $thumb_video = $this->Core->process_video($path_video, $dest_name_file, $conveyor_video->ext());
            }
            $thumb_video = "";

            //$final_file_noext = $path_video . '/' . $dest_name_file . '_c';
            $final_file_noext = $path_video . '/' . $dest_name_file;
            $thumb_video = $this->Core->get_fast_thumbnail_video($path_video, $dest_name_file.'.mp4');

            $name = $name == "" ? $conveyor_video->name() : $name;
            $description = $description == "" ? __('Video principal', true) : $description;
            $this->ClientItem->save(array(
                'type' => Item::IS_CLIENT_VIDEO,
                'name' => $name,
                'description' => $description,
                'path' => $final_file_noext,
                'thumbnail_path_video' => $thumb_video,
                'client_id' => $company_id,
                'parent_folder' => $folder_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ));

            $conveyor_video->delete(); //Eliminamos el archivo anterior

            $id_video = $this->ClientItem->getInsertID(); //obtenemos la ultima foto insertada
        }

        return $id_video;
    }

    /**
     * Add video to conveyor
     * @param int $company_id company id
     * @param int $conveyor_id conveyor id
     * @param string $path_video path to image
     * @return int
     */
    public function addVideoConveyorForCompany($company_id, $conveyor_id, $path_video, $taken_at = false, $name = "", $description = "", $folder_id = 0) {
        $taken_at = !$taken_at ? date('Y-m-d H:i:s') : $taken_at;
        $taken_at = $this->Core->transformDateLanguagetoMysqlFormat($taken_at);
        $id_video = 0;
        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Movie = ClassRegistry::init('Movie');

        $conveyor_video = new File($path_video);

        if ($conveyor_video->exists()) {//Movemos la imagen de la empresa a un folder privado
            $empresa = $this->Empresa->findById($company_id);
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _VIDEOS_CONVEYOR_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_video = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _VIDEOS_CONVEYOR_FOLDER;

            $path_video = $dest_path_video; //end path for video

            $dest_name_file = $conveyor_video->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file).'_c';//end name for video

            $name_file = $dest_name_file . '.' . $conveyor_video->ext(); //nombre del archivo de video del usuario

            $dest_path_video = $path_video . '/' . $name_file; //full path for user video

            $conveyor_video->copy($dest_path_video, true); //copy video from tmp to final path

            if($conveyor_video->ext()!='mp4'){
                $thumb_video = $this->Core->process_video($path_video, $dest_name_file, $conveyor_video->ext());
            }
            $thumb_video = "";

            //$final_file_noext = $path_video . '/' . $dest_name_file . '_c';
            $final_file_noext = $path_video . '/' . $dest_name_file;
            $thumb_video = $this->Core->get_fast_thumbnail_video($path_video, $dest_name_file.'.mp4');

            $name = $name == "" ? $conveyor_video->name() : $name;
            $description = $description == "" ? __('Video principal', true) : $description;
            $this->Movie->save(array(
                'nombre' => $name,
                'descripcion' => $description,
                'path' => $final_file_noext,
                'thumbnail_path' => $thumb_video,
                'parent_conveyor' => $conveyor_id,
                'parent_folder' => $folder_id,
                'actualizada' => date('Y-m-d H:i:s'),
                'taken_at' => $taken_at.' '.date('H:i:s')
            ));

            $conveyor_video->delete(); //Eliminamos el archivo anterior

            $id_video = $this->Movie->getInsertID(); //obtenemos la ultima foto insertada
        }

        return $id_video;
    }


    public function addReportForCompany($company_id, $conveyor_id, $path_file, $name = "", $description = "", $folder_id = 0) {
        $id_report = 0;
        $dest_path_pdf = '';

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->ClientItem = ClassRegistry::init('ClientItem');
        $empresa = $this->Empresa->findById($company_id);
        $conveyor_pdf_report = new File($path_file);
        if ($conveyor_pdf_report->exists()) {//Si el archivo existe, no hay que crear ningun pdf
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_pdf = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER;

            $dest_name_file = $conveyor_pdf_report->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);
            $name_file = $dest_name_file . '.' . $conveyor_pdf_report->ext(); //nombre del archivo de video del usuario

            $dest_path_pdf = $dest_path_pdf . '/' . $name_file;
            $conveyor_pdf_report->copy($dest_path_pdf, true);

            //$name = $name == "" ? $conveyor_pdf_report->name() : $name;
            //$description = ""; //Si viene archivo, es porque no viene contenido manual

            $file_report = $dest_path_pdf;

            $conveyor_pdf_report->delete(); //Eliminamos el archivo anterior
        } else {
            ob_start();
            include('files/templates/conti_report.php');
            $template_report = ob_get_clean();

            $description = stripcslashes($description);
            $template_report = str_replace('{company_logo}', $empresa['Empresa']['path_image'], $template_report);
            $template_report = str_replace('{title_report}', $name, $template_report);
            $template_report = str_replace('{content_report}', $description, $template_report);

            $dompdf = new DOMPDF();
            $dompdf->set_paper('letter', 'portrait');
            $dompdf->load_html($template_report);
            $dompdf->render();
            $output = $dompdf->output();

            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);
            $path_file = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER;
            new Folder($path_file, true); //true para crearlo sino existe el folder

            $file_report = $path_file . '/' . $this->Core->sanitize($name) . '_' . date('mdY') . '_' . uniqid() . '.pdf';
            file_put_contents($file_report, $output);
        }

        $credentials = $this->Core->getAppCredentials();
        $this->ClientItem->save(array(
            'type' => Item::IS_CLIENT_REPORT,
            'name' => $name,
            'path' => $file_report,
            'client_id' => $company_id,
            'parent_folder' => $folder_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $id_report = $this->ClientItem->getInsertID(); //obtenemos la ultima foto insertada
        return $id_report;
    }
    /**
     * Add report to conveyor
     * @param int $company_id company id
     * @param int $conveyor_id conveyor id
     * @param string $path_file path to file
     * @param string $name name for report
     * @param string $description content for report
     * @return int
     */
    public function addReportConveyorForCompany($company_id, $conveyor_id, $path_file, $name = "", $description = "", $folder_id = 0) {
        $id_report = 0;
        $dest_path_pdf = '';

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Report = ClassRegistry::init('Report');
        $empresa = $this->Empresa->findById($company_id);
        $conveyor_pdf_report = new File($path_file);
        if ($conveyor_pdf_report->exists()) {//Si el archivo existe, no hay que crear ningun pdf
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_pdf = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER;

            $dest_name_file = $conveyor_pdf_report->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);
            $name_file = $dest_name_file . '.' . $conveyor_pdf_report->ext(); //nombre del archivo de video del usuario            

            $dest_path_pdf = $dest_path_pdf . '/' . $name_file;
            $conveyor_pdf_report->copy($dest_path_pdf, true);

            //$name = $name == "" ? $conveyor_pdf_report->name() : $name;
            //$description = ""; //Si viene archivo, es porque no viene contenido manual

            $file_report = $dest_path_pdf;

            $conveyor_pdf_report->delete(); //Eliminamos el archivo anterior            
        } else {
            ob_start();
            include('files/templates/conti_report.php');
            $template_report = ob_get_clean();

            $description = stripcslashes($description);
            $template_report = str_replace('{company_logo}', $empresa['Empresa']['path_image'], $template_report);
            $template_report = str_replace('{title_report}', $name, $template_report);
            $template_report = str_replace('{content_report}', $description, $template_report);
            
            $dompdf = new DOMPDF();
            $dompdf->set_paper('letter', 'portrait');
            $dompdf->load_html($template_report);
            $dompdf->render();
            $output = $dompdf->output();

            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);
            $path_file = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _REPORTS_FOLDER;
            new Folder($path_file, true); //true para crearlo sino existe el folder

            $file_report = $path_file . '/' . $this->Core->sanitize($name) . '_' . date('mdY') . '_' . uniqid() . '.pdf';
            file_put_contents($file_report, $output);
        }

        $credentials = $this->Core->getAppCredentials();
        $this->Report->save(array(
            'nombre' => $name,
            'file' => $file_report,
            'parent_conveyor' => $conveyor_id,
            'parent_folder' => $folder_id,
            'owner_user' => $credentials['id'],
            'actualizada' => date('Y-m-d H:i:s')
        ));

        $id_report = $this->Report->getInsertID(); //obtenemos la ultima foto insertada
        return $id_report;
    }

    public function addFileForCompany($company_id, $conveyor_id, $path_file, $name = "", $folder_id = 0) {
        $id_file = 0;
        $dest_path_file = '';

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->ClientItem = ClassRegistry::init('ClientItem');
        $empresa = $this->Empresa->findById($company_id);
        $conveyor_file = new File($path_file);
        if ($conveyor_file->exists()) {//Si el archivo existe, no hay que crear ningun pdf
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

//             new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
//             new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
//             new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
//             new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _FILES_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_file = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _FILES_FOLDER;

            $dest_name_file = $conveyor_file->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);
            $name_file = $dest_name_file . '.' . $conveyor_file->ext(); //nombre del archivo de video del usuario

            $dest_path_file = $dest_path_file . '/' . $name_file;
            $conveyor_file->copy($dest_path_file, true);

            $file_report = $dest_path_file;
            $filePathToUpload = WWW_ROOT . $path_file;
            
            $this->Aws->putObjectOnS3($file_report, $filePathToUpload);
            
            $conveyor_file->delete(); //Eliminamos el archivo anterior
        }

        $credentials = $this->Core->getAppCredentials();
        $this->ClientItem->save(array(
            'type' => Item::IS_CLIENT_FILE,
            'name' => $name,
            'path' => $file_report,
            'client_id' => $company_id,
            'parent_folder' => $folder_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $id_file = $this->ClientItem->getInsertID(); //obtenemos la ultima foto insertada
        return $id_file;
    }
    
    /**
     * Add generic files to conveyor
     * @param int $company_id company id
     * @param int $conveyor_id conveyor id
     * @param string $path_file path to file
     * @param string $name name for report
     * @param string $description content for report
     * @return int
     */
    public function addFileConveyorForCompany($company_id, $conveyor_id, $path_file, $name = "", $folder_id = 0) {
        $id_file = 0;
        $dest_path_file = '';

        $this->Empresa = ClassRegistry::init('Empresa');
        $this->Archive = ClassRegistry::init('Archive');
        $empresa = $this->Empresa->findById($company_id);
        $conveyor_file = new File($path_file);
        if ($conveyor_file->exists()) {//Si el archivo existe, no hay que crear ningun pdf
            $diferencial = '_' . time();
            $sha_company = sha1($company_id);
            $sha_conveyor = sha1($conveyor_id);

            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor, true); //true para crearlo sino existe el folder
            new Folder(_ABSOLUTE_PATH._COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _FILES_FOLDER, true); //true para crearlo sino existe el folder (folder de usuarios de la empresa)
            $dest_path_file = _COMPANY_DATA . $sha_company . '/' . _CONVEYORS_FOLDER . '/' . $sha_conveyor . '/' . _FILES_FOLDER;

            $dest_name_file = $conveyor_file->name() . $diferencial;
            $dest_name_file = $this->Core->sanitize($dest_name_file);
            $name_file = $dest_name_file . '.' . $conveyor_file->ext(); //nombre del archivo de video del usuario            

            $dest_path_file = $dest_path_file . '/' . $name_file;
            $conveyor_file->copy($dest_path_file, true);

            $file_report = $dest_path_file;
            $conveyor_file->delete(); //Eliminamos el archivo anterior            
        }

        $credentials = $this->Core->getAppCredentials();
        $this->Archive->save(array(
            'nombre' => $name,
            'path' => $file_report,
            'parent_conveyor' => $conveyor_id,
            'parent_folder' => $folder_id,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        $id_file = $this->Archive->getInsertID(); //obtenemos la ultima foto insertada
        return $id_file;
    }

    /**
     * Procesa el video para generar uno de menor tamanio y con sus respectivo thumbnail
     * @param string $path_video es el path del video original
     * @param string $name_video es el nombre del archivo
     * @param string $ori_ext es la extension del archivo origen
     * @return string el path del thumbnail
     */
    public function process_video($path_video, $name_video, $ori_ext) {
        $videoEncoder = new VideoEncoder();
        $in_video = $path_video . '/' . $name_video . '.' . $ori_ext;
        $out_video = $path_video . '/' . $name_video . '_c.flv';
        $video_for_image = $path_video . '/' . $name_video . '_c.mpeg';

        $out_thumbnail = $path_video . '/' . $name_video . '.jpg';
        //Procesamos el video
        $videoEncoder->convert_video($in_video, $out_video, 480, 360, true);



        //Generamos el video mpeg temporal para sacar la imagen
        $videoEncoder->convert_video($in_video, $video_for_image, 480, 360, false);
        //Generamos un thumbnail del video
        $videoEncoder->grab_image($video_for_image, $out_thumbnail);


        $out_mp4_video = $path_video . '/' . $name_video . '_c.mp4';
        $videoEncoder->convert_video($video_for_image, $out_mp4_video, 480, 360, true);

        //Eliminamos el archivo origen
        $videoEncoder->remove_uploaded_video($in_video);
        $videoEncoder->remove_uploaded_video($video_for_image);

        return $out_thumbnail;
    }

}
