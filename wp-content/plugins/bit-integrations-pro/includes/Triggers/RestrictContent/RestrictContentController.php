<?php

namespace BitApps\BTCBI_PRO\Triggers\RestrictContent;

use BitCode\FI\Flow\Flow;
use RCP_Membership;

final class RestrictContentController
{
    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'Restrict Content',
            'title'          => __('Restrict Content - WordPress membership plugin that allows you to monetize content access', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'restrictcontent/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'restrictcontent/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('restrict-content-pro/restrict-content-pro.php')) {
            return $option === 'get_name' ? 'restrict-content-pro/restrict-content-pro.php' : true;
        } elseif (is_plugin_active('restrict-content/restrictcontent.php')) {
            return $option === 'get_name' ? 'restrict-content/restrictcontent.php' : true;
        }

        return false;
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Restrict Content'));
        }
        // A user's membership to a specific level expires Pro
        // A user's membership to a specific level is cancelled Pro
        $types = [
            __('A user purchases a membership level', 'bit-integrations-pro'),
            __('A users membership to a specific level expires Pro', 'bit-integrations-pro'),
            __('A users membership to a specific level is cancelled Pro', 'bit-integrations-pro')
        ];
        $restrictContent_action = [];
        foreach ($types as $index => $type) {
            $restrictContent_action[] = (object) [
                'id'    => $index + 1,
                'title' => $type,
            ];
        }
        wp_send_json_success($restrictContent_action);
    }

    public function get_a_form($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Restrict Content'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        // query for levels
        global $wpdb;

        $allLevels = $wpdb->get_results("select id,name from {$wpdb->prefix}restrict_content_pro");

        $organizelevels[] = [

            'level_id'   => 'any',
            'level_name' => __('Any', 'bit-integrations-pro')
        ];

        foreach ($allLevels as $level) {
            $organizelevels[] = [
                'level_id'   => $level->id,
                'level_name' => $level->name
            ];
        }
        $responseData['allMembership'] = $organizelevels;

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fields($id)
    {
        if (empty($id)) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'bit-integrations-pro'
                ),
                400
            );
        }

        $fields = [

            'Membership level' => (object) [
                'fieldKey'  => 'membership_level',
                'fieldName' => __('Membership level', 'bit-integrations-pro')
            ],
            'Membership payment' => (object) [
                'fieldKey'  => 'membership_payment',
                'fieldName' => __('Membership payment', 'bit-integrations-pro')
            ],
            'Membership recurring payment' => (object) [
                'fieldKey'  => 'membership_recurring_payment',
                'fieldName' => __('Membership recurring payment', 'bit-integrations-pro')
            ]

        ];

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function get_all_membership()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(__('Restrict Content is not installed or activated', 'bit-integrations-pro'));
        }
        global $wpdb;

        $allLevels = $wpdb->get_results("select id,name from {$wpdb->prefix}restrict_content_pro");

        $organizelevels[] = [

            'level_id'   => 'any',
            'level_name' => __('Any', 'bit-integrations-pro')
        ];

        foreach ($allLevels as $level) {
            $organizelevels[] = [
                'level_id'   => $level->id,
                'level_name' => $level->name
            ];
        }

        return $organizelevels;
    }

    public static function purchasesMembershipLevel($membership_id, RCP_Membership $RCP_Membership)
    {
        $flows = Flow::exists('RestrictContent', 1);
        if (!$flows) {
            return;
        }
        $user_id = $RCP_Membership->get_user_id();

        if (!$user_id) {
            return;
        }
        $level_id = $RCP_Membership->get_object_id();

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }

        if ($level_id == $flowDetails->selectedMembership || 'any' == $flowDetails->selectedMembership) {
            $organizedData = [];
            if ($membership_id) {
                $membership = rcp_get_membership($membership_id);
                if (false !== $membership) {
                    $organizedData = [
                        'membership_level'             => $membership->get_membership_level_name(),
                        'membership_payment'           => $membership->get_initial_amount(),
                        'membership_recurring_payment' => $membership->get_recurring_amount(),
                    ];
                }
            }

            Flow::execute('RestrictContent', 1, $organizedData, $flows);
        }
    }

    public static function membershipStatusExpired($old_status, $membership_id)
    {
        $flows = Flow::exists('RestrictContent', 2);
        if (!$flows) {
            return;
        }
        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }
        $membership = rcp_get_membership($membership_id);
        $membership_level = rcp_get_membership_level($membership->get_object_id());
        $level_id = (string) $membership_level->get_id();

        if ($level_id == $flowDetails->selectedMembership || 'any' == $flowDetails->selectedMembership) {
            $organizedData = [];

            if ($membership_id) {
                $membership = rcp_get_membership($membership_id);

                if (false !== $membership) {
                    $organizedData = [
                        'membership_level'             => $membership->get_membership_level_name(),
                        'membership_payment'           => $membership->get_initial_amount(),
                        'membership_recurring_payment' => $membership->get_recurring_amount(),
                    ];
                }
            }

            Flow::execute('RestrictContent', 2, $organizedData, $flows);
        }
    }

    public static function membershipStatusCancelled($old_status, $membership_id)
    {
        $flows = Flow::exists('RestrictContent', 3);
        if (!$flows) {
            return;
        }

        $organizedData = [];
        $membership = rcp_get_membership($membership_id);
        $membership_level = rcp_get_membership_level($membership->get_object_id());
        $level_id = $membership_level->get_id();

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }

        if ($level_id == $flowDetails->selectedMembership || 'any' == $flowDetails->selectedMembership) {
            if ($membership_id) {
                $membership = rcp_get_membership($membership_id);

                if (false !== $membership) {
                    $organizedData = [
                        'membership_level'             => $membership->get_membership_level_name(),
                        'membership_payment'           => $membership->get_initial_amount(),
                        'membership_recurring_payment' => $membership->get_recurring_amount(),
                    ];
                }
            }

            Flow::execute('RestrictContent', 3, $organizedData, $flows);
        }
    }

    // protected static function flowFilter($flows, $key, $value)
    // {
    //     $filteredFlows = [];
    //     foreach ($flows as $flow) {
    //         if (is_string($flow->flow_details)) {
    //             $flow->flow_details = json_decode($flow->flow_details);
    //         }
    //         if (!isset($flow->flow_details->$key) || $flow->flow_details->$key === 'any' || $flow->flow_details->$key == $value || $flow->flow_details->$key === '') {
    //             $filteredFlows[] = $flow;
    //         }
    //     }
    //     return $filteredFlows;
    // }
}
