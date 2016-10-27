<?php

// --------------------------------------
//
//    SQLite DataBase Object  v0.1
//
// --------------------------------------


class SQLite extends SQLDB {
    public function __construct($_Name) {
        parent::__construct( $_Name );

        new FS_Directory( pathinfo($_Name, PATHINFO_DIRNAME) );

        if (PHP_OS != 'WINNT')  $_Name = __DIR__ . '/' . $_Name;

        $this->dataBase = new PDO("sqlite:{$_Name}.db");
    }

    public function hasTable($_Name) {
        return  !! count( $this->query(array(
            'select'  =>  'name, sql',
            'from'    =>  'SQLite_Master',
            'where'   =>  "type = 'table' and name = '{$_Name}'"
        )) );
    }
}
