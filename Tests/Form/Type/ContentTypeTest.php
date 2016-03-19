<?php

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Form\Type;

use Ivoaz\Bundle\ContentEditableBundle\Form\Model\Content;
use Ivoaz\Bundle\ContentEditableBundle\Form\Type\ContentType;
use Symfony\Component\Form\Test\TypeTestCase;

class TestedTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'text' => 'Text',
        );

        $form = $this->factory->create(ContentType::class);

        $object = new Content();
        $object->text = 'Text';

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());
    }
}
