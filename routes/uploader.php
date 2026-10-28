<?php

use Illuminate\Support\Facades\Route;
use Pharaonic\Laravel\Uploader\Facades\Uploader;

Route::get(Uploader::route()->uri(), Uploader::route()->action())
    ->name('uploader.file');
