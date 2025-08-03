<?php
namespace admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Pro_Widget_Map {

	public function get_pro_widget_map() {

		$arr_obj = Pro_Widget_Service::get_widget_settings(
			function ( $settings ) {
				$core_widgets = $settings['settings_fields']['bbpc_active_modules'];

				$arr = [];

				foreach ( $core_widgets as $key => $widget ) {

					if ( 'pro' == $widget['widget_type'] ) {

						$ar = [
							'categories'    => [ 'bbp-core-pro' ],
							'name'          => $widget['name'],
							'title'         => $widget['label'],
							'icon'          => 'bbpc_icon_' . $widget['name'],
							'action_button' => [
								'classes' => [ 'elementor-button', 'elementor-button-success' ],
								'text'    => esc_html__( 'See it in Action', 'bbp-core' ),
								'url'     => esc_url( $widget['demo_url'] )
							],
						];

						array_push( $arr, $ar );
					}
				}

				return $arr;
			}
		);

		return $arr_obj;
	}
}
