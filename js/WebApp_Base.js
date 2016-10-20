define(['jquery', 'jQuery+', 'WC_API'],  function ($) {

/* ----- 网络 I/O 控制 ----- */

    $.ajaxSetup({
        dataFilter:    function (iData) {
            if ($.fileName( this.url ).indexOf('.')  >  -1)
                return iData;

            iData = JSON.parse( iData );

            if ( iData.code )  return  self.alert( iData.message );

            if (this.type.toUpperCase() != 'GET')  self.alert( iData.message );

            iData = iData.data || { };

            return  JSON.stringify(iData.list || iData);
        }
    });

    $(document).on('ajaxStart',  function () {

        $( this.body ).removeClass('Loaded');

    }).on('ajaxStop',  function () {

        $( this.body ).addClass('Loaded');

/* ----- 表单数据备份 ----- */

    }).on('change',  '[name]:input',  function () {

        self.localStorage[ this.getAttribute('name') ] = this.value;
    });
});