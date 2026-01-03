<?php

namespace App\Models;

use App\Core\Auth\Entities\UserEntity;
use App\Core\User\Entities\UserProfileEntity;
use DateTimeImmutable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model Eloquent User centralisé.
 *
 * Ce fichier contient :
 * - colonnes / casts / fillable pour tout stocker dans users table (incl. profile JSON)
 * - mutator pour hasher le mot de passe (si nécessaire)
 * - helpers pour merger / mettre à jour le profil stocké en JSON
 * - conversion to/from Core Entities (UserEntity / UserProfileEntity)
 *
 * Attention : si votre UseCase dans Core fournit déjà un password hashé,
 * le mutator détectera que la valeur est déjà hachée et ne la re-hashera pas.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Champs mass-assignable
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar_path',
        'verified_at',
        'profile',   // JSON column (profil utilisateur : bio, birthdate, languages, etc.)
        'rating',
    ];

    // Champs cachés dans les réponses JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts d'attributs
    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'profile' => 'array',
        'rating' => 'float',
    ];

    /**
     * Mutator pour password.
     * Si la valeur fournie est déjà un hash reconnu par password_get_info (algo != 0),
     * on la conserve telle quelle. Sinon on la hash avec Hash::make().
     *
     * Cela permet d'utiliser soit :
     *  - ->password = 'plain' (hashé automatiquement)
     *  - ->password = $entity->passwordHash (si déjà hashé par le Core)
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (empty($value)) {
                    return null;
                }

                // password_get_info retourne ['algo' => 0] si non hashé
                $info = password_get_info($value);
                if (isset($info['algo']) && $info['algo'] !== 0) {
                    // Déjà hashé (ex: Core a hashé), on garde tel quel.
                    return $value;
                }

                // Sinon, on hash avant de stocker
                return Hash::make($value);
            }
        );
    }

    /**
     * Merge et persiste des données de profil (colonne JSON `profile`).
     * Exemples de clés : bio, birthdate (Y-m-d), gender, emergency_contact (array),
     * medical_history (array), languages (array).
     *
     * Retourne l'instance User rafraîchie.
     *
     * @param array $data
     * @return $this
     */
    public function updateProfileData(array $data)
    {
        $current = $this->profile ?? [];

        // Normalisation légère : s'assurer que birthdate est une string Y-m-d si présent
        if (isset($data['birthdate'])) {
            if ($data['birthdate'] instanceof \DateTimeInterface) {
                $data['birthdate'] = $data['birthdate']->format('Y-m-d');
            } else {
                // attempt parse, ignore if invalid
                try {
                    $d = new DateTimeImmutable($data['birthdate']);
                    $data['birthdate'] = $d->format('Y-m-d');
                } catch (\Throwable $e) {
                    unset($data['birthdate']);
                }
            }
        }

        // Merge récursif (préserve sous-tableaux existants)
        $merged = array_replace_recursive($current, $data);

        $this->profile = $merged;
        $this->save();

        return $this->fresh();
    }

    /**
     * Retourne le profil normalisé (array) avec user_id inclus.
     *
     * @return array
     */
    public function getNormalizedProfile(): array
    {
        $profile = $this->profile ?? [];

        // Assurer présence de user_id pour compatibilité avec Core UserProfileEntity::fromArray
        $profile['user_id'] = $this->id;

        // Si birthdate est datetime, forcer string Y-m-d
        if (isset($profile['birthdate']) && $profile['birthdate'] instanceof \DateTimeInterface) {
            $profile['birthdate'] = $profile['birthdate']->format('Y-m-d');
        }

        return $profile;
    }

    /**
     * Convertit ce modèle Eloquent en Core UserEntity.
     *
     * @return UserEntity
     */
    public function toUserEntity(): UserEntity
    {
        // Build verifiedAt as ISO string if present
        $verified = null;
        if ($this->verified_at instanceof \DateTimeInterface) {
            $verified = new DateTimeImmutable($this->verified_at->format('c'));
        } elseif (!empty($this->verified_at)) {
            try {
                $verified = new DateTimeImmutable($this->verified_at);
            } catch (\Throwable $e) {
                $verified = null;
            }
        }

        return new UserEntity(
            $this->id,
            (string)$this->name,
            (string)$this->email,
            $this->phone ?? null,
            $this->password ?? null,
            $this->role ?? null,
            $verified,
            $this->avatar_path ?? null,
            $this->rating ?? null
        );
    }

    /**
     * Convertit le profil en Core UserProfileEntity.
     * Retourne null si l'entité profile n'existe pas.
     *
     * @return UserProfileEntity|null
     */
    public function toUserProfileEntity(): ?UserProfileEntity
    {
        $profile = $this->getNormalizedProfile();

        // Si seulement user_id et pas d'autres infos, on peut quand même retourner
        // une entité minimale
        return UserProfileEntity::fromArray($profile);
    }

    /**
     * Crée/Met à jour un modèle User depuis une UserEntity du Core.
     * Si l'entité contient un id, on essaye de retrouver et mettre à jour l'utilisateur.
     * Sinon on crée un nouvel utilisateur.
     *
     * @param UserEntity $entity
     * @return User
     */
    public static function upsertFromEntity(UserEntity $entity): User
    {
        $attributes = [
            'name' => $entity->name,
            'email' => $entity->email,
            'phone' => $entity->phone,
            'role' => $entity->role,
            'avatar_path' => $entity->avatarPath,
            'rating' => $entity->rating,
        ];

        // Si l'entité a un verifiedAt en DateTimeImmutable -> map to string
        if ($entity->verifiedAt instanceof DateTimeImmutable) {
            $attributes['verified_at'] = $entity->verifiedAt->format('Y-m-d H:i:s');
        } elseif (!empty($entity->verifiedAt)) {
            $attributes['verified_at'] = $entity->verifiedAt;
        }

        // Gestion du password : $entity->passwordHash peut être soit hashé soit null
        if (!empty($entity->passwordHash)) {
            // Le mutator password() détecte si c'est déjà un hash et évite le rehash.
            $attributes['password'] = $entity->passwordHash;
        }

        if (!empty($entity->id)) {
            $user = static::find($entity->id);
            if (!$user) {
                // fallback : essayer par email
                $user = static::where('email', $entity->email)->first();
            }
            if ($user) {
                $user->fill($attributes);
                $user->save();
                return $user->fresh();
            }
        }

        // Create new
        return static::create($attributes);
    }

    /**
     * Revoke all tokens and optionally perform other cleanup.
     */
    public function revokeAllTokens(): void
    {
        $this->tokens()->delete();
    }

    /**
     * Helper pour transformer un tableau (ex: payload API) en UserEntity.
     *
     * @param array $data
     * @return UserEntity
     */
    public static function toEntityFromArray(array $data): UserEntity
    {
        $verifiedAt = null;
        if (!empty($data['verified_at'])) {
            try {
                $verifiedAt = new DateTimeImmutable($data['verified_at']);
            } catch (\Throwable $e) {
                $verifiedAt = null;
            }
        }

        return new UserEntity(
            $data['id'] ?? null,
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? null,
            $data['password'] ?? null,
            $data['role'] ?? null,
            $verifiedAt,
            $data['avatar_path'] ?? null,
            isset($data['rating']) ? (float)$data['rating'] : null
        );
    }
}