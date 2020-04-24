<?php
/**
 * The Continental License 
 * Copyright 2014  Continental Automotive Systems, Inc. 
 * The copyright notice above does not evidence any actual 
 * or intended publication of such source code. 
 * The code contains Continental Confidential Proprietary Information.
 *
 * @file FireBaseComponent.php
 * @description
 *
 * @date 01, 2017
 * @project contiplus-web
 * @author I.E.I. Alberto Guerrero Duran (ieialbertogd@gmail.com)
 * Under Contiplus License
 */
App::uses('HttpSocket', 'Network/Http');
class FireBaseComponent extends Component {
    const API_URL = 'https://fcm.googleapis.com/fcm/send';
    const AUTH_APP_IOS_KEY = "key=AAAAVKPqWoM:APA91bF6-__tUR_3AXQUvCCYEemRk_F_8DiJK0ZlbCYXo8vGLPwrKXwegzCnUpK8Wqhx8dYpnLze5TSX5pEoMkTWFQ77JBNf1P_bBpCyfkmWxTPXJIC36zbcjRCl4y_QnX34pDJIgEVQ";
    const AUTH_APP_ANDROID_KEY = "key=AAAAVKPqWoM:APA91bF6-__tUR_3AXQUvCCYEemRk_F_8DiJK0ZlbCYXo8vGLPwrKXwegzCnUpK8Wqhx8dYpnLze5TSX5pEoMkTWFQ77JBNf1P_bBpCyfkmWxTPXJIC36zbcjRCl4y_QnX34pDJIgEVQ";

    const IOS_DEVICE = 'iOs';
    const ANDROID_DEVICE = 'Android';
    /**
     * @param $title string
     * @param $content string
     * @param $user_ids arrray
     * @return mixed json
     */
    public function push($title, $content, $user_ids){
        $this->UserDevice = ClassRegistry::init('UserDevice');

        $user_devices = $this->UserDevice->find('all', [
            'fields' => ["DISTINCT device_id", "device_type", "id"],
            'conditions' => ['user_id' => $user_ids],
            'order' => ['updated_at DESC']
        ]);

        $ios_device_ids = [];
        $android_device_ids = [];
        foreach($user_devices AS $user_device){
            $user_device = $user_device['UserDevice'];
            if($user_device['device_id']!=""){
                switch($user_device['device_type']){
                    case FireBaseComponent::IOS_DEVICE:
                        $ios_device_ids[] = $user_device['device_id'];
                        break;
                    case FireBaseComponent::ANDROID_DEVICE:
                        $android_device_ids[] = $user_device['device_id'];
                        break;
                    default:break;
                }
            }
        }

        $response_request = ['ios'=>[],'android'=>[]];
        //$ios_device_ids = []; //no send push

        //SEND TO IOS DEVICES
        if(!empty($ios_device_ids)){
            //$ios_device_ids = implode(',',$ios_device_ids);
            $HttpSocket = new HttpSocket();
            $data = [
                //"priority" => "10",//5 normal, 10 high
                "notification" => [
                    "title" => $title,
                    "body" => $content,
                    "sound" => 1,
                    "badge" => 1,//Red badge indicator in app
                ] ,
                //"to"=>$ios_device_ids //"dPJVnJgI970:APA91bE40Qxf5nEqicK2Rdrygm9tZEVVlanW1GfmH6qGNO8Yu2zc3IJjOGTFyfHd5CSCVjeC1JC79zjfaqX8KnhXMjoW170LIoENJwiUzA5yV4A50-pfo1GJWp-bc2Cv_88stLRA_MRs"
                "registration_ids" => $ios_device_ids
            ];
            $request = [
                'header' => [
                    'Content-Type' => 'application/json',
                    'Authorization'=> FireBaseComponent::AUTH_APP_IOS_KEY
                ]
            ];
            $data = json_encode($data);
            $response_request['ios'] =  json_decode($HttpSocket->post(FireBaseComponent::API_URL, $data, $request),true);
        }

        $android_device_ids = [];//no send push
        //SEND TO ANDROID DEVICES
        if(!empty($android_device_ids)){
            $android_device_ids = implode(',',$android_device_ids);
            $HttpSocket = new HttpSocket();
            $data = [
                "notification" => [
                    "title" => $title,
                    "body" => $content,
                    "sound" => 1
                ] ,
                "to"=>$android_device_ids //"dPJVnJgI970:APA91bE40Qxf5nEqicK2Rdrygm9tZEVVlanW1GfmH6qGNO8Yu2zc3IJjOGTFyfHd5CSCVjeC1JC79zjfaqX8KnhXMjoW170LIoENJwiUzA5yV4A50-pfo1GJWp-bc2Cv_88stLRA_MRs"
            ];
            $request = [
                'header' => [
                    'Content-Type' => 'application/json',
                    'Authorization'=> FireBaseComponent::AUTH_APP_ANDROID_KEY
                ]
            ];
            $data = json_encode($data);
            $response_request['android'] = json_decode($HttpSocket->post(FireBaseComponent::API_URL, $data, $request),true);
        }

        return $response_request;
    }

    /**
     * @param $title string
     * @param $content string
     * @param $user_ids arrray
     * @return mixed json
     */
    public function pushIos($title, $content, $user_ids){
        $this->UserDevice = ClassRegistry::init('UserDevice');

        $user_devices = $this->UserDevice->find('all', [
            'fields' => ["DISTINCT device_id", "device_type", "id"],
            'conditions' => ['user_id' => $user_ids],
            'order' => ['updated_at DESC']
        ]);

        $ios_device_ids = [];
        $android_device_ids = [];
        foreach($user_devices AS $user_device){
            $user_device = $user_device['UserDevice'];
            var_dump($user_device);
            if($user_device['device_id']!=""){
                switch($user_device['device_type']){
                    case FireBaseComponent::IOS_DEVICE:
                        $ios_device_ids[] = $user_device['device_id'];
                        break;
                    default:break;
                }
            }
        }

        $response_request = ['ios'=>[],'android'=>[]];
        //SEND TO IOS DEVICES
        if(!empty($ios_device_ids)){
            //$ios_device_ids = implode(',',$ios_device_ids);
            $HttpSocket = new HttpSocket();
            $data = [
                //"priority" => "10",//5 normal, 10 high
                "notification" => [
                    "title" => $title,
                    "body" => $content,
                    "sound" => 1,
                    "badge" => 1,//Red badge indicator in app
                ] ,
                //"to"=>$ios_device_ids //"dPJVnJgI970:APA91bE40Qxf5nEqicK2Rdrygm9tZEVVlanW1GfmH6qGNO8Yu2zc3IJjOGTFyfHd5CSCVjeC1JC79zjfaqX8KnhXMjoW170LIoENJwiUzA5yV4A50-pfo1GJWp-bc2Cv_88stLRA_MRs"
                "registration_ids" => $ios_device_ids
            ];
            $request = [
                'header' => [
                    'Content-Type' => 'application/json',
                    'Authorization'=> FireBaseComponent::AUTH_APP_IOS_KEY
                ]
            ];
            $data = json_encode($data);
            $response_request['ios'] =  json_decode($HttpSocket->post(FireBaseComponent::API_URL, $data, $request),true);
        }


        return $response_request;
    }

    public function pushAvailableUpdate($title, $content, $user_ids){
        $this->UserDevice = ClassRegistry::init('UserDevice');

        $user_devices = $this->UserDevice->find('all', [
            'fields' => ["DISTINCT device_id", "device_type", "id"],
            'conditions' => ['user_id' => $user_ids],
            'order' => ['updated_at DESC']
        ]);

        $ios_device_ids = [];
        $android_device_ids = [];
        foreach($user_devices AS $user_device){
            $user_device = $user_device['UserDevice'];
            if($user_device['device_id']!=""){
                switch($user_device['device_type']){
                    case FireBaseComponent::IOS_DEVICE:
                        $ios_device_ids[] = $user_device['device_id'];
                        break;
                    case FireBaseComponent::ANDROID_DEVICE:
                        $android_device_ids[] = $user_device['device_id'];
                        break;
                    default:break;
                }
            }
        }

        $response_request = ['ios'=>[],'android'=>[]];

        //SEND TO IOS DEVICES
        if(!empty($ios_device_ids)){
            //$ios_device_ids = implode(',',$ios_device_ids);
            $HttpSocket = new HttpSocket();
            $data = [
                //"to"=>"ejFGkR5-sFA:APA91bGfc81Hzg7Q_Tqmm6NnyM29j64P_bF7YhI5cr9kt_PRMPMewklWbfJtd_YAMZzVKtv376LtRk-USj-nmI_z0ZhcKCbJFsGTRleKy1EStLF3l5QINrVRdoeAxxo7jR45rABit7o8", //"dPJVnJgI970:APA91bE40Qxf5nEqicK2Rdrygm9tZEVVlanW1GfmH6qGNO8Yu2zc3IJjOGTFyfHd5CSCVjeC1JC79zjfaqX8KnhXMjoW170LIoENJwiUzA5yV4A50-pfo1GJWp-bc2Cv_88stLRA_MRs"
                "registration_ids" => $ios_device_ids,
                "content_available" => true,
                "mutable_content" => true,
                "data" => [
                    "message" => "Offer!",
                    "image_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/FloorGoban.JPG/1024px-FloorGoban.JPG"
                    ],
                "notification" => [
                    "title" => "Conti",
                    "sound" => "default",
                    "priority" => "high",
                    "tickerText" => 'Ticker text here...Ticker text here...Ticker text here',
                ]
            ];
            $request = [
                'header' => [
                    'Content-Type' => 'application/json',
                    'Authorization'=> FireBaseComponent::AUTH_APP_IOS_KEY
                ]
            ];
            $data = json_encode($data);
            $response_request['ios'] =  json_decode($HttpSocket->post(FireBaseComponent::API_URL, $data, $request),true);
        }

        /*
        //SEND TO ANDROID DEVICES
        if(!empty($android_device_ids)){
            $android_device_ids = implode(',',$android_device_ids);
            $HttpSocket = new HttpSocket();
            $data = [
                "notification" => [
                    "title" => $title,
                    "body" => $content,
                    "sound" => 1
                ] ,
                "to"=>$android_device_ids //"dPJVnJgI970:APA91bE40Qxf5nEqicK2Rdrygm9tZEVVlanW1GfmH6qGNO8Yu2zc3IJjOGTFyfHd5CSCVjeC1JC79zjfaqX8KnhXMjoW170LIoENJwiUzA5yV4A50-pfo1GJWp-bc2Cv_88stLRA_MRs"
            ];
            $request = [
                'header' => [
                    'Content-Type' => 'application/json',
                    'Authorization'=> FireBaseComponent::AUTH_APP_ANDROID_KEY
                ]
            ];
            $data = json_encode($data);
            $response_request['android'] = json_decode($HttpSocket->post(FireBaseComponent::API_URL, $data, $request),true);
        }*/

        return $response_request;
    }
}