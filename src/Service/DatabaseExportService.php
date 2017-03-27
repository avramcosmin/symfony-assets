<?php

namespace Mindlahus\SymfonyAssets\Service;

use Mindlahus\SymfonyAssets\Traits\DatabaseExportTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseExportService
{
    /**
     * @var ContainerInterface
     */
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    use DatabaseExportTrait;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}