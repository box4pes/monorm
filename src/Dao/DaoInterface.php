<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Dao;

use Monorm\RowData\PdoRowData;  // návratový objekt pro select statement->fetch()
use Monorm\RowData\RowDataInterface;
/**
 *
 * @author pes2704
 */
interface DaoInterface {
    /**
     *
     * @param type $index
     * @return \Monorm\RowData\PdoRowData
     * @throws LogicException
     */
    public function get($index);

    /**
     *
     * @param RowDataInterface $rowData
     */
    public function insert(RowDataInterface $rowData);

    /**
     *
     * @param RowDataInterface $rowData
     * @throws \UnexpectedValueException
     */
    public function update(RowDataInterface $rowData);

    /**
     *
     * @param RowDataInterface $rowData
     */
    public function delete(RowDataInterface $rowData);


}
