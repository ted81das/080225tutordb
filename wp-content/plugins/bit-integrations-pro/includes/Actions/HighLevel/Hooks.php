<?php

use BitApps\BTCBI_PRO\Actions\HighLevel\HighLevelUtilitiesPro;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

if (!defined('ABSPATH')) {
    exit;
}

Hooks::filter('btcbi_high_level_contact_utilities', [HighLevelUtilitiesPro::class, 'contactUtilities'], 10, 3);
Hooks::filter('btcbi_high_level_opportunity_utilities', [HighLevelUtilitiesPro::class, 'opportunityUtilities'], 10, 3);
