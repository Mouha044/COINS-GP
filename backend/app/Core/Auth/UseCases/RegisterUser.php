<?php

namespace App\Core\Auth\UseCases;

use App\Core\Auth\Repositories\AuthRepositoryInterface;
use App\Core\Auth\Entities\UserEntity;
use InvalidArgumentException;
use RuntimeException;

final class RegisterUser
{
    private AuthRepositoryInterface $repo;

    public function __construct(AuthRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Register user.
     *
     * @param array $data ['name','email','password','phone'?, 'role'?]
     * @return UserEntity
     * @throws InvalidArgumentException on validation error
     * @throws RuntimeException on repository error (e.g. duplicate)
     */
    public function execute(array $data): UserEntity
    {
        // Basic validation (domain rules)
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Name, email and password are required.');
        }

        // Check duplicate
        if ($this->repo->findByEmail($data['email'])) {
            throw new RuntimeException('Email already in use.');
        }

        // Hash password using PHP native (keeps Core pure — no framework dependency)
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = new UserEntity(
            null,
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $passwordHash,
            $data['role'] ?? 'sender',
            null,
            $data['avatar_path'] ?? null,
            null
        );

        $saved = $this->repo->save($user);

        return $saved;
    }
}