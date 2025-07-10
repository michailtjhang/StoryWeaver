<?php

namespace App\Filament\Widgets;

use App\Models\Story;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StoryStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $stats = $this->getStoryStats();

        // Different stats layout based on user role
        if ($this->isReviewer()) {
            return $this->getReviewerStats($stats);
        }

        return $this->getAuthorAdminStats($stats);
    }

    protected function getAuthorAdminStats(array $stats): array
    {
        return [
            Stat::make('Waiting for Review', $stats['waiting_for_review'] ?? 0)
                ->description('Stories pending review')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('In Review', $stats['in_review'] ?? 0)
                ->description('Currently being reviewed')
                ->descriptionIcon('heroicon-o-eye')
                ->color('info'),

            Stat::make('Rework', $stats['rework'] ?? 0)
                ->description('Needs improvements')
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->color('danger'),

            Stat::make('Approved', $stats['approved'] ?? 0)
                ->description('Ready for publication')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Completed', $stats['completed'] ?? 0)
                ->description('Published stories')
                ->descriptionIcon('heroicon-o-document-check')
                ->color('primary'),
        ];
    }

    protected function getReviewerStats(array $stats): array
    {
        return [
            Stat::make('Waiting (Unassigned)', $stats['waiting_unassigned'] ?? 0)
                ->description('Stories waiting for reviewer assignment')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Waiting (My Queue)', $stats['waiting_assigned_to_me'] ?? 0)
                ->description('Stories assigned to me for review')
                ->descriptionIcon('heroicon-o-inbox')
                ->color('info'),

            Stat::make('In Review (Mine)', $stats['in_review_by_me'] ?? 0)
                ->description('Stories I am currently reviewing')
                ->descriptionIcon('heroicon-o-eye')
                ->color('primary'),

            Stat::make('Total Reviewed', $stats['total_reviewed_by_me'] ?? 0)
                ->description('Stories I have reviewed')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }

    protected function getStoryStats(): array
    {
        $query = Story::query();

        if ($this->isAdmin()) {
            // Admin sees all stories
            return $this->getAdminStats($query);
        } elseif ($this->isReviewer()) {
            // Reviewer sees stories based on assignment
            return $this->getReviewerStatsData($query);
        } else {
            // Author sees only their own stories
            return $this->getAuthorStats($query);
        }
    }

    protected function getAdminStats(Builder $query): array
    {
        $results = $query->select([
            DB::raw("COUNT(CASE WHEN status = 'waiting for review' THEN 1 END) as waiting_for_review"),
            DB::raw("COUNT(CASE WHEN status = 'in review' THEN 1 END) as in_review"),
            DB::raw("COUNT(CASE WHEN status = 'rework' THEN 1 END) as rework"),
            DB::raw("COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved"),
            DB::raw("COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed"),
        ])->first();

        return [
            'waiting_for_review' => $results->waiting_for_review,
            'in_review' => $results->in_review,
            'rework' => $results->rework,
            'approved' => $results->approved,
            'completed' => $results->completed,
        ];
    }

    protected function getAuthorStats(Builder $query): array
    {
        $query->where('author_id', auth()->id());

        $results = $query->select([
            DB::raw("COUNT(CASE WHEN status = 'waiting for review' THEN 1 END) as waiting_for_review"),
            DB::raw("COUNT(CASE WHEN status = 'in review' THEN 1 END) as in_review"),
            DB::raw("COUNT(CASE WHEN status = 'rework' THEN 1 END) as rework"),
            DB::raw("COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved"),
            DB::raw("COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed"),
        ])->first();

        return [
            'waiting_for_review' => $results->waiting_for_review,
            'in_review' => $results->in_review,
            'rework' => $results->rework,
            'approved' => $results->approved,
            'completed' => $results->completed,
        ];
    }

    protected function getReviewerStatsData(Builder $query): array
    {
        $userId = auth()->id();

        $results = $query->select([
            // Stories waiting for review with no reviewer assigned
            DB::raw("COUNT(CASE WHEN status = 'waiting for review' AND reviewer_id IS NULL THEN 1 END) as waiting_unassigned"),

            // Stories waiting for review assigned to current reviewer
            DB::raw("COUNT(CASE WHEN status = 'waiting for review' AND reviewer_id = {$userId} THEN 1 END) as waiting_assigned_to_me"),

            // Stories currently being reviewed by current reviewer
            DB::raw("COUNT(CASE WHEN status = 'in review' AND reviewer_id = {$userId} THEN 1 END) as in_review_by_me"),

            // Total stories reviewed by current reviewer (approved + rework + completed)
            DB::raw("COUNT(CASE WHEN reviewer_id = {$userId} AND status IN ('approved', 'rework', 'completed') THEN 1 END) as total_reviewed_by_me"),
        ])->first();

        return [
            'waiting_unassigned' => $results->waiting_unassigned,
            'waiting_assigned_to_me' => $results->waiting_assigned_to_me,
            'in_review_by_me' => $results->in_review_by_me,
            'total_reviewed_by_me' => $results->total_reviewed_by_me,
        ];
    }

    /**
     * Check if user can view this widget
     */
    public static function canView(): bool
    {
        return auth()->check();
    }

    /**
     * Check if current user is admin
     */
    protected function isAdmin(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    /**
     * Check if current user is reviewer
     */
    protected function isReviewer(): bool
    {
        return auth()->user()->hasRole('Reviewer');
    }

    /**
     * Get widget title based on user role
     */
    protected function getHeading(): string
    {
        if ($this->isAdmin()) {
            return 'All Stories Overview';
        } elseif ($this->isReviewer()) {
            return 'Review Queue Overview';
        }

        return 'My Stories Overview';
    }

    /**
     * Alternative method using Laravel collections (if you prefer)
     */
    protected function getStoryStatsAlternative(): array
    {
        $query = Story::query();

        if ($this->isAdmin()) {
            // Admin sees all stories
            $stories = $query->get(['status']);
        } elseif ($this->isReviewer()) {
            // Reviewer logic would be more complex here, stick to raw SQL approach
            return $this->getReviewerStatsData($query);
        } else {
            // Author sees only their stories
            $stories = $query->where('author_id', auth()->id())->get(['status']);
        }

        $grouped = $stories->groupBy('status');

        return [
            'waiting_for_review' => $grouped->get('waiting for review', collect())->count(),
            'in_review' => $grouped->get('in review', collect())->count(),
            'rework' => $grouped->get('rework', collect())->count(),
            'approved' => $grouped->get('approved', collect())->count(),
            'completed' => $grouped->get('completed', collect())->count(),
        ];
    }
}
