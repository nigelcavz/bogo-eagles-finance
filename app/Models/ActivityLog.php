<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'record_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changeSummary(): array
    {
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        if (! is_array($oldValues)) {
            $oldValues = [];
        }

        if (! is_array($newValues)) {
            $newValues = [];
        }

        $keys = collect(array_keys($oldValues))
            ->merge(array_keys($newValues))
            ->unique()
            ->values();

        return $keys
            ->map(function (string|int $key) use ($oldValues, $newValues) {
                $field = (string) $key;
                $from = Arr::exists($oldValues, $field) ? $oldValues[$field] : null;
                $to = Arr::exists($newValues, $field) ? $newValues[$field] : null;

                if ($from === $to) {
                    return null;
                }

                return [
                    'field' => Str::headline($field),
                    'from' => $this->formatAuditValue($from, $field),
                    'to' => $this->formatAuditValue($to, $field),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function formattedModule(): string
    {
        return Str::headline($this->module);
    }

    public function formattedAction(): string
    {
        return Str::headline($this->action);
    }

    public function dashboardSummary(): string
    {
        $actor = $this->user?->name ?? 'System';

        return match ($this->action) {
            'announcement_created' => $actor . ' created an announcement',
            'announcement_updated' => $actor . ' updated an announcement',
            'announcement_deleted' => $actor . ' deleted an announcement',
            'event_created' => $actor . ' scheduled an event',
            'expense_created' => $actor . ' recorded an expense',
            'expense_voided' => $actor . ' voided an expense',
            'contribution_created' => $actor . ' recorded a contribution',
            'contribution_voided' => $actor . ' voided a contribution',
            'member_account_created' => $actor . ' created a member account',
            'member_deactivated' => $actor . ' set a member inactive',
            'member_reactivated' => $actor . ' reactivated a member',
            'user_role_updated' => $actor . ' updated a user role',
            'user_account_deactivated' => $actor . ' deactivated an account',
            'user_account_reactivated' => $actor . ' reactivated an account',
            default => $actor . ' recorded ' . Str::lower($this->formattedAction()),
        };
    }

    private function formatAuditValue(mixed $value, ?string $field = null): string
    {
        if ($value === null || $value === '') {
            return '--';
        }

        if (is_bool($value)) {
            if ($field === 'is_active' || $field === 'user_is_active') {
                return $value ? 'Active' : 'Inactive';
            }

            return $value ? 'Yes' : 'No';
        }

        if (is_numeric($value) && $field !== null && Str::contains($field, 'amount')) {
            return Currency::format($value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[complex value]';
        }

        if (is_string($value)) {
            if ($field !== null && (Str::endsWith($field, '_at') || Str::contains($field, 'date'))) {
                try {
                    return Carbon::parse($value)->format('M d, Y h:i A');
                } catch (\Throwable) {
                    // Fall through to the original string.
                }
            }

            return Str::headline($value) === $value ? $value : (string) $value;
        }

        return (string) $value;
    }
}
