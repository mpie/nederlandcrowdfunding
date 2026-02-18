<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FileUploadResource\Pages;
use App\Models\FileUpload;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload as FileUploadField;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileUploadResource extends Resource
{
    protected static ?string $model = FileUpload::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationLabel = 'Bestanden';

    protected static ?string $modelLabel = 'Bestand';

    protected static ?string $pluralModelLabel = 'Bestanden';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bestand uploaden')
                    ->schema([
                        FileUploadField::make('path')
                            ->label('Bestand')
                            ->required()
                            ->disk('public')
                            ->directory('uploads')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->preserveFilenames(),

                        Hidden::make('disk')
                            ->default('public'),

                        Hidden::make('uploaded_by')
                            ->default(fn (): ?int => Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')
                    ->label('Bestandsnaam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mime_type')
                    ->label('Type')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'application/pdf' => 'danger',
                        'image/jpeg', 'image/png' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'application/pdf' => 'PDF',
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        default => $state,
                    }),

                TextColumn::make('formatted_size')
                    ->label('Grootte'),

                TextColumn::make('uploader.name')
                    ->label('Geüpload door')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Geüpload op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('public_url')
                    ->label('URL')
                    ->url(fn (FileUpload $record): string => $record->public_url, shouldOpenInNewTab: true)
                    ->limit(40),
            ])
            ->filters([])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (FileUpload $record): StreamedResponse => Storage::disk($record->disk)->download(
                        $record->path,
                        $record->original_name,
                    )),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileUploads::route('/'),
            'create' => Pages\CreateFileUpload::route('/create'),
        ];
    }
}