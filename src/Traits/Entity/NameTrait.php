<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

use JMS\Serializer\Annotation as Serializer;

trait NameTrait
{
    /**
     * @var string
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"first-name", "name", "name-all"})
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     */
    private $firstName;

    /**
     * @var string
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"last-name", "name", "name-all"})
     *
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank()
     * @Assert\Length(max=20)
     */
    private $lastName;

    /**
     * @var string
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"first-last-name", "name-full", "name-all"})
     *
     * @ORM\Column(type="string", length=41)
     * @Assert\NotBlank()
     */
    private $firstLastName;

    /**
     * @var string
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"last-first-name", "name-full", "name-all"})
     *
     * @ORM\Column(type="string", length=41)
     * @Assert\NotBlank()
     */
    private $lastFirstName;

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName)
    {

        $this->firstName = $firstName;

        $this->setFirstLastName();
        $this->setLastFirstName();

        return $this;
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName)
    {

        $this->lastName = $lastName;

        $this->setFirstLastName();
        $this->setLastFirstName();

        return $this;
    }

    /**
     * @return $this
     */
    public function setFirstLastName()
    {
        $this->firstLastName = trim($this->getFirstName() . ' ' . $this->getLastName());

        return $this;
    }

    /**
     * @return $this
     */
    public function setLastFirstName()
    {
        $this->lastFirstName = trim($this->getLastName() . ' ' . $this->getFirstName());

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Get firstLastName
     *
     * @return string
     */
    public function getFirstLastName(): string
    {
        return $this->firstLastName;
    }

    /**
     * Get lastFirstName
     *
     * @return string
     */
    public function getLastFirstName(): string
    {
        return $this->lastFirstName;
    }
}
