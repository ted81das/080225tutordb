<?php

namespace BitApps\BTCBI_PRO\Actions\JetEngine;

class JetEngineRecordHelper
{
    public static function createPostTypeActions($module, $createCPTSelectedOptions, $actions)
    {
        $selectedMenuPosition = $createCPTSelectedOptions['selectedMenuPosition'];
        $selectedMenuIcon = $createCPTSelectedOptions['selectedMenuIcon'];
        $selectedSupports = $createCPTSelectedOptions['selectedSupports'];

        $data = [];

        if (!empty($selectedMenuPosition)) {
            $data['menu_position'] = $selectedMenuPosition;
        }

        if (!empty($selectedMenuIcon)) {
            $data['menu_icon'] = $selectedMenuIcon;
        }

        if (!empty($selectedSupports)) {
            $data['supports'] = explode(',', $selectedSupports);
        }

        if (!empty($actions)) {
            foreach ($actions as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function createContentTypeActions($module, $createCPTSelectedOptions, $actions)
    {
        $selectedMenuPosition = $createCPTSelectedOptions['selectedMenuPosition'];
        $selectedMenuIcon = $createCPTSelectedOptions['selectedMenuIcon'];

        $data = [];

        if (!empty($selectedMenuPosition)) {
            $data['position'] = $selectedMenuPosition;
        }

        if (!empty($selectedMenuIcon)) {
            $data['icon'] = $selectedMenuIcon;
        }

        return $data;
    }

    public static function createTaxonomyActions($module, $taxOptions, $actions)
    {
        $data = [];

        if (!empty($actions)) {
            foreach ($actions as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public static function createRelationActions($module, $relOptions, $actions)
    {
        $data = [];

        if (!empty($actions)) {
            foreach ($actions as $key => $value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
