<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        try {
            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['message' => 'Invalid Credentials'], 401);
            }

            if (request('type') == 'driver' && !auth()->user()->hasAnyRole('driver')) {
                return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
            }

            if (request('type') == 'management' && !auth()->user()->hasAnyRole('admin', 'manager')) {
                return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'An error occured while creating the token', 'message' => $e->getMessage()], 500);
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
        return response(new UserResource(withRelations(auth()->user())), 200);
    }

    /**
     * Update the authenticated User.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = auth()->user()->id;
        $data = $request->validate([
            'name' => 'string|max:255',
            'email' => "string|email|max:255|unique:users,email,{$id}",
        ]);

        auth()->user()->update($data);

        return response()->json(['message' => "The changes were saved successfully."]);
    }

    /**
     * Update the authenticated User's company.
     *
     * @return \Illuminate\Http\Response
     */
    public function company(Request $request)
    {
        if (!$request->user()->hasAnyRole('admin')) {
            return response()->json(['message' => 'You are not authorised to perform this action.'], 401);
        }

        $data = $request->validate(
            [
                'name' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'street' => 'required|string|max:255',
                'street_number' => 'required|string|max:255',
            ],
        );

        auth()->user()->company()->update($data);

        return response()->json(['message' => "The changes were saved successfully."]);
    }

    /**
     * Change password of the authenticated User.
     *
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        auth()->user()->update([
            'password' => bcrypt($data['password']),
        ]);

        return response()->json(['message' => 'The password was updated successfully.']);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
