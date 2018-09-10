<?php
namespace  Bundles\FrameworkBundle\Config;

use Symfony\Component\DependencyInjection\Container;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\FrameworkBundle\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


class ConfigServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        $definition = new Definition(Config::class,[
                new Reference('service_container') ]
        );

        $container->setDefinition('config',$definition);


    }
}