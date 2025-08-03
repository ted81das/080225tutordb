<?php

namespace BitApps\BTCBI_PRO\Triggers\FluentCrm;

use BitCode\FI\Flow\Flow;
use FluentCrm\App\Models\Lists;
use FluentCrm\App\Models\Tag;

final class FluentCrmController
{
    private const TAG_ADDED_TO_CONTACT = 'fluentcrm-1';

    private const TAG_REMOVED_FROM_CONTACT = 'fluentcrm-2';

    private const CONTACT_ADDED_TO_LIST = 'fluentcrm-3';

    private const CONTACT_REMOVED_FROM_LIST = 'fluentcrm-4';

    private const SET_CONTACT_STATUS = 'fluentcrm-5';

    private const CONTACT_CREATE = 'fluentcrm-6';

    public static function info()
    {
        $plugin_path = 'fluent-crm/fluent-crm.php';

        return [
            'name'           => 'Fluent CRM',
            'title'          => __('Fluent CRM - FluentCRM is a Self Hosted Email Marketing Automation Plugin for WordPress', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'fluent-crm/fluent-crm.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('fluent-crm/fluent-crm.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'fluentcrm/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'fluentcrm/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function checkedExistsFluentCRM()
    {
        if (!is_plugin_active('fluent-crm/fluent-crm.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Fluent CRM'));
        } else {
            return true;
        }
    }

    public function getAll()
    {
        self::checkedExistsFluentCRM();

        wp_send_json_success([
            ['id' => static::TAG_ADDED_TO_CONTACT, 'title' => __('A tag is added to contact', 'bit-integrations-pro')],
            ['id' => static::TAG_REMOVED_FROM_CONTACT, 'title' => __('A tag is removed from contact', 'bit-integrations-pro')],
            ['id' => static::CONTACT_ADDED_TO_LIST, 'title' => __('A contact is added to a list', 'bit-integrations-pro')],
            ['id' => static::CONTACT_REMOVED_FROM_LIST, 'title' => __('A contact is remove from a list', 'bit-integrations-pro')],
            ['id' => static::SET_CONTACT_STATUS, 'title' => __('A contact set to a specific status', 'bit-integrations-pro')],
            ['id' => static::CONTACT_CREATE, 'title' => __('A contact is create', 'bit-integrations-pro')],
        ]);
    }

    public function get_a_form($data)
    {
        self::checkedExistsFluentCRM();
        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Trigger doesn\'t exists any field', 'bit-integrations-pro'));
        }
        if ($data->id == static::TAG_ADDED_TO_CONTACT || $data->id == static::TAG_REMOVED_FROM_CONTACT) {
            $tags[] = [
                'tag_id'    => 'any',
                'tag_title' => __('Any Tag', 'bit-integrations-pro')
            ];
            $fluentCrmTags = self::fluentCrmTags();

            $responseData['tags'] = array_merge($tags, $fluentCrmTags);
        } elseif ($data->id == static::CONTACT_ADDED_TO_LIST || $data->id == static::CONTACT_REMOVED_FROM_LIST) {
            $lists[] = [
                'list_id'    => 'any',
                'list_title' => __('Any List', 'bit-integrations-pro')
            ];
            $fluentCrmLists = self::fluentCrmLists();

            $responseData['lists'] = array_merge($lists, $fluentCrmLists);
        } elseif ($data->id == static::SET_CONTACT_STATUS) {
            $status[] = [
                'status_id'    => 'any',
                'status_title' => __('Any status', 'bit-integrations-pro')
            ];
            $fluentCrmStatus = self::fluentCrmStatus();

            $responseData['status'] = array_merge($status, $fluentCrmStatus);
        }

        $responseData['fields'] = $fields;
        wp_send_json_success($responseData);
    }

    public static function fluentCrmStatus()
    {
        $statuses = [
            'subscribed'   => 'Subscribed',
            'pending'      => 'Pending',
            'unsubscribed' => 'Unsubscribed',
            'bounced'      => 'Bounced',
            'complained'   => 'Complained',
        ];
        $fluentCrmStatus = [];

        foreach ($statuses as $key => $status) {
            $fluentCrmStatus[] = [
                'status_id'    => $key,
                'status_title' => $status
            ];
        }

        return $fluentCrmStatus;
    }

    public static function fluentCrmTags()
    {
        self::checkedExistsFluentCRM();
        $tags = Tag::get();

        $fluentCrmTags = [];
        foreach ($tags as $tag) {
            $fluentCrmTags[] = [
                'tag_id'    => $tag->id,
                'tag_title' => $tag->title
            ];
        }

        return $fluentCrmTags;
    }

    public static function fluentCrmLists()
    {
        self::checkedExistsFluentCRM();
        $lists = Lists::get();

        $fluentCrmLists = [];
        foreach ($lists as $list) {
            $fluentCrmLists[] = [
                'list_id'    => $list->id,
                'list_title' => $list->title
            ];
        }

        return $fluentCrmLists;
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

        $fieldsCommon = [
            'Tags' => (object) [
                'fieldKey'  => 'tags',
                'fieldName' => __('Tags', 'bit-integrations-pro'),
                'required'  => false,
            ],
            'Lists' => (object) [
                'fieldKey'  => 'lists',
                'fieldName' => __('Lists', 'bit-integrations-pro'),
                'required'  => false,
            ],
        ];

        if ($id === static::TAG_ADDED_TO_CONTACT) {
            $fields = [
                'Tag IDs' => (object) [
                    'fieldKey'  => 'tag_ids',
                    'fieldName' => __('Tag IDs', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
        } elseif ($id === static::TAG_REMOVED_FROM_CONTACT) {
            $fields = [
                'Removed Tag IDs' => (object) [
                    'fieldKey'  => 'removed_tag_ids',
                    'fieldName' => __('Removed Tag IDs', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
        } elseif ($id === static::CONTACT_ADDED_TO_LIST) {
            $fields = [
                'List IDs' => (object) [
                    'fieldKey'  => 'list_ids',
                    'fieldName' => __('List IDs', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
        } elseif ($id === static::CONTACT_REMOVED_FROM_LIST) {
            $fields = [
                'Remove List IDs' => (object) [
                    'fieldKey'  => 'remove_list_ids',
                    'fieldName' => __('Remove List IDs', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
        } elseif ($id === static::SET_CONTACT_STATUS) {
            $fields = [
                'Old Status' => (object) [
                    'fieldKey'  => 'old_status',
                    'fieldName' => __('Old Status', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'New Status' => (object) [
                    'fieldKey'  => 'new_status',
                    'fieldName' => __('New Status', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
        } elseif ($id === static::CONTACT_CREATE) {
            $fields = [
                'Tags' => (object) [
                    'fieldKey'  => 'tags',
                    'fieldName' => __('Tags', 'bit-integrations-pro'),
                    'required'  => false,
                ],
                'Lists' => (object) [
                    'fieldKey'  => 'lists',
                    'fieldName' => __('Lists', 'bit-integrations-pro'),
                    'required'  => false,
                ],
            ];
            foreach ($fields as $field) {
                $fieldsNew[] = [
                    'name'  => $field->fieldKey,
                    'type'  => 'text',
                    'label' => $field->fieldName,
                ];
            }

            // $fields = $fieldsNew + self::fluentCrmFields();
            return array_merge($fieldsNew, self::fluentCrmFields());
        }

        $fields = $fields + $fieldsCommon;
        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        $contactFields = self::fluentCrmFields();

        return array_merge($fieldsNew, $contactFields);
    }

    public static function fluentCrmFields()
    {
        self::checkedExistsFluentCRM();

        $fieldOptions = [];
        $models = ['FluentCrm\\App\\Models\\Subscriber', 'FluentCrm\\App\\Models\\Company'];
        $customModels = ['FluentCrm\\App\\Models\\CustomContactField', 'FluentCrm\\App\\Models\\CustomCompanyField'];

        foreach ($models as $class) {
            if (!class_exists($class)) {
                continue;
            }

            foreach ($class::mappables() as $key => $column) {
                $fieldOptions[] = [
                    'name'  => $class === 'FluentCrm\\App\\Models\\Company' ? "company_{$key}" : $key,
                    'label' => $column,
                    'type'  => 'primary',
                ];
            }
        }

        foreach ($customModels as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $instance = new $class();
            foreach ($instance->getGlobalFields()['fields'] as $field) {
                $fieldOptions[] = [
                    'name'  => $field['slug'],
                    'label' => $field['label'],
                    'type'  => 'custom'
                ];
            }
        }

        return $fieldOptions;
    }

    public static function handle_add_tag($tag_ids, $subscriber)
    {
        $flows = Flow::exists('FluentCrm', static::TAG_ADDED_TO_CONTACT);
        $flows = self::flowFilter($flows, 'selectedTag', $tag_ids);

        if (!$flows) {
            return;
        }

        $data = self::getContactData($subscriber->email);
        $data['tag_ids'] = $tag_ids;

        Flow::execute('FluentCrm', static::TAG_ADDED_TO_CONTACT, $data, $flows);
    }

    // tag_ids = remove tag ids
    public static function handle_remove_tag($tag_ids, $subscriber)
    {
        $flows = Flow::exists('FluentCrm', static::TAG_REMOVED_FROM_CONTACT);
        $flows = self::flowFilter($flows, 'selectedTag', $tag_ids);

        if (!$flows) {
            return;
        }

        $email = $subscriber->email;

        $data = [
            'removed_tag_ids' => $tag_ids,
        ];

        $dataContact = self::getContactData($email);

        $data = $data + $dataContact;

        Flow::execute('FluentCrm', static::TAG_REMOVED_FROM_CONTACT, $data, $flows);
    }

    public static function handle_add_list($list_ids, $subscriber)
    {
        $flows = Flow::exists('FluentCrm', static::CONTACT_ADDED_TO_LIST);
        $flows = self::flowFilter($flows, 'selectedList', $list_ids);

        if (!$flows) {
            return;
        }

        $email = $subscriber->email;

        $data = [
            'list_ids' => $list_ids,
        ];

        $dataContact = self::getContactData($email);

        $data = $data + $dataContact;

        Flow::execute('FluentCrm', static::CONTACT_ADDED_TO_LIST, $data, $flows);
    }

    public static function handle_remove_list($list_ids, $subscriber)
    {
        $flows = Flow::exists('FluentCrm', static::CONTACT_REMOVED_FROM_LIST);
        $flows = self::flowFilter($flows, 'selectedList', $list_ids);

        if (!$flows) {
            return;
        }

        $email = $subscriber->email;

        $data = [
            'remove_list_ids' => $list_ids,
        ];

        $dataContact = self::getContactData($email);

        $data = $data + $dataContact;
        Flow::execute('FluentCrm', static::CONTACT_REMOVED_FROM_LIST, $data, $flows);
    }

    public static function handle_change_status($subscriber, $old_status)
    {
        $newStatus = [$subscriber->status];

        $flows = Flow::exists('FluentCrm', static::SET_CONTACT_STATUS);
        $flows = self::flowFilter($flows, 'selectedStatus', $newStatus);

        $email = $subscriber->email;

        $data = [
            'old_status' => $old_status,
            'new_status' => $newStatus,
        ];

        $dataContact = self::getContactData($email);

        $data = $data + $dataContact;

        Flow::execute('FluentCrm', static::SET_CONTACT_STATUS, $data, $flows);
    }

    public static function handle_contact_create($subscriber)
    {
        $flows = Flow::exists('FluentCrm', static::CONTACT_CREATE);

        if (!$flows) {
            return;
        }

        $email = $subscriber->email;
        $transientKey = "btcbi_fluentcrm_contact_created_{$email}";

        if (get_transient($transientKey)) {
            return;
        }

        $data = self::getContactData($email);
        set_transient($transientKey, true, 5);
        Flow::execute('FluentCrm', static::CONTACT_CREATE, $data, $flows);
    }

    public static function getContactData($email)
    {
        $contactApi = FluentCrmApi('contacts');
        $contact = $contactApi->getContact($email);

        if (!$contact) {
            return [];
        }

        $customFields = $contact->custom_fields();
        $data = array_merge($contact->toArray(), static::getCompanyData($contact), empty($customFields) ? [] : $customFields);

        $lists = $contact->lists;
        $fluentCrmLists = [];
        foreach ($lists as $list) {
            $fluentCrmLists[] = (object) [
                'list_id'    => $list->id,
                'list_title' => $list->title
            ];
        }

        $data['tags'] = implode(', ', array_column($contact->tags->toArray() ?? [], 'title'));
        $data['lists'] = $fluentCrmLists;

        return $data;
    }

    public static function getFluentCrmTags()
    {
        $tags[] = [
            'tag_id'    => 'any',
            'tag_title' => 'Any Tag',
        ];
        $fluentCrmTags = self::fluentCrmTags();

        $tags = array_merge($tags, $fluentCrmTags);
        wp_send_json_success($tags);
    }

    public static function getFluentCrmList()
    {
        $lists[] = [
            'list_id'    => 'any',
            'list_title' => 'Any List',
        ];
        $fluentCrmLists = self::fluentCrmLists();

        $lists = array_merge($lists, $fluentCrmLists);
        wp_send_json_success($lists);
    }

    public static function getFluentCrmStatus()
    {
        $status[] = [
            'status_id'    => 'any',
            'status_title' => 'Any status',
        ];
        $fluentCrmStatus = self::fluentCrmStatus();

        $status = array_merge($status, $fluentCrmStatus);
        wp_send_json_success($status);
    }

    protected static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];
        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }
                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || \in_array($flow->flow_details->{$key}, $value) || $flow->flow_details->{$key} === '') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }

    private static function getCompanyData($contact)
    {
        $data = [];
        if (!class_exists(\FluentCrm\App\Services\Helper::class) || !\FluentCrm\App\Services\Helper::isCompanyEnabled()) {
            return $data;
        }

        $company = $contact->company;
        if (empty($company)) {
            return $data;
        }

        foreach ($company->toArray() as $key => $value) {
            $data["company_{$key}"] = $value;
        }

        $customFields = $company->meta['custom_values'];
        if (!empty($customFields)) {
            $data = array_merge($data, $customFields);
        }

        return $data;
    }
}
