<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/01/17
 * Time: 22:49
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Security\Handlers;


use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
class UserAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler{

    protected $app = null;

    public function __construct(HttpKernelInterface $httpKernel, HttpUtils $httpUtils, array $options = array(), LoggerInterface $logger = null)
    {

        parent::__construct($httpKernel,$httpUtils,$options,$logger);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        return $this->httpUtils->createRedirectResponse($request, $this->options['failure_path']);
    }
}