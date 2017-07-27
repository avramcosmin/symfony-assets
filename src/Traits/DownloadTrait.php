<?php

namespace Mindlahus\SymfonyAssets\Traits;

use FOS\RestBundle\View\ViewHandler;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Helper\ResponseHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadTrait
{

    use FileTrait;

    use MimeTypeExtensionTrait;

    /**
     * This will send the file while the browser decides if to open or output for download.
     *
     * @param string $filePath
     * @param string|null $name
     * @param bool $deleteOnCompleted
     * @param bool $inlineDisposition
     * @param bool $knownSize
     * @return BinaryFileResponse
     */
    public static function StreamOrDownloadFileFromPath(
        string $filePath,
        string $name = null,
        bool $deleteOnCompleted = true,
        bool $inlineDisposition = true,
        bool $knownSize = true
    ): BinaryFileResponse
    {
        $name = $name ?: static::getFileBaseName($filePath);
        $stream = ($knownSize === true ? $filePath : new Stream($filePath));
        $response = new BinaryFileResponse($stream);

        $response->headers->set(
            'Content-Type',
            static::getMimeType(pathinfo($name, PATHINFO_EXTENSION))
        );
        $response->setContentDisposition(
            (
            $inlineDisposition === true
                ?
                ResponseHeaderBag::DISPOSITION_INLINE
                :
                ResponseHeaderBag::DISPOSITION_ATTACHMENT
            ),
            $name
        );

        if ($deleteOnCompleted === true) {
            $response->deleteFileAfterSend(true);
        }

        $response->send();

        return $response;
    }

    /**
     * http://stackoverflow.com/questions/17409115/return-image-from-controller-symfony2
     *
     * @param string $octetStream
     * @param string $fileName
     * @param bool $inlineDisposition
     * @return StreamedResponse
     */
    public static function StreamOrDownloadOctetStream(
        string $octetStream,
        string $fileName,
        bool $inlineDisposition = true
    ): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($octetStream) {
            $handle = fopen('php://output', 'br+');

            fwrite($handle, $octetStream);

            fclose($handle);
        });

        $response->headers->set(
            'Content-Type',
            MimeTypeExtensionTrait::getMimeType(
                static::getExtension($fileName)
            ));
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                (
                $inlineDisposition === true
                    ?
                    ResponseHeaderBag::DISPOSITION_INLINE
                    :
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT
                ),
                $fileName
            )
        );
        $response->send();

        return $response;
    }

    /**
     * https://www.layh.com/2014/04/18/symfony2-download-filestream-as-streamedresponse/
     * https://stackoverflow.com/questions/13010411/symfony2-force-file-download
     * https://stackoverflow.com/questions/39603052/symfony-download-files
     *
     * @param StreamedResponse|BinaryFileResponse|Response $response
     * @param string $fileName
     * @return Response
     */
    public static function ForceDownload(Response $response, string $fileName): Response
    {
        $response->headers->set(
            'Content-Type',
            'application/force-download'
        );
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileName
            )
        );
        $response->send();

        return $response;
    }

    /**
     * @param string $filePath
     * @param string $octetStream
     * @return string
     */
    public static function OctetStreamToTmpFile(string $filePath, string $octetStream): string
    {
        /**
         * make sure the directory exists to avoid errors
         */
        try {
            mkdir($filePath, 0777, true);
        } catch (\Throwable $e) {
        }
        $filePath = rtrim($filePath, '/') . '/' . bin2hex(random_bytes(20));
        file_put_contents($filePath, $octetStream);

        return $filePath;
    }

    /**
     * $tokenContent = [
     *  filePath            optional
     *  fileName            optional
     *  iat                 optional
     *  exp                 optional
     * ]
     *
     * @param ViewHandler $viewHandler
     * @param string $encryptionKey
     * @param array $tokenContent
     * @return Response
     * @throws \Throwable
     */
    public static function jwtGetDownloadToken(
        array $tokenContent,
        ViewHandler $viewHandler,
        string $encryptionKey
    ): Response
    {
        if (($tokenContent['filePath'] ?? null) && !file_exists($tokenContent['filePath'])) {
            throw new \Exception('File does not exist. No resources returned by the provided file path.');
        }
        return ResponseHelper::Serialize(
            [
                'downloadToken' => CryptoHelper::encryptArrayToBase64(
                    $tokenContent,
                    $encryptionKey
                )
            ],
            $viewHandler
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
        static::jwtIsValidSession($exp);

        return static::StreamOrDownloadFileFromPath(
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
        static::jwtIsValidSession($exp);

        return static::StreamOrDownloadOctetStream(
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
        static::jwtIsValidSession($exp);

        return static::ForceDownload($response, $fileName);
    }

    /**
     * @param int $time
     * @throws \Exception
     */
    public static function jwtIsValidSession(int $time): void
    {
        if (time() > $time) {
            throw new \Exception('Invalid download session!');
        }
    }
}