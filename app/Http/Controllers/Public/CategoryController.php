<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = Category::select('name', 'slug')->get();
        return $this->sendResponse($categories, 'Categories retrieved successfully.');
    }
}
