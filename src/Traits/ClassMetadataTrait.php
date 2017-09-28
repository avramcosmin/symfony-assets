<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Helper\EntityQueryBuilderHelper;
use Mindlahus\SymfonyAssets\Helper\StringHelper;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraints\NotBlank;

trait ClassMetadataTrait
{
    public static $glue = '_';
    public static $selectAsAlias = 'idx'; // alternatively 'idx_raw'
    public static $orderDir = 'desc';

    private static $exclusions = [
        'createdBy',
        'createdAt',
        'updatedBy',
        'updatedAt',
        'controlField',
        'password',
        'passwordConfirmation'
    ];

    private static $forcedInclusions = [];

    /**
     * @param array $exclusions
     */
    public static function setExclusions(array $exclusions): void
    {
        static::$exclusions = $exclusions;
    }

    /**
     * @param array $exclusions
     */
    public static function addExclusions(array $exclusions): void
    {
        static::$exclusions = array_merge(static::$exclusions, $exclusions);
    }

    /**
     * @param array $forcedInclusions
     */
    public static function setForcedInclusions(array $forcedInclusions): void
    {
        static::$forcedInclusions = $forcedInclusions;
    }

    /**
     * @param array $classMetadata
     * @param string $class
     * @param EntityManagerInterface $em
     * @param PropertyAccessor $accessor
     * @param array $selectedIdxs
     * @param string|null $alias
     * @param int $depth
     * @throws \Throwable
     */
    public static function getClassMetadata(
        array &$classMetadata,
        string $class,
        EntityManagerInterface $em,
        PropertyAccessor $accessor,
        array $selectedIdxs = [],
        string $alias = null,
        $depth = 3
    ): void
    {
        --$depth;

        /**
         * @var ClassMetadata|mixed $classMap
         */
        $classMap = $em->getClassMetadata($class);
        $reflectionClass = new \ReflectionClass($class);
        // ClassMetadata
        $name = $reflectionClass->getShortName();
        $alias = $alias ?: strtolower($name);
        if (!isset($classMetadata['initialized'])) {
            $classMetadata['initialized'] = true;
            $classMetadata['depth'] = $depth;
            $classMetadata['namespace'] = $class;
            $classMetadata['name'] = $name;
            $classMetadata['alias'] = $alias;
            $classMetadata['orderBy'] = null; // path to a hashed property
            $classMetadata['orderDir'] = static::$orderDir;
            $classMetadata['cols'] = []; // all properties & associations with their respective metadata
            $classMetadata['cols_idx_raw'] = []; // store a copy of the col under idx_raw
            $classMetadata['cols_tt'] = []; // columns used by the table template engine
            $classMetadata['associations'] = [];
            $classMetadata['path_history'] = [];
            $classMetadata['path'] = []; // path to the properties of the class (should be the class alias)
        }
        $classMetadata['path'][] = $alias;
        $path = implode(static::$glue, $classMetadata['path']);
        $prefix = $path . static::$glue;
        $classMetadata['path_history'][] = $path;
        $joinedAs = static::hash($path);
        if (!isset($classMetadata['joinedAs'])) {
            $classMetadata['joinedAs'] = $joinedAs;
            $classMetadata['namespaceAndAlias'] = $class . ' ' . $joinedAs;
        }

        foreach ($classMap->getFieldNames() as $fieldName) {
            $idx_raw = $prefix . $fieldName;

            if (
                in_array($fieldName, static::$exclusions, false)
                &&
                !in_array($idx_raw, static::$forcedInclusions, false)
            ) {
                continue;
            }

            $idx = static::hash($idx_raw);
            $select = $joinedAs . '.' . $fieldName;
            $selectAsAlias = ${static::$selectAsAlias}; // alternatively $idx_raw
            $fieldMap = $classMap->fieldMappings[$fieldName];
            $fieldMap['orderBy'] = $select;
            // by default sort by id
            if (!$classMetadata['orderBy'] && $fieldMap['id'] === true) {
                $classMetadata['orderBy'] = $fieldMap['orderBy'];
            }
            $fieldMap['select'] = $select;
            $fieldMap['selectAsAlias'] = $selectAsAlias;
            $fieldMap['selectAs'] = $select . ' AS ' . $selectAsAlias;
            $fieldMap['entityNamespace'] = $class;
            $fieldMap['entityName'] = $name;
            $fieldMap['entityAlias'] = $alias;
            $fieldMap['glue'] = static::$glue;
            $fieldMap['prefix'] = $prefix;
            $fieldMap['joinedAs'] = $joinedAs;
            $fieldMap['joinedAsRaw'] = $path;
            $fieldMap['path'] = $path;
            $fieldMap['idx'] = $idx;
            $fieldMap['idxRaw'] = $idx_raw;
            $fieldMap['title'] = StringHelper::camelCaseToUCWords(
                ucwords(str_replace(static::$glue, ' / ', $idx_raw))
            );
            $fieldMap['depth'] = $classMetadata['depth'] - $depth;
            $classMetadata['cols'][$idx] = $fieldMap;
            // store a copy of the col under idx_raw
            $classMetadata['cols_idx_raw'][$idx_raw] = $classMetadata['cols'][$idx];
            $classMetadata['cols_tt'][$idx] = [
                'idx' => $idx,
                'title' => $fieldMap['title'],
                'showOnSearch' => true,
                'selected' => !in_array($idx, $selectedIdxs, false)
            ];
        }

        $instance = $reflectionClass->newInstance();
        $annotationReader = new AnnotationReader();

        if ($depth >= 0) {
            foreach ($classMap->getAssociationNames() as $associationName) {
                // skip all ArrayCollection associations
                // skip all associations from the exclusion array
                // skip all associations which have been already parsed
                if (
                    $accessor->getValue($instance, $associationName) instanceof ArrayCollection
                    ||
                    in_array($associationName, $classMetadata['path'], false)
                    ||
                    (
                        !in_array($prefix . $associationName, static::$forcedInclusions, false)
                        &&
                        in_array($associationName, static::$exclusions, false)
                    )
                ) {
                    continue;
                }

                // prefix = path + glue
                $idx_raw = $prefix . $associationName;
                $idx = static::hash($idx_raw);
                $associationMapping = $classMap->associationMappings[$associationName];
                // index associations by idx (hashed idx_raw
                $classMetadata['associations'][$idx] = [
                    'joinStrategy' => static::getJoinStrategyByPropertyAnnotation(
                        $class,
                        $associationName,
                        $annotationReader,
                        count($classMetadata['path']) - 1
                    ),
                    'association' => $joinedAs . '.' . $associationName,
                    'alias' => $idx
                ];
                // store a copy of the associations under the idx_raw key
                $classMetadata['associations_idx_raw'][$idx_raw] = $classMetadata['associations'][$idx];
                static::getClassMetadata(
                    $classMetadata,
                    $associationMapping['targetEntity'],
                    $em,
                    $accessor,
                    $selectedIdxs,
                    $associationName,
                    $depth
                );
            }
        }

        array_pop($classMetadata['path']);
    }

    /**
     * @param string $class
     * @param string $associationName
     * @param AnnotationReader $annotationReader
     * @param int $depth
     * @param string $matchingStrategy
     * @return string
     * @throws \Throwable
     */
    public static function getJoinStrategyByPropertyAnnotation(
        string $class,
        string $associationName,
        AnnotationReader $annotationReader,
        int $depth,
        string $matchingStrategy = NotBlank::class
    ): string
    {
        /**
         * we try be strict only at level one
         * at deeper levels we should first track the nodes and use strict only the nodes before were strict as well
         * but we don't do this. too much of a hustle
         */
        if ($depth > 0) {
            return EntityQueryBuilderHelper::JOIN_STRATEGY_LEFT;
        }

        $propertyReflection = new ReflectionProperty($class, $associationName);
        $propertyAnnotations = $annotationReader->getPropertyAnnotations($propertyReflection);
        $joinStrategy = EntityQueryBuilderHelper::JOIN_STRATEGY_LEFT;
        foreach ($propertyAnnotations as $propertyAnnotation) {
            if (get_class($propertyAnnotation) === $matchingStrategy) {
                $joinStrategy = EntityQueryBuilderHelper::JOIN_STRATEGY_INNER;
            }
        }

        return $joinStrategy;
    }

    /**
     * @param string $str
     * @return string
     */
    public static function hash(string $str): string
    {
        return static::$glue . CryptoHelper::crc32Hash($str);
    }
}