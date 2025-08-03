<?php

namespace BitApps\BTCBI_PRO\Triggers\WPForo;

use BitCode\FI\Flow\Flow;
use wpforo\classes\Topics;

final class WPForoController
{
    public static function info()
    {
        $plugin_path = 'wpforo/wpforo.php';

        return [
            'name'           => 'wpForo Forum',
            'title'          => __('wpForo Forum', 'bit-integrations-pro'),
            'slug'           => $plugin_path,
            'pro'            => 'wpforo/wpforo.php',
            'type'           => 'form',
            'is_active'      => is_plugin_active('wpforo/wpforo.php'),
            'activation_url' => wp_nonce_url(self_admin_url('plugins.php?action=activate&amp;plugin=' . $plugin_path . '&amp;plugin_status=all&amp;paged=1&amp;s'), 'activate-plugin_' . $plugin_path),
            'install_url'    => wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_path), 'install-plugin_' . $plugin_path),
            'list'           => [
                'action' => 'wpforo/get',
                'method' => 'get',
            ],
            'fields' => [
                'action' => 'wpforo/get/form',
                'method' => 'post',
                'data'   => ['id']
            ],
            'isPro' => true
        ];
    }

    public function getAll()
    {
        if (!is_plugin_active('wpforo/wpforo.php')) {
            wp_send_json_error(\sprintf(__('%s is not installed or activated', 'bit-integrations-pro'), 'wpForo'));
        }

        $types = [
            ['id' => 'wpforo-1', 'title' => __('New Topic Added', 'bit-integrations-pro'), 'note' => __('Runs after a user creates a new topic in a forum', 'bit-integrations-pro')],
            ['id' => 'wpforo-2', 'title' => __('New Reply Added', 'bit-integrations-pro'), 'note' => __('Runs after a user replies (or posts) to a topic in a forum', 'bit-integrations-pro')],
            ['id' => 'wpforo-3', 'title' => __('New Up Vote Added', 'bit-integrations-pro'), 'note' => __('Runs after a user votes up a post or a topic', 'bit-integrations-pro')],
            ['id' => 'wpforo-4', 'title' => __('New Down Vote Added', 'bit-integrations-pro'), 'note' => __('Runs after a user votes down a post or a topic', 'bit-integrations-pro')],
            ['id' => 'wpforo-5', 'title' => __('Like a Post', 'bit-integrations-pro'), 'note' => __('Runs after a user likes a post or a topic', 'bit-integrations-pro')],
            ['id' => 'wpforo-6', 'title' => __('Dislike a Post', 'bit-integrations-pro'), 'note' => __('Runs after a user dislikes a post or a topic', 'bit-integrations-pro')],
            ['id' => 'wpforo-7', 'title' => __('User (Author) Gets a Vote Up', 'bit-integrations-pro'), 'note' => __('Runs after a post author gets a vote up on a post', 'bit-integrations-pro')],
            ['id' => 'wpforo-8', 'title' => __('User (Author) Gets a Vote Down', 'bit-integrations-pro'), 'note' => __('Runs after a post author gets a vote down on a post', 'bit-integrations-pro')],
            ['id' => 'wpforo-9', 'title' => __('User (Author) Gets a Like', 'bit-integrations-pro'), 'note' => __('Runs after a post author gets a like on a post', 'bit-integrations-pro')],
            ['id' => 'wpforo-10', 'title' => __('User (Author) Gets a Dislike', 'bit-integrations-pro'), 'note' => __('Runs after a post author gets a dislike on a post', 'bit-integrations-pro')],
            ['id' => 'wpforo-11', 'title' => __('Answer a question', 'bit-integrations-pro'), 'note' => __('Runs after an user answers a question', 'bit-integrations-pro')],
            ['id' => 'wpforo-12', 'title' => __('Get a question answered', 'bit-integrations-pro'), 'note' => __('Runs after a question author gets a question answered', 'bit-integrations-pro')],
        ];

        $tasks = [];

        foreach ($types as $item) {
            $tasks[] = (object) [
                'id'    => $item['id'],
                'title' => $item['title'],
                'note'  => $item['note'],
            ];
        }

        wp_send_json_success($tasks);
    }

    public function get_a_form($data)
    {
        if (!is_plugin_active('wpforo/wpforo.php')) {
            wp_send_json_error(__('wpForo is not installed or activated', 'bit-integrations-pro'));
        }

        if (empty($data->id)) {
            wp_send_json_error(__('Task doesn\'t exists', 'bit-integrations-pro'));
        }

        if ($data->id === 'wpforo-1') {
            $forums = WPForoHelper::getAllForums();

            if (!empty($forums)) {
                $responseData['forums'] = $forums;
            }
        } elseif ($data->id === 'wpforo-2' || $data->id === 'wpforo-3' || $data->id === 'wpforo-4' || $data->id === 'wpforo-5' || $data->id === 'wpforo-6' || $data->id === 'wpforo-11') {
            $topics = WPForoHelper::getAllTopics();

            if (!empty($topics)) {
                $responseData['topics'] = $topics;
            }
        } elseif ($data->id === 'wpforo-7' || $data->id === 'wpforo-8' || $data->id === 'wpforo-9' || $data->id === 'wpforo-10' || $data->id === 'wpforo-12') {
            $users = WPForoHelper::getAllUsers();

            if (!empty($users)) {
                $responseData['users'] = $users;
            }
        }

        $responseData['fields'] = self::fields($data);

        wp_send_json_success($responseData);
    }

    public static function fields($data)
    {
        return WPForoHelper::getFields($data);
    }

    public static function handleWPForoTopicAdd($topic, $forum)
    {
        if (empty($topic) || empty($forum)) {
            return false;
        }

        $flows = Flow::exists('WPForo', 'wpforo-1');
        $flows = WPForoHelper::flowFilter($flows, 'selectedForum', $forum['forumid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $topicAddData = WPForoHelper::formatTopicAddData($topic, $forum);

        Flow::execute('WPForo', 'wpforo-1', $topicAddData, $flows);
    }

    public static function handleWPForoPostAdd($post, $topic, $forum)
    {
        if (empty($post) || empty($topic) || empty($forum)) {
            return false;
        }

        $flows = Flow::exists('WPForo', 'wpforo-2');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $topic['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $postAddData = WPForoHelper::formatPostAddData($post, $topic, $forum);

        Flow::execute('WPForo', 'wpforo-2', $postAddData, $flows);
    }

    public static function handleWPForoUpVote($reaction, $post, $userid)
    {
        if (empty($reaction) || empty($post) || empty($userid)) {
            return false;
        }

        if ($reaction === 1) {
            $voteType = 'up';
        } else {
            $voteType = 'down';
        }

        if ($voteType === 'down') {
            return;
        }

        $flows = Flow::exists('WPForo', 'wpforo-3');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $post['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $voteUpData = WPForoHelper::formatVoteData($post, $userid);

        Flow::execute('WPForo', 'wpforo-3', $voteUpData, $flows);
    }

    public static function handleWPForoDownVote($reaction, $post, $userid)
    {
        if (empty($reaction) || empty($post) || empty($userid)) {
            return false;
        }

        if ($reaction === 1) {
            $voteType = 'up';
        } else {
            $voteType = 'down';
        }

        if ($voteType === 'up') {
            return;
        }

        $flows = Flow::exists('WPForo', 'wpforo-4');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $post['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $voteUpData = WPForoHelper::formatVoteData($post, $userid);

        Flow::execute('WPForo', 'wpforo-4', $voteUpData, $flows);
    }

    public static function handleWPForoLike($reaction, $post)
    {
        if (empty($reaction) || empty($post)) {
            return false;
        }

        $userid = $reaction['userid'];
        $reactionType = $reaction['type'];

        if ($reactionType === 'down') {
            return;
        }

        $flows = Flow::exists('WPForo', 'wpforo-5');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $post['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $reactionData = WPForoHelper::formatReactionData($post, $userid);

        Flow::execute('WPForo', 'wpforo-5', $reactionData, $flows);
    }

    public static function handleWPForoDislike($reaction, $post)
    {
        if (empty($reaction) || empty($post)) {
            return false;
        }

        $userid = $reaction['userid'];
        $reactionType = $reaction['type'];

        if ($reactionType === 'up') {
            return;
        }

        $flows = Flow::exists('WPForo', 'wpforo-6');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $post['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $reactionData = WPForoHelper::formatReactionData($post, $userid);

        Flow::execute('WPForo', 'wpforo-6', $reactionData, $flows);
    }

    public static function handleWPForoGetsUpVote($reaction, $post, $voterId)
    {
        if (empty($reaction) || empty($post) || empty($voterId)) {
            return false;
        }

        if ($reaction === 1) {
            $voteType = 'up';
        } else {
            $voteType = 'down';
        }

        if ($voteType === 'down') {
            return;
        }

        $authorId = $post['userid'];

        $flows = Flow::exists('WPForo', 'wpforo-7');
        $flows = WPForoHelper::flowFilter($flows, 'selectedUser', $authorId);

        if (empty($flows) || !$flows) {
            return;
        }

        $voteGetsData = WPForoHelper::formatGetsVoteData($authorId, $post, $voterId);

        Flow::execute('WPForo', 'wpforo-7', $voteGetsData, $flows);
    }

    public static function handleWPForoGetsDownVote($reaction, $post, $voterId)
    {
        if (empty($reaction) || empty($post) || empty($voterId)) {
            return false;
        }

        if ($reaction === 1) {
            $voteType = 'up';
        } else {
            $voteType = 'down';
        }

        if ($voteType === 'up') {
            return;
        }

        $authorId = $post['userid'];

        $flows = Flow::exists('WPForo', 'wpforo-8');
        $flows = WPForoHelper::flowFilter($flows, 'selectedUser', $authorId);

        if (empty($flows) || !$flows) {
            return;
        }

        $voteGetsData = WPForoHelper::formatGetsVoteData($authorId, $post, $voterId);

        Flow::execute('WPForo', 'wpforo-8', $voteGetsData, $flows);
    }

    public static function handleWPForoGetsLike($reaction, $post)
    {
        if (empty($reaction) || empty($post)) {
            return false;
        }

        $reactionType = $reaction['type'];

        if ($reactionType === 'down') {
            return;
        }

        $authorId = $reaction['post_userid'];
        $likerData = $reaction['userid'];

        $flows = Flow::exists('WPForo', 'wpforo-9');
        $flows = WPForoHelper::flowFilter($flows, 'selectedUser', $authorId);

        if (empty($flows) || !$flows) {
            return;
        }

        $getsLikeData = WPForoHelper::formatGetsReactionData($post, $authorId, $likerData, 'liker');

        Flow::execute('WPForo', 'wpforo-9', $getsLikeData, $flows);
    }

    public static function handleWPForoGetsDislike($reaction, $post)
    {
        if (empty($reaction) || empty($post)) {
            return false;
        }

        $reactionType = $reaction['type'];

        if ($reactionType === 'up') {
            return;
        }

        $authorId = $reaction['post_userid'];
        $likerData = $reaction['userid'];

        $flows = Flow::exists('WPForo', 'wpforo-10');
        $flows = WPForoHelper::flowFilter($flows, 'selectedUser', $authorId);

        if (empty($flows) || !$flows) {
            return;
        }

        $getsLikeData = WPForoHelper::formatGetsReactionData($post, $authorId, $likerData, 'disliker');

        Flow::execute('WPForo', 'wpforo-10', $getsLikeData, $flows);
    }

    public static function handleWPForoAnswer($answerStatus, $post)
    {
        if (empty($post)) {
            return false;
        }

        $flows = Flow::exists('WPForo', 'wpforo-11');
        $flows = WPForoHelper::flowFilter($flows, 'selectedTopic', $post['topicid']);

        if (empty($flows) || !$flows) {
            return;
        }

        $answerData = WPForoHelper::formatAnswerData($answerStatus, $post);

        Flow::execute('WPForo', 'wpforo-11', $answerData, $flows);
    }

    public static function handleWPForoGetsAnswer($answerStatus, $post)
    {
        if (empty($post)) {
            return false;
        }

        $wpForoTopics = new Topics();

        $getTopic = $wpForoTopics->get_topic($post['topicid']);

        if (empty($getTopic)) {
            return false;
        }

        $questionAuthorId = $getTopic['userid'];

        $flows = Flow::exists('WPForo', 'wpforo-12');
        $flows = WPForoHelper::flowFilter($flows, 'selectedUser', $questionAuthorId);

        if (empty($flows) || !$flows) {
            return;
        }

        $getsAnswerData = WPForoHelper::formatAnswerGetsData($answerStatus, $post, $questionAuthorId);

        Flow::execute('WPForo', 'wpforo-12', $getsAnswerData, $flows);
    }
}
