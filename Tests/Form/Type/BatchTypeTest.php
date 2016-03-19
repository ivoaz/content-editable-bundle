<?php

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Form\Type;

use Ivoaz\Bundle\ContentEditableBundle\Form\Model\Batch;
use Ivoaz\Bundle\ContentEditableBundle\Form\Model\BatchContent;
use Ivoaz\Bundle\ContentEditableBundle\Form\Type\BatchType;
use Symfony\Component\Form\Test\TypeTestCase;

class BatchTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'contents' => [
                [
                    'id' => 1,
                    'text' => 'Text 1',
                ],
                [
                    'id' => 2,
                    'text' => 'Text 2',
                ],
            ],
        ];

        $form = $this->factory->create(BatchType::class);

        $object = new Batch();
        $object->contents[] = new BatchContent();
        $object->contents[] = new BatchContent();
        $object->contents[0]->id = 1;
        $object->contents[1]->id = 2;
        $object->contents[0]->text = 'Text 1';
        $object->contents[1]->text = 'Text 2';

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());
    }
}
