<?php

namespace Groundhogg\Cli;

use Groundhogg\Event;
use function Groundhogg\_nf;
use function Groundhogg\db;
use function Groundhogg\event_queue;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Manipulate the event queue
 *
 * ## EXAMPLES
 *
 *     # Process the event queue.
 *     $ wp groundhogg-queue process
 *     Success: 1234 events completed. 5 failed.
 *
 *     # Pause pending events.
 *     $ wp groundhogg-queue pause
 *     Success: 1234 events paused.
 *
 *     # Resume paused events.
 *     $ wp groundhogg-queue resume
 *     Success: 1234 events resumed.
 *
 *     # Cancel waiting/paused events.
 *     $ wp groundhogg-queue cancel
 *     Success: 1234 events canceled.
 */
class Queue {

	/**
	 * Process all pending events in the event queue
	 */
	function process(){

		$pending = db()->event_queue->count([
			'status' => Event::WAITING
		]);

		if ( ! $pending ){
			\WP_CLI::success( 'The event queue is empty!' );
			return;
		}

		$spinner = new \cli\notify\Spinner( sprintf( 'Processing ~%s events. This might take a while...' , _nf( $pending ) ) );

		add_action( 'groundhogg/event/run/after', [ $spinner, 'tick' ] );

		$completed = event_queue()->run_queue();
		$failed    = count( event_queue()->get_errors() );
		$spinner->finish();

		\WP_CLI::success( sprintf( '%s events completed. %s failed.', _nf( $completed ), _nf( $failed ) ) );
	}

	/**
	 * Pauses pending events
	 *
	 * ## OPTIONS
	 *
	 * [--funnel=<funnel>]
	 * : Specify by funnel ID
	 *
	 * [--broadcast=<broadcast>]
	 * : Specify by broadcast ID
	 *
	 * [--contact=<contact>]
	 * : Specify by contact ID
	 *
	 * ## EXAMPLES
	 *
	 *     wp groundhogg-queue pause --funnel=1
	 */
	function pause( $args, $assoc_args ) {
		// todo implement
		\WP_CLI::error( 'This method has not yet been implemented.' );
	}

	/**
	 * Unpause paused events
	 *
	 * ## OPTIONS
	 *
	 * [--funnel=<funnel>]
	 * : Specify by funnel ID
	 *
	 * [--broadcast=<broadcast>]
	 * : Specify by broadcast ID
	 *
	 * [--contact=<contact>]
	 * : Specify by contact ID
	 *
	 * ## EXAMPLES
	 *
	 *     wp groundhogg-queue unpause --funnel=1
	 *     wp groundhogg-queue resume --funnel=1
	 *
	 * @alias resume
	 */
	function unpause( $args, $assoc_args ) {
		// todo implement
		\WP_CLI::error( 'This method has not yet been implemented.' );
	}

	/**
	 * Cancel pending events
	 *
	 * ## OPTIONS
	 *
	 * [--funnel=<funnel>]
	 * : Specify by funnel ID
	 *
	 * [--broadcast=<broadcast>]
	 * : Specify by broadcast ID
	 *
	 * [--contact=<contact>]
	 * : Specify by contact ID
	 *
	 * ## EXAMPLES
	 *
	 *     wp groundhogg-queue cancel --funnel=1
	 */
	function cancel( $args, $assoc_args ) {
		// todo implement
		\WP_CLI::error( 'This method has not yet been implemented.' );
	}

}
