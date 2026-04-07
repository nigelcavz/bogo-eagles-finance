<?php

use App\Http\Controllers\ContributionCategoryController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:' . User::ROLE_ADMIN])->group(function () {
        Route::get('/admin-only', function () {
            return 'Admin access granted.';
        })->name('admin.only');

        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}/role', [UserManagementController::class, 'update'])->name('users.update-role');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('users.update-status');
        Route::get('/activity-tracker', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::middleware(['role:' . implode(',', User::memberViewerRoles())])->group(function () {
        Route::get('/members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    });

    Route::middleware(['role:' . implode(',', User::memberManagerRoles())])->group(function () {
        Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
        Route::post('/members', [MemberController::class, 'store'])->name('members.store');
        Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    });

    Route::middleware(['role:' . implode(',', User::memberStatusManagerRoles())])->group(function () {
        Route::patch('/members/{member}/status', [MemberController::class, 'updateStatus'])->name('members.update-status');
    });

    Route::middleware(['role:' . implode(',', User::announcementManagerRoles())])->group(function () {
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    Route::middleware(['role:' . implode(',', User::financeViewerRoles())])->group(function () {
        Route::get('/finance-only', function () {
            return 'Finance access granted.';
        })->name('finance.only');

        Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index');
        Route::get('/contributions/type/{type}', [ContributionController::class, 'showType'])
            ->name('contributions.types.show');

        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    });

    Route::middleware(['role:' . implode(',', User::financeManagerRoles())])->group(function () {
        Route::get('/contribution-categories', [ContributionCategoryController::class, 'index'])
            ->name('contribution-categories.index');
        Route::post('/contribution-categories', [ContributionCategoryController::class, 'store'])
            ->name('contribution-categories.store');
        Route::get('/contribution-categories/{contributionCategory}/edit', [ContributionCategoryController::class, 'edit'])
            ->name('contribution-categories.edit');
        Route::put('/contribution-categories/{contributionCategory}', [ContributionCategoryController::class, 'update'])
            ->name('contribution-categories.update');

        Route::get('/contributions/monthly-availability', [ContributionController::class, 'monthlyAvailability'])
            ->name('contributions.monthly-availability');
        Route::get('/contributions/create', [ContributionController::class, 'create'])->name('contributions.create');
        Route::post('/contributions', [ContributionController::class, 'store'])->name('contributions.store');
        Route::patch('/contributions/{contribution}/void', [ContributionController::class, 'void'])
            ->name('contributions.void');

        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::patch('/expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void');
    });

    Route::middleware(['role:' . implode(',', User::clubRoles())])->group(function () {
        Route::get('/member-only', function () {
            return 'Member access granted.';
        })->name('member.only');

        Route::get('/my-member-profile', [MemberController::class, 'self'])
            ->name('members.self');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
