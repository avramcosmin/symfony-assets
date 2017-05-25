<?php

namespace Tests\Mindlahus\SymfonyAsstes\AbstractInterface;

use Mindlahus\SymfonyAssets\AbstractInterface\ControlFieldInterface;
use PHPUnit\Framework\TestCase;

class ControlFieldInterfaceTest extends TestCase
{
    public function testClassHasMethods(): void
    {
        $classMethods = get_class_methods(ControlFieldInterface::class);
        foreach ([
                     'setControlField',
                     'getControlField'
                 ] as $method) {
            $this->assertContains($method, $classMethods);
        }
    }
}
