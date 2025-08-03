<?php

namespace BitApps\BTCBI_PRO\Triggers\SureMembers;

use BitCode\FI\Flow\Flow;
use SureMembers\Inc\Access_Groups;

final class SureMembersController
{
    public static function info()
    {
        $plugin_path = 'suremembers/suremembers.php';

        return [
            'name'           => 'SureMembers',
            'title'          => __('SureMembers', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'suremembers/suremembers.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('suremembers/suremembers.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'suremembers/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'suremembers/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('suremembers/suremembers.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureMembers'));
        }

        $types = [
            __('User Added To Access Group', 'bit-integrations-pro'),
            __('User Removed From Access Group', 'bit-integrations-pro'),
            __('Access Group Updated', 'bit-integrations-pro')
        ];
        $tasks = [];

        foreach ($types as $key => $item) {
            $key = $key + 1;
            $tasks[] = (object) [
                'id'    => 'sureMembers-' . $key,
                'title' => $item,
            ];
        }

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('suremembers/suremembers.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureMembers'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        $accessGroups = Access_Groups::get_active();

        if ($data->id === 'sureMembers-3') {
            $groups[] = [
                'id'    => 'any',
                'title' => __('Any Group', 'bit-integrations-pro'),
            ];
        } else {
            $groups = [];
        }

        if (!empty($accessGroups)) {
            foreach ($accessGroups as $key => $accessGroup) {
                $groups[] = [
                    'id'    => $key,
                    'title' => $accessGroup
                ];
            }
        }

        $responseData['groups'] = $groups;
        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        if (\is_string($data)) {
            $id = $data;
        } else {
            $id = $data->id;
        }

        if (empty($id)) {
            return;
        }

        $fields = [];

        if ($id === 'sureMembers-1' || $id === 'sureMembers-2') {
            $fields = array_merge(SureMembersHelper::accessGroupFields(), SureMembersHelper::wpUserFileds());
        } elseif ($id === 'sureMembers-3') {
            $fields = array_merge(SureMembersHelper::accessGroupFields(), SureMembersHelper::groupUpdatedFields());
        }

        return $fields;
    }

    public static function getSureMembersGroups()
    {
        $groups = [];

        $accessGroups = Access_Groups::get_active();

        if (!empty($accessGroups)) {
            foreach ($accessGroups as $key => $accessGroup) {
                $groups[] = [
                    'id'    => $key,
                    'title' => $accessGroup
                ];
            }
        }

        return $groups;
    }

    public static function handleSureMembersAccessGrant($userId, $accessGroupIds)
    {
        if (get_transient('suremembers_after_access_grant')) {
            return;
        }

        if (\count($accessGroupIds) > 1) {
            set_transient('suremembers_after_access_grant', true, 30);
        }

        $flows = Flow::exists('SureMembers', 'sureMembers-1');

        if (empty($flows) || !$flows || empty($userId) || empty($accessGroupIds)) {
            return;
        }

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
            }

            if (isset($flow->flow_details->selectedGroup) && \is_array($accessGroupIds) && \in_array($flow->flow_details->selectedGroup, $accessGroupIds)) {
                $data = SureMembersHelper::sureMembersGrantOrRevoke($userId, $flow->flow_details->selectedGroup);

                if (!$data) {
                    continue;
                }

                Flow::execute('SureMembers', 'sureMembers-1', $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }

    public static function handleSureMembersAccessRevoke($userId, $accessGroupIds)
    {
        if (get_transient('suremembers_after_access_revoke')) {
            return;
        }

        if (\count($accessGroupIds) > 1) {
            set_transient('suremembers_after_access_revoke', true, 30);
        }

        $flows = Flow::exists('SureMembers', 'sureMembers-2');

        if (empty($flows) || !$flows || empty($userId) || empty($accessGroupIds)) {
            return;
        }

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
            }

            if (isset($flow->flow_details->selectedGroup) && \is_array($accessGroupIds) && \in_array($flow->flow_details->selectedGroup, $accessGroupIds)) {
                $data = SureMembersHelper::sureMembersGrantOrRevoke($userId, $flow->flow_details->selectedGroup);

                if (!$data) {
                    continue;
                }

                Flow::execute('SureMembers', 'sureMembers-2', $data, [$flow]);
            }
        }

        return ['type' => 'success'];
    }

    public static function handleSureMembersGroupUpdated($suremembersPostId)
    {
        $flows = Flow::exists('SureMembers', 'sureMembers-3');
        $flows = SureMembersHelper::flowFilter($flows, 'selectedGroup', $suremembersPostId);

        if (empty($flows) || !$flows || empty($suremembersPostId)) {
            return;
        }

        $data = SureMembersHelper::sureMembersGroupUpdated($suremembersPostId);

        Flow::execute('SureMembers', 'sureMembers-3', $data, $flows);
    }
}
