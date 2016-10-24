<?php

// ------------------------------
//
//    HTTP Cookie Object  v0.1
//
// ------------------------------


class HTTP_Cookie {
    private $data = array();

    public function __construct($_Cookie) {
        if (is_array( $_Cookie ))
            return  $this->data = $_Cookie;

        if (function_exists('http_parse_cookie'))
            return  $this->data = http_parse_cookie($_Cookie);

        $_Cookie = explode(';', $_Cookie, 2);

        foreach ($_Cookie as $_Item) {
            $_Item = explode('=', $_Item, 2);
            $this->data[trim( $_Item[0] )] = trim( $_Item[1] );
        }
    }
    public function __toString() {
        if (function_exists('http_build_cookie'))
            return  http_build_cookie($this->data);

        $_Cookie = array();

        foreach ($this->data  as  $_Key => $_Value)
            $_Cookie[] = "{$_Key}={$_Value}";

        return  join('; ', $_Cookie);
    }

    public function get($_Name) {
        if (isset( $this->data[$_Name] ))  return $this->data[$_Name];
    }
    public function set($_Name, $_Value) {
        $this->data[$_Name] = $_Value;
    }
}
