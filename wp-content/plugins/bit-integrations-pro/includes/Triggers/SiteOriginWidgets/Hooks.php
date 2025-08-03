<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SiteOriginWidgets\SiteOriginWidgetsController;

Hooks::add('siteorigin_widgets_contact_sent', [SiteOriginWidgetsController::class, 'handleSiteOriginWidgetsSubmit'], 10, PHP_INT_MAX);
