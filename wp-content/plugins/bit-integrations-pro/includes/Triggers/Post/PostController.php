<?php

namespace BitApps\BTCBI_PRO\Triggers\Post;

use BitCode\FI\Core\Util\Post;
use BitCode\FI\Flow\Flow;

final class PostController
{
    private const CREATE_POST = 1;

    private const UPDATE_POST = 2;

    private const DELETE_POST = 3;

    private const USER_VIEWS_POST = 4;

    private const COMMENT_ON_POST = 5;

    private const CHANGE_POST_STATUS = 6;

    private const COMMENT_DELETE_ON_POST = 7;

    private const COMMENT_UPDATE_ON_POST = 8;

    private const POST_TRASHED = 9;

    public static function info()
    {
        return [
            'name'      => 'WP Post',
            'title'     => __('Wp Post', 'bit-integrations-pro'),
            'type'      => 'form',
            'trigger'   => 'Post',
            'is_active' => true,
            'list'      => [
                'action' => 'post/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'post/get/form',
                'method' => 'post',
                'data'   => ['id'],
            ],
            'isPro' => true
        ];
    }

    public static function fields($id)
    {
        if (\in_array($id, [static::CREATE_POST, static::UPDATE_POST, static::DELETE_POST, static::USER_VIEWS_POST, static::CHANGE_POST_STATUS, static::POST_TRASHED])) {
            $fields = PostHelper::postFields();
        }

        if (\in_array($id, [static::COMMENT_ON_POST, static::COMMENT_DELETE_ON_POST, static::COMMENT_UPDATE_ON_POST])) {
            $fields = PostHelper::commentFields();
        }

        return $fields;
    }

    public function getAll()
    {
        $triggers = [
            ['id' => static::CREATE_POST, 'title' => __('Create a new post', 'bit-integrations-pro')],
            ['id' => static::UPDATE_POST, 'title' => __('Updated a post', 'bit-integrations-pro')],
            ['id' => static::DELETE_POST, 'title' => __('Delete a post', 'bit-integrations-pro')],
            ['id' => static::USER_VIEWS_POST, 'title' => __('User views a post', 'bit-integrations-pro')],
            ['id' => static::COMMENT_ON_POST, 'title' => __('User comments on a post', 'bit-integrations-pro')],
            ['id' => static::CHANGE_POST_STATUS, 'title' => __('Change post status', 'bit-integrations-pro')],
            ['id' => static::COMMENT_DELETE_ON_POST, 'title' => __('Comment deleted on a post', 'bit-integrations-pro')],
            ['id' => static::COMMENT_UPDATE_ON_POST, 'title' => __('Comment updated on a post', 'bit-integrations-pro')],
            ['id' => static::POST_TRASHED, 'title' => __('Post trashed', 'bit-integrations-pro')]
        ];

        wp_send_json_success($triggers);
    }

    public function get_a_form($data)
    {
        $responseData = [];
        $missing_field = null;

        if (!property_exists($data, 'id')) {
            $missing_field = 'Form ID';
        }

        if (!\is_null($missing_field)) {
            wp_send_json_error(\sprintf(__('%s can\'t be empty', 'bit-integrations-pro'), $missing_field));
        }

        if (\in_array(
            $data->id,
            [
                static::CREATE_POST,
                static::UPDATE_POST,
                static::DELETE_POST,
                static::CHANGE_POST_STATUS
            ]
        )) {
            $responseData['types'] = array_values(PostHelper::getPostTypes());
            array_unshift($responseData['types'], ['id' => 'any-post-type', 'title' => __('Any Post Type', 'bit-integrations-pro')]);
        }

        if (\in_array(
            $data->id,
            [
                static::USER_VIEWS_POST,
                static::COMMENT_ON_POST,
                static::COMMENT_DELETE_ON_POST,
                static::COMMENT_UPDATE_ON_POST,
                static::POST_TRASHED
            ]
        )) {
            $responseData['posts'] = PostHelper::getPostTitles();
            array_unshift($responseData['posts'], ['id' => 'any-post', 'title' => __('Any Post', 'bit-integrations-pro')]);
        }

        $responseData['fields'] = self::fields($data->id);

        if (\count($responseData['fields']) <= 0) {
            wp_send_json_error(__('Form fields doesn\'t exists', 'bit-integrations-pro'));
        }

        wp_send_json_success($responseData);
    }

    public static function createPost($postId, $newPostData, $update, $beforePostData)
    {
        if ('publish' !== $newPostData->post_status || 'revision' === $newPostData->post_type || (!empty($beforePostData->post_status) && 'publish' === $beforePostData->post_status)) {
            return false;
        }

        $postCreateFlow = Flow::exists('Post', static::CREATE_POST);

        if ($postCreateFlow) {
            $flowDetails = $postCreateFlow[0]->flow_details;

            if (\is_string($postCreateFlow[0]->flow_details)) {
                $flowDetails = json_decode($postCreateFlow[0]->flow_details);
            }

            if (isset($newPostData->post_content)) {
                $newPostData->post_content = trim(strip_tags($newPostData->post_content));
                $newPostData->post_permalink = get_permalink($newPostData);
            }

            if (isset($flowDetails->selectedPostType) && ($flowDetails->selectedPostType == 'any' || $flowDetails->selectedPostType == 'any-post-type' || $flowDetails->selectedPostType == $newPostData->post_type)) {
                if (has_post_thumbnail($postId)) {
                    $featured_image_url = get_the_post_thumbnail_url($postId, 'full');
                    $newPostData->featured_image = $featured_image_url;
                }

                $newPostData->post_categories = Post::getCategories($postId);

                Flow::execute('Post', static::CREATE_POST, (array) $newPostData, $postCreateFlow);
            }
        }
    }

    public static function postComment($cmntId, $status, $cmntData)
    {
        $cmntTrigger = Flow::exists('Post', static::COMMENT_ON_POST);

        if ($cmntTrigger) {
            $flowDetails = $cmntTrigger[0]->flow_details;

            if (\is_string($cmntTrigger[0]->flow_details)) {
                $flowDetails = json_decode($cmntTrigger[0]->flow_details);
            }

            if (isset($flowDetails->selectedPostId) && $flowDetails->selectedPostId == 'any-post' || $flowDetails->selectedPostId == $cmntData['comment_post_ID']) {
                $cmntData['comment_id'] = $cmntId;

                Flow::execute('Post', static::COMMENT_ON_POST, (array) $cmntData, $cmntTrigger);
            }
        }
    }

    public static function deletePost($postId, $deletedPost)
    {
        $postDeleteTrigger = Flow::exists('Post', static::DELETE_POST);

        if ($postDeleteTrigger) {
            $flowDetails = $postDeleteTrigger[0]->flow_details;

            if (\is_string($postDeleteTrigger[0]->flow_details)) {
                $flowDetails = json_decode($postDeleteTrigger[0]->flow_details);
            }

            if (isset($deletedPost->post_content)) {
                $deletedPost->post_content = trim(strip_tags($deletedPost->post_content));
                $deletedPost->post_permalink = get_permalink($deletedPost);
            }

            if (isset($flowDetails->selectedPostType) && $flowDetails->selectedPostType == 'any-post-type' || $flowDetails->selectedPostType == $deletedPost->post_type) {
                $deletedPost->post_categories = Post::getCategories($postId);

                Flow::execute('Post', static::DELETE_POST, (array) $deletedPost, $postDeleteTrigger);
            }
        }
    }

    public static function viewPost($content)
    {
        $postViewTrigger = Flow::exists('Post', static::USER_VIEWS_POST);

        if (is_single() && !empty($GLOBALS['post'])) {
            if (isset($postViewTrigger[0]->selectedPostId) && $postViewTrigger[0]->selectedPostId == 'any-post' || $GLOBALS['post']->ID == get_the_ID()) {
                Flow::execute('Post', static::USER_VIEWS_POST, (array) $GLOBALS['post'], $postViewTrigger);
            }
        }

        return $content;
    }

    public static function postUpdated($postId, $updatedPostData)
    {
        $postUpdateFlow = Flow::exists('Post', static::UPDATE_POST);
        if (empty($postUpdateFlow)) {
            return;
        }

        $flowDetails = \is_string($postUpdateFlow[0]->flow_details) ? json_decode($postUpdateFlow[0]->flow_details) : $postUpdateFlow[0]->flow_details;
        $transientKey = "btcbi_post_updated_{$flowDetails->selectedPostType}";

        if (get_transient($transientKey)) {
            return;
        }

        if (isset($updatedPostData->post_content)) {
            $updatedPostData->post_content = trim(strip_tags($updatedPostData->post_content));
            $updatedPostData->post_permalink = get_permalink($updatedPostData);
        }

        if (has_post_thumbnail($postId)) {
            $updatedPostData->featured_image = get_the_post_thumbnail_url($postId, 'full');
        }

        if (isset($flowDetails->selectedPostType) && ($flowDetails->selectedPostType == 'any' || $flowDetails->selectedPostType == 'any-post-type' || $flowDetails->selectedPostType == $updatedPostData->post_type)) {
            $updatedPostData->post_categories = Post::getCategories($postId);
            set_transient($transientKey, true, 5);
            Flow::execute('Post', static::UPDATE_POST, (array) $updatedPostData, $postUpdateFlow);
        }
    }

    public static function changePostStatus($newStatus, $oldStatus, $post)
    {
        if ($newStatus === $oldStatus || 'post' !== $post->post_type) {
            return;
        }

        $statusChangeTrigger = Flow::exists('Post', static::CHANGE_POST_STATUS);

        if ($statusChangeTrigger) {
            $flowDetails = $statusChangeTrigger[0]->flow_details;

            if (\is_string($statusChangeTrigger[0]->flow_details)) {
                $flowDetails = json_decode($statusChangeTrigger[0]->flow_details);
            }

            if (isset($post->post_content)) {
                $post->post_content = trim(strip_tags($post->post_content));
                $post->post_permalink = get_permalink($post);
            }
            if (has_post_thumbnail($post->ID)) {
                $post->featured_image = get_the_post_thumbnail_url($post->ID, 'full');
            }

            if (isset($flowDetails->selectedPostType) && $flowDetails->selectedPostType == 'any-post-type' || $flowDetails->selectedPostType == $post->post_type && $newStatus != $oldStatus) {
                $post->post_categories = Post::getCategories($post->ID);

                Flow::execute('Post', static::CHANGE_POST_STATUS, (array) $post, $statusChangeTrigger);
            }
        }
    }

    public static function trashComment($cmntId, $cmntData)
    {
        $cmntTrigger = Flow::exists('Post', static::COMMENT_DELETE_ON_POST);
        if ($cmntTrigger) {
            $flowDetails = $cmntTrigger[0]->flow_details;

            if (\is_string($cmntTrigger[0]->flow_details)) {
                $flowDetails = json_decode($cmntTrigger[0]->flow_details);
            }

            $cmntData = (array) $cmntData;
            if (isset($flowDetails->selectedPostId) && $flowDetails->selectedPostId == 'any-post' || $flowDetails->selectedPostId == $cmntData['comment_post_ID']) {
                $cmntData['comment_id'] = $cmntId;
                Flow::execute('Post', static::COMMENT_DELETE_ON_POST, (array) $cmntData, $cmntTrigger);
            }
        }
    }

    public static function updateComment($cmntId, $cmntData)
    {
        $cmntTrigger = Flow::exists('Post', static::COMMENT_UPDATE_ON_POST);
        if ($cmntTrigger) {
            $flowDetails = $cmntTrigger[0]->flow_details;

            if (\is_string($cmntTrigger[0]->flow_details)) {
                $flowDetails = json_decode($cmntTrigger[0]->flow_details);
            }

            $cmntData = (array) $cmntData;
            if (isset($flowDetails->selectedPostId) && $flowDetails->selectedPostId == 'any-post' || $flowDetails->selectedPostId == $cmntData['comment_post_ID']) {
                $cmntData['comment_id'] = $cmntId;
                Flow::execute('Post', static::COMMENT_UPDATE_ON_POST, (array) $cmntData, $cmntTrigger);
            }
        }
    }

    public static function trashPost($trashPostId)
    {
        $postUpdateFlow = Flow::exists('Post', static::POST_TRASHED);
        $postData = get_post($trashPostId);
        $postData->post_permalink = get_permalink($postData);

        if ($postUpdateFlow) {
            $flowDetails = $postUpdateFlow[0]->flow_details;

            if (\is_string($postUpdateFlow[0]->flow_details)) {
                $flowDetails = json_decode($postUpdateFlow[0]->flow_details);
            }
            $postData = (array) $postData;
            if (isset($flowDetails->selectedPostType) && $flowDetails->selectedPostType == 'any-post-type' || $flowDetails->selectedPostType == $postData['ID']) {
                $postData['post_categories'] = Post::getCategories($postData['ID']);

                Flow::execute('Post', static::POST_TRASHED, (array) $postData, $postUpdateFlow);
            }
        }
    }

    public static function getAllPostTypes()
    {
        $types = array_values(PostHelper::getPostTypes());
        array_unshift($types, ['id' => 'any-post-type', 'title' => __('Any Post Type', 'bit-integrations-pro')]);
        wp_send_json_success($types);
    }

    public static function getAllPosts()
    {
        $posts = PostHelper::getPostTitles();
        array_unshift($posts, ['id' => 'any-post', 'title' => __('Any Post', 'bit-integrations-pro')]);
        wp_send_json_success($posts);
    }
}
