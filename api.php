<?php

error_reporting(E_ALL ^ E_NOTICE);

require_once('php/WeChat_JS_SDK.php');
$_Config = require_once('php/config.php');

$_JS_SDK = new WeChat_JS_SDK(
    'qyapi',  $_Config['JS-SDK']['AppID'],  $_Config['JS-SDK']['AppSecret']
);

$_Item = explode('?', $_SERVER['PATH_INFO'], 1);
$_Item = substr($_Item[0], 1);

echo  $_GET['callback'] . '(' . json_encode( $_JS_SDK->$_Item ) . ');';