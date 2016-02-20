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
 * <pre>
 * {% contenteditable "content_name" locale="en" option1="value1" option2="value2" %}
 *   <p>This is a sample description</p>
 * {% endcontenteditable %}
 * </pre>
 *
 * @author Ivo Azirjans <ivo.azirjans@gmail.com>
 */
class ContentEditableTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
        $options = [];

        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            if (!$stream->test(\Twig_Token::NAME_TYPE)) {
                $this->throwSyntaxError($stream);
            }

            $key = $stream->getCurrent()->getValue();
            $stream->next();

            if (!$stream->test(\Twig_Token::OPERATOR_TYPE, '=')) {
                $options[$key] = true;

                continue;
            }

            $stream->next();

            if (!$this->testOptionValue($stream)) {
                $this->throwSyntaxError($stream);
            }

            $options[$key] = $this->getOptionValue($stream);

            $stream->next();
        }

        $stream->next();

        $body = $this->parser->subparse(
            function (\Twig_Token $token) {
                return $token->test(['endcontenteditable']);
            },
            true
        );

        if (!$body instanceof \Twig_Node_Text && !$body instanceof \Twig_Node_Expression) {
            throw new \Twig_Error_Syntax(
                'A text inside a contenteditable tag must be a simple text.',
                $body->getLine(),
                $stream->getFilename()
            );
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new ContentEditableNode(
            ['body' => $body],
            ['name' => $name, 'options' => $options],
            $lineno,
            $this->getTag()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'contenteditable';
    }

    /**
     * @param \Twig_TokenStream $stream
     *
     * @return bool
     */
    private function testOptionValue(\Twig_TokenStream $stream)
    {
        return
            $stream->test(\Twig_Token::STRING_TYPE) ||
            $stream->test(\Twig_Token::NUMBER_TYPE) ||
            $stream->test(\Twig_Token::NAME_TYPE, ['true', 'false']);
    }

    private function getOptionValue(\Twig_TokenStream $stream)
    {
        $value = $stream->getCurrent()->getValue();

        if ($stream->test(\Twig_Token::NAME_TYPE, ['true', 'false'])) {
            return 'true' === $value ? true : false;
        }

        return $value;
    }

    /**
     * @param \Twig_TokenStream $stream
     *
     * @throws \Twig_Error_Syntax
     */
    private function throwSyntaxError(\Twig_TokenStream $stream)
    {
        $token = $stream->getCurrent();

        throw new \Twig_Error_Syntax(
            sprintf(
                'Unexpected token "%s" of value "%s"',
                \Twig_Token::typeToEnglish($token->getType()),
                $token->getValue()
            ),
            $token->getLine(),
            $stream->getFilename()
        );
    }
}
