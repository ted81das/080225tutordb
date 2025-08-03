<?php

namespace BitApps\BTCBI_PRO\Triggers\WpSimplePay;

use BitCode\FI\Core\Util\Helper;

class WpSimplePayHelper
{
    public static function formatPaymentFormData($object)
    {
        $form_id = $object->metadata->simpay_form_id;
        $customer = static::formatCustomerData($object->customer ?? '');
        $paymentMethod = static::formatPaymentMethodData($object->payment_method ?? '');

        return Helper::prepareFetchFormatFields(array_merge([
            'form_id'              => $form_id,
            'amount'               => $object->amount ?? '',
            'description'          => $object->description ?? '',
            'currency'             => $object->currency ?? '',
            'latest_charge'        => $object->latest_charge ?? '',
            'confirmation_method'  => $object->confirmation_method ?? '',
            'cancellation_reason'  => $object->cancellation_reason ?? '',
            'capture_method'       => $object->capture_method ?? '',
            'payment_method_types' => !empty($object->payment_method_types) ? wp_json_encode($object->payment_method_types) : '',
        ], $customer, $paymentMethod));
    }

    public static function formatSubscriptionFormData($object)
    {
        $form_id = $object->metadata->simpay_form_id;
        $invoice = static::formatInvoiceData($object->latest_invoice ?? '');
        $customer = static::formatCustomerData($object->customer ?? '');
        $paymentMethod = static::formatPaymentMethodData($object->default_payment_method ?? '');

        $automaticTax = static::mapProperties([
            'automatic_tax_disabled_reason' => 'disabled_reason',
            'automatic_tax_enabled'         => 'enabled',
            'automatic_tax_liability'       => 'liability',
        ], $object->automatic_tax ?? null);

        $cancellationDetails = static::mapProperties([
            'cancellation_details_comment'  => 'comment',
            'cancellation_details_feedback' => 'feedback',
            'cancellation_details_reason'   => 'reason',
        ], $object->cancellation_details ?? null);

        $subscription = '';
        if (\function_exists('simpay_get_form')) {
            $form = simpay_get_form($form_id);
            $subscription = $form->company_name;
        }

        return Helper::prepareFetchFormatFields(array_merge(
            [
                'form_id'           => $form_id,
                'quantity'          => $object->quantity ?? '',
                'status'            => $object->status ?? '',
                'currency'          => $object->currency ?? '',
                'amount_due'        => $object->amount_due ?? '',
                'amount_paid'       => $object->amount_paid ?? '',
                'amount_remaining'  => $object->amount_remaining ?? '',
                'collection_method' => $object->collection_method ?? '',
                'subscription'      => $subscription,
            ],
            $invoice,
            $customer,
            $paymentMethod,
            $automaticTax,
            $cancellationDetails
        ));
    }

    public static function isPluginInstalled()
    {
        return \defined('SIMPLE_PAY_PLUGIN_NAME');
    }

    private static function formatInvoiceData($invoice = null)
    {
        return static::mapProperties([
            'invoice_id'             => 'id',
            'invoice_number'         => 'number',
            'account_country'        => 'account_country',
            'account_name'           => 'account_name',
            'account_tax_ids'        => 'account_tax_ids',
            'amount_due'             => 'amount_due',
            'amount_overpaid'        => 'amount_overpaid',
            'amount_paid'            => 'amount_paid',
            'amount_remaining'       => 'amount_remaining',
            'amount_shipping'        => 'amount_shipping',
            'application'            => 'application',
            'application_fee_amount' => 'application_fee_amount',
            'attempt_count'          => 'attempt_count',
            'attempted'              => 'attempted',
            'auto_advance'           => 'auto_advance',
            'billing_reason'         => 'billing_reason',
            'collection_method'      => 'collection_method',
            'currency'               => 'currency',
            'custom_fields'          => 'custom_fields',
            'customer_address'       => 'customer_address',
            'customer_email'         => 'customer_email',
            'customer_name'          => 'customer_name',
            'customer_phone'         => 'customer_phone',
            'customer_shipping'      => 'customer_shipping',
            'customer_tax_exempt'    => 'customer_tax_exempt',
            'default_payment_method' => 'default_payment_method',
            'default_source'         => 'default_source',
            'description'            => 'description',
            'discount'               => 'discount',
            'ending_balance'         => 'ending_balance',
            'hosted_invoice_url'     => 'hosted_invoice_url',
            'invoice_pdf'            => 'invoice_pdf',
            'issuer'                 => 'issuer.type',
            'paid'                   => 'paid',
            'subtotal'               => 'subtotal',
            'subtotal_excluding_tax' => 'subtotal_excluding_tax',
            'tax'                    => 'tax',
            'total'                  => 'total',
            'total_excluding_tax'    => 'total_excluding_tax',
        ], $invoice);
    }

    private static function formatCustomerData($customer = null)
    {
        return static::mapProperties([
            'customer_id'           => 'id',
            'name'                  => 'name',
            'balance'               => 'balance',
            'phone'                 => 'phone',
            'currency'              => 'currency',
            'address'               => 'address',
            'default_source'        => 'default_source',
            'description'           => 'description',
            'discount'              => 'discount',
            'email'                 => 'email',
            'next_invoice_sequence' => 'next_invoice_sequence',
            'shipping'              => 'shipping',
        ], $customer);
    }

    private static function formatPaymentMethodData($paymentMethod = null)
    {
        $billing = $paymentMethod->billing_details ?? null;
        $card = $paymentMethod->card ?? null;

        $billingData = static::mapProperties([
            'billing_email'       => 'email',
            'billing_name'        => 'name',
            'billing_phone'       => 'phone',
            'billing_city'        => 'address.city',
            'billing_country'     => 'address.country',
            'billing_line1'       => 'address.line1',
            'billing_line2'       => 'address.line2',
            'billing_postal_code' => 'address.postal_code',
            'billing_state'       => 'address.state',
        ], $billing);

        $cardData = static::mapProperties([
            'card_brand'                            => 'brand',
            'card_checks_address_line1_check'       => 'checks.address_line1_check',
            'card_checks_address_postal_code_check' => 'checks.address_postal_code_check',
            'card_checks_cvc_check'                 => 'checks.cvc_check',
            'card_country'                          => 'country',
            'card_display_brand'                    => 'display_brand',
            'card_exp_month'                        => 'exp_month',
            'card_exp_year'                         => 'exp_year',
            'card_fingerprint'                      => 'fingerprint',
            'card_funding'                          => 'funding',
            'card_generated_from'                   => 'generated_from',
            'card_last4'                            => 'last4',
            'card_regulated_status'                 => 'regulated_status',
            'card_wallet'                           => 'wallet',
        ], $card);

        $baseData = static::mapProperties([
            'payment_method_id'     => 'id',
            'type'                  => 'type',
            'allow_redisplay'       => 'allow_redisplay',
            'balance'               => 'balance',
            'phone'                 => 'phone',
            'currency'              => 'currency',
            'address'               => 'address',
            'default_source'        => 'default_source',
            'description'           => 'description',
            'discount'              => 'discount',
            'email'                 => 'email',
            'next_invoice_sequence' => 'next_invoice_sequence',
            'shipping'              => 'shipping',
        ], $paymentMethod);

        return array_merge($baseData, $billingData, $cardData);
    }

    private static function mapProperties(array $mapping, $source = null): array
    {
        $result = [];
        foreach ($mapping as $key => $path) {
            $result[$key] = static::getValueByPath($source, $path);
        }

        return $result;
    }

    private static function getValueByPath($object, string $path)
    {
        if (!$object) {
            return '';
        }

        $segments = explode('.', $path);
        foreach ($segments as $segment) {
            if (!\is_object($object) || !isset($object->{$segment})) {
                return '';
            }
            $object = $object->{$segment};
        }

        return $object ?? '';
    }
}
