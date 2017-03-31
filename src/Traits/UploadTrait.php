<?php

namespace Mindlahus\SymfonyAssets\Traits;

trait UploadTrait
{
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
}