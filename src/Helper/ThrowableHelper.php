<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Traits\Exception\ThrowableTrait;

class ThrowableHelper
{
    public const NO_ERROR_CODE = 1000;
    public const VALIDATION_FAILED = 2001;

    use ThrowableTrait;
}