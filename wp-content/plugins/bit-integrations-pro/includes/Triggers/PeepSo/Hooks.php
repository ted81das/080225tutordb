<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PeepSo\PeepSoController;

Hooks::add('peepso_ajax_start', [PeepSoController::class, 'handleUserFollowsPeppSoMember'], 10, 1);
Hooks::add('peepso_ajax_start', [PeepSoController::class, 'handleUserGainsFollower'], 10, 1);
Hooks::add('peepso_ajax_start', [PeepSoController::class, 'handleUserLosesFollower'], 10, 1);
Hooks::add('peepso_ajax_start', [PeepSoController::class, 'handleUserUnfollowsPeppSoMember'], 10, 1);
Hooks::add('peepso_user_after_change_avatar', [PeepSoController::class, 'handleUserUpdatesAvatar'], 10, 4);
Hooks::add('peepso_ajax_start', [PeepSoController::class, 'handleUserProfileFieldUpdate'], 10, 1);
Hooks::add('peepso_activity_after_add_post', [PeepSoController::class, 'handleNewActivityPost'], 10, 2);
