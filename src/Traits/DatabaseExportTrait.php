<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Symfony\Component\OptionsResolver\OptionsResolver;

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
     *  keep_tmp                optional    string
     * ]
     *
     * @param array $options
     * @return string
     * @throws \Throwable
     */
    public static function execute(array $options = []): string
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
            'allowed_table_prefixes',
            'keep_tmp'
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
            ->setDefault('keep_tmp', false)
            ->setAllowedTypes('database_name', ['string'])
            ->setAllowedTypes('database_user', ['string'])
            ->setAllowedTypes('database_password', ['string'])
            ->setAllowedTypes('export_as', ['string'])
            ->setAllowedTypes('dist_dir', ['string'])
            ->setAllowedTypes('tmp_dir', ['string'])
            ->setAllowedTypes('archive_name', ['string'])
            ->setAllowedTypes('allowed_table_prefixes', ['array'])
            ->setAllowedTypes('keep_tmp', ['boolean'])
            ->setAllowedValues('export_as', ['csv', 'sql']);
        $options = $resolver->resolve($options);

        try {
            $cmd = 'bash '
                . dirname(__DIR__) . '/../bin/database-dumper.sh'
                . ' -d ' . $options['database_name']
                . ' -u ' . $options['database_user']
                . ' -p\'' . $options['database_password'] . '\''
                . ' -e ' . $options['export_as']
                . ' --dist ' . $options['dist_dir']
                . ' --tmp ' . $options['tmp_dir']
                . ' --name ' . $options['archive_name']
                . ' --prefixes ' . implode(',', $options['allowed_table_prefixes']);
            if ($options['keep_tmp'] === true) {
                $cmd .= ' --keep-tmp';
            }
            shell_exec($cmd);
        } catch (\Throwable $e) {
            throw new \ErrorException('Failed to executing `database-dumper.sh`.', 0, $e);
        }

        switch (true) {
            case $options['export_as'] === 'csv':
                $ext = '.zip';
                break;
            case $options['export_as'] === 'sql':
                $ext = '.sql.gz';
                break;
            default:
                $ext = '';
        }

        $filePath = $options['dist_dir'] . $options['archive_name'] . $ext;
        if (!file_exists($filePath)) {
            throw new \ErrorException('Database Dump failed. No dump file!');
        }

        return $filePath;
    }
}