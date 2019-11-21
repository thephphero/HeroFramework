<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 22.01.2019
 * Time: 14:37
 */

namespace Bundles\SecurityBundle\DependencyInjection\Compiler;

use Bundles\FrameworkBundle\Routing\Route;
use Bundles\SecurityBundle\Controller\AuthenticationController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class EntryPointPass implements CompilerPassInterface{

    protected $routeCollection;


    public function process(ContainerBuilder $container)
    {


    }
}