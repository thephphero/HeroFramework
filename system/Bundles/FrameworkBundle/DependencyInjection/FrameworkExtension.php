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


namespace Bundles\FrameworkBundle\DependencyInjection;

use Bundles\FrameworkBundle\Config\ConfigServiceProvider;
use Bundles\FrameworkBundle\Database\DatabaseServiceProvider;
use Bundles\FrameworkBundle\Doctrine\DoctrineServiceProvider;
use Bundles\FrameworkBundle\Exception\ExceptionServiceProvider;
use Bundles\FrameworkBundle\Log\LogServiceProvider;
use Bundles\FrameworkBundle\Request\RequestServiceProvider;
use Bundles\FrameworkBundle\Routing\RoutingServiceProvider;
use Bundles\FrameworkBundle\Session\SessionServiceProvider;
use Bundles\FrameworkBundle\HttpKernel\HttpKernelServiceProvider;
use Library\Pagination\PaginationServiceProvider;
use Library\Response\ResponseServiceProvider;
use Library\Routing\Routers\Router;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\FrameworkBundle\Interfaces\EventListenerProviderInterface;

class FrameworkExtension extends Extension{

    protected $services=[
        RequestServiceProvider::class,
        RoutingServiceProvider::class,
        HttpKernelServiceProvider::class,
        ConfigServiceProvider::class,
        LogServiceProvider::class,
        ExceptionServiceProvider::class,
        DatabaseServiceProvider::class,
        //DoctrineServiceProvider::class,
        SessionServiceProvider::class,
        \Library\Csrf\CsrfServiceProvider::class,
        \Library\Template\TemplateServiceProvider::class,
        \Library\Response\ResponseServiceProvider::class
    ];

    public function load(array $configs, ContainerBuilder $container)
    {

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));

        foreach ($this->services as $service){

            if(class_exists($service)){

                $class=new $service();

                if ($class instanceof ServiceProviderInterface) {

                    $class->register($container);
                }

                if ($class instanceof EventListenerProviderInterface) {

                    $class->subscribe($container, $container->get('event_dispatcher'));
                }
            }
        }

        $this->registerContainerVariables($configs[0],$container,$loader);
        $this->registerSessionConfiguration($configs[0],$container,$loader);
        $this->registerDefaultControllerConfiguration($configs[0],$container,$loader);

        $this->addClassesToCompile([
            'Symfony\\Component\\HttpKernel\\Controller\\ControllerResolver',
            '\\Library\\Loader\\Loader',
        ]);

    }

    private function registerContainerVariables(array $config, ContainerBuilder $container, XmlFileLoader $loader){
        //Set environment variables
        $container->setParameter('kernel.timezone',$config['kernel.timezone']);
        $container->setParameter('request_listener.http_port', 80);
        $container->setParameter('request_listener.https_port', 445);
        $container->setParameter('session_dir',$config['session_dir']);
        $container->setParameter('root_dir',$container->getParameter('kernel.root_dir'));

        $container->setParameter('base_url',getenv('BASE_URL'));
        $container->setParameter('DB_HOST',getenv('DB_HOST'));
        $container->setParameter('DB_USER',getenv('DB_USER'));
        $container->setParameter('DB_PASS',getenv('DB_PASSWORD'));
        $container->setParameter('DB_SCHEMA',getenv('DB_NAME'));
        $container->setParameter('DB_PREFIX',getenv('DB_PREFIX'));
    }

    private function registerSessionConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader){

        $sessionDefinition=$container->getDefinition('session.session_storage');

        switch($config['session_driver']){
            case 'database':

                $sessionDefinition->replaceArgument(1,new Reference('session.pdo_session_handler'));
                break;

            case 'file':
                $sessionDefinition->replaceArgument(1,new Reference('session.file_session_handler'));
                break;

            default:
                $sessionDefinition->replaceArgument(1,new Reference('session.pdo_session_handler'));
                break;
        }

    }



    private function registerDefaultControllerConfiguration(array $config, ContainerBuilder $container, XmlFileLoader $loader){
        if(isset($config['default_routes'])){
            foreach($config['default_routes'] as $key=>$route){
                $container->setParameter($key,$route);
            }
        }
    }


}