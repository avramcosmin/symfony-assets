<?php

namespace Mindlahus\SymfonyAssets\Traits\Entity;

trait EntityQueryBuilderTrait
{
    /**
     * @param array $cols
     * @param string $entityAndAlias
     * @return string
     */
    static public function select(array $cols, string $entityAndAlias): string
    {
        return 'SELECT ' . implode(', ', $cols) . ' FROM ' . $entityAndAlias;
    }

    /**
     * @param array $entitiesAndTheirAliases
     * @param string $joiningStrategy
     * @return string
     */
    static public function join(array $entitiesAndTheirAliases, string $joiningStrategy = 'LEFT'): string
    {
        return implode(' ' . $joiningStrategy . ' ', $entitiesAndTheirAliases);
    }
}