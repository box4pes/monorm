<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\Hydrator;

use Monorm\RowData\RowDataInterface;

/**
 * Bezstavový hydrátor
 *
 * @author pes2704
 */
class Hydrator {

    /**
     * Hydratuje voláním setter metod entity.
     * Pokud data s příslušným indexem neexistují setter nevolá.
     *
     * @param type $entity
     * @param RowDataInterface $data
     */
    public function hydrate($entity, RowDataInterface $data){
        $classMethods = get_class_methods(get_class($entity));
        foreach ($data as $key => $value) {
            $methodName = 'set'.$this->underscoreToPascalCase($key);
            if (array_key_exists($methodName, $classMethods)) {
                $entity->$methodName($value);
            } else {
                throw new \UnexpectedValueException("Nelze použít data RowData objektu s indexem $key, entita nemá metodu $methodName.");
            }
        }
    }

    /**
     * Extrahuje voláním getter metod entity. Nastaví data s indexy odpovídajícími jménům getterů.
     * Existující data přepíše, neexistující data přidá. Data navíc v argumentu $data nevadí.
     *
     * @param type $entity
     * @param RowDataInterface $data
     */
    public function extract($entity, RowDataInterface $data) {
        foreach (get_class_methods(get_class($entity)) as $methodName) {
            if (strpos($methodName, 'get') === 0) {
                $camelCaseName = substr($methodName, 3);                   // setRazDva -> RazDva
                $value = $entity->$methodName();     // RazDva -> raz_dva
                // když getter nevrací nic - nechám to na RowData objektu (pozn. $data[NULL] -> offsetSet(index, NULL) ....
                $data[$this->camelCaseToUndescore($camelCaseName)] = $value;
            }
        }
    }

    private function camelCaseToUndescore($camelCaseName) {
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $camelCaseName));
    }

    private function underscoreToPascalCase($underscoredName){   // první písmeno velké
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $underscoredName)));
    }
}

