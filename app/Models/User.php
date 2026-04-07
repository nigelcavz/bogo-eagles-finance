<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PRESIDENT = 'president';
    public const ROLE_VICE_PRESIDENT = 'vice_president';
    public const ROLE_SECRETARY = 'secretary';
    public const ROLE_TREASURER = 'treasurer';
    public const ROLE_OFFICER = 'officer';
    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function createdContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'created_by');
    }

    public function updatedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'updated_by');
    }

    public function voidedContributions(): HasMany
    {
        return $this->hasMany(Contribution::class, 'voided_by');
    }

    public function createdExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function updatedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'updated_by');
    }

    public function voidedExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'voided_by');
    }

    public function createdAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function updatedAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'updated_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public static function systemRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
            self::ROLE_TREASURER,
            self::ROLE_OFFICER,
            self::ROLE_MEMBER,
        ];
    }

    public static function clubRoles(): array
    {
        return [
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
            self::ROLE_TREASURER,
            self::ROLE_OFFICER,
            self::ROLE_MEMBER,
        ];
    }

    public static function financeViewerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_TREASURER,
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
            self::ROLE_OFFICER,
        ];
    }

    public static function financeManagerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_TREASURER,
        ];
    }

    public static function announcementManagerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
            self::ROLE_TREASURER,
        ];
    }

    public static function calendarManagerRoles(): array
    {
        return self::announcementManagerRoles();
    }

    public static function memberViewerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_TREASURER,
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
        ];
    }

    public static function memberManagerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_SECRETARY,
        ];
    }

    public static function memberStatusManagerRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_PRESIDENT,
            self::ROLE_VICE_PRESIDENT,
            self::ROLE_SECRETARY,
            self::ROLE_TREASURER,
        ];
    }

    public static function assignableRoles(): array
    {
        return self::systemRoles();
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function canManageUsers(): bool
    {
        return $this->is_active && $this->isAdmin();
    }

    public function canManageMembers(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::memberManagerRoles());
    }

    public function canViewMembers(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::memberViewerRoles());
    }

    public function canManageMemberStatus(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::memberStatusManagerRoles());
    }

    public function canViewFinance(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::financeViewerRoles());
    }

    public function canManageFinance(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::financeManagerRoles());
    }

    public function canManageAnnouncements(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::announcementManagerRoles());
    }

    public function canManageCalendar(): bool
    {
        return $this->is_active && $this->hasAnyRole(self::calendarManagerRoles());
    }

    public function canViewOwnMemberProfile(): bool
    {
        return $this->is_active && ! $this->isAdmin() && $this->hasAnyRole(self::clubRoles());
    }
}
