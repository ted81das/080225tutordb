<?php

if (!defined('ABSPATH')) {
    exit;
}

use BitApps\BTCBI_PRO\Core\Util\Hooks;
use BitApps\BTCBI_PRO\Triggers\Affiliate\AffiliateController;

Hooks::add('affwp_set_affiliate_status', [AffiliateController::class, 'newAffiliateApproved'], 10, 3);
// Hooks::add('affwp_register_user', [AffiliateController::class, 'newAffiliatAwaitingApproval'], 15, 3);
Hooks::add('affwp_set_affiliate_status', [AffiliateController::class, 'userBecomesAffiliate'], 10, 3);
Hooks::add('affwp_insert_referral', [AffiliateController::class, 'affiliateMakesReferral'], 20, 1);
Hooks::add('affwp_set_referral_status', [AffiliateController::class, 'affiliatesReferralSpecificTypeRejected'], 99, 3);
Hooks::add('affwp_set_referral_status', [AffiliateController::class, 'affiliatesReferralSpecificTypePaid'], 99, 3);
