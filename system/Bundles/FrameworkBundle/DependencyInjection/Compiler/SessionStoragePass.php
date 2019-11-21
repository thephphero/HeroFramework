<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29/05/18
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundles\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Reference;

class SessionStoragePass implements CompilerPassInterface {

    protected $options=[
        'cookie_lifetime'=>2592000,//1 month
        'gc_probability'=>1,
        'gc_divisor'=>1000,
        'gc_maxlifetime'=>2592000
    ];

    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('framework');
        $container->setParameter('session.options',$this->options);
    }
}