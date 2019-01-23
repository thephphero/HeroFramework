<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29.07.2017
 * Time: 13:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Session;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SaveSessionListener implements EventSubscriberInterface{

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $session = $event->getRequest()->getSession();
        if ($session && $session->isStarted()) {
            $session->save();
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            // low priority but higher than StreamedResponseListener
            KernelEvents::RESPONSE => array(array('onKernelResponse', -1000)),
        );
    }
}