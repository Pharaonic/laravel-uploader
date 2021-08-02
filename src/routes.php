<?php

use Pharaonic\Laravel\Uploader\Controller\UploadController;

Route::get(config('Pharaonic.uploader.route', 'file') . '/{hash}', config('Pharaonic.uploader.controller', UploadController::class) . '@file')->name('uploaded');
