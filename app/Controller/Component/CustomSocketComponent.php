<?php

/* 
 * The Continental License
 * Copyright 2015  Continental Automotive Systems, Inc.
 * The copyright notice above does not evidence any actual
 * or intended publication of such source code.
 * The code contains Continental Confidential Proprietary Information.
 *
 *     @file CustomSocketComponent.php
 *     CustomSocketComponent
 *
 *     @project    Contiplus
 *     @author     toc-toc@cocothink.com,ieialbertogd@gmail.com
 *     @date      2015
 */

class CustomSocketComponent extends Component {
    public function send($url, $data, $method = 'POST'){
        
        $data = http_build_query($data);
        $context_options = array (
        'http' => array (
            'method' => $method,
            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data
            )
        );
        
        //$context = context_create_stream($context_options);
        $context = stream_context_create($context_options);
        
        $fp = fopen($url, 'r', false, $context);    
        return stream_get_contents($fp);
    }
}