<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 04/06/2017
 * Time: 19:28
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class ContainerAwareControllerResolver extends ControllerResolver {

    protected $container;

    public function __construct(LoggerInterface $logger = null, ContainerInterface $container = null) {

        parent::__construct($logger);

        $this->container = $container;
    }

    protected function instantiateController($class) {



        $new_class = new $class($this->container);

        return $new_class;
    }


}