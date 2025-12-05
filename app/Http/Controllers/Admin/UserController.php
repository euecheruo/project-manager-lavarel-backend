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

class UserController extends Controller
{
    public function index()
    {
        // Check 'users.view_any' permission
        Gate::authorize('viewAny', User::class);

        $users = User::with('roles')->paginate(20);
        return UserResource::collection($users);
    }

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

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->only(['first_name', 'last_name', 'email', 'is_active']));

        if ($request->has('role_name')) {
            $role = Role::where('role_name', $request->role_name)->first();
            $user->roles()->sync([$role->role_id]);
        }

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        $user->refreshTokens()->update(['is_revoked' => true]);

        return response()->json(['message' => 'User deactivated']);
    }
}
