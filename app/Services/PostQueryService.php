<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PostQueryService
{
  public function filterAndPaginate(Builder $query, Request $request)
  {
    $search = $request->query('search');
    $limit = $request->query('limit', 10);
    $offset = $request->query('offset', 0);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('short_desc', 'like', "%{$search}%")
          ->orWhere('content', 'like', "%{$search}%");
      });
    }

    if ($request->filled('category_id')) {
      $query->where('category_id', $request->query('category_id'));
    }

    if ($request->filled('status')) {
      $query->where('status', $request->query('status'));
    }

    $total = $query->count();

    $data = $query->offset($offset)
      ->limit($limit)
      ->orderBy('created_at', 'desc')
      ->get();

    return [
      'data' => $data,
      'meta' => [
        'limit' => (int) $limit,
        'offset' => (int) $offset,
        'total' => $total,
      ]
    ];
  }
}
