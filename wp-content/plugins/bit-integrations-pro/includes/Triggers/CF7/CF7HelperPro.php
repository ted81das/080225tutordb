<?php

namespace BitApps\BTCBI_PRO\Triggers\CF7;

class CF7HelperPro
{
    public static function getAdvanceCustomHtmlFields($form_text)
    {
        $fields = [];

        preg_match_all('/<input[^>]*name="([^"]*)"[^>]*type="([^"]*)"|<input[^>]*type="([^"]*)"[^>]*name="([^"]*)"/is', $form_text, $inputMatches);
        foreach ($inputMatches[0] as $index => $input) {
            $name = !empty($inputMatches[1][$index]) ? $inputMatches[1][$index] : $inputMatches[4][$index];
            $type = !empty($inputMatches[2][$index]) ? $inputMatches[2][$index] : $inputMatches[3][$index];

            $fields[] = ['name'  => $name, 'type'  => $type, 'label' => $name];
        }

        preg_match_all('/<select[^>]*name="([^"]*)"[^>]*>(.*?)<\/select>/is', $form_text, $selectMatches);
        foreach ($selectMatches[1] as $name) {
            $fields[] = ['name'  => $name, 'type'  => 'select', 'label' => $name];
        }

        preg_match_all('/<textarea[^>]*name="([^"]*)"[^>]*>(.*?)<\/textarea>/is', $form_text, $textareaMatches);
        foreach ($textareaMatches[1] as $name) {
            $fields[] = ['name'  => $name, 'type'  => 'textarea', 'label' => $name];
        }

        return $fields;
    }
}
