<?php

use App\Http\Controllers\ContributionCategoryController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin-only', function () {
            return 'Admin access granted.';
        })->name('admin.only');
    });

    Route::middleware(['role:admin,treasurer'])->group(function () {
        Route::get('/finance-only', function () {
            return 'Finance access granted.';
        })->name('finance.only');

        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');

        Route::get('/contribution-categories', [ContributionCategoryController::class, 'index'])
            ->name('contribution-categories.index');
        Route::post('/contribution-categories', [ContributionCategoryController::class, 'store'])
            ->name('contribution-categories.store');
        Route::get('/contribution-categories/{contributionCategory}/edit', [ContributionCategoryController::class, 'edit'])
            ->name('contribution-categories.edit');
        Route::put('/contribution-categories/{contributionCategory}', [ContributionCategoryController::class, 'update'])
            ->name('contribution-categories.update');

        Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index');
        Route::get('/contributions/create', [ContributionController::class, 'create'])->name('contributions.create');
        Route::post('/contributions', [ContributionController::class, 'store'])->name('contributions.store');
        Route::patch('/contributions/{contribution}/void', [ContributionController::class, 'void'])
            ->name('contributions.void');
    });

    Route::middleware(['role:member'])->group(function () {
        Route::get('/member-only', function () {
            return 'Member access granted.';
        })->name('member.only');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
