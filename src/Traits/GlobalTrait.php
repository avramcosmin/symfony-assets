<?php

namespace Mindlahus\SymfonyAssets\Traits;

trait GlobalTrait
{
    /**
     * @param $givenInstance
     * @param $expectedInstance
     * @return bool
     */
    public static function isInstanceOf($givenInstance, $expectedInstance): bool
    {
        return $givenInstance instanceof $expectedInstance;
    }
}