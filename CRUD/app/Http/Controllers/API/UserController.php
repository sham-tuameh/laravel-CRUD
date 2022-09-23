<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    //Sanctum

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6']
        ]);
        $user = User::query()->create([
            'name' => request('name'),
            //or we can use this  'name' => $request->name,
            'email' => request('email'),
            'password' => bcrypt(request('password'))
        ]);
        $authToken = $user->createToken('access-token')->plainTextToken;
        return response()->json([
            'access_token' => $authToken,
            'message' => 'user authorized'
        ]);
    }


    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::query()->where('email', '=', $request['email'])->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $authToken = $user->createToken('auth-token')->plainTextToken;
        return response()->json([
            'access_token' => $authToken,
        ]);

    }

    public function logout(Request $request): JsonResponse
    {
        $deleted = $request->user()->currentAccessToken()->delete();
        return $deleted == '1' ? response()->json(['message' => 'done']) : $deleted;
    }

}
