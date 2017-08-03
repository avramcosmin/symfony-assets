<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * Returns \stdClass()
     * [
     *  status      HTTP status code
     *  code        null
     *  type        null
     *  content     []|\stdClass()
     *  idx         random string
     * ]
     *
     * @param $data
     * @param ViewHandlerInterface $viewHandler
     * @param array $groups
     * @param int $statusCode
     * @param string|null $location
     * @return Response
     * @throws \Throwable
     */
    public static function Serialize(
        $data,
        ViewHandlerInterface $viewHandler,
        array $groups = [],
        int $statusCode = 200,
        string $location = null
    ): Response
    {
        $view = new View();
        $view->setStatusCode($statusCode);
        if ($view->getStatusCode() !== Response::HTTP_NO_CONTENT) {
            $view->setData([
                'data' => [
                    'status' => $view->getStatusCode(),
                    'code' => null,
                    'type' => null,
                    'idx' => bin2hex(random_bytes(5)),
                    'content' => $data
                ]
            ]);
            if (!empty($groups)) {
                $view->getContext()->setGroups($groups);
            }
        }
        if ($location) {
            $view->setLocation($location);
        }
        $view->setHeaders(ResponseHelper::CORS_HEADERS);
        $view->setFormat('json');

        return $viewHandler->handle($view);
    }
}