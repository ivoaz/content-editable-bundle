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

use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Ivoaz\Bundle\ContentEditableBundle\Exception\MissingLocaleException;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
interface ContentManagerInterface
{
    /**
     * @param Content $content
     */
    public function update(Content $content);

    /**
     * Gets content by its name. If content does not exist in current or specified locale it will be created with the
     * default text. If content does not exist and default text is null, then the name will be used as a text. If
     * locale can not be automatically detected, then MissingLocaleException will be thrown.
     *
     * @param string $name
     * @param string $default
     * @param string $locale
     *
     * @return Content
     *
     * @throws MissingLocaleException
     */
    public function get($name, $default = null, $locale = null);

    /**
     * @param mixed $id
     *
     * @return Content|null
     */
    public function find($id);
}
