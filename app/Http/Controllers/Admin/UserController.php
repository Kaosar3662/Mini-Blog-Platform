<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $search = $request->query('search');
        $roleFilter = $request->query('role');     // 'moderator' or 'blogger'
        $statusFilter = $request->query('status'); // 'active' or 'inactive'

        $limit = (int) $request->query('limit', 20);
        $offset = (int) $request->query('offset', 0);

        $query = User::select('id', 'name', 'email', 'role', 'status')->where('role', '!=', 'admin');;

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $total = $query->count();
        $users = $query->limit($limit)->offset($offset)->get();

        return $this->sendResponse([
            'data' => $users,
            'total' => $total
        ], 'Users retrieved successfully.');
    }



    /**
     * Update a user (Admin only)
     */
    public function update(Request $request, $email)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return $this->sendResponse(null, 'Unauthorized', false, 403);
        }

        $user = User::where('email', $email)->firstOrFail();

        // Validate request
        $validated = $request->validate([
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'name' => ['sometimes', 'string', 'min:3'],
            'password' => ['sometimes', 'string', 'min:6'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'role' => ['sometimes', Rule::in(['moderator', 'blogger'])],
        ]);

        // Update fields
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['status'])) {
            $user->status = $validated['status'];
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        $user->save();

        return $this->sendResponse([
            'data' => $user
        ], 'User updated successfully.');
    }
}
