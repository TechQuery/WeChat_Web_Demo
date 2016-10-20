<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'EasyLibs.php');



class WeChat_JS_SDK extends EasyAccess {
    private $apiType;
    private $apiRoot;
    private $appID;
    private $appSecret;

    public function __construct($_Domain, $appID, $appSecret) {
        $this->apiType = $_Domain;
        $this->apiRoot = 'https://' . $_Domain . '.weixin.qq.com/cgi-bin/';

        $this->appID = $appID;
        $this->appSecret = $appSecret;
    }

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

    private function apiCall($_API) {
        $curl = curl_init($this->apiRoot . $_API);

        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER  =>  true,
            CURLOPT_SSL_VERIFYHOST  =>  true,
            CURLOPT_TIMEOUT         =>  500,
            CURLOPT_RETURNTRANSFER  =>  true
        ));
        $res = curl_exec($curl);
        curl_close($curl);

        return json_decode($res);
    }

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
                "ticket/getticket?type=jsapi&access_token={$this->accessToken}"  :
                "get_jsapi_ticket?access_token={$this->accessToken}"
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
            "appId"     => $this->appID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
    }

    protected function getUserInfo() {
        return $this->apiCall(
            "user/getuserinfo?access_token={$this->accessToken}&code={$_GET['code']}"
        );
    }

    public function getMedia($serverId, $localFile) {
        $cURL = curl_init(
            $this->apiRoot .
            "media/get?access_token={$this->accessToken}&media_id={$serverId}"
        );
        $localFile = fopen($localFile, 'wb');
        curl_setopt($cURL, CURLOPT_FILE, $localFile);

        curl_exec($cURL);
        curl_close($cURL);

        return fclose($localFile);
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

        exit( $_Code );
    }
}