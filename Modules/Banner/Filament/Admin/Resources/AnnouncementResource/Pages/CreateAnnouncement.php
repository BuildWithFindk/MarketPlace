<?php

namespace Modules\Banner\Filament\Admin\Resources\AnnouncementResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Banner\Filament\Admin\Resources\AnnouncementResource;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;
}