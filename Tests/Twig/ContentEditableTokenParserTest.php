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
use Ivoaz\Bundle\ContentEditableBundle\Twig\ContentEditableTokenParser;

class ContentEditableTokenParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getCompileTestData
     *
     * @param ContentEditableNode $expectedNode
     * @param string              $source
     * @param string              $message
     */
    public function testCompile(ContentEditableNode $expectedNode, $source, $message = '')
    {
        $env = new \Twig_Environment(
            $this->getMock('Twig_LoaderInterface'),
            ['cache' => false, 'autoescape' => false, 'optimizations' => 0]
        );
        $env->addTokenParser(new ContentEditableTokenParser());
        $parser = new \Twig_Parser($env);

        $stream = $env->tokenize($source);
        $node = $parser->parse($stream)
            ->getNode('body')
            ->getNode(0);

        $this->assertEquals($expectedNode, $node, $message);
    }

    /**
     * @return array
     */
    public function getCompileTestData()
    {
        return [
            [
                new ContentEditableNode(
                    ['body' => new \Twig_Node_Text('Text', 1)],
                    ['name' => 'name', 'options' => []],
                    1,
                    'contenteditable'
                ),
                '{% contenteditable "name" %}Text{% endcontenteditable %}',
            ],
            [
                new ContentEditableNode(
                    ['body' => new \Twig_Node_Text('Text', 1)],
                    [
                        'name'    => 'name',
                        'options' => [
                            'test_string'  => 'string',
                            'test_number'  => 0,
                            'test_true'    => true,
                            'test_false'   => false,
                            'test_enabled' => true,
                        ],
                    ],
                    1,
                    'contenteditable'
                ),
                '{% contenteditable "name" test_string="string" test_number=0 test_true=true test_false=false test_enabled %}Text{% endcontenteditable %}',
            ],
        ];
    }
}
