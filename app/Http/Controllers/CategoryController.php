<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = $request->user()
            ->categories()
            ->withCount('expenses')
            ->withSum('expenses', 'amount')
            ->latest()
            ->get();

        $totalAmountSpent = $request->user()->expenses()->sum('amount');

        return response()->json([
            'categories' => $categories,
            'total_amount_spent' => (float) $totalAmountSpent,
        ]);
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
