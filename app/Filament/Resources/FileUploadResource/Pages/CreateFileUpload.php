<?php

declare(strict_types=1);

namespace App\Filament\Resources\FileUploadResource\Pages;

use App\Filament\Resources\FileUploadResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFileUpload extends CreateRecord
{
    protected static string $resource = FileUploadResource::class;
}