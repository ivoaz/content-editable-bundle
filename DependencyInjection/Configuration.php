<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ivoaz_content_editable');

        $rootNode
            ->children()
                ->scalarNode('editor')->defaultValue('ivoaz_content_editable.default_editor')
            ->end();

        return $treeBuilder;
    }
}
