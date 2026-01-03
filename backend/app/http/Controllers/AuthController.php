<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Core\Auth\UseCases\RegisterUser;
use App\Core\Auth\UseCases\LoginUser;
use App\Core\Auth\Repositories\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    private RegisterUser $registerUser;
    private LoginUser $loginUser;
    private AuthRepositoryInterface $authRepository;

    public function __construct(
        RegisterUser $registerUser,
        LoginUser $loginUser,
        AuthRepositoryInterface $authRepository
    ) {
        $this->registerUser = $registerUser;
        $this->loginUser = $loginUser;
        $this->authRepository = $authRepository;

        // Only logout and me require authentication
        $this->middleware('auth:sanctum')->only(['logout', 'me']);
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();

            // Use case returns a Core UserEntity with id set after persistence
            $userEntity = $this->registerUser->execute($payload);

            // Retrieve Eloquent model to create a Sanctum token and to shape response
            $eloquent = User::find($userEntity->id);
            if (!$eloquent) {
                // Should not happen, but guard anyway
                return response()->json(['message' => 'Erreur interne lors de la création de l\'utilisateur.'], 500);
            }

            // Create API token (Sanctum)
            $token = $eloquent->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => $eloquent->makeHidden(['password', 'remember_token'])->toArray(),
                'token' => $token,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            // Domain error (eg. duplicate email)
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            // Unexpected
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    /**
     * Login user and return token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $userEntity = $this->loginUser->execute($data['email'], $data['password']);

            // Retrieve Eloquent model to create token / response
            $eloquent = User::where('email', $userEntity->email)->first();
            if (!$eloquent) {
                return response()->json(['message' => 'Utilisateur introuvable après authentification.'], 500);
            }

            $token = $eloquent->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => $eloquent->makeHidden(['password', 'remember_token'])->toArray(),
                'token' => $token,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    /**
     * Logout the authenticated user (revoke tokens).
     */
    public function logout(Request $request): JsonResponse
    {
        $eloquent = $request->user(); // Eloquent User via Sanctum

        if (!$eloquent) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        try {
            // Revoke tokens directly (infrastructure) and inform repository (if implemented)
            $eloquent->tokens()->delete();

            // If repository has logic for revocation (audit, etc.)
            try {
                // Map Eloquent to minimal UserEntity for repository call
                $entity = new \App\Core\Auth\Entities\UserEntity(
                    $eloquent->id,
                    $eloquent->name,
                    $eloquent->email,
                    $eloquent->phone ?? null,
                    $eloquent->password ?? null,
                    $eloquent->role ?? null,
                    $eloquent->verified_at ? new \DateTimeImmutable($eloquent->verified_at) : null,
                    $eloquent->avatar_path ?? null,
                    $eloquent->rating ?? null
                );
                $this->authRepository->revokeTokens($entity);
            } catch (\Throwable $e) {
                // If repository revoke fails, ignore — tokens already deleted above.
            }

            return response()->json(['message' => 'Déconnexion effectuée.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Impossible de se déconnecter pour le moment.'], 500);
        }
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $eloquent = $request->user();
        if (!$eloquent) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        return response()->json($eloquent->makeHidden(['password', 'remember_token'])->toArray());
    }
}