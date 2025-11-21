<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Services\User\ListUsers;
use App\Services\User\LoginUser;
use App\Services\User\CreateUser;
use App\Services\User\LogoutUser;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Services\User\ChangeUserRole;
use Knuckles\Scribe\Attributes\Group;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;
use App\Http\Requests\ChangeUserTypeRequest;
use Knuckles\Scribe\Attributes\Authenticated;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group(name: 'User Auth')]

class UserController extends Controller
{
    /**
     * Register User
     *
     * Register new user
     *
     * Access Level: N/A
     *
     * @return UserResource
     *
     * @throws ValidationException
     */
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function register(RegisterRequest $request)
    {

        $data = $request->validated();

        $user =  (new CreateUser)->execute($data);

        return new UserResource($user);
    }

    /**
     * Login User
     *
     *
     * Login a user and return a token.
     *
     * Access Level: N/A
     *
     * @return array
     *
     * @throws ValidationException
     */
    #[ResponseFromApiResource(UserResource::class, User::class, additional: ['token' => '5|kBPlXpDNHg491Yg5qTJr2jdTq9PL8L8Z8i0w4jYz22d20fdc'])]
    public function login(LoginRequest $request)
    {

        $data = $request->validated();

        $result = (new LoginUser)->execute($data);


        return [
            'data' => new UserResource($result['user']),
            'access_token' => $result['token'],
        ];
    }

    /**
     * Logout User
     *
     * Log user out
     *
     * Access Level: N/A
     */
    #[Authenticated]
    #[Response(['message' => 'Logged out successfully'])]
    public function logout(Request $request): JsonResponse
    {
        $result = (new LogoutUser)->execute($request->user());

        return $result ? response()->json(['message' => 'Logged out successfully!']) : response()->json(['message' => 'Logout failed'], 500);
    }

    /**
     * Get Users
     *
     * get list of users
     *
     * Access Level : Manager
     */
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class, User::class, collection: true, factoryStates: ['roles'])]
    public function getUsers(Request $request): AnonymousResourceCollection
    {
        $data = (new ListUsers)->execute();

        return UserResource::collection($data);
    }

    /**
     * Get User
     *
     * Get a selected user depending on the id
     *
     * Access Level: Manager
     */
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class, User::class), UrlParam(name: 'id', type: 'int', description: 'searched user\'s id', example: 1)]
    public function getUser(User $user): UserResource
    {
        $data = $user;

        return new UserResource($data);
    }

    /**
     * Get Logged In User
     *
     * get user's own data
     *
     * Access Level: N/A
     */
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function getLoggedInUser(Request $request): UserResource
    {
        $user = $request->user();

        return new UserResource($user);
    }

    /**
     * Change User Role
     *
     * Changes the user role from user to manager or vice versa.
     *
     * Access Level: Admin
     *
     * @return UserResource
     */
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function changeUserRole(ChangeUserTypeRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        $targetUser = (new ChangeUserRole)->execute($user, $data);

        return new UserResource($targetUser);
    }

    /**
     * Update User
     *
     * Update user's own data such as (name ,  email , password)
     * Password confirmation is required only when there is a new password entered
     *
     * Access Level: N/A
     */
    #[Authenticated]
    #[ResponseFromApiResource(UserResource::class, User::class)]
    public function updateUser(UpdateUserRequest $request): UserResource
    {
        $data = $request->validated();

        $user = (new \App\Services\User\UpdateUser)->execute($request->user(), $data);

        return new UserResource($user);
    }
}
