<?php
/**
 * Number Formatter by FlowMattic.
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FlowMattic_Number_Formatter.
 *
 * @since 4.1.10
 */
class FlowMattic_Number_Formatter {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 4.3.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 4.1.10
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for number formatter.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'number_formatter',
			array(
				'name'         => esc_html__( 'Number Formatter', 'flowmattic' ),
				'icon'         => FLOWMATTIC_PLUGIN_URL . 'inc/apps/number-formatter/icon.svg',
				'instructions' => __( 'Format number with different methods.', 'flowmattic' ),
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
	 * @since 4.1.10
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-number-formatter', FLOWMATTIC_PLUGIN_URL . 'inc/apps/number-formatter/view-number-formatter.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 4.1.10
	 * @return array
	 */
	public function get_actions() {
		return array(
			'format_number'         => array(
				'title'       => esc_html__( 'Format Number', 'flowmattic' ),
				'description' => esc_html__( 'Format the given number to a new style.', 'flowmattic' ),
			),
			'format_phone_number'   => array(
				'title'       => esc_html__( 'Format Phone Number', 'flowmattic' ),
				'description' => esc_html__( 'Format the given phone number with/without country code etc.', 'flowmattic' ),
			),
			'format_currency'       => array(
				'title'       => esc_html__( 'Format Currency', 'flowmattic' ),
				'description' => esc_html__( 'Format the given currency to a new style.', 'flowmattic' ),
			),
			'decimal_converter'     => array(
				'title'       => esc_html__( 'Decimal Converter', 'flowmattic' ),
				'description' => esc_html__( 'Convert the given number to a different decimal format.', 'flowmattic' ),
			),
			'minor_unit_conversion' => array(
				'title'       => esc_html__( 'Minor Unit Conversion', 'flowmattic' ),
				'description' => esc_html__( 'Convert the given number to a different minor unit.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Run action step.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$response_array = array();

		// CS.
		$capture_data;

		switch ( $action ) {
			case 'format_number':
				$response_array = $this->format_number( $fields );
				break;

			case 'format_phone_number':
				$response_array = $this->format_phone_number( $fields );
				break;

			case 'format_currency':
				$response_array = $this->format_currency( $fields );
				break;

			case 'decimal_converter':
				$response_array = $this->decimal_converter( $fields );
				break;

			case 'minor_unit_conversion':
				$response_array = $this->minor_unit_conversion( $fields );
				break;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Format number.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $fields Fields.
	 * @return array
	 */
	public function format_number( $fields ) {
		$number   = isset( $fields['number'] ) ? (float) $fields['number'] : '';
		$grouping = isset( $fields['grouping'] ) ? $fields['grouping'] : '';

		// Set the request body.
		$this->request_body = array(
			'number'   => $number,
			'grouping' => $grouping,
		);

		$formatted_number = '';

		// Get the lenght of the number after the decimal point.
		$decimal_number = explode( '.', $number );
		$decimal_length = isset( $decimal_number[1] ) ? strlen( $decimal_number[1] ) : 0;

		if ( 'comma' === $grouping ) {
			$formatted_number = number_format( $number, $decimal_length, '.', ',' );
		} elseif ( 'space' === $grouping ) {
			$formatted_number = number_format( $number, $decimal_length, '.', ' ' );
		}

		return array(
			'formatted_number' => $formatted_number,
		);
	}

	/**
	 * Format phone number.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $fields Fields.
	 * @return array
	 */
	public function format_phone_number( $fields ) {
		$phone_number = isset( $fields['phone_number'] ) ? $fields['phone_number'] : '';
		$country_code = isset( $fields['country_code'] ) ? $fields['country_code'] : '';
		$format       = isset( $fields['format'] ) ? $fields['format'] : '';

		// Set the request body.
		$this->request_body = array(
			'phone_number' => $phone_number,
			'country_code' => $country_code,
			'format'       => $format,
		);

		$formatted_phone_number = '';

		// Get the country code number.
		$numerical_country_code = $this->get_country_code_number( $country_code );
		$country_code           = $numerical_country_code['code'];
		$country                = $numerical_country_code['country'];
		$phone_format           = $numerical_country_code['format'];

		// If the phone number starts with a plus sign and country code, remove it.
		if ( '+' . $country_code === substr( $phone_number, 0, strlen( '+' . $country_code ) ) ) {
			$phone_number = substr( $phone_number, strlen( '+' . $country_code ) );
		}

		// Remove any non-numeric characters from the phone number.
		$phone_number = preg_replace( '/[^0-9]/', '', $phone_number );

		// If the phone number starts with a zero, remove it.
		if ( '0' === substr( $phone_number, 0, 1 ) ) {
			$phone_number = substr( $phone_number, 1 );
		}

		// If the phone number starts with the country code, remove it.
		if ( '0' === substr( $phone_number, 0, strlen( $country_code ) ) ) {
			$phone_number = substr( $phone_number, strlen( $country_code ) );
		}

		// Remove the country code from the phone format.
		$phone_format = str_replace( '+' . $country_code . ' ', '', $phone_format );

		$error = '';

		// Format the phone number based on the format.
		if ( 'international' === $format ) {
			// Count the required digits in the format.
			$required_digits = substr_count( $phone_format, 'X' );

			// Check if number of digits match.
			if ( $required_digits < strlen( $phone_number ) ) {
				$error = 'Phone number has more digits than required. Falling back to E.164 format.';

				$formatted_phone_number = '+' . $country_code . $phone_number;
			} elseif ( $required_digits > strlen( $phone_number ) ) {
				$error = 'Phone number has less digits than required. Falling back to E.164 format.';

				$formatted_phone_number = '+' . $country_code . $phone_number;
			} else {
				// Insert the phone number into the format.
				$current_index = 0;
				$number_length = strlen( $phone_format );

				for ( $i = 0; $i < $number_length; $i++ ) {
					if ( 'X' === $phone_format[ $i ] ) {
						if ( $current_index < strlen( $phone_number ) ) {
							$formatted_phone_number .= $phone_number[ $current_index ];
							++$current_index;
						}
					} else {
						$formatted_phone_number .= $phone_format[ $i ];
					}
				}

				$formatted_phone_number = '+' . $country_code . ' ' . $formatted_phone_number;
			}
		} elseif ( 'national' === $format ) {
			// Format the number with spaces - 3 digits, 3 digits, 4 digits.
			$formatted_phone_number = substr( $phone_number, 0, 3 ) . ' ' . substr( $phone_number, 3, 3 ) . ' ' . substr( $phone_number, 6 );

			$formatted_phone_number = $formatted_phone_number;
		} elseif ( 'e164' === $format ) {
			$formatted_phone_number = '+' . $country_code . $phone_number;
		}

		return array(
			'formatted_phone_number' => $formatted_phone_number,
			'country'                => $country,
			'dialing_code'           => '+' . $country_code,
			'message'                => $error,
		);
	}

	/**
	 * Format currency.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $fields Fields.
	 * @return array
	 */
	public function format_currency( $fields ) {
		$currency = isset( $fields['currency'] ) ? $fields['currency'] : '';
		$amount   = isset( $fields['amount'] ) ? (float) $fields['amount'] : '';
		$format   = isset( $fields['currency_format'] ) ? $fields['currency_format'] : '';
		$locale   = isset( $fields['currency_locale'] ) ? $fields['currency_locale'] : '';

		// Set the request body.
		$this->request_body = array(
			'currency' => $currency,
			'amount'   => $amount,
			'format'   => $format,
			'locale'   => $locale,
		);

		$formatted_currency = '';

		$fmt = new NumberFormatter( $locale, NumberFormatter::DECIMAL );

		// Set the number pattern.
		$fmt->setPattern( $format );

		// Format the currency.
		$formatted_currency = $fmt->formatCurrency( $amount, $currency );

		return array(
			'formatted_currency' => $formatted_currency,
		);
	}

	/**
	 * Decimal converter.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $fields Fields.
	 * @return array
	 */
	public function decimal_converter( $fields ) {
		$number         = isset( $fields['number'] ) ? (float) $fields['number'] : '';
		$decimal_places = isset( $fields['decimal_places'] ) ? $fields['decimal_places'] : '';

		// Set the request body.
		$this->request_body = array(
			'number'         => $number,
			'decimal_places' => $decimal_places,
		);

		$converted_number = '';

		// Convert the number to the required decimal count.
		$converted_number = number_format( $number, $decimal_places, '.', '' );

		return array(
			'converted_number' => $converted_number,
		);
	}

	/**
	 * Minor unit conversion.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $fields Fields.
	 * @return array
	 */
	public function minor_unit_conversion( $fields ) {
		$amount        = isset( $fields['amount'] ) ? (float) $fields['amount'] : '';
		$currency_code = isset( $fields['currency_code'] ) ? $fields['currency_code'] : '';

		// Set the request body.
		$this->request_body = array(
			'amount'        => $amount,
			'currency_code' => $currency_code,
		);

		$converted_amount = '';

		// Convert the amount to the minor unit.
		$converted_amount = $amount * 100;

		// Get the currency decimal count.
		$decimal_length = $this->get_currency_decimals( $currency_code );

		return array(
			'converted_amount' => ( 0 !== $decimal_length ) ? $converted_amount : $amount,
			'currency_code'    => $currency_code,
		);
	}

	/**
	 * Get country code number.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param array $country_code Country code.
	 * @return string
	 */
	public function get_country_code_number( $country_code ) {
		// @codingStandardsIgnoreStart
		$country_codes = array(
			'AF' => array( 'country' => 'Afghanistan', 'code' => '93', 'format' => '+93 XX XXX XXXX' ),
			'AL' => array( 'country' => 'Albania', 'code' => '355', 'format' => '+355 XXX XXX XXX' ),
			'DZ' => array( 'country' => 'Algeria', 'code' => '213', 'format' => '+213 XXX XXX XXX' ),
			'AS' => array( 'country' => 'American Samoa', 'code' => '1-684', 'format' => '+1-684 XXX XXXX' ),
			'AD' => array( 'country' => 'Andorra', 'code' => '376', 'format' => '+376 XXX XXX' ),
			'AO' => array( 'country' => 'Angola', 'code' => '244', 'format' => '+244 XXX XXX XXX' ),
			'AI' => array( 'country' => 'Anguilla', 'code' => '1-264', 'format' => '+1-264 XXX XXXX' ),
			'AQ' => array( 'country' => 'Antarctica', 'code' => '672', 'format' => '+672 X XXXX' ),
			'AG' => array( 'country' => 'Antigua and Barbuda', 'code' => '1-268', 'format' => '+1-268 XXX XXXX' ),
			'AR' => array( 'country' => 'Argentina', 'code' => '54', 'format' => '+54 X XXXX XXXX' ),
			'AM' => array( 'country' => 'Armenia', 'code' => '374', 'format' => '+374 XX XXX XXX' ),
			'AW' => array( 'country' => 'Aruba', 'code' => '297', 'format' => '+297 XXX XXXX' ),
			'AU' => array( 'country' => 'Australia', 'code' => '61', 'format' => '+61 X XXXX XXXX' ),
			'AT' => array( 'country' => 'Austria', 'code' => '43', 'format' => '+43 XXX XXX XXXX' ),
			'AZ' => array( 'country' => 'Azerbaijan', 'code' => '994', 'format' => '+994 XX XXX XX XX' ),
			'BS' => array( 'country' => 'Bahamas', 'code' => '1-242', 'format' => '+1-242 XXX XXXX' ),
			'BH' => array( 'country' => 'Bahrain', 'code' => '973', 'format' => '+973 XXXX XXXX' ),
			'BD' => array( 'country' => 'Bangladesh', 'code' => '880', 'format' => '+880 XXX XXX XXX' ),
			'BB' => array( 'country' => 'Barbados', 'code' => '1-246', 'format' => '+1-246 XXX XXXX' ),
			'BY' => array( 'country' => 'Belarus', 'code' => '375', 'format' => '+375 XX XXX XX XX' ),
			'BE' => array( 'country' => 'Belgium', 'code' => '32', 'format' => '+32 XXX XX XX XX' ),
			'BZ' => array( 'country' => 'Belize', 'code' => '501', 'format' => '+501 XXX XXXX' ),
			'BJ' => array( 'country' => 'Benin', 'code' => '229', 'format' => '+229 XX XX XXXX' ),
			'BM' => array( 'country' => 'Bermuda', 'code' => '1-441', 'format' => '+1-441 XXX XXXX' ),
			'BT' => array( 'country' => 'Bhutan', 'code' => '975', 'format' => '+975 XX XXX XXX' ),
			'BO' => array( 'country' => 'Bolivia', 'code' => '591', 'format' => '+591 X XXX XXXX' ),
			'BA' => array( 'country' => 'Bosnia and Herzegovina', 'code' => '387', 'format' => '+387 XX XXX XXX' ),
			'BW' => array( 'country' => 'Botswana', 'code' => '267', 'format' => '+267 XX XXX XXX' ),
			'BR' => array( 'country' => 'Brazil', 'code' => '55', 'format' => '+55 XX XXXXX XXXX' ),
			'BN' => array( 'country' => 'Brunei', 'code' => '673', 'format' => '+673 XXX XXXX' ),
			'BG' => array( 'country' => 'Bulgaria', 'code' => '359', 'format' => '+359 X XXX XXXX' ),
			'BF' => array( 'country' => 'Burkina Faso', 'code' => '226', 'format' => '+226 XX XX XXXX' ),
			'BI' => array( 'country' => 'Burundi', 'code' => '257', 'format' => '+257 XX XX XXXX' ),
			'KH' => array( 'country' => 'Cambodia', 'code' => '855', 'format' => '+855 XX XXX XXX' ),
			'CM' => array( 'country' => 'Cameroon', 'code' => '237', 'format' => '+237 XXXX XXXX' ),
			'CA' => array( 'country' => 'Canada', 'code' => '1', 'format' => '+1 XXX XXX XXXX' ),
			'CV' => array( 'country' => 'Cape Verde', 'code' => '238', 'format' => '+238 XXX XX XX' ),
			'KY' => array( 'country' => 'Cayman Islands', 'code' => '1-345', 'format' => '+1-345 XXX XXXX' ),
			'CF' => array( 'country' => 'Central African Republic', 'code' => '236', 'format' => '+236 XX XX XXXX' ),
			'TD' => array( 'country' => 'Chad', 'code' => '235', 'format' => '+235 XX XX XX XX' ),
			'CL' => array( 'country' => 'Chile', 'code' => '56', 'format' => '+56 X XXXX XXXX' ),
			'CN' => array( 'country' => 'China', 'code' => '86', 'format' => '+86 XX XXXXX XXXX' ),
			'CO' => array( 'country' => 'Colombia', 'code' => '57', 'format' => '+57 XXX XXX XXXX' ),
			'KM' => array( 'country' => 'Comoros', 'code' => '269', 'format' => '+269 XX XXX XXXX' ),
			'CG' => array( 'country' => 'Congo', 'code' => '242', 'format' => '+242 XX XXX XXXX' ),
			'CD' => array( 'country' => 'Congo, Democratic Republic of the', 'code' => '243', 'format' => '+243 XXX XXX XXX' ),
			'CR' => array( 'country' => 'Costa Rica', 'code' => '506', 'format' => '+506 XXX XXX XXXX' ),
			'HR' => array( 'country' => 'Croatia', 'code' => '385', 'format' => '+385 X XXX XXXX' ),
			'CU' => array( 'country' => 'Cuba', 'code' => '53', 'format' => '+53 X XXX XXXX' ),
			'CY' => array( 'country' => 'Cyprus', 'code' => '357', 'format' => '+357 XX XXX XXX' ),
			'CZ' => array( 'country' => 'Czech Republic', 'code' => '420', 'format' => '+420 XXX XXX XXX' ),
			'DK' => array( 'country' => 'Denmark', 'code' => '45', 'format' => '+45 XX XX XX XX' ),
			'DJ' => array( 'country' => 'Djibouti', 'code' => '253', 'format' => '+253 XX XX XXXX' ),
			'DM' => array( 'country' => 'Dominica', 'code' => '1-767', 'format' => '+1-767 XXX XXXX' ),
			'DO' => array( 'country' => 'Dominican Republic', 'code' => '1-809', 'format' => '+1-809 XXX XXXX' ),
			'EC' => array( 'country' => 'Ecuador', 'code' => '593', 'format' => '+593 X XXX XXXX' ),
			'EG' => array( 'country' => 'Egypt', 'code' => '20', 'format' => '+20 X XXX XXXX' ),
			'SV' => array( 'country' => 'El Salvador', 'code' => '503', 'format' => '+503 XX XX XXXX' ),
			'GQ' => array( 'country' => 'Equatorial Guinea', 'code' => '240', 'format' => '+240 XXX XXX XXX' ),
			'ER' => array( 'country' => 'Eritrea', 'code' => '291', 'format' => '+291 X XXX XXX' ),
			'EE' => array( 'country' => 'Estonia', 'code' => '372', 'format' => '+372 XXX XXXX' ),
			'ET' => array( 'country' => 'Ethiopia', 'code' => '251', 'format' => '+251 XX XXX XXXX' ),
			'FJ' => array( 'country' => 'Fiji', 'code' => '679', 'format' => '+679 XXX XXXX' ),
			'FI' => array( 'country' => 'Finland', 'code' => '358', 'format' => '+358 XX XXX XXXX' ),
			'FR' => array( 'country' => 'France', 'code' => '33', 'format' => '+33 X XX XX XX XX' ),
			'GA' => array( 'country' => 'Gabon', 'code' => '241', 'format' => '+241 X XX XX XX' ),
			'GM' => array( 'country' => 'Gambia', 'code' => '220', 'format' => '+220 XXX XXXX' ),
			'GE' => array( 'country' => 'Georgia', 'code' => '995', 'format' => '+995 XXX XX XX XX' ),
			'DE' => array( 'country' => 'Germany', 'code' => '49', 'format' => '+49 XXX XXX XXXX' ),
			'GH' => array( 'country' => 'Ghana', 'code' => '233', 'format' => '+233 XXX XXX XXX' ),
			'GR' => array( 'country' => 'Greece', 'code' => '30', 'format' => '+30 XXX XXX XXXX' ),
			'GL' => array( 'country' => 'Greenland', 'code' => '299', 'format' => '+299 XX XX XX' ),
			'GD' => array( 'country' => 'Grenada', 'code' => '1-473', 'format' => '+1-473 XXX XXXX' ),
			'GU' => array( 'country' => 'Guam', 'code' => '1-671', 'format' => '+1-671 XXX XXXX' ),
			'GT' => array( 'country' => 'Guatemala', 'code' => '502', 'format' => '+502 X XXX XXXX' ),
			'GN' => array( 'country' => 'Guinea', 'code' => '224', 'format' => '+224 XX XXX XXXX' ),
			'GW' => array( 'country' => 'Guinea-Bissau', 'code' => '245', 'format' => '+245 XXX XXX XXX' ),
			'GY' => array( 'country' => 'Guyana', 'code' => '592', 'format' => '+592 XXX XXXX' ),
			'HT' => array( 'country' => 'Haiti', 'code' => '509', 'format' => '+509 XX XX XXXX' ),
			'HN' => array( 'country' => 'Honduras', 'code' => '504', 'format' => '+504 XXXX XXXX' ),
			'HK' => array( 'country' => 'Hong Kong', 'code' => '852', 'format' => '+852 XXXX XXXX' ),
			'HU' => array( 'country' => 'Hungary', 'code' => '36', 'format' => '+36 X XXX XXXX' ),
			'IS' => array( 'country' => 'Iceland', 'code' => '354', 'format' => '+354 XXX XXXX' ),
			'IN' => array( 'country' => 'India', 'code' => '91', 'format' => '+91 XXXXX XXXXX' ),
			'ID' => array( 'country' => 'Indonesia', 'code' => '62', 'format' => '+62 XXX XXXX XXXX' ),
			'IR' => array( 'country' => 'Iran', 'code' => '98', 'format' => '+98 XXX XXX XXXX' ),
			'IQ' => array( 'country' => 'Iraq', 'code' => '964', 'format' => '+964 XXX XXX XXXX' ),
			'IE' => array( 'country' => 'Ireland', 'code' => '353', 'format' => '+353 X XXX XXXX' ),
			'IL' => array( 'country' => 'Israel', 'code' => '972', 'format' => '+972 X XXX XXXX' ),
			'IT' => array( 'country' => 'Italy', 'code' => '39', 'format' => '+39 XXX XXX XXXX' ),
			'CI' => array( 'country' => 'Ivory Coast', 'code' => '225', 'format' => '+225 XX XXX XXXX' ),
			'JM' => array( 'country' => 'Jamaica', 'code' => '1-876', 'format' => '+1-876 XXX XXXX' ),
			'JP' => array( 'country' => 'Japan', 'code' => '81', 'format' => '+81 XX XXXX XXXX' ),
			'JO' => array( 'country' => 'Jordan', 'code' => '962', 'format' => '+962 X XXX XXXX' ),
			'KZ' => array( 'country' => 'Kazakhstan', 'code' => '7', 'format' => '+7 XXX XXX XXXX' ),
			'KE' => array( 'country' => 'Kenya', 'code' => '254', 'format' => '+254 XXX XXX XXX' ),
			'KI' => array( 'country' => 'Kiribati', 'code' => '686', 'format' => '+686 XXXX XXXX' ),
			'KP' => array( 'country' => 'North Korea', 'code' => '850', 'format' => '+850 X XXX XXXX' ),
			'KR' => array( 'country' => 'South Korea', 'code' => '82', 'format' => '+82 XX XXXX XXXX' ),
			'KW' => array( 'country' => 'Kuwait', 'code' => '965', 'format' => '+965 XXXX XXXX' ),
			'KG' => array( 'country' => 'Kyrgyzstan', 'code' => '996', 'format' => '+996 XXX XXX XXX' ),
			'LA' => array( 'country' => 'Laos', 'code' => '856', 'format' => '+856 XX XXX XXX' ),
			'LV' => array( 'country' => 'Latvia', 'code' => '371', 'format' => '+371 XX XXX XXXX' ),
			'LB' => array( 'country' => 'Lebanon', 'code' => '961', 'format' => '+961 X XXX XXX' ),
			'LS' => array( 'country' => 'Lesotho', 'code' => '266', 'format' => '+266 XXX XXXX' ),
			'LR' => array( 'country' => 'Liberia', 'code' => '231', 'format' => '+231 XX XXX XXXX' ),
			'LY' => array( 'country' => 'Libya', 'code' => '218', 'format' => '+218 XX XXX XXXX' ),
			'LI' => array( 'country' => 'Liechtenstein', 'code' => '423', 'format' => '+423 XXX XXXX' ),
			'LT' => array( 'country' => 'Lithuania', 'code' => '370', 'format' => '+370 X XXX XXXX' ),
			'LU' => array( 'country' => 'Luxembourg', 'code' => '352', 'format' => '+352 X XXX XXXX' ),
			'MO' => array( 'country' => 'Macau', 'code' => '853', 'format' => '+853 XXXX XXXX' ),
			'MK' => array( 'country' => 'Macedonia', 'code' => '389', 'format' => '+389 XX XXX XXX' ),
			'MG' => array( 'country' => 'Madagascar', 'code' => '261', 'format' => '+261 XX XX XXXXX' ),
			'MW' => array( 'country' => 'Malawi', 'code' => '265', 'format' => '+265 X XXX XXXX' ),
			'MY' => array( 'country' => 'Malaysia', 'code' => '60', 'format' => '+60 X XXX XXXX' ),
			'MV' => array( 'country' => 'Maldives', 'code' => '960', 'format' => '+960 XXX XXXX' ),
			'ML' => array( 'country' => 'Mali', 'code' => '223', 'format' => '+223 XX XX XXXX' ),
			'MT' => array( 'country' => 'Malta', 'code' => '356', 'format' => '+356 XX XX XX XX' ),
			'MH' => array( 'country' => 'Marshall Islands', 'code' => '692', 'format' => '+692 XXX XXXX' ),
			'MR' => array( 'country' => 'Mauritania', 'code' => '222', 'format' => '+222 XX XX XXXX' ),
			'MU' => array( 'country' => 'Mauritius', 'code' => '230', 'format' => '+230 XXX XXXX' ),
			'MX' => array( 'country' => 'Mexico', 'code' => '52', 'format' => '+52 XX XXXX XXXX' ),
			'FM' => array( 'country' => 'Micronesia', 'code' => '691', 'format' => '+691 XXX XXXX' ),
			'MD' => array( 'country' => 'Moldova', 'code' => '373', 'format' => '+373 XXX XXX XX' ),
			'MC' => array( 'country' => 'Monaco', 'code' => '377', 'format' => '+377 XXX XX XX' ),
			'MN' => array( 'country' => 'Mongolia', 'code' => '976', 'format' => '+976 XX XXXX XXXX' ),
			'ME' => array( 'country' => 'Montenegro', 'code' => '382', 'format' => '+382 XX XXX XXX' ),
			'MS' => array( 'country' => 'Montserrat', 'code' => '1-664', 'format' => '+1-664 XXX XXXX' ),
			'MA' => array( 'country' => 'Morocco', 'code' => '212', 'format' => '+212 XX XXX XXXX' ),
			'MZ' => array( 'country' => 'Mozambique', 'code' => '258', 'format' => '+258 XX XXX XXX' ),
			'MM' => array( 'country' => 'Myanmar', 'code' => '95', 'format' => '+95 X XXX XXXX' ),
			'NA' => array( 'country' => 'Namibia', 'code' => '264', 'format' => '+264 XX XXX XXXX' ),
			'NR' => array( 'country' => 'Nauru', 'code' => '674', 'format' => '+674 XXX XXX' ),
			'NP' => array( 'country' => 'Nepal', 'code' => '977', 'format' => '+977 X XXX XXX' ),
			'NL' => array( 'country' => 'Netherlands', 'code' => '31', 'format' => '+31 X XXX XXXX' ),
			'NZ' => array( 'country' => 'New Zealand', 'code' => '64', 'format' => '+64 XX XXX XXXX' ),
			'NI' => array( 'country' => 'Nicaragua', 'code' => '505', 'format' => '+505 XXXX XXXX' ),
			'NE' => array( 'country' => 'Niger', 'code' => '227', 'format' => '+227 XX XX XXXX' ),
			'NG' => array( 'country' => 'Nigeria', 'code' => '234', 'format' => '+234 XXX XXX XXXX' ),
			'NU' => array( 'country' => 'Niue', 'code' => '683', 'format' => '+683 XXX XXXX' ),
			'NF' => array( 'country' => 'Norfolk Island', 'code' => '672', 'format' => '+672 X XXXX' ),
			'MP' => array( 'country' => 'Northern Mariana Islands', 'code' => '1-670', 'format' => '+1-670 XXX XXXX' ),
			'NO' => array( 'country' => 'Norway', 'code' => '47', 'format' => '+47 XX XX XX XX' ),
			'OM' => array( 'country' => 'Oman', 'code' => '968', 'format' => '+968 XX XXX XXX' ),
			'PK' => array( 'country' => 'Pakistan', 'code' => '92', 'format' => '+92 XXX XXX XXXX' ),
			'PW' => array( 'country' => 'Palau', 'code' => '680', 'format' => '+680 XXX XXXX' ),
			'PS' => array( 'country' => 'Palestine', 'code' => '970', 'format' => '+970 XX XXX XXXX' ),
			'PA' => array( 'country' => 'Panama', 'code' => '507', 'format' => '+507 XXX XXXX' ),
			'PG' => array( 'country' => 'Papua New Guinea', 'code' => '675', 'format' => '+675 XXX XXXX' ),
			'PY' => array( 'country' => 'Paraguay', 'code' => '595', 'format' => '+595 XXX XXX XXX' ),
			'PE' => array( 'country' => 'Peru', 'code' => '51', 'format' => '+51 X XXX XXXX' ),
			'PH' => array( 'country' => 'Philippines', 'code' => '63', 'format' => '+63 XXX XXX XXXX' ),
			'PL' => array( 'country' => 'Poland', 'code' => '48', 'format' => '+48 XXX XXX XXX' ),
			'PT' => array( 'country' => 'Portugal', 'code' => '351', 'format' => '+351 X XXX XXXX' ),
			'PR' => array( 'country' => 'Puerto Rico', 'code' => '1-787', 'format' => '+1-787 XXX XXXX' ),
			'QA' => array( 'country' => 'Qatar', 'code' => '974', 'format' => '+974 XXX XXX XXX' ),
			'RE' => array( 'country' => 'Reunion', 'code' => '262', 'format' => '+262 XXXXXXXX' ),
			'RO' => array( 'country' => 'Romania', 'code' => '40', 'format' => '+40 XXX XXX XXX' ),
			'RU' => array( 'country' => 'Russia', 'code' => '7', 'format' => '+7 XXX XXX XX XX' ),
			'RW' => array( 'country' => 'Rwanda', 'code' => '250', 'format' => '+250 XXX XXX XXX' ),
			'KN' => array( 'country' => 'Saint Kitts and Nevis', 'code' => '1-869', 'format' => '+1-869 XXX XXXX' ),
			'LC' => array( 'country' => 'Saint Lucia', 'code' => '1-758', 'format' => '+1-758 XXX XXXX' ),
			'VC' => array( 'country' => 'Saint Vincent and the Grenadines', 'code' => '1-784', 'format' => '+1-784 XXX XXXX' ),
			'WS' => array( 'country' => 'Samoa', 'code' => '685', 'format' => '+685 XX XXX' ),
			'SM' => array( 'country' => 'San Marino', 'code' => '378', 'format' => '+378 XXX XXX XXX' ),
			'ST' => array( 'country' => 'Sao Tome and Principe', 'code' => '239', 'format' => '+239 XX XXX XXXX' ),
			'SA' => array( 'country' => 'Saudi Arabia', 'code' => '966', 'format' => '+966 X XXX XXXX' ),
			'SN' => array( 'country' => 'Senegal', 'code' => '221', 'format' => '+221 XX XXX XXXX' ),
			'RS' => array( 'country' => 'Serbia', 'code' => '381', 'format' => '+381 XX XXX XXXX' ),
			'SC' => array( 'country' => 'Seychelles', 'code' => '248', 'format' => '+248 X XXX XXX' ),
			'SL' => array( 'country' => 'Sierra Leone', 'code' => '232', 'format' => '+232 XX XXX XXX' ),
			'SG' => array( 'country' => 'Singapore', 'code' => '65', 'format' => '+65 XXXX XXXX' ),
			'SK' => array( 'country' => 'Slovakia', 'code' => '421', 'format' => '+421 XXX XXX XXX' ),
			'SI' => array( 'country' => 'Slovenia', 'code' => '386', 'format' => '+386 XX XXX XXX' ),
			'SB' => array( 'country' => 'Solomon Islands', 'code' => '677', 'format' => '+677 XXX XXXX' ),
			'SO' => array( 'country' => 'Somalia', 'code' => '252', 'format' => '+252 X XXX XXX' ),
			'ZA' => array( 'country' => 'South Africa', 'code' => '27', 'format' => '+27 XX XXX XXXX' ),
			'ES' => array( 'country' => 'Spain', 'code' => '34', 'format' => '+34 XXX XXX XXX' ),
			'LK' => array( 'country' => 'Sri Lanka', 'code' => '94', 'format' => '+94 XX XXX XXXX' ),
			'SD' => array( 'country' => 'Sudan', 'code' => '249', 'format' => '+249 XXX XXX XXXX' ),
			'SR' => array( 'country' => 'Suriname', 'code' => '597', 'format' => '+597 XXX XXXX' ),
			'SZ' => array( 'country' => 'Swaziland', 'code' => '268', 'format' => '+268 XXXX XXXX' ),
			'SE' => array( 'country' => 'Sweden', 'code' => '46', 'format' => '+46 XX XXX XXXX' ),
			'CH' => array( 'country' => 'Switzerland', 'code' => '41', 'format' => '+41 XX XXX XXXX' ),
			'SY' => array( 'country' => 'Syria', 'code' => '963', 'format' => '+963 XX XXX XXXX' ),
			'TW' => array( 'country' => 'Taiwan', 'code' => '886', 'format' => '+886 X XXX XXXX' ),
			'TJ' => array( 'country' => 'Tajikistan', 'code' => '992', 'format' => '+992 XX XXX XXXX' ),
			'TZ' => array( 'country' => 'Tanzania', 'code' => '255', 'format' => '+255 XX XXX XXXX' ),
			'TH' => array( 'country' => 'Thailand', 'code' => '66', 'format' => '+66 X XXXX XXXX' ),
			'TG' => array( 'country' => 'Togo', 'code' => '228', 'format' => '+228 XX XXX XXX' ),
			'TO' => array( 'country' => 'Tonga', 'code' => '676', 'format' => '+676 XXX XXXX' ),
			'TT' => array( 'country' => 'Trinidad and Tobago', 'code' => '1-868', 'format' => '+1-868 XXX XXXX' ),
			'TN' => array( 'country' => 'Tunisia', 'code' => '216', 'format' => '+216 XX XXX XXX' ),
			'TR' => array( 'country' => 'Turkey', 'code' => '90', 'format' => '+90 XXX XXX XXXX' ),
			'TM' => array( 'country' => 'Turkmenistan', 'code' => '993', 'format' => '+993 XX XXX XXX' ),
			'TV' => array( 'country' => 'Tuvalu', 'code' => '688', 'format' => '+688 XXX XXX' ),
			'UG' => array( 'country' => 'Uganda', 'code' => '256', 'format' => '+256 XXX XXX XXX' ),
			'UA' => array( 'country' => 'Ukraine', 'code' => '380', 'format' => '+380 XX XXX XXXX' ),
			'AE' => array( 'country' => 'United Arab Emirates', 'code' => '971', 'format' => '+971 XX XXX XXXX' ),
			'GB' => array( 'country' => 'United Kingdom', 'code' => '44', 'format' => '+44 XX XXXX XXXX' ),
			'US' => array( 'country' => 'United States', 'code' => '1', 'format' => '+1 XXX XXX XXXX' ),
			'UY' => array( 'country' => 'Uruguay', 'code' => '598', 'format' => '+598 X XXX XXXX' ),
			'UZ' => array( 'country' => 'Uzbekistan', 'code' => '998', 'format' => '+998 XX XXX XXXX' ),
			'VU' => array( 'country' => 'Vanuatu', 'code' => '678', 'format' => '+678 XXX XXX' ),
			'VA' => array( 'country' => 'Vatican City', 'code' => '379', 'format' => '+379 XXX XXX XXX' ),
			'VE' => array( 'country' => 'Venezuela', 'code' => '58', 'format' => '+58 XXX XXX XXXX' ),
			'VN' => array( 'country' => 'Vietnam', 'code' => '84', 'format' => '+84 XXX XXX XXXX' ),
			'YE' => array( 'country' => 'Yemen', 'code' => '967', 'format' => '+967 XXX XXX XXX' ),
			'ZM' => array( 'country' => 'Zambia', 'code' => '260', 'format' => '+260 XX XXX XXX' ),
			'ZW' => array( 'country' => 'Zimbabwe', 'code' => '263', 'format' => '+263 XX XXX XXX' ),
		);
		// @codingStandardsIgnoreEnd

		return $country_codes[ $country_code ];
	}

	/**
	 * Get the currency decimals.
	 *
	 * @access public
	 * @since 4.1.10
	 * @param string $currency Currency code.
	 * @return string
	 */
	public function get_currency_decimals( $currency ) {
		$currency_decimals = array(
			'AED' => 2,
			'AFN' => 2,
			'ALL' => 2,
			'AMD' => 2,
			'ANG' => 2,
			'AOA' => 2,
			'ARS' => 2,
			'AUD' => 2,
			'AWG' => 2,
			'AZN' => 2,
			'BAM' => 2,
			'BBD' => 2,
			'BDT' => 2,
			'BGN' => 2,
			'BHD' => 3,
			'BIF' => 0,
			'BMD' => 2,
			'BND' => 2,
			'BOB' => 2,
			'BRL' => 2,
			'BSD' => 2,
			'BTN' => 2,
			'BWP' => 2,
			'BYN' => 2,
			'BZD' => 2,
			'CAD' => 2,
			'CDF' => 2,
			'CHF' => 2,
			'CLP' => 0,
			'CNY' => 2,
			'COP' => 2,
			'CRC' => 2,
			'CUP' => 2,
			'CVE' => 2,
			'CZK' => 2,
			'DJF' => 0,
			'DKK' => 2,
			'DOP' => 2,
			'DZD' => 2,
			'EGP' => 2,
			'ERN' => 2,
			'ETB' => 2,
			'EUR' => 2,
			'FJD' => 2,
			'FKP' => 2,
			'GBP' => 2,
			'GEL' => 2,
			'GHS' => 2,
			'GIP' => 2,
			'GMD' => 2,
			'GNF' => 0,
			'GTQ' => 2,
			'GYD' => 2,
			'HKD' => 2,
			'HNL' => 2,
			'HRK' => 2,
			'HTG' => 2,
			'HUF' => 2,
			'IDR' => 2,
			'ILS' => 2,
			'INR' => 2,
			'IQD' => 3,
			'IRR' => 2,
			'ISK' => 0,
			'JMD' => 2,
			'JOD' => 3,
			'JPY' => 0,
			'KES' => 2,
			'KGS' => 2,
			'KHR' => 2,
			'KID' => 2,
			'KMF' => 0,
			'KRW' => 0,
			'KWD' => 3,
			'KYD' => 2,
			'KZT' => 2,
			'LAK' => 2,
			'LBP' => 2,
			'LKR' => 2,
			'LRD' => 2,
			'LSL' => 2,
			'LYD' => 3,
			'MAD' => 2,
			'MDL' => 2,
			'MGA' => 2,
			'MKD' => 2,
			'MMK' => 2,
			'MNT' => 2,
			'MOP' => 2,
			'MRO' => 2,
			'MUR' => 2,
			'MVR' => 2,
			'MWK' => 2,
			'MXN' => 2,
			'MYR' => 2,
			'MZN' => 2,
			'NAD' => 2,
			'NGN' => 2,
			'NIO' => 2,
			'NOK' => 2,
			'NPR' => 2,
			'NZD' => 2,
			'OMR' => 3,
			'PAB' => 2,
			'PEN' => 2,
			'PGK' => 2,
			'PHP' => 2,
			'PKR' => 2,
			'PLN' => 2,
			'PYG' => 0,
			'QAR' => 2,
			'RON' => 2,
			'RSD' => 2,
			'RUB' => 2,
			'RWF' => 0,
			'SAR' => 2,
			'SBD' => 2,
			'SCR' => 2,
			'SDG' => 2,
			'SEK' => 2,
			'SGD' => 2,
			'SHP' => 2,
			'SLL' => 2,
			'SOS' => 2,
			'SRD' => 2,
			'SSP' => 2,
			'STD' => 2,
			'SYP' => 2,
			'SZL' => 2,
			'THB' => 2,
			'TJS' => 2,
			'TMT' => 2,
			'TND' => 3,
			'TOP' => 2,
			'TRY' => 2,
			'TTD' => 2,
			'TWD' => 2,
			'TZS' => 2,
			'UAH' => 2,
			'UGX' => 0,
			'USD' => 2,
			'UYU' => 2,
			'UZS' => 2,
			'VEF' => 2,
			'VND' => 0,
			'VUV' => 0,
			'WST' => 2,
			'XAF' => 0,
			'XCD' => 2,
			'XOF' => 0,
			'XPF' => 0,
			'YER' => 2,
			'ZAR' => 2,
			'ZMW' => 2,
		);

		return isset( $currency_decimals[ $currency ] ) ? $currency_decimals[ $currency ] : 0;
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 4.1.10
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
	 * Return the request data sent to the action.
	 *
	 * @access public
	 * @since 4.3.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Number_Formatter();
