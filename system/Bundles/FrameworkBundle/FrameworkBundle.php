<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29/05/18
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle;

use Bundles\FrameworkBundle\DependencyInjection\Compiler\ErrorSubscriberPass;
use Bundles\FrameworkBundle\DependencyInjection\Compiler\LogPass;
use Bundles\FrameworkBundle\DependencyInjection\Compiler\RouteCollectionPass;
use Bundles\FrameworkBundle\DependencyInjection\Compiler\SessionStoragePass;
use Bundles\FrameworkBundle\DependencyInjection\Compiler\ContainerBuilderDebugDumpPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class FrameworkBundle extends Bundle{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new ContainerBuilderDebugDumpPass());
        $container->addCompilerPass(new RouteCollectionPass());
        $container->addCompilerPass(new SessionStoragePass());
        $container->addCompilerPass(new LogPass());
        $container->addCompilerPass(new ErrorSubscriberPass());
    }



}