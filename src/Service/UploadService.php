<?php

namespace Mindlahus\SymfonyAssets\Service;

use Mindlahus\SymfonyAssets\Traits\UploadTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UploadService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    use UploadTrait;

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}