<?php

namespace BitApps\BTCBI_PRO\Actions\HighLevel;

class HighLevelUtilitiesPro
{
    public static function contactUtilities($module, $selectedOptions, $actions)
    {
        $tags = [];

        if (isset($selectedOptions['selectedTags']) && !empty($selectedOptions['selectedTags'])) {
            $tags = explode(',', $selectedOptions['selectedTags']);
        }

        $data['tags'] = $tags;

        if (!empty($actions) && isset($actions['dnd']) && $actions['dnd']) {
            $data['dnd'] = true;
        }

        return $data;
    }

    public static function opportunityUtilities($module, $selectedOptions, $actions)
    {
        $tags = [];

        if (isset($selectedOptions['selectedTags']) && !empty($selectedOptions['selectedTags'])) {
            $tags = explode(',', $selectedOptions['selectedTags']);
        }

        $data['tags'] = $tags;

        return $data;
    }
}
