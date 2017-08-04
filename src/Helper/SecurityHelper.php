<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityHelper
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     * @throws \Exception
     */
    public static function getUsername(ContainerInterface $container)
    {
        /**
         * @var TokenStorageInterface $tokenStorage
         */
        $tokenStorage = $container->get(
            'security.token_storage',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        $authorizationChecker = $container->get(
            'security.authorization_checker',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        if (null === $tokenStorage || null === $authorizationChecker || null === $token = $tokenStorage->getToken()) {
            throw new \Exception('Invalid Token Storage. SecurityHelper cannot retrieve the username.');
        }

        return $token->getUser()->getUsername();
    }
}