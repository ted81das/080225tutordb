<?php

namespace BitApps\BTCBI_PRO\Triggers\UltimateMember;

class UltimateMemberHelper
{
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

        $userFields = [
            'User ID' => [
                'name'  => 'user_id',
                'label' => __('User Id', 'bit-integrations-pro')
            ],
            'First Name' => [
                'name'  => 'first_name',
                'label' => __('First Name', 'bit-integrations-pro')
            ],
            'Last Name' => [
                'name'  => 'last_name',
                'label' => __('Last Name', 'bit-integrations-pro')
            ],
            'Nick Name' => [
                'name'  => 'nickname',
                'label' => __('Nick Name', 'bit-integrations-pro')
            ],
            'Avatar URL' => [
                'name'  => 'avatar_url',
                'label' => __('Avatar URL', 'bit-integrations-pro')
            ],
            'Email' => [
                'name'  => 'user_email',
                'label' => __('Email', 'bit-integrations-pro'),
            ],
        ];

        if ($id == 'roleChange' || $id == 'roleSpecificChange') {
            $fields = $userFields;
            (array) $fields['Role'] = [
                'name'  => 'role',
                'label' => __('Role', 'bit-integrations-pro')
            ];
        } else {
            $form_id = absint($id);
            if (\function_exists('UM')) {
                $um_fields = UM()->query()->get_attr('custom_fields', $form_id);
                $formType = UM()->query()->get_attr('mode', $form_id);

                if ($um_fields) {
                    $fields = [];
                    foreach ($um_fields as $field) {
                        if (isset($field['public']) && 1 === absint($field['public'])) {
                            $input_id = $field['metakey'];
                            $input_title = $field['title'];
                            $token_id = "{$input_id}";
                            $input_type = $field['type'];
                            if ($token_id !== 'user_password') {
                                $fields[] = [
                                    'name'  => $token_id,
                                    'label' => $input_title,
                                    'type'  => $input_type,
                                ];
                            }
                        }
                    }
                    if ($formType == 'login') {
                        $fields = array_merge($fields, $userFields);
                    }
                }
            }
        }
        foreach ($fields as $field) {
            $fieldsNew[] = [
                'name'  => $field['name'],
                'type'  => \array_key_exists('type', $field) ? $field['type'] : 'text',
                'label' => $field['label'],
            ];
        }

        return $fieldsNew;
    }

    public static function getAllLoginAndRegistrationForm($formType)
    {
        $args = [
            'posts_per_page'   => 999,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'post_type'        => 'um_form',
            'post_status'      => 'publish',
            'suppress_filters' => true,
            'fields'           => ['ids', 'titles'],
            'meta_query'       => [
                [
                    'key'     => '_um_mode',
                    'value'   => $formType,
                    'compare' => 'LIKE',
                ],
            ],
        ];

        $forms_list = get_posts($args);
        $formName = ucfirst($formType);
        foreach ($forms_list as $form) {
            $allForms[] = [
                'id'    => "{$form->ID}",
                'title' => "{$formName} via {$form->post_title}",
            ];
        }

        return $allForms;
    }

    public static function getRoles()
    {
        $roles = [];
        foreach (wp_roles()->roles as $role_name => $role_info) {
            $roles[] = [
                'name'  => $role_name,
                'label' => $role_info['name'],
            ];
        }

        return $roles;
    }

    public static function getUserInfo($user_id)
    {
        $userInfo = get_userdata($user_id);
        $user = [];
        if ($userInfo) {
            $userData = $userInfo->data;
            $user_meta = get_user_meta($user_id);
            $user = [
                'user_id'    => $user_id,
                'first_name' => $user_meta['first_name'][0],
                'last_name'  => $user_meta['last_name'][0],
                'user_email' => $userData->user_email,
                'nickname'   => $userData->user_nicename,
                'avatar_url' => get_avatar_url($user_id),
            ];
        }

        return $user;
    }

    public static function setUploadFieldData($data, $form_id, $user_id)
    {
        $fields = UltimateMemberHelper::fields($form_id);
        $userMeta = get_user_meta($user_id);
        $uploadDir = wp_upload_dir()['baseurl'] . "/ultimatemember/{$user_id}/";

        foreach ($fields as $field) {
            if (($field['type'] === 'file' || $field['type'] === 'image') && isset($data[$field['name']])) {
                $attachment = str_replace(['file_', 'stream_photo_'], '', $data[$field['name']]);
                $attachment = explode('.', $attachment)[0] ?? $attachment;

                foreach ($userMeta[$field['name']] as $value) {
                    if (strpos($value, $attachment) !== false) {
                        $data[$field['name']] = $uploadDir . $value;

                        break;
                    }
                }
            }
        }

        return $data;
    }
}
