<?php

/*
 * This file is part of the Ivoaz ContentEditable bundle.
 *
 * (c) Ivo Azirjans <ivo.azirjans@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ivoaz\Bundle\ContentEditableBundle\Tests\EntityManager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Ivoaz\Bundle\ContentEditableBundle\EntityManager\ContentManager;
use Ivoaz\Bundle\ContentEditableBundle\EntityRepository\ContentRepository;
use Ivoaz\Bundle\ContentEditableBundle\Exception\MissingLocaleException;
use Ivoaz\Bundle\ContentEditableBundle\Entity\Content;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $em;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uow;

    /**
     * @var ContentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    public function setUp()
    {
        $this->uow = $this->getMock(UnitOfWork::class, ['isInIdentityMap'], [], '', false);

        $this->repository = $this->getMock(ContentRepository::class, ['findOneByNameAndLocale'], [], '', false);

        $this->em = $this->getMock(EntityManagerInterface::class);
        $this->em->method('getUnitOfWork')
            ->willReturn($this->uow);
        $this->em->method('getRepository')
            ->with(Content::class)
            ->willReturn($this->repository);
    }

    public function testUpdateFlushesEntity()
    {
        $manager = new ContentManager($this->em);

        $content = new Content();

        $this->uow->method('isInIdentityMap')
            ->with($content)
            ->willReturn(true);

        $this->em->expects($this->once())
            ->method('flush')
            ->with($content);

        $manager->update($content);
    }

    public function testUpdateDetachesEntity()
    {
        $manager = new ContentManager($this->em);

        $content = new Content();
        $managedContent = new Content();

        $this->uow->method('isInIdentityMap')
            ->with($content)
            ->willReturn(false);

        $this->em->expects($this->at(1))
            ->method('merge')
            ->with($content)
            ->willReturn($managedContent);

        $this->em->expects($this->at(2))
            ->method('flush')
            ->with($managedContent);

        $this->em->expects($this->at(3))
            ->method('detach')
            ->with($managedContent);

        $manager->update($content);
    }

    public function testGetReturnsFoundContentByNameAndLocale()
    {
        $manager = new ContentManager($this->em);

        $expectedContent = new Content();

        $this->repository->method('findOneByNameAndLocale')
            ->with('name', 'en')
            ->willReturn($expectedContent);

        $content = $manager->get('name', 'text', 'en');

        $this->assertSame($expectedContent, $content);
    }

    public function testGetCreatesNewContent()
    {
        $manager = new ContentManager($this->em);

        $expectedContent = new Content();
        $expectedContent->setName('name')
            ->setText('text')
            ->setLocale('en');

        $this->repository->method('findOneByNameAndLocale')
            ->with('name', 'en')
            ->willReturn(null);

        $this->em->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(
                    function ($value) use ($expectedContent) {
                        return $value == $expectedContent;
                    }
                )
            );

        $this->em->expects($this->once())
            ->method('flush')
            ->with(
                $this->callback(
                    function ($value) use ($expectedContent) {
                        return $value == $expectedContent;
                    }
                )
            );

        $content = $manager->get('name', 'text', 'en');

        $this->assertEquals($expectedContent, $content);
    }

    public function testGetUsesNameForDefaultWhenNotSpecified()
    {
        $manager = new ContentManager($this->em);

        $expectedContent = new Content();
        $expectedContent->setName('name')
            ->setText('name')
            ->setLocale('en');

        $this->repository->method('findOneByNameAndLocale')
            ->with('name', 'en')
            ->willReturn(null);

        $content = $manager->get('name', null, 'en');

        $this->assertEquals($expectedContent, $content);
    }

    public function testGetDetachesExistingEntity()
    {
        $manager = new ContentManager($this->em);

        $content = new Content();

        $this->repository->method('findOneByNameAndLocale')
            ->with('name', 'en')
            ->willReturn($content);

        $this->em->expects($this->once())
            ->method('detach')
            ->with($content);

        $manager->get('name', 'text', 'en');
    }

    public function testGetDetachesNewEntity()
    {
        $manager = new ContentManager($this->em);

        $this->repository->method('findOneByNameAndLocale')
            ->with('name', 'en')
            ->willReturn(null);

        $content = new Content();
        $content->setName('name')
            ->setText('text')
            ->setLocale('en');

        $this->em->expects($this->once())
            ->method('detach')
            ->with(
                $this->callback(
                    function ($value) use ($content) {
                        return $value == $content;
                    }
                )
            );

        $manager->get('name', 'text', 'en');
    }

    public function testGetGuessesLocaleFromRequest()
    {
        $requests = new RequestStack();
        $request = new Request();
        $request->setLocale('en');
        $requests->push($request);

        $manager = new ContentManager($this->em, $requests);

        $content = $manager->get('name');

        $this->assertSame('en', $content->getLocale());
    }

    public function testGetThrowsMissingLocaleException()
    {
        $this->expectException(MissingLocaleException::class);

        $manager = new ContentManager($this->em);
        $manager->get('name');
    }
}
