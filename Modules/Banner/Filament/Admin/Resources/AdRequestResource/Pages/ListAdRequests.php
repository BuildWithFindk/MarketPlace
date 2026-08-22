<?php

namespace Modules\Banner\Filament\Admin\Resources\AdRequestResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Banner\Filament\Admin\Resources\AdRequestResource;

class ListAdRequests extends ListRecords
{
    protected static string $resource = AdRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
