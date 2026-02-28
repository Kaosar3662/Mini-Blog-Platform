<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminPostController extends BaseController
{
    // List all posts with pagination and filters
    public function index(Request $request)
    {
        $search = request()->query('search');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);


        $query = Post::with('category:id,name')->where('user_id', Auth::id())->with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('short_desc', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $query->whereNull('deleted_at');

        $total = $query->count();
        $posts = $query->offset($offset)->limit($limit)->orderBy('created_at', 'desc')->get();

        return $this->sendResponse([
            'data' => $posts,
            'meta' => [
                'limit' => (int)$limit,
                'offset' => (int)$offset,
                'total' => $total
            ]
        ], 'Posts retrieved successfully.');
    }

    // View a single post
    public function show($slug)
    {
        $post = Post::where('slug', $slug)->whereNull('deleted_at')->with('user')->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        return $this->sendResponse($post, 'Post retrieved successfully.');
    }

    // Approve a post
    public function approve($slug)
    {
        $post = Post::where('slug', $slug)->whereNull('deleted_at')->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        if ($post->status === 'draft') {
            return $this->sendError('Cannot approve a draft post.', [], 400);
        }

        $post->status = 'approved';
        $post->published_at = Carbon::now();
        // $post->approved_by = Auth::id();
        $post->save();

        return $this->sendResponse($post, 'Post approved successfully.');
    }

    // Reject a post
    public function reject($slug)
    {
        $post = Post::where('slug', $slug)->whereNull('deleted_at')->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        if ($post->status === 'draft') {
            return $this->sendError('Cannot reject a draft post.', [], 400);
        }

        $post->status = 'rejected';
        $post->published_at = null;
        $post->save();

        return $this->sendResponse($post, 'Post rejected successfully.');
    }
}
