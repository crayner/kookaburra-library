<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 8/10/2019
 * Time: 13:02
 */

namespace Kookaburra\Library\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Kookaburra\Library\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * getConfigTreeBuilder
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kookaburra_library');
        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('generate_identifier')->defaultTrue()->end()
                ->integerNode('maximum_copies')->defaultValue(20)->end()
                ->integerNode('borrow_period')->defaultValue(7)->end()
                ->integerNode('renewal_maximum')->defaultValue(1)->end()
                ->arrayNode('item_types')->end()
                ->arrayNode('allowed_borrowers')->scalarPrototype()->end()
            ->end()
        ;
        return $treeBuilder;
    }
}