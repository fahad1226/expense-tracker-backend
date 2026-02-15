<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->categories()
            ->latest()
            ->get();
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $request->user()
            ->categories()
            ->create($request->validated());

        return response()->json($category, 201);
    }

    public function show(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);

        return $category;
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $this->authorizeCategory($request, $category);

        $category->update($request->validated());

        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeCategory($request, $category);

        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    private function authorizeCategory($request, $category)
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
