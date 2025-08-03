<?php

if (!defined('ABSPATH')) {
    exit;
}
    
use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\PrestoPlayer\PrestoPlayerController;

Hooks::add('presto_player_progress', [PrestoPlayerController::class, 'handleVideoCompleted'], 10, 3);
Hooks::add('presto_player_progress', [PrestoPlayerController::class, 'handleVideoWatched'], 10, 3);
