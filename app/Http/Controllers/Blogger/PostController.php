<?php

namespace App\Http\Controllers\Blogger;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends BaseController
{
    // Validation rules for store and update
    protected $postValidationRules = [
        'title' => 'required|string|min:5|max:255',
        'short_desc' => 'required|string|min:20|max:500',
        'content' => 'required|string|min:50',
        'category_id' => 'required|exists:categories,id',
        'status' => 'nullable|in:draft,pending',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ];

    // List blogger's posts with pagination and filters
    public function index(Request $request)
    {
        $search = request()->query('search');
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 0);

        $query = Post::with('category:id,name')->where('user_id', Auth::id());

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

    // View a single post
    public function show($slug)
    {
        $post = Post::where('slug', $slug)->where('user_id', Auth::id())->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        return $this->sendResponse($post, 'Post retrieved successfully.');
    }

    // Create a new post
    public function store(Request $request)
    {
        $request->validate($this->postValidationRules);

        $status = $request->input('status');
        if ($status !== 'draft') {
            $status = 'pending';
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('posts', 'public');
        }

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'short_desc' => $request->short_desc,
            'slug' => $slug,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'cover_image' => $coverImagePath,
            'status' => $status,
        ]);

        return $this->sendResponse($post, 'Post created successfully.', 201);
    }

    // Update a post
    public function update(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->where('user_id', Auth::id())->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        // Convert 'required' to 'sometimes' for update
        $updateRules = $this->postValidationRules;
        foreach ($updateRules as $key => $rule) {
            $updateRules[$key] = str_replace('required', 'sometimes', $rule);
        }

        $request->validate($updateRules);

        $post->title = $request->input('title', $post->title);
        $post->short_desc = $request->input('short_desc', $post->short_desc);
        $post->content = $request->input('content', $post->content);
        $post->category_id = $request->input('category_id', $post->category_id);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('posts', 'public');
            $post->cover_image = $coverImagePath;
        }

        // Set status
        $status = $request->input('status');
        if ($status !== 'draft') {
            $status = 'pending';
        }
        $post->status = $status;

        //TODO: Add a Helper for Slug Making

        // Generate unique slug if title changed
        if ($request->has('title') && $request->title !== $post->title) {
            $baseSlug = Str::slug($request->title);
            $newSlug = $baseSlug;
            while (Post::where('slug', $newSlug)->where('id', '!=', $post->id)->exists()) {
                $newSlug = $baseSlug . '-' . date('sihdmy');
            }
            $post->slug = $newSlug;
        }

        $post->save();

        return $this->sendResponse($post, 'Post updated successfully.');
    }

    // Soft delete a post
    public function destroy($slug)
    {
        $post = Post::where('slug', $slug)->where('user_id', Auth::id())->first();

        if (!$post) {
            return $this->sendError('Post not found.', [], 404);
        }

        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully.');
    }
}
