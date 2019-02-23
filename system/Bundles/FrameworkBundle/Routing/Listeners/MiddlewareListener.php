<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 07/09/2018
 * Time: 11:10
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Routing\Listeners;

use Core\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MiddlewareListener implements  EventSubscriberInterface{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    /**
     * Runs before filters.
     *
     * @param GetResponseEvent $event The event to handle
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $routeName = $request->attributes->get('_route');

        if (!$route = $this->container->get('routing.route_collection')->get($routeName)) {
            return;
        }
        var_dump($route->getOption('_before_middlewares'));
        foreach ((array) $route->getOption('_before_middlewares') as $callback) {

            $ret = call_user_func($this->container->get('callback_resolver')->resolveCallback($callback), $request, $this->container);
            if ($ret instanceof Response) {
                $event->setResponse($ret);
                return;
            } elseif (null !== $ret) {
                throw new \RuntimeException(sprintf('A before middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }

    /**
     * Runs after filters.
     *
     * @param FilterResponseEvent $event The event to handle
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        if (!$route = $this->container->get('routing.route_collection')->get($routeName)) {
            return;
        }
        foreach ((array) $route->getOption('_after_middlewares') as $callback) {
            $response = call_user_func($this->container->get('callback_resolver')->resolveCallback($callback), $request, $event->getResponse(), $this->app);
            if ($response instanceof Response) {
                $event->setResponse($response);
            } elseif (null !== $response) {
                throw new \RuntimeException(sprintf('An after middleware for route "%s" returned an invalid response value. Must return null or an instance of Response.', $routeName));
            }
        }
    }
    public static function getSubscribedEvents()
    {
        return array(
            // this must be executed after the late events defined with before() (and their priority is -512)
            KernelEvents::REQUEST => array('onKernelRequest', -34),
            KernelEvents::RESPONSE => array('onKernelResponse', 128),
        );
    }

}