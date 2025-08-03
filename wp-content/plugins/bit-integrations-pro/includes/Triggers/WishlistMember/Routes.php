<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Route;
use BitApps\BTCBI_PRO\Triggers\WishlistMember\WishlistMemberController;

Route::get('wishlist_member/get', [WishlistMemberController::class, 'getAllTasks']);
Route::post('wishlist_member/test', [WishlistMemberController::class, 'getTestData']);
Route::post('wishlist_member/test/remove', [WishlistMemberController::class, 'removeTestData']);
