<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\Post\PostController;

Route::get('post/get', [PostController::class, 'getAll']);
Route::post('post/get/form', [PostController::class, 'get_a_form']);

// for edit

Route::get('get_all_post_Types', [PostController::class, 'getAllPostTypes']);
Route::get('get_all_post_posts', [PostController::class, 'getAllPosts']);
