<?php namespace Mindlahus\SymfonyAssets\AbstractInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;

interface ResourceInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void;

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * @return ObjectManager
     */
    public function getEntityManager(): ObjectManager;

    /**
     * @return PropertyAccessor
     */
    public function getAccessor(): PropertyAccessor;

    /**
     * @return Logger
     */
    public function getLogger(): Logger;

    /**
     * @return \stdClass
     */
    public function getRequestContent(): \stdClass;

    /**
     * @param string $msg
     * @param string $context
     */
    public function log(string $msg, string $context): void;
}