<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Mindlahus\SymfonyAssets\Traits\Entity\EntityTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ControllerTrait
{
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
            EntityTrait::EntityCreate(
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
            EntityTrait::EntityChange(
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
        EntityTrait::EntityRemove($entity, $em);

        return ResponseHelper::Serialize(
            [],
            $viewHandler,
            $groups,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @param int $exp
     * @param string $filePath
     * @param string|null $fileName
     * @param bool $deleteOnCompleted
     * @param bool $inlineDisposition
     * @param bool $knownSize
     * @return BinaryFileResponse
     */
    public static function jwtStreamOrDownloadFileFromPath(
        int $exp,
        string $filePath,
        string $fileName = null,
        bool $deleteOnCompleted = true,
        bool $inlineDisposition = true,
        bool $knownSize = true
    ): BinaryFileResponse
    {
        DownloadTrait::jwtIsValidSession($exp);

        return DownloadTrait::StreamOrDownloadFileFromPath(
            $filePath,
            $fileName,
            $deleteOnCompleted,
            $inlineDisposition,
            $knownSize

        );
    }

    /**
     * @param int $exp
     * @param string $octetStream
     * @param string $fileName
     * @param bool $inlineDisposition
     * @return StreamedResponse
     */
    public static function jwtStreamOrDownloadOctetStream(
        int $exp,
        string $octetStream,
        string $fileName,
        bool $inlineDisposition = true
    ): StreamedResponse
    {
        DownloadTrait::jwtIsValidSession($exp);

        return DownloadTrait::StreamOrDownloadOctetStream(
            $octetStream,
            $fileName,
            $inlineDisposition
        );
    }

    /**
     * @param int $exp
     * @param Response $response
     * @param string $fileName
     * @return Response
     */
    public static function jwtForceDownload(
        int $exp,
        Response $response,
        string $fileName
    ): Response
    {
        DownloadTrait::jwtIsValidSession($exp);

        return DownloadTrait::ForceDownload($response, $fileName);
    }
}