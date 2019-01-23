<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 30/07/2017
 * Time: 09:46
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundles\FrameworkBundle\Interfaces;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


interface EventListenerProviderInterface{

    public function subscribe(ContainerBuilder $container,EventDispatcherInterface $dispatcher);
}