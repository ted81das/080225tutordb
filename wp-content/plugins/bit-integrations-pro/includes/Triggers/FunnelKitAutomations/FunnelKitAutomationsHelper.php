<?php

namespace BitApps\BTCBI_PRO\Triggers\FunnelKitAutomations;

use BWFCRM_Tag;
use BWFCRM_Lists;

class FunnelKitAutomationsHelper
{
    public static function isPluginInstalled()
    {
        return class_exists('BWFCRM_Contact');
    }

    public static function getContactData($contact)
    {
        return [
            'contact_id'    => $contact->get_id(),
            'wpid'          => $contact->get_wpid(),
            'uid'           => $contact->get_uid(),
            'email'         => $contact->get_email(),
            'first_name'    => $contact->get_f_name(),
            'last_name'     => $contact->get_l_name(),
            'contact_no'    => $contact->contact_no(),
            'state'         => $contact->get_state(),
            'country'       => $contact->get_country(),
            'timezone'      => $contact->get_timezone(),
            'creation_date' => !empty($contact->get_creation_date()) ? $contact->get_creation_date() : '',
            'last_modified' => !empty($contact->get_last_modified()) ? $contact->get_last_modified() : '',
            'source'        => $contact->get_source(),
            'type'          => $contact->get_type(),
            'status'        => $contact->get_status(),
        ];
    }

    public static function getListData($lists)
    {
        if (! class_exists('BWFCRM_Lists')) {
            return [];
        }

        $lists = \is_array($lists) ? $lists : [$lists];

        $listData = [];
        foreach ($lists as $list) {
            if (!is_numeric($list) && (!\is_object($list) || !method_exists($list, 'get_id'))) {
                continue;
            }

            $listId = is_numeric($list) ? $list : $list->get_id();
            $BWFCRMList = BWFCRM_Lists::get_lists([$listId]);

            if (!empty($BWFCRMList)) {
                $listData[] = [
                    'list_id'   => $BWFCRMList[0]['ID'],
                    'list_name' => $BWFCRMList[0]['name'],
                ];
            }
        }

        return wp_json_encode($listData);
    }

    public static function getTagData($tags)
    {
        if (! class_exists('BWFCRM_Tag')) {
            return [];
        }

        $tags = \is_array($tags) ? $tags : [$tags];

        $tagsData = [];
        foreach ($tags as $tag) {
            if (!is_numeric($tag) && (!\is_object($tag) || !method_exists($tag, 'get_id'))) {
                continue;
            }

            $tagId = is_numeric($tag) ? $tag : $tag->get_id();
            $BWFCRMTag = BWFCRM_Tag::get_tags([$tagId]);

            if (!empty($BWFCRMTag)) {
                $tagsData[] = [
                    'tag_id'   => $BWFCRMTag[0]['ID'],
                    'tag_name' => $BWFCRMTag[0]['name'],
                ];
            }
        }

        return wp_json_encode($tagsData);
    }
}
