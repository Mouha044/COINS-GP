<?php

namespace App\Core\User\Entities;

use DateTimeImmutable;

final class UserProfileEntity
{
    public int $userId;
    public ?string $bio;
    public ?DateTimeImmutable $birthdate;
    public ?string $gender;
    public ?array $emergencyContact;
    public ?array $medicalHistory;
    public ?array $languages;

    public function __construct(
        int $userId,
        ?string $bio = null,
        ?DateTimeImmutable $birthdate = null,
        ?string $gender = null,
        ?array $emergencyContact = null,
        ?array $medicalHistory = null,
        ?array $languages = null
    ) {
        $this->userId = $userId;
        $this->bio = $bio;
        $this->birthdate = $birthdate;
        $this->gender = $gender;
        $this->emergencyContact = $emergencyContact;
        $this->medicalHistory = $medicalHistory;
        $this->languages = $languages;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'bio' => $this->bio,
            'birthdate' => $this->birthdate ? $this->birthdate->format('Y-m-d') : null,
            'gender' => $this->gender,
            'emergency_contact' => $this->emergencyContact,
            'medical_history' => $this->medicalHistory,
            'languages' => $this->languages,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['user_id'],
            $data['bio'] ?? null,
            isset($data['birthdate']) ? new DateTimeImmutable($data['birthdate']) : null,
            $data['gender'] ?? null,
            $data['emergency_contact'] ?? null,
            $data['medical_history'] ?? null,
            $data['languages'] ?? null
        );
    }
}