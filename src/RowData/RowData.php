<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Monorm\RowData;

/**
 * Description of RowData
 *
 * @author pes2704
 */
class RowData extends \ArrayObject implements RowDataInterface {


    private $changed;
    private $nulled;

    /**
     * V kostruktoru se mastaví způsob zapisu dat do RowData objektu na ARRAY_AS_PROPS a pokud je zadán parametr $data, zapíší se tato data
     * do interní storage objektu. Nastavení ARRAY_AS_PROPS způsobí, že každý zápis dalších dat je prováděn metodou offsetSet.
     * @param type $data
     */
    public function __construct($data=[]) {
        parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
    }
    
    public function isChanged() {
        return (isset($this->changed) AND count($this->changed)) ? TRUE : FALSE;
    }

    /**
     * Vrací pole změněných hodnot od instancování objektu nebo od posledního volání této metody.
     * Metoda vrací evidované změněné hodnoty a evidenci změněných hodnot smaže. Další změny jsou pak dále evidovány a příští volání
     * této metody vrací jen tyto další změny.
     *
     * @return array
     */
    public function fetchChanged() {
        if (isset($this->changed)) {
            $ret = $this->changed;
            unset($this->changed);
        } else {
            $ret = [];  // zajišťuje správný návratový typ + když není isset($this->changed) tak přiřazení $ret = $this->changed volá metodu offsetGet('changed')
        }

        return $ret;
    }

    public function offsetGet($index) {
        return parent::offsetGet($index);
    }

    public function offsetExists($index) {
        return parent::offsetExists($index);
    }

    public function exchangeArray($data) {
        // Zde by se musely v cyklu vyhodnocovat varianty byla/nebyla data x jsou/nejsou nová data
        throw new LogicException('Nelze použít metodu exchangeArray(). Použijte offsetSet().');
    }

    public function append($value) {
        throw new LogicException('Nelze vkládat neindexovaná data. Použijte offsetSet().');
    }

    /**
     * Ukládá data, která byla změněna po instancování. Metodě offsetSet nevadí, když je zavolána s hodnotou $value=NULL.
     * Postupuje takto:
     * Stará data jsou, metoda vrací jinou hodnotu -> unset data + nastavit changed=value
     * Stará data jsou, value je NULL -> nastavit  speciální hodnotu changed = self::CODE_FOR_NULL_VALUE -> umožní provést zápis NULL do db = smazání sloupce
     *  tak, že v SQL INSERT musí být INSERT INTO tabulka (sloupec) VALUES (NULL) - NULL je klíčová (rezervované) slovo -> nemůžu je vkládat jako proměnnou s "hodnotou" NULL
     *  pak mám INSERT INTO tabulka (sloupec) VALUES () a to NULL nevyrobí
     * Stará data nejsou, metoda vrací hodnotu (ne NULL) -> nastavit changed=value
     * Stará data nejsou, metoda vrací NULL -> stará data nejsou protože je v db NULL nebo se sloupec v selectu nečetl -> v obou případech nedělat nic
     *
     * @param type $index
     * @param type $value
     */
    public function offsetSet($index, $value) {
//        if ($this->getChangedWasCalled) {
//            throw new LogicException('Již byla zavolána metoda getChanged() a data jsou zničena. Objekt nelze dále používat.');
//        }
        if (isset($value)) {
            // změněná nebo nová data
            if (parent::offsetExists($index) AND parent::offsetGet($index) != $value) {
                parent::offsetUnset($index);
                $this->changed[$index] = $value;
            } elseif (!parent::offsetExists($index)) {  // nová data nebo opakovaně měněná data
                $this->changed[$index] = $value;
            }
        } elseif (parent::offsetExists($index) AND parent::offsetGet($index) !== NULL) {
        // kontrola !== NULL je nutná, extract volá všechny settery a pokud vlastnost nebyla vůbec nastavena setter vrací NULL
        // musím kontrolavat, že data jsou NULL, ale původně nebyla - proto nelze volat offseUnset (ale data se neduplikují, v changed je jen konstanta)
            // smazat existující data
            $this->changed[$index] = self::CODE_FOR_NULL_VALUE;
        }
    }

}
