<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\View\ViewHandlerInterface;
use Mindlahus\SymfonyAssets\AbstractInterface\ResourceAbstract;
use Mindlahus\SymfonyAssets\Exception\NotFoundException;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Mindlahus\SymfonyAssets\Traits\Entity\EntityTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ControllerTrait
{
    /**
     * @param int $id
     * @param string $repository
     * @param ViewHandlerInterface $viewHandler
     * @param ObjectManager $em
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function FindOneByIdAndSerialize(int $id,
                                                   string $repository,
                                                   ViewHandlerInterface $viewHandler,
                                                   ObjectManager $em,
                                                   array $groups = []
    ): Response
    {
        return static::FindOneByAndSerialize(
            [
                'id' => $id
            ],
            $repository,
            $viewHandler,
            $em,
            $groups
        );
    }

    /**
     * @param array $findBy
     * @param string $repository
     * @param ViewHandlerInterface $viewHandler
     * @param ObjectManager $em
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function FindOneByAndSerialize(array $findBy,
                                                 string $repository,
                                                 ViewHandlerInterface $viewHandler,
                                                 ObjectManager $em,
                                                 array $groups = []
    ): Response
    {
        $entity = $em->getRepository($repository)->findOneBy($findBy);

        if (!$entity) {
            throw new NotFoundException();
        }

        return ResponseHelper::Serialize(
            $entity,
            $viewHandler,
            $groups
        );
    }

    /**
     * @param string $repository
     * @param ViewHandlerInterface $viewHandler
     * @param ObjectManager $em
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function GetManyAndSerialize(string $repository,
                                               ViewHandlerInterface $viewHandler,
                                               ObjectManager $em,
                                               array $groups = []
    ): Response
    {
        return ResponseHelper::Serialize(
            $em->getRepository($repository)->findAll(),
            $viewHandler,
            $groups
        );
    }

    /**
     * @param string $repository
     * @param ViewHandlerInterface $viewHandler
     * @param ObjectManager $em
     * @param array $findBy
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function FindManyByAndSerialize(string $repository,
                                                  ViewHandlerInterface $viewHandler,
                                                  ObjectManager $em,
                                                  array $findBy,
                                                  array $groups = []
    ): Response
    {
        return ResponseHelper::Serialize(
            $em->getRepository($repository)->findBy($findBy),
            $viewHandler,
            $groups
        );
    }

    /**
     * @param string $repository
     * @param ViewHandlerInterface $viewHandler
     * @param ObjectManager $em
     * @param string $method
     * @param array|null $params
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function FindUsingMethodAndSerialize(string $repository,
                                                       ViewHandlerInterface $viewHandler,
                                                       ObjectManager $em,
                                                       string $method,
                                                       array $params = null,
                                                       array $groups = []
    ): Response
    {
        if ($params) {
            $data = $em->getRepository($repository)->{$method}($params);
        } else {
            $data = $em->getRepository($repository)->{$method}();
        }
        return ResponseHelper::Serialize(
            $data,
            $viewHandler,
            $groups
        );
    }

    /**
     * @param ResourceAbstract $entityResource
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @param ViewHandlerInterface $viewHandler
     * @param string $method
     * @param array $groups
     * @param int|null $statusCode
     * @return Response
     * @throws \Throwable
     */
    public static function CreateAndSerialize(ResourceAbstract $entityResource,
                                              $entity,
                                              ValidatorInterface $validator,
                                              ObjectManager $em,
                                              ViewHandlerInterface $viewHandler,
                                              string $method = 'create',
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
     * @param $entity
     * @param ValidatorInterface $validator
     * @param ObjectManager $em
     * @param ViewHandlerInterface $viewHandler
     * @param string $method
     * @param array $groups
     * @param int|null $statusCode
     * @return Response
     * @throws \Throwable
     */
    public static function ChangeAndSerialize(ResourceAbstract $entityResource,
                                              $entity,
                                              ValidatorInterface $validator,
                                              ObjectManager $em,
                                              ViewHandlerInterface $viewHandler,
                                              string $method = 'change',
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
     * @param ViewHandlerInterface $viewHandler
     * @param array $groups
     * @return Response
     * @throws \Throwable
     */
    public static function RemoveAndSerialize($entity,
                                              ObjectManager $em,
                                              ViewHandlerInterface $viewHandler,
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
     * @throws \Throwable
     */
    public static function jwtStreamOrDownloadFileFromPath(int $exp,
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
     * @throws \Throwable
     */
    public static function jwtStreamOrDownloadOctetStream(int $exp,
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
     * @throws \Throwable
     */
    public static function jwtForceDownload(int $exp,
                                            Response $response,
                                            string $fileName
    ): Response
    {
        DownloadTrait::jwtIsValidSession($exp);

        return DownloadTrait::ForceDownload($response, $fileName);
    }

    /**
     * $payload = [
     *  iat                 optional
     *  exp                 optional
     * ]
     *
     * @param array $payload
     * @param string $encryptionKey
     * @param ViewHandlerInterface $viewHandler
     * @return Response
     * @throws \Throwable
     */
    public static function jwtGetEncryptedPayload(array $payload,
                                                  string $encryptionKey,
                                                  ViewHandlerInterface $viewHandler
    ): Response
    {
        return ResponseHelper::Serialize(
            [
                'encryptedPayload' => CryptoHelper::encryptArrayToBase64(
                    $payload,
                    $encryptionKey
                )
            ],
            $viewHandler
        );
    }
}