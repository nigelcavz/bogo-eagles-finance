<?php

use App\Http\Controllers\ContributionCategoryController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin-only', function () {
            return 'Admin access granted.';
        })->name('admin.only');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}/role', [UserManagementController::class, 'update'])->name('users.update-role');
    });

    Route::middleware(['role:admin,president,treasurer'])->group(function () {
        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
        Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    });

    Route::middleware(['role:admin,treasurer'])->group(function () {
        Route::get('/finance-only', function () {
            return 'Finance access granted.';
        })->name('finance.only');

        Route::get('/contribution-categories', [ContributionCategoryController::class, 'index'])
            ->name('contribution-categories.index');
        Route::post('/contribution-categories', [ContributionCategoryController::class, 'store'])
            ->name('contribution-categories.store');
        Route::get('/contribution-categories/{contributionCategory}/edit', [ContributionCategoryController::class, 'edit'])
            ->name('contribution-categories.edit');
        Route::put('/contribution-categories/{contributionCategory}', [ContributionCategoryController::class, 'update'])
            ->name('contribution-categories.update');

        Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index');
        Route::get('/contributions/monthly-availability', [ContributionController::class, 'monthlyAvailability'])
            ->name('contributions.monthly-availability');
        Route::get('/contributions/type/{type}', [ContributionController::class, 'showType'])
            ->name('contributions.types.show');
        Route::get('/contributions/create', [ContributionController::class, 'create'])->name('contributions.create');
        Route::post('/contributions', [ContributionController::class, 'store'])->name('contributions.store');
        Route::get('/contributions/{contribution}/edit', [ContributionController::class, 'edit'])
            ->name('contributions.edit');
        Route::put('/contributions/{contribution}', [ContributionController::class, 'update'])
            ->name('contributions.update');
        Route::patch('/contributions/{contribution}/void', [ContributionController::class, 'void'])
            ->name('contributions.void');

        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('/expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void');
    });

    Route::middleware(['role:member,officer,president,treasurer'])->group(function () {
        Route::get('/member-only', function () {
            return 'Member access granted.';
        })->name('member.only');

        Route::get('/my-member-profile', [MemberController::class, 'self'])
            ->name('members.self');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
