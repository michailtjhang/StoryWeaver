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

    protected function getStoryStats(): array
    {
        $query = Story::query();

        // Filter by author if user is authenticated and not admin
        if (auth()->check() && !$this->isAdmin()) {
            $query->where('author_id', auth()->id());
        }

        // Get counts for all statuses in one query
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

    /**
     * Alternative method using Laravel collections (if you prefer)
     */
    protected function getStoryStatsAlternative(): array
    {
        $query = Story::query();

        if (auth()->check() && !$this->isAdmin()) {
            $query->where('author_id', auth()->id());
        }

        $stories = $query->get(['status']);
        $grouped = $stories->groupBy('status');

        return [
            'waiting_for_review' => $grouped->get('waiting for review', collect())->count(),
            'in_review' => $grouped->get('in review', collect())->count(),
            'rework' => $grouped->get('rework', collect())->count(),
            'approved' => $grouped->get('approved', collect())->count(),
            'completed' => $grouped->get('completed', collect())->count(),
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
        // Option 1: Using role name
        return auth()->user()->hasRole('Admin');

        // Option 2: Using role ID (uncomment if you prefer this)
        // return auth()->user()->role_id === 1;

        // Option 3: Using custom admin field (uncomment if you prefer this)
        // return auth()->user()->is_admin;

        // Option 4: Using gate/policy (uncomment if you prefer this)
        // return auth()->user()->can('view-all-stories');
    }

    /**
     * Get widget title based on user role
     */
    protected function getHeading(): string
    {
        if ($this->isAdmin()) {
            return 'All Stories Overview';
        }

        return 'My Stories Overview';
    }
}
