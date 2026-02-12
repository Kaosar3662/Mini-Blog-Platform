<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends BaseController
{
    public function index()
    {
        $categories = Category::select('id', 'name', 'slug')->get();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return $this->sendResponse([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 'Created successfully.', 201);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Not found.', null, 404);
        }
        return $this->sendResponse([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 'Category retrieved successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Category not found.', null, 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        
        return $this->sendResponse([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 'Updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->sendError('Not found.', null, 404);
        }

        $category->delete();
        return $this->sendResponse(null, 'Deleted successfully.');
    }
}
