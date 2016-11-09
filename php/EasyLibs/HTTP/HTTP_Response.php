<?php

// -----------------------------------
//
//    HTTP Response Object  v0.1
//
// -----------------------------------


class HTTP_Response {
    public static $statusCode = array(
        '100'  =>  'Continue',
        '101'  =>  'Switching Protocols',
        '200'  =>  'OK',
        '201'  =>  'Created',
        '202'  =>  'Accepted',
        '203'  =>  'Non-authoritative Information',
        '204'  =>  'No Content',
        '205'  =>  'Reset Content',
        '206'  =>  'Partial Content',
        '300'  =>  'Multiple Choices',
        '301'  =>  'Moved Permanently',
        '302'  =>  'Found',
        '303'  =>  'See Other',
        '304'  =>  'Not Modified',
        '305'  =>  'Use Proxy',
        '306'  =>  'Unused',
        '307'  =>  'Temporary Redirect',
        '400'  =>  'Bad Request',
        '401'  =>  'Unauthorized',
        '402'  =>  'Payment Required',
        '403'  =>  'Forbidden',
        '404'  =>  'Not Found',
        '405'  =>  'Method Not Allowed',
        '406'  =>  'Not Acceptable',
        '407'  =>  'Proxy Authentication Required',
        '408'  =>  'Request Timeout',
        '409'  =>  'Conflict',
        '410'  =>  'Gone',
        '411'  =>  'Length Required',
        '412'  =>  'Precondition Failed',
        '413'  =>  'Request Entity Too Large',
        '414'  =>  'Request-url Too Long',
        '415'  =>  'Unsupported Media Type',
        '416'  =>  '',
        '417'  =>  'Expectation Failed',
        '428'  =>  'Precondition Required',
        '429'  =>  'Too Many Requests',
        '431'  =>  'Request Header Fields Too Large',
        '500'  =>  'Internal Server Error',
        '501'  =>  'Not Implemented',
        '502'  =>  'Bad Gateway',
        '503'  =>  'Service Unavailable',
        '504'  =>  'Gateway Timeout',
        '505'  =>  'HTTP Version Not Supported',
        '511'  =>  'Network Authentication Required'
    );

    private static function getFriendlyHeaders($_Header_Array) {
        $_Header = array();

        foreach ($_Header_Array as $_Str) {
            $_Item = explode(':', $_Str, 2);

            if (isset( $_Item[1] )) {
                $_Header[trim( $_Item[0] )] = trim( $_Item[1] );
                continue;
            }
            if ( preg_match('#HTTP/[\d\.]+\s+(\d+)#', $_Str, $_Num) )
                $_Header['Response-Code'] = intval( $_Num[1] );
            else
                $_Header[] = $_Str;
        }
        return $_Header;
    }

    public  $header;
    private $rawString;
    private $data;

    public function __construct($_Header = null,  $_Data = '{}') {
        $this->header = isset($_Header[0]) ? self::getFriendlyHeaders($_Header) : $_Header;

        if (is_string( $_Data )) {
            $this->rawString = $_Data;
            $this->data = json_decode($_Data, true);
        } else {
            $this->rawString = json_encode($_Data);
            $this->data = $_Data;
        }
    }

    public function __get($_Key) {
        return $this->{$_Key};
    }

    public function __set($_Key, $_Value) {
        switch ($_Key) {
            case 'rawString':        {
                $this->rawString = $_Value;
                $this->data = json_decode($_Value, true);
                break;
            }
            case 'data':    {
                $this->rawString = json_encode($_Value);
                $this->data = $_Value;
            }
        }
    }
}
