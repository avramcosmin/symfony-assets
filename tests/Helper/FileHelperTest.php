<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Helper\FileHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FileHelperTest extends WebTestCase
{
    public function testSanitizeBaseName()
    {
        $this->assertEquals('some_base_name.txt', FileHelper::sanitizeBaseName(
            'some base-name.txt'
        ));

        $this->assertEquals('some-base-name.txt', FileHelper::sanitizeBaseName(
            'some base---name.txt',
            '-'
        ));

        $this->assertEquals('some-base-n-aame.txt', FileHelper::sanitizeBaseName(
            'some base---nàăme.txt',
            '-'
        ));
    }

    public function testGetFileExtension()
    {
        $this->assertEquals('xlsx', FileHelper::getFileExtension(
            new \SplFileInfo(sys_get_temp_dir() . '/test.xlsx')
        ));
    }

    public function testGetExtension()
    {
        $this->assertEquals('xlsx', FileHelper::getExtension('C:\\Users\\Some User\\file.xlsx'));
    }

    public function testGetFileBaseName()
    {
        $this->assertEquals('test.jpg', FileHelper::getFileBaseName('/var/www/test.jpg'));
    }
}
