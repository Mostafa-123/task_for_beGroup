<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ManageFileTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use function App\apiResponse;

class AuthController extends Controller
{

    use ManageFileTrait;
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:25',
                'email' => 'required|email|unique:users',
                'phone' => 'required|min:8|max:15',
                'password' => [
                    'required',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/',
                    'min:8',
                ],
                'image' => 'nullable|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'image.mimes' => 'Image must be a file of type: jpg, jpeg, png, gif, webp.',
            ]);
            $imagePath = $this->uploadFile($request, 'image', 'UsersPhotos');
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->image = $imagePath;
            $user->save();
            $user->load(['created_tasks', 'assign_tasks']);
            return apiResponse(201, new UserResource($user), 'User Registered Successfully');

        } catch (ValidationException $e) {
            return apiResponse(
                422,
                $e->errors(),
                'Validation Failed'
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->with(['created_tasks', 'assign_tasks'])->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return apiResponse(401, [], 'Invalid email or password');
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return apiResponse(201, [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user)
            ], 'Login successful');

        } catch (ValidationException $e) {
            return apiResponse(
                422,
                $e->errors(),
                'Validation Failed'
            );
        }
    }
    public function logout()
    {
        $user = auth('sanctum')->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return apiResponse(200, [], "Logged Out Successfully");
    }

    public function profile()
    {
        $user = auth('sanctum')->user()->load(['created_tasks', 'assign_tasks']);

        return apiResponse(200, new UserResource($user), "User Profile");

    }

}
