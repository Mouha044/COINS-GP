<?php

namespace App\Core\Auth\Repositories;

use App\Core\Auth\Entities\UserEntity;

interface AuthRepositoryInterface
{
    /**
     * Persist a new user and return the stored entity (with id).
     */
    public function save(UserEntity $user): UserEntity;

    /**
     * Find a user by email (or null).
     */
    public function findByEmail(string $email): ?UserEntity;

    /**
     * Revoke tokens / logout actions for a user (infrastructure responsibility).
     */
    public function revokeTokens(UserEntity $user): void;
}