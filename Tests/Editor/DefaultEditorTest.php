<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Extension;

use Ivoaz\Bundle\ContentEditableBundle\Editor\DefaultEditor;
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class DefaultEditorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Twig_Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $twig;

    /**
     * @var DefaultEditor
     */
    private $editor;

    public function setUp()
    {
        $this->twig = $this->getMock(\Twig_Environment::class, ['render'], [], '', false);
        $container = $this->getMock(ContainerInterface::class);
        $container->method('get')
            ->with('twig')
            ->willReturn($this->twig);
        $this->editor = new DefaultEditor($container);
    }

    /**
     * @dataProvider getRenderContentTestData
     *
     * @param string  $expectedText
     * @param Content $content
     * @param array   $options
     * @param string  $message
     */
    public function testRenderContent($expectedText, Content $content, array $options = [], $message = '')
    {
        $text = $this->editor->renderContent($content, $options);

        $this->assertSame($expectedText, $text, $message);
    }

    /**
     * @return array
     */
    public function getRenderContentTestData()
    {
        $content = new Content();
        $content->setId(1)
            ->setText('Example text')
            ->setLocale('en');

        return [
            [
                'Example text',
                $content,
                ['separately' => true],
                'The text should not be modified when editable separately.',
            ],
            [
                '<span class="ivoaz-content-editable" data-ivoaz-content-editable-id="1">Example text</span>',
                clone $content,
                [],
                'The text was not wrapped correctly.',
            ],
        ];
    }


    /**
     * @dataProvider getRenderEditorTestData
     *
     * @param array  $contents
     * @param array  $separateContents
     * @param string $message
     */
    public function testRenderEditor($contents, $separateContents, $message = '')
    {
        $this->twig->method('render')
            ->will(
                $this->returnValueMap(
                    [
                        ['@IvoazContentEditable/editor.html.twig', [], '<p>editor</p>'],
                        [
                            '@IvoazContentEditable/separately_editable_contents.html.twig',
                            ['contents' => $separateContents],
                            '<p>separate contents</p>',
                        ],
                    ]
                )
            );

        foreach ($contents as $content) {
            $this->editor->renderContent($content, []);
        }

        foreach ($separateContents as $content) {
            $this->editor->renderContent($content, ['separately' => true]);
        }

        $html = $this->editor->renderEditor(new Response());

        $expectedHtml = empty($separateContents)
            ? '<p>editor</p>'
            : '<p>separate contents</p><p>editor</p>';

        $this->assertSame($expectedHtml, $html, $message);
    }

    /**
     * @return array
     */
    public function getRenderEditorTestData()
    {
        return [
            [
                [new Content(), new Content()],
                [(new Content())->setId(1), (new Content())->setId(2)],
                'Editor should be injected with separately editable contents.',
            ],
            [
                [new Content(), new Content()],
                [],
                'Editor should be injected without separately editable contents.',
            ],
        ];
    }
}
