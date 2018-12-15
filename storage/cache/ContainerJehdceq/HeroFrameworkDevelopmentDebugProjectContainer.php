<?php

namespace ContainerJehdceq;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class HeroFrameworkDevelopmentDebugProjectContainer extends Container
{
    private $buildParameters;
    private $containerDir;
    private $parameters;
    private $targetDirs = array();

    public function __construct(array $buildParameters = array(), $containerDir = __DIR__)
    {
        $dir = $this->targetDirs[0] = \dirname($containerDir);
        for ($i = 1; $i <= 4; ++$i) {
            $this->targetDirs[$i] = $dir = \dirname($dir);
        }
        $this->buildParameters = $buildParameters;
        $this->containerDir = $containerDir;
        $this->parameters = $this->getDefaultParameters();

        $this->services = array();
        $this->fileMap = array(
            'config' => 'getConfigService.php',
            'context' => 'getContextService.php',
            'controller_resolver' => 'getControllerResolverService.php',
            'db' => 'getDbService.php',
            'event_dispatcher' => 'getEventDispatcherService.php',
            'http_kernel' => 'getHttpKernelService.php',
            'kernel.exception_listener' => 'getKernel_ExceptionListenerService.php',
            'listener.middleware' => 'getListener_MiddlewareService.php',
            'listener.router' => 'getListener_RouterService.php',
            'log' => 'getLogService.php',
            'matcher' => 'getMatcherService.php',
            'request' => 'getRequestService.php',
            'request.factory' => 'getRequest_FactoryService.php',
            'request_stack' => 'getRequestStackService.php',
            'response.listener' => 'getResponse_ListenerService.php',
            'router' => 'getRouterService.php',
            'routing.matcher_factory' => 'getRouting_MatcherFactoryService.php',
            'routing.route_collection' => 'getRouting_RouteCollectionService.php',
            'routing.route_loader' => 'getRouting_RouteLoaderService.php',
            'session' => 'getSessionService.php',
            'session.file_session_handler' => 'getSession_FileSessionHandlerService.php',
            'session.pdo_session_handler' => 'getSession_PdoSessionHandlerService.php',
            'session.save_session_listener' => 'getSession_SaveSessionListenerService.php',
            'session.session_listener' => 'getSession_SessionListenerService.php',
            'session.session_storage' => 'getSession_SessionStorageService.php',
            'url_generator' => 'getUrlGeneratorService.php',
        );
        $this->privates = array(
            'config' => true,
            'context' => true,
            'controller_resolver' => true,
            'db' => true,
            'event_dispatcher' => true,
            'http_kernel' => true,
            'kernel.exception_listener' => true,
            'listener.middleware' => true,
            'listener.router' => true,
            'log' => true,
            'matcher' => true,
            'request' => true,
            'request.factory' => true,
            'request_stack' => true,
            'response.listener' => true,
            'router' => true,
            'routing.matcher_factory' => true,
            'routing.route_collection' => true,
            'routing.route_loader' => true,
            'session' => true,
            'session.file_session_handler' => true,
            'session.pdo_session_handler' => true,
            'session.save_session_listener' => true,
            'session.session_listener' => true,
            'session.session_storage' => true,
            'url_generator' => true,
        );

        $this->aliases = array();
    }

    public function getRemovedIds()
    {
        return require $this->containerDir.\DIRECTORY_SEPARATOR.'removed-ids.php';
    }

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

    protected function load($file, $lazyLoad = true)
    {
        return require $this->containerDir.\DIRECTORY_SEPARATOR.$file;
    }

    public function getParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
            $name = $this->normalizeParameterName($name);

            if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
                throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
            }
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    public function hasParameter($name)
    {
        $name = (string) $name;
        if (isset($this->buildParameters[$name])) {
            return true;
        }
        $name = $this->normalizeParameterName($name);

        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters);
    }

    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

    private $loadedDynamicParameters = array(
        'kernel.root_dir' => false,
        'kernel.project_dir' => false,
        'kernel.cache_dir' => false,
        'kernel.logs_dir' => false,
        'kernel.bundles_metadata' => false,
        'root_dir' => false,
        'debug.container.dump' => false,
    );
    private $dynamicParameters = array();

    /**
     * Computes a dynamic parameter.
     *
     * @param string The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter($name)
    {
        switch ($name) {
            case 'kernel.root_dir': $value = $this->targetDirs[2]; break;
            case 'kernel.project_dir': $value = $this->targetDirs[2]; break;
            case 'kernel.cache_dir': $value = $this->targetDirs[0]; break;
            case 'kernel.logs_dir': $value = ($this->targetDirs[1].'/logs'); break;
            case 'kernel.bundles_metadata': $value = array(
                'FrameworkBundle' => array(
                    'parent' => NULL,
                    'path' => ($this->targetDirs[2].'/system/Bundles/FrameworkBundle'),
                    'namespace' => 'Bundles\\FrameworkBundle',
                ),
            ); break;
            case 'root_dir': $value = $this->targetDirs[2]; break;
            case 'debug.container.dump': $value = ($this->targetDirs[0].'/HeroFrameworkDevelopmentDebugProjectContainer.xml'); break;
            default: throw new InvalidArgumentException(sprintf('The dynamic parameter "%s" must be defined.', $name));
        }
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
    }

    private $normalizedParameterNames = array(
        'db_host' => 'DB_HOST',
        'db_user' => 'DB_USER',
        'db_pass' => 'DB_PASS',
        'db_schema' => 'DB_SCHEMA',
        'db_prefix' => 'DB_PREFIX',
    );

    private function normalizeParameterName($name)
    {
        if (isset($this->normalizedParameterNames[$normalizedName = strtolower($name)]) || isset($this->parameters[$normalizedName]) || array_key_exists($normalizedName, $this->parameters)) {
            $normalizedName = isset($this->normalizedParameterNames[$normalizedName]) ? $this->normalizedParameterNames[$normalizedName] : $normalizedName;
            if ((string) $name !== $normalizedName) {
                @trigger_error(sprintf('Parameter names will be made case sensitive in Symfony 4.0. Using "%s" instead of "%s" is deprecated since Symfony 3.4.', $name, $normalizedName), E_USER_DEPRECATED);
            }
        } else {
            $normalizedName = $this->normalizedParameterNames[$normalizedName] = (string) $name;
        }

        return $normalizedName;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'kernel.environment' => 'development',
            'kernel.debug' => true,
            'kernel.name' => 'HeroFramework',
            'kernel.bundles' => array(
                'FrameworkBundle' => 'Bundles\\FrameworkBundle\\FrameworkBundle',
            ),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'HeroFrameworkDevelopmentDebugProjectContainer',
            'kernel.timezone' => 'Europe/Berlin',
            'request_listener.http_port' => 80,
            'request_listener.https_port' => 445,
            'session_dir' => 'storage/sessions',
            'base_url' => 'thephphero.local',
            'DB_HOST' => 'localhost',
            'DB_USER' => 'root',
            'DB_PASS' => 'hb5x92kk,',
            'DB_SCHEMA' => 'noshpos',
            'DB_PREFIX' => false,
            'session.options' => array(
                'cookie_lifetime' => 2592000,
                'gc_probability' => 1,
                'gc_divisor' => 1000,
                'gc_maxlifetime' => 2592000,
            ),
        );
    }
}
