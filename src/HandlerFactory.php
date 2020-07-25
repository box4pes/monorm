<?php

namespace Monorm;

use Pes\Database\Handler\Account;
use Pes\Database\Handler\ConnectionInfo;
use Pes\Database\Handler\DbTypeEnum;
use Pes\Database\Handler\DsnProvider\DsnProviderMysql;
use Pes\Database\Handler\OptionsProvider\OptionsProviderMysql;
use Pes\Database\Handler\AttributesProvider\AttributesProvider;
use Pes\Database\Handler\Handler;

use Psr\Log\NullLogger;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HandlerFactory
 *
 * @author pes2704
 */
class HandlerFactory {


    const DB_NAME = 'pes';
    const DB_HOST = 'localhost';
    const USER = 'pes_tester';
    const PASS = 'pes_tester';

    /**
     * vytvoří db handler
     * @return Handler
     */
    public function create() {
        $account = new Account(self::USER, self::PASS);
        $connectionInfoUtf8 = new ConnectionInfo(DbTypeEnum::MySQL, self::DB_HOST, self::DB_NAME);
        $dsnProvider = new DsnProviderMysql();
        $optionsProvider = new OptionsProviderMysql();
        $logger = new NullLogger();
        $attributesProviderDefault = new AttributesProvider();
        return new Handler($account, $connectionInfoUtf8, $dsnProvider, $optionsProvider, $attributesProviderDefault, $logger);
    }
}
