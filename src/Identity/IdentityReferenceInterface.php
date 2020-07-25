<?php

namespace Monorm\Identity;

/**
 * Description of RelationInterface
 *
 * @author pes2704
 */
interface IdentityReferenceInterface {

    public function getDatabaseNick();

    public function getRelationName();

    /**
     * @return IdentityKeyInterface
     */
    public function getKey();

}
