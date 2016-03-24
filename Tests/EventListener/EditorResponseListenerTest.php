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
use Ivoaz\Bundle\ContentEditableBundle\Editor\EditorInterface;
use Ivoaz\Bundle\ContentEditableBundle\EventListener\EditorResponseListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class EditorResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EditorResponseListener
     */
    private $listener;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var DefaultEditor
     */
    private $editor;

    /**
     * @var FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    public function setUp()
    {
        $this->editor = $this->getMock(EditorInterface::class);
        $this->authorizationChecker = $this->getMock(AuthorizationCheckerInterface::class);
        $this->listener = new EditorResponseListener($this->editor, $this->authorizationChecker);
        $this->event = $this->getMock(
            FilterResponseEvent::class,
            ['getRequest', 'getResponse', 'isMasterRequest'],
            [],
            '',
            false
        );
    }

    /**
     * @dataProvider getOnKernelResponseTestData
     *
     * @param bool   $isMasterRequest
     * @param bool   $isXmlHttpRequest
     * @param bool   $isRedirection
     * @param bool   $isHtml
     * @param bool   $isAuthorized
     * @param string $message
     */
    public function testEditorIsNotRenderedOnKernelResponse(
        $isMasterRequest,
        $isXmlHttpRequest,
        $isRedirection,
        $isHtml,
        $isAuthorized,
        $message
    ) {
        $this->editor->method('renderEditor')
            ->willReturn('<div>editor</div>');

        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn($isAuthorized);

        $request = new Request();
        if ($isXmlHttpRequest) {
            $request->headers->add(['X-Requested-With' => 'XMLHttpRequest']);
        }

        $response = new Response(
            '<body></body>',
            $isRedirection ? 300 : 200,
            ['Content-Type' => $isHtml ? 'text/html' : 'text/plain']
        );

        $this->event->method('getRequest')
            ->willReturn($request);
        $this->event->method('getResponse')
            ->willReturn($response);
        $this->event->method('isMasterRequest')
            ->willReturn($isMasterRequest);

        $this->listener->onKernelResponse($this->event);

        $this->assertSame('<body></body>', $response->getContent(), $message);
    }

    /**
     * @return array
     */
    public function getOnKernelResponseTestData()
    {
        return [
            [
                false,
                false,
                false,
                true,
                true,
                'Editor should not be rendered when it is not master request.',
            ],
            [
                true,
                true,
                false,
                true,
                true,
                'Editor should not be rendered when it is xml http request.',
            ],
            [
                true,
                false,
                true,
                true,
                true,
                'Editor should not be rendered on redirection.',
            ],
            [
                true,
                false,
                false,
                false,
                true,
                'Editor should not be rendered when response is not html.',
            ],
            [
                true,
                false,
                false,
                true,
                false,
                'Editor should not be rendered when not authorized.',
            ],
        ];
    }

    public function testEditorIsNotRenderedWhenNotBehindFirewall()
    {
        $this->editor->method('renderEditor')
            ->willReturn('<div>editor</div>');

        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willThrowException(new AuthenticationCredentialsNotFoundException());

        $request = new Request();
        $response = new Response(
            '<body></body>',
            200,
            ['Content-Type' => 'text/html']
        );

        $this->event->method('getRequest')
            ->willReturn($request);
        $this->event->method('getResponse')
            ->willReturn($response);
        $this->event->method('isMasterRequest')
            ->willReturn(true);

        $this->listener->onKernelResponse($this->event);

        $this->assertSame('<body></body>', $response->getContent());
    }

    public function testEditorIsInjectedOnKernelResponse()
    {
        $this->editor->method('renderEditor')
            ->willReturn('<div>editor</div>');

        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $request = new Request();
        $response = new Response(
            '<body><h1>Some content</h1></body>',
            200,
            ['Content-Type' => 'text/html']
        );

        $this->event->method('getRequest')
            ->willReturn($request);
        $this->event->method('getResponse')
            ->willReturn($response);
        $this->event->method('isMasterRequest')
            ->willReturn(true);

        $this->listener->onKernelResponse($this->event);

        $this->assertSame('<body><h1>Some content</h1><div>editor</div></body>', $response->getContent());
    }

    public function testCustomEditorInjectionIsPossible()
    {
        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $request = new Request();
        $response = new Response(
            '<body></body>',
            200,
            ['Content-Type' => 'text/html']
        );

        $this->event->method('getRequest')
            ->willReturn($request);
        $this->event->method('getResponse')
            ->willReturn($response);
        $this->event->method('isMasterRequest')
            ->willReturn(true);

        $this->editor->expects($this->once())
            ->method('renderEditor')
            ->with($response)
            ->willReturn(null);

        $this->listener->onKernelResponse($this->event);
    }
}
