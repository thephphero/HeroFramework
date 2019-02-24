<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 24/02/19
 * Time: 14:29
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CallbackResolver{

    const SERVICE_PATTERN = "/[A-Za-z0-9\._\-]+:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";


    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function isValid($name)
    {
        //return is_string($name) && (preg_match(static::SERVICE_PATTERN, $name) || $this->container->has($name));
        return is_string($name) || $this->container->has($name);
    }

    public function convertCallback($callback)
    {

        $callback = new \ReflectionClass($callback);

        if (!$callback->isInstantiable()) {
            throw new \InvalidArgumentException(sprintf('Middleware "%s" is not callable.', $callback->getName()));
        }
        $class=$callback->newInstance();
        return $class;
    }

    public function resolveCallback($name)
    {
        return $this->isValid($name) ? $this->convertCallback($name) : $name;
    }

}