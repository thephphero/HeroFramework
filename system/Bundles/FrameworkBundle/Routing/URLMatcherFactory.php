<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 31.10.2017
 * Time: 15:41
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class URLMatcherFactory{

    private  $router;

    private $context;

    private $booted=false;

    private $matcher;



    public function __construct(Router $router, RequestContext $context)
    {
        $this->router=$router;

        $this->context=$context;


    }

    public function create(){

        if(!$this->booted){

            $this->matcher=new UrlMatcher($this->router->getRouteCollection(),$this->context);
            $this->booted=true;
        }

        return $this->matcher;
    }


}