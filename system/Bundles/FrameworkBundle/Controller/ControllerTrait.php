<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 19/11/2017
 * Time: 19:28
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait ControllerTrait{

    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     *
     * @final since version 3.4
     */
    protected function generateUrl($route, array $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }


    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @throws \LogicException
     *
     * @final since version 3.4
     */
    protected function isGranted($attributes, $subject = null)
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }
        return $this->container->get('security.authorization_checker')->isGranted($attributes, $subject);
    }

    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     *
     * @final since version 3.4
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        if (!$this->isGranted($attributes, $object)) {
            throw $this->createAccessDeniedException($message);
        }
    }

    /**
     * Throws an exception unless the user has the necessary permissions to access a property.
     * Different than denyAccessUnlessGranted, because its used only by the permissions system and not to
     * deny access to unlogged users.
     *
     * @param $attributes
     * @param null $object
     * @param string $message
     */
    protected function checkUserPermissions($attributes, $object = null, $message = 'Access Denied.', $showMessge = true){


        if (!$this->isGranted($attributes, $object)) {

            if($showMessge)
                throw $this->createInsufficientPermissionsException($message);
            else
                return false;


        } else {

            return true;
        }
    }

    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @final since version 3.4
     */
    protected function createAccessDeniedException($message = 'Access Denied.', \Exception $previous = null)
    {
        return new AccessDeniedException($message, $previous);
    }

    /**
     * Returns an InsufficientPermissionsException.
     * @param string $message
     * @param \Exception|null $previous
     * @return InsufficientPermissionsException
     */
    protected function createInsufficientPermissionsException($message = 'Access Denied.', \Exception $previous = null){
        return new InsufficientPermissionsException($message,$previous);
    }
}