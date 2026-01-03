<?php

namespace App\Core\Auth\UseCases;

use App\Core\Auth\Repositories\AuthRepositoryInterface;
use App\Core\Auth\Entities\UserEntity;
use RuntimeException;

final class LoginUser
{
    private AuthRepositoryInterface $repo;

    public function __construct(AuthRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Attempt login, returns UserEntity on success.
     *
     * @throws RuntimeException on invalid credentials
     */
    public function execute(string $email, string $password): UserEntity
    {
        $user = $this->repo->findByEmail($email);
        if (!$user || empty($user->passwordHash)) {
            throw new RuntimeException('Invalid credentials.');
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new RuntimeException('Invalid credentials.');
        }

        return $user;
    }
}