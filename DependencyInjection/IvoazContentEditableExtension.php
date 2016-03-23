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

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class IvoazContentEditableExtension extends Extension
{
    private static $objectManagers = [
        Configuration::MODEL_ORM     => 'doctrine.orm.%s_entity_manager',
        Configuration::MODEL_MONGODB => 'doctrine_mongodb.odm.%s_document_manager',
    ];

    private static $defaultModelManagerNameParameters = [
        Configuration::MODEL_ORM     => 'doctrine.default_entity_manager',
        Configuration::MODEL_MONGODB => 'doctrine_mongodb.odm.default_document_manager',
    ];

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $modelType = $config['model_type'];
        $container->setParameter(sprintf('ivoaz_content_editable.model_type_%s', $modelType), true);

        if ($config['model_manager_name']) {
            $managerName = $config['model_manager_name'];
        } else {
            $managerName = $container->getParameter(self::$defaultModelManagerNameParameters[$modelType]);
        }

        $container->setParameter('ivoaz_content_editable.model_manager_name', $managerName);

        $objectManager = sprintf(self::$objectManagers[$modelType], $managerName);
        $container->setAlias('ivoaz_content_editable.object_manager', new Alias($objectManager, false));

        $editor = $config['editor'];
        $container->setAlias('ivoaz_content_editable.editor', new Alias($editor, false));
    }
}
