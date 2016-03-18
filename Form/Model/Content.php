<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Form\Model;

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class Content
{
    /**
     * @var string
     */
    public $text;

    /**
     * @param \Ivoaz\Bundle\ContentEditableBundle\Entity\Content $content
     */
    public function update(\Ivoaz\Bundle\ContentEditableBundle\Entity\Content $content)
    {
        $content->setText($this->text);
    }
}
