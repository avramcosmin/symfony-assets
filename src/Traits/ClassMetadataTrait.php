<?php

namespace Mindlahus\SymfonyAssets\Traits;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\Helper\CryptoHelper;
use Mindlahus\SymfonyAssets\Helper\EntityQueryBuilderHelper;
use Mindlahus\SymfonyAssets\Helper\StringHelper;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraints\NotBlank;

trait ClassMetadataTrait
{
    public static $glue = '_';

    /**
     * @param array $classMetadata
     * @param string $class
     * @param ObjectManager $em
     * @param PropertyAccessor $accessor
     * @param array $selected_idx
     * @param string|null $alias
     * @param int $depth
     * @param array $exclusions
     * @throws \Throwable
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
        $path = implode(static::$glue, $classMetadata['path']);
        $prefix = $path . static::$glue;
        $classMetadata['path_history'][] = $path;
        $joinedAs = static::hash($path);
        if (!isset($classMetadata['joinedAs'])) {
            $classMetadata['joinedAs'] = $joinedAs;
        }

        foreach ($classMap->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $exclusions, false)) {
                continue;
            }
            $idx_raw = $prefix . $fieldName;
            $idx = static::hash($idx_raw);
            $fieldMap = $classMap->fieldMappings[$fieldName];
            $fieldMap['orderBy'] = $joinedAs . '.' . $fieldName;
            if (!$classMetadata['orderBy'] && $fieldMap['id'] === true) {
                $classMetadata['orderBy'] = $fieldMap['orderBy'];
            }
            $fieldMap['entityNamespace'] = $class;
            $fieldMap['entityName'] = $name;
            $fieldMap['entityAlias'] = $alias;
            $fieldMap['path'] = $path;
            $fieldMap['glue'] = static::$glue;
            $fieldMap['prefix'] = $prefix;
            $fieldMap['joinedAs'] = $joinedAs;
            $fieldMap['joinedAsRaw'] = $path;
            $fieldMap['idx'] = $idx;
            $fieldMap['idxRaw'] = $idx_raw;
            $fieldMap['title'] = StringHelper::camelCaseToUCWords(
                ucwords(str_replace(static::$glue, ' ', $idx_raw))
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
        $annotationReader = new AnnotationReader();

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
                $idx = static::hash($idx_raw);
                $associationMapping = $classMap->associationMappings[$associationName];
                $classMetadata['associations'][$idx] = [
                    'joinStrategy' => static::getJoinStrategyByPropertyAnnotation(
                        $class,
                        $associationName,
                        $annotationReader
                    ),
                    'association' => $joinedAs . '.' . $associationName,
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

    /**
     * @param string $class
     * @param string $associationName
     * @param AnnotationReader $annotationReader
     * @param string $matchingStrategy
     * @return string
     * @throws \Throwable
     */
    public static function getJoinStrategyByPropertyAnnotation(
        string $class,
        string $associationName,
        AnnotationReader $annotationReader,
        string $matchingStrategy = NotBlank::class
    ): string
    {
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