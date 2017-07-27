<?php

namespace Mindlahus\SymfonyAssets\Exception;

use Mindlahus\SymfonyAssets\Helper\ThrowableHelper;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailedException extends HttpException
{
    public function __construct(
        ConstraintViolationListInterface $errors,
        \Exception $previous = null,
        array $headers = []
    )
    {
        $data = [
            'message' => 'Validation failed',
            'errors' => []
        ];

        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $data['errors'][] = [
                'propertyPath' => $error->getPropertyPath(),
                'message' => $error->getMessage()
            ];
        }

        parent::__construct(
            400,
            json_encode($data),
            $previous,
            $headers,
            ThrowableHelper::VALIDATION_FAILED
        );
    }
}