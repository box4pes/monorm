<?php

namespace Monorm\Identity;

/**
 * Description of Relation
 *
 * @author pes2704
 */
class IdentityReference implements IdentityReferenceInterface, \Serializable {

    private $databaseNick;

    private $relationName;
    /**
     * @var IdentityKeyInterface
     */
    private $key;

    /**
     *
     * @param type $databaseName
     * @param type $relationName
     * @param IdentityKeyInterface $key
     */
    public function __construct($databaseNick, $relationName, IdentityKeyInterface $key) {
        $this->databaseNick = $databaseNick;
        $this->relationName = $relationName;
        $this->key = $key;
    }

    /**
     * Nick databáze
     * @return string
     */
    public function getDatabaseNick() {
        return $this->databaseNick;
    }

    /**
     * Jméno relace, t.j. jméno databázové tabulky
     * @return string
     */
    public function getRelationName() {
        return $this->relationName;
    }

    /**
     * Vrací objekt - klíč relace.
     * @return IdentityKeyInterface
     */
    public function getKey() {
        return $this->key;
    }

    public function serialize() {
        return serialize(
            array(
                'databaseNick' => $this->databaseNick,
                'relationName' => $this->relationName,
                'key' => serialize($this->key)
            ));
    }

    public function unserialize($serialized) {
        $data = unserialize($serialized);
        $this->databaseNick = $data['databaseNick'];
        $this->relationName = $data['relationName'];
        $this->key = unserialize($data['key']);
    }
}
