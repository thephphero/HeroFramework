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

use Bundles\SecurityBundle\DependencyInjection\Compiler\AddMiddlewarePass;
use Bundles\SecurityBundle\DependencyInjection\Compiler\EntryPointPass;
use Bundles\SecurityBundle\DependencyInjection\Compiler\OverrideAnonymousUserCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;



class SecurityBundle extends Bundle{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        //$extension = $container->getExtension('security');

        $container->addCompilerPass(new OverrideAnonymousUserCompilerPass());
        $container->addCompilerPass(new AddMiddlewarePass());
        $container->addCompilerPass(new EntryPointPass());
    }
}