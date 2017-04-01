<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\Helper\ControllerHelper;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait DownloadTrait
{
    /**
     * @param string $path
     * @param string|null $name
     * @param bool $deleteOnCompleted
     * @return BinaryFileResponse
     */
    public static function execute(
        string $path,
        string $name = null,
        bool $deleteOnCompleted = true)
    {
        $name = $name ?: pathinfo($path, PATHINFO_BASENAME);
        $response = new BinaryFileResponse($path);

        $response->setStatusCode(200);
        $response->headers->set(
            'Content-Type',
            VariablesMapTrait::getMimeType(pathinfo($name, PATHINFO_EXTENSION))
        );
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name
        );
        //$response->headers->set('Content-Disposition', '"attachment; filename=' . $name . ';"');
        //$response->send();

        if ($deleteOnCompleted === true) {
            $response->deleteFileAfterSend(true);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @param string $fileName
     * @return Response
     */
    public static function streamResponse(Response $response, string $fileName)
    {
        $response->setStatusCode(200);
        $response->headers->set(
            'Content-Type',
            'application/force-download'
        );
        $response->headers->set(
            'Content-Disposition',
            '"attachment; filename=' . $fileName . ';"'
        );
        // used for debug
        // $response->sendContent();
        $response->send();

        return $response;
    }

    /**
     * @param string $path
     * @param string $octetStream
     * @return string
     */
    public static function octetStreamToTmp(string $path, string $octetStream)
    {
        /**
         * make sure the directory exists to avoid errors
         */
        mkdir($path, 0777, true);
        $path = trim($path, '/') . '/' . bin2hex(random_bytes(20));
        file_put_contents($path, $octetStream);
        return $path;
    }

    /**
     * $tokenContent = [
     *  file_path           optional if ignore_file_path set boolean true
     *  file_name           optional
     *  direct_input        optional
     * ]
     *
     * @param Request $request
     * @param ViewHandler $viewHandler
     * @param string $encryptionKey
     * @param array $tokenContent
     * @return Response
     * @throws \Exception
     */
    public static function jwtGetDownloadToken(
        Request $request,
        ViewHandler $viewHandler,
        string $encryptionKey,
        array $tokenContent = []
    )
    {
        $tokenContent = array_merge($request->request->all(), $tokenContent);

        if (
            ($tokenContent['direct_input'] ?? null) !== true
            &&
            (
                !is_string($tokenContent['file_path'])
                ||
                !file_exists($tokenContent['file_path'])
            )
        ) {
            throw new \Exception('Invalid file path. String of valid file path expected.');
        }

        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        return ControllerHelper::Serialize(
            [
                'token' => CryptoHelper::encrypt(
                    array_merge(
                        [
                            'jwt' => $jwt
                        ],
                        $tokenContent
                    ),
                    $encryptionKey
                )
            ],
            $viewHandler
        );
    }

    /**
     * @param array $decryptedToken
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     */
    public static function jwtForceDownload(array $decryptedToken)
    {
        static::jwtIsValidSession($decryptedToken);

        return static::execute(
            $decryptedToken['file_path'],
            $decryptedToken['file_name'] ?? null,
            true
        );
    }

    /**
     * @param Response $response
     * @param array $decryptedToken
     * @return Response
     */
    public static function jwtStreamDownload(Response $response, array $decryptedToken)
    {
        static::jwtIsValidSession($decryptedToken);

        return static::streamResponse($response, $decryptedToken['file_name']);
    }

    /**
     * @param array $decryptedToken
     * @throws \Exception
     */
    public static function jwtIsValidSession(array $decryptedToken)
    {
        if (time() > $decryptedToken['exp']) {
            throw new \Exception('Invalid download session!');
        }
    }
}