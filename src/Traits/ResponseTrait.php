<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * For PUT/POST requests that upload files
     * make sure you pass the Request $request argument
     * but only after you call RequestHelper::initialize()
     *
     * Returns \stdClass()
     * {
     *      code,
     *      data (array, \stdClass)
     * }
     *
     * @param $data
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @param int $statusCode
     * @param Request $request Pass this in case of a PUT request
     * @return Response
     */
    public static function Serialize(
        $data,
        ViewHandler $viewHandler,
        array $groups = [],
        int $statusCode = 200,
        Request $request = null
    ): Response
    {
        $view = new View();
        $view->setStatusCode($statusCode);
        if ($view->getStatusCode() !== Response::HTTP_NO_CONTENT) {
            $view->setData(['data' => $data]);
            if (!empty($groups)) {
                $view->getContext()->setGroups($groups);
            }
        }
        $view->setHeaders(ResponseHelper::CORS_HEADERS);
        $view->setFormat('json');

        return $viewHandler->handle($view, $request);
    }
}