<?php

namespace BitApps\BTCBI_PRO\Triggers\JetpackCRM;

use BitCode\FI\Core\Util\Helper;

class JetpackCRMHelper
{
    public static function isPluginInstalled()
    {
        return class_exists('ZeroBSCRM');
    }

    public static function allTasks()
    {
        return [
            ['form_name' => __('Company Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_new_company', 'skipPrimaryKey' => true],
            ['form_name' => __('Company Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_company', 'skipPrimaryKey' => true],
            ['form_name' => __('Contact Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_new_customer', 'skipPrimaryKey' => true],
            ['form_name' => __('Contact Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_customer', 'skipPrimaryKey' => true],
            ['form_name' => __('Event Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_event', 'skipPrimaryKey' => true],
            ['form_name' => __('Invoice Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_invoice', 'skipPrimaryKey' => true],
            ['form_name' => __('Quote Accepted', 'bit-integrations-pro'), 'triggered_entity_id' => 'jpcrm_quote_accepted', 'skipPrimaryKey' => true],
            ['form_name' => __('Quote Created', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_new_quote', 'skipPrimaryKey' => true],
            ['form_name' => __('Quote Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_quote', 'skipPrimaryKey' => true],
            ['form_name' => __('Transaction Deleted', 'bit-integrations-pro'), 'triggered_entity_id' => 'zbs_delete_transaction', 'skipPrimaryKey' => true],
        ];
    }

    public static function formatCompanyData($company_id, $raw = false)
    {
        if (empty($company_id) || !\function_exists('zeroBS_getCompany')) {
            return [];
        }

        $company = zeroBS_getCompany($company_id);
        if (! $company) {
            return [];
        }

        $company = array_merge($company, [
            'main_address_line_1'        => $company['addr1'] ?? '',
            'main_address_line_2'        => $company['addr2'] ?? '',
            'main_address_city'          => $company['city'] ?? '',
            'main_address_state'         => $company['county'] ?? '',
            'main_address_postal_code'   => $company['postcode'] ?? '',
            'main_address_country'       => $company['country'] ?? '',
            'second_address_line_1'      => $company['secaddr1'] ?? '',
            'second_address_line_2'      => $company['secaddr2'] ?? '',
            'second_address_city'        => $company['seccity'] ?? '',
            'second_address_state'       => $company['seccounty'] ?? '',
            'second_address_postal_code' => $company['secpostcode'] ?? '',
            'second_address_country'     => $company['seccountry'] ?? '',
            'main_telephone'             => $company['maintel'] ?? '',
            'secondary_telephone'        => $company['sectel'] ?? '',
        ]);

        $unnecessaryKeys = [
            'addr1', 'addr2', 'city', 'county', 'postcode', 'country', 'secaddr1', 'secaddr2', 'seccity', 'seccounty',
            'secpostcode', 'seccountry', 'maintel', 'sectel', 'created', 'lastupdated', 'lastcontacted', 'contacts',
        ];

        $company = static::removeKeys($company, $unnecessaryKeys);

        return $raw ? $company : Helper::prepareFetchFormatFields($company);
    }

    public static function formatContactData($contact_id, $raw = false)
    {
        if (empty($contact_id) || !\function_exists('zeroBS_getCustomer')) {
            return [];
        }

        $contact = zeroBS_getCustomer($contact_id);
        if (! $contact) {
            return [];
        }

        $contact = array_merge($contact, [
            'full_name'                  => $contact['fullname'] ?? '',
            'first_name'                 => $contact['fname'] ?? '',
            'last_name'                  => $contact['lname'] ?? '',
            'main_address_line_1'        => $contact['addr1'] ?? '',
            'main_address_line_2'        => $contact['addr2'] ?? '',
            'main_address_city'          => $contact['city'] ?? '',
            'main_address_state'         => $contact['county'] ?? '',
            'main_address_postal_code'   => $contact['postcode'] ?? '',
            'main_address_country'       => $contact['country'] ?? '',
            'second_address_line_1'      => $contact['secaddr_addr1'] ?? '',
            'second_address_line_2'      => $contact['secaddr_addr2'] ?? '',
            'second_address_city'        => $contact['secaddr_city'] ?? '',
            'second_address_state'       => $contact['secaddr_county'] ?? '',
            'second_address_postal_code' => $contact['secaddr_postcode'] ?? '',
            'second_address_country'     => $contact['secaddr_country'] ?? '',
            'home_telephone'             => $contact['hometel'] ?? '',
            'work_telephone'             => $contact['worktel'] ?? '',
            'mobile_telephone'           => $contact['mobtel'] ?? '',
        ]);

        $unnecessaryKeys = [
            'fullname', 'fname', 'lname', 'addr1', 'addr2', 'city', 'county', 'postcode', 'country',
            'secaddr_addr1', 'secaddr_addr2', 'secaddr_city', 'secaddr_county', 'secaddr_postcode',
            'secaddr_country', 'hometel', 'worktel', 'mobtel', 'owner', 'wpid', 'lastcontacted', 'createduts',
            'lastupdated', 'lastcontacteduts',
        ];

        $contact = static::removeKeys($contact, $unnecessaryKeys);

        return $raw ? $contact : Helper::prepareFetchFormatFields($contact);
    }

    public static function formatSingleField($key, $value, $label)
    {
        return [
            $key => [
                'name'  => $key . '.value',
                'type'  => 'number',
                'label' => $label . ' (' . $value . ')',
                'value' => $value,
            ]
        ];
    }

    public static function formatQuoteData($quote_id)
    {
        if (empty($quote_id) || !\function_exists('zeroBS_getQuote') || !\function_exists('zeroBS_getQuoteStatus')) {
            return [];
        }

        $quote = zeroBS_getQuote($quote_id);
        if (empty($quote)) {
            return [];
        }

        $quote['quote_status'] = str_replace(['<strong>', '</strong>'], '', zeroBS_getQuoteStatus($quote));
        $quote['quote_date'] = $quote['date_date'];

        $quote['contact'] = static::formatContactData(static::getCompanyOrContactId($quote, 'contact'), true);
        $quote['company'] = static::formatCompanyData(static::getCompanyOrContactId($quote, 'company'), true);

        $unnecessaryKeys = [
            'date_date', 'owner', 'id_override', 'date', 'send_attachments', 'hash', 'lastviewed',
            'accepted', 'created', 'lastupdated', 'status', 'acceptedip', 'acceptedsigned'
        ];

        return Helper::prepareFetchFormatFields(static::removeKeys($quote, $unnecessaryKeys));
    }

    private static function getCompanyOrContactId($quote, $key)
    {
        return $quote[$key][0]['id'] ?? '';
    }

    private static function removeKeys(array $data, array $keys)
    {
        return array_diff_key($data, array_flip($keys));
    }
}
