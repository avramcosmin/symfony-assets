<?php

namespace Tests\Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Traits\FileTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class staticTest extends WebTestCase
{
    use FileTrait;
    
    public function testSanitizeBaseName()
    {
        $this->assertEquals('some_base_name.txt', static::sanitizeBaseName(
            'some base-name.txt'
        ));

        $this->assertEquals('some-base-name.txt', static::sanitizeBaseName(
            'some base---name.txt',
            '-'
        ));

        $this->assertEquals('some-base-n-aame.txt', static::sanitizeBaseName(
            'some base---nàăme.txt',
            '-'
        ));
    }

    public function testGetFileExtension()
    {
        $this->assertEquals('xlsx', static::getFileExtension(
            new \SplFileInfo(sys_get_temp_dir() . '/test.xlsx')
        ));
    }

    public function testGetExtension()
    {
        $this->assertEquals('xlsx', static::getExtension('C:\\Users\\Some User\\file.xlsx'));
    }

    public function testGetFileBaseName()
    {
        $this->assertEquals('test.jpg', static::getFileBaseName('/var/www/test.jpg'));
    }
}
