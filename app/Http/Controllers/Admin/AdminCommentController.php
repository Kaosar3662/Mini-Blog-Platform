<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Comment;
use Illuminate\Http\Request;

class AdminCommentController extends BaseController
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $limit = (int) $request->query('limit', 10);
        $offset = (int) $request->query('offset', 0);

        $query = Comment::with(['post', 'user']);

        if ($search) {
            $query->where('comment_text', 'like', '%' . $search . '%')
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $comments = $query->limit($limit)->offset($offset)->get();

        if ($comments->isEmpty()) {
            return $this->sendResponse([
                'total' => $total,
                'data' => [],
            ], 'No comments found.');
        }

        $data = $comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'post_id' => $comment->post_id,
                'post_slug' => $comment->post->slug,
                'post_title' => $comment->post->title ?? '',
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->name ?? '',
                'comment_text' => $comment->comment_text,
                'status' => $comment->status,
                'created_at' => $comment->created_at,
            ];
        });

        return $this->sendResponse([
            'total' => $total,
            'data' => $data,
            'limit' => $limit,
            'offset' => $offset,
        ], 'Comments retrieved successfully.');
    }


    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved']);

        return $this->sendResponse(null, 'Comment approved.');
    }

    public function hide($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'hidden']);

        return $this->sendResponse(null, 'Comment hidden.');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return $this->sendResponse(null, 'Comment deleted.');
    }


    public function show($id)
    {
        $comment = Comment::with(['post', 'user'])->findOrFail($id);
        return $this->sendResponse($comment, 'Comment retrieved successfully.');
    }
}
