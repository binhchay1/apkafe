<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Options;

use ahrefs\AhrefsSeo_Vendor\League\ISO3166\ISO3166;

/**
 * Options class.
 *
 * @since 0.9.6
 */
class Countries {

	protected const OPTION_COUNTRY      = 'ahrefs-seo-content-country';
	protected const DEFAULT_COUNTRY     = ''; // empty value = All countries.
	protected const DEFAULT_CODE_AHREFS = 'us';

	/**
	 * Set country code. Validate value.
	 *
	 * @param string|null $country_code3 Alpha3 country code. Null or empty string for All countries.
	 * @return void
	 */
	public function set_country( ?string $country_code3 ) : void {
		if ( is_null( self::get_country_fields( $country_code3 ?? '' ) ) ) {
			$country_code3 = self::DEFAULT_COUNTRY;
		}
		update_option( self::OPTION_COUNTRY, $country_code3 );
	}

	/**
	 * Get value of country code.
	 *
	 * @return string Alpha3 country code or empty string (All countries).
	 */
	public function get_country() : string {
		$result = get_option( self::OPTION_COUNTRY, null );
		return is_string( $result ) ? substr( $result, 0, 3 ) : '';
	}

	/**
	 * Get value of country code for ahrefs.
	 *
	 * @param string $country_code3 Alpha3 country code.
	 * @return string Alpha2 country code used in ahrefs.
	 */
	public static function get_country_code_ahrefs( string $country_code3 ) : string {
		$country_fields = self::get_country_fields( $country_code3 );
		return ! is_null( $country_fields ) ? $country_fields['ahrefs'] : self::DEFAULT_CODE_AHREFS;
	}

	/**
	 * Get value of country name.
	 *
	 * @param string|null $country_code3 Google code of country or null for current value from settings.
	 * @return string Country name or "All countries".
	 */
	public function get_country_name( ?string $country_code3 = null ) : string {
		$result = self::get_country_fields( $country_code3 ?? '' );
		return is_array( $result ) ? (string) $result['name'] : __( 'All countries', 'ahrefs-seo' );
	}

	/**
	 * Get list of countries for choose from Google
	 *
	 * @return array<string, string> Index is Google code, value is country name.
	 */
	public function get_google_list() : array {
		$data   = self::get_full_list();
		$result = [];
		foreach ( $data as $country_fields ) {
			$result[ "{$country_fields['google']}" ] = $country_fields['name'];
		}
		return $result;
	}

	/**
	 * Get value of country name.
	 *
	 * @param string $country_code3 Google code of country.
	 * @return array|null Country fields or null if not found.
	 */
	protected static function get_country_fields( string $country_code3 ) : ?array {
		if ( '' !== $country_code3 ) {
				$data = self::get_full_list();
			foreach ( $data as $country_fields ) {
				if ( $country_fields['google'] === $country_code3 ) {
					return $country_fields;
				}
			}
		}
		return null;
	}

	/**
	 * Get full list of countries with code
	 *
	 * @return array<array> Countries list with the 'ahrefs', 'google' and 'name' indexes.
	 */
	protected static function get_full_list() : array {
		return [
			[
				'ahrefs' => 'us',
				'google' => '',
				'name'   => _x( 'All countries', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'af',
				'google' => 'AFG',
				'name'   => _x( 'Afghanistan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'al',
				'google' => 'ALB',
				'name'   => _x( 'Albania', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'dz',
				'google' => 'DZA',
				'name'   => _x( 'Algeria', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'as',
				'google' => 'ASM',
				'name'   => _x( 'American Samoa', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ad',
				'google' => 'AND',
				'name'   => _x( 'Andorra', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ao',
				'google' => 'AGO',
				'name'   => _x( 'Angola', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ai',
				'google' => 'AIA',
				'name'   => _x( 'Anguilla', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ag',
				'google' => 'ATG',
				'name'   => _x( 'Antigua and Barbuda', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ar',
				'google' => 'ARG',
				'name'   => _x( 'Argentina', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'am',
				'google' => 'ARM',
				'name'   => _x( 'Armenia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'au',
				'google' => 'AUS',
				'name'   => _x( 'Australia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'at',
				'google' => 'AUT',
				'name'   => _x( 'Austria', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'az',
				'google' => 'AZE',
				'name'   => _x( 'Azerbaijan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bs',
				'google' => 'BHS',
				'name'   => _x( 'Bahamas', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bh',
				'google' => 'BHR',
				'name'   => _x( 'Bahrain', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bd',
				'google' => 'BGD',
				'name'   => _x( 'Bangladesh', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'by',
				'google' => 'BLR',
				'name'   => _x( 'Belarus', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'be',
				'google' => 'BEL',
				'name'   => _x( 'Belgium', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bz',
				'google' => 'BLZ',
				'name'   => _x( 'Belize', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bj',
				'google' => 'BEN',
				'name'   => _x( 'Benin', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bt',
				'google' => 'BTN',
				'name'   => _x( 'Bhutan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bo',
				'google' => 'BOL',
				'name'   => _x( 'Bolivia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ba',
				'google' => 'BIH',
				'name'   => _x( 'Bosnia and Herzegovina', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bw',
				'google' => 'BWA',
				'name'   => _x( 'Botswana', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'br',
				'google' => 'BRA',
				'name'   => _x( 'Brazil', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bn',
				'google' => 'BRN',
				'name'   => _x( 'Brunei Darussalam', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bg',
				'google' => 'BGR',
				'name'   => _x( 'Bulgaria', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bf',
				'google' => 'BFA',
				'name'   => _x( 'Burkina Faso', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'bi',
				'google' => 'BDI',
				'name'   => _x( 'Burundi', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'kh',
				'google' => 'KHM',
				'name'   => _x( 'Cambodia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cm',
				'google' => 'CMR',
				'name'   => _x( 'Cameroon', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ca',
				'google' => 'CAN',
				'name'   => _x( 'Canada', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cv',
				'google' => 'CPV',
				'name'   => _x( 'Cape Verde', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cf',
				'google' => 'CAF',
				'name'   => _x( 'Central African Republic', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'td',
				'google' => 'TCD',
				'name'   => _x( 'Chad', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cl',
				'google' => 'CHL',
				'name'   => _x( 'Chile', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'co',
				'google' => 'COL',
				'name'   => _x( 'Colombia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cg',
				'google' => 'COG',
				'name'   => _x( 'Congo', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cd',
				'google' => 'COD',
				'name'   => _x( 'Congo, Democratic Republic', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ck',
				'google' => 'COK',
				'name'   => _x( 'Cook Islands', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cr',
				'google' => 'CRI',
				'name'   => _x( 'Costa Rica', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ci',
				'google' => 'CIV',
				'name'   => _x( "Cote D'Ivoire", 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'hr',
				'google' => 'HRV',
				'name'   => _x( 'Croatia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cy',
				'google' => 'CYP',
				'name'   => _x( 'Cyprus', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'cz',
				'google' => 'CZE',
				'name'   => _x( 'Czech Republic', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'dk',
				'google' => 'DNK',
				'name'   => _x( 'Denmark', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'dj',
				'google' => 'DJI',
				'name'   => _x( 'Djibouti', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'dm',
				'google' => 'DMA',
				'name'   => _x( 'Dominica', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'do',
				'google' => 'DOM',
				'name'   => _x( 'Dominican Republic', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ec',
				'google' => 'ECU',
				'name'   => _x( 'Ecuador', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'eg',
				'google' => 'EGY',
				'name'   => _x( 'Egypt', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sv',
				'google' => 'SLV',
				'name'   => _x( 'El Salvador', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ee',
				'google' => 'EST',
				'name'   => _x( 'Estonia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'et',
				'google' => 'ETH',
				'name'   => _x( 'Ethiopia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'fj',
				'google' => 'FJI',
				'name'   => _x( 'Fiji', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'fi',
				'google' => 'FIN',
				'name'   => _x( 'Finland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'fr',
				'google' => 'FRA',
				'name'   => _x( 'France', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ga',
				'google' => 'GAB',
				'name'   => _x( 'Gabon', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gm',
				'google' => 'GMB',
				'name'   => _x( 'Gambia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ge',
				'google' => 'GEO',
				'name'   => _x( 'Georgia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'de',
				'google' => 'DEU',
				'name'   => _x( 'Germany', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gh',
				'google' => 'GHA',
				'name'   => _x( 'Ghana', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gi',
				'google' => 'GIB',
				'name'   => _x( 'Gibraltar', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gr',
				'google' => 'GRC',
				'name'   => _x( 'Greece', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gl',
				'google' => 'GRL',
				'name'   => _x( 'Greenland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gp',
				'google' => 'GLP',
				'name'   => _x( 'Guadeloupe', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gt',
				'google' => 'GTM',
				'name'   => _x( 'Guatemala', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gg',
				'google' => 'GGY',
				'name'   => _x( 'Guernsey', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gy',
				'google' => 'GUY',
				'name'   => _x( 'Guyana', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ht',
				'google' => 'HTI',
				'name'   => _x( 'Haiti', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'hn',
				'google' => 'HND',
				'name'   => _x( 'Honduras', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'hk',
				'google' => 'HKG',
				'name'   => _x( 'Hong Kong', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'hu',
				'google' => 'HUN',
				'name'   => _x( 'Hungary', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'is',
				'google' => 'ISL',
				'name'   => _x( 'Iceland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'in',
				'google' => 'IND',
				'name'   => _x( 'India', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'id',
				'google' => 'IDN',
				'name'   => _x( 'Indonesia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'iq',
				'google' => 'IRQ',
				'name'   => _x( 'Iraq', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ie',
				'google' => 'IRL',
				'name'   => _x( 'Ireland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'il',
				'google' => 'ISR',
				'name'   => _x( 'Israel', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'it',
				'google' => 'ITA',
				'name'   => _x( 'Italy', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'jm',
				'google' => 'JAM',
				'name'   => _x( 'Jamaica', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'jp',
				'google' => 'JPN',
				'name'   => _x( 'Japan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'je',
				'google' => 'JEY',
				'name'   => _x( 'Jersey', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'jo',
				'google' => 'JOR',
				'name'   => _x( 'Jordan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'kz',
				'google' => 'KAZ',
				'name'   => _x( 'Kazakhstan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ke',
				'google' => 'KEN',
				'name'   => _x( 'Kenya', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ki',
				'google' => 'KIR',
				'name'   => _x( 'Kiribati', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'kr',
				'google' => 'KOR',
				'name'   => _x( 'Korea', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'kw',
				'google' => 'KWT',
				'name'   => _x( 'Kuwait', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'kg',
				'google' => 'KGZ',
				'name'   => _x( 'Kyrgyzstan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'la',
				'google' => 'LAO',
				'name'   => _x( "Lao People's Democratic Republic", 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'lv',
				'google' => 'LVA',
				'name'   => _x( 'Latvia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'lb',
				'google' => 'LBN',
				'name'   => _x( 'Lebanon', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ls',
				'google' => 'LSO',
				'name'   => _x( 'Lesotho', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ly',
				'google' => 'LBY',
				'name'   => _x( 'Libyan Arab Jamahiriya', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'li',
				'google' => 'LIE',
				'name'   => _x( 'Liechtenstein', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'lt',
				'google' => 'LTU',
				'name'   => _x( 'Lithuania', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'lu',
				'google' => 'LUX',
				'name'   => _x( 'Luxembourg', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mk',
				'google' => 'MKD',
				'name'   => _x( 'Macedonia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mg',
				'google' => 'MDG',
				'name'   => _x( 'Madagascar', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mw',
				'google' => 'MWI',
				'name'   => _x( 'Malawi', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'my',
				'google' => 'MYS',
				'name'   => _x( 'Malaysia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mv',
				'google' => 'MDV',
				'name'   => _x( 'Maldives', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ml',
				'google' => 'MLI',
				'name'   => _x( 'Mali', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mt',
				'google' => 'MLT',
				'name'   => _x( 'Malta', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mu',
				'google' => 'MUS',
				'name'   => _x( 'Mauritius', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mx',
				'google' => 'MEX',
				'name'   => _x( 'Mexico', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'fm',
				'google' => 'FSM',
				'name'   => _x( 'Micronesia, Federated States of', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'md',
				'google' => 'MDA',
				'name'   => _x( 'Moldova', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mn',
				'google' => 'MNG',
				'name'   => _x( 'Mongolia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'me',
				'google' => 'MNE',
				'name'   => _x( 'Montenegro', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ms',
				'google' => 'MSR',
				'name'   => _x( 'Montserrat', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ma',
				'google' => 'MAR',
				'name'   => _x( 'Morocco', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mz',
				'google' => 'MOZ',
				'name'   => _x( 'Mozambique', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'mm',
				'google' => 'MMR',
				'name'   => _x( 'Myanmar', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'na',
				'google' => 'NAM',
				'name'   => _x( 'Namibia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'nr',
				'google' => 'NRU',
				'name'   => _x( 'Nauru', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'np',
				'google' => 'NPL',
				'name'   => _x( 'Nepal', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'nl',
				'google' => 'NLD',
				'name'   => _x( 'Netherlands', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'nz',
				'google' => 'NZL',
				'name'   => _x( 'New Zealand', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ni',
				'google' => 'NIC',
				'name'   => _x( 'Nicaragua', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ne',
				'google' => 'NER',
				'name'   => _x( 'Niger', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ng',
				'google' => 'NGA',
				'name'   => _x( 'Nigeria', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'nu',
				'google' => 'NIU',
				'name'   => _x( 'Niue', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'no',
				'google' => 'NOR',
				'name'   => _x( 'Norway', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'om',
				'google' => 'OMN',
				'name'   => _x( 'Oman', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pk',
				'google' => 'PAK',
				'name'   => _x( 'Pakistan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ps',
				'google' => 'PSE',
				'name'   => _x( 'Palestinian Territory, Occupied', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pa',
				'google' => 'PAN',
				'name'   => _x( 'Panama', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pg',
				'google' => 'PNG',
				'name'   => _x( 'Papua New Guinea', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'py',
				'google' => 'PRY',
				'name'   => _x( 'Paraguay', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pe',
				'google' => 'PER',
				'name'   => _x( 'Peru', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ph',
				'google' => 'PHL',
				'name'   => _x( 'Philippines', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pn',
				'google' => 'PCN',
				'name'   => _x( 'Pitcairn', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pl',
				'google' => 'POL',
				'name'   => _x( 'Poland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pt',
				'google' => 'PRT',
				'name'   => _x( 'Portugal', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'pr',
				'google' => 'PRI',
				'name'   => _x( 'Puerto Rico', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'qa',
				'google' => 'QAT',
				'name'   => _x( 'Qatar', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ro',
				'google' => 'ROU',
				'name'   => _x( 'Romania', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ru',
				'google' => 'RUS',
				'name'   => _x( 'Russian Federation', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'rw',
				'google' => 'RWA',
				'name'   => _x( 'Rwanda', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sh',
				'google' => 'SHN',
				'name'   => _x( 'Saint Helena', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'vc',
				'google' => 'VCT',
				'name'   => _x( 'Saint Vincent and Grenadines', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ws',
				'google' => 'WSM',
				'name'   => _x( 'Samoa', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sm',
				'google' => 'SMR',
				'name'   => _x( 'San Marino', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'st',
				'google' => 'STP',
				'name'   => _x( 'Sao Tome and Principe', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sa',
				'google' => 'SAU',
				'name'   => _x( 'Saudi Arabia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sn',
				'google' => 'SEN',
				'name'   => _x( 'Senegal', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'rs',
				'google' => 'SRB',
				'name'   => _x( 'Serbia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sc',
				'google' => 'SYC',
				'name'   => _x( 'Seychelles', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sl',
				'google' => 'SLE',
				'name'   => _x( 'Sierra Leone', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sg',
				'google' => 'SGP',
				'name'   => _x( 'Singapore', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sk',
				'google' => 'SVK',
				'name'   => _x( 'Slovakia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'si',
				'google' => 'SVN',
				'name'   => _x( 'Slovenia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sb',
				'google' => 'SLB',
				'name'   => _x( 'Solomon Islands', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'so',
				'google' => 'SOM',
				'name'   => _x( 'Somalia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'za',
				'google' => 'ZAF',
				'name'   => _x( 'South Africa', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'es',
				'google' => 'ESP',
				'name'   => _x( 'Spain', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'lk',
				'google' => 'LKA',
				'name'   => _x( 'Sri Lanka', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'sr',
				'google' => 'SUR',
				'name'   => _x( 'Suriname', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'se',
				'google' => 'SWE',
				'name'   => _x( 'Sweden', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ch',
				'google' => 'CHE',
				'name'   => _x( 'Switzerland', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tw',
				'google' => 'TWN',
				'name'   => _x( 'Taiwan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tj',
				'google' => 'TJK',
				'name'   => _x( 'Tajikistan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tz',
				'google' => 'TZA',
				'name'   => _x( 'Tanzania', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'th',
				'google' => 'THA',
				'name'   => _x( 'Thailand', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tl',
				'google' => 'TLS',
				'name'   => _x( 'Timor-Leste', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tg',
				'google' => 'TGO',
				'name'   => _x( 'Togo', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tk',
				'google' => 'TKL',
				'name'   => _x( 'Tokelau', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'to',
				'google' => 'TON',
				'name'   => _x( 'Tonga', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tt',
				'google' => 'TTO',
				'name'   => _x( 'Trinidad and Tobago', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tn',
				'google' => 'TUN',
				'name'   => _x( 'Tunisia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tr',
				'google' => 'TUR',
				'name'   => _x( 'Turkey', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'tm',
				'google' => 'TKM',
				'name'   => _x( 'Turkmenistan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ug',
				'google' => 'UGA',
				'name'   => _x( 'Uganda', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ua',
				'google' => 'UKR',
				'name'   => _x( 'Ukraine', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'ae',
				'google' => 'ARE',
				'name'   => _x( 'United Arab Emirates', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'gb',
				'google' => 'GBR',
				'name'   => _x( 'United Kingdom', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'us',
				'google' => 'USA',
				'name'   => _x( 'United States', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'uy',
				'google' => 'URY',
				'name'   => _x( 'Uruguay', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'uz',
				'google' => 'UZB',
				'name'   => _x( 'Uzbekistan', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'vu',
				'google' => 'VUT',
				'name'   => _x( 'Vanuatu', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 've',
				'google' => 'VEN',
				'name'   => _x( 'Venezuela', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'vn',
				'google' => 'VNM',
				'name'   => _x( 'Vietnam', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'vg',
				'google' => 'VGB',
				'name'   => _x( 'Virgin Islands, British', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'vi',
				'google' => 'VIR',
				'name'   => _x( 'Virgin Islands, U.S.', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'zm',
				'google' => 'ZMB',
				'name'   => _x( 'Zambia', 'Country name', 'ahrefs-seo' ),
			],
			[
				'ahrefs' => 'zw',
				'google' => 'ZWE',
				'name'   => _x( 'Zimbabwe', 'Country name', 'ahrefs-seo' ),
			],
		];
	}
}
