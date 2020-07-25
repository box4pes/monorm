<?php

namespace Monorm\Identity;

/**
 *
 * @author pes2704
 */
interface IdentityKeyInterface {

    public function isGenerated();
    public function getAttribute(): IdentityAttributeInterface;
    public function getKeyHash();
    public function setKeyHash(array $keyHash);

    /**
     * Shodné klíče - mají stejné páry index/hodnota, nezáleží na pořadí.
     */
    public function isEqual(IdentityKeyInterface $key);
}
