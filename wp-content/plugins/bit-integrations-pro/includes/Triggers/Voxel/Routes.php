<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Voxel\VoxelController;
use BitApps\BTCBI_PRO\Triggers\Voxel\VoxelHelper;

Route::get('voxel/get', [VoxelController::class, 'getAll']);
Route::post('voxel/get/form', [VoxelController::class, 'get_a_form']);
Route::get('voxel/get/post-types', [VoxelHelper::class, 'getAllPostTypes']);
Route::post('voxel/get/fields', [VoxelController::class, 'fields']);
