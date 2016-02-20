<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Controller;

use Ivoaz\Bundle\ContentEditableBundle\Controller\ContentController;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactory;

    /**
     * @var FormInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $form;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationChecker;

    /**
     * @var ContentController
     */
    private $controller;

    public function setUp()
    {
        $this->manager = $this->getMock(ContentManagerInterface::class);
        $this->form = $this->getMock(FormInterface::class);
        $this->formFactory = $this->getMock(FormFactoryInterface::class);
        $this->formFactory->method('create')
            ->willReturn($this->form);
        $this->authorizationChecker = $this->getMock(AuthorizationCheckerInterface::class);

        $this->controller = new ContentController($this->manager, $this->formFactory, $this->authorizationChecker);
    }

    public function testUpdateActionReturnsErrorWhenContentNotFound()
    {
        $this->setAuthorized(true);

        $this->manager->method('find')
            ->with(1)
            ->willReturn(null);

        $request = new Request([], [], ['id' => 1]);
        $response = $this->controller->updateAction($request);

        $expectedResponse = new JsonResponse(
            ['errors' => [['title' => sprintf('Content with id "1" does not exist.')]]],
            400,
            ['Content-Type' => 'application/vnd.api+json']
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateActionReturnsFormErrors()
    {
        $this->setAuthorized(true);

        $content = new Content();

        $this->manager->method('find')
            ->with(1)
            ->willReturn($content);

        $this->form->method('isValid')
            ->willReturn(false);

        $this->form->method('getErrors')
            ->with(true, true)
            ->willReturn(
                [
                    new FormError('Test error1'),
                    new FormError('Test error2'),
                ]
            );

        $request = new Request([], [], ['id' => 1]);
        $response = $this->controller->updateAction($request);

        $expectedResponse = new JsonResponse(
            ['errors' => [['title' => 'Test error1'], ['title' => 'Test error2']]],
            400,
            ['Content-Type' => 'application/vnd.api+json']
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateActionUpdatesContent()
    {
        $this->setAuthorized(true);

        $content = new Content();

        $this->manager->method('find')
            ->with(1)
            ->willReturn($content);

        $this->form->method('isValid')
            ->willReturn(true);

        $this->manager->expects($this->once())
            ->method('update')
            ->with($content);

        $request = new Request([], [], ['id' => 1]);
        $response = $this->controller->updateAction($request);

        $expectedResponse = new Response('', Response::HTTP_NO_CONTENT, ['Content-Type' => 'application/vnd.api+json']);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateActionIsForbidden()
    {
        $this->setAuthorized(false);

        $request = new Request([], [], ['id' => 1]);
        $response = $this->controller->updateAction($request);

        $expectedResponse = new Response('', Response::HTTP_FORBIDDEN, ['Content-Type' => 'application/vnd.api+json']);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @param bool $authorized
     */
    private function setAuthorized($authorized)
    {
        $this->authorizationChecker->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn($authorized);
    }
}
