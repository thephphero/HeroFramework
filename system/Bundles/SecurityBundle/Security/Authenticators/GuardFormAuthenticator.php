<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 15/07/17
 * Time: 22:23
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\Authenticators;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class GuardFormAuthenticator extends AbstractGuardAuthenticator {

    private $router;

    protected $encoderFactory;

    public function __construct(RouterInterface $router, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->router=$router;

        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * if getCredentials returns null, this guard will be ignored and Symfony will continue down the chain
     * of other guards (in Symf v2.8); https://symfony.com/doc/2.8/security/guard_authentication.html
     *
     * @param Request $request
     * @return array|mixed|null|void
     */
    public function getCredentials(Request $request){

        if ($request->getPathInfo() != '/login_check' || !$request->isMethod('POST')) {
            return;
        }

        $username = $request->request->get('_username', null);
        $password = $request->request->get('_password', null);

        if(!$username || !$password)
            return null;

        return array(
            'username' => $username,
            'password' => $password
        );
    }


    public function getUser($credentials, UserProviderInterface $userProvider){
        $username = $credentials['username'];

        if(null === $username) {
            return;
        }

        return $userProvider->loadUserByUsername($username);
    }


    public function checkCredentials($credentials, UserInterface $user){

        $plainPassword = $credentials['password'];

        $result= $this->passwordEncoder->isPasswordValid(
            $user,
            $plainPassword,
            $user->getSalt()
        );

        return $result;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

        $targetPath = $request->post->get('_target_path','/');
        return RedirectResponse::create($targetPath);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception->getMessage());

        $targetPath = $request->request->get('_target_path', null);

        if($targetPath) {
            $redirectUrl = '/login?_target_path='.$targetPath.'&message='.urlencode($exception->getMessage());
        }
        else{
            $redirectUrl = '/login?message='.urlencode($exception->getMessage());
        }

        return RedirectResponse::create($redirectUrl);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url='/login';
        return RedirectResponse::create($url);
    }

    public function supportsRememberMe()
    {
        return false;
    }

}