<?php

namespace BitApps\BTCBI_PRO\Triggers\SureMembers;

class SureMembersHelper
{
    public static function groupUpdatedFields()
    {
        return [
            [
                'name'  => 'suremembers_group_rules',
                'type'  => 'text',
                'label' => __('Group Rules Rules', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'suremembers_user_roles',
                'type'  => 'text',
                'label' => __('Group Suremembers User Roles', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'suremembers_redirect_url',
                'type'  => 'text',
                'label' => __('Group Restrict Redirect URL', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'suremembers_unauthorized_action',
                'type'  => 'text',
                'label' => __('Group Restrict Unauthorized Action', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'suremembers_preview_content',
                'type'  => 'text',
                'label' => __('Group Restrict Preview Content', 'bit-integrations-pro'),
            ]
        ];
    }

    public static function wpUserFileds()
    {
        return [
            [
                'name'  => 'wp_user_id',
                'type'  => 'text',
                'label' => __('WP User ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'user_login',
                'type'  => 'text',
                'label' => __('User Login', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'display_name',
                'type'  => 'text',
                'label' => __('Display Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'user_firstname',
                'type'  => 'text',
                'label' => __('User First Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'user_lastname',
                'type'  => 'text',
                'label' => __('User Lastname', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'user_email',
                'type'  => 'text',
                'label' => __('User Email', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'user_role',
                'type'  => 'text',
                'label' => __('User Role', 'bit-integrations-pro'),
            ]
        ];
    }

    public static function accessGroupFields()
    {
        return [
            [
                'name'  => 'ID',
                'type'  => 'text',
                'label' => __('Group ID', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_author',
                'type'  => 'text',
                'label' => __('Group Post Author', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_date',
                'type'  => 'text',
                'label' => __('Group Post Date', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_date_gmt',
                'type'  => 'text',
                'label' => __('Group Post Date GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_content',
                'type'  => 'text',
                'label' => __('Group Post Content', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_title',
                'type'  => 'text',
                'label' => __('Group Post Title', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_excerpt',
                'type'  => 'text',
                'label' => __('Group Post Excerpt', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_status',
                'type'  => 'text',
                'label' => __('Group Post Status', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'comment_status',
                'type'  => 'text',
                'label' => __('Group Comment Status', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'ping_status',
                'type'  => 'text',
                'label' => __('Group Ping Status', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_password',
                'type'  => 'text',
                'label' => __('Group Post Password', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_name',
                'type'  => 'text',
                'label' => __('Group Post Name', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'to_ping',
                'type'  => 'text',
                'label' => __('Group To Ping', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'pinged',
                'type'  => 'text',
                'label' => __('Group Pinged', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_modified',
                'type'  => 'text',
                'label' => __('Group Post Modified', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_modified_gmt',
                'type'  => 'text',
                'label' => __('Group Post Modified GMT', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_content_filtered',
                'type'  => 'text',
                'label' => __('Group Post Content Filtered', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_parent',
                'type'  => 'text',
                'label' => __('Group Post Parent', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'guid',
                'type'  => 'text',
                'label' => __('Group Guid', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'menu_order',
                'type'  => 'text',
                'label' => __('Group Menu Order', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_type',
                'type'  => 'text',
                'label' => __('Group Post Type', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'post_mime_type',
                'type'  => 'text',
                'label' => __('Group Post Mime Type', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'comment_count',
                'type'  => 'text',
                'label' => __('Group Comment Count', 'bit-integrations-pro'),
            ],
            [
                'name'  => 'filter',
                'type'  => 'text',
                'label' => __('Group Filter', 'bit-integrations-pro'),
            ]
        ];
    }

    public static function sureMembersGrantOrRevoke($userId, $accessGroupId)
    {
        $groupData = (array) get_post(\intval($accessGroupId));

        if (empty($groupData)) {
            return false;
        }

        $userData = get_userdata(\intval($userId));

        if (empty($userData) || !isset($userData->ID)) {
            return false;
        }

        $formattedUserData = [
            'wp_user_id'     => $userData->ID,
            'user_login'     => $userData->user_login,
            'display_name'   => $userData->display_name,
            'user_firstname' => $userData->user_firstname,
            'user_lastname'  => $userData->user_lastname,
            'user_email'     => $userData->user_email,
            'user_role'      => \is_array($userData->roles)
                                    ? implode(',', $userData->roles) : $userData->roles,
        ];

        return array_merge($groupData, $formattedUserData);
    }

    public static function sureMembersGroupUpdated($suremembersPostId)
    {
        $groupData = (array) get_post(\intval($suremembersPostId));

        if (empty($groupData)) {
            return false;
        }

        $sanitizedPost = sanitize_post($_POST);
        $sanitizedPostData = [];

        if (isset($sanitizedPost['suremembers_post'])) {
            $suremembersPost = $sanitizedPost['suremembers_post'];
            $sanitizedPostData['suremembers_group_rules'] = implode(',', $suremembersPost['rules']);
            $sanitizedPostData['suremembers_user_roles'] = implode(',', $suremembersPost['suremembers_user_roles']);
            $suremembersRedirectUrl = wp_parse_url($suremembersPost['restrict']['redirect_url']);
            $redirectURLPath = trim($suremembersRedirectUrl['path'], '/');
            $sanitizedPostData['suremembers_redirect_url'] = $redirectURLPath;
            $sanitizedPostData['suremembers_unauthorized_action'] = $suremembersPost['restrict']['unauthorized_action'];
            $sanitizedPostData['suremembers_preview_content'] = $suremembersPost['restrict']['preview_content'];
        }

        return array_merge($groupData, $sanitizedPostData);
    }

    public static function flowFilter($flows, $key, $value)
    {
        $filteredFlows = [];
        if (\is_array($flows) || \is_object($flows)) {
            foreach ($flows as $flow) {
                if (\is_string($flow->flow_details)) {
                    $flow->flow_details = json_decode($flow->flow_details);
                }
                if (!isset($flow->flow_details->{$key}) || $flow->flow_details->{$key} === 'any' || $flow->flow_details->{$key} == $value || $flow->flow_details->{$key} === '') {
                    $filteredFlows[] = $flow;
                }
            }
        }

        return $filteredFlows;
    }
}
