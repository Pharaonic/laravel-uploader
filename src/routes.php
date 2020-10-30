<?php

use Pharaonic\Laravel\Uploader\Controller\UploadController;

Route::get(config('Pharaonic.uploader.route', 'file') . '/{hash}', UploadController::class . '@file')->name('uploaded');
