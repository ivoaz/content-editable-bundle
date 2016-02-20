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

use Ivoaz\Bundle\ContentEditableBundle\Editor\EditorInterface;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Ivoaz\Bundle\ContentEditableBundle\Twig\ContentEditableExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentEditableExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $editor;

    /**
     * @var ContentManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var ContentEditableExtension
     */
    private $extension;

    public function setUp()
    {
        $this->editor = $this->getMock(EditorInterface::class);
        $this->manager = $this->getMock(ContentManagerInterface::class);
        $this->authorizationChecker = $this->getMock(AuthorizationCheckerInterface::class);

        $this->extension = new ContentEditableExtension($this->manager, $this->authorizationChecker, $this->editor);
    }

    /**
     * @dataProvider getRenderContentEditableTestData
     *
     * @param string $expectedText
     * @param string $text
     * @param string $name
     * @param array  $options
     * @param bool   $isAuthorized
     * @param string $message
     */
    public function testRenderContentEditable($expectedText, $text, $name, $options, $isAuthorized, $message)
    {
        $content = new Content();
        $content->setId(1)
            ->setName($name)
            ->setText($text)
            ->setLocale($options['locale']);

        $this->manager->method('get')
            ->with($name, $text, $options['locale'])
            ->willReturn($content);

        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn($isAuthorized);

        $this->editor->method('renderContent')
            ->with($content, $options)
            ->willReturn(sprintf('editable_%s', $text));

        $rendered = $this->extension->render($text, $name, $options);

        $this->assertSame($expectedText, $rendered, $message);
    }

    /**
     * @return array
     */
    public function getRenderContentEditableTestData()
    {
        return [
            ['editable_text', 'text', 'name', ['locale' => 'en'], true, 'Should have rendered editable text.'],
            ['text', 'text', 'name', ['locale' => 'en'], false, 'Should not have rendered editable text.'],
        ];
    }
}
