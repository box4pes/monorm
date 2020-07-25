<?php

interface RowDataInterface extends \IteratorAggregate, \ArrayAccess, \Serializable, \Countable {   
    // extenduje všechna rozhraní, která implementuje \ArrayObject mimo \Traversable - to nelze neb je prázdné
    public function isChanged();
    public function getChanged();
    public function deleteChanged();
}


/**
 * Ukládá data, která byla nastavena po instancování RowData (i nezměněná). 
 * Data jsou v paměti duplikována, ale není třeba je pak vybírat nebo odmazávat z původního array objectu. 
 * Třída tak upřednostňuje rychlost za cenu vyšší paměťové náročnosti. Je vhodná, pokud se ukládá jen málo a data nejsou velká.
 */
class RowData extends \ArrayObject implements RowDataInterface {
            
    const CODE_FOR_NULL_VALUE = 'special_string_for_NULL_value';
    
    private $changed;
    
    public function __construct($data = []) {
        parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
    }
    
    public function isChanged() {
        return count($this->changed) ? TRUE : FALSE;
    }
    
    public function getChanged() {
        return $this->changed;
    }
    
    public function deleteChanged() {
        unset($this->changed);
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
     * Ukládá indexy dat, která byla změněna po instancování. Metodě offsetSet nevadí, když je zavolána s hodnotou $value=NULL.
     * Postupuje takto:
     * Stará data jsou, metoda vrací jinou hodnotu -> unset data + nastavit changed=value
     * Stará data jsou, value je NULL -> unset data + nastavit changed=value + nastavit nulled=TRUE -> umožní provést zápis NULL do db = smazání sloupce (pokud db neumožňuje null, je to chyba návrhu aplikace)
     *  tak, že v SQL INSERT musí být INSERT INTO tabulka (sloupec) VALUES (NULL) - NULL je řetězec 'NULL' -> nemůžu je vkládat proměnnou s "hodnotou" NULL 
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
            } elseif (!parent::offsetExists($index)) {
                $this->changed[$index] = $value;                
            }
        } elseif (parent::offsetExists($index)) {
            // smazat data
            parent::offsetUnset($index);
            $this->changed[$index] = self::CODE_FOR_NULL_VALUE;
        }
    }
}

interface DaoInterface {
    public function get($index);
    
    public function insert(RowDataInterface $rowData);
    
    public function update(RowDataInterface $rowData);
}

class Dao implements DaoInterface {
    
    private $database = array(
        'To je moje identita.' => array('identity'=>'To je moje identita.', 'hlava'=>'Je to hlava má.', 'krk'=>'Krk jí nevnímá.'),
        'To je tvoje identita.' => array('identity'=>'To je tvoje identita.', 'hlava'=>'Je to hlava tvá.', 'krk'=>'Krk ji ovládá.', 'ruce'=>'Ty ti seberem.')
        
    );
    
    public function get($index) {
        return new RowData($this->database[$index]);
    }
    
    public function insert(RowDataInterface $rowData) {
        echo '<p>Insert data:</p>';
        var_dump($rowData->getChanged());

        $rowData->deleteChanged();
   }
    
    public function update(RowDataInterface $rowData) {
        echo '<p>Update data:</p>';
        echo '<p>Updated values:</p>';
        foreach ($rowData->getChanged() as $key=>$value) {
            if ($value != RowData::CODE_FOR_NULL_VALUE) {
                $set[] = $key.' = '.$value;
            } else {
                $set[] = $key.' = NULL';
            }
        }
        echo '<p>SET '.implode(', ', $set).'</p>';
        var_dump($rowData->getChanged());
        
        $rowData->deleteChanged();
    }
}

class DataManager {
    
    private $dao;
    private $oldDataStorage;
    private $newDataStorage;


    public function __construct() {
        $this->dao = new Dao();
        $this->oldDataStorage = new \ArrayObject();
        $this->newDataStorage = new \ArrayObject();
    }

    public function get($index) {
        if ($this->newDataStorage->offsetExists($index)) {
            return $this->newDataStorage->offsetGet($index);
        } elseif (!$this->oldDataStorage->offsetExists($index)) {
            $data = $this->dao->get($index);
            $this->oldDataStorage->offsetSet($index, $data);
        }
        if ($this->oldDataStorage->offsetExists($index)) {
            return $this->oldDataStorage->offsetGet($index);
        } else {
            throw new UnexpectedValueException("V úložišti (databázi) neexistují data se požadovaným indexem.");
        }
    }    
    
    public function set($index, $data) {
        if ($this->oldDataStorage->offsetExists($index)) {
            $this->oldDataStorage->offsetSet($index, $data);
        } else {
            $this->newDataStorage->offsetSet($index, $data);
        }
        return $this;
    }
    
    public function flush() {
        foreach ($this->oldDataStorage as $rowData) {
            if ($rowData->isChanged()) {
                $this->dao->update($rowData);
            }
        }
        foreach ($this->newDataStorage as $rowData) {
            $this->dao->insert($rowData);
        }
    }

}

class EntityManager {
    
    private $oldEntitiesStorage;
    private $newEntitiesStorage;
    private $dataManager;
    private $flushed;
    
    public function __construct(DataManager $dataManager) {
        $this->dataManager = $dataManager;
    }
        
    public function get($identity) {
        $data = $this->dataManager->get($identity);
        return $this->hydrate(new Entity($identity));
    }
    
    public function persist(Entity $entity, $identity) {
        if ($entity->getIdentity()) {
            throw new LogicException('Chybný pokus o persistování entity, která má identitu (entita předtím načtená z databáze) metodou persist(), která je určena je pro nově vytvořené entity.');
        }        
        $index = $identity;
        $this->newEntitiesStorage[$index] = $entity;
    }
    
    public function hydrate(Entity $entity) {
        $identity = $entity->getIdentity();
        if (!$identity) {
            throw new UnexpectedValueException("Není entity bez identity! Entity musí mít identitu před pokusem o hydrataci.");
        }
        $index = $identity;
        if (!isset($this->oldEntitiesStorage[$index])) {
            $hydrator = new Hydrator();
            $hydrator->hydrate($entity, $this->dataManager->get($index));  
            $this->oldEntitiesStorage[$index] = $entity;
        }
        return $this->oldEntitiesStorage[$index];
    }
    
    public function flush() {
            if ($this->oldEntitiesStorage) {
                $hydrator = new Hydrator();        
                foreach ($this->oldEntitiesStorage as $index => $entity) {
                    $data = $this->dataManager->get($index);
                    $hydrator->extract($entity, $data);
                }
            }
            if ($this->newEntitiesStorage) {
                foreach ($this->newEntitiesStorage as $index => $entity) {
                    $data = new RowData();  // tady není třeba big data
                    $hydrator->extract($entity, $data);
                    $this->dataManager->set($index, $data);
                }
            }
            unset($this->newEntitiesStorage);  // v případě opakování flush() se budou insertovat jen další nové entity
            $this->dataManager->flush();
    }
    
    public function __destruct() {
        $this->flush();
    }
}

class Entity {
    private $identity;
    private $hlava;
    private $krk;
    private $ruce;
    private $nohy;
    
    public function __construct($identity=NULL) {
        $this->identity = $identity;
    }
    
    public function getIdentity() {
        return $this->identity;
    }    
    
    public function getHlava() {
        return $this->hlava;
    }

    public function getKrk() {
        return $this->krk;
    }

    public function getRuce() {
        return $this->ruce;
    }

    public function getNohy() {
        return $this->nohy;
    }

    public function setHlava($hlava) {
        $this->hlava = $hlava;
        return $this;
    }

    public function setKrk($krk) {
        $this->krk = $krk;
        return $this;
    }

    public function setRuce($ruce) {
        $this->ruce = $ruce;
        return $this;
    }

    public function setNohy($nohy) {
        $this->nohy = $nohy;
        return $this;
    }
}

/**
 * Bezstavový
 */
class Hydrator {
    
    /**
     * Hydratuje voláním setter metod entity. Používá jen data s indexy odpovídajícími jménům metod. 
     * Při hydrataci se tedy řídí jmény metod entity a ne indexy zadaných dat.
     * Pokud data s příslušným indexem neexistují setter nevolá.
     * 
     * @param type $entity
     * @param RowDataInterface $data
     */
    public function hydrate($entity, RowDataInterface $data){
        foreach (get_class_methods(get_class($entity)) as $methodName) {
            if (strpos($methodName, 'set') === 0) {
                $camelCaseName = substr($methodName, 3);                   // setRazDva -> RazDva
                $dataName = $this->camelCaseToUndescore($camelCaseName);     // RazDva -> raz_dva
                if (isset($data[$dataName])) {
                    $entity->$methodName($data[$dataName]);    
                } 
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
        return strtolower(preg_replace( '/([a-z0-9])([A-Z])/', "$1_$2", $camelCaseName ));
    }
}

#############################################################################################################################

//require '../../vendor/autoload.php';
    
    $dataManager = new DataManager();
    $entityManager = new EntityManager($dataManager);
    $mojeEntity = $entityManager->get('To je moje identita.');
    var_dump($mojeEntity);
    //update
    $mojeEntity->setHlava('To je hlava cizí.'); //value
    $mojeEntity->setKrk(NULL); //smazání krku staré entitě -> mělo by vzniknout set krk=NULL
    var_dump($mojeEntity);

    $tvojeEntity = $entityManager->get('To je tvoje identita.');
    var_dump($tvojeEntity);
    $tvojeEntity->setRuce(NULL);

    //insert
    $newEntity = new Entity();
    $newEntity->setNohy('Má jen nohy.')->setRuce('A taky ruce.')->setKrk('Půjčím ti na chvilu krk.')->setKrk(NULL);  //smazání krku nové entitě -> smaže se jen vlastnost entity, až se budou vytvářet data, tak už tak davno vlastnost není 
    var_dump($newEntity);
    $entityManager->persist($newEntity, 'To je nová identita.');