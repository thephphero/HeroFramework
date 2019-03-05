<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 18.02.2019
 * Time: 15:35
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
    }
}