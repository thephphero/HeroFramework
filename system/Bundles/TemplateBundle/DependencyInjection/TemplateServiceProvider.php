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

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Environment;
use Symfony\Component\DependencyInjection\Definition;
use Bundles\TemplateBundle\Template\Template;
use Symfony\Component\DependencyInjection\Reference;
class TemplateServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        //Twig Loader
        $twigLoaderDefinition = new Definition(\Twig_Loader_Filesystem::class,[
            'app/Controller',
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