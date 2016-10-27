<?php

// ---------------------------------
//
//    MySQL DataBase Object  v0.1
//
// ---------------------------------


class MySQL extends SQLDB {
    public function __construct(
        $_Name,  $_Account = 'root:',  $_CharSet = 'utf8',  $_Option = null
    ) {
        parent::__construct( $_Name );

        $_Name = array_merge(
            array('host' => 'localhost'),
            is_string($_Name)  ?  array('dbname' => $_Name)  :  $_Name,
            array('charset' => $_CharSet)
        );
        $_DSN = array();

        foreach ($_Name  as  $_Key => $_Value)
            $_DSN[] = "{$_Key}={$_Value}";

        $_Account = explode(':', $_Account);

        $this->dataBase = new PDO(
            'mysql:' . join(';', $_DSN),  $_Account[0],  $_Account[1],  $_Option
        );
    }

    public function hasTable($_Name) {
        return  !! count( $this->query(array(
            'select'  =>  'table_schema, table_name',
            'from'    =>  'information_schema.tables',
            'where'   =>
                "table_schema = '{$this->name}' and table_name = '{$_Name}'"
        )) );
    }
}
