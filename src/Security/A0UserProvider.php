<?php

namespace Mindlahus\SymfonyAssets\Security;

use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\Core\JWTUserProviderInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\User\UserInterface;

class A0UserProvider implements JWTUserProviderInterface
{
    protected $auth0Service;

    public function __construct(Auth0Service $auth0Service)
    {
        $this->auth0Service = $auth0Service;
    }

    /**
     * @param \stdClass $jwt
     * @return A0User
     */
    public function loadUserByJWT($jwt): A0User
    {
        $data = $this->auth0Service->getUserProfileByA0UID($jwt->token, $jwt->sub);

        return new A0User($data, ['ROLE_VIEW']);
    }

    /**
     * @param string $username
     * @return mixed
     * @throws \Throwable
     */
    public function loadUserByUsername($username)
    {
        throw new NotImplementedException('method not implemented');
    }

    /**
     * @return A0AnonymousUser
     */
    public function getAnonymousUser(): A0AnonymousUser
    {
        return new A0AnonymousUser();
    }

    /**
     * @param UserInterface $user
     * @return mixed
     * @throws \Throwable
     */
    public function refreshUser(UserInterface $user)
    {
        throw new NotImplementedException('Method not implemented.');
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class === A0User::class;
    }

    /**
     * @return Auth0Service
     */
    public function getAuth0Service(): Auth0Service
    {
        return $this->auth0Service;
    }
}
