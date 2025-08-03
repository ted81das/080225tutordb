<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\QuillForms\QuillFormsController;

Route::get('quill-forms/get', [QuillFormsController::class, 'getAllTasks']);
Route::post('quill-forms/test', [QuillFormsController::class, 'getTestData']);
Route::post('quill-forms/test/remove', [QuillFormsController::class, 'removeTestData']);
