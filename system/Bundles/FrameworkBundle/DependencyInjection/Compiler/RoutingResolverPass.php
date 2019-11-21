<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 06/09/18
 * Time: 14:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\RouteCollectionBuilder;

class RoutingResolverPass implements CompilerPassInterface{

    const loaderTag = 'routing.loader';
    const  resolverServiceId = 'routing.loader_resolver';

    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {

        if (false === $container->hasDefinition(self::resolverServiceId)) {
            return;
        }
        $resolverDefinition = $container->getDefinition(self::resolverServiceId);
        foreach ($this->findAndSortTaggedServices(self::loaderTag, $container) as $id) {
            $resolverDefinition->addMethodCall('addLoader', [$id]);
        }

    }
}