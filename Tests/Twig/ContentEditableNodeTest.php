<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Twig;

use Ivoaz\Bundle\ContentEditableBundle\Twig\ContentEditableNode;

class ContentEditableNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Compiler
     */
    private $compiler;

    public function setUp()
    {
        $this->compiler = new \Twig_Compiler(new \Twig_Environment(new \Twig_Loader_Array([])));
    }

    /**
     * @dataProvider getCompileTestData
     *
     * @param string $expectedSource
     * @param array  $nodes
     * @param array  $attributes
     * @param string $message
     */
    public function testCompile($expectedSource, $nodes, $attributes, $message = '')
    {
        $node = new ContentEditableNode($nodes, $attributes);

        $node->compile($this->compiler);

        $this->assertSame($expectedSource, $this->compiler->getSource(), $message);
    }

    /**
     * @return array
     */
    public function getCompileTestData()
    {
        return [
            [
                'echo $this->env->getExtension(\'ivoaz_content_editable\')->render("","name",array());',
                ['body' => new \Twig_Node_Text('', 0)],
                ['name' => 'name', 'options' => []],
                'The text and options should be compiled empty.'
            ],
            [
                'echo $this->env->getExtension(\'ivoaz_content_editable\')->render("text","name",array("locale" => "en", "test_option" => "test"));',
                ['body' => new \Twig_Node_Text('text', 0)],
                ['name' => 'name', 'options' => ['locale' => 'en', 'test_option' => 'test']],
                'The text and options should be compiled.'
            ],
        ];
    }
}
