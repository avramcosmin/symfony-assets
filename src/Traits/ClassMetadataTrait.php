<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Helper\StringHelper;
use Symfony\Component\PropertyAccess\PropertyAccessor;

trait ClassMetadataTrait
{
    /**
     * @param array $classMetadata
     * @param string $class
     * @param ObjectManager $em
     * @param PropertyAccessor $accessor
     * @param array $selected_idx
     * @param string|null $alias
     * @param int $depth
     * @param array $exclusions
     */
    public static function getClassMetadata(
        array &$classMetadata,
        string $class,
        ObjectManager $em,
        PropertyAccessor $accessor,
        array $selected_idx = [],
        string $alias = null,
        $depth = 3,
        array $exclusions = [
            'createdBy',
            'createdAt',
            'updatedBy',
            'updatedAt',
            'controlField',
            'password',
            'passwordConfirmation'
        ]
    ): void
    {
        --$depth;

        /**
         * @var ClassMetadata|mixed $classMap
         */
        $classMap = $em->getClassMetadata($class);
        $reflectionClass = new \ReflectionClass($class);
        $name = $reflectionClass->getShortName();
        $alias = $alias ?: strtolower($name);
        if (!isset($classMetadata['initialized'])) {
            $classMetadata['initialized'] = true;
            $classMetadata['depth'] = $depth;
            $classMetadata['namespace'] = $class;
            $classMetadata['name'] = $name;
            $classMetadata['alias'] = $alias;
            $classMetadata['cols'] = [];
            $classMetadata['orderBy'] = null;
            $classMetadata['orderDir'] = 'desc';
            $classMetadata['associations'] = [];
            $classMetadata['cols_tt'] = [];
            $classMetadata['path_history'] = [];
            $classMetadata['path'] = [];
        }
        $classMetadata['path'][] = $alias;
        $glue = '_';
        $path = implode($glue, $classMetadata['path']);
        $prefix = $path . $glue;
        $classMetadata['path_history'][] = $path;
        $joinedAs = $glue . CryptoHelper::crc32Hash($path);
        if (!isset($classMetadata['joinedAs'])) {
            $classMetadata['joinedAs'] = $joinedAs;
        }

        foreach ($classMap->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $exclusions, false)) {
                continue;
            }
            $idx_raw = $prefix . $fieldName;
            $idx = $glue . CryptoHelper::crc32Hash($idx_raw);
            $fieldMap = $classMap->fieldMappings[$fieldName];
            if (!$classMetadata['orderBy'] && $fieldMap['id'] === true) {
                $classMetadata['orderBy'] = $joinedAs . '.' . $fieldName;
            }
            $fieldMap['entityNamespace'] = $class;
            $fieldMap['entityName'] = $name;
            $fieldMap['entityAlias'] = $alias;
            $fieldMap['path'] = $path;
            $fieldMap['glue'] = $glue;
            $fieldMap['prefix'] = $prefix;
            $fieldMap['joinedAs'] = $joinedAs;
            $fieldMap['joinedAsRaw'] = $path;
            $fieldMap['idx'] = $idx;
            $fieldMap['idxRaw'] = $idx_raw;
            $fieldMap['title'] = StringHelper::camelCaseToUCWords(
                ucwords(str_replace($glue, ' ', $idx_raw))
            );
            $fieldMap['depth'] = $classMetadata['depth'] - $depth;
            $classMetadata['cols'][$idx] = $fieldMap;
            $classMetadata['cols_tt'][$idx] = [
                'idx' => $idx,
                'title' => $fieldMap['title'],
                'visible' => !in_array($idx, $selected_idx, false)
            ];
        }

        $instance = $reflectionClass->newInstance();

        if ($depth >= 0) {
            foreach ($classMap->getAssociationNames() as $associationName) {
                if (
                    $accessor->getValue($instance, $associationName) instanceof ArrayCollection
                    ||
                    in_array($associationName, $exclusions, false)
                    ||
                    in_array($associationName, $classMetadata['path'], false)
                ) {
                    continue;
                }
                $idx_raw = $prefix . $associationName;
                $idx = $glue . CryptoHelper::crc32Hash($idx_raw);
                $associationMapping = $classMap->associationMappings[$associationName];
                $classMetadata['associations'][$idx] = [
                    'association' => $alias . '.' . $associationName,
                    'alias' => $idx
                ];
                static::getClassMetadata(
                    $classMetadata,
                    $associationMapping['targetEntity'],
                    $em,
                    $accessor,
                    $selected_idx,
                    $associationName,
                    $depth
                );
            }
        }

        array_pop($classMetadata['path']);
    }
}