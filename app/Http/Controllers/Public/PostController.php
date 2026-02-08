<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return "Some Function will be here";
    }

    public function show($slug)
    {
        return "Some Function will be here";
    }
}
