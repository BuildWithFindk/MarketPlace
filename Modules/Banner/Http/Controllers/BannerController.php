<?php

namespace Modules\Banner\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('banner::index');
    }
}
