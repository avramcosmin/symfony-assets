<?php

namespace Mindlahus\SymfonyAssets\AbstractInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Mindlahus\SymfonyAssets\Helper\RequestHelper;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class ResourceAbstract
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var Logger $logger
     */
    private $logger;
    /**
     * @var PropertyAccess
     */
    private $accessor;
    /**
     * @var ContainerInterface
     */
    private $container;

    private $requestContent;

    /**
     * todo : check that the logger actually works
     *
     * IMPORTANT!   If you should use an instance of the RequestStack,
     *              return the Request by calling $request->getCurrentRequest()
     *
     * ResourceAbstract constructor.
     * @param Request $request
     * @param ObjectManager $entityManager
     * @param Logger $logger
     * @param null $requestContent
     */
    public function __construct(
        Request $request,
        ObjectManager $entityManager,
        Logger $logger,
        $requestContent = null
    )
    {
        RequestHelper::initialize($request);
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
     * @return PropertyAccess|\Symfony\Component\PropertyAccess\PropertyAccessor
     */
    public function getAccessor()
    {
        return $this->accessor;
    }

    /**
     * @return Logger|\stdClass
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return mixed|\stdClass
     */
    public function getRequestContent()
    {
        return $this->requestContent;
    }
}