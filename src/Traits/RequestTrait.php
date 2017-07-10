<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Listener\HttpPutStreamListener;
use Symfony\Component\HttpFoundation\Request;

trait RequestTrait
{
    /**
     * @param Request $request
     */
    public static function initialize(Request $request): void
    {
        /**
         * Do this only if the Request $request is passed by the container
         * and only if it is the case of a PUT request
         */
        if (($_SERVER['CONTENT_TYPE'] ?? null) && !static::isJSON($request)) {
            /**
             * The scope is only to properly initialize the Request $request on PUT requests
             */
            $httpPutStreamListener = new HttpPutStreamListener();
            $data = $httpPutStreamListener->getData(
                $request
            );
            if (!$data['isEmptyPutStream']) {
                $request->initialize(
                    [],
                    $data['request'],
                    [],
                    [],
                    $data['files']
                );
                $request->setRequestFormat('json');
            }
        }
    }

    /**
     * @param Request $request
     * @param bool $reInitialize
     * @return mixed|\stdClass
     */
    public static function getContent(Request $request, $reInitialize = false)
    {
        /**
         * GET, DELETE request
         */
        if (static::isJSON($request)) {
            return json_decode($request->getContent());
        }

        if ($reInitialize === true) {
            static::initialize($request);
        }

        /**
         * POST, PUT request with FILE
         * PUT request content is prepared by static::initialize()
         */
        if ($request->request->has('jsonContent')) {
            return json_decode($request->request->get('jsonContent'));
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