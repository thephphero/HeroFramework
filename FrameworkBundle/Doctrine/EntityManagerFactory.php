<?php

namespace Library\Doctrine;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Tools\Setup;
use Library\Config\Config;
use Library\Doctrine\Functions\DateFunction;

class EntityManagerFactory {
    /**
     * @param Config $config
     * @returns EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    public static function createEntityManager(Config $config) {
        $paths = [
            __DIR__
        ];

        $isDevMode = $config->get('environment') === 'development' ? true : false;
        $proxyDir = $config->get('root_dir').'/storage/cache/doctrineproxy';

        $emConfig = Setup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            $proxyDir,
            self::getAnnotationCache($config),
            false
        );

        $emConfig->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

        $emConfig->addCustomDatetimeFunction('DATE', function() {
            return new DateFunction();
        });

        $em = EntityManager::create(self::getDatabaseParams($config), $emConfig);

        AnnotationRegistry::registerLoader('class_exists'); // todo: There should be something way better than this.

        return $em;
    }

    /**
     * @param Config $config
     * @return CacheProvider
     * @throws \Exception
     */
    protected static function getAnnotationCache(Config $config) {
        if($config->get('environment') === 'development')
            return new ArrayCache();

        //
        $cacheDirectoryPath = $config->get('root_dir').'/storage/cache/doctrineannotations';
        if(!is_dir($cacheDirectoryPath)) {
            $result = mkdir($cacheDirectoryPath, 0775, true);

            if(!$result) {
                throw new \Exception('Unable to write to the storage/cache directory.');
            }

            chmod($cacheDirectoryPath, 0775);
        }

        $fileSystemCache = new FilesystemCache($cacheDirectoryPath);
        return $fileSystemCache;
    }

    protected static function getDatabaseParams(Config $config) {
        return [
            'host' => $config->get('DB_HOST'),
            'driver' => 'pdo_mysql', // todo: This should not be hardcoded.
            'user' => $config->get('DB_USER'),
            'password' => $config->get('DB_PASS'),
            'dbname' => $config->get('DB_SCHEMA'),
            'charset' => 'utf8' // todo: This should not be hardcoded.
        ];
    }
}