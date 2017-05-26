<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper;

use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Mindlahus\SymfonyAssets\Helper\EntityHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Mindlahus\SymfonyAssets\Helper\Resources\MockGroupEntity;
use Tests\Mindlahus\SymfonyAssets\Helper\Resources\MockMemberEntity;

class EntityHelperTest extends WebTestCase
{
    /**
     * @var EntityHelper
     */
    private $entityHelper;

    public function setUp(): void
    {
        $this->entityHelper = new EntityHelper();
    }

    /**
     * @group getFormattedDateTime
     */
    public function testGetFormattedDateTime(): void
    {
        $this->assertEquals(
            'Monday, 15-Aug-2005 15:52:01 UTC',
            EntityHelper::getFormattedDateTime(new \DateTime('15-08-2005T15:52:01'), \DateTime::COOKIE)
        );
        $this->assertEquals(
            '2011-01-01T15:03:01+01:00',
            EntityHelper::getFormattedDateTime(new \DateTime('2011-01-01T15:03:01+01:00'))
        );
    }

    /**
     * @group validDateDiffInterval
     */
    public function testValidDateDiffInterval(): void
    {
        $timeZoneDate = new \DateTime('15-9-2000', new DateTimeZone('America/New_York'));
        $date1 = new \DateTime('15-9-2000');
        $date2 = new \DateTime('16-9-2000');

        $this->assertEquals($date2->diff($date1), EntityHelper::validDateDiffInterval($date2, $timeZoneDate));

        $this->assertEquals(1, EntityHelper::validDateDiffInterval($date2, $timeZoneDate, '%a'));
        $this->assertEquals(1, EntityHelper::validDateDiffInterval($timeZoneDate, $date2, '%a'));
        $this->assertEquals(0, EntityHelper::validDateDiffInterval($date1, $date1, '%a'));

        $this->assertEquals(-1, EntityHelper::validDateDiffInterval($date2, $timeZoneDate, '%r%a'));
    }

    /**
     * @group getMembersAllTogether
     */
    public function testGetMembersAllTogether(): void
    {
        $groups = new MockGroupEntity();
        $members = new MockMemberEntity();
        $options = [
            'getter' => 'getMembers'
        ];

        $groups->setMembers(new ArrayCollection([$members]));
        $members->setGroups(new ArrayCollection([$groups]));

        $membersAllTogether = EntityHelper::getMembersAllTogether([$groups], [$members], $options);

        $this->assertEquals(1, $membersAllTogether->total);
        $this->assertEquals(2, $membersAllTogether->array[0]);
        $this->assertEquals(true, $membersAllTogether->collection[0] instanceof $members);
    }

    /**
     * @group isNotValidPassword
     */
    public function testIsNotValidPassword(): void
    {
        $this->assertEquals(
            'Sorry! You have to choose a password which you never used in the past.',
            EntityHelper::isNotValidPassword(
                'password',
                'password',
                EntityHelper::setPasswordHistory('password', 's:8:"password";'))
        );
        $this->assertEquals(
            false,
            EntityHelper::isNotValidPassword('Password1@', 'Password1@', '')
        );

        $this->assertEquals(
            '`Password` and `Password Confirmation` does not match!',
            EntityHelper::isNotValidPassword('password', 'wrongPassword', '')
        );
        $this->assertEquals(
            'Your password should contain at least one digit.',
            EntityHelper::isNotValidPassword('password', 'password', '')
        );
        $this->assertEquals(
            'Your password should contain at least one uppercase letter.',
            EntityHelper::isNotValidPassword('password1', 'password1', '')
        );
        $this->assertEquals(
            'Your password should contain at least one lowercase letter.',
            EntityHelper::isNotValidPassword('PASSWORD1', 'PASSWORD1', '')
        );
        $this->assertEquals(
            'Your password should contain at least one special character.',
            EntityHelper::isNotValidPassword('Password1', 'Password1', '')
        );
        $this->assertEquals(
            'Your password should contain at least 6 (six) characters.',
            EntityHelper::isNotValidPassword('P@s1', 'P@s1', '')
        );
    }

}
