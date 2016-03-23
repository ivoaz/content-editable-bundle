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
    const MODEL_ORM = 'orm';
    const MODEL_MONGODB = 'mongodb';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ivoaz_content_editable');

        $supportedModelTypes = [self::MODEL_ORM, self::MODEL_MONGODB];

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()

                ->scalarNode('model_type')
                    ->validate()
                        ->ifNotInArray($supportedModelTypes)
                        ->thenInvalid(
                            sprintf(
                                'The model type "%%s" is not supported. Please use one of the following model type: %s.',
                                implode(', ', $supportedModelTypes)
                            )
                        )
                    ->end()
                    ->cannotBeEmpty()
                    ->defaultValue(self::MODEL_ORM)
                ->end()

                ->scalarNode('model_manager_name')
                    ->defaultValue(null)
                ->end()

                ->scalarNode('editor')
                    ->cannotBeEmpty()
                    ->defaultValue('ivoaz_content_editable.default_editor')
                ->end()

            ->end();

        return $treeBuilder;
    }
}
