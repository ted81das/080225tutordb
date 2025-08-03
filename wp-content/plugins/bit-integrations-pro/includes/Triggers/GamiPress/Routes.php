<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\GamiPress\GamiPressController;

Route::get('gamipress/get', [GamiPressController::class, 'getAll']);
Route::post('gamipress/get/form', [GamiPressController::class, 'get_a_form']);

Route::get('get_all_rank_by_types', [GamiPressController::class, 'getAllRankBYType']);
Route::get('get_all_award_by_achievement_type', [GamiPressController::class, 'getAllAwardBYAchievementType']);

Route::get('get_all_achievement_type', [GamiPressController::class, 'getAllAchievementType']);
Route::get('get_all_rank_type', [GamiPressController::class, 'getAllRankType']);
