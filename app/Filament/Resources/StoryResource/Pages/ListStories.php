<?php

namespace App\Filament\Resources\StoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\StoryResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStories extends ListRecords
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'waiting for review' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'waiting for review')),
            'in review' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'in review')),
            'rework' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rework')),
            'approved' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved')),
            'completed' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed')),  
        ];
    }
}
