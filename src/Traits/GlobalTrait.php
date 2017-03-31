<?php

namespace Mindlahus\SymfonyAssets\Traits;

trait GlobalTrait
{
    /**
     * @param $givenInstance
     * @param $expectedInstance
     * @return bool
     */
    public static function isInstanceOf($givenInstance, $expectedInstance)
    {
        return $givenInstance instanceof $expectedInstance;
    }
}