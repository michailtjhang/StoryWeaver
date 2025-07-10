<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\StoryResource\RelationManagers;
use App\Models\Story;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoryResource extends Resource
{
    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('story_content')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn(Story $record): string => match ($record->status) {
                    'waiting for review' => 'warning',
                    'in review' => 'info',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'rework' => 'primary',
                    default => 'secondary',
                }),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviewer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()->hasRole('Admin'))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Story deleted successfully')
                    ->failureNotificationTitle('Failed to delete story')
                    ->icon('heroicon-o-trash'),
                Tables\Actions\Action::make('review')
                    ->label('Review')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn(Story $record) => auth()->user()->hasRole('Reviewer') && 
                    $record->status === 'waiting for review' &&
                    ($record->reviewer_id === auth()->user()->id || $record->reviewer_id === null))
                    ->requiresConfirmation()
                    ->action(function (Story $record) {
                        $record->update(['status' => 'in review', 'reviewer_id' => auth()->user()->id]);
                        return redirect(static::getUrl('view', ['record' => $record]))
                            ->with('success', 'Story is now under review');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ])
                ->visible(fn() => auth()->user()->hasRole('Admin')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'view' => Pages\ViewStory::route('/{record}'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->when(auth()->user()->hasRole('Writer'), function (Builder $query) {
                $query->where('author_id', auth()->user()->id);
            });
    }
}
