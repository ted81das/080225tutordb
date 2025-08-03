<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Actions\FluentCRM\FluentCRMProHelper;

Hooks::filter('fluent_crm_assign_company', [FluentCRMProHelper::class, 'assignCompany'], 10, 2);
