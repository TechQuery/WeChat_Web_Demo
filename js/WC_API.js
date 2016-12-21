define([
    'jquery', 'WeChat', 'WC_Sign', 'jQuery+'
],  function ($, WeChat, WC_Sign) {

/* ---------- API 签名、初始化 ---------- */

    WeChat.config($.extend(WC_Sign, {
        jsApiList:    [
            'checkJsApi',

            'onMenuShareTimeline', 'onMenuShareAppMessage',
            'onMenuShareQQ', 'onMenuShareQZone',
            'onMenuShareWeibo',

            'hideMenuItems', 'showMenuItems',
            'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',

            'startRecord', 'stopRecord', 'onVoiceRecordEnd',

            'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd',
            'uploadVoice', 'downloadVoice', 'translateVoice',

            'chooseImage', 'previewImage', 'uploadImage', 'downloadImage',

            'getNetworkType', 'openLocation', 'getLocation',

            'hideOptionMenu', 'showOptionMenu', 'closeWindow',

            'scanQRCode', 'chooseWXPay', 'openProductSpecificView',

            'addCard', 'chooseCard', 'openCard'
        ]
    }));

/* ---------- API 兼容 Promise/A+ ---------- */

    $.each([
        'chooseImage', 'uploadImage', 'scanQRCode', 'getLocation'
    ],  function () {
        var _Old_ = WeChat[this];

        WeChat[this] = function (iOption) {

            return  new Promise(function () {

                _Old_.call(WeChat, $.extend({
                    success:    arguments[0],
                    fail:       arguments[1]
                }, iOption));
            });
        };
    });

    return WeChat;
});