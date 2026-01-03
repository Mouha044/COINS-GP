<?php

namespace App\Core\User\Repositories;

use App\Core\User\Entities\UserProfileEntity;

interface UserRepositoryInterface
{
    /**
     * Update or create profile data and return the stored profile entity.
     */
    public function updateProfile(UserProfileEntity $profile): UserProfileEntity;

    /**
     * Return profile entity for a given user id or null.
     */
    public function findProfileByUserId(int $userId): ?UserProfileEntity;
}