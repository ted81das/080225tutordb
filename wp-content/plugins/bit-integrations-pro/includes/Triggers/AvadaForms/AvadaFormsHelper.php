<?php

namespace BitApps\BTCBI_PRO\Triggers\AvadaForms;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class AvadaFormsHelper
{
    public static function formatFields($formSubmission, $formId)
    {
        $fields = ['form_id' => $formId];
        $form_fields = json_decode(stripslashes($_POST['field_types']), true);
        $submissionsData = $formSubmission['data'];

        foreach ($form_fields as $key => $type) {
            if ('submit' === $type) {
                continue;
            }

            $value = (isset($submissionsData[$key])) ? $submissionsData[$key] : '';

            if ('checkbox' === $type) {
                $value = ('' === $value) ? [''] : $value;
                $value = (\is_array($value) && 1 === \count($value)) ? $value[0] : wp_json_encode($value);
            }

            if (\is_array($value) || \is_object($value)) {
                $fields = Helper::flattenNestedData($fields, $key, $value);
            } else {
                $fields[$key] = $value;
            }
        }

        foreach ($formSubmission['submission'] as $key => $value) {
            $fields['submission_' . $key] = $value;
        }

        $fields = array_merge($fields, User::currentUser());

        return Helper::prepareFetchFormatFields($fields);
    }

    public static function isPluginInstalled()
    {
        return class_exists('Fusion_Builder_Form_Helper');
    }
}
