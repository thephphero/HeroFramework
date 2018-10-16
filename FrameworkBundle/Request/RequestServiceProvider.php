<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 30/07/17
 * Time: 12:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Request;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RequestServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {

        $requestFactoryDefinition=new Definition(RequestFactory::class);
        $container->setDefinition('request.factory',$requestFactoryDefinition);

        $requestDefinition=new Definition(Request::class);


        $request=$container->setDefinition('request',$requestDefinition);
        $request->setFactory([new Reference('request.factory'),'create']);


    }
}