<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'bbp_voting_get_plugin_install_link' ) ) {
    function bbp_voting_get_plugin_install_link( $plugin, $action = 'install-plugin' ) {
        return esc_url(
            wp_nonce_url(
                add_query_arg(
                    [
                        'action' => $action,
                        'plugin' => $plugin,
                    ],
                    admin_url( 'update.php' )
                ),
                $action . '_' . $plugin
            )
        );
    }
}


if ( ! function_exists( 'bbp_voting_get_plugin_activate_link' ) ) {
	function bbp_voting_get_plugin_activate_link( $plugin, $action = 'activate' ) {
		if ( strpos( $plugin, '/' ) ) {
			$plugin = str_replace( '\/', '%2F', $plugin );
		}
		$url                = sprintf( admin_url( 'plugins.php?action=' . $action . '&plugin=%s&plugin_status=all&paged=1&s' ), $plugin );
		$_REQUEST['plugin'] = $plugin;
		$url                = wp_nonce_url( $url, $action . '-plugin_' . $plugin );
		return $url;
	}
}


if ( ! function_exists( 'bbp_voting_field' ) ) {
	function bbp_voting_field( $key, $name, $label = '', $description = '', $type = 'bool', $pro = false ) {
		$name_and_id = 'name="' . $key . '" id="' . $key . '"';
		$teaser      = defined( 'BBPVOTINGPRO' ) ? false : $pro;
		$attributes  = $teaser ? 'disabled' : $name_and_id;
		?>
		<tr valign="top">
			<th scope="row">
				<?php echo $name; ?>:
				<?php if ( $pro ) { ?>
					<a href="https://wpforthewin.com/product/bbpress-voting-pro/" target="_blank"><span class="bbp-voting-pro-badge bbp-voting-pro-green">Pro</span></a>
				<?php } ?>
			</th>
			<td>
				<?php if ( $type == 'bool' ) { ?>
					<input type="checkbox" <?php echo $attributes; ?> value="true" 
					<?php
					if ( get_option( $key ) == 'true' ) {
						echo 'checked';}
					?>
					 />
				<?php } elseif ( is_array( $type ) ) { ?>
					<select <?php echo $attributes; ?>>
						<?php foreach ( $type as $option ) { ?>
							<option value="<?php echo $option; ?>"
							<?php
							if ( get_option( $key ) == $option ) {
								echo ' selected';}
							?>
							><?php echo ucwords( str_replace( '-', ' ', $option ) ); ?></option>
						<?php } ?>
					</select>
				<?php } else { ?>
					<input type="<?php echo $type; ?>" <?php echo $attributes; ?> value="<?php echo get_option( $key ); ?>" data-lpignore="true" />
				<?php } ?>
				<?php if ( ! empty( $label ) ) { ?>
					<label for="<?php echo $key; ?>"><?php echo $label; ?></label>
				<?php } ?>
				<?php if ( ! empty( $description ) ) { ?>
					<p class="description"><?php echo $description; ?></p>
				<?php } ?>
			</td>
		</tr>
		<?php
	}
}

if ( ! function_exists( 'bbp_voting_get_current_post_type' ) ) {
	function bbp_voting_get_current_post_type() {
		$this_post_id = bbp_get_reply_id() ?: bbp_get_topic_id();
		return get_post_type( $this_post_id );
	}
}

if ( ! function_exists( 'bbp_voting_get_post_type_by_id' ) ) {
	function bbp_voting_get_post_type_by_id( $post_id ) {
		$this_post_id = bbp_get_reply_id( $post_id ) ?: bbp_get_topic_id( $post_id );
		return get_post_type( $this_post_id );
	}
}

if ( ! class_exists( 'WilsonConfidenceIntervalCalculator' ) ) {
	class WilsonConfidenceIntervalCalculator {
		/**
		 * Computed value for confidence (z)
		 *
		 * These values were computed using Ruby's Statistics2.pnormaldist function
		 * 1.645 = 90% confidence
		 * 1.959964 = 95.0% confidence
		 * 2.241403 = 97.5% confidence
		 */
		public function getScore( int $positiveVotes, int $totalVotes, float $confidence = 1.645 ) : float {
			return (float) $totalVotes ? $this->lowerBound( $positiveVotes, $totalVotes, $confidence ) : 0;
		}
		private function lowerBound( int $positiveVotes, int $totalVotes, float $confidence ) : float {
			$phat        = 1.0 * $positiveVotes / $totalVotes;
			$numerator   = $this->calculationNumerator( $totalVotes, $confidence, $phat );
			$denominator = $this->calculationDenominator( $totalVotes, $confidence );
			return $numerator / $denominator;
		}
		private function calculationDenominator( int $total, float $z ) : float {
			return 1 + $z * $z / $total;
		}
		private function calculationNumerator( int $total, float $z, float $phat ) : float {
			return $phat + $z * $z / ( 2 * $total ) - $z * sqrt( ( $phat * ( 1 - $phat ) + $z * $z / ( 4 * $total ) ) / $total );
		}
	}
}


if ( ! function_exists( 'bbp_voting_parse_args' ) ) {
	function bbp_voting_parse_args( $args, $defaults = [], $filter_key = '' ) {

		// Setup a temporary array from $args
		if ( is_object( $args ) ) {
			$r = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$r =& $args;
		} else {
			$r = [];
			wp_parse_str( $args, $r );
		}

		// Passively filter the args before the parse
		if ( ! empty( $filter_key ) ) {
			$r = apply_filters( "bbp_voting_before_{$filter_key}_parse_args", $r, $args, $defaults );
		}

		// Parse
		if ( is_array( $defaults ) && ! empty( $defaults ) ) {
			$r = array_merge( $defaults, $r );
		}

		// Aggressively filter the args after the parse
		if ( ! empty( $filter_key ) ) {
			$r = apply_filters( "bbp_voting_after_{$filter_key}_parse_args", $r, $args, $defaults );
		}

		// Return the parsed results
		return $r;
	}
}
