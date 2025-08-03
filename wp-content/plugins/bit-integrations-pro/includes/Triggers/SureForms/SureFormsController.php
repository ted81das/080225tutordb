<?php

namespace BitApps\BTCBI_PRO\Triggers\SureForms;

use BitCode\FI\Flow\Flow;

final class SureFormsController
{
    public static function info()
    {
        $plugin_path = 'sureforms/sureforms.php';

        return [
            'name'           => 'SureForms',
            'title'          => __('SureForms', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'sureforms/sureforms.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('sureforms/sureforms.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'sureforms/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'sureforms/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('sureforms/sureforms.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureForms'));
        }

        $all_forms = [];

        $sureForms = get_posts(
            [
                'posts_per_page' => - 1,
                'orderby'        => 'name',
                'order'          => 'asc',
                'post_type'      => 'sureforms_form',
                'post_status'    => 'publish',
            ]
        );

        if (!empty($sureForms)) {
            foreach ($sureForms as $item) {
                $all_forms[] = (object) ['id' => (string) $item->ID, 'title' => $item->post_title];
            }
        }

        wp_send_json_success($all_forms);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('sureforms/sureforms.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'SureForms'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bit-integrations-pro'));
        }

        $fields = self::fields($data->id);

        if (empty($fields)) {
            wp_send_json_error(__('Fields fetching failed!', 'bit-integrations-pro'), 400);
        }

        $responseData['fields'] = $fields;

        wp_send_json_success($responseData);
    }

    public static function fields($form_id)
    {
        $fields = [];

        $content = get_post_field('post_content', $form_id);

        preg_match_all('/<!-- wp:srfm\/([a-z\-]+) \{(.*?)\} \/-->/', $content, $matches, PREG_SET_ORDER);

        if (!empty($matches)) {
            foreach ($matches as $item) {
                $attributes_json = '{' . $item[2] . '}';
                $attributes = json_decode($attributes_json, true);

                if (\is_array($attributes) && isset($attributes['slug'])) {
                    $fields[] = [
                        'name'  => $attributes['slug'],
                        'type'  => $item[1] ? $item['1'] : 'text',
                        'label' => isset($attributes['label']) ? $attributes['label']
                        : ucwords(str_replace(['srfm-', '-'], ['', ' '], $attributes['slug']))
                    ];
                }
            }
        }

        return $fields;
    }

    public static function handleSureFormsSubmit($formSubmitResponse)
    {
        $formId = $formSubmitResponse['form_id'];
        $formData = $formSubmitResponse['data'];

        if (empty($formId) || empty($formData)) {
            return;
        }

        if ($flows = Flow::exists('SureForms', $formId)) {
            Flow::execute('SureForms', $formId, $formData, $flows);
        }
    }
}
