<?php
/**
 * Created by PhpStorm.
 * User: celsoluiz81
 * Date: 05.08.17
 * Time: 23:00
 */

namespace Bundles\FrameworkBundle\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;

class ConnectionFactory{

    public static function create(array $params, Configuration $config = null, EventManager $eventManager = null){

        return DriverManager::getConnection($params,$config,$eventManager);
    }
}