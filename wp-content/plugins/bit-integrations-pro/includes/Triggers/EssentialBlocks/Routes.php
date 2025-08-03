<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\EssentialBlocks\EssentialBlocksController;

Route::post('essential_blocks/get', [EssentialBlocksController::class, 'getTestData']);
Route::post('essential_blocks/test/remove', [EssentialBlocksController::class, 'removeTestData']);
