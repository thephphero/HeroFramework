<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 25/12/16
 * Time: 13:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\HttpKernel;

use EventListeners\MiddlewareListener;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Library\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernel;
use Bundles\FrameworkBundle\Controller\ContainerAwareControllerResolver;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Bundles\FrameworkBundle\Interfaces\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpKernelServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        //Request stack
        $requestStackDefinition = new Definition(RequestStack::class);
        $container->setDefinition('request_stack',$requestStackDefinition);

        //Controller Resolver
        $controllerResolverDefinition = new Definition(ContainerAwareControllerResolver::class,[
            new Reference('log'),
            new Reference('service_container')
        ]);
        $container->setDefinition('controller_resolver',$controllerResolverDefinition);

        //Event Dispatcher
        $dispatcherDefinition = new Definition(EventDispatcher::class);
        $container->setDefinition('event_dispatcher',$dispatcherDefinition);

        //HttpKernel
        $kernelDefinition = new Definition(HttpKernel::class,[
            new Reference('event_dispatcher'),
            new Reference('controller_resolver'),
            new Reference('request_stack'),
        ]);
        $container->setDefinition('http_kernel',$kernelDefinition);

        //Response Listener
        $responseListenerDefinition=new Definition(ResponseListener::class,[
            '%kernel.charset%'
        ]);
        $responseListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('response.listener',$responseListenerDefinition);

    }

}