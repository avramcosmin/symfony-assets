<?php

namespace Mindlahus\SymfonyAssets\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NotFoundException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null, array $headers = [], int $code = 0)
    {
        parent::__construct(404, $message ?? 'Not Found!', $previous, $headers, $code);
    }
}