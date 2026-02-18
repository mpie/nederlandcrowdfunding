<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PageStatus;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pagina\'s';

    protected static ?string $modelLabel = 'Pagina';

    protected static ?string $pluralModelLabel = 'Pagina\'s';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Pagina')
                    ->tabs([
                        Tab::make('Algemeen')
                            ->icon('heroicon-o-cog-6-tooth')
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
                                    ->rules(['alpha_dash'])
                                    ->live(onBlur: true),

                                Select::make('parent_id')
                                    ->label('Bovenliggende pagina')
                                    ->relationship('parent', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                Select::make('status')
                                    ->options(collect(PageStatus::cases())->mapWithKeys(fn (PageStatus $status): array => [$status->value => $status->label()]))
                                    ->required()
                                    ->default(PageStatus::Draft->value),

                                DateTimePicker::make('published_at')
                                    ->label('Publicatiedatum'),

                                TextInput::make('sort_order')
                                    ->label('Sorteervolgorde')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Tab::make('Inhoud')
                            ->icon('heroicon-o-pencil-square')
                            ->visible(fn (Get $get): bool => ! in_array($get('slug'), ['home', 'leden', 'bestuur-directie'], true))
                            ->schema([
                                RichEditor::make('content')
                                    ->label('Pagina inhoud')
                                    ->columnSpanFull(),
                            ]),

                        // === Homepage tabs ===
                        Tab::make('Hero')
                            ->icon('heroicon-o-sparkles')
                            ->visible(fn (Get $get): bool => $get('slug') === 'home')
                            ->schema([
                                TextInput::make('blocks.hero.title')
                                    ->label('Hero titel')
                                    ->maxLength(255)
                                    ->placeholder('Branchevereniging met impact...'),

                                Textarea::make('blocks.hero.subtitle')
                                    ->label('Hero subtitel')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Nederland Crowdfunding is de branchevereniging...'),

                                TextInput::make('blocks.hero.cta_text')
                                    ->label('CTA knop tekst')
                                    ->maxLength(50)
                                    ->placeholder('Laatste nieuws'),

                                TextInput::make('blocks.hero.cta_url')
                                    ->label('CTA knop URL')
                                    ->maxLength(255)
                                    ->placeholder('/actueel'),

                                TextInput::make('blocks.hero.cta2_text')
                                    ->label('Tweede knop tekst')
                                    ->maxLength(50)
                                    ->placeholder('Neem contact op'),

                                TextInput::make('blocks.hero.cta2_url')
                                    ->label('Tweede knop URL')
                                    ->maxLength(255)
                                    ->placeholder('/contact'),
                            ]),

                        Tab::make('Content blokken')
                            ->icon('heroicon-o-squares-2x2')
                            ->visible(fn (Get $get): bool => $get('slug') === 'home')
                            ->schema([
                                Repeater::make('blocks.cards')
                                    ->label('Kaarten')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Titel')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('icon')
                                            ->label('FontAwesome icoon')
                                            ->placeholder('fa-solid fa-building-columns')
                                            ->maxLength(100),

                                        RichEditor::make('content')
                                            ->label('Inhoud')
                                            ->required(),
                                    ])
                                    ->defaultItems(3)
                                    ->maxItems(6)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Over sectie')
                            ->icon('heroicon-o-information-circle')
                            ->visible(fn (Get $get): bool => $get('slug') === 'home')
                            ->schema([
                                TextInput::make('blocks.about.title')
                                    ->label('Sectie titel')
                                    ->maxLength(255)
                                    ->placeholder('Over de branchevereniging'),

                                RichEditor::make('blocks.about.content')
                                    ->label('Tekst')
                                    ->columnSpanFull(),

                                TextInput::make('blocks.about.link_text')
                                    ->label('Link tekst')
                                    ->maxLength(100)
                                    ->placeholder('Lees meer over de vereniging'),

                                TextInput::make('blocks.about.link_url')
                                    ->label('Link URL')
                                    ->maxLength(255)
                                    ->placeholder('/over-ons/de-vereniging'),
                            ]),

                        Tab::make('Statistieken')
                            ->icon('heroicon-o-chart-bar')
                            ->visible(fn (Get $get): bool => $get('slug') === 'home')
                            ->schema([
                                TextInput::make('blocks.stats.title')
                                    ->label('Blok titel')
                                    ->maxLength(255)
                                    ->placeholder('Nederland Crowdfunding'),

                                TextInput::make('blocks.stats.subtitle')
                                    ->label('Blok subtitel')
                                    ->maxLength(255)
                                    ->placeholder('versterkt het klimaat voor MKB-financiering'),

                                Repeater::make('blocks.stats.items')
                                    ->label('Statistieken')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Waarde')
                                            ->required()
                                            ->maxLength(50)
                                            ->placeholder('12'),

                                        TextInput::make('label')
                                            ->label('Label')
                                            ->required()
                                            ->maxLength(100)
                                            ->placeholder('Leden'),
                                    ])
                                    ->defaultItems(4)
                                    ->maxItems(6)
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('FAQ')
                            ->icon('heroicon-o-question-mark-circle')
                            ->visible(fn (Get $get): bool => $get('slug') === 'home')
                            ->schema([
                                TextInput::make('blocks.faq.title')
                                    ->label('Sectie titel')
                                    ->maxLength(255)
                                    ->placeholder('Veelgestelde vragen'),

                                TextInput::make('blocks.faq.subtitle')
                                    ->label('Sectie subtitel')
                                    ->maxLength(255)
                                    ->placeholder('Crowdfunding is meer dan financiering'),

                                Repeater::make('blocks.faq.items')
                                    ->label('Vragen')
                                    ->schema([
                                        TextInput::make('question')
                                            ->label('Vraag')
                                            ->required()
                                            ->maxLength(255),

                                        RichEditor::make('answer')
                                            ->label('Antwoord')
                                            ->required(),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        // === Leden page tab ===
                        Tab::make('Leden')
                            ->icon('heroicon-o-building-office')
                            ->visible(fn (Get $get): bool => $get('slug') === 'leden')
                            ->schema([
                                Textarea::make('blocks.members.intro')
                                    ->label('Introductietekst')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Repeater::make('blocks.members.items')
                                    ->label('Leden')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Naam')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('url')
                                            ->label('Website URL')
                                            ->maxLength(255)
                                            ->placeholder('https://...'),

                                        FileUpload::make('logo')
                                            ->label('Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('leden-logos')
                                            ->imageResizeMode('contain')
                                            ->imageCropAspectRatio(null)
                                            ->imageResizeTargetWidth('400')
                                            ->imageResizeTargetHeight('200')
                                            ->maxSize(2048),

                                        RichEditor::make('description')
                                            ->label('Beschrijving'),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        // === Bestuur page tab ===
                        Tab::make('Team')
                            ->icon('heroicon-o-user-group')
                            ->visible(fn (Get $get): bool => $get('slug') === 'bestuur-directie')
                            ->schema([
                                Textarea::make('blocks.team.intro')
                                    ->label('Introductietekst')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Repeater::make('blocks.team.items')
                                    ->label('Teamleden')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Naam')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('role')
                                            ->label('Functie')
                                            ->maxLength(255)
                                            ->placeholder('Voorzitter'),

                                        TextInput::make('company')
                                            ->label('Bedrijf')
                                            ->maxLength(255),

                                        FileUpload::make('photo')
                                            ->label('Foto')
                                            ->image()
                                            ->disk('public')
                                            ->directory('team-photos')
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth('400')
                                            ->imageResizeTargetHeight('400')
                                            ->maxSize(2048),

                                        RichEditor::make('bio')
                                            ->label('Biografie'),
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('SEO Titel')
                                    ->maxLength(255),

                                Textarea::make('seo_description')
                                    ->label('SEO Beschrijving')
                                    ->maxLength(160)
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable(),

                TextColumn::make('parent.title')
                    ->label('Bovenliggend')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PageStatus $state): string => $state->label())
                    ->color(fn (PageStatus $state): string => $state->color()),

                TextColumn::make('sort_order')
                    ->label('Volgorde')
                    ->sortable(),

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

                SelectFilter::make('parent_id')
                    ->label('Bovenliggende pagina')
                    ->relationship('parent', 'title'),
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
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}