<?php

namespace Mindlahus\SymfonyAssets\AbstractInterface;

interface NameInterface
{
    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName(string $firstName);

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName(string $lastName);

    /**
     * @return $this
     */
    public function setFirstLastName();

    /**
     * @return $this
     */
    public function setLastFirstName();

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName(): string;

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName(): string;

    /**
     * Get firstLastName
     *
     * @return string
     */
    public function getFirstLastName(): string;

    /**
     * Get lastFirstName
     *
     * @return string
     */
    public function getLastFirstName(): string;
}