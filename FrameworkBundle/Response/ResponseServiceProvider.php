<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 22/10/17
 * Time: 20:50
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Bundles\FrameworkBundle\Response;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\FrameworkBundle\Response\Listeners\ViewResponseListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;


class ResponseServiceProvider implements ServiceProviderInterface {

    public function register(ContainerBuilder $container)
    {
        $viewResponseListenerDefinition=new Definition(ViewResponseListener::class);
        $viewResponseListenerDefinition->addTag('kernel.event_listener',['event'=>'kernel.view','method'=>'onKernelView']);

        $container->setDefinition('listener.view_response',$viewResponseListenerDefinition);
    }
}