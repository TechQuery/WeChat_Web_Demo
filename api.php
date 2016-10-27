<?php

error_reporting(E_ALL ^ E_NOTICE);

$_Config = require_once('php/config.php');


/* ---------- 微信 SDK ---------- */

require_once('php/WeChat_SDK.php');

$_JS_SDK = new WeChat_SDK(
    'qyapi',  $_Config['JS-SDK']['AppID'],  $_Config['JS-SDK']['AppSecret']
);

/* ---------- RESTful API 服务器 ---------- */

require_once('php/EasyLibs.php');


$_HTTP_Server = new HTTPServer(false,  function ($_Path) use ($_JS_SDK) {

    $_Path = join('/', $_Path);

    session_start();

    if ($_Path == 'WeChat/signPackage') {
        $_SESSION['UserId'] = $_JS_SDK->userInfo->UserId;
        session_write_close();
    }

    return  isset( $_SESSION['UserId'] );
});


/* ---------- 数据库对象 ---------- */

$_Connection = $_Config['DataBase']['connection'];

$_SQLDB = new MySQL(
    $_Config['DataBase']['base_name'],  "{$_Connection[1]}:{$_Connection[2]}"
);

/* ---------- RESTful API 路由 ---------- */

$_HTTP_Server->on('GET',  '/WeChat/',  function ($_Path) use ($_JS_SDK) {

    return  $_GET['callback'] . '(' . json_encode( $_JS_SDK->{$_Path[1]} ) . ');';
});