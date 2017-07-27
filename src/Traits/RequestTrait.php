<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Symfony\Component\HttpFoundation\Request;

trait RequestTrait
{
    /**
     * @param Request $request
     * @return \stdClass
     */
    public static function getContent(Request $request): \stdClass
    {
        /**
         * GET, DELETE request
         */
        if (static::isJSON($request)) {
            return json_decode($request->getContent());
        }

        return new \stdClass();
    }

    /**
     * @param Request $request
     * @return bool
     */
    private static function isJSON(Request $request): bool
    {
        return method_exists($request, 'getContentType')
            &&
            $request->getContentType() === 'json';
    }
}