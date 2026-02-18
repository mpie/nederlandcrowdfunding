<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions\Pages;

use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewContactSubmission extends ViewRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    public function getTitle(): string
    {
        return 'Bericht van ' . $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggleRead')
                ->label(fn (): string => $this->record->is_read ? 'Markeer als ongelezen' : 'Markeer als gelezen')
                ->icon(fn (): string => $this->record->is_read ? 'heroicon-o-envelope' : 'heroicon-o-envelope-open')
                ->action(function (): void {
                    $this->record->update(['is_read' => ! $this->record->is_read]);
                }),

            Actions\Action::make('toggleSpam')
                ->label(fn (): string => $this->record->is_spam ? 'Geen spam' : 'Markeer als spam')
                ->icon('heroicon-o-shield-exclamation')
                ->color(fn (): string => $this->record->is_spam ? 'success' : 'danger')
                ->action(function (): void {
                    $this->record->update(['is_spam' => ! $this->record->is_spam]);
                }),

            Actions\Action::make('reply')
                ->label('Beantwoorden')
                ->icon('heroicon-o-paper-airplane')
                ->url(fn (): string => 'mailto:' . $this->record->email . '?subject=Re: ' . urlencode($this->record->subject ?? 'Uw bericht'))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateRecord(ContactSubmission $record): ContactSubmission
    {
        if (! $record->is_read) {
            $record->update(['is_read' => true]);
        }

        return $record;
    }
}