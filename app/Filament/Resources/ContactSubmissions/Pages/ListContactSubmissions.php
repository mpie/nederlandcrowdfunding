<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactSubmissions\Pages;

use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use Filament\Resources\Pages\ListRecords;

final class ListContactSubmissions extends ListRecords
{
    protected static string $resource = ContactSubmissionResource::class;
}