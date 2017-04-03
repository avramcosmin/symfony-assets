<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileTrait
{
    /**
     * @param string $baseName
     * @param string $glue
     * @return string
     * @throws \Exception
     */
    public static function sanitizeBaseName(string $baseName, string $glue = '_')
    {
        $extension = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));
        if (empty($extension)) {
            throw new \Exception("Expecting extension. None given.");
        }

        $fileName = pathinfo($baseName, PATHINFO_FILENAME);
        if (empty($fileName)) {
            throw new \Exception("Expecting file name. None given.");
        }

        return StringTrait::sanitizeString($fileName, $glue) . '.' . $extension;
    }

    /**
     * @param $file
     * @return string
     * @throws \Exception
     */
    public static function getFileExtension(File $file)
    {
        if ($file instanceof UploadedFile) {
            $fileName = $file->getClientOriginalName();
        } elseif ($file instanceof File) {
            $fileName = $file->getFilename();
        } else {
            throw new \Exception('Not an instance of file');
        }

        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }
}