<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStory extends ViewRecord
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->visible(fn() => auth()->user()->hasRole('Reviewer') &&
                    $this->record->status === 'in review' &&
                    $this->record->reviewer_id === auth()->user()->id)
                ->form([
                    \Filament\Forms\Components\Textarea::make('feedback')
                        ->label('Feedback')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function ($record, $data) {
                    $record->status = 'approved';
                    $record->feedback = $data['feedback'];
                    $record->save();
                }),
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->color('danger')
                ->visible(fn() => auth()->user()->hasRole('Reviewer') &&
                    $this->record->status === 'in review' &&
                    $this->record->reviewer_id === auth()->user()->id)
                ->form([
                    \Filament\Forms\Components\Textarea::make('feedback')
                        ->label('Feedback')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function ($record, $data) {
                    $record->status = 'cancelled';
                    $record->feedback = $data['feedback'];
                    $record->save();
                }),
            Actions\Action::make('rework')
                ->label('Rework')
                ->color('info')
                ->visible(fn() => auth()->user()->hasRole('Reviewer') &&
                    $this->record->status === 'in review' &&
                    $this->record->reviewer_id === auth()->user()->id)
                ->form([
                    \Filament\Forms\Components\Textarea::make('feedback')
                        ->label('Feedback')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->action(function ($record, $data) {
                    $record->status = 'rework';
                    $record->feedback = $data['feedback'];
                    $record->save();
                }),
        ];
    }
}
