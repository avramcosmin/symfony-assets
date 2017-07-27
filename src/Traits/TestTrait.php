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

    /**
     * @param array $assertions
     */
    private function batchAssertEquals(
        array $assertions
    ): void
    {
        foreach ($assertions as $arr) {
            $this->assertEquals($arr[0], $arr[1]);
        }
    }
}