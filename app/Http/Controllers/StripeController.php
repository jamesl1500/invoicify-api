<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\SetupIntent;
use Stripe\Customer;

use App\Models\Client;

class StripeController extends Controller
{
    // Setup Intent
    public function createSetupIntent(Request $request)
    {
        $client = $request->user();

        // Validate the request
        if (!$client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set the Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create a new customer id
        if(!$client->stripe_customer_id) {
            $customer = Customer::create([
                'email' => $client->email,
                'name' => $client->name,
            ]);

            // Save the customer id to the client
            $client->stripe_customer_id = $customer->id;
            $client->save();
        }

        // Create a SetupIntent
        $setupIntent = SetupIntent::create([
            'customer' => $client->stripe_customer_id,
            'payment_method_types' => ['card'],
        ]);

        // Return the SetupIntent
        return response()->json([
            'client_secret' => $setupIntent->client_secret,
            'customer_id' => $client->stripe_customer_id,
        ], 200);
    }

    // Get saved cards
    public function getSavedCards(Request $request)
    {
        $client = $request->user();

        // Validate the request
        if (!$client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set the Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $methods = \Stripe\PaymentMethod::all([
            'customer' => $client->stripe_customer_id,
            'type' => 'card',
        ]);

        $cards = collect($methods->data)->map(function ($method) {
            return [
                'id' => $method->id,
                'brand' => $method->card->brand,
                'last4' => $method->card->last4,
                'exp_month' => $method->card->exp_month,
                'exp_year' => $method->card->exp_year,
                'billing_details' => $method->billing_details,
            ];
        });

        // Return the payment methods
        return response()->json($cards, 200);
    }

    // Attach payment method
    public function attachPaymentMethod(Request $request)
    {
        $client = $request->user();

        // Validate the request
        if (!$client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set the Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Attach the payment method to the customer
        $paymentMethod = \Stripe\PaymentMethod::retrieve($request->input('payment_method_id'));
        $paymentMethod->attach(['customer' => $client->stripe_customer_id]);

        // Set the default payment method for the customer
        $customer = Customer::update($client->stripe_customer_id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id,
            ],
        ]);

        // Return the payment method
        return response()->json($paymentMethod, 200);
    }

    // Detach payment method
    public function detachPaymentMethod(Request $request, $payment_method_id)
    {
        $client = $request->user();

        // Validate the request
        if (!$client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set the Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Check if the payment method belongs to the customer
        $paymentMethod = \Stripe\PaymentMethod::retrieve($payment_method_id);

        if ($paymentMethod->customer !== $client->stripe_customer_id) {
            return response()->json(['error' => 'Payment method does not belong to the customer'], 403);
        }
        
        // Detach the payment method from the customer
        $paymentMethod = \Stripe\PaymentMethod::retrieve($payment_method_id);
        $paymentMethod->detach();

        // Return a success message
        return response()->json(['message' => 'Payment method detached successfully'], 200);
    }

    /**
     * userOnboard
     * 
     * This method is used to onboard a user to Stripe
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function userOnboarding(Request $request)
    {
        $user = $request->user();

        // Setup stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // If the user already has a stripe account, return the account id
        if ($user->stripe_customer_id) {
            return response()->json(['account_id' => $user->stripe_customer_id], 200);
        }

        // Create a new stripe account
        $account = \Stripe\Account::create([
            'type' => 'express',
            'country' => 'US',
            'email' => $user->email,
        ]);

        // Save the account id to the user
        $user->stripe_customer_id = $account->id;
        $user->save();

        // Create an account link for the user to complete onboarding
        $accountLink = \Stripe\AccountLink::create([
            'account' => $account->id,
            'refresh_url' => env('FRONTEND_URL') . '/auth/signup/refresh',
            'return_url' => env('FRONTEND_URL') . '/auth/signup/complete',
            'type' => 'account_onboarding',
        ]);

        // Return the account link
        return response()->json([
            'url' => $accountLink->url,
            'account_id' => $account->id,
        ], 200);
    }

    /**
     * userOnboardingVerify
     * 
     * This method is used to verify the user onboarding
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOnboardingVerify(Request $request)
    {
        $user = $request->user();

        // Validate the request
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the user has a stripe account
        if (!$user->stripe_customer_id) {
            return response()->json(['error' => 'User does not have a stripe account'], 404);
        }

        // Set the Stripe API key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Retrieve the account
        $account = \Stripe\Account::retrieve($user->stripe_customer_id);

        // Check if the account is fully onboarded
        if($account->details_submitted && $account->charges_enabled) {
            return response()->json(['onboarded' => true, 'account' => $account], 200);
        }
        // Check if the account is not fully onboarded
        if(!$account->details_submitted || !$account->charges_enabled) {
            // Return the link to the user to complete onboarding
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id,
                'refresh_url' => env('FRONTEND_URL') . '/auth/signup/refresh',
                'return_url' => env('FRONTEND_URL') . '/auth/signup/complete',
                'type' => 'account_onboarding',
            ]);
            
            return response()->json(['onboarded' => false, 'account' => $account, 'url' => $accountLink], 200);
        }
    }
}
