<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Handle forgot password
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgot(Request $request)
    {
        $email = $request->get('email');

        $user = DB::table('users')->whereEmail($email)->first();

        if ($user) {
            try {
                $this->deleteTokens($email);

                $this->createToken($email);
            } catch (\Exception $e) {
                return response()->json(['message' => 'An error occured while creating the token.'], 500);
            }
        }

        return response()->json(['message' => 'If the entered email matched our records, an email has been sent with instructions for password resetting.']);
    }

    /**
     * Change password
     *
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request, $token)
    {
        $email = $request->get('email');

        $reset = DB::table('password_resets')->whereToken($token)->whereEmail($email)->first();

        if ($reset && Carbon::create($reset->created_at)->diffInSeconds(Carbon::now()) <= env('RESET_LINK_EXPIRY')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 422);
            }

            DB::table('users')->whereEmail($email)->update([
                'password' => bcrypt($request->get('password')),
            ]);

            return response()->json(['message' => 'The password was updated successfully.']);
        }

        return response()->json(['message' => 'The password reset link is either invalid or expired.'], 400);
    }

    /**
     * Create reset token
     */
    protected function createToken($email)
    {
        return DB::table('password_resets')->insert(['email' => $email, 'token' => Str::random(75), 'created_at' => Carbon::now()]);;
    }

    /**
     * Delete all user's reset tokens
     */
    protected function deleteTokens($email)
    {
        return DB::table('password_resets')->whereEmail($email)->delete();
    }
}
