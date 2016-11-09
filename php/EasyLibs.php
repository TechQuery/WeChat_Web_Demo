<?php
//
//                >>>  EasyLibs.php  <<<
//
//
//      [Version]    v2.8  (2016-11-09)  Stable
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

    if ($_Path)
        require_once(join(DIRECTORY_SEPARATOR, array(
            __DIR__, 'EasyLibs', $_Path, "$_ClassName.php"
        )));

},  true,  true);



// ----------------------------------------
//
//    Data Model Abstract Class  v0.3
//
// ----------------------------------------


abstract class DataModel extends EasyAccess {
    protected $dataBase;
    protected $dataTable;
    protected $requestData;

    public function __construct($_SQLDB, $_Request_Data) {
        $this->dataBase = $_SQLDB;
        $this->dataTable = $_SQLDB->{$this->name};

        $this->requestData = $_Request_Data;
    }

    protected function checkError() {
        $_Error = $this->dataBase->errorInfo();

        if ($_Error[0] != '00000')  throw  new Exception($_Error[2], $_Error[0]);
    }

    abstract protected function getFields();

    abstract protected function getOrderBy();

    abstract protected function getLimit();

    public function search(array $_Condition = array()) {

        $_Args = array_filter( $this->fields );

        $_Condition = join(' and ',  array_merge($_Condition, array_map(
            function ($_Value, $_Key) {
                return  "$_Key like '%$_Value%'";
            },
            array_values( $_Args ),
            array_keys( $_Args )
        )));

        $_Statement = array(
            'select'  =>  'count(*)',
            'from'    =>  $this->name
        );

        if ( $_Condition )  $_Statement['where'] = $_Condition;

        $_Result = $this->dataBase->query( $_Statement );

        $this->checkError();

        $_Result = array('total'  =>  intval( $_Result[0]['count(*)'] ));

        $_Statement['select'] = '*';
        $_Statement['order by'] = $this->orderBy;
        $_Statement['limit'] = $this->limit;

        $_Result['rows'] = $this->dataBase->query( $_Statement );

        $this->checkError();

        return $_Result;
    }

    public function getItemBy($_Name) {
        $_Item = $this->dataBase->query(array(
            'select'  =>  '*',
            'from'    =>  $this->name,
            'where'   =>  "$_Name = '{$this->fields[$_Name]}'"
        ));

        $this->checkError();

        return $_Item[0];
    }

    public function write($_ID_Name = '') {
        if ($_ID_Name  &&  $this->getItemBy( $_ID_Name ))
            $this->dataTable->update(
                "$_ID_Name = '{$this->fields[$_ID_Name]}'",  $this->fields
            );
        else
            $this->dataTable->insert( $this->fields );

        $this->checkError();

        return $this->dataBase->lastInsertId();
    }

    public function remove($_ID_Name = '',  array $_Condition = array()) {
        if (! $_ID_Name)  return;

        $_Result = $this->dataTable->delete(
            join(' and ',  array_merge($_Condition, array(
                "$_ID_Name = '{$this->fields[$_ID_Name]}'"
            )))
        );
        $this->checkError();

        return $_Result;
    }
}

spl_autoload_register(function ($_ClassName) {

    $_Path = join(DIRECTORY_SEPARATOR, array(
        __DIR__,  'DataModel',  "$_ClassName.php"
    ));

    if (file_exists( $_Path ))  require_once( $_Path );

},  true,  true);