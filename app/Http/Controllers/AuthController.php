<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Swagger\SwaggerComponents;
use App\Http\Requests\StoreUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    use HttpResponses;

    /**
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"Authentication"},
 *     summary="Register a new user",
 *     description="Register a new user and generate an authentication token",
 *     operationId="register",
 *     @OA\RequestBody(
 *         required=true,
 *         description="User registration data",
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "email", "password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User registration successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="user", type="object"),
 *             @OA\Property(property="token", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string"))
 *             )
 *         )
 *     )
 * )
 */


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

     /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="Log in an existing user",
     *     description="Authenticate user and generate a token",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User login credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid login details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid login details")
     *         )
     *     )
     * )
     */

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


    /**
     * @OA\Post(
     *     path="/api/auth/reset-password",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     description="Reset the user's password",
     *     operationId="resetPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Password reset data",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
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


    /**
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Authentication"},
 *     summary="Log out the current user",
 *     description="Invalidate the user's authentication token",
 *     operationId="logout",
 *     security={{"sanctum": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="User logout successful",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Logged out successfully")
 *         )
 *     )
 * )
 */

 public function logout(Request $request) {
    $user = $request->user();  // Get the authenticated user

    if ($user) {
        $user->currentAccessToken()->delete();
        return $this->successResponse('', 'Logged out successfully');
    } else {
        // If no user is authenticated, return an error response
        return $this->errorResponse('User not authenticated', 401);
    }
}













}
