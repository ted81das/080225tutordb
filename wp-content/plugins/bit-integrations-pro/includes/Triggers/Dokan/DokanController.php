<?php

namespace BitApps\BTCBI_PRO\Triggers\Dokan;

use BitCode\FI\Flow\Flow;

// use WeDevs\Dokan\Vendor\Vendor;

final class DokanController
{
    public static function info()
    {
        $plugin_path = 'dokan-lite/dokan.php';

        return [
            'name'           => 'Dokan',
            'title'          => __('Dokan', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'dokan-lite/dokan.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('dokan-lite/dokan.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'dokan/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'dokan/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true,
            'note'  => '<p>' . __('To use <strong>Dokan Refund</strong> (request, approved, cancelled) tasks, you need to have <strong>Dokan Pro</strong> plugin', 'bit-integrations-pro') . '</p>'
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('dokan-lite/dokan.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Dokan'));
        }

        $tasks = [
            (object) ['id' => 'dokan-1', 'title' => __('New Vendor Added', 'bit-integrations-pro'), 'note' => \sprintf(__('Runs after a vendor is %s', 'bit-integrations-pro'), __('created', 'bit-integrations-pro'))],
            (object) ['id' => 'dokan-2', 'title' => __('Vendor Updated', 'bit-integrations-pro'), 'note' => \sprintf(__('Runs after a vendor is %s', 'bit-integrations-pro'), __('updated', 'bit-integrations-pro'))],
            (object) ['id' => 'dokan-3', 'title' => __('Vendor Deleted', 'bit-integrations-pro'), 'note' => \sprintf(__('Runs after a vendor is %s', 'bit-integrations-pro'), __('deleted', 'bit-integrations-pro'))]
        ];

        if (is_plugin_active('dokan-pro/dokan-pro.php')) {
            $tasks[] = (object) ['id' => 'dokan-4', 'title' => __('New Refund Request', 'bit-integrations-pro'), 'note' => __('Runs after a vendor requested a refund', 'bit-integrations-pro')];
            $tasks[] = (object) ['id' => 'dokan-5', 'title' => __('Refund Approved', 'bit-integrations-pro'), 'note' => \sprintf(__('Runs after a refund is %s', 'bit-integrations-pro'), __('approved', 'bit-integrations-pro'))];
            $tasks[] = (object) ['id' => 'dokan-6', 'title' => __('Refund Cancelled', 'bit-integrations-pro'), 'note' => \sprintf(__('Runs after a refund is %s', 'bit-integrations-pro'), __('cancelled', 'bit-integrations-pro'))];
        }

        $tasks[] = (object) [
            'id'   => 'dokan-7', 'title' => __('User Becomes Vendor or Vendor Registration', 'bit-integrations-pro'),
            'note' => __('Runs after a user becomes a vendor or if a user registers as a vendor', 'bit-integrations-pro')
        ];

        $tasks[] = (object) ['id' => 'dokan-8', 'title' => __('New Withdraw Request', 'bit-integrations-pro'), 'note' => __('Runs after a vendor requested a withdraw', 'bit-integrations-pro')];

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('dokan-lite/dokan.php')) {
            wp_send_json_error(__('Dokan is not installed or activated', 'bit-integrations-pro'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        return DokanHelper::getFields($data);
    }

    public static function handleVendorAdd($vendorId, $data)
    {
        if (empty($vendorId) || empty($data)) {
            return false;
        }

        $flows = Flow::exists('Dokan', 'dokan-1');

        if (empty($flows) || !$flows) {
            return;
        }

        $vendorAddData = DokanHelper::formatVendorData($vendorId, $data);

        Flow::execute('Dokan', 'dokan-1', $vendorAddData, $flows);
    }

    public static function handleVendorUpdate($vendorId, $data)
    {
        if (empty($vendorId) || empty($data)) {
            return false;
        }

        $flows = Flow::exists('Dokan', 'dokan-2');

        if (empty($flows) || !$flows) {
            return;
        }

        $vendorUpdateData = DokanHelper::formatVendorData($vendorId, $data);

        Flow::execute('Dokan', 'dokan-2', $vendorUpdateData, $flows);
    }

    public static function handleVendorDelete($vendorId)
    {
        if (is_plugin_active('dokan-lite/dokan.php')) {
            $userData = get_userdata($vendorId);

            if (!empty($userData) && \in_array('seller', $userData->roles)) {
                $vendor = dokan()->vendor->get($vendorId)->to_array();

                if (empty($vendor) || is_wp_error($vendor)) {
                    return;
                }

                $flows = Flow::exists('Dokan', 'dokan-3');

                if (empty($flows) || !$flows) {
                    return;
                }

                $vendorDeleteData = DokanHelper::formatVendorData($vendorId, $vendor);

                Flow::execute('Dokan', 'dokan-3', $vendorDeleteData, $flows);
            }
        }
    }

    public static function dokanRefundRequest($refund)
    {
        if (!$refund) {
            return;
        }

        $flows = Flow::exists('Dokan', 'dokan-4');

        if (empty($flows) || !$flows) {
            return;
        }

        $refundRequestData = DokanHelper::formatRefundData($refund);

        if (!empty($refundRequestData)) {
            Flow::execute('Dokan', 'dokan-4', $refundRequestData, $flows);
        }
    }

    public static function dokanRefundApproved($refundData, $args, $vendorRefund)
    {
        if (!$refundData) {
            return;
        }

        $flows = Flow::exists('Dokan', 'dokan-5');

        if (empty($flows) || !$flows) {
            return;
        }

        $refundApprovedData = DokanHelper::formatRefundData($refundData);

        if (!empty($refundApprovedData)) {
            Flow::execute('Dokan', 'dokan-5', $refundApprovedData, $flows);
        }
    }

    public static function dokanRefundCancelled($refundData)
    {
        if (!$refundData) {
            return;
        }

        $flows = Flow::exists('Dokan', 'dokan-6');

        if (empty($flows) || !$flows) {
            return;
        }

        $refundCancelledData = DokanHelper::formatRefundData($refundData);

        if (!empty($refundCancelledData)) {
            Flow::execute('Dokan', 'dokan-6', $refundCancelledData, $flows);
        }
    }

    public static function dokanUserToVendor($userId, $shopInfo)
    {
        if (empty($userId)) {
            return;
        }

        $flows = Flow::exists('Dokan', 'dokan-7');

        if (empty($flows) || !$flows) {
            return;
        }

        $userToVendorData = DokanHelper::formatUserToVendorData($userId);

        if (!empty($userToVendorData)) {
            Flow::execute('Dokan', 'dokan-7', $userToVendorData, $flows);
        }
    }

    public static function dokanWithdrawRequest($userId, $amount, $method)
    {
        if (empty($userId) || empty($amount) || empty($method)) {
            return;
        }

        $flows = Flow::exists('Dokan', 'dokan-8');

        if (empty($flows) || !$flows) {
            return;
        }

        $withdrawRequestData = DokanHelper::formatWithdrawRequestData($userId, $amount, $method);

        if (!empty($withdrawRequestData)) {
            Flow::execute('Dokan', 'dokan-8', $withdrawRequestData, $flows);
        }
    }

    public static function withdrawRequestCancel($userEmail, $subject, $body)
    {
        error_log(print_r([$userEmail, $subject, $body], true));
    }
}
