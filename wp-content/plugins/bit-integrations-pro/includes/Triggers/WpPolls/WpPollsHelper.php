<?php

namespace BitApps\BTCBI_PRO\Triggers\WpPolls;

use BitCode\FI\Core\Util\User;
use BitCode\FI\Core\Util\Helper;

class WpPollsHelper
{
    public static function getPollIds()
    {
        $pollKey = isset($_POST['poll_id']) ? sanitize_key($_POST['poll_id']) : null;
        if (! $pollKey || ! isset($_POST['poll_' . $pollKey])) {
            return;
        }

        $poll_id = (int) $pollKey;

        $nonce_key = 'poll_' . $poll_id . '_nonce';
        if (!check_ajax_referer('poll_' . $poll_id . '-nonce', $nonce_key, false)) {
            return;
        }

        return [
            'selected_answers_ids' => sanitize_text_field($_POST['poll_' . $poll_id]),
            'poll_id'              => $poll_id
        ];
    }

    public static function formatPollData($selected_answers_ids, $poll_id)
    {
        global $wpdb;
        $query = $wpdb->prepare("
			SELECT
				p.pollq_question AS question,
				GROUP_CONCAT(a.polla_answers SEPARATOR ', ') AS answers,
				p.pollq_timestamp AS start_date,
				p.pollq_expiry AS end_date,
				sa.selected_answers
			FROM {$wpdb->prefix}pollsq p
			LEFT JOIN {$wpdb->prefix}pollsa a ON p.pollq_id = a.polla_qid
			LEFT JOIN (
				SELECT 
					polla_qid, 
					GROUP_CONCAT(polla_answers SEPARATOR ', ') AS selected_answers
				FROM {$wpdb->prefix}pollsa
				WHERE FIND_IN_SET(polla_aid, %s)
				GROUP BY polla_qid
			) AS sa ON p.pollq_id = sa.polla_qid
			WHERE p.pollq_id = %d
			GROUP BY p.pollq_question, p.pollq_timestamp, p.pollq_expiry
		", $selected_answers_ids, $poll_id);

        $poll_data = $wpdb->get_row($query);
        if (empty($poll_data)) {
            return;
        }

        return Helper::prepareFetchFormatFields(array_merge([
            'poll_id'    => $poll_id,
            'question'   => $poll_data->question,
            'answers'    => $poll_data->answers,
            'start_date' => gmdate('Y-m-d H:i:s', $poll_data->start_date),
            'end_date'   => (int) $poll_data->end_date === 0
                                    ? 'Not set'
                                    : gmdate('Y-m-d H:i:s', $poll_data->end_date),
            'selected_answers' => $poll_data->selected_answers
        ], User::currentUser()));
    }

    public static function isPluginInstalled()
    {
        return \defined('WP_POLLS_VERSION');
    }
}
