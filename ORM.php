<?php
namespace Monorm;

use Monorm\DataManager\DataManager;
use Monorm\EntityManager\EntityManager;
use Monorm\Dao\Dao;
use Monorm\Entity\Entity;

require 'vendor/autoload.php';

function setUp() {
    //fixture:
    //vymaaže tabulku, zapíše řádky
    $dbh = (new HandlerFactory())->create();
    $dbh->exec("DELETE FROM orm");
    $dbh->exec("INSERT INTO orm (identity, hlava, krk) VALUES ('To je moje identita.', 'Je to hlava má uááá.', 'Krk jí nevnímá.')");
    $dbh->exec("INSERT INTO orm (identity, hlava, krk, ruce) VALUES ('To je tvoje identita.', 'Je to hlava tvá.', 'Krk ji ovládá.', 'Ty ti seberem.')");
    $dbh->exec("INSERT INTO orm (identity, hlava, krk, ruce) VALUES ('To je třetí identita.', 'Je to bezhlavá', 'Bez hlavy není krku.', 'Aspoň čtyři ručky.')");
}

    setUp();

    $dataManager = new DataManager(new Dao((new HandlerFactory())->create()));
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
    $newEntity = new Entity('To je nová identita.');
    $newEntity->setNohy('Má jen nohy.')->setRuce('A taky ruce.')->setKrk('Půjčím ti na chvilu krk.');
    var_dump($newEntity);
    $entityManager->persist($newEntity);
    $newEntity->setKrk(NULL);  //smazání krku nové entitě -> smaže se jen vlastnost entity, až se budou vytvářet data, tak už tak davno vlastnost není

    $tretiEntity = $entityManager->get('To je třetí identita.');
    var_dump($tretiEntity);
    $entityManager->unpersist($tretiEntity);