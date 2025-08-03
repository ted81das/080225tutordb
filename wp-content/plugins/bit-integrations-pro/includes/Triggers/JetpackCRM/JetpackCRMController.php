<?php

namespace BitApps\BTCBI_PRO\Triggers\JetpackCRM;

use BitApps\BTCBI_PRO\Triggers\TriggerController;
use BitCode\FI\Core\Util\Helper;
use BitCode\FI\Flow\Flow;

final class JetpackCRMController
{
    public static function info()
    {
        return [
            'name'              => 'Jetpack CRM',
            'title'             => __('JetpackCRM is a WordPress Customer Support plugin.', 'bit-integrations-pro'),
            'type'              => 'custom_form_submission',
            'is_active'         => JetpackCRMHelper::isPluginInstalled(),
            'documentation_url' => '#',
            'tutorial_url'      => '#',
            'tasks'             => [
                'action' => 'jetpack_crm/get',
                'method' => 'get',
            ],
            'fetch' => [
                'action' => 'jetpack_crm/test',
                'method' => 'post',
            ],
            'fetch_remove' => [
                'action' => 'jetpack_crm/test/remove',
                'method' => 'post',
            ],
            'isPro' => true
        ];
    }

    public function getAllTasks()
    {
        if (!JetpackCRMHelper::isPluginInstalled()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Jetpack CRM'));
        }

        wp_send_json_success(JetpackCRMHelper::allTasks());
    }

    public function getTestData($data)
    {
        return TriggerController::getTestData($data->triggered_entity_id);
    }

    public function removeTestData($data)
    {
        return TriggerController::removeTestData($data, $data->triggered_entity_id);
    }

    public static function handleCompanyCreated($company_id)
    {
        $formData = JetpackCRMHelper::formatCompanyData($company_id);

        return static::flowExecute('zbs_new_company', $formData);
    }

    public static function handleCompanyDeleted($company_id)
    {
        if (empty($company_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('company_id', $company_id, 'Company id');

        return static::flowExecute('zbs_delete_company', $formData);
    }

    public static function handleContactCreated($contact_id)
    {
        $formData = JetpackCRMHelper::formatContactData($contact_id);

        return static::flowExecute('zbs_new_customer', $formData);
    }

    public static function handleContactDeleted($contact_id)
    {
        if (empty($contact_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('contact_id', $contact_id, 'Contact id');

        return static::flowExecute('zbs_delete_customer', $formData);
    }

    public static function handleEventDeleted($event_id)
    {
        if (empty($event_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('event_id', $event_id, 'Event id');

        return static::flowExecute('zbs_delete_event', $formData);
    }

    public static function handleInvoiceDeleted($invoice_id)
    {
        if (empty($invoice_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('invoice_id', $invoice_id, 'Invoice id');

        return static::flowExecute('zbs_delete_invoice', $formData);
    }

    public static function handleQuoteAccepted($quote_id)
    {
        $formData = JetpackCRMHelper::formatQuoteData($quote_id);

        return static::flowExecute('jpcrm_quote_accepted', $formData);
    }

    public static function handleQuoteCreated($quote_id)
    {
        $formData = JetpackCRMHelper::formatQuoteData($quote_id);

        return static::flowExecute('zbs_new_quote', $formData);
    }

    public static function handleQuoteDeleted($quote_id)
    {
        if (empty($quote_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('quote_id', $quote_id, 'Quote id');

        return static::flowExecute('zbs_delete_quote', $formData);
    }

    public static function handleTransactionDeleted($transaction_id)
    {
        if (empty($transaction_id)) {
            return;
        }

        $formData = JetpackCRMHelper::formatSingleField('transaction_id', $transaction_id, 'Transaction id');

        return static::flowExecute('zbs_delete_transaction', $formData);
    }

    private static function flowExecute($triggered_entity_id, $formData)
    {
        if (empty($formData) || !\is_array($formData)) {
            return;
        }

        Helper::setTestData("btcbi_{$triggered_entity_id}_test", array_values($formData));

        $flows = Flow::exists('JetpackCRM', $triggered_entity_id);
        if (!$flows) {
            return;
        }

        Flow::execute('JetpackCRM', $triggered_entity_id, array_column($formData, 'value', 'name'), $flows);

        return ['type' => 'success'];
    }
}
