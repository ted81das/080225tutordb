<?php
/**
 * FlowMattic Webhook Background Process
 *
 * @package FlowMattic
 * @since 3.0
 */

/**
 * FlowMattic Webhook Background Process class.
 *
 * @extends FlowMattic_Background_Process
 */
class FlowMattic_Webhook_Background_Process extends FlowMattic_Background_Process {
	/**
	 * Cron action handle.
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'flowmattic_background_process';

	/**
	 * Continue executing workflow with queued webhooks.
	 *
	 * @param array $webhook_data Queue webhook data with saved task to iterate over.
	 *
	 * @return bool
	 */
	protected function task( $webhook_data ) {
		// Process the webhook queue.
		wp_flowmattic()->workflow->process_webhook_queue( $webhook_data[0], $webhook_data[1], $webhook_data[2] );

		// Return false to remove the webhook from queue.
		return false;
	}

	/**
	 * Complete processing queue.
	 */
	protected function complete() {
		parent::complete();
	}
}
