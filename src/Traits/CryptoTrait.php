<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Helper\StringHelper;
use phpseclib\Crypt\AES;

trait CryptoTrait
{
    /**
     * @param string $str
     * @param string $key
     * @param bool $base64Encode
     * @return string
     * @throws \Throwable
     */
    public static function encryptAES(
        string $str,
        string $key,
        bool $base64Encode = false
    ): string
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
     * @param string|null $str
     * @param string $key
     * @param bool $base64Decode
     * @return string|null
     * @throws \Throwable
     */
    public static function decryptAES(
        string $str,
        string $key,
        bool $base64Decode = false
    ):? string
    {
        $cipher = new AES();
        $cipher->setKey($key);

        if ($base64Decode === true) {
            $str = StringHelper::base64url_decode($str);
        }

        return $str ? $cipher->decrypt($str) : null;
    }

    /**
     * @param array $payload
     * @param string $key
     * @param int $expires
     * @return string
     * @throws \Throwable
     */
    public static function encryptArrayToBase64(
        array $payload,
        string $key,
        int $expires = 300
    ): string
    {
        $cipher = new AES();
        $cipher->setKey($key);
        $time = time();

        $str = StringHelper::base64url_encode(
            array_merge([
                'exp' => $time + $expires,
                'iat' => $time
            ], $payload),
            true
        );

        return StringHelper::base64url_encode($cipher->encrypt($str));
    }

    /**
     * @param string $str
     * @param string $key
     * @return array Encrypted with $this->encrypt()
     * @throws \Throwable
     */
    public static function decryptBase64ToArray(string $str, string $key): array
    {
        $cipher = new AES();
        $cipher->setKey($key);

        return StringHelper::base64url_decode(
            $cipher->decrypt(StringHelper::base64url_decode($str)),
            true
        );
    }
}