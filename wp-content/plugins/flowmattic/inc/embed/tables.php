<?php
/**
 * FlowMattic Tables embed file.
 *
 * @package flowmattic
 * @since 5.0
 */

remove_all_actions( 'wp_head' );
remove_all_actions( 'wp_footer' );

// Remove user login check.
remove_action( 'wp_ajax_nopriv_flowmattic_chatbot', 'flowmattic_chatbot_ajax' );
?>
<?php
// Assign the table ID.
$table_id = isset( $_GET['table_id'] ) ? base64_decode( sanitize_text_field( wp_unslash( $_GET['table_id'] ) ) ) : '';

// Explode the table ID.
$table_id_data = explode( '@@', $table_id );

// Get the table name.
$table_name = $table_id_data[0];

// Get the database name.
$database_name = $table_id_data[1];

// Check if filter is set.
$filter_column = isset( $_GET['filter_column'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_column'] ) ) : '';
$filter_value  = isset( $_GET['filter_value'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_value'] ) ) : '';

// Get the nonce.
$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) )  : wp_create_nonce( 'flowmattic_table_nonce_frontend' );
?>
<!DOCTYPE html>
<html class="flowmattic-table-html">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo esc_attr( $table_name ); ?> table</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<div id="tablesApp"></div>
	<script type="text/javascript">
		var table_name = '<?php echo $table_name; ?>';
		var database_id = '<?php echo $database_name; ?>';
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		var security	= '<?php echo $nonce; ?>';
		var empty_icon  = '<?php echo esc_url( FLOWMATTIC_PLUGIN_URL . 'tables/src/img/emptyRecordTemplate_light.svg' ); ?>';
		var isFrontend = true;
		<?php
		if ( ! empty( $filter_column ) && ! empty( $filter_value ) ) {
			$filter_obj = array(
				'name'  => $filter_column,
				'value' => $filter_value,
			);
			?>
			var frontendFilter = <?php echo wp_json_encode( $filter_obj ); ?>;
			<?php
		} else {
			?>
			var frontendFilter = false;
			<?php
		}
		?>
	</script>
	<?php
	echo '<script type="text/javascript" id="flowmattic-tables-react" src="' . FLOWMATTIC_PLUGIN_URL . '/tables/build/react-vendor.bundle.js"></script>';
	echo '<script type="text/javascript" id="flowmattic-tables-main" src="' . FLOWMATTIC_PLUGIN_URL . '/tables/build/tables.bundle.js"></script>';
	echo '<link rel="stylesheet" id="flowmattic-tables-main" href="' . FLOWMATTIC_PLUGIN_URL . '/tables/build/tables.css"></script>';
	?>
	</body>
</html>
