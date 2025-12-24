<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $categories = Category::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_system', true);
        })->orderBy('is_system', 'desc')
            ->orderBy('name_ar')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        Category::create([
            'user_id' => $request->user()->id,
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'color' => $validated['color'],
            'icon' => $validated['icon'] ?? null,
            'is_system' => false,
        ]);

        return redirect()->route('categories.index')
            ->with('success', __('category_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        $this->authorize('view', $category);

        return Inertia::render('Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', __('category_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        // Don't allow deleting system categories
        if ($category->is_system) {
            return redirect()->back()
                ->with('error', __('cannot_delete_system_category'));
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', __('category_deleted'));
    }
}
