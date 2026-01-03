<?php

namespace App\Core\Auth\Entities;

use DateTimeImmutable;

final class UserEntity
{
    public ?int $id;
    public string $name;
    public string $email;
    public ?string $phone;
    public ?string $passwordHash;
    public ?string $role;
    public ?DateTimeImmutable $verifiedAt;
    public ?string $avatarPath;
    public ?float $rating;

    public function __construct(
        ?int $id,
        string $name,
        string $email,
        ?string $phone = null,
        ?string $passwordHash = null,
        ?string $role = 'sender',
        ?DateTimeImmutable $verifiedAt = null,
        ?string $avatarPath = null,
        ?float $rating = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->verifiedAt = $verifiedAt;
        $this->avatarPath = $avatarPath;
        $this->rating = $rating;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name'=> $this->name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'password_hash'=> $this->passwordHash,
            'role'=> $this->role,
            'verified_at'=> $this->verifiedAt ? $this->verifiedAt->format('c') : null,
            'avatar_path'=> $this->avatarPath,
            'rating'=> $this->rating,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['password_hash'] ?? null,
            $data['role'] ?? 'sender',
            isset($data['verified_at']) ? new DateTimeImmutable($data['verified_at']) : null,
            $data['avatar_path'] ?? null,
            isset($data['rating']) ? (float)$data['rating'] : null
        );
    }
}