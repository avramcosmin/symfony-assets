<?php

namespace Mindlahus\SymfonyAssets\Validator\Constraints;

use Mindlahus\SymfonyAssets\Helper\StringHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstrainsIntValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!StringHelper::isFloat($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}