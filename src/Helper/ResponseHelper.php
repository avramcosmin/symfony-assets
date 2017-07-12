<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Traits\ResponseTrait;

class ResponseHelper
{
    public const CORS_HEADERS = [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, DELETE, PUT, PATCH, OPTIONS'
    ];

    use ResponseTrait;
}