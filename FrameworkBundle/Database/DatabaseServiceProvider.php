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

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DatabaseServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        $definition = new Definition(Database::class,[
            new Reference('config')
        ]);

        $definition->setFactory(array(PDOFactory::class,'createPDO'));

        $container->setDefinition('db',$definition);
    }
}