<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

use Mindlahus\SymfonyAssets\Helper\EntityQueryBuilderHelper;

trait EntityQueryBuilderTrait
{
    /**
     * @param array $cols
     * @param string $namespaceAndAlias
     * @return string
     */
    public static function select(array $cols, string $namespaceAndAlias): string
    {
        return 'SELECT ' . implode(', ', $cols) . ' FROM ' . $namespaceAndAlias;
    }

    /**
     * @param array $entitiesAndTheirAliases
     * @param string $joiningStrategy
     * @return string
     */
    public static function join(
        array $entitiesAndTheirAliases,
        string $joiningStrategy = EntityQueryBuilderHelper::JOIN_STRATEGY_LEFT
    ): string
    {
        if (!empty($entitiesAndTheirAliases)) {
            return $joiningStrategy . implode($joiningStrategy, $entitiesAndTheirAliases);
        }

        return '';
    }

    /**
     * @param array $conditions
     * @return string
     */
    public static function where(array $conditions): string
    {
        if (!empty($conditions)) {
            return ' WHERE ' . implode(' AND ', $conditions);
        }

        return '';
    }

    /**
     * @param string|null $orderBy
     * @param string $orderDir
     * @return string
     */
    public static function orderBy(
        string $orderBy = null,
        string $orderDir = EntityQueryBuilderHelper::ORDER_DIR_DESC
    ): string
    {
        if ($orderBy) {
            return ' ORDER BY ' . $orderBy . ' ' . $orderDir;
        }

        return '';
    }

    /**
     * $classMetadata = [
     *  initialized     (bool)
     *  depth           (int)
     *  namespace       (string)
     *  name            (string)
     *  alias           (string)
     *  cols            (array)
     *  orderBy         (string)
     *  orderDir        (string)
     *  associations    (array)
     *  cols_tt         (array)
     *  path_history    (array)
     *  path            (array)
     *  joinedAs        (string)
     * ]
     * $selectedIdx = [
     *  $idx (string)
     * ]
     *
     * @param array $classMetadata
     * @param array $selectedIdxs
     * @param array $where
     * @param string|null $orderBy
     * @param string|null $orderDir
     * @return string
     */
    public static function buildDqlFromClassMetadata(
        array $classMetadata,
        array $selectedIdxs,
        array $where = [],
        string $orderBy = null,
        string $orderDir = null
    ): string
    {
        $selects = [];
        $joins = [];
        foreach ($selectedIdxs as $selectedIdx) {
            $selectedIdx = $classMetadata['cols'][$selectedIdx];
            $selects[] = $selectedIdx['joinedAs'] . '.' . $selectedIdx['fieldName'];
            if ($join = $classMetadata['associations'][$selectedIdx['joinedAs']] ?? null) {
                $joins[] = implode(' ', $join);
            }
        }
        $dql = static::select(
            $selects,
            $classMetadata['namespace'] . ' ' . $classMetadata['joinedAs']
        );
        $dql .= static::join($joins);
        $dql .= static::where($where);
        $dql .= static::orderBy(
            $orderBy ?: $classMetadata['orderBy'],
            $orderDir ?: $classMetadata['orderDir']
        );

        return $dql;
    }
}