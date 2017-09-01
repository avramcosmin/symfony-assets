<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\Exception\NotFoundException;
use Mindlahus\SymfonyAssets\Traits\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerHelper
{
    use ControllerTrait;

    /**
     * @param string $entityResourceClass
     * @param string $entityClass
     * @param Request $request
     * @param ContainerInterface $container
     * @param array $groups
     * @param string $method
     * @param int $statusCode
     * @return Response
     * @throws \Throwable
     */
    public static function ReflectionCreateAndSerialize(
        string $entityResourceClass,
        string $entityClass,
        Request $request,
        ContainerInterface $container,
        array $groups = [],
        string $method = 'create',
        int $statusCode = Response::HTTP_CREATED
    ): Response
    {
        $em = $container->get('doctrine.orm.entity_manager');
        return static::CreateAndSerialize(
            new $entityResourceClass(
                $request,
                $em,
                $container->get('logger')
            ),
            new $entityClass(),
            $container->get('validator'),
            $em,
            $container->get('fos_rest.view_handler'),
            $method,
            $groups,
            $statusCode
        );
    }

    /**
     * @param int $id
     * @param string $entityResourceClass
     * @param string $entityClass
     * @param Request $request
     * @param ContainerInterface $container
     * @param array $groups
     * @param string $method
     * @param int $statusCode
     * @return Response
     * @throws \Throwable
     */
    public static function ReflectionChangeAndSerialize(
        int $id,
        string $entityResourceClass,
        string $entityClass,
        Request $request,
        ContainerInterface $container,
        array $groups = [],
        string $method = 'change',
        int $statusCode = Response::HTTP_OK
    ): Response
    {
        /**
         * @var $em ObjectManager
         */
        $em = $container->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository($entityClass)
            ->findOneBy([
                'id' => $id
            ]);

        if (!$entity) {
            throw new NotFoundException('Not found! Resource with id[' . $id . '] does not exist.');
        }

        return static::ChangeAndSerialize(
            new $entityResourceClass(
                $request,
                $em,
                $container->get('logger')
            ),
            $entity,
            $container->get('validator'),
            $em,
            $container->get('fos_rest.view_handler'),
            $method,
            $groups,
            $statusCode
        );
    }
}