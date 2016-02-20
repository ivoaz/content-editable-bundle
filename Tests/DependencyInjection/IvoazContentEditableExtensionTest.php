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
    }

    /**
     * @dataProvider getEditorConfigurationTestData
     *
     * @param string $expectedEditor
     * @param array  $configs
     * @param string $message
     */
    public function testEditorConfiguration($expectedEditor, $configs, $message = '')
    {
        $this->extension->load($configs, $this->container);

        $definition = $this->container->getDefinition('ivoaz_content_editable.twig_extension');
        $editor = $definition->getArgument(2);

        $expectedEditor = new Reference($expectedEditor);

        $this->assertEquals($expectedEditor, $editor, $message);
    }

    /**
     * @return array
     */
    public function getEditorConfigurationTestData()
    {
        return [
            ['ivoaz_content_editable.default_editor', [], 'The default editor was not used.'],
            ['custom_editor_service', [['editor' => 'custom_editor_service']], 'Custom editor service was not set.'],
        ];
    }
}
