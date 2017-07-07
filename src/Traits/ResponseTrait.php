<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * For PUT requests make sure you call RequestHelper::initialize() and then pass Request $request
     *
     * @param $data
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @param int|null $statusCode
     * @param Request $request Pass this in case of a PUT request
     * @return Response
     */
    public static function Serialize(
        $data,
        ViewHandler $viewHandler,
        array $groups = [],
        int $statusCode = null,
        Request $request = null
    ): Response
    {
        $view = new View();
        $view->setData(['data' => $data]);
        if (!empty($groups)) {
            $view->getContext()->setGroups($groups);
        }

        if ($statusCode) {
            $view->setStatusCode($statusCode);
        }

        return $viewHandler->handle($view, $request);
    }
}