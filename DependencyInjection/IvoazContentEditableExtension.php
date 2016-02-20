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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class IvoazContentEditableExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['editor'])) {
            $container->getDefinition('ivoaz_content_editable.twig_extension')
                ->replaceArgument(2, new Reference($config['editor']));

            $container->getDefinition('ivoaz_content_editable.editor_response_listener')
                ->replaceArgument(0, new Reference($config['editor']));
        }
    }
}
