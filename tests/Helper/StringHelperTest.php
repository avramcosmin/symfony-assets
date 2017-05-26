<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Helper\StringHelper;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StringHelperTest extends WebTestCase
{
    /**
     * @var StringHelper
     */
    private $stringHelper;

    public function setUp(): void
    {
        $this->stringHelper = new StringHelper();
    }

    /**
     * @param array $assertions
     * @param string $method
     * @param string $test
     */
    private function _assert(
        array $assertions,
        string $method,
        string $test
    ): void
    {
        foreach ($assertions as $arr) {
            call_user_func_array(
                [$this, $test],
                [
                    $arr[0],
                    forward_static_call([StringHelper::class, $method], $arr[1])]
            );
        }
    }

    /**
     * @group parsedownExtra
     */
    public function testParsedownExtra(): void
    {
        $this->assertEquals(
            '<p>6554</p>',
            StringHelper::parsedownExtra('6554')
        );
    }

    /**
     * @group shortenThis
     */
    public function testShortenThis(): void
    {
        $this->assertEquals(
            'Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been…',
            StringHelper::shortenThis(
                'Lorem Ipsum is simply dummy text of the printing and type setting industry. Lorem Ipsum has been the industry',
                95
            )
        );
    }

    /**
     * @group dateFormat
     */
    public function testDateFormat()
    {
        $this->_assert(
            [
                ['2011-01-01T15:03:01+01:00', new \DateTime('01-01-2011T15:03:01+01:00')]
            ],
            'dateFormat',
            'assertEquals'
        );
        $this->assertEquals(
            'Monday, 15-Aug-2005 15:52:01 UTC',
            StringHelper::dateFormat(new \DateTime('15-08-2005T15:52:01'), \DateTime::COOKIE)
        );
    }

    /**
     * @group toCamelCase
     */
    public function testToCamelCase(): void
    {
        $this->_assert(
            [
                ['SomeProperty', 'Some property'],
                ['SomeProperty', 'Some Property'],
                ['SomeProperty', 'Some-property'],
                ['SomeProperty', 'Some-Property'],
                ['SomeProperty', 'Some property']
            ],
            'toCamelCase',
            'assertEquals'
        );

        $this->assertEquals(
            'PrefixSomePropertySome',
            StringHelper::toCamelCase('Some-Property-Some', 'prefix')
        );
    }

    /**
     * @group base64url_encode
     */
    public function testBase64url_encode(): void
    {
        $this->_assert(
            [
                ['VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw,,', 'This is an encoded string']
            ],
            'base64url_encode',
            'assertEquals'
        );
    }

    /**
     * @group base64url_decode
     */
    public function testBase64url_decode(): void
    {
        $this->_assert(
            [
                ['This is an encoded string', 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw,,']
            ],
            'base64url_decode',
            'assertEquals'
        );
    }

    /**
     * @group isFloat
     */
    public function testIsFloat(): void
    {
        $this->_assert(
            [
                [true, 5.00],
                [true, 1.3e4],
                [true, '5.00'],
                [false, 'some text'],
                [false, true],
            ],
            'isFloat',
            'assertEquals'
        );
    }

    /**
     * @group isInt
     */
    public function testIsInt(): void
    {
        $this->_assert(
            [
                [true, 5],
                [true, '5'],
                [false, '5.00'],
                [false, 'some text'],
                [false, true],
            ],
            'isInt',
            'assertEquals'
        );
    }

    /**
     * @group isDateTime
     */
    public function testIsDateTime(): void
    {
        $this->_assert(
            [
                [new \DateTime('2017-09-11'), new \DateTime('2017-09-11')],
                [new \DateTime('2017-09-11', new DateTimeZone('UTC')), '2017-09-11'],
                [false, 'some text']
            ],
            'isDateTime',
            'assertEquals'
        );
    }
}
