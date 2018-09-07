<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 20/11/16
 * Time: 15:50
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Database;

use Bundles\FrameworkBundle\Config\Config;
use PDO;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Database {

    private $error;

    private static $instance = null;

    private $pdo;


    private function __construct($host, $schema, $user, $password) {

        $this->Connect($host, $schema, $user, $password);
    }

    public static function GetInstance(Config $config) {

        $host=$config->get('DB_HOST');
        $schema=$config->get('DB_SCHEMA');
        $user=$config->get('DB_USER');
        $password=$config->get('DB_PASS');

        if (!self::$instance)
            self::$instance = new database($host, $schema, $user, $password);

        return self::$instance;
    }


    private function Connect($host, $databaseName, $username, $password) {

        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC
        );

        $dsn = 'mysql:dbname=' . $databaseName . ';host=' . $host . ';charset=utf8';

        try {

            $this->pdo = new PDO($dsn, $username, $password, $options);

        } catch (PDOException $e) {

            $this->error = $e->getMessage();
            throw new Exception($this->error);
        }
    }

    public function GetPDO() {
        return $this->pdo;
    }

}

?>
