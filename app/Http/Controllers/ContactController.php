<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    public function confirm(StoreContactRequest $request): View
    {
        $validated = $request->validated();

        $category = Category::findOrFail($validated['category_id']);

        $tags = isset($validated['tag_ids'])
            ? Tag::whereIn('id', $validated['tag_ids'])->get()
            : collect();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }
}
