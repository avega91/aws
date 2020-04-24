<?php
/*
 * The Continental License
 * Copyright 2014  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file SecurityComponent.php
 *     Component to manage system security
 *
 *     @project    Contiplus
 *     @author     jose.avalos@dextratech.com
 *     @date       test 2019
 */

App::import("vendors", "autoload", array("file" => "autoload.php'"));
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

 class AwsComponent extends Component {

 	private $_options = [
                'region'  => 'eu-central-1',
                'version' => 'latest', //'2006-03-01'
                'credentials' => [
                    'key'    => "AKIA53GW6HFKCHBAHNHH",
                    'secret' => "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",
                ]
            ];

 	private $bucket = 'contiplus-uploads-dev';

    /**
     * Class constructor
     */
    public function __construct(ComponentCollection $collection, array $settings = array()) {
       	parent::__construct($collection, $settings);
    }



    public function putObjectOnS3($key, $filePathToUpload) {
    	$s3 = new S3Client($this->_options);
    	$result = $s3->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
            'Body'   => fopen($filePathToUpload, 'r+'),
            'ACL'    => 'public-read'
        ]);
    }
 
}