<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 04/12/17
 * Time: 00:17
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Security\Listeners;

use Bundles\FrameworkBundle\Security\Exception\InsufficientPermissionsException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class UserPermissionsExceptionListener{

    public function __construct()
    {

    }

    public function onKernelException(GetResponseForExceptionEvent $event){
        $exception= $event->getException();

        if(!($exception instanceof InsufficientPermissionsException)){
            return;
        }

        $response=new RedirectResponse('/common/error/error401');
        $event->setResponse($response);

    }
}