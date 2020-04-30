<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Store company and user resources in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),             [
            'company.name' => 'required|max:255',
            'company.country' => 'required|max:255',
            'company.city' => 'required|max:255',
            'company.street' => 'required|max:255',
            'company.street_number' => 'required|max:255',
            'user.name' => 'required|max:255',
            'user.email' => 'required|email|max:255|unique:users,email',
            'user.password' => 'required|min:6|confirmed',
        ],
        [
            'company.name.required' => 'The company name is required.',
            'company.country.required' => 'The country is required.',
            'company.city.required' => 'The city is required.',
            'company.street.required' => 'The street is required.',
            'company.street_number.required' => 'The street number is required.',
            'user.name.required' => 'Your name is required.',
            'user.email.required' => 'Your email is required.',
            'user.email.email' => 'Your email must be a valid email address.',
            'user.email.unique' => 'The email has already been taken.',
            'user.password.required' => 'The password is required.',
            'user.password.confirmed' => 'The password confirmation does not match.',
            'user.password.min' => 'The password must be at least 6 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        DB::transaction(function () use ($request) {
            $company = Company::create([
                'name' => $request->input('company.name'),
                'country' => $request->input('company.country'),
                'city' => $request->input('company.city'),
                'street' => $request->input('company.street'),
                'street_number' => $request->input('company.street_number'),
            ]);


            $user = $company->users()->create([
                'name' => $request->input('user.name'),
                'email' => $request->input('user.email'),
                'password' => bcrypt($request->input('user.password')),
            ]);

            $user->roles()->attach(1);
        });

        return response()->json([
            'message' => 'Registration completed successfully',
        ], 201);
    }
}
