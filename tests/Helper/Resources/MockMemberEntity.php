<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper\Resources;

use Doctrine\Common\Collections\ArrayCollection;

class MockMemberEntity
{
    private $id;

    private $groups;

    /**
     * MockMemberEntity constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->id = 2;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param $groups
     */
    public function setGroups(ArrayCollection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @param $feature
     */
    public function addGroup($feature): void
    {
        $this->groups[] = $feature;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    /**
     * @param $group
     */
    public function removeGroup($group): void
    {
        $this->groups->removeElement($group);
    }
}