<?php

namespace App\Http\Controllers;

use App\FaqCategory;
use Illuminate\Support\Facades\Cache;

class PagesController extends Controller {

    public function home()
    {
        return view('pages.home');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function rewards()
    {
        return view('pages.rewards');
    }

    public function contacts()
    {
        $cacheKey = 'FAQ_ALL';
        $categories = Cache::remember($cacheKey, now()->addDays(30), function () {
            return FaqCategory::with('questions')->get();
        });

        return view('pages.contacts', compact('categories'));
    }

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function termsAndConditions()
    {
        return view('pages.terms-and-conditions');
    }
}
