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

namespace Bundles\SecurityBundle\DependencyInjection;

use Bundles\SecurityBundle\Security\SecurityBundleServiceProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class SecurityExtension extends Extension{

    public function load(array $configs, ContainerBuilder $container)
    {
        //Service Provider
        $serviceProvider = new SecurityBundleServiceProvider();
        $serviceProvider->register($container);
    }
}