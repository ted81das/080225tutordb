<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\WpPolls\WpPollsController;

Hooks::add('wp_polls_vote_poll_success', [WpPollsController::class, 'handlePollSubmitted'], 10, 0);
