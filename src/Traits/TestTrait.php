<?php

namespace Mindlahus\SymfonyAssets\Traits;

trait TestTrait
{
    /**
     * Tears down the Mockery framework
     */
    public function tearDown(): void
    {
        \Mockery::close();
    }
}