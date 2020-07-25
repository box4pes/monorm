<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Identity;

/**
 *
 * @author pes2704
 */
interface IdentityAttributeInterface {
    public function isEqual(IdentityAttributeInterface $attribute);
    public function getFields();
}
