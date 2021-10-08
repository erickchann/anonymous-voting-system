<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = request(['username', 'password']);
        
        if ($validator->fails() || !Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if (!Auth::user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        return response()->json(Auth::user());
    }
    
    public function reset(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $old_password = $request->old_password;
        $new_password = $request->new_password;
        
        if (!Auth::attempt(['username' => $user->username, 'password' => $old_password])) {
            return response()->json(['message' => 'old password did not match'], 422);
        }

        User::find($user->id)->update(['password' => bcrypt($new_password)]);

        Auth::logout();
        return response()->json(['message' => 'reset success, user logged out'], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        if (!Auth::user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
