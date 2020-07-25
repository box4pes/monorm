<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\RowData;

/**
 *
 * @author pes2704
 */
interface RowDataInterface extends \IteratorAggregate, \ArrayAccess, \Serializable, \Countable {

    const CODE_FOR_NULL_VALUE = 'special_string_for_NULL_value';

    // extenduje všechna rozhraní, která implementuje \ArrayObject mimo \Traversable - to nelze neb je prázdné
    public function isChanged();
    public function fetchChanged();
}
