<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;
use Oro\Bundle\SecurityBundle\Tests\Unit\Fixtures\Models\CMS\CmsComment;

class ObjectIdAccessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetIdPrefersInterfaceOverGetId()
    {
        $accessor = new ObjectIdAccessor($this->doctrineHelper);

        $obj = $this->getMockBuilder('Symfony\Component\Security\Acl\Model\DomainObjectInterface')
            ->setMethods(['getObjectIdentifier', 'getId'])
            ->getMock();
        $obj
            ->expects($this->once())
            ->method('getObjectIdentifier')
            ->will($this->returnValue('getObjectIdentifier()'));
        $obj
            ->expects($this->never())
            ->method('getId')
            ->will($this->returnValue('getId()'));

        $id = $accessor->getId($obj);

        $this->assertEquals('getObjectIdentifier()', $id);
    }

    public function testGetIdWithoutDomainObjectInterface()
    {
        $accessor = new ObjectIdAccessor($this->doctrineHelper);

        $id = $accessor->getId(new TestDomainObject());
        $this->assertEquals('getId()', $id);
    }

    public function testGetIdNull()
    {
        $this->expectException(\Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException::class);
        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $accessor = new ObjectIdAccessor($doctrineHelper);

        $accessor->getId(null);
    }

    public function testGetIdNonValidObject()
    {
        $this->expectException(\Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException::class);
        $accessor = new ObjectIdAccessor($this->doctrineHelper);
        $accessor->getId(new \stdClass());
    }

    public function testGetIdEntity()
    {
        $accessor = new ObjectIdAccessor($this->doctrineHelper);

        $object = new CmsComment();
        $object->setId(234);

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntity')
            ->with($object)
            ->willReturn(true);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object)
            ->willReturn($object->getIdentity());

        $this->assertEquals(234, $accessor->getId($object));
    }
}
