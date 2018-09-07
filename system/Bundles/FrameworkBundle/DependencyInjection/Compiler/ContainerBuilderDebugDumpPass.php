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
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;
use Symfony\Component\Config\ConfigCache;

class ContainerBuilderDebugDumpPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container)
    {
        $debug = $container->getParameter('kernel.debug');

        if ($debug) {
            $dumpfile=$container->getParameter('kernel.cache_dir').DIRECTORY_SEPARATOR.$container->getParameter('kernel.container_class');
            $container->setParameter('debug.container.dump', $dumpfile.'.xml');
            $cache = new ConfigCache($container->getParameter('debug.container.dump'), true);
            $cache->write((new XmlDumper($container))->dump(), $container->getResources());
            if (!$cache->isFresh()) {
                $cache->write((new XmlDumper($container))->dump(), $container->getResources());
            }
        }
    }
}