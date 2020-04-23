<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validate(
            [
                'company.name' => 'required|string|max:255',
                'company.country' => 'required|string|max:255',
                'company.city' => 'required|string|max:255',
                'company.street' => 'required|string|max:255',
                'company.street_number' => 'required|string|max:255',
                'user.name' => 'required|string|max:255',
                'user.email' => 'required|string|email|max:255|unique:users,email',
                'user.password' => 'required|string|min:6|confirmed',
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
                ]
            );

        DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data['company']['name'],
                'country' => $data['company']['country'],
                'city' => $data['company']['city'],
                'street' => $data['company']['street'],
                'street_number' => $data['company']['street_number'],
            ]);


            $user = $company->users()->create([
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password' => bcrypt($data['user']['password']),
            ]);

            $user->roles()->attach(1);
        });

        return response()->json([
            'message' => 'Registration completed successfully',
        ], 201);
    }
}
