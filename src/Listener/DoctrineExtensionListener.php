<?php

namespace Mindlahus\SymfonyAssets\Listener;

use Gedmo\Blameable\BlameableListener;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DoctrineExtensionListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    private $userRepository;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @param string $userRepository
     */
    public function setUserRepository(string $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \Exception
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        try {
            $tokenStorage = $this->container->get(
                'security.token_storage',
                ContainerInterface::NULL_ON_INVALID_REFERENCE
            );
            $authorizationChecker = $this->container->get(
                'security.authorization_checker',
                ContainerInterface::NULL_ON_INVALID_REFERENCE
            );
            if (null !== $tokenStorage
                &&
                null !== $authorizationChecker
                &&
                null !== $tokenStorage->getToken()
            ) {
                /**
                 * @var BlameableListener $blameable
                 */
                $blameable = $this->container->get('gedmo.listener.blameable');
                $user = $this->container->get('doctrine.orm.entity_manager')
                    ->getRepository(
                        $this->userRepository
                    )->findOneBy([
                        'username' => $tokenStorage->getToken()->getUser()->getUsername()
                    ]);
                $blameable->setUserValue($user);
            }
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
