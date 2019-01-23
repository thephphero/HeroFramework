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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class FrameworkExtension extends Extension{

    public function load(array $configs, ContainerBuilder $container)
    {

        //Service Provider
        $serviceProvider = new FrameworkBundleServiceProvider();
        $serviceProvider->register($container);

        $this->registerContainerVariables($configs[0],$container);
        $this->registerSessionConfiguration($configs[0],$container);
        $this->registerDefaultControllerConfiguration($configs[0],$container);

    }

    private function registerContainerVariables(array $config, ContainerBuilder $container){

        //Set environment variables
        $container->setParameter('kernel.timezone',$config['kernel.timezone']);
        $container->setParameter('request_listener.http_port', 80);
        $container->setParameter('request_listener.https_port', 445);
        $container->setParameter('session_dir',$config['session_dir']);
        $container->setParameter('root_dir',$container->getParameter('kernel.root_dir'));
        $container->setParameter('default_locale',$config['default_locale']);
        $container->setParameter('template_cache_dir',$config['template_cache_dir']);
        $container->setParameter('template_dir',$config['template_dir']);

        $container->setParameter('base_url',getenv('BASE_URL'));
        $container->setParameter('DB_HOST',getenv('DB_HOST'));
        $container->setParameter('DB_USER',getenv('DB_USER'));
        $container->setParameter('DB_PASS',getenv('DB_PASSWORD'));
        $container->setParameter('DB_SCHEMA',getenv('DB_NAME'));
        $container->setParameter('DB_PREFIX',getenv('DB_PREFIX'));
    }

    private function registerSessionConfiguration(array $config, ContainerBuilder $container){

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

    private function registerDefaultControllerConfiguration(array $config, ContainerBuilder $container){
        if(isset($config['default_routes'])){
            foreach($config['default_routes'] as $key=>$route){
                $container->setParameter($key,$route);
            }
        }
    }


}