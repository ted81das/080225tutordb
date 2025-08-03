<?php

namespace BitApps\BTCBI_PRO\Triggers\JetEngine;

use WP_Query;

final class JetEngineHelper
{
    public static function commentFields()
    {
        return [
            [
                'name'  => 'comment_id',
                'type'  => 'text',
                'label' => __('Comment ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_post_ID',
                'type'  => 'text',
                'label' => __('Comment Post ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'user_id',
                'type'  => 'text',
                'label' => __('Comment Author ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_author',
                'type'  => 'text',
                'label' => __('Comment Author Name', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_author_email',
                'type'  => 'text',
                'label' => __('Comment Author Email', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_author_IP',
                'type'  => 'text',
                'label' => __('Comment Author IP', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_agent',
                'type'  => 'text',
                'label' => __('Comment Author Agent', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_author_url',
                'type'  => 'text',
                'label' => __('Comment Author URL', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_content',
                'type'  => 'text',
                'label' => __('Comment Content', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_type',
                'type'  => 'text',
                'label' => __('Comment Type', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_parent',
                'type'  => 'text',
                'label' => __('Comment Parent ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_date',
                'type'  => 'text',
                'label' => __('Comment Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_date_gmt',
                'type'  => 'text',
                'label' => __('Comment Date Time', 'bit-integrations-pro')
            ],

        ];
    }

    public static function postFields()
    {
        return [
            [
                'name'  => 'ID',
                'type'  => 'text',
                'label' => __('Post Id', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_title',
                'type'  => 'text',
                'label' => __('Post Title', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_content',
                'type'  => 'text',
                'label' => __('Post Content', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_excerpt',
                'type'  => 'text',
                'label' => __('Post Excerpt', 'bit-integrations-pro')
            ],
            [
                'name'  => 'guid',
                'type'  => 'text',
                'label' => __('Post URL', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_type',
                'type'  => 'text',
                'label' => __('Post Type', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_author',
                'type'  => 'text',
                'label' => __('Post Author ID', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_status',
                'type'  => 'text',
                'label' => __('Post Comment Status', 'bit-integrations-pro')
            ],
            [
                'name'  => 'comment_count',
                'type'  => 'text',
                'label' => __('Post Comment Count', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_status',
                'type'  => 'text',
                'label' => __('Post Status', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_date',
                'type'  => 'text',
                'label' => __('Post Created Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'post_modified',
                'type'  => 'text',
                'label' => __('Post Modified Date', 'bit-integrations-pro')
            ],
            [
                'name'  => 'meta_key',
                'type'  => 'text',
                'label' => __('Meta Key', 'bit-integrations-pro')
            ],
            [
                'name'  => 'meta_value',
                'type'  => 'text',
                'label' => __('Meta Value', 'bit-integrations-pro')
            ]
        ];
    }

    public static function getPostTypes()
    {
        $cptArguments = [
            'public'          => true,
            'capability_type' => 'post',
        ];

        $types = get_post_types($cptArguments, 'object');

        $lists = [];

        foreach ($types as $key => $type) {
            $lists[$key]['id'] = $type->name;
            $lists[$key]['title'] = $type->label;
        }

        return $lists;
    }

    public static function getPostTitles()
    {
        $query = new WP_Query([
            'post_type' => 'post',
            'nopaging'  => true,
        ]);

        $posts = $query->get_posts();

        $postTitles = [];

        foreach ($posts as $key => $post) {
            $postTitles[$key]['id'] = $post->ID;
            $postTitles[$key]['title'] = $post->post_title;
        }

        return $postTitles;
    }
}
