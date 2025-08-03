<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\SolidAffiliate\SolidAffiliateController;

Hooks::add('data_model_solid_affiliate_affiliates_save', [SolidAffiliateController::class, 'newSolidAffiliateCreated'], 10, 1);
Hooks::add('data_model_solid_affiliate_referrals_save', [SolidAffiliateController::class, 'newSolidAffiliateReferralCreated'], 10, 1);
