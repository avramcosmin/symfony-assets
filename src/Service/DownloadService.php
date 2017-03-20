<?php

namespace Mindlahus\SymfonyAssets\Service;

use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\Helper\ControllerHelper;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Traits\DownloadTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DownloadService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    use DownloadTrait;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Request $request
     * @param ViewHandler $viewHandler
     * @param string $encryptionKey
     * @param string|null $filePath
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jwtGetDownloadToken(
        Request $request,
        ViewHandler $viewHandler,
        string $encryptionKey,
        string $filePath = null
    )
    {
        $filePath = $filePath ?? $request->request->get('filePath');
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        return ControllerHelper::Serialize(
            CryptoHelper::encrypt(
                [
                    'file_path' => $filePath,
                    'jwt' => $jwt
                ],
                $encryptionKey
            ),
            $viewHandler
        );
    }

    /**
     * @param array $decryptedToken
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public function jwtForceDownload(array $decryptedToken)
    {
        if (time() > $decryptedToken['exp']) {
            throw new \Exception('Invalid download session!');
        }

        return $this->execute(
            $decryptedToken['file_path'],
            null,
            true
        );
    }
}