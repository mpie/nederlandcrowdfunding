<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions;

use App\Filament\Resources\ContactSubmissions\Pages\ListContactSubmissions;
use App\Filament\Resources\ContactSubmissions\Pages\ViewContactSubmission;
use App\Models\ContactSubmission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Berichten';

    protected static ?string $modelLabel = 'Bericht';

    protected static ?string $pluralModelLabel = 'Berichten';

    protected static string | \UnitEnum | null $navigationGroup = 'Contact';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $count = ContactSubmission::where('is_read', false)->where('is_spam', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contactgegevens')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Naam'),
                        TextEntry::make('email')
                            ->label('E-mail')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Telefoon')
                            ->placeholder('—'),
                        TextEntry::make('subject')
                            ->label('Onderwerp')
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Section::make('Bericht')
                    ->schema([
                        TextEntry::make('message')
                            ->label('')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('IP adres'),
                        TextEntry::make('created_at')
                            ->label('Ontvangen op')
                            ->dateTime('d-m-Y H:i'),
                        IconEntry::make('is_read')
                            ->label('Gelezen')
                            ->boolean(),
                        IconEntry::make('is_spam')
                            ->label('Spam')
                            ->boolean(),
                    ])
                    ->columns(4)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning')
                    ->width('40px'),

                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (ContactSubmission $record): string => $record->is_read ? 'normal' : 'bold'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('subject')
                    ->label('Onderwerp')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('message')
                    ->label('Bericht')
                    ->limit(60)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_spam')
                    ->label('Spam')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-exclamation')
                    ->falseIcon('heroicon-o-shield-check')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Ontvangen')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Status')
                    ->trueLabel('Gelezen')
                    ->falseLabel('Ongelezen')
                    ->placeholder('Alle'),

                TernaryFilter::make('is_spam')
                    ->label('Spam')
                    ->trueLabel('Spam')
                    ->falseLabel('Geen spam')
                    ->placeholder('Alle'),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactSubmissions::route('/'),
            'view' => ViewContactSubmission::route('/{record}'),
        ];
    }
}