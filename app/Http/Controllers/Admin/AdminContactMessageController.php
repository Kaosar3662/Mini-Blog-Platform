<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use MessageFormatter;

class AdminContactMessageController extends BaseController
{
    // List all messages with pagination, search, and status filter
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);
        $status = $request->query('status');

        $query = ContactMessage::query();

        // Search by name, email, or subject
        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $messages = $query->offset($offset)
                          ->limit($limit)
                          ->orderBy('created_at', 'desc')
                          ->get();

        return $this->sendResponse([
            'data' => $messages,
            'meta' => [
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'total' => $total
            ]
        ], 'Messages retrieved successfully.');
    }

    // Get single message by ID and mark as read
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->status === 'unread') {
            $message->update(['status' => 'read']);
        }

        return $this->sendResponse($message, 'Message retrieved successfully.');
    }
}
