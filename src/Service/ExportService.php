<?php

namespace Mindlahus\SymfonyAssets\Service;

use Mindlahus\SymfonyAssets\Traits\ExportTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExportService
{
    /**
     * @var ContainerInterface
     */
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    use ExportTrait;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}