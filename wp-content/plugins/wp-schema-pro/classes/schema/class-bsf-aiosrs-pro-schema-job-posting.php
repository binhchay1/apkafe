<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Job_Posting' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Job_Posting {

		/**
		 * Render Schema.
		 *
		 * @param  array $data Meta Data.
		 * @param  array $post Current Post Array.
		 * @return array
		 */
		public static function render( $data, $post ) {
			$schema = array();

			$schema['@context'] = 'https://schema.org';
			$schema['@type']    = 'JobPosting';

			if ( isset( $data['title'] ) && ! empty( $data['title'] ) ) {
				$schema['title'] = esc_html( wp_strip_all_tags( $data['title'] ) );
			}

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$schema['description'] = esc_html( wp_strip_all_tags( $data['description'] ) );
			}

			if ( isset( $data['start-date'] ) && ! empty( $data['start-date'] ) ) {
				$schema['datePosted'] = esc_html( wp_strip_all_tags( $data['start-date'] ) );
			}

			if ( isset( $data['expiry-date'] ) && ! empty( $data['expiry-date'] ) ) {
				$schema['validThrough'] = esc_html( wp_strip_all_tags( $data['expiry-date'] ) );
			}

			if ( isset( $data['job-type'] ) && ! empty( $data['job-type'] ) ) {
				$schema['employmentType'] = esc_html( wp_strip_all_tags( $data['job-type'] ) );
			}

			if ( isset( $data['education-requirements'] ) && ! empty( $data['education-requirements'] ) ) {
				$schema['educationRequirements'] = esc_html( wp_strip_all_tags( $data['education-requirements'] ) );
			}

			if ( isset( $data['experience-requirements'] ) && ! empty( $data['experience-requirements'] ) ) {
				$schema['experienceRequirements'] = esc_html( wp_strip_all_tags( $data['experience-requirements'] ) );
			}

			if ( isset( $data['industry'] ) && ! empty( $data['industry'] ) ) {
				$schema['industry'] = esc_html( wp_strip_all_tags( $data['industry'] ) );
			}

			if ( isset( $data['qualifications'] ) && ! empty( $data['qualifications'] ) ) {
				$schema['qualifications'] = esc_html( wp_strip_all_tags( $data['qualifications'] ) );
			}

			if ( isset( $data['responsibilities'] ) && ! empty( $data['responsibilities'] ) ) {
				$schema['responsibilities'] = esc_html( wp_strip_all_tags( $data['responsibilities'] ) );
			}

			if ( isset( $data['skills'] ) && ! empty( $data['skills'] ) ) {
				$schema['skills'] = esc_html( wp_strip_all_tags( $data['skills'] ) );
			}

			if ( isset( $data['work-hours'] ) && ! empty( $data['work-hours'] ) ) {
				$schema['workHours'] = esc_html( wp_strip_all_tags( $data['work-hours'] ) );
			}

			if ( ( isset( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) ||
				( isset( $data['same-as'] ) && ! empty( $data['same-as'] ) ) ) {

				$schema['hiringOrganization']['@type'] = 'Organization';

				if ( isset( $data['orgnization-name'] ) && ! empty( $data['orgnization-name'] ) ) {
					$schema['hiringOrganization']['name'] = esc_html( wp_strip_all_tags( $data['orgnization-name'] ) );
				}
				if ( isset( $data['same-as'] ) && ! empty( $data['same-as'] ) ) {
					$schema['hiringOrganization']['sameAs'] = esc_url( $data['same-as'] );
				}
				if ( isset( $data['organization-logo'] ) && ! empty( $data['organization-logo'] ) ) {

					$schema['hiringOrganization']['logo'] = BSF_AIOSRS_Pro_Schema_Template::get_image_schema( $data['organization-logo'], 'ImageObject' );
				}
			}

			if ( ( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) ||
				( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) ||
				( isset( $data['location-postal'] ) && ! empty( $data['location-postal'] ) ) ||
				( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) ||
				( isset( $data['location-country'] ) && ! empty( $data['location-country'] ) ) ) {

				$schema['jobLocation']['@type']            = 'Place';
				$schema['jobLocation']['address']['@type'] = 'PostalAddress';

				if ( isset( $data['location-street'] ) && ! empty( $data['location-street'] ) ) {
					$schema['jobLocation']['address']['streetAddress'] = esc_html( wp_strip_all_tags( $data['location-street'] ) );
				}
				if ( isset( $data['location-locality'] ) && ! empty( $data['location-locality'] ) ) {
					$schema['jobLocation']['address']['addressLocality'] = esc_html( wp_strip_all_tags( $data['location-locality'] ) );
				}
				if ( isset( $data['location-postal'] ) && ! empty( $data['location-postal'] ) ) {
					$schema['jobLocation']['address']['postalCode'] = esc_html( wp_strip_all_tags( $data['location-postal'] ) );
				}
				if ( isset( $data['location-region'] ) && ! empty( $data['location-region'] ) ) {
					$schema['jobLocation']['address']['addressRegion'] = esc_html( wp_strip_all_tags( $data['location-region'] ) );
				}
				if ( isset( $data['location-country'] ) && ! empty( $data['location-country'] ) ) {
					$schema['jobLocation']['address']['addressCountry'] = esc_html( wp_strip_all_tags( $data['location-country'] ) );
				}
			}

			if ( isset( $data['salary-currency'] ) && ! empty( $data['salary-currency'] ) ) {
				$schema['baseSalary']['@type']    = 'MonetaryAmount';
				$schema['baseSalary']['currency'] = esc_html( wp_strip_all_tags( $data['salary-currency'] ) );
			}

			if ( ( isset( $data['salary'] ) && ! empty( $data['salary'] ) ) ||
				( isset( $data['salary-unit'] ) && ! empty( $data['salary-unit'] ) ) ) {

				$schema['baseSalary']['@type']          = 'MonetaryAmount';
				$schema['baseSalary']['value']['@type'] = 'QuantitativeValue';

				if ( isset( $data['salary'] ) && ! empty( $data['salary'] ) ) {
					$schema['baseSalary']['value']['value'] = esc_html( wp_strip_all_tags( $data['salary'] ) );
				}
				if ( isset( $data['salary-min-value'] ) && ! empty( $data['salary-min-value'] ) ) {
					$schema['baseSalary']['value']['minValue'] = esc_html( wp_strip_all_tags( $data['salary-min-value'] ) );
				}
				if ( isset( $data['salary-max-value'] ) && ! empty( $data['salary-max-value'] ) ) {
					$schema['baseSalary']['value']['maxValue'] = esc_html( wp_strip_all_tags( $data['salary-max-value'] ) );
				}
				if ( isset( $data['salary-unit'] ) && ! empty( $data['salary-unit'] ) ) {
					$schema['baseSalary']['value']['unitText'] = esc_html( wp_strip_all_tags( $data['salary-unit'] ) );
				}
			}

			return apply_filters( 'wp_schema_pro_schema_job_posting', $schema, $data, $post );
		}

	}
}
