<?php

// --------------------------------------
//
//    SQL DataBase Abstract Class  v0.2
//
// --------------------------------------


abstract class SQLDB {

    /* ----- SQL Statement Generation ----- */

    protected static $statement = array(
        'select'  =>  array(
            'select',  'from',  'where',  'order by',  'limit',  'offset'
        )
    );

    protected static function queryString($_SQL_Array) {
        $_SQL = array();

        foreach (self::$statement  as  $_Name => $_Key)
            if (isset( $_SQL_Array[$_Name] ))
                for ($i = 0;  $i < count($_Key);  $i++)
                    if (isset( $_SQL_Array[ $_Key[$i] ] )) {
                        $_SQL[] = $_Key[$i];
                        $_SQL[] = $_SQL_Array[ $_Key[$i] ];
                    }
        return  join(' ', $_SQL);
    }

    /* ----- Data Base Operation ----- */

    protected $dataBase;
    protected $table = array();
    public    $name;

    public function query(
        $_SQL_Array,  $_Fetch_Type = PDO::FETCH_OBJ,  $_Fetch_Args = null
    ) {
        $_Query = $this->dataBase->query( self::queryString( $_SQL_Array ) );

        if (! $_Query)
            $_Query = array();
        elseif (! $_Fetch_Args)
            $_Query = array_map(
                function ($_Object) {
                    return  get_object_vars( $_Object );
                },
                $_Query->fetchAll($_Fetch_Type)
            );
        else
            $_Query = $_Query->fetchAll($_Fetch_Type, $_Fetch_Args);

        return $_Query;
    }

    /* ----- Data Table Operation ----- */

    abstract public function hasTable($_Name);

    protected function addTable($_Name) {
        return  $this->table[$_Name] = new SQL_Table($this->dataBase, $_Name);
    }

    public function __get($_Name) {
        if ($_Name == 'error')
            return array(
                'code'  =>  $this->dataBase->errorCode(),
                'info'  =>  $this->dataBase->errorInfo(),
            );
        if (isset( $this->table[$_Name] ))
            return $this->table[$_Name];

        if ($this->hasTable( $_Name ))
            return $this->addTable($_Name);
    }

    public function createTable($_Name, $_Column_Define) {
        $_Define_Array = array();

        foreach ($_Column_Define  as  $_Key => $_Define)
            $_Define_Array[] = "{$_Key} {$_Define}";

        $_Result = $this->dataBase->exec(
            "create Table if not exist {$_Name} (\n    "  .
            join(",\n    ", $_Define_Array)  .
            "\n)"
        );
        return  is_numeric($_Result) ? (!! $this->addTable($_Name)) : false;
    }

    public function dropTable($_Name) {
        if (is_numeric(
            $this->dataBase->exec("drop Table if exist {$_Name}")
        )) {
            unset( $this->table[$_Name] );
            return true;
        }
    }
}
