<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Controller;

use Ivoaz\Bundle\ContentEditableBundle\Form\Type\ContentBatchType;
use Ivoaz\Bundle\ContentEditableBundle\Form\Type\ContentType;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentController
{
    /**
     * @var ContentManagerInterface
     */
    private $manager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param ContentManagerInterface       $manager
     * @param FormFactoryInterface          $formFactory
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ContentManagerInterface $manager,
        FormFactoryInterface $formFactory,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->manager = $manager;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        if (!$this->isAuthorized()) {
            return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
        }

        $id = $request->get('id');
        $content = $this->manager->find($id);

        if (null === $content) {
            return $this->createErrorResponse([$this->createContentNotFoundError($id)]);
        }

        $form = $this->formFactory->create(ContentType::class);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $errors = $this->getErrors($form);

            return $this->createErrorResponse($errors);
        }

        $form->getData()->update($content);
        $this->manager->update($content);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function batchUpdateAction(Request $request)
    {
        if (!$this->isAuthorized()) {
            return new JsonResponse(null, JsonResponse::HTTP_FORBIDDEN);
        }

        $form = $this->formFactory->create(BatchType::class);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $errors = $this->getErrors($form);

            return $this->createErrorResponse($errors);
        }

        $errors = [];
        $contents = [];

        foreach ($form->getData()->contents as $data) {
            $content = $this->manager->find($data->id);

            if (null === $content) {
                $errors[] = $this->createContentNotFoundError($data->id);

                continue;
            }

            $data->update($content);
            $contents[] = $content;
        }

        if (0 !== count($errors)) {
            return $this->createErrorResponse($errors);
        }

        foreach ($contents as $content) {
            $this->manager->update($content);
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function getErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors(true, true) as $error) {
            $errors[] = ['title' => $error->getMessage()];
        }

        return $errors;
    }

    /**
     * @param array $errors
     * @param int   $status
     *
     * @return JsonResponse
     */
    private function createErrorResponse(array $errors, $status = 400)
    {
        return new JsonResponse(['errors' => $errors], $status);
    }

    /**
     * @return bool
     */
    private function isAuthorized()
    {
        return $this->authorizationChecker->isGranted('ROLE_ADMIN');
    }

    /**
     * @param $id
     *
     * @return array
     */
    private function createContentNotFoundError($id)
    {
        return ['title' => sprintf('Content with id "%s" was not found.', $id)];
    }
}
