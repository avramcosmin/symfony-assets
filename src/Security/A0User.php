<?php

namespace Mindlahus\SymfonyAssets\Security;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class A0User implements UserInterface, EquatableInterface
{
    private $jwt;
    private $roles;

    public function __construct($jwt, array $roles)
    {
        $this->jwt = $jwt;
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return array_merge(
            $this->jwt['app_metadata']['roles'] ?: [],
            $this->roles
        );
    }

    /**
     * @return mixed|null
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->jwt['user_metadata']['username'];
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof A0User) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}
