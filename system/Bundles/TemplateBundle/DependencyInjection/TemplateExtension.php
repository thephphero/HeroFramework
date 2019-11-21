<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 27/02/19
 * Time: 14:42
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\TemplateBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TemplateExtension extends Extension{

    public function load(array $configs, ContainerBuilder $container)
    {
        //Service Provider
        $serviceProvider = new TemplateServiceProvider();
        $serviceProvider->register($container);

        $this->registerTemplateDir($configs[0],$container);

    }

    private function registerTemplateDir(array $config, ContainerBuilder $container){


        $templateDir = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.$config['template_dir'];
        if(!file_exists($templateDir)){
            mkdir($templateDir,0775, true);

        }
        $container->setParameter('template_dir',$config['template_dir']);
        $container->setParameter('template_cache_dir',$config['template_cache_dir']);
    }

}



