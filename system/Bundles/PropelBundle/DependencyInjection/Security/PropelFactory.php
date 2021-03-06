<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 11.03.2019
 * Time: 13:29
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\PropelBundle\DependencyInjection\Security;

class PropelFactory{

    use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
    use Symfony\Component\Config\Definition\Builder\NodeDefinition;
    use Symfony\Component\DependencyInjection\ChildDefinition;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    /**
     * PropelFactory creates services for Propel user provider.
     *
     * @author William Durand <william.durand1@gmail.com>
     */
class PropelFactory implements UserProviderFactoryInterface
{
    private $key;
    private $providerId;
    public function __construct($key, $providerId)
    {
        $this->key = $key;
        $this->providerId = $providerId;
    }
    public function create(ContainerBuilder $container, $id, $config)
    {
        $container
            ->setDefinition($id, new ChildDefinition($this->providerId))
            ->addArgument($config['class'])
            ->addArgument($config['property'])
        ;
    }
    public function getKey()
    {
        return $this->key;
    }
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
            ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('property')->defaultNull()->end()
            ->end()
        ;
    }
}
}
