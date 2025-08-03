<?php

namespace BitApps\BTCBI_PRO\Triggers\AdvancedAds;

class StaticData
{
    public static function forms()
    {
        return [
            ['form_name' => __('Ad status changed', 'bit-integrations-pro'), 'triggered_entity_id' => 'transition_post_status', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from draft to pending', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-draft-to-pending', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from draft to publish', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-draft-to-publish', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from draft to expired', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-draft-to-advanced_ads_expired', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from pending to draft', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-pending-to-draft', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from pending to publish', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-pending-to-publish', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from pending to expired', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-pending-to-advanced_ads_expired', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from publish to draft', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-publish-to-draft', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from publish to pending', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-publish-to-pending', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from publish to expired', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-publish-to-advanced_ads_expired', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from expired to publish', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-advanced_ads_expired-to-publish', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from expired to pending', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-advanced_ads_expired-to-pending', 'skipPrimaryKey' => true],
            ['form_name' => __('Ad status changed from expired to draft', 'bit-integrations-pro'), 'triggered_entity_id' => 'advanced-ads-ad-status-advanced_ads_expired-to-draft', 'skipPrimaryKey' => true],
        ];
    }
}
