<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 13.12.2018
 * Time: 14:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\DependencyInjection;

use Bundles\FrameworkBundle\Locale\Language;
use Bundles\FrameworkBundle\Locale\LocaleListener;
use Bundles\FrameworkBundle\Template\Template;
use Bundles\FrameworkBundle\Template\TemplateFactory;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bundles\FrameworkBundle\Config\Config;
use Bundles\FrameworkBundle\Exception\ExceptionSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernel;
use Bundles\FrameworkBundle\Controller\ContainerAwareControllerResolver;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Bundles\FrameworkBundle\Log\Log;
use Bundles\FrameworkBundle\Request\Request;
use Bundles\FrameworkBundle\Request\RequestFactory;
use Bundles\FrameworkBundle\Response\Listeners\ViewResponseListener;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Router;
use Bundles\FrameworkBundle\Routing\Listeners\MiddlewareListener;
use Bundles\FrameworkBundle\Routing\RouteLoader;
use Bundles\FrameworkBundle\Routing\URLMatcherFactory;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\EventListener\SaveSessionListener;
use Bundles\FrameworkBundle\Session\SessionListener;
use Bundles\FrameworkBundle\Database\Database;
use Bundles\FrameworkBundle\Database\PDOFactory;
use Twig\Environment;

class FrameworkBundleServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        //Request stack
        $requestStackDefinition = new Definition(RequestStack::class);
        $container->setDefinition('request_stack',$requestStackDefinition);

        //Request
        $requestFactoryDefinition=new Definition(RequestFactory::class);
        $container->setDefinition('request.factory',$requestFactoryDefinition);
        $requestDefinition=new Definition(Request::class);
        $request=$container->setDefinition('request',$requestDefinition);
        $request->setFactory([new Reference('request.factory'),'create']);

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

        //Config class
        $definition = new Definition(Config::class,[
                new Reference('service_container') ]
        );
        $container->setDefinition('config',$definition);

        //Log
        if (!file_exists($container->getParameter('kernel.logs_dir'))) {
            mkdir($container->getParameter('kernel.logs_dir'), 0775, true);
        }
        $definition = new Definition(Log::class,[null]);
        $container->setDefinition('log',$definition);

        //Exception Handler
        $definition=new Definition(ExceptionSubscriber::class,[
            'App\\common\\ErrorController::exception',
            null,//This is the environment and gets set during compilation
            new Reference('log')
        ]);
        if($container->getParameter('kernel.debug')==false) {
            $definition->addTag('kernel.event_subscriber');
        }
        $container->setDefinition('kernel.exception_listener',$definition);

        //Response
        $viewResponseListenerDefinition=new Definition(ViewResponseListener::class);
        $viewResponseListenerDefinition->addTag('kernel.event_listener',['event'=>'kernel.view','method'=>'onKernelView']);
        $container->setDefinition('listener.view_response',$viewResponseListenerDefinition);

        //Database (PDO)
        $definition = new Definition(Database::class,[
            new Reference('config')
        ]);
        $definition->setFactory(array(PDOFactory::class,'createPDO'));
        $container->setDefinition('db',$definition);

        //Session Handler (PDO)
        $pdoSessionHandlerDefinition=new Definition(PdoSessionHandler::class,[
            new Reference('db')
        ]);
        $container->setDefinition('session.pdo_session_handler',$pdoSessionHandlerDefinition);

        //Session Handler (File)
        $fileSessionHandlerDefinition=new Definition(NativeFileSessionHandler::class,[
            '%session_dir%'
        ]);
        $container->setDefinition('session.file_session_handler',$fileSessionHandlerDefinition);

        //Storage Handler
        $nativeSessionStorageDefinition=new Definition(NativeSessionStorage::class,[
            [],
            new Reference('session.session_handler')
        ]);
        $container->setDefinition('session.session_storage',$nativeSessionStorageDefinition);

        //Session
        $sessionDefinition = new Definition(Session::class,[
            new Reference('session.session_storage')
        ]);
        $container->setDefinition('session',$sessionDefinition);

        //Session Listener
        $sessionListenerDefinition = new Definition(SessionListener::class,[
            new Reference('service_container')
        ]);
        $sessionListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('session.session_listener',$sessionListenerDefinition);

        //Save Session Listener
        $saveSessionListenerDefinition=new Definition(SaveSessionListener::class);
        $saveSessionListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('session.save_session_listener',$saveSessionListenerDefinition);

        //Locale Listener
        $localListenerDefinition=new Definition(LocaleListener::class);
        $localListenerDefinition->addTag('kernel.event_listener',['event'=>'kernel.request','method'=>'onKernelRequest']);
        $container->setDefinition('locale.local_listener',$localListenerDefinition);

        //Translator
        $translatorDefinition=new Definition(Language::class,[
            new Reference('request'),
            '%default_locale%'
        ]);
        $translatorDefinition->addMethodCall('load',['%root_dir%']);
        $container->set('locale.translator',$translatorDefinition);

        //Twig Loader
        $twigLoaderDefinition = new Definition(\Twig_Loader_Filesystem::class,[
            'app',
            '%kernel.root_dir%'
        ]);
        $container->setDefinition('twig.loader',$twigLoaderDefinition);

        //Twig Environment
        $twigEnvironmentDefinition = new Definition(Environment::class,[
            new Reference('twig.loader'),
            [
                'cache'=>'%template_cache_dir%',
                'auto_reload'=>'%kernel.debug%'
            ]
        ]);
        $container->setDefinition('twig.environment',$twigEnvironmentDefinition);

        $templateDefinition= new Definition(Template::class,[
            new Reference('twig.environment')
        ]);
        $templateDefinition->addMethodCall('addTemplateDir',['%template_dir%']);
        $container->setDefinition('template',$templateDefinition);

    }
}