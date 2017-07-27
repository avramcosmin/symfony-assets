<?php

namespace Mindlahus\SymfonyAssets\EventListener;

use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Mindlahus\SymfonyAssets\Helper\ThrowableHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Sources:
 *  https://inviqa.com/blog/custom-error-handling-symfony
 *  https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Controller/ExceptionController.php
 */
class ExceptionListener
{
    /**
     * Returns \stdClass()
     * [
     *  status      HTTP status code
     *  code        Exception code
     *  type        throwable|validation_errors|not_found
     *  content     [message (string), errors (array)]
     *  idx         random string
     * ]
     *
     * The client can use also the bad_request type
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        $statusCode = $this->getStatusCode($exception);
        $message = $exception->getMessage();
        $data = [
            'status' => $statusCode,
            'code' => $exception->getCode() ?? ThrowableHelper::NO_ERROR_CODE,
            'type' => 'throwable',
            'idx' => bin2hex(random_bytes(5)),
            'content' => [
                'message' => $message,
                'errors' => []
            ]
        ];

        if ($exception instanceof ValidationFailedException) {
            $data['content'] = json_decode($message);
            $data['type'] = 'validation_errors';
        }

        if ($statusCode === Response::HTTP_NOT_FOUND) {
            $data['type'] = 'not_found';
        }

        $event->setResponse(new JsonResponse(
            [
                'data' => $data
            ],
            $statusCode,
            ResponseHelper::CORS_HEADERS
        ));
    }

    /**
     * @param \Exception $exception
     * @return int
     */
    protected function getStatusCode(\Exception $exception): int
    {
        /**
         * todo : figure out the best option of getting the status code
         */
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}