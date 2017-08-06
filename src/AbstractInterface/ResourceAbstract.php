<?php

namespace Mindlahus\SymfonyAssets\AbstractInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\Helper\RequestHelper;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class ResourceAbstract implements ResourceInterface
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var ObjectManager
     */
    protected $entityManager;
    /**
     * @var Logger $logger
     */
    protected $logger;
    /**
     * @var PropertyAccess
     */
    protected $accessor;
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $requestContent;

    /**
     * IMPORTANT!   If you should use an instance of the RequestStack,
     *              return the Request by calling $request->getCurrentRequest()
     *
     * ResourceAbstract constructor.
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param Logger $logger
     * @param \stdClass|null $requestContent
     * @throws \Throwable
     */
    public function __construct(
        Request $request,
        ObjectManager $entityManager,
        Logger $logger,
        \stdClass $requestContent = null
    )
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->requestContent = $requestContent ?? RequestHelper::getContent($request);
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return ObjectManager
     */
    public function getEntityManager(): ObjectManager
    {
        return $this->entityManager;
    }

    /**
     * @return PropertyAccessor
     */
    public function getAccessor(): PropertyAccessor
    {
        return $this->accessor;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return \stdClass
     */
    public function getRequestContent(): \stdClass
    {
        return $this->requestContent;
    }

    /**
     * @param string $msg
     * @param string $context
     */
    public function log(string $msg, string $context = 'onew'): void
    {
        $this->logger->error($msg, [$context]);
    }
}