<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 22.01.2019
 * Time: 14:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Exception\LogicException;


class AddMiddlewarePass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {

        if (!$container->hasDefinition('security.access.decision_manager')) {
            return;
        }

        $voters = array();
        foreach ($container->findTaggedServiceIds('security.voter') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $voters[$priority][] = new Reference($id);
        }

        krsort($voters);
        $voters = call_user_func_array('array_merge', $voters);

        if (!$voters) {
            throw new LogicException('No security voters found. You need to tag at least one with "security.voter"');
        }

        $container->getDefinition('security.access.decision_manager')->addMethodCall('setVoters', array($voters));

    }
}