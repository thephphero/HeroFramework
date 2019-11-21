<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/10/2019
 * Time: 15:11
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddConsoleCommandPass implements CompilerPassInterface{

    const commandTag = 'console.command';

    public function __construct()
    {

    }

    public function process(ContainerBuilder $container)
    {
        $commandServices = $container->findTaggedServiceIds(self::commandTag, true);
        $lazyCommandMap = [];
        $lazyCommandRefs = [];
        $serviceIds = [];

        foreach ($commandServices as $id => $tags) {

            $definition = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($definition->getClass());

            if (isset($tags[0]['command'])) {
                $commandName = $tags[0]['command'];
            } else {
                if (!$r = $container->getReflectionClass($class)) {
                    throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(Command::class)) {
                    throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s" must be a subclass of "%s".', $id, $this->commandTag, Command::class));
                }
                $commandName = $class::getDefaultName();
            }
            if (null === $commandName) {
                if (!$definition->isPublic() || $definition->isPrivate()) {
                    $commandId = 'console.command.public_alias.'.$id;
                    $container->setAlias($commandId, $id)->setPublic(true);
                    $id = $commandId;
                }
                $serviceIds[] = $id;
                continue;
            }
        }

        $container->setParameter('console.command.ids', $serviceIds);
    }
}