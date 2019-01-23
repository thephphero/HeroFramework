<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/07/17
 * Time: 21:31
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\Handlers;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class UserAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{

    public function __construct(HttpUtils $httpUtils, array $options)
    {
        parent::__construct($httpUtils, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }
}
