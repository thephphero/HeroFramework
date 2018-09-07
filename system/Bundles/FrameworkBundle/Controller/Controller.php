<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 20/03/17
 * Time: 19:28
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Controller;


use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
class Controller  implements ContainerAwareInterface {

    use ContainerAwareTrait;
    use ControllerTrait;

    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns the current incoming request
     * @return mixed
     */
    protected function request(){
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Redirects user to previous page
     * @return RedirectResponse
     */
    protected function previous(){
        $referer = $this->container->get('request_stack')->getCurrentRequest()->headers->get('referer');
        return $this->redirect($referer);
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array  $path       An array of path parameters
     * @param array  $query      An array of query parameters
     *
     * @return Response A Response instance
     */
    protected function forward($controller, array $path = array(), array $query = array())
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $path['_forwarded'] = $request->attributes;
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, null, $path);
        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param mixed $data    The response data
     * @param int   $status  The status code to use for the Response
     * @param array $headers Array of extra headers to add
     * @param array $context Context to pass to serializer when using serializer component
     *
     * @return JsonResponse
     */
    protected function json($data, $status = 200, $headers = array(), $context = array())
    {
        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($data, 'json', array_merge(array(
                'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
            ), $context));
            return new JsonResponse($json, $status, $headers, true);
        }
        return new JsonResponse($data, $status, $headers);
    }



    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string          $message  A message
     * @param \Exception|null $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    protected function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Get a user from the Security Token Storage.
     *
     * @return mixed
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The Security is not registered in your application.');
        }
        if (null === $token = $this->container->get('security.token_storage')->getToken()) {

            return;
        }
        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }
        return $user;
    }

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id The service id
     *
     * @return bool true if the service id is defined, false otherwise
     */
    protected function has($id)
    {
        return $this->container->has($id);
    }
    /**
     * Gets a container service by its id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }
    /**
     * Gets a container configuration parameter by its name.
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }
    /**
     * Checks the validity of a CSRF token.
     *
     * @param string $id    The id used when generating the token
     * @param string $token The actual token sent with the request that should be validated
     *
     * @return bool
     */
    protected function isCsrfTokenValid($id, $token)
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            throw new \LogicException('CSRF protection is not enabled in your application.');
        }
        return $this->container->get('security.csrf.token_manager')->isTokenValid(new CsrfToken($id, $token));
    }

    public function __get($key) {
        return ($this->container->has($key)) ? $this->container->get($key) :$this->{$key};
    }
}
