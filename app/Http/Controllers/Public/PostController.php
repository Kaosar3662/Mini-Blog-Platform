<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);

        $query = Post::where('status', 'approved')
            ->whereNull('deleted_at')
            ->with(['category:id,name', 'user:id,name']);

        // Search by title, short description, or content
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('short_desc', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        $total = $query->count();

        $posts = $query->offset($offset)
            ->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'data' => $posts,
            'meta' => [
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'total' => $total
            ]
        ], 'Posts retrieved successfully.');
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'approved')
            ->whereNull('deleted_at')
            ->with(['category:id,name', 'user:id,name'])
            ->first();

        if (!$post) {
            return $this->sendError('Post not found.', null, 404);
        }

        return $this->sendResponse($post, 'Success');
    }
}
