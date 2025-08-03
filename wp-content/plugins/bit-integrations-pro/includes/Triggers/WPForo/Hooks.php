<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WPForo\WPForoController;

Hooks::add('wpforo_after_add_topic', [WPForoController::class, 'handleWPForoTopicAdd'], 10, 2);
Hooks::add('wpforo_after_add_post', [WPForoController::class, 'handleWPForoPostAdd'], 10, 3);
Hooks::add('wpforo_vote', [WPForoController::class, 'handleWPForoUpVote'], 10, 3);
Hooks::add('wpforo_vote', [WPForoController::class, 'handleWPForoDownVote'], 10, 3);
Hooks::add('wpforo_react_post', [WPForoController::class, 'handleWPForoLike'], 10, 2);
Hooks::add('wpforo_react_post', [WPForoController::class, 'handleWPForoDislike'], 10, 2);
Hooks::add('wpforo_vote', [WPForoController::class, 'handleWPForoGetsUpVote'], 10, 3);
Hooks::add('wpforo_vote', [WPForoController::class, 'handleWPForoGetsDownVote'], 10, 3);
Hooks::add('wpforo_react_post', [WPForoController::class, 'handleWPForoGetsLike'], 10, 2);
Hooks::add('wpforo_react_post', [WPForoController::class, 'handleWPForoGetsDislike'], 10, 2);
Hooks::add('wpforo_answer', [WPForoController::class, 'handleWPForoAnswer'], 10, 2);
Hooks::add('wpforo_answer', [WPForoController::class, 'handleWPForoGetsAnswer'], 10, 2);
