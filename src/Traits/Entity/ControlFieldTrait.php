<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

trait ControlFieldTrait
{

    /**
     * @var string $controlField
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $controlField;

    /**
     * @return $this
     */
    public function setControlField()
    {
        $this->controlField = bin2hex(random_bytes(20));

        return $this;
    }

    /**
     * @return string
     */
    public function getControlField(): string
    {
        return $this->controlField;
    }

}