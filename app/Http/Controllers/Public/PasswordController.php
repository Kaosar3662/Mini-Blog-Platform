<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Notifications\Authentication\PasswordResetNotification;

class PasswordController extends BaseController
{
    // Send password reset link
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Generate reset token and expiry (60 minutes)
        $user->password_reset_token = Str::random(64);
        $user->password_reset_expires_at = Carbon::now()->addMinutes(60);
        $user->save();

        // Generate reset URL
        $resetUrl = env('FRONTEND_URL') . '/reset-password?token=' . $user->password_reset_token;

        // Send notification
        $user->notify(new PasswordResetNotification($resetUrl, $user->name));

        return $this->sendResponse([], 'Password reset link sent.');
    }

    // Reset password using token
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:users,password_reset_token',
            'password' => 'required|string|min:6',
            'c_password' => 'required|string',
        ]);

        if ($request->password !== $request->c_password) {
            return $this->sendError('Password and confirm password do not match.', [], 422);
        }

        $user = User::where('password_reset_token', $request->token)->first();

        if (!$user) {
            return $this->sendError('Invalid or expired token.', [], 404);
        }

        // Check expiry
        if ($user->password_reset_expires_at && Carbon::now()->greaterThan($user->password_reset_expires_at)) {
            return $this->sendError('Password reset token has expired.', [], 400);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_expires_at = null;
        $user->save();

        return $this->sendResponse([], 'Password has been reset successfully.');
    }
}
