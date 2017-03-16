<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Helper\StringHelper;
use phpseclib\Crypt\AES;

trait CryptoTrait
{
    /**
     * @param array $str
     * @param string $key
     * @param bool $base64Encode
     * @return string
     */
    public static function encryptAES(array $str, string $key, $base64Encode = false)
    {
        $cipher = new AES();
        $cipher->setKey($key);

        $str = $cipher->encrypt($str);

        if ($base64Encode === true) {
            return StringHelper::base64url_encode($str);
        }

        return $str;
    }

    /**
     * @param string $str
     * @param string $key
     * @param bool $base64Decode
     * @return string
     */
    public static function decryptAES(string $str, string $key, $base64Decode = false)
    {
        $cipher = new AES();
        $cipher->setKey($key);

        if ($base64Decode === true) {
            $str = StringHelper::base64url_decode($str);
        }

        return $cipher->decrypt($str);
    }

    /**
     * @param array $payload
     * @param string $key
     * @param int $expires
     * @return string
     */
    public static function encrypt(array $payload, string $key, int $expires = 300)
    {
        $cipher = new AES();
        $cipher->setKey($key);

        $str = StringHelper::base64url_encode((object)array_merge($payload, [
            'expires' => $expires,
            'created' => time()
        ]));

        return StringHelper::base64url_encode($cipher->encrypt($str), true);
    }

    /**
     * @param string $str
     * @param string $key
     * @return string
     */
    public static function decrypt(string $str, string $key)
    {
        $cipher = new AES();
        $cipher->setKey($key);

        return StringHelper::base64url_encode($cipher->decrypt(StringHelper::base64url_decode($str, true)));
    }
}