<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 24/02/17
 * Time: 23:12
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Routing;

use Symfony\Component\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Bundles\FrameworkBundle\Routing\RouteAction;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Component\Routing\RouterInterface;

class Route extends SymfonyRoute{

    /**
     * The URI pattern the route responds to.
     *
     * @var string
     */
    public $uri;
    /**
     * The HTTP methods the route responds to.
     *
     * @var array
     */
    public $methods;
    /**
     * The route action array.
     *
     * @var array
     */
    public $action;
    /**
     * The controller instance.
     *
     * @var mixed
     */
    public $controller;
    /**
     * The default values for the route.
     *
     * @var array
     */
    public $defaults = [];
    /**
     * The regular expression requirements.
     *
     * @var array
     */
    public $wheres = [];
    /**
     * The array of matched parameters.
     *
     * @var array
     */
    public $parameters;
    /**
     * The parameter names for the route.
     *
     * @var array|null
     */
    public $parameterNames;
    /**
     * The computed gathered middleware.
     *
     * @var array|null
     */
    public $computedMiddleware;
    /**
     * The compiled version of the route.
     *
     * @var \Symfony\Component\Routing\CompiledRoute
     */
    public $compiled;

    /**
     * The validators used by the routes.
     *
     * @var array
     */
    public static $validators;

    public function __construct($methods=array(),$uri,$action){

        $this->uri = $uri;
        $this->methods = (array) $methods;
        $this->action = $this->parseAction($action);

        if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
            $this->methods[] = 'HEAD';
        }

        if (isset($this->action['prefix'])) {
            $this->prefix($this->action['prefix']);
        }


        $this->setMethods($this->methods);

        $this->setPath($uri);

        $this->setDefault('_controller',$action['controller']);



        $this->setOptions(['compiler_class'=>'Symfony\\Component\\Routing\\RouteCompiler']);

    }

    /**
     * Set the action array for the route.
     *
     * @param  array  $action
     * @return $this
     */
    public function setAction(array $parameters)
    {

        $this->parameters = $parameters;

        foreach($parameters as $key=>$value){

            $key=strtolower($key);

            switch ($key){

                case'host':
                    $this->setHost($value);
                    break;
                case'defaults':
                    $this->setDefaults($value);
                case'requirements':
                    if(is_array($value) && !empty($value)){
                        foreach ($value as $name=>$requirement){
                            $this->setRequirement($name,$requirement);
                        }

                    }
                    break;
            }
        }
        return $this;
    }

    public function getAction(){

        return $this->action;
    }

    /**
     * Set a regular expression requirement on the route.
     *
     * @param  array|string  $name
     * @param  string  $expression
     * @return $this
     */
    public function where($name, $expression = null)
    {
        foreach ($this->parseWhere($name, $expression) as $name => $expression) {
            $this->wheres[$name] = $expression;

        }

        $this->addRequirements($this->wheres);
        return $this;
    }


    /**
     * Parse arguments to the where method into an array.
     *
     * @param  array|string  $name
     * @param  string  $expression
     * @return array
     */
    protected function parseWhere($name, $expression)
    {
        return is_array($name) ? $name : [$name => $expression];
    }


    protected function parseAction($action)
    {
        return RouteAction::parse($this->uri, $action);
    }

    /**
     * Get the prefix of the route instance.
     *
     * @return string
     */
    public function getPrefix()
    {
        return isset($this->action['prefix']) ? $this->action['prefix'] : null;
    }
    /**
     * Add a prefix to the route URI.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $uri = rtrim($prefix, '/').'/'.ltrim($this->uri, '/');
        $this->uri = trim($uri, '/');
        return $this;
    }
    /**
     * Get or set the middlewares attached to the route.
     *
     * @param  array|string|null $middleware
     * @return $this|array
     */
    public function middleware($middleware = null)
    {
        if (is_null($middleware)) {
            return (array) Arr::get($this->action, 'middleware', []);
        }

        if (is_string($middleware)) {
            $middleware = [$middleware];
        }

        $this->action['middleware'] = array_merge(
            (array) Arr::get($this->action, 'middleware', []), $middleware
        );

        return $this;
    }


}