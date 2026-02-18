<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems;

use App\Enums\MenuLocation;
use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Resources\MenuItems\Pages\ListMenuItems;
use App\Models\MenuItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Menu beheer';

    protected static ?string $modelLabel = 'Menu-item';

    protected static ?string $pluralModelLabel = 'Menu-items';

    protected static string | \UnitEnum | null $navigationGroup = 'Instellingen';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu-item')
                    ->schema([
                        Select::make('location')
                            ->label('Locatie')
                            ->options(collect(MenuLocation::cases())->mapWithKeys(
                                fn (MenuLocation $loc): array => [$loc->value => $loc->label()],
                            ))
                            ->required()
                            ->native(false),

                        Select::make('parent_id')
                            ->label('Bovenliggend item (submenu)')
                            ->relationship(
                                'parent',
                                'label',
                                fn ($query) => $query->whereNull('parent_id')->orderBy('location')->orderBy('sort_order'),
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Laat leeg voor een top-level item.'),

                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('url')
                            ->label('URL')
                            ->maxLength(255)
                            ->placeholder('/over-ons/de-vereniging of https://...')
                            ->helperText('Gebruik een relatieve URL (bijv. /contact) of een absolute URL.'),

                        TextInput::make('route_name')
                            ->label('Laravel route naam')
                            ->maxLength(255)
                            ->placeholder('home, posts.index, contact')
                            ->helperText('Alternatief voor URL. Als ingevuld wordt URL genegeerd.'),

                        TextInput::make('icon')
                            ->label('Icoon (FontAwesome)')
                            ->maxLength(100)
                            ->placeholder('fa-solid fa-building-columns')
                            ->helperText('Optioneel, wordt getoond in submenu\'s.'),

                        Select::make('target')
                            ->label('Openen in')
                            ->options([
                                '_self' => 'Zelfde venster',
                                '_blank' => 'Nieuw venster/tab',
                            ])
                            ->default('_self')
                            ->native(false),

                        TextInput::make('sort_order')
                            ->label('Volgorde')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lager nummer = eerder getoond.'),

                        Toggle::make('is_active')
                            ->label('Actief')
                            ->default(true)
                            ->helperText('Niet-actieve items worden niet getoond.'),

                        Toggle::make('is_highlighted')
                            ->label('Uitgelicht (CTA knop)')
                            ->default(false)
                            ->helperText('Wordt als knop getoond in de navbar (bijv. Contact).'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable()
                    ->sortable()
                    ->description(fn (MenuItem $record): ?string => $record->parent?->label ? 'Sub van: ' . $record->parent->label : null),

                TextColumn::make('location')
                    ->label('Locatie')
                    ->formatStateUsing(fn (MenuLocation $state): string => $state->label())
                    ->badge()
                    ->color(fn (MenuLocation $state): string => match ($state) {
                        MenuLocation::Navbar => 'info',
                        MenuLocation::FooterPages => 'warning',
                        MenuLocation::FooterAbout => 'success',
                    })
                    ->sortable(),

                TextColumn::make('resolved_url')
                    ->label('URL')
                    ->limit(40)
                    ->color('gray'),

                TextColumn::make('icon')
                    ->label('Icoon')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),

                IconColumn::make('is_highlighted')
                    ->label('CTA')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->label('Locatie')
                    ->options(collect(MenuLocation::cases())->mapWithKeys(
                        fn (MenuLocation $loc): array => [$loc->value => $loc->label()],
                    )),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->groups([
                Group::make('location')
                    ->label('Locatie')
                    ->getTitleFromRecordUsing(fn (MenuItem $record): string => $record->location->label()),
            ])
            ->defaultGroup('location');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}