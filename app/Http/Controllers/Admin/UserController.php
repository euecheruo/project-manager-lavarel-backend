<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="User Management",
 * description="Executive-only endpoints for managing employees and roles."
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/users",
     * operationId="getUsers",
     * tags={"User Management"},
     * summary="List all employees",
     * description="Retrieve a paginated list of all users and their global roles. Restricted to Executives.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Page number for pagination",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/UserResource")
     * )
     * ),
     * @OA\Response(response=403, description="Forbidden. Requires Executive role.")
     * )
     */
    public function index()
    {
        // Check 'users.view_any' permission
        Gate::authorize('viewAny', User::class);

        $users = User::with('roles')->paginate(20);
        return UserResource::collection($users);
    }

    /**
     * @OA\Post(
     * path="/api/users",
     * operationId="createUser",
     * tags={"User Management"},
     * summary="Onboard a new employee",
     * description="Creates a user record and assigns a global role. Triggers an invite email (logic deferred).",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"first_name", "last_name", "email", "role_name"},
     * @OA\Property(property="first_name", type="string", example="Alice"),
     * @OA\Property(property="last_name", type="string", example="Smith"),
     * @OA\Property(property="email", type="string", format="email", example="alice@company.com"),
     * @OA\Property(property="role_name", type="string", enum={"Executive", "Manager", "Associate"}, example="Associate")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User created successfully",
     * @OA\JsonContent(ref="#/components/schemas/UserResource")
     * ),
     * @OA\Response(response=422, description="Validation Error (Email exists or Invalid Role)")
     * )
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password_hash' => Hash::make(Str::random(12)),
            'is_active' => true,
        ]);

        $role = Role::where('role_name', $request->role_name)->firstOrFail();
        $user->roles()->attach($role->role_id);

        return new UserResource($user);
    }

    /**
     * @OA\Put(
     * path="/api/users/{user}",
     * operationId="updateUser",
     * tags={"User Management"},
     * summary="Update employee details",
     * description="Modify details, promote/demote roles, or change active status.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * description="User ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="first_name", type="string"),
     * @OA\Property(property="last_name", type="string"),
     * @OA\Property(property="email", type="string", format="email"),
     * @OA\Property(property="role_name", type="string", enum={"Executive", "Manager", "Associate"}),
     * @OA\Property(property="is_active", type="boolean")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/UserResource")
     * )
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->only(['first_name', 'last_name', 'email', 'is_active']));

        if ($request->has('role_name')) {
            $role = Role::where('role_name', $request->role_name)->first();
            $user->roles()->sync([$role->role_id]);
        }

        return new UserResource($user->load('roles'));
    }

    /**
     * @OA\Delete(
     * path="/api/users/{user}",
     * operationId="deleteUser",
     * tags={"User Management"},
     * summary="Deactivate employee",
     * description="Soft deletes the user and revokes all refresh tokens immediately.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * description="User ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="User deactivated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User deactivated")
     * )
     * )
     * )
     */
    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        $user->refreshTokens()->update(['is_revoked' => true]);

        return response()->json(['message' => 'User deactivated']);
    }
}
