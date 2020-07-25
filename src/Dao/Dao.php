<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Dao;

use Pes\Database\Handler\HandlerInterface;
use Monorm\RowData\PdoRowData;  // návratový objekt pro select statement->fetch()
use Monorm\RowData\RowDataInterface;

/**
 * Description of Dao
 *
 * @author pes2704
 */
class Dao implements DaoInterface {

    /**
     *
     * @var Handler
     */
    private $dbHandler;

    public function __construct(HandlerInterface $handler) {
        $this->dbHandler = $handler;
    }

    /**
     *
     * @param type $index
     * @return \Monorm\RowData\PdoRowData
     * @throws LogicException
     */
    public function get($index) {
        $stmt = $this->dbHandler->query("SELECT identity, hlava, krk, ruce, nohy FROM orm WHERE identity='$index'");
        if ( ! $stmt->setFetchMode(\PDO::FETCH_CLASS, PdoRowData::class)) {
            throw new \LogicException("Nepodařilo se nastavit fetch mode.");
        }
        $rowData = $stmt->fetch();
        return $rowData;
    }

    /**
     *
     * @param RowDataInterface $rowData
     */
    public function insert(RowDataInterface $rowData) {
        echo '<p>Insert data:</p>';
        foreach ($rowData->fetchChanged() as $key=>$value) {
            if ($value != RowDataInterface::CODE_FOR_NULL_VALUE) {
                $cols[] = $key;
                $values[] = "'".$value."'";
            } else {
                $cols[] = $key;
                $values[] = "NULL";            }
        }
        $sql = "INSERT INTO orm (".implode(', ', $cols).") VALUES (".implode(', ', $values).")";
        echo $sql;
        $this->dbHandler->exec($sql);
   }

    /**
     *
     * @param RowDataInterface $rowData
     * @throws \UnexpectedValueException
     */
    public function update(RowDataInterface $rowData) {
        echo '<p>Update data:</p>';
        foreach ($rowData->fetchChanged() as $key=>$value) {
            if ($key=='identity') {
                throw new \UnexpectedValueException("Byla změněna identita entity.");
            }
            if ($value != RowDataInterface::CODE_FOR_NULL_VALUE) {
                $set[] = $key." = '".$value."'";
            } else {
                $set[] = $key.' = NULL';
            }
        }
        $id = $rowData->identity;
        $sql = "UPDATE orm SET ".implode(', ', $set)." WHERE identity='".$id."'";
        echo $sql;
        $this->dbHandler->exec($sql);
    }

    /**
     *
     * @param RowDataInterface $rowData
     */
    public function delete(RowDataInterface $rowData) {
        echo '<p>Delete data:</p>';
        $id = $rowData->identity;
        $sql = "DELETE FROM orm WHERE identity='".$id."'";
        echo $sql;
        $this->dbHandler->exec($sql);
    }
}

