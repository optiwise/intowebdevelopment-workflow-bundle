<?php

namespace IntoWebDevelopment\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('intowebdev_workflow')
            ->children()
                ->arrayNode("validation")
                    ->children()
                        ->booleanNode("use_prevalidate_before_steps")->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
