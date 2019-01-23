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
namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ErrorSubscriberPass implements CompilerPassInterface{

    /**
     * process
     * The current system environment gets read from the .env file
     * and is handed down to the kernel for processing. There it gets
     * inserted into the container as a kernel paramenter.
     *
     * This pass reads the current value of the kernel.environment from
     * the container and sets it as second argument of ExceptionServiceProvider shortly
     * before compilation and caching of all classes in the container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $environment = $container->getParameter('kernel.environment');

        $definition =  $container->findDefinition('kernel.exception_listener');

        $definition->replaceArgument(1,$environment);
    }
}