<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 18.11.2019
 * Time: 16:22
 */

namespace Bundles\SecurityBundle\DependencyInjection;

use Bundles\SecurityBundle\Security\Repositories\User;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $rootNode = $tb->root('routing');

        $this->addRoutingNode($rootNode);

        return $tb;
    }

    private function addRoutingNode(ArrayNodeDefinition $root){
        $root->children()
            ->scalarNode('resource')
                ->defaultValue('.')
            ->end()
            ->scalarNode('type')
                ->defaultNull()
            ->end()
        ->end();
    }
}