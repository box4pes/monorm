<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Identity;

use Monorm\Identity\Exception\InvalidAttributeFieldsException;

/**
 * Description of IdentityAttribute
 *
 * @author pes2704
 */
class IdentityAttribute implements IdentityAttributeInterface, \Serializable {

    private $attributeFields = [];

    public function __construct(array $attributeFields) {
        if ($attributeFields) {
            $this->attributeFields = $attributeFields;
        } else {
            throw new InvalidAttributeFieldsException("Identity attribut musí mít alespoň jedno pole.");
        }
    }

    public function getFields() {
        return $this->attributeFields;
    }

    public function isEqual(IdentityAttributeInterface $attribute) {
        return array_values($this->attributeFields) == array_values($attribute->getFields());
    }

    public function serialize() {
        return serialize($this->attributeFields);
    }

    public function unserialize($serialized) {
        $this->attributeFields = unserialize($serialized);
    }
}
