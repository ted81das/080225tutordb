<?php

namespace BitApps\BTCBI_PRO\Triggers\NinjaTables;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class NinjaTablesHelper
{
    public static function isPluginInstalled()
    {
        return \defined('NINJA_TABLES_VERSION');
    }

    public static function formatRowData($insert_id, $table_id)
    {
        global $wpdb;
        $sql = 'SELECT * FROM ' . $wpdb->prefix . 'ninja_table_items WHERE table_id = %d AND id = %d ORDER BY id DESC LIMIT 1';
        $results = $wpdb->get_row($wpdb->prepare($sql, $table_id, $insert_id), ARRAY_A); // @phpcs:ignore

        if (empty($results)) {
            return [];
        }

        $results['value'] = json_decode($results['value'], true);
        $owner = User::get($results['owner_id']);

        $results = array_merge($results, [
            'owner_login'        => $owner['wp_user_login'] ?? '',
            'owner_display_name' => $owner['wp_display_name'] ?? '',
            'owner_first_name'   => $owner['wp_user_first_name'] ?? '',
            'owner_last_name'    => $owner['wp_user_last_name'] ?? '',
            'owner_last_name'    => $owner['wp_user_last_name'] ?? '',
            'owner_email'        => $owner['wp_user_email'] ?? '',
            'owner_registered'   => $owner['wp_user_registered'] ?? '',
            'owner_role'         => $owner['wp_user_role'] ?? '',
        ]);

        return Helper::prepareFetchFormatFields($results);
    }
}
