<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Member;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $announcements = $this->dashboardAnnouncementFeed();

        return view('dashboard', [
            'notifications' => $this->notificationsForUser($user),
            'announcements' => $announcements,
            'quickStats' => $this->buildQuickStats($user),
            'monthlySnapshot' => $this->buildMonthlySnapshot($user),
            'personalSection' => $this->buildPersonalSection($user),
            'recentActivities' => $this->buildRecentActivities($user),
        ]);
    }

    private function notificationsForUser(User $user): array
    {
        $notifications = collect();

        if ($user->canManageFinance()) {
            $notifications = $notifications->concat($this->buildFinanceNotifications());
        }

        $notifications = $notifications
            ->concat($this->buildUpcomingEventNotifications())
            ->concat($this->buildGeneralNotifications());

        return $notifications
            ->filter(fn ($notification) => filled($notification['message'] ?? null))
            ->unique(fn ($notification) => ($notification['icon'] ?? 'info') . '|' . ($notification['message'] ?? ''))
            ->values()
            ->all();
    }

    private function buildFinanceNotifications(): Collection
    {
        $behindCount = $this->behindOnDuesCount();

        if ($behindCount === 0) {
            return collect();
        }

        return collect([
            [
                'severity' => 'warning',
                'icon' => 'currency',
                'message' => sprintf(
                    '%d %s behind on monthly dues for %d.',
                    $behindCount,
                    str('member')->plural($behindCount),
                    now()->year
                ),
            ],
        ]);
    }

    private function buildGeneralNotifications(): Collection
    {
        $announcement = $this->publishedAnnouncements()->first();

        if (! $announcement) {
            return collect();
        }

        return collect([
            [
                'severity' => 'success',
                'icon' => 'calendar',
                'message' => 'Upcoming event: ' . $announcement->title,
            ],
        ]);
    }

    private function buildUpcomingEventNotifications(): Collection
    {
        $event = Event::query()
            ->whereDate('event_date', '>=', now()->toDateString())
            ->whereDate('event_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->first();

        if (! $event) {
            return collect();
        }

        return collect([
            [
                'severity' => 'success',
                'icon' => 'calendar',
                'message' => 'Upcoming event: ' . $event->title . ' on ' . $event->event_date->format('M d'),
            ],
        ]);
    }

    private function publishedAnnouncements(): Collection
    {
        return Announcement::query()
            ->with(['creator', 'event'])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->where('visibility', 'all')
                    ->orWhereNull('visibility');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(3)
            ->get();
    }

    private function dashboardAnnouncementFeed(): Collection
    {
        $announcements = $this->publishedAnnouncements();
        $linkedEventIds = $announcements
            ->pluck('event_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $standaloneUpcomingEvents = Event::query()
            ->with('creator')
            ->whereDate('event_date', '>=', now()->toDateString())
            ->whereDate('event_date', '<=', now()->addDays(30)->toDateString())
            ->when($linkedEventIds->isNotEmpty(), function ($query) use ($linkedEventIds) {
                $query->whereNotIn('id', $linkedEventIds);
            })
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->take(3)
            ->get()
            ->map(function (Event $event) {
                return (object) [
                    'kind' => 'event',
                    'title' => $event->title,
                    'body' => $event->description,
                    'creator' => $event->creator,
                    'event' => $event,
                    'published_at' => $event->created_at,
                    'created_at' => $event->created_at,
                ];
            });

        $announcementCards = $announcements->map(function (Announcement $announcement) {
            return (object) [
                'kind' => 'announcement',
                'title' => $announcement->title,
                'body' => $announcement->body,
                'creator' => $announcement->creator,
                'event' => $announcement->event,
                'published_at' => $announcement->published_at,
                'created_at' => $announcement->created_at,
            ];
        });

        return $announcementCards
            ->concat($standaloneUpcomingEvents)
            ->sortByDesc(function ($item) {
                return optional($item->event)->event_date?->timestamp
                    ?? optional($item->published_at)->timestamp
                    ?? optional($item->created_at)->timestamp
                    ?? 0;
            })
            ->values();
    }

    private function behindOnDuesCount(): int
    {
        $monthlyCategoryId = $this->monthlyCategoryId();

        if (! $monthlyCategoryId) {
            return 0;
        }

        $activeMemberIds = Member::query()
            ->where('membership_status', 'active')
            ->pluck('id');

        if ($activeMemberIds->isEmpty()) {
            return 0;
        }

        $expectedMonths = collect(range(1, now()->month));

        $coveredMonthsByMember = ContributionCoverage::query()
            ->join('contributions', 'contributions.id', '=', 'contribution_coverages.contribution_id')
            ->where('contributions.status', 'active')
            ->where('contributions.contribution_category_id', $monthlyCategoryId)
            ->whereIn('contribution_coverages.member_id', $activeMemberIds)
            ->where('contribution_coverages.coverage_year', now()->year)
            ->whereIn('contribution_coverages.coverage_month', $expectedMonths)
            ->get([
                'contribution_coverages.member_id',
                'contribution_coverages.coverage_month',
            ])
            ->groupBy('member_id')
            ->map(fn (Collection $rows) => $rows->pluck('coverage_month')->map(fn ($month) => (int) $month)->unique());

        return $activeMemberIds
            ->filter(function (int $memberId) use ($coveredMonthsByMember, $expectedMonths) {
                $coveredMonths = $coveredMonthsByMember->get($memberId, collect());

                return $expectedMonths->diff($coveredMonths)->isNotEmpty();
            })
            ->count();
    }

    private function buildQuickStats(User $user): array
    {
        $memberQuery = $this->directoryMemberQuery();

        return [
            'can_view_finance' => $user->canViewFinance(),
            'total_contributions' => $user->canViewFinance()
                ? Contribution::query()->where('status', 'active')->sum('amount')
                : null,
            'total_expenses' => $user->canViewFinance()
                ? Expense::query()->where('status', 'active')->sum('amount')
                : null,
            'net_balance' => $user->canViewFinance()
                ? Contribution::query()->where('status', 'active')->sum('amount')
                    - Expense::query()->where('status', 'active')->sum('amount')
                : null,
            'total_members' => (clone $memberQuery)->count(),
            'active_members' => (clone $memberQuery)->where('membership_status', 'active')->count(),
        ];
    }

    private function buildMonthlySnapshot(User $user): ?array
    {
        return [
            'contributions' => Contribution::query()
                ->where('status', 'active')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'expenses' => Expense::query()
                ->where('status', 'active')
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
        ];
    }

    private function buildPersonalSection(User $user): array
    {
        $linkedMember = $user->loadMissing('member')->member;

        if (! $linkedMember) {
            return [
                'member' => null,
                'latest_contribution' => null,
                'dues_status' => null,
            ];
        }

        $latestContribution = Contribution::query()
            ->with('category')
            ->where('member_id', $linkedMember->id)
            ->where('status', 'active')
            ->latest('payment_date')
            ->latest('id')
            ->first();

        return [
            'member' => $linkedMember,
            'latest_contribution' => $latestContribution,
            'dues_status' => $this->buildDuesStatusForMember($linkedMember),
        ];
    }

    private function buildDuesStatusForMember(Member $member): ?array
    {
        $monthlyCategoryId = $this->monthlyCategoryId();

        if (! $monthlyCategoryId) {
            return null;
        }

        $expectedMonths = collect(range(1, now()->month));
        $coveredMonths = collect($this->coveredMonthsForMemberCurrentYear($member->id, $monthlyCategoryId));
        $missingMonths = $expectedMonths->diff($coveredMonths)->values();

        if ($missingMonths->isEmpty()) {
            return [
                'state' => 'paid',
                'label' => 'Paid',
                'detail' => 'Paid through ' . now()->format('F') . ' ' . now()->year . '.',
            ];
        }

        return [
            'state' => 'missing',
            'label' => 'Missing',
            'detail' => 'Missing ' . $missingMonths->count() . ' ' . str('month')->plural($missingMonths->count()) . ' for ' . now()->year . '.',
        ];
    }

    private function buildRecentActivities(User $user): Collection
    {
        if (! $user->hasAnyRole([
            User::ROLE_ADMIN,
            User::ROLE_PRESIDENT,
            User::ROLE_VICE_PRESIDENT,
            User::ROLE_SECRETARY,
            User::ROLE_TREASURER,
            User::ROLE_OFFICER,
        ])) {
            return collect();
        }

        return ActivityLog::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', '!=', User::ROLE_ADMIN);
                    });
            })
            ->latest('created_at')
            ->latest('id')
            ->take(5)
            ->get();
    }

    private function directoryMemberQuery()
    {
        return Member::query()
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', '!=', User::ROLE_ADMIN);
                    });
            });
    }

    private function monthlyCategoryId(): ?int
    {
        $categoryId = ContributionCategory::query()
            ->where('name', ContributionCategory::MONTHLY_DUES_NAME)
            ->value('id');

        return $categoryId ? (int) $categoryId : null;
    }

    private function coveredMonthsForMemberCurrentYear(int $memberId, int $monthlyCategoryId): array
    {
        return ContributionCoverage::query()
            ->join('contributions', 'contributions.id', '=', 'contribution_coverages.contribution_id')
            ->where('contributions.status', 'active')
            ->where('contributions.contribution_category_id', $monthlyCategoryId)
            ->where('contribution_coverages.member_id', $memberId)
            ->where('contribution_coverages.coverage_year', now()->year)
            ->whereIn('contribution_coverages.coverage_month', range(1, now()->month))
            ->pluck('contribution_coverages.coverage_month')
            ->map(fn ($month) => (int) $month)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
