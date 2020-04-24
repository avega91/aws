<?php

$path = array_key_exists('HTTP_HOST', $_SERVER) && $_SERVER['HTTP_HOST']=='localhost' ?
    '/Applications/XAMPP/xamppfiles/htdocs/imodco/app/webroot/' : $_SERVER['DOCUMENT_ROOT'].'app/webroot/';
// define('_ABSOLUTE_PATH',$path);

define('_ABSOLUTE_PATH', WWW_ROOT);

/**Control de Accesos */
define('_LOGIN_REDIRECT','/');
define('_LOGOUT_REDIRECT','/Access/login');
define('_LOGOUT_REDIRECT_OK','/Access/logout');


/**ACCESO DE USUARIOS, EN ESTE CASO ACCESO PERSONAL AUTORIZADO **/
/**Indica el nombre de la tabla de la cual se obtendran los usuarios, para loguearse y poder administrar los resultados*/
define('_MODEL_AUTH_BD','usuarios_empresas');
define('_MODEL_AUTH_APP','UsuariosEmpresa');

/**Indica el campo que es la primary key en la tabla arriba indicada **/
define('_ID_FIELD_MODEL','id');

/**Indica el campo que se tomara como usuario para loguearse, en este caso se usa la 'clave_uaslp', pero puede ser la clave de empleado UASLP**/
define('_NAME_FIELD_MODEL','username');

/**Indica el campo que se tomara como password del usuario para loguearse, en este caso se usa la 'password'**/
define('_PASSWORD_FIELD_MODEL','password');

/**Indica el tipo de encriptacion que se esta usando en la tabla, para el campo password **/
define('_TYPECRYPT','sha1');/*Or md5 or none*/


define('_LOGO_CONTINENTAL_PDF','img/logo_continental_pdf.png');
define('_LOGO_CONTITECH_PDF','img/logo_contitech_pdf.png');
define('_LOGO_CONTIPLUS_PDF','img/logo_contiplus_pdf.png');

define('_DEFAULT_DD_USER_IMG','img/default_dd_user_img.png');
define('_DEFAULT_COMPANY_IMG','img/image_default_client_page.png');
define('_PATH_COVER_NEWS','uploads/news/');
define('_COMPANY_DATA','uploads/empresas/');
define('_USERS_FOLDER','colaboradores');
define('_CONVEYORS_FOLDER','bandas');
define('_BUOYS_FOLDER','buoys');
define('_SAVINGS_FOLDER','savings');
define('_REPORTS_FOLDER','reportes');
define('_FILES_FOLDER','files');

define('_IMGS_CONVEYOR_FOLDER','images');
define('_VIDEOS_CONVEYOR_FOLDER','videos');
define('_PDFS_CONVEYOR_FOLDER','pdfs');

define('_EXT_UPLOAD_VIDEO','\.(mp4|flv|mov|wmv|mpeg|3gp|wma|avi)$');
define('_SIZE_UPLOAD_VIDEO',104857600);//4 MB
define('_SIZE_UPLOAD_VIDEO_MB',100);//4 MB

define('_SHA1_SIGNATURE', 'SignSha1System');        /**Digest application**/
define('_B64_SIGNATURE', 'Sign64');

define('IS_ESPANIOL','es');
define('IS_ENGLISH','en');

define('_ACCEPT_MAIL_NOTIFICATIONS','SI');
define('_SITE_ACCESS','SITE');
define('_DESKTOP_DEVICE','DESKTOP');
define('_MOBILE_DEVICE','MOVIL');
define('_LOCK_TIME',30);

define('_DEMO_CONVEYOR_TRACKING_CODE','0rmYyn1kdzQMjq69xDrNUIabTTdGPJRDg');

$domain = array_key_exists('SERVER_NAME', $_SERVER) ? $_SERVER['SERVER_NAME'] : 'localhost';
define('_DOMAIN_COOKIE', $domain);
define('_COOKIE_TIME',time()+3600*2);

define('_API_PDF_WRITER', 'http://contiplus.net/Cron/create_public_pdf');
define('_PATH_PDF_TMP_REPORTS','uploads/htmlreports/');
define('_PATH_GENERIC_TMP_FILES','uploads/tmp/');

define('_AWS_S3', isset($_SERVER['S3_BUCKET']) ? $_SERVER['S3_BUCKET'] : 'https://contiplus-uploads-dev.s3.eu-central-1.amazonaws.com/');