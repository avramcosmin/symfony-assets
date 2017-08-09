<?php

namespace Mindlahus\SymfonyAssets\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SecurityHelper
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     * @throws \Exception
     */
    public static function getUsername(ContainerInterface $container)
    {
        $token = static::getTokenStorage($container);
        static::getAuthorizationChecker($container);

        return $token->getUser()->getUsername();
    }

    /**
     * @param ContainerInterface $container
     * @param array $roles
     * @throws \Throwable
     */
    public static function isGranted(ContainerInterface $container, array $roles = []): void
    {
        $token = static::getTokenStorage($container);
        $authorizationChecker = static::getAuthorizationChecker($container);

        if (true !== $token->isAuthenticated()) {
            throw new AccessDeniedHttpException('Not authenticated!');
        }

        if (!empty($roles) && true !== $authorizationChecker->isGranted($roles)) {
            throw new AccessDeniedHttpException(
                'Not authenticated!'
            );
        }
    }

    /**
     * @param ContainerInterface $container
     * @return TokenInterface
     * @throws \Exception
     */
    public static function getTokenStorage(ContainerInterface $container): TokenInterface
    {
        $tokenStorage = $container->get(
            'security.token_storage',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        if (null === $tokenStorage || null === $token = $tokenStorage->getToken()) {
            throw new \Exception('Invalid Token Storage. SecurityHelper gives up.');
        }

        return $token;
    }

    /**
     * @param ContainerInterface $container
     * @return AuthorizationChecker
     * @throws \Exception
     */
    public static function getAuthorizationChecker(ContainerInterface $container): AuthorizationChecker
    {
        $authorizationChecker = $container->get(
            'security.authorization_checker',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );

        if (null === $authorizationChecker) {
            throw new \Exception('Invalid authorization checker. SecurityHelper gives up.');
        }

        return $authorizationChecker;
    }
}