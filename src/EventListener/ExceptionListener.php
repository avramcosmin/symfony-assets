<?php

namespace Mindlahus\SymfonyAssets\EventListener;

use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Sources:
 *  https://inviqa.com/blog/custom-error-handling-symfony
 *  https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Controller/ExceptionController.php
 */
class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        $code = $this->getStatusCode($exception);
        $message = $exception->getMessage();
        if ($exception instanceof ValidationFailedException) {
            $message = json_decode($message);
        }

        $responseData = [
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];

        $event->setResponse(new JsonResponse($responseData, $code));
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