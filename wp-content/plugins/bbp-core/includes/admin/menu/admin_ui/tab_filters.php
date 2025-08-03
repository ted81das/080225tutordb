<?php

// Define the expected items
$expected_items = [ 'open', 'closed', 'hidden', 'no_reply', 'all', 'trash' ];

// Get the $filter_set, with a default value if it's not set
$filter_set = bbpc_get_opt('filter_buttons') ?? [];

// Check if all expected items are in the $filter_set
$are_all_available = empty(array_diff($expected_items, $filter_set)) ? 'all-available' : '';

?>

<div class="easydocs-filter-container">
	<ul class="single-item-filter <?php echo esc_attr($are_all_available) ?>">
		<?php if ( in_array( 'all', $filter_set, true ) ) : ?>
			<li class="easydocs-btn" cookie-id="all-<?php echo esc_attr( $item ); ?>" data-filter="all">
			<span class="dashicons dashicons-editor-ul"></span>
				<?php esc_html_e( 'All Topics', 'bbp-core' ); ?>
                <span class="filter-count-badge"> (<?php echo esc_html( $children->post_count ); ?>) </span>
			</li>
		<?php endif; ?>

		<?php if ( in_array( 'open', $filter_set, true ) ) : ?>
            <li class="easydocs-btn" cookie-id="open-<?php echo esc_attr( $item ); ?>" data-filter=".open-topics">
                <img src="<?php echo BBPC_IMG ?>/icon/open.svg" alt="<?php esc_attr_e( 'Open icon', 'bbp-core' ) ?>">
				<?php esc_html_e( 'Open', 'bbp-core' ); ?>
                <span class="filter-count-badge"> (<?php echo esc_html( $count_open ); ?>) </span>
            </li>
		<?php endif; ?>

		<?php if ( in_array( 'closed', $filter_set, true ) ) : ?>
			<li class="easydocs-btn" cookie-id="closed-<?php echo esc_attr( $item ); ?>" data-filter=".closed-topics">
			<span class="dashicons dashicons-dismiss"></span>
			<?php esc_html_e( 'Closed', 'bbp-core' ); ?>
			<span class="filter-count-badge"> (<?php echo esc_html( $count_closed ); ?>) </span>
			</li>
		<?php endif; ?>

		<?php if ( in_array( 'hidden', $filter_set, true ) ) : ?>
		<li class="easydocs-btn" cookie-id="hidden-<?php echo esc_attr( $item ); ?>" data-filter=".hidden-topics" title="<?php esc_attr_e( 'Spam & Pending Topics', 'bbp-core' ); ?>">
		<span class="dashicons dashicons-hidden"></span>
            <?php esc_html_e( 'Hidden', 'bbp-core' ); ?>
            <span class="filter-count-badge"> (<?php echo esc_html( $count_hidden ); ?>) </span>
		</li>
		<?php endif; ?>

		<?php if ( in_array( 'no_reply', $filter_set, true ) ) : ?>
		<li class="easydocs-btn" cookie-id="no_reply-<?php echo esc_attr( $item ); ?>" data-filter=".no-reply">
		<span class="dashicons dashicons-backup"></span>
			<?php esc_html_e( 'No Reply', 'bbp-core' ); ?>
			<span class="filter-count-badge"> (<?php echo esc_html( $count_no_reply ); ?>) </span>
		</li>
		<?php endif; ?>

		<?php if ( in_array( 'solved', $filter_set, true ) ) : ?>
		<li class="easydocs-btn" cookie-id="solved-<?php echo esc_attr( $item ); ?>" data-filter=".solved">
            <span class="dashicons dashicons-yes-alt"></span>
			<?php esc_html_e( 'Solved', 'bbp-core' ); ?>
			<span class="filter-count-badge"> (<?php echo esc_html( $count_solved ); ?>) </span>
		</li>
		<?php endif; ?>

		<?php if ( in_array( 'unsolved', $filter_set, true ) ) : ?>
		<li class="easydocs-btn" cookie-id="unsolved-<?php echo esc_attr( $item ); ?>" data-filter=".unsolved">
            <span class="dashicons dashicons-info-outline"></span>
			<?php esc_html_e( 'Unsolved', 'bbp-core' ); ?>
			<span class="filter-count-badge"> (<?php echo esc_html( $count_unsolved ); ?>) </span>
		</li>
		<?php endif; ?>

	</ul>

    <?php if ( in_array( 'trash', $filter_set, true ) ) : ?>
		<div class="easydocs-btn-sm bbpc-trash-filter">
			<a href="<?php echo admin_url( 'edit.php?post_status=trash&post_type=topic' ); ?>">
                <img src="<?php echo BBPC_IMG ?>/icon/trash.svg" alt="<?php esc_attr_e( 'Trash icons', 'bbp-core') ?>">
				<?php esc_html_e( 'Trashed', 'bbp-core' ); ?>
				<span class="filter-count-badge"> (<?php echo esc_html( $count_trash ); ?>) </span>
			</a>
		</div>
    <?php endif; ?>
</div>
