<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\DependencyInjection;

use Ivoaz\Bundle\ContentEditableBundle\DependencyInjection\IvoazContentEditableExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class IvoazContentEditableExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IvoazContentEditableExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->extension = new IvoazContentEditableExtension();
        $this->container = new ContainerBuilder();

        $this->container->setParameter('doctrine.default_entity_manager', 'default');
        $this->container->setParameter('doctrine_mongodb.odm.default_document_manager', 'default');
    }

    /**
     * @dataProvider getEditorConfigurationTestData
     *
     * @param string $expected
     * @param array  $configs
     * @param string $message
     */
    public function testEditorConfiguration($expected, $configs, $message = '')
    {
        $this->extension->load($configs, $this->container);

        $alias = $this->container->getAlias('ivoaz_content_editable.editor');

        $this->assertEquals($expected, $alias, $message);
    }

    /**
     * @return array
     */
    public function getEditorConfigurationTestData()
    {
        return [
            ['ivoaz_content_editable.default_editor', [], 'The default editor service was not used.'],
            [
                'custom_editor_service',
                ['ivoaz_content_editable' => ['editor' => 'custom_editor_service']],
                'Custom editor service was not set.',
            ],
        ];
    }

    /**
     * @dataProvider getModelTypeConfigurationTestData
     *
     * @param string $expected
     * @param array  $configs
     * @param string $message
     */
    public function testModelTypeConfiguration($expected, $configs, $message = '')
    {
        $this->extension->load($configs, $this->container);

        try {
            $parameter = $this->container->getParameter(sprintf('ivoaz_content_editable.model_type_%s', $expected));
        } catch (\InvalidArgumentException $e) {
            $parameter = false;
        }

        $this->assertTrue($parameter, $message);
    }

    /**
     * @return array
     */
    public function getModelTypeConfigurationTestData()
    {
        return [
            ['orm', [], 'The default model type was not used.'],
            [
                'orm',
                ['ivoaz_content_editable' => ['model_type' => 'orm']],
                'The orm model type was not used.',
            ],
            [
                'mongodb',
                ['ivoaz_content_editable' => ['model_type' => 'mongodb']],
                'The mongodb model type was not used.',
            ],
        ];
    }

    /**
     * @dataProvider getObjectManagerConfigurationTestData
     *
     * @param string $expectedModelManagerName
     * @param string $expectedObjectManager
     * @param array  $configs
     * @param string $message
     */
    public function testObjectManagerConfiguration(
        $expectedModelManagerName,
        $expectedObjectManager,
        $configs,
        $message = ''
    ) {
        $this->extension->load($configs, $this->container);

        $alias = $this->container->getAlias('ivoaz_content_editable.object_manager');
        $modelManagerName = $this->container->getParameter('ivoaz_content_editable.model_manager_name');

        $this->assertEquals($expectedObjectManager, $alias, $message);
        $this->assertEquals($expectedModelManagerName, $modelManagerName, $message);
    }

    /**
     * @return array
     */
    public function getObjectManagerConfigurationTestData()
    {
        return [
            ['default', 'doctrine.orm.default_entity_manager', [], 'The default orm object manager was not used.'],
            [
                'default',
                'doctrine.orm.default_entity_manager',
                ['ivoaz_content_editable' => ['model_type' => 'orm']],
                'The default orm object manager was not used.',
            ],
            [
                'default',
                'doctrine_mongodb.odm.default_document_manager',
                ['ivoaz_content_editable' => ['model_type' => 'mongodb']],
                'The default mongodb object manager was not used.',
            ],
            [
                'custom',
                'doctrine_mongodb.odm.custom_document_manager',
                ['ivoaz_content_editable' => ['model_type' => 'mongodb', 'model_manager_name' => 'custom']],
                'A custom object manager was not used.',
            ],
        ];
    }
}
