<?php

namespace BitApps\BTCBI_PRO\Triggers\BuddyBoss;

class BuddyBossHelper
{
    public static function getBuddyBossProfileField()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bp_xprofile_fields';
        $results = $wpdb->get_results("SELECT id, type, name FROM {$table_name}");

        return $results;
    }

    public static function getProfileData($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bp_xprofile_data';

        return $wpdb->get_results($wpdb->prepare("SELECT id, field_id, user_id, value FROM {$table_name} WHERE user_id = %d", $user_id));
    }
}
