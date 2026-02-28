<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Notifications\Authentication\VerifyEmailNotification;
use Carbon\Carbon;

class EmailVerificationController extends BaseController
{
    // Verify email using token with expiry check
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (!$user) {
            return $this->sendError('Invalid or expired token.', [], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse([], 'Email already verified.');
        }

        if ($user->email_verification_expires_at && Carbon::now()->greaterThan($user->email_verification_expires_at)) {
            return $this->sendError('Verification link has expired.', [], 410);
        }

        $user->markEmailAsVerified();
        $user->email_verification_token = null;
        $user->email_verification_expires_at = null;
        $user->save();

        return $this->sendResponse([], 'Email verified successfully.');
    }

    // Resend verification email
    public function resend(Request $request)
    {

        if ($request->token) {
            $user = User::where('email_verification_token', $request->token)->first();
        } elseif ($request->email) {
            $user = User::where('email', $request->email)->first();
        } else {
            return $this->sendError('Email or token is required.', [], 400);
        }

        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }
        if ($user->hasVerifiedEmail()) {
            return $this->sendResponse([], 'Email already verified.');
        }

        // Generate new token and expiry (60 minutes)
        $user->email_verification_token = Str::random(64);
        $user->email_verification_expires_at = Carbon::now()->addMinutes(60);
        $user->save();

        // Generate verification URL
        $verificationUrl = env('FRONTEND_URL') . '/verify-email?token=' . $user->email_verification_token;

        // Send notification
        $user->notify(new VerifyEmailNotification($verificationUrl, $user->name));

        return $this->sendResponse([], 'Verification email resent.');
    }
}
