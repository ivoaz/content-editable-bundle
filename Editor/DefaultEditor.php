<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Editor;

use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class DefaultEditor implements EditorInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var array
     */
    private $separatelyEditableContents;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->separatelyEditableContents = [];
    }

    /**
     * {@inheritdoc}
     */
    public function renderContent(Content $content, array $options)
    {
        $text = $content->getText();

        if (isset($options['separately']) && $options['separately']) {
            $this->separatelyEditableContents[$content->getId()] = $content;

            return $text;
        }

        return sprintf(
            '<span class="ivoaz-content-editable" data-ivoaz-content-editable-id="%s">%s</span>',
            $content->getId(),
            $text
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderEditor(Response $response)
    {
        if (!$this->twig) {
            $this->twig = $this->container->get('twig');
        };

        if (!empty($this->separatelyEditableContents)) {
            $html = $this->twig->render(
                '@IvoazContentEditable/separately_editable_contents.html.twig',
                ['contents' => array_values($this->separatelyEditableContents)]
            );
        } else {
            $html = '';
        }

        $html .= $this->twig->render('@IvoazContentEditable/editor.html.twig');

        return $html;
    }
}
