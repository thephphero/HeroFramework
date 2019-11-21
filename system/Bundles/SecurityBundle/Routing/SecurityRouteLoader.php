<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 18.10.2019
 * Time: 14:23
 */
namespace Bundles\SecurityBundle\Routing;

use Bundles\FrameworkBundle\Routing\Route;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Loader\DependencyInjection\ServiceRouterLoader;
use Symfony\Component\Routing\RouteCollection;

class SecurityRouteLoader extends Loader {
    private $loaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        $route = new Route(['GET'], '/login',['controller'=>'\\Bundles\\FrameworkBundle\\Controller\\AuthenticationController::login'],'login');

        $routes->add('authentication.login', $route);

        $this->isLoaded = true;
var_dump('exc');
        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'service' === $type;
    }
}

