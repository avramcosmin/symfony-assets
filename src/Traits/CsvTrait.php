<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Mindlahus\SymfonyAssets\Helper\StringHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

trait CsvTrait
{
    /**
     * @param array $entities
     * @param array $header
     * @param array $cols
     * @param string|null $filePath Full path including the file name
     * @return string
     */
    public static function entitiesToCSV(
        array $entities,
        array $header,
        array $cols,
        string $filePath = null
    ): string
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $handler = $filePath ? fopen($filePath, 'bw+') : fopen('php://output', 'br+');
        fputcsv($handler, $header);
        foreach ($entities as $entity) {
            fputcsv($handler, static::_mapCSV($entity, $cols, $accessor));
        }
        fclose($handler);

        return $handler;
    }

    /**
     * @param $entity
     * @param array $cols
     * @param PropertyAccessor $accessor
     * @return array
     */
    public static function _mapCSV($entity, array $cols, PropertyAccessor $accessor): array
    {
        $response = [];
        foreach ($cols as $col) {
            $val = $accessor->getValue($entity, $col);
            if (is_bool($val)) {
                $response[] = (filter_var($val, FILTER_VALIDATE_BOOLEAN) === false ? 'NO' : 'YES');
            } else if ($val instanceof \DateTime) {
                $response[] = StringHelper::dateFormat($val);
            } else if ($val === null) {
                $response[] = '';
            } else {
                $response[] = $val;
            }
        }
        return $response;
    }
}