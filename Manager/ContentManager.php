<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Ivoaz\Bundle\ContentEditableBundle\Exception\MissingLocaleException;
use Ivoaz\Bundle\ContentEditableBundle\Model\Content;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentManager implements ContentManagerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var RequestStack
     */
    private $requests;

    /**
     * @param ObjectManager $om
     * @param RequestStack  $requests
     */
    public function __construct(ObjectManager $om, RequestStack $requests = null)
    {
        $this->om = $om;
        $this->requests = $requests;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Content $content)
    {
        $managed = true;

        if (!$this->om->contains($content)) {
            $managed = false;
            $content = $this->om->merge($content);
        }

        $this->om->flush($content);

        if (!$managed) {
            $this->om->detach($content);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $locale = null, $default = null)
    {
        if (null === $locale) {
            if (null === $this->requests || !$request = $this->requests->getMasterRequest()) {
                throw new MissingLocaleException(
                    'Could not get content because locale is missing. Try passing it as a parameter.'
                );
            }

            $locale = $request->getLocale();
        }

        $content = $this->om->getRepository(Content::class)
            ->findOneBy(
                [
                    'name'   => $name,
                    'locale' => $locale,
                ]
            );

        if (!$content) {
            if (null === $default) {
                $default = $name;
            }

            $content = $this->create($name, $default, $locale);
        }

        $this->om->detach($content);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $repository = $this->om->getRepository(Content::class);

        return $repository->find($id);
    }

    /**
     * @param string $name
     * @param string $text
     * @param string $locale
     *
     * @return Content
     */
    private function create($name, $text, $locale)
    {
        $content = new Content();
        $content->setName($name)
            ->setText($text)
            ->setLocale($locale);

        $this->om->persist($content);
        $this->om->flush($content);

        return $content;
    }
}
