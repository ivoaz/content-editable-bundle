<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\EntityRepository;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentRepository extends BaseEntityRepository
{
    /**
     * @param string $name
     * @param string $locale
     *
     * @return \Ivoaz\Bundle\ContentEditableBundle\Entity\Content|null
     */
    public function findOneByNameAndLocale($name, $locale)
    {
        return $this->findOneBy(
            [
                'name'   => $name,
                'locale' => $locale,
            ]
        );
    }
}
