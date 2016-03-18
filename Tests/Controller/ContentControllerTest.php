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
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Ivoaz\Bundle\ContentEditableBundle\Form\Model\Batch;
use Ivoaz\Bundle\ContentEditableBundle\Form\Model\Content as FormContent;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            ['errors' => [['title' => sprintf('Content with id "1" was not found.')]]],
            400
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
            400
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

        $this->form->method('getData')
            ->willReturn(new FormContent());

        $this->manager->expects($this->once())
            ->method('update')
            ->with($content);

        $request = new Request([], [], ['id' => 1]);
        $response = $this->controller->updateAction($request);

        $expectedResponse = new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

        $this->assertEquals($expectedResponse, $response);
    }

    public function testBatchUpdateActionReturnsErrorWhenContentNotFound()
    {
        $this->setAuthorized(true);

        $this->form->method('isValid')
            ->willReturn(true);

        $this->form->method('getData')
            ->willReturn($this->getBatch());

        $this->manager->method('find')
            ->willReturn(null);

        $response = $this->controller->batchUpdateAction(new Request());

        $expectedResponse = new JsonResponse(
            [
                'errors' => [
                    ['title' => 'Content with id "1" was not found.'],
                    ['title' => 'Content with id "2" was not found.'],
                ],
            ],
            400
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testBatchUpdateActionReturnsFormErrors()
    {
        $this->setAuthorized(true);

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

        $response = $this->controller->batchUpdateAction(new Request());

        $expectedResponse = new JsonResponse(
            ['errors' => [['title' => 'Test error1'], ['title' => 'Test error2']]],
            400
        );

        $this->assertEquals($expectedResponse, $response);
    }

    public function testBatchUpdateActionUpdatesContent()
    {
        $this->setAuthorized(true);

        $content1 = new Content();
        $content2 = new Content();

        $this->manager->method('find')
            ->will($this->returnValueMap([[1, $content1], [2, $content2]]));

        $this->form->method('isValid')
            ->willReturn(true);

        $this->form->method('getData')
            ->willReturn($this->getBatch());

        $this->manager->expects($this->exactly(2))
            ->method('update')
            ->withConsecutive([$content1], [$content2]);

        $response = $this->controller->batchUpdateAction(new Request());

        $expectedResponse = new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @dataProvider getActionIsForbiddenTestData
     *
     * @param string $method
     */
    public function testActionIsForbidden($method)
    {
        $this->setAuthorized(false);

        $response = call_user_func([$this->controller, $method], new Request());
        $expectedResponse = new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);

        $this->assertEquals($expectedResponse, $response, sprintf('Action "%s" should be forbidden.', $method));
    }

    /**
     * @return array
     */
    public function getActionIsForbiddenTestData()
    {
        return [['updateAction'], ['batchUpdateAction']];
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

    /**
     * @return Batch
     */
    private function getBatch()
    {
        $batch = new Batch();
        $batch->contents[] = new FormContent();
        $batch->contents[] = new FormContent();
        $batch->contents[0]->id = 1;
        $batch->contents[1]->id = 2;
        $batch->contents[0]->text = 'Text 1';
        $batch->contents[1]->text = 'Text 2';

        return $batch;
    }
}
