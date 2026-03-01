<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Services\PostQueryService;
use Carbon\Carbon;

class AdminPostController extends BaseController
{
    // List all posts with pagination and filters
    public function index(Request $request, PostQueryService $service)
    {
        $query = Post::with(['category:id,name', 'user'])
            ->whereNull('deleted_at');

        $result = $service->filterAndPaginate($query, $request);

        return $this->sendResponse($result, 'Posts retrieved successfully.');
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
