<?php

namespace Mindlahus\SymfonyAssets\AbstractInterface;

interface ControlFieldInterface
{
    /**
     * @return $this
     */
    public function setControlField();

    /**
     * @return string
     */
    public function getControlField(): string;
}