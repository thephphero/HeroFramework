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

use Core\Container;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\EventListener\SessionListener as AbstractSessionListener;

class SessionListener extends AbstractSessionListener {

    private $container;

    public function __construct(ContainerInterface $container)
    {

        parent::__construct($container);
        $this->container=$container;
    }

    protected function getSession()
    {
        if (!$this->container->has('session')) {

            return;
        }

        return $this->container->get('session');
    }
}