<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Notifications\Authentication\VerifyEmailNotification;
use Carbon\Carbon;

class AuthController extends BaseController
{
    // Register new blogger
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Generate email verification token
        $emailVerificationToken = Str::random(64);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'blogger',
            'email_verification_token' => $emailVerificationToken,
        ]);

        // Generate new token and expiry (60 minutes)
        $user->email_verification_token = Str::random(64);
        $user->email_verification_expires_at = Carbon::now()->addMinutes(60);
        $user->save();

        // Generate verification URL
        $verificationUrl = env('FRONTEND_URL') . '/verify-email?token=' . $user->email_verification_token;

        // Send notification
        $user->notify(new VerifyEmailNotification($verificationUrl, $user->name));

        return $this->sendResponse([
            'user' => $user->name,
        ], 'Blogger registered successfully. Verification email sent.', 201);
    }

    // Login user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('No user found with this email.', null, 404);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is Disabled or Deleted.'
            ], 403);
        }
        if ($user->email_verified_at === null) {
            return response()->json(['message' => 'Please verify your email first'], 403);
        }

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->sendError('The credentials are wrong.', null, 401);
        }
        $token = $user->createToken($user->role . '-token')->plainTextToken;
        return $this->sendResponse([
            'user' => $user->name,
            'role' => $user->role,
            'token' => $token
        ], 'Login successful', 200);
    }
}
