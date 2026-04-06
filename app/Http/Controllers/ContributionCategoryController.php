<?php

namespace App\Http\Controllers;

use App\Models\ContributionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributionCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ContributionCategory::query()
            ->withCount('contributions')
            ->orderBy('name')
            ->get();

        return view('contribution-categories.index', compact('categories'));
    }

    public function edit(ContributionCategory $contributionCategory): View
    {
        return view('contribution-categories.edit', compact('contributionCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contribution_categories,name'],
            'description' => ['nullable', 'string'],
            'default_amount' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        ContributionCategory::create($validated);

        return redirect()
            ->route('contribution-categories.index')
            ->with('success', 'Contribution category created successfully.');
    }

    public function update(Request $request, ContributionCategory $contributionCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contribution_categories,name,' . $contributionCategory->id],
            'description' => ['nullable', 'string'],
            'default_amount' => ['nullable', 'numeric', 'gt:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $contributionCategory->update($validated);

        return redirect()
            ->route('contribution-categories.index')
            ->with('success', 'Contribution category updated successfully.');
    }
}
