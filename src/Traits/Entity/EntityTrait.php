<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract;
use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait EntityTrait
{
    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @return mixed
     */
    public static function EntityCreate(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        ObjectManager $em
    )
    {
        return static::EntityChangeOrCreate(
            $entityResource,
            $method,
            $entity,
            $validator,
            $em,
            true
        );
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @return mixed
     */
    public static function EntityChange(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        ObjectManager $em
    )
    {
        return static::EntityChangeOrCreate(
            $entityResource,
            $method,
            $entity,
            $validator,
            $em
        );
    }

    /**
     * @param $entity
     * @param ObjectManager $em
     */
    public static function EntityRemove(
        $entity,
        ObjectManager $em
    ): void
    {
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @param bool $persist
     * @return mixed
     */
    private static function EntityChangeOrCreate(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        ObjectManager $em,
        bool $persist = false
    )
    {
        $entityResource->{$method}($entity);
        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            throw new ValidationFailedException($errors);
        }

        if ($persist === true) {
            $em->persist($entity);
        }
        $em->flush();

        return $entity;
    }
}