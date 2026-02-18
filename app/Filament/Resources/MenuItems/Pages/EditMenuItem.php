<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems\Pages;

use App\Filament\Resources\MenuItems\MenuItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    public function getTitle(): string
    {
        return 'Bewerken: ' . $this->record->label;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}