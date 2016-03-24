<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\EventListener;

use Ivoaz\Bundle\ContentEditableBundle\Editor\EditorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class EditorResponseListener
{
    /**
     * @var EditorInterface
     */
    private $editor;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param EditorInterface               $editor
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(EditorInterface $editor, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->editor = $editor;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        try {
            if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
                return;
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $response = $event->getResponse();

        if ($response->isRedirection() || false === strpos($response->headers->get('Content-Type', ''), 'text/html')) {
            return;
        }

        $html = $this->editor->renderEditor($response);

        if (!empty($html)) {
            $this->injectEditor($response, $html);
        }
    }

    /**
     * @param Response $response
     * @param string   $editorHtml
     */
    private function injectEditor(Response $response, $editorHtml)
    {
        $html = $response->getContent();
        $pos = mb_strripos($html, '</body>');

        if (false === $pos) {
            return;
        }

        $html = mb_substr($html, 0, $pos).$editorHtml.mb_substr($html, $pos);

        $response->setContent($html);
    }
}
