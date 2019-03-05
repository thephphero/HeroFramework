<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 21.01.2019
 * Time: 15:20
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\DependencyInjection;

use Bundles\SecurityBundle\DependencyInjection\SecurityBundleServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Bundles\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Interfaces\ServiceProviderInterface;
use Interfaces\EventListenerProviderInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
class SecurityExtension extends Extension{

    private $requestMatchers = array();
    private $expressions = array();
    private $contextListeners = array();
    private $listenerPositions = array('logout', 'pre_auth', 'guard', 'form', 'http', 'remember_me', 'anonymous');
    private $factories = array();
    private $userProviderFactories = array();
    private $expressionLanguage;

    public function __construct()
    {
        foreach ($this->listenerPositions as $position) {
            $this->factories[$position] = array();
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {

        if (!array_filter($configs)) {
            return;
        }

        //Service Provider
        $serviceProvider = new SecurityBundleServiceProvider();
        $serviceProvider->register($container);


        $mainConfig = $this->getConfiguration($configs, $container);
        //$config = $this->processConfiguration($configs[0], $configs);
        $config = $configs[0];


        if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
            $container->removeDefinition('security.expression_language');
            $container->removeDefinition('security.access.expression_voter');
        }

        // set some global scalars
        $container->setParameter('security.access.denied_url', $config['access_denied_url']);
        $container->setParameter('security.authentication.manager.erase_credentials', $config['erase_credentials']);
        $container->setParameter('security.authentication.session_strategy.strategy', $config['session_fixation_strategy']);
        $container
            ->getDefinition('security.access.decision_manager')
            ->addArgument($config['access_decision_manager']['strategy'])
            ->addArgument($config['access_decision_manager']['allow_if_all_abstain'])
            ->addArgument($config['access_decision_manager']['allow_if_equal_granted_denied'])
        ;
        $container->setParameter('security.access.always_authenticate_before_granting', $config['always_authenticate_before_granting']);
        $container->setParameter('security.authentication.hide_user_not_found', $config['hide_user_not_found']);

        $this->createFirewalls($config, $container);
        $this->createAuthorization($config, $container);
        $this->createRoleHierarchy($config, $container);

        if ($config['encoders']) {
            $this->createEncoders($config['encoders'], $container);
        }

        // load ACL
        if (isset($config['acl'])) {
            $this->aclLoad($config['acl'], $container);
        }

        // add some required classes for compilation
        $this->addClassesToCompile(array(
            'Symfony\Component\Security\Http\Firewall',
            'Symfony\Component\Security\Core\User\UserProviderInterface',
            'Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager',
            'Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage',
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManager',
            'Symfony\Component\Security\Core\Authorization\AuthorizationChecker',
            'Symfony\Component\Security\Core\Authorization\Voter\VoterInterface',
            'Bundles\SecurityBundle\Security\FirewallMap',
            'Bundles\SecurityBundle\Security\FirewallContext',
            'Symfony\Component\HttpFoundation\RequestMatcher',
        ));
    }

    private function createRoleHierarchy($config, ContainerBuilder $container)
    {
        if (!isset($config['role_hierarchy']) || 0 === count($config['role_hierarchy'])) {
            $container->removeDefinition('security.role_hierarchy_voter');
            return;
        }

        //Role Hierarchy
        $container->getDefinition('security.role_hierarchy')
            ->replaceArgument(0,$config['role_hierarchy']);


        $container->removeDefinition('security.access.simple_role_voter');
    }


    private function createAuthorization($config, ContainerBuilder $container)
    {
        if (!isset($config['access_control'])) {
            return;
        }

        foreach ($config['access_control'] as $access) {

            $allow_if = empty($access['allow_if'])?:null;

            $requires_channel=empty($access['requires_channel'])?null:$access['requires_channel'];

            $matcher = $this->createRequestMatcher(
                $container,
                isset($access['path'])?$access['path']:null,
                isset($access['host'])?$access['host']:null,
                isset($access['methods'])?$access['methods']:null,
                isset($access['ips'])?$access['ips']:null
            );

            $attributes=[];

            if(isset($access['roles'])){
                $attributes = $access['roles'];
            }

            if (isset($access['allow_if'])) {
                $attributes[] = $this->createExpression($container,$allow_if );
            }
            $container->getDefinition('security.access_map')->addMethodCall('add', array($matcher, $attributes, $requires_channel));
        }
    }


    private function createFirewalls($config, ContainerBuilder $container)
    {

        if (!isset($config['firewalls'])) {
            return;
        }
        $firewalls = $config['firewalls'];
        $providerIds = $this->createUserProviders($config, $container);

        // make the ContextListener aware of the configured user providers
        $definition = $container->getDefinition('security.context_listener');
        $arguments = $definition->getArguments();
        $userProviders = array();

        foreach ($providerIds as $userProviderId) {
            $userProviders[] = new Reference($userProviderId);
        }

        $arguments[1] = $userProviders;
        $definition->setArguments($arguments);

        // load firewall map
        $mapDef = $container->getDefinition('security.firewall.map');
        $map = $authenticationProviders = array();

        foreach ($firewalls as $name => $firewall) {

            list($matcher, $listeners, $exceptionListener) = $this->createFirewall($container, $name, $firewall, $authenticationProviders, $providerIds);
            $contextId = 'security.firewall.map.context.'.$name;
            $context = $container->setDefinition($contextId, new DefinitionDecorator('security.firewall.context'));

            $context->replaceArgument(0, $listeners)->replaceArgument(1, $exceptionListener);

            $map[$contextId] = $matcher;
        }

        $mapDef->replaceArgument(1, $map);

        // add authentication providers to authentication manager
        $authenticationProviders = array_map(function ($id) {
            return new Reference($id);
        }, array_values(array_unique($authenticationProviders)));
        $container
            ->getDefinition('security.authentication.manager')
            ->replaceArgument(0, $authenticationProviders);
    }


    private function createFirewall(ContainerBuilder $container, $id, $firewall, &$authenticationProviders, $providerIds)
    {

        // Matcher
        $matcher = null;
        if (isset($firewall['request_matcher'])) {
            $matcher = new Reference($firewall['request_matcher']);
        } elseif (isset($firewall['pattern']) || isset($firewall['host'])) {
            $pattern = isset($firewall['pattern']) ? $firewall['pattern'] : null;
            $host = isset($firewall['host']) ? $firewall['host'] : null;
            $methods = isset($firewall['methods']) ? $firewall['methods'] : array();
            $matcher = $this->createRequestMatcher($container, $pattern, $host, $methods);
        }

        // Stateless
        $stateless = isset($firewall['stateless']) ? (bool) $firewall['stateless'] : false;

        // Security disabled?
        $security = isset($firewall['security']) ? (bool) $firewall['security'] : true;

        if (false === isset($security)) {
            return array($matcher, array(), null);
        }

        // Provider id (take the first registered provider if none defined)
        if (isset($firewall['provider'])) {
            $defaultProvider = $this->getUserProviderId($firewall['provider']);
        } else {
            $defaultProvider = reset($providerIds);
        }

        // Register listeners
        $listeners = array();

        // Channel listener
        $listeners[] = new Reference('security.channel_listener');

        // Context serializer listener
        if (false===$stateless) {
            $contextKey = $id;
            if (isset($firewall['context'])) {
                $contextKey = $firewall['context'];
            }
            $listeners[] = new Reference($this->createContextListener($container, $contextKey));
        }

        // Logout listener
        if (isset($firewall['logout'])) {

            $listenerId = 'security.logout_listener.'.$id;

            $firewall['logout']['csrf_parameter']=isset($firewall['logout']['csrf_parameter'])?$firewall['logout']['csrf_parameter']:null;
            $firewall['logout']['csrf_token_id']=isset($firewall['logout']['csrf_token_id'])?$firewall['logout']['csrf_token_id']:null;
            $firewall['logout']['path']=isset($firewall['logout']['path'])?$firewall['logout']['path']:null;

            $listener = $container->setDefinition($listenerId, new DefinitionDecorator('security.logout_listener'));

            $listener->replaceArgument(3, array(
                'csrf_parameter' => $firewall['logout']['csrf_parameter'],
                'csrf_token_id' => $firewall['logout']['csrf_token_id'],
                'logout_path' => $firewall['logout']['path'],
            ));

            $listeners[] = new Reference($listenerId);

            // add logout success handler
            if (isset($firewall['logout']['success_handler'])) {
                $logoutSuccessHandlerId = $firewall['logout']['success_handler'];
            } else {
                $logoutSuccessHandlerId = 'security.logout.success_handler.'.$id;
                $logoutSuccessHandler = $container->setDefinition($logoutSuccessHandlerId, new DefinitionDecorator('security.logout.success_handler'));
                $logoutSuccessHandler->replaceArgument(1, $firewall['logout']['target']);
            }

            $listener->replaceArgument(2, new Reference($logoutSuccessHandlerId));

            // add CSRF provider
            if (isset($firewall['logout']['csrf_token_generator'])) {
                $listener->addArgument(new Reference($firewall['logout']['csrf_token_generator']));
            }

            // add session logout handler
            if (true === empty($firewall['logout']['invalidate_session']) && false === $stateless) {
                $listener->addMethodCall('addHandler', array(new Reference('security.logout.handler.session')));
            }

            // add cookie logout handler
            if(!empty($firewall['logout']['delete_cookies'])) {
                if (count($firewall['logout']['delete_cookies']) > 0) {
                    $cookieHandlerId = 'security.logout.handler.cookie_clearing.' . $id;
                    $cookieHandler = $container->setDefinition($cookieHandlerId, new DefinitionDecorator('security.logout.handler.cookie_clearing'));
                    $cookieHandler->addArgument($firewall['logout']['delete_cookies']);
                    $listener->addMethodCall('addHandler', array(new Reference($cookieHandlerId)));
                }
            }

            // add custom handlers
            $handlers = !empty($firewall['logout']['handlers'])?$firewall['logout']['handlers']:array();

            foreach ($handlers as $handlerId) {
                $listener->addMethodCall('addHandler', array(new Reference($handlerId)));
            }

            // register with LogoutUrlGenerator
            $container->getDefinition('security.logout_url_generator')
                ->addMethodCall('registerListener', array(
                    $id,
                    $firewall['logout']['path'],
                    $firewall['logout']['csrf_token_id'],
                    $firewall['logout']['csrf_parameter'],
                    isset($firewall['logout']['csrf_token_generator']) ? new Reference($firewall['logout']['csrf_token_generator']) : null,
                ));
        }

        // Determine default entry point
        $configuredEntryPoint = $this->createEntryPoint($firewall);

        // Authentication listeners
        list($authListeners, $defaultEntryPoint) = $this->createAuthenticationListeners($container, $id, $firewall, $authenticationProviders, $defaultProvider, $configuredEntryPoint);
        $listeners = array_merge($listeners, $authListeners);

        // Switch user listener
        if (isset($firewall['switch_user'])) {
            $listeners[] = new Reference($this->createSwitchUserListener($container, $id, $firewall['switch_user'], $defaultProvider));
        }

        // Access listener
        $listeners[] = new Reference('security.access_listener');

        // Exception listener
        $exceptionListener = new Reference($this->createExceptionListener($container, $firewall, $id, $configuredEntryPoint ?: $defaultEntryPoint, $stateless));

        if(isset($firewall['user_checker'])){
            $container->setAlias(new Alias('security.user_checker.'.$id, false), $firewall['user_checker']);
        }
        else{
            $container->setAlias(new Alias('security.user_checker.'.$id, false), 'security.user_checker');
        }

        return array($matcher, $listeners, $exceptionListener);
    }

    private function createEntryPoint($firewall){


        if(isset($firewall['entry_point']) && $firewall['entry_point']!='security.authentication.form_entry_point'){
            $entry_point=$firewall['entry_point'];

        }
        else{
            $entry_point='security.authentication.form_entry_point';
        }

        return $entry_point;

    }

    private function createContextListener($container, $contextKey)
    {
        if (isset($this->contextListeners[$contextKey])) {
            return $this->contextListeners[$contextKey];
        }

        $listenerId = 'security.context_listener.'.count($this->contextListeners);
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('security.context_listener'));
        $listener->replaceArgument(2, $contextKey);

        return $this->contextListeners[$contextKey] = $listenerId;
    }


    private function createAuthenticationListeners($container, $id, $firewall, &$authenticationProviders, $defaultProvider, $defaultEntryPoint)
    {
        $listeners = array();
        $hasListeners = false;

        foreach ($this->listenerPositions as $position) {
            foreach ($this->factories[$position] as $factory) {

                $key = str_replace('-', '_', $factory->getKey());

                if (isset($firewall[$key])) {

                    $userProvider = isset($firewall[$key]['provider']) ? $this->getUserProviderId($firewall[$key]['provider']) : $defaultProvider;
                    list($provider, $listenerId, $defaultEntryPoint) = $factory->create($container, $id, $firewall[$key], $userProvider, $defaultEntryPoint);
                    $listeners[] = new Reference($listenerId);

                    $authenticationProviders[] = $provider;
                    $hasListeners = true;
                }
            }
        }

        // Anonymous
        if (!empty($firewall['anonymous'])) {

            $listenerId = 'security.authentication.listener.anonymous.'.$id;

            $container
                ->setDefinition($listenerId, new DefinitionDecorator('security.authentication.listener.anonymous'))
                ->replaceArgument(1, $firewall['anonymous']['secret'])
            ;
            $listeners[] = new Reference($listenerId);
            $providerId = 'security.authentication.provider.anonymous.'.$id;

            $container
                ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.anonymous'))
                ->replaceArgument(0, $firewall['anonymous']['secret'])
            ;
            $authenticationProviders[] = $providerId;
            $hasListeners = true;
        }

        if (false === $hasListeners) {
            throw new InvalidConfigurationException(sprintf('No authentication listener registered for firewall "%s".', $id));
        }

        return array($listeners, $defaultEntryPoint);
    }


    private function createEncoders($encoders, ContainerBuilder $container)
    {
        $encoderMap = array();

        foreach ($encoders as $class => $encoder) {

            $encoderMap[$class] = $this->createEncoder($encoder, $container);
        }

        $container->getDefinition('security.encoder.factory')->replaceArgument(0,$encoderMap);

    }


    private function createEncoder($config, ContainerBuilder $container)
    {

        // a custom encoder service
        if (isset($config['id'])) {
            return new Reference($config['id']);
        }
        // plaintext encoder
        if ('plaintext' === $config['algorithm']) {
            $arguments = array($config['ignore_case']);
            return array(
                'class' => new Reference('security.encoder.plain.class'),
                'arguments' => $arguments,
            );
        }
        // pbkdf2 encoder
        if ('pbkdf2' === $config['algorithm']) {
            return array(
                'class' => new Reference('security.encoder.pbkdf2.class'),
                'arguments' => array(
                    $config['hash_algorithm'],
                    $config['encode_as_base64'],
                    $config['iterations'],
                    $config['key_length'],
                ),
            );
        }
        // bcrypt encoder
        if ('bcrypt' === $config['algorithm']) {

            return array(
                'class' => new Reference('security.encoder.bcrypt'),
                'arguments' => array($config['cost']),
            );
        }

        // sha256 encoder
        if ('sha256' === $config['algorithm']) {

            return array(
                'class' => new Reference('security.encoder.sha256'),
                'arguments' => array(),
            );
        }


        // message digest encoder
        return array(
            'class' => new Reference('security.encoder.digest.class'),
            'arguments' => array(
                $config['algorithm'],
                $config['encode_as_base64'],
                $config['iterations'],
            ),
        );
    }


    // Parses user providers and returns an array of their ids
    private function createUserProviders($config, ContainerBuilder $container)
    {
        $providerIds = array();

        foreach ($config['providers'] as $name => $provider) {

            $id = $this->createUserDaoProvider($name, $provider, $container);
            $providerIds[] = $id;
        }

        return $providerIds;
    }


    private function createUserDaoProvider($name, $provider, ContainerBuilder $container)
    {
        $id=$name;

        $name = $this->getUserProviderId($name);

        // Doctrine Entity and In-memory DAO provider are managed by factories
        foreach ($this->userProviderFactories as $factory) {
            $key = str_replace('-', '_', $factory->getKey());
            if ($key==$id) {

                $factory->create($container, $name, $provider);
                return $name;
            }
        }

        // Existing DAO service provider
        if (isset($provider['id'])) {

            $container->setAlias($name, new Alias($provider['id'], false));

            return $provider['id'];
        }

        // Chain provider
        if (isset($provider['chain'])) {

            $providers = array();
            foreach ($provider['chain']['providers'] as $providerName) {

                $providers[] = new Reference($this->getUserProviderId($providerName));
            }

            $container->setDefinition($name, new DefinitionDecorator('security.user.provider.chain'))
                ->replaceArgument(0,$providers);
            return $name;


        }

        throw new InvalidConfigurationException(sprintf('Unable to create definition for "%s" user provider', $name));
    }


    private function getUserProviderId($name)
    {
        return 'security.user.provider.'.strtolower($name);
    }


    private function createExceptionListener($container, $config, $id, $defaultEntryPoint, $stateless)
    {

        $exceptionListenerId = 'security.exception.listener.'.$id;
        $listener = $container->setDefinition($exceptionListenerId, new DefinitionDecorator('security.exception.listener'));
        $listener->replaceArgument(3, $id);
        $listener->replaceArgument(4, null === $defaultEntryPoint ? null : new Reference($defaultEntryPoint));
        $listener->replaceArgument(8, $stateless);

        // access denied handler setup
        if (isset($config['access_denied_handler'])) {

            $listener->replaceArgument(6, new Reference($config['access_denied_handler']));
        } elseif (isset($config['access_denied_url'])) {

            $listener->replaceArgument(5, $config['access_denied_url']);
        }

        return $exceptionListenerId;
    }


    private function createSwitchUserListener($container, $id, $config, $defaultProvider)
    {
        $userProvider = isset($config['provider']) ? $this->getUserProviderId($config['provider']) : $defaultProvider;

        $switchUserListenerId = 'security.authentication.switchuser_listener.'.$id;
        $listener = $container->setDefinition($switchUserListenerId, new DefinitionDecorator('security.authentication.switchuser_listener'));
        $listener->replaceArgument(1, new Reference($userProvider));
        $listener->replaceArgument(2, new Reference('security.user_checker.'.$id));
        $listener->replaceArgument(3, $id);
        $listener->replaceArgument(6, isset($config['parameter']) ? $config['parameter'] : '_switch_user');
        $listener->replaceArgument(7, isset($options['role']) ? $options['role'] : 'ROLE_ALLOWED_TO_SWITCH');
        return $switchUserListenerId;
    }


    private function createExpression($container, $expression)
    {
        if (isset($this->expressions[$id = 'security.expression.'.sha1($expression)])) {
            return $this->expressions[$id];
        }
        $container
            ->register($id, 'Symfony\Component\ExpressionLanguage\SerializedParsedExpression')
            ->setPublic(false)
            ->addArgument($expression)
            ->addArgument(serialize($this->getExpressionLanguage()->parse($expression, array('token', 'user', 'object', 'roles', 'request', 'trust_resolver'))->getNodes()))
        ;
        return $this->expressions[$id] = new Reference($id);
    }


    private function createRequestMatcher($container, $path = null, $host = null, $methods = array(), $ip = null, array $attributes = array())
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));

        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        if (isset($this->requestMatchers[$id])) {

            return $this->requestMatchers[$id];
        }

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container->register($id, RequestMatcher::class)
            ->setPublic(false)
            ->setArguments($arguments);


        return $this->requestMatchers[$id]=new Reference($id);
    }


    public function addSecurityListenerFactory(SecurityFactoryInterface $factory)
    {
        $this->factories[$factory->getPosition()][] = $factory;
    }


    public function addUserProviderFactory(UserProviderFactoryInterface $factory)
    {
        $this->userProviderFactories[] = $factory;
    }


    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        // first assemble the factories
        return new MainConfiguration($this->factories, $this->userProviderFactories);
    }


    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $this->expressionLanguage = new ExpressionLanguage();
        }
        return $this->expressionLanguage;
    }
}