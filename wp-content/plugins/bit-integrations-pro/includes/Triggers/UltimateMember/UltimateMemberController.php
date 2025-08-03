<?php

namespace BitApps\BTCBI_PRO\Triggers\UltimateMember;

use BitCode\FI\Flow\Flow;

final class UltimateMemberController
{
    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'UltimateMember',
            'title'          => __('Ultimate Member is the #1 user profile & membership plugin for WordPress. The plugin makes it a breeze for users to sign-up and become members of your website', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'ultimatemember/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'ultimatemember/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive()
    {
        return (bool) (class_exists('UM'))

        ;
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Ultimate Member'));
        }

        $loginForms = UltimateMemberHelper::getAllLoginAndRegistrationForm('login');
        $registrationForms = UltimateMemberHelper::getAllLoginAndRegistrationForm('register');

        $types = array_merge([
            [
                'id'    => 'roleSpecificChange',
                'title' => __('User\'s role changes to a specific role', 'bit-integrations-pro')
            ],
            [
                'id'    => 'roleChange',
                'title' => __('User\'s role change', 'bit-integrations-pro')
            ],
        ], $loginForms, $registrationForms);
        $ultimateMember_action = [];
        foreach ($types as $type) {
            $ultimateMember_action[] = (object) [
                'id'    => $type['id'],
                'title' => $type['title'],
            ];
        }
        wp_send_json_success($ultimateMember_action);
    }

    public function get_a_form($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Ultimate Member'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = UltimateMemberHelper::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $responseData['fields'] = $fields;
        $id = $data->id;
        if ($id == 'roleSpecificChange') {
            $responseData['allRole'] = UltimateMemberHelper::getRoles();
        }
        wp_send_json_success($responseData);
    }

    public static function handleUserLogViaForm($um_args)
    {
        if (!isset($um_args['form_id']) || !\function_exists('um_user')) {
            return;
        }
        $user_id = um_user('ID');
        $form_id = $um_args['form_id'];
        $flows = Flow::exists('UltimateMember', $form_id);
        if (empty($flows)) {
            return;
        }
        $finalData = UltimateMemberHelper::getUserInfo($user_id);
        $finalData['username'] = $um_args['username'];
        if ($finalData) {
            Flow::execute('UltimateMember', $form_id, $finalData, $flows);
        }
    }

    public static function handleUserRegisViaForm($user_id, $um_args)
    {
        $form_id = $um_args['form_id'];
        $flows = Flow::exists('UltimateMember', $form_id);

        if (empty($flows) || empty($um_args['submitted'])) {
            return;
        }

        $data = UltimateMemberHelper::setUploadFieldData($um_args['submitted'], $form_id, $user_id);

        Flow::execute('UltimateMember', $form_id, $data, $flows);
    }

    public static function handleUserRoleChange($user_id, $role, $old_roles)
    {
        $form_id = 'roleChange';
        $flows = Flow::exists('UltimateMember', $form_id);
        if (empty($flows)) {
            return;
        }
        $finalData = UltimateMemberHelper::getUserInfo($user_id);
        $finalData['role'] = $role;

        if ($finalData) {
            Flow::execute('UltimateMember', $form_id, $finalData, $flows);
        }
    }

    public static function handleUserSpecificRoleChange($user_id, $role, $old_roles)
    {
        $form_id = 'roleSpecificChange';
        $flows = Flow::exists('UltimateMember', $form_id);
        if (empty($flows)) {
            return;
        }
        $flowDetails = json_decode($flows[0]->flow_details);
        $selectedRole = !empty($flowDetails->selectedRole) ? $flowDetails->selectedRole : [];
        $finalData = UltimateMemberHelper::getUserInfo($user_id);
        $finalData['role'] = $role;
        if ($finalData && $role === $selectedRole) {
            Flow::execute('UltimateMember', $form_id, $finalData, $flows);
        }
    }

    public static function getUMrole()
    {
        $roles = UltimateMemberHelper::getRoles();
        wp_send_json_success($roles);
    }
}
