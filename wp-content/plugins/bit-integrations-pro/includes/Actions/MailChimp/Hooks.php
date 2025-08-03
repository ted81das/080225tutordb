<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Actions\MailChimp\MailChimpRecordHelper;
use BitApps\BTCBI_PRO\Core\Util\Hooks;

Hooks::filter('btcbi_mailchimp_add_remove_tag', [MailChimpRecordHelper::class, 'addRemoveTag'], 10, 4);
Hooks::filter('btcbi_mailchimp_map_language', [MailChimpRecordHelper::class, 'mapLanguageField'], 10, 2);
Hooks::add('btcbi_mailchimp_store_gdpr_permission', [MailChimpRecordHelper::class, 'updateGDPRPermissions'], 10, 6);
