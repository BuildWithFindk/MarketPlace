<?php

namespace Modules\Banner\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Banner\App\Models\Banner;

class BannerController extends Controller
{
    public function redirect(Banner $banner): RedirectResponse
    {
        $banner->trackClick();

        $destination = $banner->getDestinationUrl();

        if ($destination === '#') {
            return redirect()->back();
        }

        if ($banner->link_type === 'external') {
            return redirect()->away($destination);
        }

        return redirect($destination);
    }
}
