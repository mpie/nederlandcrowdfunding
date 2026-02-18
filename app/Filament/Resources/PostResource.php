<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PageStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?string $modelLabel = 'Bericht';

    protected static ?string $pluralModelLabel = 'Berichten';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inhoud')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Textarea::make('excerpt')
                            ->label('Samenvatting')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Inhoud')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Publicatie')
                    ->schema([
                        Select::make('status')
                            ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $status): array => [$status->value => $status->label()]))
                            ->required()
                            ->default(PageStatus::Draft->value),

                        DateTimePicker::make('published_at')
                            ->label('Publicatiedatum'),

                        Select::make('author_id')
                            ->label('Auteur')
                            ->relationship('author', 'name')
                            ->default(fn (): ?int => auth()->id())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PageStatus $state): string => $state->label())
                    ->color(fn (PageStatus $state): string => $state->color()),

                TextColumn::make('published_at')
                    ->label('Gepubliceerd op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $status): array => [$status->value => $status->label()])),

                SelectFilter::make('author_id')
                    ->label('Auteur')
                    ->relationship('author', 'name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}