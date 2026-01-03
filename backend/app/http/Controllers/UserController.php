<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Core\User\UseCases\GetUserProfile;
use App\Core\User\UseCases\UpdateProfile;
use App\Core\User\Entities\UserProfileEntity;
use InvalidArgumentException;
use RuntimeException;

class UserController extends Controller
{
    private GetUserProfile $getUserProfile;
    private UpdateProfile $updateProfile;

    public function __construct(GetUserProfile $getUserProfile, UpdateProfile $updateProfile)
    {
        $this->getUserProfile = $getUserProfile;
        $this->updateProfile = $updateProfile;

        $this->middleware('auth:sanctum')->only(['me', 'update']);
    }

    /**
     * Return authenticated user's basic data (delegates to AuthController->me usually).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        return response()->json($user->makeHidden(['password', 'remember_token'])->toArray());
    }

    /**
     * Show public profile for a user id.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $profile = $this->getUserProfile->execute($id);
            if (!$profile) {
                return response()->json(['message' => 'Profil introuvable.'], 404);
            }

            return response()->json($profile->toArray());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * Expected payload (partial):
     *  - bio
     *  - birthdate (Y-m-d)
     *  - gender
     *  - emergency_contact (object)
     *  - medical_history (array)
     *  - languages (array)
     */
    public function update(Request $request): JsonResponse
    {
        $eloquent = $request->user();
        if (!$eloquent) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        // Basic validation — adapt rules as needed
        $validated = $request->validate([
            'bio' => 'nullable|string|max:1000',
            'birthdate' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|string|in:male,female,other',
            'emergency_contact' => 'nullable|array',
            'medical_history' => 'nullable|array',
            'languages' => 'nullable|array',
        ]);

        try {
            $birthdate = isset($validated['birthdate']) ? new \DateTimeImmutable($validated['birthdate']) : null;

            $profileEntity = new UserProfileEntity(
                (int)$eloquent->id,
                $validated['bio'] ?? null,
                $birthdate,
                $validated['gender'] ?? null,
                $validated['emergency_contact'] ?? null,
                $validated['medical_history'] ?? null,
                $validated['languages'] ?? null
            );

            $updated = $this->updateProfile->execute($profileEntity);

            return response()->json($updated->toArray());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }
}