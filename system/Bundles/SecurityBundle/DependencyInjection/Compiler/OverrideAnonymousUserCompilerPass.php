<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 22.01.2019
 * Time: 15:11
 */

namespace Bundles\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bundles\SecurityBundle\Security\Listeners\AnonymousAuthenticationListener;
class OverrideAnonymousUserCompilerPass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('security.authentication.listener.anonymous');
        $definition->setClass(AnonymousAuthenticationListener::class);
    }
}