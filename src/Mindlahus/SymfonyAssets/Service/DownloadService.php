<?php

namespace Mindlahus\SymfonyAssets\Service;

use Mindlahus\SymfonyAssets\Traits\DownloadTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DownloadService
{
    public $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    use DownloadTrait;
}