<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
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
            return response()->json(['message' => 'An error occured while creating the token'], 500);
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => "email|max:255|unique:users,email,{$id}",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        auth()->user()->update($request->all());

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

        $validator = Validator::make($request->all(), [
            'cvr' => 'required|size:8',
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'postal' => 'required|size:4',
            'city' => 'required|max:255',
            'street' => 'required|max:255',
            'street_number' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }
        
        auth()->user()->company()->update($request->all());

        return response()->json(['message' => "The changes were saved successfully."]);
    }

    /**
     * Change password of the authenticated User.
     *
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        auth()->user()->update([
            'password' => bcrypt($request->get('password')),
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
