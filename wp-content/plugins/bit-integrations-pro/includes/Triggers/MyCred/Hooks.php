<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\MyCred\MyCredController;

Hooks::filter('mycred_add_finished', [MyCredController::class, 'handleUserEarnsPoints'], 10, 2);
Hooks::filter('mycred_add_finished', [MyCredController::class, 'handleUserLosesPoints'], 10, 2);
Hooks::add('mycred_after_badge_assign', [MyCredController::class, 'handleCaptureBadgeEarned'], 10, 3);
Hooks::add('mycred_user_got_promoted', [MyCredController::class, 'handleCaptureRankEarned'], 10, 4);
Hooks::add('mycred_user_got_demoted', [MyCredController::class, 'handleCaptureRankLost'], 10, 4);
