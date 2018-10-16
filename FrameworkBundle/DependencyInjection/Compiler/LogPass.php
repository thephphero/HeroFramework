<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 06/09/18
 * Time: 14:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LogPass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {
        $definition =  $container->findDefinition('log');

        $filename=$container->getParameter('kernel.logs_dir').DIRECTORY_SEPARATOR.'log_project_'.$container->get('request')->getHttpHost().'.log';
        $definition->replaceArgument(0,$filename);
    }
}