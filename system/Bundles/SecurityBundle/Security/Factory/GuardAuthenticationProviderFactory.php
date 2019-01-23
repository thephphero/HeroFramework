<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 15.07.17
 * Time: 14:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\Security\Factory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class GuardAuthenticationProviderFactory implements SecurityFactoryInterface {

    public function create(Container $container, $id, $config, $userProvider, $defaultEntryPoint){

        $authenticatorIds = $config['authenticators'];

        //$userProvider = isset($config['users'])?$config['users']:'security.user.provider.default';


        $authenticatorReferences = array();

        foreach ($authenticatorIds as $authenticatorId) {
            $authenticatorReferences[] = new Reference($authenticatorId);
        }

        // configure the GuardAuthenticationFactory to have the dynamic constructor arguments
        $providerId = 'security.authentication.provider.guard.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('security.authentication.provider.guard'))
            ->replaceArgument(0, $authenticatorReferences)
            ->replaceArgument(1, new Reference($userProvider))
            ->replaceArgument(2, $id)
            ->replaceArgument(3, new Reference('security.user_checker.'.$id))

        ;

        // listener
        $listenerId = 'security.authentication.listener.guard.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('security.authentication.listener.guard'));
        $listener->replaceArgument(2, $id);
        $listener->replaceArgument(3, $authenticatorReferences);

        // determine the entryPointId to use
        $entryPointId = $this->determineEntryPoint($defaultEntryPoint, $config);

        // this is always injected - then the listener decides if it should be used
        $container
            ->getDefinition($listenerId)
            ->addTag('security.remember_me_aware', array('id' => $id, 'provider' => $userProvider));
        return array($providerId, $listenerId, $entryPointId);

    }

    public function getPosition()
    {
        return 'pre_auth';
    }


    public function getKey()
    {
        return 'guard';
    }

    protected function getListenerId()
    {
        return 'security.authentication.listener.guard';
    }


    private function determineEntryPoint($defaultEntryPointId, array $config)
    {

        if ($defaultEntryPointId) {
            // explode if they've configured the entry_point, but there is already one
            if (isset($config['entry_point'])) {
                throw new \LogicException(sprintf(
                    'The guard authentication provider cannot use the "%s" entry_point because another entry point is already configured by another provider! Either remove the other provider or move the entry_point configuration as a root key under your firewall (i.e. at the same level as "guard").',
                    $config['entry_point']
                ));
            }
            return $defaultEntryPointId;
        }
        if (isset($config['entry_point'])) {
            // if it's configured explicitly, use it!
            return $config['entry_point'];
        }
        $authenticatorIds = $config['authenticators'];
        if (count($authenticatorIds) == 1) {
            // if there is only one authenticator, use that as the entry point
            return array_shift($authenticatorIds);
        }
        // we have multiple entry points - we must ask them to configure one
        throw new \LogicException(sprintf(
            'Because you have multiple guard configurators, you need to set the "guard.entry_point" key to one of you configurators (%s)',
            implode(', ', $authenticatorIds)
        ));
    }


}