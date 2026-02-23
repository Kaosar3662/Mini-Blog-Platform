<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateModerator extends BaseController
{
    // Admin creates a new moderator
    public function store(Request $request)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return $this->sendError('Unauthorized. Only admin can create moderators.', null, 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $moderator = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'moderator',
        ]);

        return $this->sendResponse([
            'moderator' => $moderator->name,
            'role' => $moderator->role,
        ], 'Moderator created successfully', 201);
    }
}
