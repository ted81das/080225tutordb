<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\BuddyBoss\BuddyBossController;

Route::get('buddyboss/get', [BuddyBossController::class, 'getAll']);
Route::post('buddyboss/get/form', [BuddyBossController::class, 'get_a_form']);
Route::get('get_all_topic_by_forum', [BuddyBossController::class, 'getTopicByForum']);
Route::get('get_all_group', [BuddyBossController::class, 'getAllGroup']);
Route::get('get_all_forum', [BuddyBossController::class, 'getAllForums']);
Route::get('get_all_topic', [BuddyBossController::class, 'getAllTopic']);
