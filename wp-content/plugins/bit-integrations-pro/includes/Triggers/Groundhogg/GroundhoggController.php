<?php

namespace BitApps\BTCBI_PRO\Triggers\Groundhogg;

use BitCode\FI\Flow\Flow;

final class GroundhoggController
{
    public static function info()
    {
        $plugin_path = self::pluginActive('get_name');

        return [
            'name'           => 'Groundhogg',
            'title'          => __('Groundhogg is the platform web creators choose to build professional WordPress websites, grow their skills, and build their business. Start for free today!', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => $plugin_path,
            'type'           => 'form',
            'is_active'      => is_plugin_active($plugin_path),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'groundhogg/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'groundhogg/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public static function pluginActive($option = null)
    {
        if (is_plugin_active('groundhogg/groundhogg.php')) {
            return $option === 'get_name' ? 'groundhogg/groundhogg.php' : true;
        } elseif (is_plugin_active('groundhogg/groundhogg.php')) {
            return $option === 'get_name' ? 'groundhogg/groundhogg.php' : true;
        }

        return false;
    }

    public static function handle_groundhogg_submit($a, $fieldValues)
    {
        global $wp_rest_server;
        $form_id = 1;
        $request = $wp_rest_server->get_raw_data();
        $data = json_decode($request);
        $meta = $data->meta;

        $fieldValues['primary_phone'] = $meta->primary_phone;
        $fieldValues['mobile_phone'] = $meta->mobile_phone;

        if (isset($data->tags)) {
            $fieldValues['tags'] = self::setTagNames($data->tags);
        }

        $flows = Flow::exists('Groundhogg', $form_id);
        if (!$flows) {
            return;
        }

        $data = $fieldValues;
        Flow::execute('Groundhogg', $form_id, $data, $flows);
    }

    public static function tagApplied($a, $b)
    {
        $data = $a['data'];
        $form_id = 2;
        $flows = Flow::exists('Groundhogg', $form_id);
        $getSelected = $flows[0]->flow_details;
        $enCode = json_decode($getSelected);

        if (isset($a['tags'])) {
            $data['tags'] = self::setTagNames($a['tags']);
        }
        if (!$flows) {
            return;
        }

        if ($enCode->selectedTag == $b || $enCode->selectedTag == 'any') {
            Flow::execute('Groundhogg', $form_id, $data, $flows);
        }
    }

    public static function tagRemove($a, $b)
    {
        $data = $a['data'];
        $form_id = 3;
        $flows = Flow::exists('Groundhogg', $form_id);
        $getSelected = $flows[0]->flow_details;
        $enCode = json_decode($getSelected);

        if (isset($a['tags'])) {
            $data['tags'] = self::setTagNames($a['tags']);
        }
        if (!$flows) {
            return;
        }

        if ($enCode->selectedTag == $b || $enCode->selectedTag == 'any') {
            Flow::execute('Groundhogg', $form_id, $data, $flows);
        }
    }

    public function getAll()
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Groundhogg'));
        }
        $types = [
            __('Contact-Create', 'bit-integrations-pro'),
            __('Add-Tag-To-Contact', 'bit-integrations-pro'),
            __('Remove-Tag-From-Contact', 'bit-integrations-pro')
        ];
        $groundhogg_action = [];
        foreach ($types as $index => $type) {
            $groundhogg_action[] = (object) [
                'id'    => $index + 1,
                'title' => $type,
            ];
        }

        wp_send_json_success($groundhogg_action);
    }

    public function getFormFields($data)
    {
        if (!self::pluginActive()) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'Groundhogg'));
        }
        if (empty($data->id)) {
            wp_send_json_error(__('Doesn\'t exists', 'bit-integrations-pro'));
        }
        $id = $data->id;
        if ($id == 2 || $id == 3) {
            $responseData['allTag'] = static::prepareTagsFormat();
        }

        $fields = self::fields($data->id);
        if (empty($fields)) {
            wp_send_json_error(__('Doesn\'t exists any field', 'bit-integrations-pro'));
        }

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

        $fields = [
            'First Name' => (object) [
                'fieldKey'  => 'first_name',
                'fieldName' => __('First Name', 'bit-integrations-pro')
            ],
            'Last Name' => (object) [
                'fieldKey'  => 'last_name',
                'fieldName' => __('Last Name', 'bit-integrations-pro')
            ],
            'Email' => (object) [
                'fieldKey'  => 'email',
                'fieldName' => __('Email', 'bit-integrations-pro')
            ],
            'Primary Phone' => (object) [
                'fieldKey'  => 'primary_phone',
                'fieldName' => __('Primary Phone', 'bit-integrations-pro')
            ],
            'Mobile Phone' => (object) [
                'fieldKey'  => 'mobile_phone',
                'fieldName' => __('Mobile Phone', 'bit-integrations-pro')
            ],
            'Owner Id' => (object) [
                'fieldKey'  => 'owner_id',
                'fieldName' => __('Owner Id', 'bit-integrations-pro')
            ],
            'Optin Status' => (object) [
                'fieldKey'  => 'optin_status',
                'fieldName' => __('Optin Status', 'bit-integrations-pro')
            ],
            'Tags' => (object) [
                'fieldKey'  => 'tags',
                'fieldName' => __('Tags', 'bit-integrations-pro')
            ],
        ];

        $fieldsNew = [];

        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field->fieldKey,
                'type'  => 'text',
                'label' => $field->fieldName,
            ];
        }

        return $fieldsNew;
    }

    public static function getAllFormsFromPostMeta($postMeta)
    {
        $forms = [];
        foreach ($postMeta as $widget) {
            foreach ($widget->elements as $elements) {
                foreach ($elements->elements as $element) {
                    if (isset($element->widgetType) && $element->widgetType == 'form') {
                        $forms[] = $element;
                    }
                }
            }
        }

        return $forms;
    }

    public static function getAllTags()
    {
        $allTag = static::prepareTagsFormat();

        wp_send_json_success($allTag);
    }

    protected static function setTagNames($tag_ids)
    {
        if (!class_exists('\Groundhogg\Tag')) {
            return;
        }

        $tag_list = [];

        foreach ($tag_ids as $tag_id) {
            $tag = new \Groundhogg\Tag($tag_id);
            $tag_list[] = $tag->get_name();
        }

        return implode(',', $tag_list);
    }

    private static function prepareTagsFormat()
    {
        $allTag = [[
            'tag_id'   => 'any',
            'tag_name' => __('Any Tag', 'bit-integrations-pro')
        ]];

        if (\function_exists('\Groundhogg\get_db')) {
            $tags = \Groundhogg\get_db('tags')->query(['limit' => 1000]);

            foreach ($tags as $val) {
                $allTag[] = [
                    'tag_id'   => $val->tag_id,
                    'tag_name' => $val->tag_name,
                ];
            }
        }

        return $allTag;
    }
}
