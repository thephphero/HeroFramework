<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 05/08/2017
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Doctrine\ORM\EntityManager;

class DoctrineServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        $definition = new Definition(
            EntityManager::class,
            [
                new Reference('config')
            ]
        );

        $definition->setFactory([EntityManagerFactory::class, 'createEntityManager']);
        $definition->setLazy(true);

        $container->setDefinition('entityManager', $definition);
    }
}