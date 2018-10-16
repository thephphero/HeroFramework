<?php
/**
 * Created by PhpStorm.
 * User: celsoluiz81
 * Date: 01/07/17
 * Time: 11:16
 */

namespace Bundles\FrameworkBundle\Log;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\FrameworkBundle\Log\Log;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LogServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {

        //Create folder if doesn't exist
        if (!file_exists($container->getParameter('kernel.logs_dir'))) {
            mkdir($container->getParameter('kernel.logs_dir'), 0775, true);
        }

        //$filename = $container->getParameter('log_dir').DIRECTORY_SEPARATOR.'log_project_'.$container->get('request')->getHost().'.log';

        $definition = new Definition(Log::class,[null]);
        $container->setDefinition('log',$definition);


    }
}