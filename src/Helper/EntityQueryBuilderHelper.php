<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Mindlahus\SymfonyAssets\Traits\Entity\EntityQueryBuilderTrait;

class EntityQueryBuilderHelper
{
    /**
     * https://stackoverflow.com/questions/5706437/whats-the-difference-between-inner-join-left-join-right-join-and-full-join
     */
    public const JOIN_STRATEGY_DEFAULT = ' JOIN ';
    public const JOIN_STRATEGY_LEFT = ' LEFT JOIN ';
    public const JOIN_STRATEGY_RIGHT = ' RIGHT JOIN ';
    public const JOIN_STRATEGY_INNER = ' INNER JOIN ';
    public const JOIN_STRATEGY_OUTER = ' FULL OUTER JOIN ';
    public const ORDER_DIR_ASC = 'ASC';
    public const ORDER_DIR_DESC = 'DESC';

    use EntityQueryBuilderTrait;
}