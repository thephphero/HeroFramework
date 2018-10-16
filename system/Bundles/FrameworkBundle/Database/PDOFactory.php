<?php

namespace Bundles\FrameworkBundle\Database;

use Bundles\FrameworkBundle\Config\Config;

class PDOFactory{

    public static function createPDO(Config $config)
    {
        $PDO = Database::GetInstance($config)->GetPDO();

        return $PDO;
    }
}