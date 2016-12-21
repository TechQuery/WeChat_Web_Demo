<?php
//
//                >>>  WeChat_SDK.php  <<<
//
//
//      [Version]    v0.6  (2016-11-12)  Stable
//
//      [Require]    EasyLibs.php  v2.5+
//
//      [Usage]      A Light-weight PHP SDK modified
//                   from WeChat offical example.
//
//
//            (C)2016    shiy2008@gmail.com
//


require_once(__DIR__ . DIRECTORY_SEPARATOR . 'EasyLibs.php');



class WeChat_SDK extends EasyAccess {
    private $httpClient;
    private $apiType;
    private $apiRoot;
    private $appID;
    private $appSecret;

    public function __construct($_Domain, $appID, $appSecret) {
        $this->httpClient = new HTTPClient();

        $this->apiType = $_Domain;
        $this->apiRoot = 'https://' . $_Domain . '.weixin.qq.com/cgi-bin/';

        $this->appID = $appID;
        $this->appSecret = $appSecret;
    }

/* ----- 底层方法 ----- */

    private static function createNonceStr($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';

        for ($i = 0;  $i < $length;  $i++)
            $str .= substr($chars,  mt_rand(0, strlen($chars) - 1),  1);

        return $str;
    }

    private function getConfig($filename) {
        return json_decode(trim(substr(
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $filename . '.php'),
            16
        )));
    }

    private function setConfig($filename, $content) {
        return file_put_contents(
            __DIR__ . DIRECTORY_SEPARATOR . $filename . '.php',
            '<?php exit(); ?>' . json_encode($content)
        );
    }

    private function apiCall($_API,  $_Method = 'get',  array $_Data = array()) {

        $_Token = preg_match('/^\w+token\?/', $_API)  ?
            ''  :  "&access_token={$this->accessToken}";

        $_Response = $this->httpClient->{$_Method}(
            "{$this->apiRoot}{$_API}{$_Token}",  $_Data
        )->rawString;

        return  preg_match('/^media\//', $_API)  ?
            $_Response  :  json_decode( $_Response );
    }

/* ----- 信息获取 ----- */

    protected function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = $this->getConfig('access_token');

        if ($data->expire_time > time())  return $data->access_token;

        $res = $this->apiCall(
            ($this->apiType == 'api')  ?
                "token?grant_type=client_credential&appid={$this->appID}&secret={$this->appSecret}"  :
                "gettoken?corpid={$this->appID}&corpsecret={$this->appSecret}"
        );
        if ( $res->access_token ) {
            $data->expire_time = time() + 7000;
            $data->access_token = $res->access_token;
            $this->setConfig('access_token', $data);
        }

        return $res->access_token;
    }

    protected function getJSAPITicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = $this->getConfig('jsapi_ticket');

        if ($data->expire_time > time())  return $data->jsapi_ticket;

        $res = $this->apiCall(
            ($this->apiType == 'api')  ?
                "ticket/getticket?type=jsapi"  :  "get_jsapi_ticket?"
        );
        if ( $res->ticket ) {
            $data->expire_time = time() + 7000;
            $data->jsapi_ticket = $res->ticket;
            $this->setConfig('jsapi_ticket', $data);
        }

        return $res->ticket;
    }

    protected function getSignPackage() {
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (
            (isset( $_SERVER['HTTPS'] )  &&  ($_SERVER['HTTPS'] !== 'off'))  || ($_SERVER['SERVER_PORT'] == 443)
        ) ? 'https' : 'http';

        $url = (strpos($_SERVER['PHP_SELF'], 'index.php') === false)  ?
            $_SERVER['HTTP_REFERER']  :
            "$protocol://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

        $timestamp = time();
        $nonceStr = self::createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = join('&', array(
            "jsapi_ticket={$this->JSAPITicket}",
            "noncestr={$nonceStr}",
            "timestamp={$timestamp}",
            "url={$url}"
        ));

        $signature = sha1($string);

        return array(
            "appId"      =>  $this->appID,
            "nonceStr"   =>  $nonceStr,
            "timestamp"  =>  $timestamp,
            "signature"  =>  $signature,
        );
    }

    protected function getLoginInfo() {
        return  $this->apiCall("service/get_login_info?", 'post', array(
            'auth_code'  =>  $_GET['auth_code']
        ));
    }

    protected function getUserInfo() {
        return $this->apiCall("user/getuserinfo?code={$_GET['code']}");
    }

/* ----- 公用方法 ----- */

    public function getUser($UID) {
        return $this->apiCall("user/get?userid={$UID}");
    }

    public function getMedia($serverId,  $_File_Path = '') {
        $_File = $this->apiCall("media/get?media_id={$serverId}");

        return  ($_File_Path && $_File)  ?
            file_put_contents($_File_Path, $_File)  :  $_File;
    }

    public function response($_Message = 'Success',  $_Code = 0) {
        if (preg_match('/json|javascript/', $_SERVER['HTTP_ACCEPT']))
            echo json_encode(array(
                'code'     =>  $_Code,
                'message'  =>  $_Message
            ));
        else {
            $_RL_URL = explode('?', $_SERVER['HTTP_REFERER']);

            header(
                'Refresh: 3;url=' .
                'https://open.weixin.qq.com/connect/oauth2/authorize?' .
                "appid={$this->appID}&" .
                "redirect_uri={$_RL_URL[0]}&" .
                'response_type=code&scope=SCOPE&state=STATE#wechat_redirect'
            );
            header('Content-Type: text/html;charset=UTF-8');

            echo "<h1 align=\"center\">{$_Message}（ 3 秒后返回）</h1>";
        }

        exit((int) $_Code);
    }
}
