<?php
/**
 * Application Name: Webpage Parser
 * Description: Add Webpage Parser integration to FlowMattic.
 * Version: 3.2.0
 * Author: InfiWebs
 * Author URI: https://www.infiwebs.com
 * Textdomain: flowmattic
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webpage Parser class.
 */
class FlowMattic_Webpage_Parser {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 3.2.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for webpage-parser.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'webpage_parser',
			array(
				'name'         => esc_attr__( 'Webpage Parser by FlowMattic', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/webpage-parser/icon.png',
				'instructions' => __( 'Extract important data from any URL in your workflow.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'base'         => 'core',
				'type'         => 'action',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-webpage-parser', FLOWMATTIC_PLUGIN_URL . 'inc/apps/webpage-parser/view-webpage-parser.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'parse_webpage' => array(
				'title'       => esc_attr__( 'Parse Webpage by URL', 'flowmattic' ),
				'description' => esc_attr__( 'Parse any Webpage URL into JSON data.', 'flowmattic' ),
			),
			'get_meta_tags' => array(
				'title'       => esc_attr__( 'Get Meta Tags', 'flowmattic' ),
				'description' => esc_attr__( 'Get meta tags of any Webpage URL.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step        = (array) $step;
		$action      = $step['action'];
		$fields      = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$webpage_url = ( isset( $fields['webpage_url'] ) && '' !== trim( $fields['webpage_url'] ) ) ? $fields['webpage_url'] : '';
		$has_headers = ( isset( $fields['has_headers'] ) && 'yes' === trim( $fields['has_headers'] ) ) ? true : false;

		// Set the request body.
		$this->request_body = $fields;

		if ( '' === $webpage_url ) {
			$response = array(
				'status' => 'error',
				'result' => esc_html__( 'Webpage File URL is required.', 'flowmattic' ),
			);
		} elseif ( 'parse_webpage' === $action ) {
				$response = $this->parse_url( $webpage_url );
		} elseif ( 'get_meta_tags' === $action ) {
			$response = $this->get_meta_tags_by_url( $webpage_url );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Parse the given URL and extract data.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param string $url Webpage URL to parse.
	 * @return array
	 */
	public function parse_url( $url ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return array(
				'error' => esc_html__( 'The PHP DOM extension is not enabled on this server. Please contact your hosting provider.', 'flowmattic' ),
			);
		}

		$data = array(
			'title'        => '',
			'description'  => '',
			'meta'         => array(),
			'content'      => '',
			'schema'       => array(),
			'images'       => array(),
			'emails'       => array(),
			'social_links' => array(),
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return array(
				'error' => $response->get_error_message(),
			);
		}

		$html = wp_remote_retrieve_body( $response );

		$doc = new DOMDocument();
		// Suppress DOM warnings and errors temporarily.
		libxml_use_internal_errors( true );

		// Convert to UTF-8 and then load.
		$utf8_html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );

		$doc->loadHTML( $utf8_html );
		libxml_clear_errors(); // Clear any errors that might have occurred.

		$domxpath = new DOMXPath( $doc );

		// Get Schema (application/ld+json) data.
		$schema_elements = $domxpath->query( '//script[@type="application/ld+json"]' );
		$schemas         = array();
		foreach ( $schema_elements as $element ) {
			$schema_data = json_decode( $element->nodeValue, true );
			if ( $schema_data ) {
				$schemas[] = $schema_data;
			}
		}

		if ( ! empty( $schemas ) ) {
			$data['schema'] = wp_json_encode( $schemas );
		}

		// Remove script and style elements.
		$script_tags = $doc->getElementsByTagName( 'script' );
		$style_tags  = $doc->getElementsByTagName( 'style' );

		// Remove all script and style elements.
		while ( ( $script_tags = $doc->getElementsByTagName( 'script' ) ) && $script_tags->length ) {
			$script_tags->item( 0 )->parentNode->removeChild( $script_tags->item( 0 ) );
		}

		while ( ( $style_tags = $doc->getElementsByTagName( 'style' ) ) && $style_tags->length ) {
			$style_tags->item( 0 )->parentNode->removeChild( $style_tags->item( 0 ) );
		}

		$xpath = new DOMXPath( $doc );

		// Get title.
		$title = $xpath->query( '/html/head/title' );
		if ( $title->length > 0 ) {
			$data['title'] = trim( $title->item( 0 )->nodeValue );
		}

		// Get meta description.
		$meta_description = $xpath->query( '/html/head/meta[@name="description"]/@content' );
		if ( $meta_description->length > 0 ) {
			$data['description'] = trim( $meta_description->item( 0 )->nodeValue );
		}

		// Get all meta tags.
		$meta_tags       = $xpath->query( '/html/head/meta' );
		$meta_tags_array = array();
		foreach ( $meta_tags as $meta ) {
			$name = $meta->getAttribute( 'name' );
			if ( ! $name ) {
				$name = $meta->getAttribute( 'property' );
			}
			$content = $meta->getAttribute( 'content' );
			if ( $name && $content ) {
				$meta_tags_array['meta'][ $name ] = $content;
			}
		}

		if ( ! empty( $meta_tags_array ) ) {
			$data['meta'] = wp_json_encode( array( $meta_tags_array ) );
		}

		// Get the body content as text.
		$body = $xpath->query( '/html/body' );
		if ( $body->length > 0 ) {
			$scrape_content = $body->item( 0 )->nodeValue;

			// Convert br tags to new lines.
			$data['content'] = str_replace( '<br>', "\n", $scrape_content );

			// Start new line for each tag.
			$data['content'] = preg_replace( '/<\/(p|h1|h2|h3|h4|h5|h6|li|ul|ol|div|section|article|header|footer|nav|aside|main|table|tr|td|th|tbody|thead|tfoot|caption|form|fieldset|legend|label|input|textarea|select|option|button|a|span|strong|em|b|i|u|s|strike|del|ins|sub|sup|small|big|pre|code|blockquote|cite|q|abbr|acronym|address|time|date|dfn|kbd|samp|var|mark|ruby|rt|rp|bdi|bdo|wbr|br|hr|img|svg|canvas|video|audio|iframe|embed|object|param|source|track|map|area|a|link|meta|base|title|head|html|body|script|style|noscript|basefont|font|center|dir|menu|menuitem|summary|details|figure|figcaption|picture|source|track|dialog|slot|template' . ')>/', "\n ", $data['content'] );

			// Remove multiple line breaks and keep only one.
			$data['content'] = preg_replace( '/\s+\n/', " \n ", $data['content'] );

			// Trim the content.
			$data['content'] = trim( $data['content'] );

			// Remove multiple spaces at the beginning of each line.
			$data['content'] = preg_replace( '/^\s+/m', ' ', $data['content'] );
		}

		// Get images.
		$images         = array();
		$image_elements = $xpath->query( '//img' );
		foreach ( $image_elements as $element ) {
			$img_data = array();
			foreach ( $element->attributes as $attribute ) {
				$attr_name  = $attribute->nodeName;
				$attr_value = $attribute->nodeValue;

				// Check for non-standard attributes used for lazy-loading or other purposes.
				if ( ! in_array( $attr_name, array( 'width', 'height', 'style', 'class', 'id', 'data-sizes' ) ) ) {
					$img_data[ $attr_name ] = $attr_value;

					// Handle relative image URLs by making them absolute.
					if ( ( 'src' === $attr_name || 'data-src' === $attr_name ) && ! filter_var( $attr_value, FILTER_VALIDATE_URL ) && false === strpos( $attr_value, 'data:image' ) ) {
						$parsed_url             = parse_url( $url );
						$base                   = $parsed_url['scheme'] . '://' . $parsed_url['host'];
						$img_data[ $attr_name ] = $base . $attr_value;
					}
				}
			}

			$images[] = $img_data;
		}

		if ( ! empty( $images ) ) {
			$data['images'] = wp_json_encode( $images );
		}

		// Extract emails using regex.
		$email_pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/';
		preg_match_all( $email_pattern, $html, $matches );

		// Ensure only unique emails are added.
		$emails = array_unique( $matches[0] );

		$email_object = array();
		if ( ! empty( $emails ) ) {
			foreach ( $emails as $email ) {
				$email_object[] = array(
					'email' => $email,
				);
			}
		}

		if ( ! empty( $email_object ) ) {
			$data['emails']      = wp_json_encode( $email_object );
			$data['emails_list'] = implode( ',', $emails );
		}

		// Check for social links.
		$social_platforms = array( 'facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com', 'youtube.com' );
		$links            = $xpath->query( '//a[@href]' );
		$social_links     = array();
		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );
			foreach ( $social_platforms as $platform ) {
				if ( strpos( $href, $platform ) !== false ) {
					$platform_name                  = str_replace( '.com', '', $platform );
					$social_links[ $platform_name ] = $href;
					break; // break inner loop to avoid adding same link for multiple platforms.
				}
			}
		}

		if ( ! empty( $social_links ) ) {
			// Ensure only unique social links are added.
			$data['social_links'] = wp_json_encode( array( $social_links ) );
		}

		// Get all links.
		$all_links = array();
		foreach ( $links as $link ) {
			// Skip the hash links.
			if ( strpos( $link->getAttribute( 'href' ), '#' ) === 0 ) {
				continue;
			}

			$all_links[] = $link->getAttribute( 'href' );
		}

		if ( ! empty( $all_links ) ) {
			// Ensure only unique links are added.
			$all_links = array_unique( $all_links );

			// Categorize the links as internal or external.
			$internal_links = array();
			$external_links = array();

			foreach ( $all_links as $link ) {
				if ( filter_var( $link, FILTER_VALIDATE_URL ) ) {
					$parsed_url = wp_parse_url( $link );
					if ( isset( $parsed_url['host'] ) && wp_parse_url( $url, PHP_URL_HOST ) === $parsed_url['host'] ) {
						$internal_links[] = $link;
					} else {
						$external_links[] = $link;
					}
				} else {
					// Handle relative links.
					$parsed_url = parse_url( $url );
					$base       = $parsed_url['scheme'] . '://' . $parsed_url['host'];
					$full_link  = $base . $link;
					if ( filter_var( $full_link, FILTER_VALIDATE_URL ) ) {
						$internal_links[] = $full_link;

						// Ensure only unique links are added.
						$internal_links = array_unique( $internal_links );
					}
				}
			}

			$data['internal_links'] = wp_json_encode( $internal_links );
			$data['external_links'] = wp_json_encode( $external_links );

			// Add all links.
			$data['all_links'] = wp_json_encode( $all_links );
		}

		// Get all headings.
		$headings = array();
		$tags     = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
		foreach ( $tags as $tag ) {
			$elements = $xpath->query( '//' . $tag );
			foreach ( $elements as $element ) {
				$headings[] = array(
					'tag'     => $tag,
					'heading' => $element->nodeValue,
				);
			}
		}

		if ( ! empty( $headings ) ) {
			$data['heading_tags'] = wp_json_encode( $headings );
		}

		return $data;
	}

	/**
	 * Parse the given URL and extract data.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param string $url Webpage URL to parse.
	 * @return array
	 */
	public function get_meta_tags_by_url( $url ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return array(
				'error' => esc_html__( 'The PHP DOM extension is not enabled on this server. Please contact your hosting provider.', 'flowmattic' ),
			);
		}

		$data = array(
			'title'       => '',
			'description' => '',
		);

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false; // There was an error making the request.
		}

		$html = wp_remote_retrieve_body( $response );

		$doc = new DOMDocument();
		// Suppress DOM warnings and errors temporarily.
		libxml_use_internal_errors( true );
		$doc->loadHTML( $html );
		libxml_clear_errors(); // Clear any errors that might have occurred.
		$xpath = new DOMXPath( $doc );

		// Get title.
		$title = $xpath->query( '/html/head/title' );
		if ( $title->length > 0 ) {
			$data['title'] = trim( $title->item( 0 )->nodeValue );
		}

		// Get meta description.
		$meta_description = $xpath->query( '/html/head/meta[@name="description"]/@content' );
		if ( $meta_description->length > 0 ) {
			$data['description'] = trim( $meta_description->item( 0 )->nodeValue );
		}

		// Get all meta tags.
		$meta_tags       = $xpath->query( '/html/head/meta' );
		$meta_tags_array = array();
		foreach ( $meta_tags as $meta ) {
			$name = $meta->getAttribute( 'name' );
			if ( ! $name ) {
				$name = $meta->getAttribute( 'property' );
			}
			$content = $meta->getAttribute( 'content' );
			if ( $name && $content ) {
				$data[ 'meta-' . $name ] = $content;
			}
		}

		if ( ! empty( $meta_tags_array ) ) {
			// $data['meta'] = wp_json_encode( array( $meta_tags_array ) );
		}

		return $data;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 3.2.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event       = $event_data['event'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data sent to API endpoint.
	 *
	 * @access public
	 * @since 3.2.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Webpage_Parser();
