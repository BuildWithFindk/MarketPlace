<?php

Route::get('/debug-html', function () { auth()->loginUsingId(1); return view('site::layouts.app'); });
