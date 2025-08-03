<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'gdbbp_Error' ) ) {
	class gdbbp_Error {
		var $errors = [];

		function __construct() {
		}

		function add( $code, $message, $data ) {
			$this->errors[ $code ][] = [ $message, $data ];
		}
	}
}

if ( ! function_exists( 'bbpc_bbpress_get_user_roles' ) ) {
	function bbpc_bbpress_get_user_roles() {
		$roles = [];

		$dynamic_roles = bbp_get_dynamic_roles();

		foreach ( $dynamic_roles as $role => $obj ) {
			$roles[ $role ] = $obj['name'];
		}

		return $roles;
	}
}

if ( ! function_exists( 'bbpc_has_bbpress' ) ) {
	function bbpc_has_bbpress() {
		if ( function_exists( 'bbp_version' ) ) {
			$version = bbp_get_version();
			$version = intval( substr( str_replace( '.', '', $version ), 0, 2 ) );

			return $version > 22;
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'bbpc_bbpress_version' ) ) {
	function bbpc_bbpress_version( $ret = 'code' ) {
		if ( ! bbpc_has_bbpress() ) {
			return null;
		}

		$version = bbp_get_version();

		if ( isset( $version ) ) {
			if ( $ret == 'code' ) {
				return substr( str_replace( '.', '', $version ), 0, 2 );
			} else {
				return $version;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'bbpc_is_bbpress' ) ) {
	function bbpc_is_bbpress() {
		$is = bbpc_has_bbpress() ? is_bbpress() : false;

		return apply_filters( 'bbpc_is_bbpress', $is );
	}
}

if ( ! function_exists( 'bbpc_is_user_moderator' ) ) {
	function bbpc_is_user_moderator() {
		global $current_user;

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'bbp_moderator', $current_user->roles );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'bbpc_is_user_admin' ) ) {
	function bbpc_is_user_admin() {
		global $current_user;

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'administrator', $current_user->roles );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'bbpc_url_campaign_tracking' ) ) {
	function bbpc_url_campaign_tracking( $url, $campaign = '', $medium = '', $content = '', $term = '', $source = null ) {
		if ( ! empty( $campaign ) ) {
			$url = add_query_arg( 'utm_campaign', $campaign, $url );
		}

		if ( ! empty( $medium ) ) {
			$url = add_query_arg( 'utm_medium', $medium, $url );
		}

		if ( ! empty( $content ) ) {
			$url = add_query_arg( 'utm_content', $content, $url );
		}

		if ( ! empty( $term ) ) {
			$url = add_query_arg( 'utm_term', $term, $url );
		}

		if ( is_null( $source ) ) {
			$source = parse_url( get_bloginfo( 'url' ), PHP_URL_HOST );
		}

		if ( ! empty( $source ) ) {
			$url = add_query_arg( 'utm_source', $source, $url );
		}

		return esc_url( $url );
	}
}
