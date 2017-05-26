<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper\Resources;

use Doctrine\Common\Collections\ArrayCollection;

class MockGroupEntity
{
    private $id;

    private $members;

    /**
     * MockGroupEntity constructor.
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->id = 1;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param ArrayCollection $members
     */
    public function setMembers(ArrayCollection $members): void
    {
        $this->members = $members;
    }

    /**
     * @param $member
     */
    public function addMember($member): void
    {
        $this->members[] = $member;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers(): ArrayCollection
    {
        return $this->members;
    }

    /**
     * @param $member
     */
    public function removeProduct($member): void
    {
        $this->members->removeElement($member);
    }
}