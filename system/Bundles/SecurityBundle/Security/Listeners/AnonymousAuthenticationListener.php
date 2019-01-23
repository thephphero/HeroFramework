<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 24/09/17
 * Time: 06:02
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\Listeners;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Bundles\SecurityBundle\Security\Repositories\AnonymousUser;

class AnonymousAuthenticationListener implements ListenerInterface{

    private $tokenStorage;
    private $secret;
    private $authenticationManager;
    private $logger;


    public function __construct(TokenStorageInterface $tokenStorage,$secret,LoggerInterface $logger, AuthenticationManagerInterface $authenticationManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->secret = $secret;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
    }

    public function handle(GetResponseEvent $event)
    {

        try {


            $token = $this->tokenStorage->getToken();

            if (!$token) {
                $token = new AnonymousToken($this->secret, new AnonymousUser(), array());
                if (null !== $this->authenticationManager) {
                    $token = $this->authenticationManager->authenticate($token);
                }
                $this->tokenStorage->setToken($token);
            }


            if (null !== $this->logger) {
                //$this->logger->info('Populated the TokenStorage with an anonymous Token.');
            }
            return;

        } catch (AuthenticationException $failed) {

            if (null !== $this->logger) {
                // $this->logger->info('Anonymous authentication failed.', array('exception' => $failed));
            }
        }



    }
}