<?php
//
//                >>>  EasyLibs.php  <<<
//
//
//      [Version]    v2.5  (2016-10-26)  Stable
//
//      [Require]    PHP v5.3.6+
//
//      [Usage]      A Light-weight PHP Class Library
//                   without PHP Extensions.
//
//
//          (C)2015-2016    shiy2008@gmail.com
//


// ---------------------------------------------
//
//    Object Attribute Access Controller  v0.1
//
// ---------------------------------------------


abstract class EasyAccess {
    protected $data = array();

    private static function getName($_Key,  $_Mode = 0) {
        return  ($_Mode ? 'set' : 'get') .
            strtoupper( $_Key[0] ) . substr($_Key, 1);
    }

    public function __get($_Key) {
        return  isset( $this->data[$_Key] )  ?  $this->data[$_Key]  :  (
            $this->data[$_Key] = $this->{ self::getName($_Key) }()
        );
    }
    public function __set($_Key, $_Value) {
        $_Name = self::getName($_Key, 1);

        if (method_exists($this, $_Name))
            $this->$_Name( $this->data[$_Key] = $_Value );
    }
}

// ------------------------------
//
//    Class Auto Loader  v0.1
//
// ------------------------------


spl_autoload_register(function ($_ClassName) {

    $_Path = array_reduce(
        array(
            '/^(FS)_/', '/(SQL)/', '/^(HTTP)/', '/^(HTML)/'
        ),
        function  ($_Prev, $_Item) use ($_ClassName) {
            return  ((! $_Prev)  &&  preg_match($_Item, $_ClassName, $_Path))  ?
                $_Path[1]  :  $_Prev;
        }
    );

    require_once(join(DIRECTORY_SEPARATOR, array(
        __DIR__, 'EasyLibs', $_Path, "$_ClassName.php"
    )));

},  true,  true);
