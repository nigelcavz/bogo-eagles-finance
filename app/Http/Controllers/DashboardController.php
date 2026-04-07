<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
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
        $announcements = $this->publishedAnnouncements();

        return view('dashboard', [
            'notifications' => $this->notificationsForUser($user),
            'announcements' => $announcements,
        ]);
    }

    private function notificationsForUser(User $user): array
    {
        $financeNotifications = $user->canManageFinance()
            ? $this->buildFinanceNotifications()
            : collect();

        $generalNotifications = $this->buildGeneralNotifications();

        return $financeNotifications
            ->concat($generalNotifications)
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

    private function publishedAnnouncements(): Collection
    {
        return Announcement::query()
            ->with('creator')
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

    private function behindOnDuesCount(): int
    {
        $monthlyCategoryId = ContributionCategory::query()
            ->where('name', ContributionCategory::MONTHLY_DUES_NAME)
            ->value('id');

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
}
