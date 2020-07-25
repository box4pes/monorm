<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Entity;

use Monorm\Identity\IdentityKeyInterface;

/**
 * Description of EntityAbstract
 *
 * @author pes2704
 */
class EntityAbstract implements EntityInterface {

    private $identityKey;

    public function __construct(IdentityKeyInterface $identityKey) {
        $this->identityKey = $identityKey;
    }
    
    public function getIdentityKey(): IdentityKeyInterface {
        return $this->identityKey;
    }
}
