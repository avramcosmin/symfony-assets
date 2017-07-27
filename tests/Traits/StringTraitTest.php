<?php

namespace Tests\Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Traits\StringTrait;
use Mindlahus\SymfonyAssets\Traits\TestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StringTraitTest extends WebTestCase
{
    use TestTrait;
    use StringTrait;

    /**
     * @group shortenThis
     */
    public function testShortenThis(): void
    {
        $this->batchAssertEquals([
            [
                'Lorem Ipsum is simply dummy text of…',
                static::shortenThis(
                    '<p>Lorem Ipsum is simply dummy text of the printing and type setting industry.</p>',
                    35
                )
            ],
            [
                'Lorem Ipsum is simply dummy text.',
                static::shortenThis(
                    'Lorem Ipsum is simply dummy text.',
                    35
                )
            ]
        ]);
    }

    /**
     * @group parsedownExtra
     */
    public function testParsedownExtra(): void
    {
        $this->batchAssertEquals([
            [
                '',
                static::parsedownExtra()
            ],
            [
                '<p>Super Test!</p>',
                static::parsedownExtra('Super Test!')
            ]
        ]);
    }

    /**
     * @group dateFormat
     */
    public function testDateFormat()
    {
        $this->batchAssertEquals(
            [
                [
                    '2011-01-01T15:03:01+01:00',
                    static::dateFormat(new \DateTime('01-01-2011T15:03:01+01:00'))
                ],
                [
                    'Monday, 15-Aug-2005 15:52:01 UTC',
                    static::dateFormat(new \DateTime('15-08-2005T15:52:01'), \DateTime::COOKIE)
                ]
            ]
        );
    }

    /**
     * @group toCamelCase
     */
    public function testToCamelCase(): void
    {
        $this->batchAssertEquals(
            [
                ['SomeProperty',
                    static::toCamelCase('Some property')
                ],
                [
                    'SomeProperty',
                    static::toCamelCase('Some Property')
                ],
                [
                    'SomeProperty',
                    static::toCamelCase('Some-property')
                ],
                [
                    'SomeProperty',
                    static::toCamelCase('Some-Property')
                ],
                [
                    'SomeProperty',
                    static::toCamelCase('Some property')
                ],
                [
                    'PrefixSomePropertySome',
                    static::toCamelCase('Some-Property-Some', 'prefix')
                ]
            ]
        );
    }

    /**
     * @group base64url_encode
     */
    public function testBase64url_encode(): void
    {
        $this->batchAssertEquals(
            [
                [
                    'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw,,',
                    static::base64url_encode('This is an encoded string')
                ],
                [
                    'eyJhcHBsZSI6MywibGVtb24iOjV9',
                    static::base64url_encode(
                        ['apple' => 3, 'lemon' => 5],
                        true
                    )
                ]
            ]
        );
    }

    /**
     * @group base64url_decode
     */
    public function testBase64url_decode(): void
    {
        $this->batchAssertEquals(
            [
                [
                    'This is an encoded string',
                    static::base64url_decode('VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw,,')
                ],
                [
                    ['apple' => 3, 'lemon' => 5],
                    static::base64url_decode(
                        'eyJhcHBsZSI6MywibGVtb24iOjV9',
                        true
                    )
                ]
            ]
        );
    }

    /**
     * @group isFloat
     */
    public function testIsFloat(): void
    {
        $this->batchAssertEquals(
            [
                [1111.28983, static::isFloat('1,111.28983')],
                [12.0, static::isFloat(12)],
                [null, static::isFloat('Some Text!')],
                [null, static::isFloat(true)],
                [12.3, static::isFloat(12.3)],
                [14000.2, static::isFloat('14,000.2')]
            ]
        );
    }

    /**
     * @group isInt
     */
    public function testIsInt(): void
    {
        $this->batchAssertEquals(
            [
                [null, static::isInt(12.3)],
                [12, static::isInt('12')],
                [2, static::isInt(2)],
                [null, static::isInt('Some Text!')],
                [null, static::isInt(null)],
                [null, static::isInt(true)],
                [null, static::isInt('1790.23')],
                [null, static::isInt('1790.00')]
            ]
        );
    }

    /**
     * @group isDateTime
     */
    public function testIsDateTime(): void
    {
        $this->batchAssertEquals(
            [
                [
                    new \DateTime('2017-09-11'),
                    static::isDateTime(new \DateTime('2017-09-11'))
                ],
                [
                    new \DateTime('2017-09-11'),
                    static::isDateTime('2017-09-11')
                ],
                [
                    null,
                    static::isDateTime('23233fdg')
                ]
            ]
        );
    }

    /**
     * @group sanitizeString
     */
    public function testSanitizeString(): void
    {
        $this->batchAssertEquals(
            [
            [
                'su_uper_test',
                static::sanitizeString('suùper  test')
            ],
            [
                'u_a_ista_i_a',
                static::sanitizeString('ùàìșțăîâ')
            ]
        ]
        );
    }
}
