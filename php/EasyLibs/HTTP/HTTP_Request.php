<?php

// ------------------------------
//
//    HTTP Request Object  v0.2
//
// ------------------------------


class HTTP_Request {
    private static $More_Header = array(
        'REMOTE_ADDR', 'REQUEST_METHOD', 'CONTENT_TYPE', 'CONTENT_LENGTH'
    );
    private static function getHeaders() {
        $_Header = array();  $_Take = false;

        foreach ($_SERVER  as  $_Key => $_Value) {
            if (substr($_Key, 0, 5) == 'HTTP_') {
                $_Key = substr($_Key, 5);
                $_Take = true;
            }
            if ($_Take  ||  in_array($_Key, self::$More_Header)) {
                $_Header[str_replace(' ', '-', ucwords(
                    strtolower( str_replace('_', ' ', $_Key) )
                ))] = $_Value;
                $_Take = false;
            }
        }
        return $_Header;
    }

    private static $IPA_Header = array(
        'Client-Ip',  'X-Forwarded-For',  'Remote-Addr'
    );
    private static function getIPA($_Header) {
        foreach (self::$IPA_Header as $_Key) {
            if (empty( $_Header[$_Key] ))  continue;

            $_IPA = explode(',', $_Header[$_Key]);
            return  trim( $_IPA[0] );
        }
    }
    private static function getData($_Method) {
        if ($_Method == 'GET')  return $_GET;
        if ($_Method == 'POST')  return $_POST;

        parse_str(file_get_contents('php://input'), $_Args);

        if ($_Method == 'DELETE') {
            global $_DELETE;
            $_DELETE = $_Args;
        } elseif ($_Method == 'PUT') {
            global $_PUT;
            $_PUT = $_Args;
        }
        return $_Args;
    }

    public $header;
    public $method;
    public $IPAddress;
    public $cookie;
    public $data;

    public function __construct() {
        $_Header = $this->header = self::getHeaders();

        $this->method = $this->header['Request-Method'];

        $this->IPAddress = self::getIPA( $this->header );

        if (isset( $_Header['Cookie'] ))
            $this->cookie = new HTTP_Cookie( $_Header['Cookie'] );

        $this->data = self::getData( $this->method );
    }
}
