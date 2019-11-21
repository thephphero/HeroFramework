<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 18.02.2019
 * Time: 15:35
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundles\PropelBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class PropelExtension extends Extension{

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $configs[0];
        if (1 === count($config['database']['connections'])) {
            $defaultConnection = array_keys($config['database']['connections'])[0];
            if (!isset($config['runtime']['defaultConnection'])) {
                $config['runtime']['defaultConnection'] = $defaultConnection;
            }
            if (!isset($config['generator']['defaultConnection'])) {
                $config['generator']['defaultConnection'] = $defaultConnection;
            }
        }

        $container->setParameter('propel.logging', $config['runtime']['logging']);
        $container->setParameter('propel.configuration', $config);
        $container->setParameter('propel.dbal.default_connection', $defaultConnection);

        //Service Provider
        $serviceProvider = new PropelBundleServiceProvider();
        $serviceProvider->register($container);

        $this->setUserProviderClass($config,$container);
    }

    public function setUserProviderClass(array $configs, ContainerBuilder $container){
        $container->setParameter('propel.security.user.provider.class','Bundles\\PropelBundle\\Security\\User\\PropelUserProvider');
    }
}