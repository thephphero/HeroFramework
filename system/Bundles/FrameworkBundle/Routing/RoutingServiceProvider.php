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

namespace Bundles\FrameworkBundle\Routing;

use Core\Container;

use Bundles\FrameworkBundle\Routing\Listeners\MiddlewareListener;
use Bundles\FrameworkBundle\Interfaces\EventListenerProviderInterface;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\KernelEvents;
class RoutingServiceProvider implements ServiceProviderInterface{


    public function register(ContainerBuilder $container){


        //Request Context
        $contextDefinition = new Definition(RequestContext::class);
        $container->setDefinition('context',$contextDefinition);

        //Route Collection
        $collectionDefinition = new Definition(RouteCollection::class);
        $container->setDefinition('routing.route_collection',$collectionDefinition);

        //URL Generator
        $generatorDefinition = new Definition(UrlGenerator::class,[
            new Reference('routing.route_collection'),
            new Reference('context'),
            new Reference('log')
        ]);
        $container->setDefinition('url_generator',$generatorDefinition);

        //Route Loader
        $routeLoaderDefinition = new Definition(RouteLoader::class,[
            new Reference('request'),
            new Reference('config')
        ]);
        $container->setDefinition('routing.route_loader',$routeLoaderDefinition);

        //Router
        $routerDefinition = new Definition(Router::class,[
            new Reference('routing.route_loader'),
            null,
            $options=array(),
            new Reference('context'),
            new Reference('log')
        ]);
        $container->setDefinition('router',$routerDefinition);

        //URL Matcher Factory
        $urlMatcherFactoryDefinition=new Definition(URLMatcherFactory::class,[
            new Reference('router'),
            new Reference('context')
        ]);
        $container->setDefinition('routing.matcher_factory',$urlMatcherFactoryDefinition);

        //URL Matcher
        $matcherDefinition= new Definition(UrlMatcher::class,[
            new Reference('routing.route_collection'),
            new Reference('context')
        ]);
        $matcherDefinition->setFactory([new Reference('routing.matcher_factory'),'create']);
        $container->setDefinition('matcher',$matcherDefinition);

        //Router Listener
        $routerListenerDefinition=new Definition(RouterListener::class,[
            new Reference('matcher'),
            new Reference('request_stack'),
            new Reference('context'),
            new Reference('log')
        ]);
        $routerListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('listener.router',$routerListenerDefinition);

        //Middleware Listener
        $middlewareListenerDefinition = new Definition(MiddlewareListener::class,[
            new Reference('service_container')
        ]);
        $middlewareListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('listener.middleware',$middlewareListenerDefinition);


    }

}


