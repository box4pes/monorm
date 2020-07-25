<?php
declare(strict_types=1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PHPUnit\Framework\TestCase;

use Monorm\HandlerFactory;
use Monorm\Dao\Dao;
use Monorm\DataManager\DataManager;
use Monorm\EntityManager\EntityManager;

use Monorm\Entity\EntityAbstract;

/**
 * Description of Entity
 *
 * @author pes2704
 */
class Entity extends EntityAbstract {

    private $hlava;
    private $krk;
    private $ruce;
    private $nohy;

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
 * Description of MonormTest
 *
 * @author pes2704
 */
class MonormTest extends TestCase {

    private $handler;


    public static function mock() {
    }

    public static function setUpBeforeClass(): void {
    }

    protected function setUp(): void {
        $this->handler = (new HandlerFactory())->create();
        //fixture:
        //vymaže tabulku, zapíše řádky
        $this->handler->exec("DELETE FROM orm");
        $this->handler->exec("INSERT INTO orm (identity, hlava, krk) VALUES ('To je moje identita.', 'Je to hlava má uááá.', 'Krk jí nevnímá.')");
        $this->handler->exec("INSERT INTO orm (identity, hlava, krk, ruce) VALUES ('To je tvoje identita.', 'Je to hlava tvá.', 'Krk ji ovládá.', 'Ty ti seberem.')");
        $this->handler->exec("INSERT INTO orm (identity, hlava, krk, ruce) VALUES ('To je třetí identita.', 'Je to bezhlavá', 'Bez hlavy není krku.', 'Aspoň čtyři ručky.')");

    }

    public function testGetExistingRow() {
        $dataManager = new DataManager(new Dao((new HandlerFactory())->create()));
        $entityManager = new EntityManager($dataManager);

        $mojeEntity = $entityManager->get('To je moje identita.');
        $this->assertInstanceOf(Entity::class, $mojeEntity);
        //update
        $mojeEntity->setHlava('To je hlava cizí.'); //value
        $mojeEntity->setKrk(NULL); //smazání krku staré entitě -> mělo by vzniknout set krk=NULL
        var_dump($mojeEntity);

        $tvojeEntity = $entityManager->get('To je tvoje identita.');
        var_dump($tvojeEntity);
        $tvojeEntity->setRuce(NULL);

        //insert
        $newEntity = new Entity('To je nová identita.');
        $newEntity->setNohy('Má jen nohy.')->setRuce('A taky ruce.')->setKrk('Půjčím ti na chvilu krk.');
        var_dump($newEntity);
        $entityManager->persist($newEntity);
        $newEntity->setKrk(NULL);  //smazání krku nové entitě -> smaže se jen vlastnost entity, až se budou vytvářet data, tak už tak davno vlastnost není

        $tretiEntity = $entityManager->get('To je třetí identita.');
        var_dump($tretiEntity);
        $entityManager->unpersist($tretiEntity);
    }
}
