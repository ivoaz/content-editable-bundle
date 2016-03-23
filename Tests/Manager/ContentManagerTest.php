<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\UnitOfWork;
use Ivoaz\Bundle\ContentEditableBundle\Manager\ContentManager;
use Ivoaz\Bundle\ContentEditableBundle\Exception\MissingLocaleException;
use Ivoaz\Bundle\ContentEditableBundle\Model\Content;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $om;

    /**
     * @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    public function setUp()
    {
        $this->repository = $this->getMock(ObjectRepository::class);

        $this->om = $this->getMock(ObjectManager::class);
        $this->om->method('getRepository')
            ->with(Content::class)
            ->willReturn($this->repository);
    }

    public function testUpdateFlushesObject()
    {
        $manager = new ContentManager($this->om);

        $content = new Content();

        $this->om->method('contains')
            ->with($content)
            ->willReturn(true);

        $this->om->expects($this->once())
            ->method('flush')
            ->with($content);

        $manager->update($content);
    }

    public function testUpdateDetachesObject()
    {
        $manager = new ContentManager($this->om);

        $content = new Content();
        $managedContent = new Content();

        $this->om->method('contains')
            ->with($content)
            ->willReturn(false);

        $this->om->expects($this->at(1))
            ->method('merge')
            ->with($content)
            ->willReturn($managedContent);

        $this->om->expects($this->at(2))
            ->method('flush')
            ->with($managedContent);

        $this->om->expects($this->at(3))
            ->method('detach')
            ->with($managedContent);

        $manager->update($content);
    }

    public function testGetReturnsFoundContentByNameAndLocale()
    {
        $manager = new ContentManager($this->om);

        $expectedContent = new Content();

        $this->repository->method('findOneBy')
            ->with(['name' => 'name', 'locale' => 'en'])
            ->willReturn($expectedContent);

        $content = $manager->get('name', 'en', 'text');

        $this->assertSame($expectedContent, $content);
    }

    public function testGetCreatesNewContent()
    {
        $manager = new ContentManager($this->om);

        $expectedContent = new Content();
        $expectedContent->setName('name')
            ->setText('text')
            ->setLocale('en');

        $this->repository->method('findOneBy')
            ->with(['name' => 'name', 'locale' => 'en'])
            ->willReturn(null);

        $this->om->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(
                    function ($value) use ($expectedContent) {
                        return $value == $expectedContent;
                    }
                )
            );

        $this->om->expects($this->once())
            ->method('flush')
            ->with(
                $this->callback(
                    function ($value) use ($expectedContent) {
                        return $value == $expectedContent;
                    }
                )
            );

        $content = $manager->get('name', 'en', 'text');

        $this->assertEquals($expectedContent, $content);
    }

    public function testGetUsesNameForDefaultWhenNotSpecified()
    {
        $manager = new ContentManager($this->om);

        $expectedContent = new Content();
        $expectedContent->setName('name')
            ->setText('name')
            ->setLocale('en');

        $this->repository->method('findOneBy')
            ->with(['name' => 'name', 'locale' => 'en'])
            ->willReturn(null);

        $content = $manager->get('name', 'en');

        $this->assertEquals($expectedContent, $content);
    }

    public function testGetDetachesExistingObject()
    {
        $manager = new ContentManager($this->om);

        $content = new Content();

        $this->repository->method('findOneBy')
            ->with(['name' => 'name', 'locale' => 'en'])
            ->willReturn($content);

        $this->om->expects($this->once())
            ->method('detach')
            ->with($content);

        $manager->get('name', 'en', 'text');
    }

    public function testGetDetachesNewObject()
    {
        $manager = new ContentManager($this->om);

        $this->repository->method('findOneBy')
            ->with(['name' => 'name', 'locale' => 'en'])
            ->willReturn(null);

        $content = new Content();
        $content->setName('name')
            ->setText('text')
            ->setLocale('en');

        $this->om->expects($this->once())
            ->method('detach')
            ->with(
                $this->callback(
                    function ($value) use ($content) {
                        return $value == $content;
                    }
                )
            );

        $manager->get('name', 'en', 'text');
    }

    public function testGetGuessesLocaleFromRequest()
    {
        $requests = new RequestStack();
        $request = new Request();
        $request->setLocale('en');
        $requests->push($request);

        $manager = new ContentManager($this->om, $requests);

        $content = $manager->get('name');

        $this->assertSame('en', $content->getLocale());
    }

    public function testGetThrowsMissingLocaleException()
    {
        $this->setExpectedException(MissingLocaleException::class);

        $manager = new ContentManager($this->om);
        $manager->get('name');
    }
}
