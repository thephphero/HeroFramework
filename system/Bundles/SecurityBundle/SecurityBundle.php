<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 21.01.2019
 * Time: 15:20
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle;

use Bundles\SecurityBundle\DependencyInjection\Compiler\EntryPointPass;
use Bundles\SecurityBundle\DependencyInjection\Compiler\OverrideAnonymousUserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Bundles\SecurityBundle\DependencyInjection\Compiler\AddMiddlewarePass;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\HttpBasicFactory;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\RememberMeFactory;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\RemoteUserFactory;
use Bundles\SecurityBundle\DependencyInjection\Security\UserProvider\InMemoryFactory;
use Bundles\SecurityBundle\DependencyInjection\Security\Factory\GuardAuthenticationFactory;


class SecurityBundle extends Bundle{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');

        $extension->addSecurityListenerFactory(new FormLoginFactory());
        $extension->addSecurityListenerFactory(new HttpBasicFactory());
        $extension->addSecurityListenerFactory(new RememberMeFactory());
        $extension->addSecurityListenerFactory(new RemoteUserFactory());
        $extension->addSecurityListenerFactory(new GuardAuthenticationFactory());
        $extension->addUserProviderFactory(new InMemoryFactory());

        //$container->addCompilerPass(new OverrideAnonymousUserCompilerPass());
        $container->addCompilerPass(new AddSecurityVotersPass());
    }
}