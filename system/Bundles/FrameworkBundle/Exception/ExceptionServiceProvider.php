<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/10/2017
 * Time: 00:51
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Exception;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Library\Routing\Listeners\HttpNotFoundListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Library\Exception\ExceptionSubscriber;
class ExceptionServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        $definition=new Definition(ExceptionSubscriber::class,[
            'App\\common\\ErrorController::exception',
            null,//This is the environment and gets set during compilation
            new Reference('log')
        ]);

        if($container->getParameter('kernel.debug')==false) {
            $definition->addTag('kernel.event_subscriber');
        }
        $container->setDefinition('kernel.exception_listener',$definition);

    }

}