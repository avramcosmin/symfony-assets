<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract;
use Mindlahus\SymfonyAssets\Exception\ValidationFailedException;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ControllerTrait
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

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @param int|null $statusCode
     * @return Response
     */
    public static function CreateAndSerialize(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        ObjectManager $em,
        ViewHandler $viewHandler,
        array $groups = [],
        int $statusCode = Response::HTTP_CREATED
    ): Response
    {
        return ResponseHelper::Serialize(
            static::EntityCreate(
                $entityResource,
                $method,
                $entity,
                $validator,
                $em
            ),
            $viewHandler,
            $groups,
            $statusCode
        );
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param string $method
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @param int|null $statusCode
     * @return Response
     */
    public static function ChangeAndSerialize(
        ResourceAbstract $entityResource,
        string $method,
        $entity,
        ValidatorInterface $validator,
        ObjectManager $em,
        ViewHandler $viewHandler,
        array $groups = [],
        int $statusCode = null
    ): Response
    {
        return ResponseHelper::Serialize(
            static::EntityChange(
                $entityResource,
                $method,
                $entity,
                $validator,
                $em
            ),
            $viewHandler,
            $groups,
            $statusCode
        );
    }

    /**
     * @param $entity
     * @param ObjectManager $em
     * @param ViewHandler $viewHandler
     * @param array $groups
     * @return Response
     */
    public static function RemoveAndSerialize(
        $entity,
        ObjectManager $em,
        ViewHandler $viewHandler,
        array $groups = []
    ): Response
    {
        static::EntityRemove($entity, $em);

        return ResponseHelper::Serialize(
            [],
            $viewHandler,
            $groups,
            Response::HTTP_NO_CONTENT
        );
    }
}