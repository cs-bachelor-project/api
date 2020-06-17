<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Traits\Planable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    use Planable;

    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        if ($company->stripe_id != null && !empty($company->asStripeCustomer()->subscriptions->data[0])) {
            return response()->json($company->asStripeCustomer()->subscriptions->data[0]->plan, 200);
        }

        return response()->json(['message' => 'You do not have an active subscription.'], 200);
    }

    public function store(Request $request)
    {
        auth()->user()->company->newSubscription('default', $request->get('subscription'))->create($request->get('payment')['id']);

        return response()->json(['message' => 'Subscription created successfully.'], 200);
    }

    public function update(Request $request)
    {
        $current_drivers = auth()->user()->company->users()->whereHas('roles', function ($q) {
            $q->whereName('driver');
        })->count();

        $new_max_drivers = DB::table('plan_options')->select('option', 'value')->where('stripe_plan', $request->get('subscription'))->where('option', 'max-drivers')->first();

        $diff = $current_drivers - $new_max_drivers->value;

        if ($current_drivers > $new_max_drivers->value) {
            return response()->json([
                'errors' => [
                    "You must delete {$diff} driver(s) to downgrade."
                ]
            ], 400);
        }

        auth()->user()->company->subscription('default')->swap($request->get('subscription'));

        return response()->json(['message' => 'Subscription changed successfully.'], 200);
    }
}
