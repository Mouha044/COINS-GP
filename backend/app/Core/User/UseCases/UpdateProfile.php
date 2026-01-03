<?php

namespace App\Core\User\UseCases;

use App\Core\User\Repositories\UserRepositoryInterface;
use App\Core\User\Entities\UserProfileEntity;
use InvalidArgumentException;

final class UpdateProfile
{
    private UserRepositoryInterface $repo;

    public function __construct(UserRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function execute(UserProfileEntity $profile): UserProfileEntity
    {
        if ($profile->userId <= 0) {
            throw new InvalidArgumentException('Invalid user id.');
        }

        // Domain rules could go here (eg. allowed languages, max length)
        return $this->repo->updateProfile($profile);
    }
}