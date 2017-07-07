<?php namespace Mindlahus\SymfonyAssets\AbstractInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

interface ResourceAbstractInterface
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
     * @return PropertyAccess|\Symfony\Component\PropertyAccess\PropertyAccessor
     */
    public function getAccessor();

    /**
     * @return Logger|\stdClass
     */
    public function getLogger();

    /**
     * @return mixed|\stdClass
     */
    public function getRequestContent();
}