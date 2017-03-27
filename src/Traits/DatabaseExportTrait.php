<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\ORM\Query;
use Mindlahus\SymfonyAssets\Helper\DownloadHelper;
use Mindlahus\SymfonyAssets\Helper\StringHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

trait DatabaseExportTrait
{
    /**
     * $options = [
     *  database_name           required    string
     *  database_username       required    string
     *  database_password       required    string
     *  export_as               required    string
     *  dist_dir                required    string
     *  tmp_dir                 required    string
     *  archive_name            required    string
     *  allowed_table_prefixes  required    string
     * ]
     *
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public static function execute(array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            'database_name',
            'database_user',
            'database_password',
            'export_as',
            'dist_dir',
            'tmp_dir',
            'archive_name',
            'allowed_table_prefixes'
        ])
            ->setRequired([
                'database_name',
                'database_user',
                'database_password',
                'export_as',
                'dist_dir',
                'tmp_dir',
                'archive_name',
                'allowed_table_prefixes'
            ])
            ->setAllowedTypes('database_name', ['string'])
            ->setAllowedTypes('database_user', ['string'])
            ->setAllowedTypes('database_password', ['string'])
            ->setAllowedTypes('export_as', ['string'])
            ->setAllowedTypes('dist_dir', ['string'])
            ->setAllowedTypes('tmp_dir', ['string'])
            ->setAllowedTypes('archive_name', ['string'])
            ->setAllowedTypes('allowed_table_prefixes', ['array'])
            ->setAllowedValues('export_as', array('csv', 'sql'));
        $options = $resolver->resolve($options);

        try {
            shell_exec(
                'bash '
                . dirname(__DIR__) . '/../bin/database-dumper.sh'
                . ' -d ' . $options['database_name']
                . ' -u ' . $options['database_user']
                . ' -p\'' . $options['database_password'] . '\''
                . ' -e ' . $options['export_as']
                . ' --dist ' . $options['dist_dir']
                . ' --tmp ' . $options['tmp_dir']
                . ' --name ' . $options['archive_name']
                . ' --prefixes ' . implode('|', $options['allowed_table_prefixes'])
            );
        } catch (\Throwable $e) {
            throw new \Exception('Failed to executing `database-dumper.sh`.', 0, $e);
        }

        switch (true) {
            case $options['export_as'] === 'csv':
                $ext = '.zip';
                break;
            case $options['export_as'] === 'sql':
                $ext = '.gz';
                break;
            default:
                $ext = '';
        }

        $filePath = $options['dist_dir'] . $options['archive_name'] . $ext;
        if (!file_exists($filePath)) {
            throw new \Exception('Database Dump failed. No dump file!');
        }

        return $filePath;
    }

    /**
     * @param Query $entities
     * @param array $header
     * @param array $cols
     * @param string|null $filePath Full path including the file name
     * @return string
     */
    public static function entitiesToCSV(Query $entities, array $header, array $cols, string $filePath = null)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $handler = $filePath ? fopen($filePath, 'w+') : fopen('php://output', 'r+');
        fputcsv($handler, $header);
        foreach ($entities->execute() as $entity) {
            fputcsv($handler, static::_mapCSV($entity, $cols, $accessor));
        }
        fclose($handler);

        return $handler;
    }

    /**
     * @param Query $entities
     * @param array $header
     * @param array $cols
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function inMemoryEntitiesToCSV(Query $entities, array $header, array $cols, string $fileName)
    {
        return DownloadHelper::streamResponse(
            new StreamedResponse(function () use ($entities, $header, $cols) {
                return static::entitiesToCSV($entities, $header, $cols);
            }),
            $fileName
        );
    }

    /**
     * @param $entity
     * @param array $cols
     * @param PropertyAccessor $accessor
     * @return array
     */
    public static function _mapCSV($entity, array $cols, PropertyAccessor $accessor)
    {
        $response = [];
        foreach ($cols as $col) {
            $val = $accessor->getValue($entity, $col);
            if (is_bool($val)) {
                $response[] = (filter_var($val, FILTER_VALIDATE_BOOLEAN) === false ? 'NO' : 'YES');
            } else if ($val instanceof \DateTime) {
                $response[] = StringHelper::dateFormat($val);
            } else if (is_null($val)) {
                $response[] = '';
            } else {
                $response[] = $val;
            }
        }
        return $response;
    }
}