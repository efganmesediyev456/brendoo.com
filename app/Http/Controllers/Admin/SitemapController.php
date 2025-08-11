<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Content;
use App\Models\Link;
use App\Models\Product;
use App\Models\Rule;
use App\Models\Service;
use App\Models\Single;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    
    public function index()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $perPage = 1000;
        $pages = ceil($totalProducts / $perPage);

        return response()->view('front.sitemap-index', compact('pages'))
                         ->header('Content-Type', 'application/xml');
    }

    public function products($page)
    {
        $perPage = 1000;
        $products = Product::where('is_active', true)->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->view('front.sitemap-products', compact('products'))
                         ->header('Content-Type', 'application/xml');
    }

    public function static()
    {
        $pages = Rule::get();
        return response()->view('front.sitemap-static', compact('pages'))
                        ->header('Content-Type', 'application/xml');
    }

}
