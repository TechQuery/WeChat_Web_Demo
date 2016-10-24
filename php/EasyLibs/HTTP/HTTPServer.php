<?php

// ------------------------------
//
//    Simple HTTP Server  v0.3
//
// ------------------------------


class HTTPServer {
    public $request;
    private $onStart;

    public function setStatus($_Code) {
        $_Message = isset( HTTP_Response::$statusCode[$_Code] )  ?
            HTTP_Response::$statusCode[$_Code]  :  '';

        header("HTTP/1.1 {$_Code} {$_Message}");

        if ($_Code == 429)
            header('Retry-After: '.func_get_arg(1));
    }

    public function setHeader($_Head,  $_Value = null) {
        if (! is_array($_Head))
            $_Head = array("{$_Head}" => $_Value);

        if (! isset( $_Head['X-Powered-By'] ))
            $_Head['X-Powered-By'] = '';

        $_XPB = $_Head['X-Powered-By'];

        if (stripos($_XPB, 'EasyLibs')  ===  false) {
            $_XPB .= '; EasyLibs.php/1.6';
            $_Head['X-Powered-By'] = trim(preg_replace('/;\s*;/', ';', $_XPB),  ';');
        }

        if (isset( $_Head['WWW-Authenticate'] ))
            $this->setStatus(401);
        else {
            $this->setStatus(
                isset( $_Head['Response-Code'] )  ?  $_Head['Response-Code']  :  200
            );
            unset( $_Head['Response-Code'] );
        }
        foreach ($_Head  as  $_Key => $_String)
            header("{$_Key}: {$_String}");

        return $this;
    }

    public function __construct($_xDomain = false,  $_onStart = null) {
        $this->request = new HTTP_Request();
        $this->onStart = $_onStart;

        $_Header = $this->request->header;

        if ((! $_xDomain)  ||  ($this->request->method != 'OPTIONS'))
            return;

        $_AC = 'Access-Control';
        $_ACA = "{$_AC}-Allow";    $_ACR = "{$_AC}-Request";

        $this->setHeader(array(
            'Response-Code'          =>  204,
            "{$_ACA}-Origin"         =>
                isset( $_Header['Origin'] )  ?  $_Header['Origin']  :  '*',
            "{$_ACA}-Methods"        =>
                isset( $_Header["{$_ACR}-Methods"] )  ?
                    $_Header["{$_ACR}-Methods"]  :  'GET, POST, PUT, DELETE',
            "{$_ACA}-Headers"        =>
                isset( $_Header["{$_ACR}-Headers"] )  ?
                    $_Header["{$_ACR}-Headers"]  :  'X-Requested-With',
            "{$_ACA}-Credentials"    =>  true,
            "{$_AC}-Expose-Headers"  =>  'X-Powered-By',
            "{$_AC}-Max-Age"         =>  300
        ));
        exit;
    }

    public function setCookie($_Key,  $_Value = null) {
        if (! is_array($_Key))
            $_Key = array("{$_Key}" => $_Value);

        $this->setHeader('Set-Cookie',  new HTTP_Cookie($_Key));
    }
    //  ToDo --- http://php.net/manual/zh/features.http-auth.php
    public function auth($_Tips = 'Auth Needed',  $_Check_Handle) {
        if (isset( $_SERVER['PHP_AUTH_USER'] )  &&  isset( $_SERVER['PHP_AUTH_PW'] )) {
            if (false !== call_user_func(
                $_Check_Handle,  $_SERVER['PHP_AUTH_USER'],  $_SERVER['PHP_AUTH_PW'],  $this
            ))
                return;
            else
                $this->setStatus(403);
        } else
            $this->setHeader('WWW-Authenticate', "Basic realm=\"{$_Tips}\"");

        exit;
    }
    public function download($_File) {
        $this->setHeader(array(
            'Content-Type'               =>  'application/octet-stream',
            'Content-Disposition'        =>  'attachment; filename="'.basename($_File).'"',
            'Content-Transfer-Encoding'  =>  'binary'
        ));
        readfile($_File);
        exit;
    }
    public function send($_Data,  $_Header = null) {
        if ($_Data instanceof HTTP_Response) {
            $_Header = $_Data->header;
            $_Data = $_Data->data;
        }
        if ($_Header)  $this->setHeader($_Header);
        echo  is_string($_Data) ? $_Data : json_encode($_Data);
        ob_flush() & flush();
    }
    public function redirect($_URL,  $_Second = 0,  $_Tips = '') {
        if ($_Second)
            $this->send($_Tips, array(
                'Refresh'  =>  "{$_Second}; url={$_URL}"
            ));
        else
            $this->setHeader('Location', $_URL);

        exit;
    }

    public function on($_Method, $_Path, $_Callback) {
        $_rPath = $_SERVER['PATH_INFO'];
        if (
            ($this->request->method == strtoupper($_Method))  &&
            (stripos($_rPath, $_Path)  !==  false)
        ) {
            $_rPath = explode('/',  trim($_rPath, '/'));

            $_Return = isset( $this->onStart )  ?
                call_user_func($this->onStart, $_rPath, $this->request)  :  null;

            if ($_Return !== false)
                $_Return = call_user_func_array($_Callback, array(
                    $_rPath,  $this->request,  $_Return
                ));
            if (is_array( $_Return ))
                $this->send($_Return['data'], $_Return['header']);
            else
                $this->send(
                     is_string($_Return) ? $_Return : json_encode($_Return)
                );
        }
        return $this;
    }
}
