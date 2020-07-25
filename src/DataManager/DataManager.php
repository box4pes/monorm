<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\DataManager;

use Monorm\Dao\DaoInterface;

/**
 * Description of DstaManager
 *
 * @author pes2704
 */
class DataManager {

    /**
     * @var DaoInterface
     */
    private $dao;
    private $oldDataStorage;
    private $newDataStorage;
    private $removedDataStorage;

    public function __construct(DaoInterface $dao) {
        $this->dao = $dao;
        $this->oldDataStorage = new \ArrayObject();
        $this->newDataStorage = new \ArrayObject();
        $this->removedDataStorage = new \ArrayObject();
    }

    public function get($index) {
        if ($this->newDataStorage->offsetExists($index)) {
            return $this->newDataStorage->offsetGet($index);
        } elseif (!$this->oldDataStorage->offsetExists($index)) {
            $data = $this->dao->get($index);
            $this->oldDataStorage->offsetSet($index, $data);
        }
        if ($this->oldDataStorage->offsetExists($index)) {
            return $this->oldDataStorage->offsetGet($index);
        } else {
            throw new UnexpectedValueException("V úložišti (databázi) neexistují data se požadovaným indexem.");
        }
    }

    public function set($index, $data) {
        if ($this->oldDataStorage->offsetExists($index)) {
            $this->oldDataStorage->offsetSet($index, $data);
        } else {
            $this->newDataStorage->offsetSet($index, $data);
        }
        return $this;
    }

    public function unset($index) {
        if ($this->oldDataStorage->offsetExists($index)) {
            $this->removedDataStorage->offsetSet($index, $this->oldDataStorage->offsetGet($index));
            $this->oldDataStorage->offsetUnset($index);
        }
        if ($this->newDataStorage->offsetExists($index)) {
            $this->newDataStorage->offsetUnset($index);
        }
        return $this;
    }

    public function flush() {
        foreach ($this->oldDataStorage as $rowData) {
            if ($rowData->isChanged()) {
                $this->dao->update($rowData);
            }
        }
        foreach ($this->newDataStorage as $rowData) {
            $this->dao->insert($rowData);
        }
        foreach ($this->removedDataStorage as $rowData) {
            $this->dao->delete($rowData);
        }
    }

}

