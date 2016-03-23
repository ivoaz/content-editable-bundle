<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Twig;

use Ivoaz\Bundle\ContentEditableBundle\Editor\EditorInterface;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentEditableExtension extends \Twig_Extension
{
    /**
     * @var ContentManagerInterface
     */
    private $manager;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var EditorInterface
     */
    private $editor;

    /**
     * @param ContentManagerInterface       $manager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EditorInterface               $editor
     */
    public function __construct(
        ContentManagerInterface $manager,
        AuthorizationCheckerInterface $authorizationChecker,
        EditorInterface $editor
    ) {
        $this->manager = $manager;
        $this->authorizationChecker = $authorizationChecker;
        $this->editor = $editor;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'contenteditable',
                [$this, 'render'],
                ['pre_escape' => 'html', 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new ContentEditableTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function render($default, $name = null, array $options = [])
    {
        if (null === $name) {
            $name = $default;
        }

        $locale = isset($options['locale']) ? $options['locale'] : null;
        $content = $this->manager->get($name, $locale, $default);

        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $content->getText();
        }

        return $this->editor->renderContent($content, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ivoaz_content_editable';
    }
}
