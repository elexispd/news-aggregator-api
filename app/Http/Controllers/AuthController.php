<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use HttpResponses;

    public function register(StoreUserRequest $request) {
        $request->validated($request->all());
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return $this->successResponse([
            'user' => $user,
            'token' => $user->createToken('authToken'.$user->user)->plainTextToken,

        ]);

    }

    public function login(LoginUserRequest $request) {
        $request->validated($request->all());
        if(!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('', 'Invalid login details', 401);
        }

        $user = User::where('email', $request->email)->first() ;
        return $this->successResponse([
            'user' => $user,
            'token' => $user->createToken('authToken'.$user->user)->plainTextToken,

        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse('', 'User not found', 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse('', 'Password reset successfully');
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse('', 'Logged out successfully');
    }












}
