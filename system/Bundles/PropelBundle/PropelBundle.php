<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 18.02.2019
 * Time: 15:29
 */

namespace Bundles\PropelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerMasterSlave;
use Propel\Runtime\Connection\ConnectionManagerSingle;

class PropelBundle extends Bundle{

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        try {
            $this->configureConnections();
            if ($this->container->getParameter('propel.logging')) {
                $this->configureLogging();
            }
        } catch( \Exception $e ) {
        }
    }
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('propel');

        if ($container->hasExtension('security')) {
            $container->getExtension('security')->addUserProviderFactory(new PropelFactory('propel', 'propel.security.user.provider'));
        }

    }

    protected function configureConnections()
    {
        $config = $this->container->getParameter('propel.configuration');
        $defaultConnection = !empty($config['runtime']['defaultConnection']) ? $config['runtime']['defaultConnection'] : key($config['database']['connections']);
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->setDefaultDatasource($defaultConnection);
        foreach ($config['database']['connections'] as $name => $config) {
            if (!empty($config['slaves'])) {
                $manager = new ConnectionManagerMasterSlave();
                // configure the master (write) connection
                $manager->setWriteConfiguration($config);
                // configure the slave (read) connections
                $slaveConnections = [];
                foreach ($config['slaves'] as $slave) {
                    $slaveConnections[] = array_merge($config, [
                        'dsn' => $slave['dsn'],
                        'slaves' => null
                    ]);
                }
                $manager->setReadConfiguration($slaveConnections);
            } else {
                $manager = new ConnectionManagerSingle();
                $manager->setConfiguration($config);
            }
            $serviceContainer->setAdapterClass($name, $config['adapter']);
            $serviceContainer->setConnectionManager($name, $manager);
        }
    }


    protected function configureLogging()
    {
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->setLogger('defaultLogger', $this->container->get('propel.logger'));
        foreach ($serviceContainer->getConnectionManagers() as $manager) {
            $connection = $manager->getReadConnection($serviceContainer->getAdapter($manager->getName()));
            $connection->setLogMethods(array_merge($connection->getLogMethods(), array('prepare')));
            $connection = $manager->getWriteConnection();
            $connection->setLogMethods(array_merge($connection->getLogMethods(), array('prepare')));
        }
    }
}