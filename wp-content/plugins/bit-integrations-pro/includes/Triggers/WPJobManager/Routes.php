<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WPJobManager\WPJobManagerController;
use BitApps\BTCBI_PRO\Triggers\WPJobManager\WPJobManagerHelper;

Route::get('wpjobmanager/get', [WPJobManagerController::class, 'getAll']);
Route::post('wpjobmanager/get/form', [WPJobManagerController::class, 'get_a_form']);
Route::get('wpjobmanager/get/job-types', [WPJobManagerHelper::class, 'getAllJobTypes']);
Route::get('wpjobmanager/get/jobs', [WPJobManagerHelper::class, 'getAllJobs']);
Route::get('wpjobmanager/get/users', [WPJobManagerHelper::class, 'getAllUsers']);
Route::get('wpjobmanager/get/application-statuses', [WPJobManagerHelper::class, 'getApplicationStatuses']);
