<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 27/11/16
 * Time: 21:31
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Security;

use Bundles\FrameworkBundle\Security\Authenticators\FormAuthenticator;
use Bundles\FrameworkBundle\Security\Authenticators\TokenAuthenticator;
use Bundles\FrameworkBundle\Security\Authenticators\GuardFormAuthenticator;
use Bundles\FrameworkBundle\Security\Encoders\Sha256PasswordEncoder;
use Bundles\FrameworkBundle\Security\Listeners\AnonymousAuthenticationListener;
use Bundles\FrameworkBundle\Security\Listeners\UserPermissionsExceptionListener;
use Library\Security\Factory\EntryPointFactory;
use Library\Security\Factory\InMemoryUserProviderFactory;
use Library\Security\Factory\SwitchUserListenerFactory;
use Bundles\FrameworkBundle\Security\UserProviders\TokenUserProvider;
use Bundles\FrameworkBundle\Security\UserProviders\UserProvider;
use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\FrameworkBundle\Security\Handlers\UserAuthenticationFailureHandler;
use Bundles\FrameworkBundle\Handlers\AccessDeniedHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Authentication\Provider\RememberMeAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\Provider\AnonymousAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;
use Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;
use Symfony\Component\Security\Http\RememberMe\PersistentTokenBasedRememberMeServices;
use Symfony\Component\Security\Http\RememberMe\TokenBasedRememberMeServices;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Bundles\SecurityBundle\Security\FirewallMap;
use Bundles\SecurityBundle\Security\FirewallContext;
use Bundles\FrameworkBundle\Security\Voters\UserPermissionsVoter;

class SecurityServiceProvider implements ServiceProviderInterface {


    private $requestMatchers = array();

    private $expressions = array();

    private $contextListeners = array();

    private $listenerPositions = array('logout', 'pre_auth', 'guard', 'form', 'http', 'remember_me', 'anonymous');

    private $factories = array();

    private $userProviderFactories = array();

    private $expressionLanguage;

    private $firewalls=[];

    public function __construct()
    {
        foreach ($this->listenerPositions as $position) {
            $this->factories[$position] = array();
        }
    }

    public function register(ContainerBuilder $container){

        $providerKey='';

        //$container->setParameter('security.access_rules',[]);
        $container->setParameter('security.hide_user_not_found',true);
        $container->setParameter('security.encoder.bcrypt.cost',13);

        $container->setParameter('security.role_hierarchy.roles', array());

        //Token Storage
        $tokenStorageDefinition=new Definition(TokenStorage::class);
        $container->setDefinition('security.token_storage',$tokenStorageDefinition);

        //Context Listener
        $contextListenerDefinition=new Definition(ContextListener::class,[
            new Reference('security.token_storage'),
            $userProviders=[],
            $providerKey,
            new Reference('log'),
            new Reference('event_dispatcher')
        ]);
        $container->setDefinition('security.context_listener',$contextListenerDefinition);

        //Trust Resolver
        $trustResolverDefinition = new Definition(AuthenticationTrustResolver::class,[
            'Symfony\Component\Security\Core\Authentication\Token\AnonymousToken',
            'Symfony\Component\Security\Core\Authentication\Token\RememberMeToken'
        ]);
        $container->setDefinition('security.trust_resolver',$trustResolverDefinition);

        //Authenticated voter
        $authenticationVoterDefinition=new Definition(AuthenticatedVoter::class,[
            new Reference('security.trust_resolver')
        ]);
        $authenticationVoterDefinition->addTag('security.voter');
        $container->setDefinition('security.authenticated_voter',$authenticationVoterDefinition);

        //Role Hierarchy
        $roleHierarchyDefinition=new Definition(RoleHierarchy::class,[
            array()
        ]);
        $container->setDefinition('security.role_hierarchy',$roleHierarchyDefinition);

        //Security role Hierarchy voter
        $roleHierarchyVoterDefinition=new Definition(RoleHierarchyVoter::class,[
            new Reference('security.role_hierarchy')
        ]);
        $roleHierarchyVoterDefinition->addTag('security.voter');
        $container->setDefinition('security.role_hierarchy_voter',$roleHierarchyVoterDefinition);

        //User Permissions Voter
        $userPermissionsVoter=new Definition(UserPermissionsVoter::class,[
            new Reference('db'),
        ]);
        $userPermissionsVoter->addTag('security.voter');
        $userPermissionsVoter->setPublic(false);
        $container->setDefinition('security.user_permissions_voter',$userPermissionsVoter);

        //Access Manager
        $accessManagerDefinition=new Definition(AccessDecisionManager::class,[
            array(
                new Reference('security.role_hierarchy_voter'),
                new Reference('security.authenticated_voter')
            )
        ]);
        $container->setDefinition('security.access.decision_manager',$accessManagerDefinition);

        //Encoder BCrypt
        $encoderBcryptDefinition=new Definition(BCryptPasswordEncoder::class,[
            $container->getParameter('security.encoder.bcrypt.cost')
        ]);
        $container->setDefinition('security.encoder.bcrypt',$encoderBcryptDefinition);

        //Encoder Sha256
        $encoderSha256Definition=new Definition(Sha256PasswordEncoder::class);
        $container->setDefinition('security.encoder.sha256',$encoderSha256Definition);

        //Encoder factory
        $encoderFactoryDefinition = new Definition(EncoderFactory::class,[
            array()
        ]);
        $container->setDefinition('security.encoder.factory',$encoderFactoryDefinition);

        //Password Encoder
        $encoderPasswordDefinition=new Definition(UserPasswordEncoder::class,[
            new Reference('security.encoder.factory')
        ]);
        $container->setDefinition('security.encoder.password',$encoderPasswordDefinition);

        //User Checker
        $userCheckerDefinition=new Definition(UserChecker::class);
        $container->setDefinition('security.user_checker',$userCheckerDefinition);

        //Entry points
        $formEntryPointDefinition=new Definition(FormAuthenticationEntryPoint::class,[
            new Reference('http_kernel'),
            new Reference('security.http_utils'),
            new Reference('login'),
            true
        ]);
        $container->setDefinition('security.authentication.form_entry_point',$formEntryPointDefinition);

        //Channel Listener
        $channelListenerDefinition=new Definition(Firewall\ChannelListener::class,[
            new Reference('security.access_map'),
            new Reference('security.retry_authentication_entry_point'),
            new Reference('log'),
            new Reference('security.authentication.form_entry_point'),
        ]);
        $container->setDefinition('security.channel_listener',$channelListenerDefinition);

        //HttpUtils
        $httpUtilsDefinition=new Definition(HttpUtils::class,[
            new Reference('url_generator'),
            new Reference('matcher')
        ]);
        $container->setDefinition('security.http_utils',$httpUtilsDefinition);

        //Security Session
        $sessionStrategyDefinition=new Definition(SessionAuthenticationStrategy::class,[
            SessionAuthenticationStrategy::MIGRATE
        ]);
        $container->setDefinition('security.session_strategy',$sessionStrategyDefinition);

        //Last Error
        $lastErrorDefinition=new Definition(AuthenticationUtils::class,[
            new Reference('request_stack')
        ]);
        $container->setDefinition('security.authentication_utils',$lastErrorDefinition);

        //Success Handler
        $successHandlerDefinition=new Definition(UserAuthenticationSuccessHandler::class,[
            new Reference('security.http_utils'),
            array()
        ]);
        $container->setDefinition('security.authentication.success_handler',$successHandlerDefinition);

        //Failure Handler
        $failureHandlerDefinition=new Definition(UserAuthenticationFailureHandler::class,[
            new Reference('http_kernel'),
            new Reference('security.http_utils'),
            array(),
            new Reference('log')
        ]);
        $container->setDefinition('security.authentication.failure_handler',$failureHandlerDefinition);

        //Access Denied handler
        $accessDeniedHandlerDefinition=new Definition(AccessDeniedHandler::class);
        $container->setDefinition('security.access_denied_handler',$accessDeniedHandlerDefinition);

        //Logout URL generator
        $logoutUrlGeneratorDefinition=new Definition(LogoutUrlGenerator::class,[
            new Reference('request_stack'),
            new Reference('url_generator'),
            new Reference('security.token_storage')
        ]);
        $container->setDefinition('security.logout_url_generator',$logoutUrlGeneratorDefinition);

        //Request Matcher
        $requestMatcherDefinition=new Definition(RequestMatcher::class);
        $container->setDefinition('security.request_matcher',$requestMatcherDefinition);

        //Default User Providers
        $userProviderDefaultDefinition=new Definition(UserProvider::class,[
            new Reference('db'),
            new Reference('project')
        ]);
        $container->setDefinition('security.provider.user',$userProviderDefaultDefinition);


        //Token User Provider
        $tokenUserProviderDefinition=new Definition(TokenUserProvider::class,[
            new Reference('db')
        ]);
        $container->setDefinition('security.provider.token',$tokenUserProviderDefinition);

        //In Memory User Provider
        $inMemoryUserProviderDefinition=new Definition(InMemoryUserProvider::class,[
            array()
        ]);
        $container->setDefinition('security.provider.in_memory',$inMemoryUserProviderDefinition);


        //Remember-Me Provider
        $rememberMeProviderDefinition=new Definition(RememberMeAuthenticationProvider::class,[
            new Reference('security.user_checker'),
            null,
            null
        ]);
        $container->setDefinition('security.authentication.provider.rememberme',$rememberMeProviderDefinition);

        //Retry Authentication Entry POint
        $retryEntryPointDefinition=new Definition(RetryAuthenticationEntryPoint::class,[
            80,
            443
        ]);
        $container->setDefinition('security.retry_authentication_entry_point',$retryEntryPointDefinition);

        //RememberMe Token Service
        $rememeberMeTokenServiceDefinition=new Definition(PersistentTokenBasedRememberMeServices::class,[
            array(),
            new Reference('security.retry_authentication_entry_point'),
            $providerKey,
            array(),
            new Reference('log'),
            null
        ]);
        $container->setDefinition('security.authentication.rememberme.services.persistent',$rememeberMeTokenServiceDefinition);

        //Token Base services (simplehash)
        $tokenBasedRememberMeDefinition=new Definition(TokenBasedRememberMeServices::class,[
            array(),
            null,
            $providerKey,
            array(),
            new Reference('log')
        ]);
        $container->setDefinition('security.authentication.rememberme.services.simplehash',$tokenBasedRememberMeDefinition);

        //Chain User Provider
        $chainUserProviderDefinition=new Definition(ChainUserProvider::class,[
            array()
        ]);
        $container->setDefinition('security.user.provider.chain',$chainUserProviderDefinition);

        //Guard Authenticator Handler
        $guardAuthenticationHandlerDefinition=new Definition(GuardAuthenticatorHandler::class,[
            new Reference('security.token_storage'),
            new Reference('event_dispatcher')
        ]);
        $container->setDefinition('security.authentication.guard_handler',$guardAuthenticationHandlerDefinition);

        //Guard Form Authenticator
        $guardFormAuthenticatorDefinition=new Definition(GuardFormAuthenticator::class,[
            new Reference('router'),
            new Reference('security.encoder.password')
        ]);
        $container->setDefinition('security.guard.form.authenticator',$guardFormAuthenticatorDefinition);

        //Shop Form Authenticator
        $shopFormAuthenticatorDefinition=new Definition(GuardShopAuthenticator::class,[
            new Reference('router'),
            new Reference('security.encoder.password')
        ]);
        $container->setDefinition('security.guard.shop.authenticator',$shopFormAuthenticatorDefinition);


        //API Key Authenticator
        $tokenAuthenticationDefinition=new Definition(TokenAuthenticator::class,[
            new Reference('security.encoder.password')
        ]);
        $container->setDefinition('security.token.authenticator',$tokenAuthenticationDefinition);


        //Guard Auth Provider
        $authenticationProviderGuardDefinition=new Definition(GuardAuthenticationProvider::class,[
            $authenticators=[],
            new Reference('security.user.provider.chain'),
            $providerKey,
            new Reference('security.user_checker')
        ]);
        $container->setDefinition('security.authentication.provider.guard',$authenticationProviderGuardDefinition);

        //Anonymous Auth Provider
        $anonymousProviderDefinition=new Definition(AnonymousAuthenticationProvider::class,[
            $providerKey
        ]);
        $container->setDefinition('security.authentication.provider.anonymous',$anonymousProviderDefinition);

        //DAO Auth Provider
        $authenticationProviderDaoDefinition=new Definition(DaoAuthenticationProvider::class,[
            new Reference('security.user.provider.chain'),
            new Reference('security.user_checker'),
            $providerKey,
            new Reference('security.encoder.factory'),
        ]);
        $container->setDefinition('security.authentication.provider.dao',$authenticationProviderDaoDefinition);

        //Password Authenticator
        $passwordAuthenticatorDefinition=new Definition(PasswordAuthenticator::class);
        $container->setDefinition('security.authentication.form',$passwordAuthenticatorDefinition);

        //Anonymous Authentication Listener
        $anonymousListenerDefinition=new Definition(AnonymousAuthenticationListener::class,[
            new Reference('security.token_storage'),
            $providerKey,
            new Reference('log'),
            new Reference('security.authentication.manager')
        ]);
        $anonymousListenerDefinition->addTag('kernel.event_listener',['event'=>'kernel.request','method'=>'handle']);
        $container->setDefinition('security.authentication.listener.anonymous',$anonymousListenerDefinition);

        //UserPermissions Exception Listener
        $userPermissionsExceptionListenerDefinition=new Definition(UserPermissionsExceptionListener::class,[
            new Reference('service_container')
        ]);
        $userPermissionsExceptionListenerDefinition->addTag('kernel.event_listener',['event'=>'kernel.exception','method'=>'onKernelException']);
        $container->setDefinition('security.authorization.listener.user_permissions',$userPermissionsExceptionListenerDefinition);

        //Authentication Manager
        $authenticationManagerDefinition=new Definition(AuthenticationProviderManager::class,[
            new Reference('security.authentication.provider.dao'),
            new Reference('security.authentication.provider.guard'),
            new Reference('security.authentication.provider.anonymous')
        ]);
        $authenticationManagerDefinition->addMethodCall('setEventDispatcher',[new Reference('event_dispatcher')]);
        $container->setDefinition('security.authentication.manager',$authenticationManagerDefinition);

        //Authorization Checker
        $authorizationCheckerDefinition=new Definition(AuthorizationChecker::class,[
            new Reference('security.token_storage'),
            new Reference('security.authentication.manager'),
            new Reference('security.access.decision_manager')
        ]);
        $container->setDefinition('security.authorization_checker',$authorizationCheckerDefinition);

        //Guard Listener
        $authenticationListenerGuardDefinition=new Definition(GuardAuthenticationListener::class,[
            new Reference('security.authentication.guard_handler'),
            new Reference('security.authentication.manager'),
            $providerKey,
            array(),
            new Reference('log')
        ]);
        $container->setDefinition('security.authentication.listener.guard',$authenticationListenerGuardDefinition);

        //Form Authentication Listener
        $usernamePasswordFormAuthenticationListenerDefiniton=new Definition(Firewall\UsernamePasswordFormAuthenticationListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.authentication.manager'),
            new Reference('security.session_strategy'),
            new Reference('security.http_utils'),
            $providerKey,
            null,
            null,
            array(),
            new Reference('log'),
            new Reference('event_dispatcher'),
        ]);
        $container->setDefinition('security.authentication.listener.form',$usernamePasswordFormAuthenticationListenerDefiniton);

        //Switch User Listener
        $switchUserListenerDefinition=new Definition(Firewall\SwitchUserListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.user.provider.default'),
            new Reference('security.user_checker'),
            $providerKey,
            new Reference('security.access.decision_manager'),
            new Reference('log'),
            null,
            null,
            new Reference('event_dispatcher')
        ]);
        $container->setDefinition('security.authentication.switchuser_listener',$switchUserListenerDefinition);

        //Remember me listener
        $rememberMeListenerDefinition=new Definition(Firewall\RememberMeListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.authentication.rememberme.services.simplehash'),
            new Reference('security.authentication.manager'),
            new Reference('log'),
            new Reference('event_dispatcher'),
            true,
            new Reference('security.session_strategy')
        ]);
        $container->setDefinition('security.authentication.listener.rememberme',$rememberMeListenerDefinition);

        //Access Map
        $accessMapDefinition=new Definition(AccessMap::class);
        $container->setDefinition('security.access_map',$accessMapDefinition);

        //Exception listener
        $exceptionListenerDefinition=new Definition(ExceptionListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.trust_resolver'),
            new Reference('security.http_utils'),
            null,
            null,
            null,
            new Reference('security.access_denied_handler'),
            new Reference('log'),
            null
        ]);
        $container->setDefinition('security.exception.listener',$exceptionListenerDefinition);


        //Logout Success handler
        $logoutSuccessHandlerDefinition=new Definition(PosCustomLogoutSuccessHandler::class,[
            new Reference('security.http_utils'),
            '/'
        ]);
        $container->setDefinition('security.logout.success_handler',$logoutSuccessHandlerDefinition);


        //Session Logout Handler
        $sessionLogoutHandlerDefinition=new Definition(SessionLogoutHandler::class);
        $container->setDefinition('security.logout.handler.session',$sessionLogoutHandlerDefinition);

        //Logout Listener
        $logoutListenerDefinition=new Definition(Firewall\LogoutListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.http_utils'),
            new Reference('security.logout.success_handler'),
            array(),
            new Reference('csrf.token_manager')
        ]);
        $container->setDefinition('security.logout_listener',$logoutListenerDefinition);

        //Logout URL generator
        $logoutUrlGeneratorDefinition=new Definition(LogoutUrlGenerator::class,[
            new Reference('request_stack'),
            new Reference('url_generator'),
            new Reference('security.token_storage')
        ]);
        $container->setDefinition('security.logout_url_generator',$logoutUrlGeneratorDefinition);


        $container->setParameter('security.authentication_providers',array_map(function ($provider) use ($container) {
            return $container->get($provider);
        }, array_unique(array())));


        // generate the build-in authentication factories
        $inMemoryProviderFactoryDefinition=new Definition(InMemoryUserProviderFactory::class);
        $container->setDefinition('security.user.provider.in_memory.factory',$inMemoryProviderFactoryDefinition);

        $switchUserListenerFactoryDefinition=new Definition(SwitchUserListenerFactory::class);
        $container->setDefinition('security.listener.switch_user.factory',$switchUserListenerFactoryDefinition);

        $guardAuthenticatorProviderFactoryDefinition=new Definition(GuardAuthenticationProvider::class);
        $container->setDefinition('security.authentication.provider.guard.factory',$guardAuthenticatorProviderFactoryDefinition);

        //Firewall Map
        $firewallMapDefinition=new Definition(FirewallMap::class,[
            new Reference('service_container'),
            array()
        ]);
        $container->setDefinition('security.firewall.map',$firewallMapDefinition);


        //Firewall Context
        $firewallContextDefinition=new Definition(FirewallContext::class,[
            array(),
            new Reference('security.exception.listener')
        ]);
        $container->setDefinition('security.firewall.context',$firewallContextDefinition);

        // Access Listener
        $accessListenerDefinition=new Definition(AccessListener::class,[
            new Reference('security.token_storage'),
            new Reference('security.access.decision_manager'),
            new Reference('security.access_map'),
            new Reference('security.authentication.manager'),
            new Reference('log')
        ]);
        $container->setDefinition('security.access_listener',$accessListenerDefinition);

        //Form Authenticator
        $formAuthenticatiorDefinition=new Definition(FormAuthenticator::class,[
            new Reference('security.encoder.password')
        ]);
        $container->setDefinition('security.authenticator.form',$formAuthenticatiorDefinition);

        //Token Authenticator
        $tokenAuthenticatorDefinition=new Definition(TokenAuthenticator::class,[
            new Reference('security.encoder.factory')
        ]);
        $container->setDefinition('security.authenticator.token',$tokenAuthenticatorDefinition);

        //Validator
        if ($container->has('validator')) {
            $passwordValidatorDefinition=new Definition(UserPasswordValidator::class,[
                new Reference('security.token_storage'),
                new Reference('security.encoder_factory')
            ]);
            $container->setDefinition('security.validator.user_password_validator',$passwordValidatorDefinition);

            $container->setParameter('validator.validator_service_ids',array_merge($container->getParameter('validator.validator_service_ids'), array('security.validator.user_password' => 'security.validator.user_password_validator')));

        }

        //Firewall
        $firewallDefinition=new Definition(Firewall::class,[
            new Reference('security.firewall.map'),
            new Reference('event_dispatcher')
        ]);
        $firewallDefinition->addTag('kernel.event_listener',array('event'=>'kernel.request','method'=>'onKernelRequest'));
        $container->setDefinition('security.firewall',$firewallDefinition);

        //User
        if ($token=$container->get('security.token_storage')->getToken()) {
            $container->setParameter('user', $token->getUser());
        }


    }

}
