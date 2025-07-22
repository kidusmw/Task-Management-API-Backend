<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google’s OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback()
    {
        try {
            // Get the user information from Google
            $user = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect('/')->with('error', 'Google authentication failed.');
        }

        // Check if the user already exists in the database
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            Auth::login($existingUser);
            $userToLogin = $existingUser;
        } else {
            $newUser = User::updateOrCreate([
                'email' => $user->email
            ], [
                'name' => $user->name,
                'password' => Hash::make(Str::random(16)),
                'email_verified_at' => now()
            ]);
            Auth::login($newUser);
            $userToLogin = $newUser;
        }

        // Generate a token for the user
        $token = $userToLogin->createToken('auth_token')->plainTextToken;

        // Pass user details and token to frontend
        $frontendUrl = env('FRONTEND_URL', 'http://127.0.0.1:5173'); // Use environment variable
        return redirect($frontendUrl . '/?token=' . $token . '&name=' . urlencode($userToLogin->name) . '&email=' . urlencode($userToLogin->email));
    }
}
