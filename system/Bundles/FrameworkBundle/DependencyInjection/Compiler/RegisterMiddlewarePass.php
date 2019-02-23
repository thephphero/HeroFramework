<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/02/19
 * Time: 22:02
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterMiddlewarePass implements CompilerPassInterface{

    protected $container;
    private $useConfig; //Store middleware in configuration or container

    public function __construct($useConfig=true)
    {
        $this->useConfig = $useConfig;
    }

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        $contents = require $container->getParameter('kernel.root_dir').'/config/app.php';
        if(isset($contents['middleware'])){
            $this->registerMiddleware($contents['middleware']);
        }

    }

    protected function registerMiddleware($contents=[])
    {

        if($this->useConfig){
            $this->container->get('config')->set('middleware',$contents);
        }
        else{
            foreach ($contents as $name=>$class) {

                $middlewareDefinition = new Definition($class);
                $middlewareDefinition->addTag('middleware');
                $this->container->setDefinition('middleware.'.$name,$middlewareDefinition);

            }
        }
    }
}