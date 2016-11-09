<?php


// ------------------------------
//
//    Simple HTTP Client  v0.2
//
// ------------------------------


class HTTPClient {
    private static function setRequestHeaders($_Header_Array) {
        $_Header = array();

        foreach ($_Header_Array  as  $_Key => $_Value)
            $_Header[] = "$_Key: $_Value";

        if (
            empty( $_Header_Array['Content-Type'] )  &&
            isset( $_Header_Array['Request-Method'] )  &&
            (strtoupper( $_Header_Array['Request-Method'] )  ==  'POST')
        )
            $_Header[] = 'Content-Type: application/x-www-form-urlencoded';

        return  join("\r\n", $_Header);
    }

    private function request($_Method,  $_URL,  $_Header,  $_Data = array()) {
        $_Response = @file_get_contents($_URL, false, stream_context_create(array(
            'http' => array(
                'method'   =>  $_Method,
                'header'   =>  self::setRequestHeaders($_Header),
                'content'  =>  http_build_query($_Data)
            )
        )));
        return  ($_Response !== false)  ?
            new HTTP_Response($http_response_header, $_Response)  :  $_Response;
    }

    public function head($_URL) {
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        return  get_headers($_URL, 1);
    }
    public function get($_URL,  $_Header = array()) {
        return  $this->request('GET', $_URL, $_Header);
    }
    //  ToDo --- http://php.net/manual/zh/features.file-upload.post-method.php
    public function post($_URL,  $_Data,  $_Header = array()) {
        return  $this->request('POST', $_URL, $_Data, $_Header);
    }
    public function delete($_URL,  $_Data,  $_Header = array()) {
        return  $this->request('DELETE', $_URL, $_Data, $_Header);
    }
    public function put($_URL,  $_Data,  $_Header = array()) {
        return  $this->request('PUT', $_URL, $_Data, $_Header);
    }
}
