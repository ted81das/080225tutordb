<?php

namespace BitApps\BTCBI_PRO\Triggers\Affiliate;

use BitCode\FI\Flow\Flow;

final class AffiliateController
{
    private const AFFILIATE_APPROVED = 1;

    private const USER_BECOMES_AN_AFFILIATE = 2;

    private const AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE = 3;

    private const AFFILIATE_REFERRAL_TYPE_REJECTED = 4;

    private const AFFILIATE_REFERRAL_TYPE_PAID = 5;

    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'Affiliate',
            'title'          => __('Affiliate - WordPress membership plugin that allows you to monetize content access', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'affiliate/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'affiliate/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('affiliate-wp/affiliate-wp.php')) {
            return $option === 'get_name' ? 'affiliate-wp/affiliate-wp.php' : true;
        }

        return false;
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'AffiliateWP'));
        }

        $affiliate_action = [
            (object) ['id' => static::AFFILIATE_APPROVED, 'title' => __('A new affiliate is approved', 'bit-integrations-pro')],
            (object) ['id' => static::USER_BECOMES_AN_AFFILIATE, 'title' => __('A user becomes an affiliate', 'bit-integrations-pro')],
            (object) ['id' => static::AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE, 'title' => __('An affiliate makes a referral of a specific type', 'bit-integrations-pro')],
            (object) ['id' => static::AFFILIATE_REFERRAL_TYPE_REJECTED, 'title' => __('An affiliates referral of a specific type is rejected Pro', 'bit-integrations-pro')],
            (object) ['id' => static::AFFILIATE_REFERRAL_TYPE_PAID, 'title' => __('An affiliates referral of a specific type is paid Pro', 'bit-integrations-pro')],
        ];

        wp_send_json_success($affiliate_action);
    }

    public function get_a_form($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(__('AffiliateWP is not installed or activated', 'bit-integrations-pro'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Trigger type doesn\'t exists', 'bit-integrations-pro'));
        }
        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }

        $organizeType[] = [
            'type_id'   => 'any',
            'type_name' => __('Any', 'bit-integrations-pro')
        ];

        $typeId = 1;
        foreach (affiliate_wp()->referrals->types_registry->get_types() as $type_keys => $type) {
            $organizeType[] = [
                'type_id'   => $typeId,
                'type_name' => $type['label'],
                'type_key'  => $type_keys
            ];
            $typeId++;
        }
        $responseData['allType'] = $organizeType;
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

        if ($id == static::AFFILIATE_APPROVED || $id == static::USER_BECOMES_AN_AFFILIATE) {
            $fields = [
                'status' => (object) [
                    'fieldKey'  => 'status',
                    'fieldName' => __('Status', 'bit-integrations-pro'),
                ],
                'flat_rate_basis' => (object) [
                    'fieldKey'  => 'flat_rate_basis',
                    'fieldName' => __('Flat Rate Basis', 'bit-integrations-pro'),
                ],
                'payment_email' => (object) [
                    'fieldKey'  => 'payment_email',
                    'fieldName' => __('Payment Email', 'bit-integrations-pro'),
                ],
                'rate_type' => (object) [
                    'fieldKey'  => 'rate_type',
                    'fieldName' => __('Rate Type', 'bit-integrations-pro'),
                ],
                'affiliate_note' => (object) [
                    'fieldKey'  => 'affiliate_note',
                    'fieldName' => __('Affiliate Note', 'bit-integrations-pro'),
                ],
                'oldStatus' => (object) [
                    'fieldKey'  => 'old_status',
                    'fieldName' => __('Old Status', 'bit-integrations-pro'),
                ],
                'name' => (object) [
                    'fieldKey'  => 'name',
                    'fieldName' => __('Name', 'bit-integrations-pro'),
                ],
                'username' => (object) [
                    'fieldKey'  => 'username',
                    'fieldName' => __('UserName', 'bit-integrations-pro'),
                ],
                'account_email' => (object) [
                    'fieldKey'  => 'account_email',
                    'fieldName' => __('Account Email', 'bit-integrations-pro'),
                ],
                'website_url' => (object) [
                    'fieldKey'  => 'website_url',
                    'fieldName' => __('website Url', 'bit-integrations-pro'),
                ],
                'promotion_method' => (object) [
                    'fieldKey'  => 'promotion_method',
                    'fieldName' => __('How will you promote us?', 'bit-integrations-pro'),
                ],
            ];
        } elseif ($id == static::AFFILIATE_REFERRAL_TYPE_REJECTED || $id == static::AFFILIATE_REFERRAL_TYPE_PAID) {
            $fields = [
                'affiliate_id' => (object) [
                    'fieldKey'  => 'affiliate_id',
                    'fieldName' => __('Affiliate ID', 'bit-integrations-pro'),
                ],
                'affiliate_url' => (object) [
                    'fieldKey'  => 'affiliate_url',
                    'fieldName' => __('Affiliate URL', 'bit-integrations-pro'),
                ],
                'referral_description' => (object) [
                    'fieldKey'  => 'referral_description',
                    'fieldName' => __('Referral Description', 'bit-integrations-pro'),
                ],
                'amount' => (object) [
                    'fieldKey'  => 'amount',
                    'fieldName' => __('Amount', 'bit-integrations-pro'),
                ],
                'context' => (object) [
                    'fieldKey'  => 'context',
                    'fieldName' => __('Context', 'bit-integrations-pro'),
                ],
                'campaign' => (object) [
                    'fieldKey'  => 'campaign',
                    'fieldName' => __('Campaign', 'bit-integrations-pro'),
                ],
                'reference' => (object) [
                    'fieldKey'  => 'reference',
                    'fieldName' => __('Reference', 'bit-integrations-pro'),
                ],
                'status' => (object) [
                    'fieldKey'  => 'status',
                    'fieldName' => __('Status', 'bit-integrations-pro'),
                ],
                'flat_rate_basis' => (object) [
                    'fieldKey'  => 'flat_rate_basis',
                    'fieldName' => __('Flat Rate Basis', 'bit-integrations-pro'),
                ],
                'account_email' => (object) [
                    'fieldKey'  => 'account_email',
                    'fieldName' => __('Account Email', 'bit-integrations-pro'),
                ],
                'payment_email' => (object) [
                    'fieldKey'  => 'payment_email',
                    'fieldName' => __('Payment Email', 'bit-integrations-pro'),
                ],
                'rate_type' => (object) [
                    'fieldKey'  => 'rate_type',
                    'fieldName' => __('Rate Type', 'bit-integrations-pro'),
                ],
                'affiliate_note' => (object) [
                    'fieldKey'  => 'affiliate_note',
                    'fieldName' => __('Affiliate Note', 'bit-integrations-pro'),
                ],
                'oldStatus' => (object) [
                    'fieldKey'  => 'old_status',
                    'fieldName' => __('Old Status', 'bit-integrations-pro'),
                ],
            ];
        } elseif ($id = static::AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE) {
            $fields = [
                'affiliate_id' => (object) [
                    'fieldKey'  => 'affiliate_id',
                    'fieldName' => __('Affiliate ID', 'bit-integrations-pro'),
                ],
                'affiliate_url' => (object) [
                    'fieldKey'  => 'affiliate_url',
                    'fieldName' => __('Affiliate URL', 'bit-integrations-pro'),
                ],
                'referral_description' => (object) [
                    'fieldKey'  => 'referral_description',
                    'fieldName' => __('Referral Description', 'bit-integrations-pro'),
                ],
                'amount' => (object) [
                    'fieldKey'  => 'amount',
                    'fieldName' => __('Amount', 'bit-integrations-pro'),
                ],
                'context' => (object) [
                    'fieldKey'  => 'context',
                    'fieldName' => __('Context', 'bit-integrations-pro'),
                ],
                'campaign' => (object) [
                    'fieldKey'  => 'campaign',
                    'fieldName' => __('Campaign', 'bit-integrations-pro'),
                ],
                'reference' => (object) [
                    'fieldKey'  => 'reference',
                    'fieldName' => __('Reference', 'bit-integrations-pro'),
                ],
                'flat_rate_basis' => (object) [
                    'fieldKey'  => 'flat_rate_basis',
                    'fieldName' => __('Flat Rate Basis', 'bit-integrations-pro'),
                ],
                'account_email' => (object) [
                    'fieldKey'  => 'account_email',
                    'fieldName' => __('Account Email', 'bit-integrations-pro'),
                ],
                'payment_email' => (object) [
                    'fieldKey'  => 'payment_email',
                    'fieldName' => __('Payment Email', 'bit-integrations-pro'),
                ],
                'rate_type' => (object) [
                    'fieldKey'  => 'rate_type',
                    'fieldName' => __('Rate Type', 'bit-integrations-pro'),
                ],
                'affiliate_note' => (object) [
                    'fieldKey'  => 'affiliate_note',
                    'fieldName' => __('Affiliate Note', 'bit-integrations-pro'),
                ],
            ];
        }

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => $field->fieldKey === 'payment_email' ? 'email' : 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function affiliateGetAllType()
    {
        $organizeType[] = [
            'type_id'   => 'any',
            'type_name' => __('Any', 'bit-integrations-pro')
        ];
        $typeId = 1;
        foreach (affiliate_wp()->referrals->types_registry->get_types() as $type_keys => $type) {
            $organizeType[] = [
                'type_id'   => $typeId,
                'type_name' => $type['label']
            ];
            $typeId++;
        }

        return $organizeType;
    }

    public static function newAffiliateApproved($affiliate_id, $status, $old_status)
    {
        if ('pending' === $status || !$flows = Flow::exists('Affiliate', static::AFFILIATE_APPROVED)) {
            return;
        }

        $affiliate = affwp_get_affiliate($affiliate_id);

        if (empty($affiliate) || empty($affiliate->user_id)) {
            return;
        }

        $data = static::setAffiliateData($affiliate_id, $affiliate, $status, $old_status);

        Flow::execute('Affiliate', static::AFFILIATE_APPROVED, $data, $flows);
    }

    public static function userBecomesAffiliate($affiliate_id, $status, $old_status)
    {
        if ('active' !== $status || !$flows = Flow::exists('Affiliate', static::USER_BECOMES_AN_AFFILIATE)) {
            return $status;
        }

        $affiliate = affwp_get_affiliate($affiliate_id);

        if (empty($affiliate) || empty($affiliate->user_id)) {
            return;
        }

        $data = static::setAffiliateData($affiliate_id, $affiliate, $status, $old_status);

        Flow::execute('Affiliate', static::USER_BECOMES_AN_AFFILIATE, $data, $flows);
    }

    public static function affiliateMakesReferral($referral_id)
    {
        $flows = Flow::exists('Affiliate', static::AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE);

        if (!$flows) {
            return;
        }

        $referral = affwp_get_referral($referral_id);
        $affiliate = affwp_get_affiliate($referral->affiliate_id);
        $user_id = affwp_get_affiliate_user_id($referral->affiliate_id);
        $affiliateNote = maybe_serialize(affwp_get_affiliate_meta($affiliate->affiliate_id, 'notes', true));
        $user = get_user_by('id', $user_id);

        $data = [
            'affiliate_id'         => $referral->affiliate_id,
            'affiliate_url'        => maybe_serialize(affwp_get_affiliate_referral_url(['affiliate_id' => $referral->affiliate_id])),
            'referral_description' => $referral->description,
            'amount'               => $referral->amount,
            'context'              => $referral->context,
            'campaign'             => $referral->campaign,
            'reference'            => $referral->reference,
            'flat_rate_basis'      => $affiliate->flat_rate_basis,
            'account_email'        => $user->user_email,
            'payment_email'        => $affiliate->payment_email,
            'rate_type'            => $affiliate->rate_type,
            'affiliate_note'       => $affiliateNote,

        ];

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }

        $allTypes = $flowDetails->allType;

        $selectedTypeID = $flowDetails->selectedType;

        foreach ($allTypes as $type) {
            if ($referral->type == $type->type_key && $type->type_id == $selectedTypeID) {
                Flow::execute('Affiliate', static::AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE, $data, $flows);

                break;
            }
        }

        if ($selectedTypeID == 'any') {
            Flow::execute('Affiliate', static::AFFILIATE_MAKE_REFERRAL_SPECIFIC_TYPE, $data, $flows);
        }
    }

    public static function affiliatesReferralSpecificTypeRejected($referral_id, $new_status, $old_status)
    {
        $flows = Flow::exists('Affiliate', static::AFFILIATE_REFERRAL_TYPE_REJECTED);
        if (!$flows) {
            return;
        }

        if ((string) $new_status === (string) $old_status || 'rejected' !== (string) $new_status) {
            return $new_status;
        }

        $referral = affwp_get_referral($referral_id);
        $type = $referral->type;
        $user_id = affwp_get_affiliate_user_id($referral->affiliate_id);
        $user = get_user_by('id', $user_id);
        $affiliate = affwp_get_affiliate($referral->affiliate_id);
        $affiliateNote = maybe_serialize(affwp_get_affiliate_meta($affiliate->affiliate_id, 'notes', true));

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }

        $allTypes = $flowDetails->allType;

        $selectedTypeID = $flowDetails->selectedType;

        $data = [
            'affiliate_id'         => $referral->affiliate_id,
            'affiliate_url'        => maybe_serialize(affwp_get_affiliate_referral_url(['affiliate_id' => $referral->affiliate_id])),
            'referral_description' => $referral->description,
            'amount'               => $referral->amount,
            'context'              => $referral->context,
            'campaign'             => $referral->campaign,
            'reference'            => $referral->reference,
            'status'               => $new_status,
            'flat_rate_basis'      => $affiliate->flat_rate_basis,
            'account_email'        => $user->user_email,
            'payment_email'        => $affiliate->payment_email,
            'rate_type'            => $affiliate->rate_type,
            'affiliate_note'       => $affiliateNote,
            'old_status'           => $old_status,

        ];

        foreach ($allTypes as $type) {
            if ($referral->type == $type->type_key && $type->type_id == $selectedTypeID) {
                Flow::execute('Affiliate', static::AFFILIATE_REFERRAL_TYPE_REJECTED, $data, $flows);
            }
        }

        if ($selectedTypeID == 'any') {
            Flow::execute('Affiliate', static::AFFILIATE_REFERRAL_TYPE_REJECTED, $data, $flows);
        }
    }

    public static function affiliatesReferralSpecificTypePaid($referral_id, $new_status, $old_status)
    {
        $flows = Flow::exists('Affiliate', static::AFFILIATE_REFERRAL_TYPE_PAID);
        if (!$flows) {
            return;
        }

        if ((string) $new_status === (string) $old_status || 'paid' !== (string) $new_status) {
            return $new_status;
        }

        $referral = affwp_get_referral($referral_id);
        $type = $referral->type;
        $user_id = affwp_get_affiliate_user_id($referral->affiliate_id);
        $user = get_user_by('id', $user_id);
        $affiliate = affwp_get_affiliate($referral->affiliate_id);
        $affiliateNote = maybe_serialize(affwp_get_affiliate_meta($affiliate->affiliate_id, 'notes', true));

        foreach ($flows as $flow) {
            if (\is_string($flow->flow_details)) {
                $flow->flow_details = json_decode($flow->flow_details);
                $flowDetails = $flow->flow_details;
            }
        }

        $allTypes = $flowDetails->allType;

        $selectedTypeID = $flowDetails->selectedType;

        $data = [
            'affiliate_id'         => $referral->affiliate_id,
            'affiliate_url'        => maybe_serialize(affwp_get_affiliate_referral_url(['affiliate_id' => $referral->affiliate_id])),
            'referral_description' => $referral->description,
            'amount'               => $referral->amount,
            'context'              => $referral->context,
            'campaign'             => $referral->campaign,
            'reference'            => $referral->reference,
            'status'               => $new_status,
            'flat_rate_basis'      => $affiliate->flat_rate_basis,
            'account_email'        => $user->user_email,
            'payment_email'        => $affiliate->payment_email,
            'rate_type'            => $affiliate->rate_type,
            'affiliate_note'       => $affiliateNote,
            'old_status'           => $old_status,

        ];

        foreach ($allTypes as $type) {
            if ($referral->type == $type->type_key && $type->type_id == $selectedTypeID) {
                Flow::execute('Affiliate', static::AFFILIATE_REFERRAL_TYPE_PAID, $data, $flows);
            }
        }

        if ($selectedTypeID == 'any') {
            Flow::execute('Affiliate', static::AFFILIATE_REFERRAL_TYPE_PAID, $data, $flows);
        }
    }

    private static function setAffiliateData($affiliate_id, $affiliate, $status, $old_status)
    {
        $user = get_user_by('id', $affiliate->user_id);

        return [
            'status'           => $status,
            'flat_rate_basis'  => $affiliate->flat_rate_basis,
            'payment_email'    => $affiliate->payment_email,
            'rate_type'        => $affiliate->rate_type,
            'old_status'       => $old_status,
            'name'             => $user->display_name,
            'account_email'    => $user->user_email,
            'username'         => affwp_get_affiliate_username($affiliate_id),
            'website_url'      => $user->user_url,
            'promotion_method' => affwp_get_affiliate_meta($affiliate_id, 'promotion_method', true)
        ];
    }
}
