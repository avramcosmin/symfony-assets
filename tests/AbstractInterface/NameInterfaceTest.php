<?php

namespace Tests\Mindlahus\SymfonyAsstes\AbstractInterface;

use Mindlahus\SymfonyAssets\AbstractInterface\NameInterface;
use PHPUnit\Framework\TestCase;

class NameInterfaceTest extends TestCase
{
    public function testClassHasMethods(): void
    {
        $classMethods = get_class_methods(NameInterface::class);
        foreach ([
                     'setFirstName',
                     'setLastName',
                     'setFirstLastName',
                     'setLastFirstName',
                     'getFirstName',
                     'getLastName',
                     'getFirstLastName',
                     'getLastFirstName',
                 ] as $method) {
            $this->assertContains($method, $classMethods);
        }
    }
}