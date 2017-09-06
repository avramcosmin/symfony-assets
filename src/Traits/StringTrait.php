<?php

namespace Mindlahus\SymfonyAssets\Traits;

use ForceUTF8\Encoding;

trait StringTrait
{
    /**
     * Shorten a string to the desired length.
     *
     * @param $str
     * @param int|\number $length
     * @return string
     * @throws \Throwable
     */
    public static function shortenThis($str, int $length = null): string
    {
        if (!$length) {
            $length = 255;
        }

        if ($length > 255) {
            throw new \Error('The length argument should be 255 or less.');
        }

        /**
         * [:space:] removes \t as well
         * http://stackoverflow.com/questions/2326125/remove-multiple-whitespaces
         * http://www.php.net/manual/en/regexp.reference.character-classes.php
         */
        $str = mb_ereg_replace(
            '[[:space:]]+',
            ' ',
            strip_tags($str),
            'ms'
        );
        $length = abs((int)$length - 1);

        /**
         * allow to only remove all special chars and html entities without shortening the string
         */
        if ($length === null) {
            return $str;
        }

        if (strlen($str) > $length) {
            $str = mb_ereg_replace(
                '^(.{1,' . $length . '})([^\s]+)(\s.*|$)',
                '\\1\\2…',
                $str, 'ms'
            );
        }

        return $str;
    }

    /**
     * @param string $str
     * @return string
     */
    public static function parsedownExtra(string $str = ''): string
    {
        $parsedownExtra = new \ParsedownExtra();

        return $parsedownExtra->text($str);
    }

    /**
     * \DateTime::ISO8601 is not compatible with the ISO8601 itself
     * For compatibility use \DateTime::ATOM or just c
     *
     * @param \DateTime $val
     * @param string $format
     * @return string
     */
    public static function dateFormat(\DateTime $val, string $format = \DateTime::ATOM): string
    {
        return $val->format($format);
    }


    /**
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public static function toCamelCase(string $str, string $prefix = null): string
    {
        $str = ucwords(strtolower(preg_replace('/[^A-z0-9]+/', ' ', $str)));

        if (!empty($prefix)) {
            $str = ucfirst(strtolower($prefix)) . $str;
        }

        return preg_replace('/\s+/', '', $str);
    }


    /**
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public static function camelCaseToUCWords(string $str, string $delimiter = ' '): string
    {
        return ucfirst(preg_replace('/(?<=[a-z])(?=[A-Z0-9])/', $delimiter, $str));
    }

    /**
     * @param string|array|\stdClass $str
     * @param bool $jsonEncode
     * @return string
     * @throws \Throwable
     */
    public static function base64url_encode($str, bool $jsonEncode = false): string
    {
        if (!is_string($str) && !is_array($str) && !$str instanceof \stdClass) {
            throw new \ErrorException('`str` should be a string, array or at most an instance of \stdClass().');
        }

        if ($jsonEncode === true) {
            $str = json_encode($str);

            if (!$str) {
                throw new \ErrorException('`base64url_encode()` failed to `json_encode()`.');
            }
        }

        $str = base64_encode($str);

        if (!$str) {
            throw new \ErrorException('`base64url_encode` failed to `base64_encode()`.');
        }

        return strtr($str, '+/=', '-_,');
    }

    /**
     * @param string|bool $str
     * @param bool $jsonDecode
     * @return mixed
     * @throws \Throwable
     */
    public static function base64url_decode(string $str, bool $jsonDecode = false)
    {
        $str = strtr($str, '-_,', '+/=');
        $str = base64_decode($str);

        if (!$str) {
            throw new \ErrorException('`base64url_decode` failed to `base64_decode()`.');
        }

        if ($jsonDecode === true) {
            $str = json_decode($str, true);

            if (!$str) {
                throw new \ErrorException('`base64url_decode()` failed to `json_encode()`.');
            }
        }

        return $str;
    }

    /**
     * @param $val
     * @return float|null
     */
    public static function isFloat($val):? float
    {
        // we do this because filter_var(true, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND) returns 1
        if ($val === true) {
            return null;
        }

        return filter_var(
            $val,
            FILTER_VALIDATE_FLOAT,
            FILTER_FLAG_ALLOW_THOUSAND | FILTER_NULL_ON_FAILURE
        );
    }

    /**
     * @param $val
     * @return bool|mixed
     */
    public static function isInt($val):? int
    {
        // we do this because filter_var(true, FILTER_VALIDATE_INT) returns 1
        if ($val === true) {
            return null;
        }

        return filter_var($val, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param $val
     * @return null|\DateTime
     */
    public static function isDateTime($val):? \DateTime
    {
        if ($val instanceof \DateTime) {
            return $val;
        }

        try {
            return new \DateTime($val, new \DateTimeZone('UTC'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $str
     * @param string $glue
     * @return string
     */
    public static function sanitizeString(string $str, string $glue = '_'): string
    {
        $str = Encoding::toUTF8($str);

        if (function_exists('iconv')) {
            $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        }

        $str = preg_replace('/[^a-zA-Z0-9]/', ' ', $str);
        $str = trim(preg_replace("/\\s+/", ' ', $str));
        $str = strtolower($str);

        return str_replace(' ', $glue, $str);
    }
}