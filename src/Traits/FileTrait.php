<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileTrait
{
    /**
     * @param string $baseName
     * @param string $glue
     * @return string
     * @throws \Throwable
     */
    public static function sanitizeBaseName(string $baseName, string $glue = '_'): string
    {
        $extension = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
        if (empty($extension)) {
            throw new \Exception('Expecting extension. None given.');
        }

        $fileName = pathinfo($baseName, PATHINFO_FILENAME);
        if (empty($fileName)) {
            throw new \Exception('Expecting file name. None given.');
        }

        return StringTrait::sanitizeString($fileName, $glue) . '.' . $extension;
    }

    /**
     * @param \SplFileInfo $file
     * @return string
     */
    public static function getFileExtension(\SplFileInfo $file): string
    {
        if ($file instanceof UploadedFile) {
            $fileName = $file->getClientOriginalName();
        } else {
            $fileName = $file->getFilename();
        }

        return static::getExtension($fileName);
    }

    /**
     * @param string $filePath
     * @return string
     */
    public static function getExtension(string $filePath): string
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * @param string $filePath
     * @return string
     */
    public static function getFileBaseName(string $filePath): string
    {
        return pathinfo($filePath, PATHINFO_BASENAME);
    }
}