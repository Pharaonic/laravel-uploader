<?php

use Illuminate\Support\Facades\Route;
use Pharaonic\Laravel\Uploader\Facades\Uploader;

Route::middleware('web')
    ->name('uploader.file')
    ->get(
        Uploader::route()->uri(),
        Uploader::route()->action()
    );
