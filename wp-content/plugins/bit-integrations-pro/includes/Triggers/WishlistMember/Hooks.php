<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WishlistMember\WishlistMemberController;

Hooks::add('wishlistmember_add_user_levels', [WishlistMemberController::class, 'handleUserAddedToMembershipLevel'], 10, 2);
Hooks::add('wishlistmember_remove_user_levels', [WishlistMemberController::class, 'handleUserRemovedFromMembershipLevel'], 10, 2);
