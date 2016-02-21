<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\EntityManager;

use Doctrine\ORM\EntityManagerInterface;
use Ivoaz\Bundle\ContentEditableBundle\Exception\MissingLocaleException;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManagerInterface;
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentManager implements ContentManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RequestStack
     */
    private $requests;

    /**
     * @param EntityManagerInterface $em
     * @param RequestStack           $requests
     */
    public function __construct(EntityManagerInterface $em, RequestStack $requests = null)
    {
        $this->em = $em;
        $this->requests = $requests;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Content $content)
    {
        $managed = true;

        if (!$this->em->getUnitOfWork()->isInIdentityMap($content)) {
            $managed = false;
            $content = $this->em->merge($content);
        }

        $this->em->flush($content);

        if (!$managed) {
            $this->em->detach($content);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null, $locale = null)
    {
        if (null === $locale) {
            if (null === $this->requests || !$request = $this->requests->getMasterRequest()) {
                throw new MissingLocaleException(
                    'Could not get content because locale is missing. Try passing it as a parameter.'
                );
            }

            $locale = $request->getLocale();
        }

        $content = $this->em->getRepository(Content::class)
            ->findOneByNameAndLocale($name, $locale);

        if (!$content) {
            if (null === $default) {
                $default = $name;
            }

            $content = $this->create($name, $default, $locale);
        }

        $this->em->detach($content);

        return $content;
    }

    /**
     * @param int $id
     *
     * @return Content|null
     */
    public function find($id)
    {
        $repository = $this->em->getRepository(Content::class);

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

        $this->em->persist($content);
        $this->em->flush($content);

        return $content;
    }
}
