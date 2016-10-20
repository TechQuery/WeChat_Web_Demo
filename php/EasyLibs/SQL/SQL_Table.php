<?php

// --------------------------------------
//
//    SQL DataBase Table Object  v0.3
//
// --------------------------------------


class SQL_Table {
    private $ownerBase;
    private $name;

    public function __construct($_DataBase, $_Name) {
        $this->ownerBase = $_DataBase;
        $this->name = $_Name;
    }

    public function rename($_Name) {
        return  $this->ownerBase->exec(
            "alter table {$this->name} rename to {$_Name}"
        );
    }
    public function addColumn($_Name,  $_Define = null) {
        if (is_string( $_Name ))
            $_Name = array("$_Name" => $_Define);

        foreach ($_Name  as  $_Key => $_Value)
            $this->ownerBase->exec(
                "alter table {$this->name} add column {$_Key} {$_Value}"
            );
    }
    public function insert($_Record) {
        $_Field_Name = array();  $_Field_Value = array();

        foreach ($_Record  as  $_Name => $_Value)
            if ($_Value !== null) {
                $_Field_Name[] = $_Name;
                $_Field_Value[] = is_string( $_Value )  ?
                    $this->ownerBase->quote($_Value)  :  $_Value;
            }
        return  $this->ownerBase->exec(join('', array(
            "insert into {$this->name} (",
            join(', ', $_Field_Name),
            ') values (',
            join(', ', $_Field_Value),
            ')'
        )));
    }
    public function update($_Where, $_Data) {
        $_Set_Data = array();

        foreach ($_Data  as  $_Name => $_Value)
            $_Set_Data[] = "{$_Name}=".(
                is_string( $_Value )  ?
                    $this->ownerBase->quote($_Value)  :  $_Value
            );
        return  $this->ownerBase->exec(join(' ', array(
            "update {$this->name} set",
            join(', ', $_Set_Data),
            "where {$_Where}"
        )));
    }
    public function delete($_Where) {
        return  $this->ownerBase->exec(
            "delete from {$this->name} where {$_Where}"
        );
    }
}
