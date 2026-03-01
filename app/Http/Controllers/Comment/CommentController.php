<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\BaseController;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends BaseController
{
    public function store(Request $request, $slug)
    {
        $request->validate([
            'comment_text' => 'required|string|max:2000'
        ]);

        $post = Post::where('slug', $slug)->firstOrFail();

        Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => null,
            'comment_text' => $request->comment_text,
            'status' => 'pending'
        ]);

        return $this->sendResponse(null, 'Comment submitted for approval.');
    }

    public function reply(Request $request, $slug, $parentId)
    {
        $request->validate([
            'comment_text' => 'required|string|max:2000'
        ]);

        $post = Post::where('slug', $slug)->firstOrFail();

        $parentComment = Comment::where('id', $parentId)
            ->where('post_id', $post->id)
            ->firstOrFail();

        if ($parentComment->status === 'hidden') {
            return $this->sendError('You cannot reply to a hidden comment.', [], 403);
        }

        Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $parentComment->id,
            'comment_text' => $request->comment_text,
            'status' => 'pending'
        ]);

        return $this->sendResponse(null, 'Reply submitted for approval.');
    }
}
