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

use Ivoaz\Bundle\ContentEditableBundle\Model\Content;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
interface EditorInterface
{
    /**
     * @param Content $content
     * @param array   $options
     *
     * @return string
     */
    public function renderContent(Content $content, array $options);

    /**
     * Returns editor content or injects it into the response directly.
     *
     * @param Response $response
     *
     * @return string|null
     */
    public function renderEditor(Response $response);
}
