<?php

namespace App\Core\User\UseCases;

use App\Core\User\Repositories\UserRepositoryInterface;
use App\Core\User\Entities\UserProfileEntity;
use RuntimeException;

final class GetUserProfile
{
    private UserRepositoryInterface $repo;

    public function __construct(UserRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function execute(int $userId): ?UserProfileEntity
    {
        if ($userId <= 0) {
            throw new RuntimeException('Invalid user id.');
        }

        return $this->repo->findProfileByUserId($userId);
    }
}