<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 11.09.2017
 * Time: 17:44
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Routing;

use Core\Container;
use Library\Config\Config;

use Bundles\FrameworkBundle\Request\Request;
use Bundles\FrameworkBundle\Routing\RouteNameCreator;
use Symfony\Component\Config\Loader\Loader;
use Bundles\FrameworkBundle\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Closure;
use Bundles\FrameworkBundle\Routing\RouteGroup;
use Bundles\FrameworkBundle\Support\Traits\Macroable;




class RouteLoader extends Loader{

    use Macroable {
        __call as macroCall;
    }

    private $file;

    private $loaded = false;

    protected $groupStack=[];

    protected $routes;

    protected $patterns=[];

    protected $context;

    protected $config;

    protected $request;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];


    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    protected $namespace = 'App';

    public function __construct(Request $request , Config $config)
    {
        $this->request=$request;

        $this->config=$config;

        $this->file = $config->get('kernel.root_dir').DIRECTORY_SEPARATOR.'app/routes.php';

    }

    public function get($uri, $action = null)
    {

        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    public function post($uri, $action = null)
    {
        return $this->addRoute(['POST', 'HEAD'], $uri, $action);
    }

    public function put($uri, $action = null)
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch($uri, $action = null)
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete($uri, $action = null)
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function options($uri, $action = null)
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    public function any($uri, $action = null)
    {
        $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

        return $this->addRoute($verbs, $uri, $action);

    }

    public function group(array $attributes, Closure $callback)
    {
        $this->updateGroupStack($attributes);
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {

            $attributes = RouteGroup::merge($attributes, end($this->groupStack));
        }
        $this->groupStack[] = $attributes;
    }


    protected function addRoute($methods, $uri, $action)
    {
        $route = $this->createRoute($methods, $uri, $action);

        $routeName = RouteNameCreator::createRouteName($methods,$this->prefix($uri));
        $this->routes->add($routeName,$route);

        return $route;
    }


    protected function createRoute($methods, $uri, $action)
    {
        // If the route is routing to a controller we will parse the route action into
        // an acceptable array format before registering it and creating this route
        // instance itself. We need to build the Closure that will call this out.
        if ($this->actionReferencesController($action)) {

            $action = $this->convertToControllerAction($action);
        }

        $route = $this->newRoute($methods, $this->prefix($uri), $action);

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
        if ($this->hasGroupStack()) {

            $this->mergeGroupAttributesIntoRoute($route);
        }

        $this->addWhereClausesToRoute($route);

        return $route;
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param  array  $action
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if (! $action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }
        return false;
    }


    /**
     * Add a controller based route action to the action array.
     *
     * @param  array|string  $action
     * @return array
     */
    protected function convertToControllerAction($action)
    {

        if (is_string($action)) {
            $action = ['uses' => $action];
        }
        // Here we'll merge any group "uses" statement if necessary so that the action
        // has the proper clause for this property. Then we can simply set the name
        // of the controller on the action and return the action array for usage.
        if (! empty($this->groupStack)) {
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }
        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
        $action['controller'] = $action['uses'];

        return $action;
    }


    /**
     * Prepend the last group namespace onto the use clause.
     *
     * @param  string  $class
     * @return string
     */
    protected function prependGroupNamespace($class)
    {
        $group = end($this->groupStack);
        return isset($group['namespace']) && strpos($class, '\\') !== 0
            ? $group['namespace'].'\\'.$class : $class;
    }


    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }


    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    /**
     * Get a route parameter for the current route.
     *
     * @param  string  $key
     * @param  string  $default
     * @return mixed
     */
    public function input($key, $default = null)
    {
        return $this->current()->parameter($key, $default);
    }


    /**
     * Get the request currently being dispatched.
     *
     * @return \Library\Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Library\Routing\Route
     */
    public function getCurrentRoute()
    {
        return $this->current();
    }

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Library\Routing\Route
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRoutes(){

        return $this->routes;
    }

    /**
     * Set the route collection instance.
     *
     * @param  \Library\Routing\RouteCollection  $routes
     * @return void
     */
    public function setRoutes(RouteCollection $routes)
    {
        foreach ($routes as $route) {
            $route->setRouter($this)->setContainer($this->container);
        }
        $this->routes = $routes;
        $this->container->instance('routes', $this->routes);
    }
    /**
     * Prefix the given URI with the last prefix.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }
    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    public function getLastGroupPrefix()
    {
        if (! empty($this->groupStack)) {
            $last = end($this->groupStack);

            return isset($last['prefix']) ? $last['prefix'] : '';
        }

        return '';
    }

    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Library\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {

        return (new Route($methods, $uri, $action));
    }

    /**
     * Merge the group stack with the controller action.
     *
     * @param  \Library\Routing\Route  $route
     * @return void
     */
    protected function mergeGroupAttributesIntoRoute($route)
    {
        $route->setAction($this->mergeWithLastGroup($route->getAction()));
    }

    /**
     * Add the necessary where clauses to the route based on its initial registration.
     *
     * @param  \Library\Routing\Route  $route
     * @return \Library\Routing\Route
     */
    protected function addWhereClausesToRoute($route)
    {
        $route->where(array_merge(
            $this->patterns, isset($route->getAction()['where']) ? $route->getAction()['where'] : []
        ));
        return $route;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param  array  $new
     * @return array
     */
    public function mergeWithLastGroup($new)
    {

        return RouteGroup::merge($new, end($this->groupStack));
    }



    /**
     * Gather the middleware for the given route.
     *
     * @param  \Library\Routing\Route  $route
     * @return array
     */
    public function gatherRouteMiddlewares(Route $route)
    {
        return Collection::make($route->middleware())->map(function ($name) {
            return Collection::make($this->resolveMiddlewareClassName($name));
        })
            ->flatten()->all();
    }

    /**
     * Resolve the middleware name to a class name(s) preserving passed parameters.
     *
     * @param  string  $name
     * @return string|array
     */
    public function resolveMiddlewareClassName($name)
    {
        $map = $this->middleware;

        // If the middleware is the name of a middleware group, we will return the array
        // of middlewares that belong to the group. This allows developers to group a
        // set of middleware under single keys that can be conveniently referenced.
        if (isset($this->middlewareGroups[$name])) {
            return $this->parseMiddlewareGroup($name);
            // When the middleware is simply a Closure, we will return this Closure instance
            // directly so that Closures can be registered as middleware inline, which is
            // convenient on occasions when the developers are experimenting with them.
        } elseif (isset($map[$name]) && $map[$name] instanceof Closure) {
            return $map[$name];
            // Finally, when the middleware is simply a string mapped to a class name the
            // middleware name will get parsed into the full class name and parameters
            // which may be run using the Pipeline which accepts this string format.
        } else {
            list($name, $parameters) = array_pad(explode(':', $name, 2), 2, null);

            return (isset($map[$name]) ? $map[$name] : $name).
                ($parameters !== null ? ':'.$parameters : '');
        }
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param  string  $name
     * @return array
     */
    protected function parseMiddlewareGroup($name)
    {
        $results = [];

        foreach ($this->middlewareGroups[$name] as $middleware) {
            // If the middleware is another middleware group we will pull in the group and
            // merge its middleware into the results. This allows groups to conveniently
            // reference other groups without needing to repeat all their middlewares.
            if (isset($this->middlewareGroups[$middleware])) {
                $results = array_merge(
                    $results, $this->parseMiddlewareGroup($middleware)
                );

                continue;
            }

            list($middleware, $parameters) = array_pad(
                explode(':', $middleware, 2), 2, null
            );

            // If this middleware is actually a route middleware, we will extract the full
            // class name out of the middleware list now. Then we'll add the parameters
            // back onto this class' name so the pipeline will properly extract them.
            if (isset($this->middleware[$middleware])) {
                $middleware = $this->middleware[$middleware];
            }

            $results[] = $middleware.($parameters ? ':'.$parameters : '');
        }

        return $results;
    }

    public function load($resource=null, $type = null)
    {

        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "file" route loader twice');
        }

        $this->routes = new RouteCollection();

        if ($this->file instanceof Closure) {
            $this->file($this);
        } else {
            $router = $this;

            $router->group(['namespace' => $this->namespace], function ($router) use($resource) {

                if(!is_file($this->file)){
                    throw new FileNotFoundException($this->file);
                }
                require $this->file;
            });

        }

        $this->loaded = true;

        return $this->routes;

    }

    public function supports($resource, $type = null)
    {
        return 'file'===$type;
    }
}