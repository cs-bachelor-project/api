<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mailjet\Client as MailjetClient;
use \Mailjet\Resources as MailjetResources;

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
        $validator = Validator::make(
            $request->all(),
            [
                'company.cvr' => 'required|size:8|unique:companies,cvr',
                'company.name' => 'required|max:255',
                'company.country' => 'required|max:255',
                'company.postal' => 'required|size:4',
                'company.city' => 'required|max:255',
                'company.street' => 'required|max:255',
                'company.street_number' => 'required|max:255',
                'user.name' => 'required|max:255',
                'user.email' => 'required|email|max:255|unique:users,email',
                'user.password' => 'required|min:6|confirmed',
            ],
            [
                'company.cvr.required' => 'The CVR is required.',
                'company.cvr.size' => 'The CVR must be of 8 digits.',
                'company.cvr.unique' => 'A company with this CVR is already registered.',
                'company.name.required' => 'The company name is required.',
                'company.country.required' => 'The country is required.',
                'company.postal.required' => 'The postal code is required.',
                'company.postal.size' => 'The postal code must be of 4 digits.',
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

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        DB::transaction(function () use ($request) {
            $company = Company::create([
                'cvr' => $request->input('company.cvr'),
                'name' => $request->input('company.name'),
                'country' => $request->input('company.country'),
                'postal' => $request->input('company.postal'),
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

        $this->successMail($request->input('user.email'), $request->input('user.name'), $request->input('company.name'));

        $this->createStripeCustomer($request->get('company'), $request->get('user'));

        return response()->json([
            'message' => 'Registration completed successfully',
        ], 201);
    }

    public function successMail($email, $userName, $companyName)
    {
        $mj = new MailjetClient(env('MJ_APIKEY_PUBLIC'), env('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => env('EMAIL_ADDRESS'),
                        'Name' => env('APP_NAME'),
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $userName,
                        ]
                    ],
                    'TemplateID' => 1393925,
                    'TemplateLanguage' => true,
                    'Subject' => 'Welcome',
                    'Variables' => [
                        'company_name' => $companyName,
                        'user_name' => $userName,
                    ]
                ]
            ]
        ];

        $mj->post(MailjetResources::$Email, ['body' => $body]);
    }

    public function createStripeCustomer($company, $user)
    {
        Company::firstWhere('cvr', $company['cvr'])->createAsStripeCustomer([
            'name' => $company['name'],
            'email' => $user['email'],
            'address' => [
                'line1' => "{$company['street']} {$company['street_number']}",
                'city' => $company['city'],
                'country' => $company['country'],
                'postal_code' => $company['postal'],
            ]
        ]);
    }
}
