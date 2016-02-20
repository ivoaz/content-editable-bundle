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

/**
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentEditableNode extends \Twig_Node
{
    /**
     * @param \Twig_Compiler $compiler
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $body = $this->getNode('body');

        if ($body instanceof \Twig_Node_Expression_Constant) {
            $body = new \Twig_Node_Expression_Constant(trim($body->getAttribute('value')), $body->getLine());
        } elseif ($body instanceof \Twig_Node_Text) {
            $body = new \Twig_Node_Expression_Constant(trim($body->getAttribute('data')), $body->getLine());
        }

        $compiler->write('echo $this->env->getExtension(\'ivoaz_content_editable\')->render(')
            ->subcompile($body)
            ->raw(',')
            ->repr($this->getAttribute('name'))
            ->raw(',')
            ->repr($this->getAttribute('options'))
            ->raw(');');
    }
}
