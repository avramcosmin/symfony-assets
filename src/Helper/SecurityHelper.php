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
     * @throws \Throwable
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

        if (empty($roles)
            ||
            true !== $token->isAuthenticated()
            ||
            true !== $authorizationChecker->isGranted($roles)
        ) {
            throw new AccessDeniedHttpException(
                'Denied access. Authenticate or Grant Role!'
            );
        }
    }

    /**
     * @param ContainerInterface $container
     * @return TokenInterface
     * @throws \Throwable
     */
    public static function getTokenStorage(ContainerInterface $container): TokenInterface
    {
        $tokenStorage = $container->get(
            'security.token_storage',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        if (null === $tokenStorage || null === $token = $tokenStorage->getToken()) {
            throw new \ErrorException('Invalid Token Storage. SecurityHelper gives up.');
        }

        return $token;
    }

    /**
     * @param ContainerInterface $container
     * @return AuthorizationChecker
     * @throws \Throwable
     */
    public static function getAuthorizationChecker(ContainerInterface $container): AuthorizationChecker
    {
        $authorizationChecker = $container->get(
            'security.authorization_checker',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );

        if (null === $authorizationChecker) {
            throw new \ErrorException('Invalid authorization checker. SecurityHelper gives up.');
        }

        return $authorizationChecker;
    }
}