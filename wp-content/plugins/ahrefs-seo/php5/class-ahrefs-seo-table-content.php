<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Tip_Expand_Suggestion;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use stdClass;
/**
 * Implement table for Content audit screen.
 */
class Ahrefs_Seo_Table_Content extends Ahrefs_Seo_Table {

	const FILTER_KEYWORDS_ALL         = '';
	const FILTER_KEYWORDS_SUGGESTED   = 0;
	const FILTER_KEYWORDS_APPROVED    = 1;
	const FILTER_KEYWORDS_NO_DETECTED = 2;
	const FILTER_KEYWORDS_DUPLICATED  = 3;
	const FILTER_KEYWORDS_YOAST       = 4;
	const FILTER_KEYWORDS_RANKMATH    = 5;
	const FILTER_KEYWORDS_AIOSEO      = 6;
	/**
	 * One of indexes from self::$tabs
	 */
	const DEFAULT_TAB = '';
	/**
	 * Content class instance.
	 *
	 * @var Ahrefs_Seo_Data_Content
	 */
	private $content;
	/**
	 * Current author id.
	 *
	 * @var int
	 */
	private $author = 0;
	/**
	 * Current tab, each tab has one or more post groups by action
	 *
	 * @var string
	 */
	private $tab = '';
	/**
	 * Current raw value of category box
	 *
	 * @var string
	 */
	private $cat_value_raw = '';
	/**
	 * Current date filter value, YYYYMM or empty string.
	 *
	 * @var string
	 */
	private $date = '';
	/**
	 * Current category id (from Categories filter) or empty string.
	 *
	 * @var string
	 */
	private $category = '';
	/**
	 * Current taxonomy (from Categories filter) or empty string.
	 *
	 * @var string|null Empty string if any taxonomy allowed.
	 */
	private $taxonomy = null;
	/**
	 * Current keywords type approved ('1') or not ('0') or empty string.
	 *
	 * @var string
	 */
	private $keywords = '';
	/**
	 * Current exclusion reason (for Excluded tab) or empty string.
	 *
	 * @var string
	 */
	private $reason = '';
	/**
	 * Current post type (from Categories filter) or empty string.
	 *
	 * @var string
	 */
	private $post_type = '';
	/**
	 * Current page id (from Categories filter) or 0.
	 *
	 * @var int
	 */
	private $page_id = 0;
	/**
	 * Return results for those post or tax items only.
	 *
	 * @var Post_Tax[]
	 */
	private $ids = [];
	/** @var bool Some language code used */
	private $is_lang_used = false;
	/**
	 * Default orderby value.
	 *
	 * @var string
	 */
	protected $default_orderby = 'created';
	/**
	 * Tabs at the page
	 *
	 * @var array<string, string>
	 */
	protected $tabs = [];
	/**
	 * Hints for tabs.
	 *
	 * @var array<string, string> Index is tab id, value is html code.
	 */
	protected $tab_hints = [];
	/**
	 * Constructor
	 *
	 * @param array $args Initial options.
	 */
	public function __construct( $args = [] ) {
		$this->tabs = $this::get_tab_names();
        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended -- we create tables on content audit page and load GET parameters, it must work even without nonce.
		// fill from request.
		$this->date          = isset( $_REQUEST['m'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['m'] ) ) : '';
		$this->cat_value_raw = isset( $_REQUEST['cat'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cat'] ) ) : '';
		$this->tab           = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : self::DEFAULT_TAB;
		$this->keywords      = isset( $_REQUEST['keywords'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['keywords'] ) ) : '';
		$this->reason        = isset( $_REQUEST['reason'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['reason'] ) ) : '';
		$this->author        = isset( $_REQUEST['author'] ) ? absint( $_REQUEST['author'] ) : 0;
        // phpcs:enable WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected,WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $this->tabs[ $this->tab ] ) ) {
			$this->tab = self::DEFAULT_TAB;
		}
		// need to fill tab property before parent constructor call.
		parent::__construct(
			[
				'plural' => 'backlinks',
				'screen' => is_array( $args ) && isset( $args['screen'] ) ? $args['screen'] : null,
				'ajax'   => true,
			]
		);
		$this->tab_hints = [
			'dropped'          => $this->prepare_hint( __( 'These pages used to rank in the top 3 for your target keyword but no longer are (as of last audit)', 'ahrefs-seo' ) ),
			'well-performing'  => $this->prepare_hint( __( 'These pages are ranking in the top 3 for your target keyword', 'ahrefs-seo' ) ),
			'under-performing' => $this->prepare_hint( __( 'These pages have a significant number of backlinks, but are not ranking in the top 3 for your target keyword', 'ahrefs-seo' ) ),
			'non-performing'   => $this->prepare_hint( __( 'These pages are ranking below the top 20 for your target keyword', 'ahrefs-seo' ) ),
			'excluded'         => sprintf(
				'<span class="help-small" title="%s<br><ol class=\'internal-hint\'><li>%s</li></ol>%s">&nbsp;</span>', // both substituted strings must not have double quote char, because it will break the title="..." content.
				$this->prepare_quotes( __( 'These pages do not participate in the content audit. Automatically excluded pages are:', 'ahrefs-seo' ) ),
				$this->prepare_quotes( implode( '</li><li>', [ __( 'Noindex page', 'ahrefs-seo' ), __( 'Non-canonical page', 'ahrefs-seo' ), __( 'Redirected page', 'ahrefs-seo' ), __( 'Out of scope', 'ahrefs-seo' ), __( 'Newly published', 'ahrefs-seo' ), __( 'Added since last audit', 'ahrefs-seo' ), __( 'Error analyzing', 'ahrefs-seo' ) ] ) ),
				$this->prepare_quotes(
					sprintf(
						"%s <a href='%s' target='blank' class='internal-hint-a'>%s</a>", // html code must not contain double quotes.
						$this->prepare_quotes( esc_html__( 'You can also manually exclude pages from the audit.', 'ahrefs-seo' ) ),
						'https://help.ahrefs.com/en/articles/3901720-how-does-the-ahrefs-seo-wordpress-plugin-work', // no double quotes!
						__( 'Learn more', 'ahrefs-seo' )
					)
				)
			),
		];
		$this->content   = Ahrefs_Seo_Data_Content::get();
		$name            = '';
		$value           = $this->cat_value_raw;
		// parse cat_value at "cat-id", "page-id" or "0" - for all categories.
		if ( false !== strpos( $this->cat_value_raw, Ahrefs_Seo_Data_Content::CAT_FILTER_DIVIDER ) ) {
			list($name, $value) = explode( Ahrefs_Seo_Data_Content::CAT_FILTER_DIVIDER, $this->cat_value_raw, 2 );
		}
		switch ( $name ) {
			case 'cat':
				$this->post_type = 'post'; // "cat-0".
				if ( ! empty( $value ) ) {
					$this->category = $value; // "cat-xx".
				} else {
					$this->post_type = 'post'; // "cat-0".
				}
				break;
			case 'page':
				if ( ! empty( $value ) ) {
					$this->page_id = absint( $value ); // "page-xx".
				} else {
					$this->post_type = 'page'; // "page-0".
				}
				break;
			case 'product':
				$this->post_type = 'product'; // "product-0".
				if ( ! empty( $value ) ) {
					$this->category = $value; // "product-xx".
				}
				break;
			default:
				if ( 0 === strpos( $name, 'tax:' ) ) { // possible values: 'tax:', 'tax:category' or 'tax:product_cat'.
					$this->taxonomy = substr( $name, 4 );
				} else {
					$this->post_type = $name;
				}
		}
		add_filter( 'hidden_columns', [ $this, 'hidden_columns_filter' ], 10, 3 );
	}
	/**
	 * Get id and title for all tabs.
	 *
	 * @since 0.9.4
	 *
	 * @return array<string, string> Array [ tab name => tab title ].
	 */
	public static function get_tab_names() {
		return [
			''                 => _x( 'All analyzed', 'Title of tab and chart legend', 'ahrefs-seo' ),
			'dropped'          => _x( 'No longer well-performing', 'Title of tab', 'ahrefs-seo' ),
			'well-performing'  => _x( 'Well-performing', 'Title of tab and chart legend', 'ahrefs-seo' ),
			'under-performing' => _x( 'Under-performing', 'Title of tab and chart legend', 'ahrefs-seo' ),
			'non-performing'   => _x( 'Non-performing', 'Title of tab and chart legend', 'ahrefs-seo' ),
			'excluded'         => _x( 'Excluded', 'Title of tab and chart legend', 'ahrefs-seo' ),
		];
	}
	/**
	 * Prepare simple tooltip content
	 *
	 * @since 0.8.6
	 *
	 * @param string $text Source text string.
	 * @return string Escaped string.
	 */
	private function prepare_hint( $text ) {
		return sprintf( '<span class="help-small" title="%s">&nbsp;</span>', esc_attr( $text ) );
	}
	/**
	 * Replace quotes in html string
	 *
	 * @since 0.8.6
	 *
	 * @param string $html Source HTML string.
	 * @return string Resulting string, not escaped.
	 */
	public function prepare_quotes( $html ) {
		return str_replace(
			'"',
			'&quot;', // so replace possible double quote with html entity.
			$html
		);
	}
	/**
	 * Filter for default columns visibility.
	 *
	 * @param string[]   $hidden An array of hidden columns.
	 * @param \WP_Screen $screen WP_Screen object of the current screen.
	 * @param bool       $use_defaults Whether to show the default columns.
	 * @return string[]
	 */
	public function hidden_columns_filter( $hidden, $screen, $use_defaults = false ) {
		// do not define parameter types for filter function.
		if ( $use_defaults ) {
			if ( $this->screen->id === $screen->id ) {
				$hidden[] = 'categories'; // do not show categories by default.
				$hidden[] = 'author'; // do not show author by default.
			}
		}
		return $hidden;
	}
	/**
	 * Get sortable columns
	 *
	 * @return array<string, array<string|bool>>
	 */
	protected function get_sortable_columns() {
		return array(
			'title'          => array( 'title', false ),
			'keyword'        => array( 'keyword', false ),
			'position'       => array( 'position', false ),
			'total'          => array( 'total', false ),
			'organic'        => array( 'organic', false ),
			'backlinks'      => array( 'backlinks', false ),
			'refdomains'     => array( 'refdomains', false ),
			'date'           => array( 'created', false ),
			'last_well_date' => array( 'last_well_date', false ),
			'action'         => array( 'action', false ),
		);
	}
	/**
	 * Get columns
	 *
	 * @return array<string, string>
	 */
	public function get_columns() {
		$settings_content = Links::settings( Ahrefs_Seo_Screen_Settings::TAB_CONTENT );
		$waiting_text     = $this->content->get_waiting_as_text();
		$country_code     = ( new Snapshot() )->get_country_code( $this->content->snapshot_context_get() );
		$columns          = [
			'cb'             => '<input type="checkbox" />',
			'title'          => _x( 'Title', 'Table column title', 'ahrefs-seo' ),
			'author'         => _x( 'Author', 'Table column title', 'ahrefs-seo' ),
			'keyword'        => _x( 'Target Keywords', 'Table column title', 'ahrefs-seo' ),
			'categories'     => _x( 'Categories', 'Table column title', 'ahrefs-seo' ),
			'position'       => sprintf( '<span title="%s">%s</span>', esc_attr__( 'The average position of this page in search results for a target keyword over the last 3 months. It is retrieved from your Google Search Console account.', 'ahrefs-seo' ), '' === $country_code ? esc_html_x( 'Position', 'Table column title', 'ahrefs-seo' ) : sprintf( esc_html_x( 'Position in %s', 'Table column title', 'ahrefs-seo' ), $country_code ) ),
			'total'          => sprintf(
				'<span title="%s">%s</span>',
				sprintf(
				/* Translators: %s: number of weeks or months: "12 weeks" or "3 months" */
					esc_attr__( 'This metric is retrieved from your Google Analytics account. It is the monthly average traffic from all sources to this page, acquired in the last %s.', 'ahrefs-seo' ),
					$waiting_text
				),
				esc_html_x( 'Total traffic', 'Table column title', 'ahrefs-seo' )
			),
			'organic'        => sprintf(
				'<span title="%s">%s</span>',
				sprintf(
				/* Translators: %s: number of weeks or months: "12 weeks" or "3 months" */
					esc_attr__( 'This metric is retrieved from your Google Analytics account. It includes only monthly average traffic from organic search to this page, acquired in the last %s.', 'ahrefs-seo' ),
					$waiting_text
				),
				esc_html_x( 'Organic traffic', 'Table column title', 'ahrefs-seo' )
			),
			'backlinks'      => sprintf( '<span title="%s">%s</span>', esc_attr__( 'How many links point to your target in total. Not to be confused with the number of pages linking to your target, as a single page can give multiple backlinks.', 'ahrefs-seo' ), esc_html_x( 'Backlinks', 'Table column title', 'ahrefs-seo' ) ),
			'refdomains'     => sprintf( '<span title="%s">%s</span>', esc_attr__( 'Number of domains containing at least one backlink that links to the target.', 'ahrefs-seo' ), esc_html_x( 'Ref. domains', 'Table column title', 'ahrefs-seo' ) ),
			'date'           => _x( 'Date', 'Table column title', 'ahrefs-seo' ),
			'last_well_date' => _x( 'Last well done', 'Table column title', 'ahrefs-seo' ),
			'action'         => 'excluded' !== $this->tab ? sprintf(
				'<span class="show_tooltip" title="" data-tooltip="%s">%s</span>',
				esc_attr(
					sprintf(
					/* Translators: %s: expanded to "content audit settings" with a link */
						__( 'Recommended based on the traffic, backlinks & waiting time thresholds you’ve set in the %s.', 'ahrefs-seo' ),
						sprintf( "<a href='%s'>%s</a>", esc_attr( $settings_content ), esc_html__( 'content audit settings', 'ahrefs-seo' ) )
					)
				),
				_x( 'Suggestion', 'Table column title', 'ahrefs-seo' )
			) : sprintf( '<span class="show_tooltip" title="" data-tooltip="%s">%s</span>', esc_attr__( 'The reason for post being excluded or not analyzed.', 'ahrefs-seo' ), esc_html_x( 'Reasons', 'Table column title', 'ahrefs-seo' ) ),
		];
		if ( Ahrefs_Seo_Data_Content::STATUS4_DROPPED !== $this->tab ) {
			unset( $columns['last_well_date'] );
		}
		return $columns;
	}
	/**
	 * Generates content for a single row of the table
	 *
	 * @param stdClass $item The current item.
	 * @return void
	 */
	public function single_row( $item ) {
		// Note: can not define type of parameters, because not defined in parent class.
		$item->post_tax = Post_Tax::create_from_string( (string) $item->id );
		?>
		<tr class="content-item
		<?php
		if ( ! $item->post_tax->user_can_manage() ) {
			echo esc_attr( ' uiroles-can-not-manage' );
		} ?>">
		<?php
		$this->single_row_columns( $item );
		?>
		</tr>
		<?php
	}
	/**
	 * Display checkbox column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_cb( $item ) {
		// Note: can not define type of parameters, because not defined in parent class.
		printf( '<input type="checkbox" name="link[]" value="%s" data-id="%s" data-ver="%d" %s/>', esc_attr( $item->id ), esc_attr( $item->id ), intval( $item->ver ), isset( $item->post_tax ) ? disabled( ! $item->post_tax->user_can_manage(), true, false ) : false );
	}
	/**
	 * Display title column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_title( stdClass $item ) {
		$title    = $item->title ?: __( '(no title)', 'ahrefs-seo' );
		$url_edit = '';
		$post_tax = isset( $item->post_tax ) ? $item->post_tax : Post_Tax::create_from_string( (string) $item->id );
		if ( $post_tax->user_can_edit() ) {
			$url_edit = $post_tax->get_url_edit(); // post edit link, may be empty.
		}
		// term edit link.
		if ( ! empty( $url_edit ) ) {
			printf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_attr( $url_edit ),
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)', 'ahrefs-seo' ), $title ) ),
				esc_html( $title )
			);
		} else {
			// just a title.
			echo esc_html( $title );
		}
	}
	/**
	 * Display author column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_author( stdClass $item ) {
		if ( $item->author ) {
			$link = add_query_arg(
				[
					'author'   => get_the_author_meta( 'ID', $item->author ),
					'keywords' => $this->keywords,
				],
				Links::content_audit( $this->tab )
			);
			$link = Helper_Content::get()->update_link( $link );
			?>
			<a href="<?php echo esc_attr( $link ); ?>" class="author-link">
								<?php
								echo esc_html( get_the_author_meta( 'display_name', $item->author ) );
								?>
			</a>
			<?php
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display target keyword column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_keyword( stdClass $item ) {
		?>
		<div class="content-post-keyword">
			<?php
			if ( is_null( $item->keyword ) || '' === $item->keyword ) {
				$this::some_empty_message();
			} else {
				echo esc_html( $item->keyword );
			}
			?>
		</div>
		<?php
		self::print_keyword_source_badge( $item->keyword, $item->is_approved_keyword, $item->kw_low, $item->kw_source );
	}
	/**
	 * Print badge for keyword based on couple of parameters.
	 *
	 * @since 0.9.4
	 *
	 * @param string|null $keyword Keyword.
	 * @param string|null $is_approved_keyword Is keyword approved, field as string, directly from DB.
	 * @param string|null $kw_low Does article have low words for TF-IDF analysis, field as string, directly from DB.
	 * @param string|null $kw_source Source of keyword if any.
	 * @return void
	 */
	public static function print_keyword_source_badge( $keyword = null, $is_approved_keyword = null, $kw_low = null, $kw_source = null ) {
		if ( Sources::SOURCE_YOASTSEO === $kw_source ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-imported 
			<?php
			echo esc_attr( 'badge-keyword-yoast' ); ?>">✓ 
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'PULLED FROM YOAST', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( Sources::SOURCE_AIOSEO === $kw_source ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-imported 
			<?php
			echo esc_attr( 'badge-keyword-aioseo' ); ?>">✓ 
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'PULLED FROM AIOSEO', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( Sources::SOURCE_RANKMATH === $kw_source ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-imported 
			<?php
			echo esc_attr( 'badge-keyword-rankmath' ); ?>">✓ 
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'PULLED FROM RANKMATH', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( $kw_low && empty( $keyword ) ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-too-low">
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'CONTENT LENGTH TOO SHORT', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( ! $is_approved_keyword && empty( $keyword ) ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-empty">
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'NO KEYWORD DETECTED', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( $is_approved_keyword ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-approved">✓ 
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'APPROVED', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		} elseif ( ! is_null( $keyword ) && '' !== $keyword ) {
			?>
			<a href="#" class="badge-keyword badge-keyword-suggested">
			<?php
			/* Translators: as short text as possible */
			echo esc_html_x( 'SUGGESTED KEYWORD', 'Badge title', 'ahrefs-seo' );
			?>
			</a>
			<?php
		}
	}
	/**
	 * Display categories column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_categories( stdClass $item ) {
		if ( ! empty( $item->categories ) ) {
			sort( $item->categories );
			echo implode( ', ', $item->categories ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $item->categories contains html links.
		}
		$badge     = $this::get_post_type_badge( $item->badge );
		$post_tax  = isset( $item->post_tax ) ? $item->post_tax : Post_Tax::create_from_string( (string) $item->id );
		$edit_link = $post_tax->user_can_edit() ? $post_tax->get_url_edit() : $post_tax->get_url();
		if ( $edit_link ) {
			?>
			<a href="<?php echo esc_attr( $edit_link ); ?>" class="content-post-button" target="_blank">
								<?php
								echo esc_html( $badge );
								?>
			</a>
			<?php
		}
	}
	/**
	 * Return a title for a post type badge
	 *
	 * @param string $badge Badge field value from DB.
	 * @return string Title of post type badge.
	 */
	public static function get_post_type_badge( $badge ) {
		switch ( $badge ) {
			case 'post':
				return _x( 'Post', 'Post type badge', 'ahrefs-seo' );
			case 'page':
				return _x( 'Page', 'Post type badge', 'ahrefs-seo' );
			case 'product':
				return _x( 'Product', 'Post type badge', 'ahrefs-seo' );
			case 'category':
				return _x( 'Category', 'Post type badge', 'ahrefs-seo' );
			case 'product_cat':
			case 'products':
				return _x( 'Products', 'Post type badge', 'ahrefs-seo' );
			case 'post_tag':
				return _x( 'Post Tag', 'Post type badge', 'ahrefs-seo' );
		}
		return $badge;
	}
	/**
	 * Display date column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_date( stdClass $item ) {
		if ( ! empty( $item->created ) && '' !== str_replace( [ '0', '-' ], '', $item->created ) ) {
			$date = date_create_from_format( 'Y-m-d', (string) $item->created );
			if ( false !== $date ) {
				echo esc_html( date_format( $date, 'j M Y' ) );
				return;
			}
		}
		$this::some_empty_message();
	}
	/**
	 * Display 'last well done' date column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_last_well_date( stdClass $item ) {
		if ( ! empty( $item->last_well_date ) && '' !== str_replace( [ '0', '-' ], '', $item->last_well_date ) ) {
			echo esc_html( (string) $item->last_well_date );
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display total traffic column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_total( stdClass $item ) {
		if ( ! is_null( $item->total ) ) {
			if ( intval( $item->total ) >= 0 ) {
				if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
					$this::some_empty_message();
				} else {
					echo esc_html( $item->total );
				}
			} else {
				$this::some_error_message();
			}
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display Position column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_position( stdClass $item ) {
		$this::print_position( is_null( $item->position ) ? null : floatval( $item->position ) );
	}
	/**
	 * Display Organic traffic column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_organic( stdClass $item ) {
		if ( ! is_null( $item->organic ) ) {
			if ( intval( $item->organic ) >= 0 ) {
				if ( defined( 'AHREFS_SEO_NO_GA' ) && AHREFS_SEO_NO_GA ) {
					$this::some_empty_message();
				} else {
					echo esc_html( $item->organic );
				}
			} else {
				$this::some_error_message();
			}
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display Backlinks column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_backlinks( stdClass $item ) {
		if ( ! is_null( $item->backlinks ) ) {
			if ( intval( $item->backlinks ) >= 0 ) {
				echo esc_html( $item->backlinks );
			} else {
				$this::some_error_message();
			}
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display Ref.domains column
	 *
	 * @since 0.9.8
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_refdomains( stdClass $item ) {
		if ( ! is_null( $item->refdomains ) ) {
			if ( intval( $item->refdomains ) >= 0 ) {
				echo esc_html( $item->refdomains );
			} else {
				$this::some_error_message();
			}
		} else {
			$this::some_empty_message();
		}
	}
	/**
	 * Display Suggested action column
	 *
	 * @param stdClass $item Item.
	 */
	protected function column_action( stdClass $item ) {
		$action = isset( $item->action ) ? $item->action : Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST;
		// Items never added (action is null) to content audit will have ACTION4_ADDED_SINCE_LAST.
		$title = $this::get_action_title( (string) $action );
		?>
		<a class="status-action 
		<?php
		echo esc_attr( "status-{$action}" );
		?>
		content-more-button button" href="#"><span class="arrow-down"></span><span class="arrow-text">
		<?php
		echo esc_html( $title );
		?>
		</span></a>
		<?php
	}
	/**
	 * Get title for action.
	 *
	 * @since 0.8.6
	 *
	 * @param string $action Action, one of Ahrefs_Seo_Data_Content::ACTION4_xxx const.
	 * @return string Action title (translated).
	 */
	public static function get_action_title( $action ) {
		$title = str_replace( '_', ' ', ucfirst( $action ) );
		switch ( $action ) {
			case Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST:
				$title = _x( 'Added since last audit', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE:
				$title = _x( 'Noindex page', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL:
				$title = _x( 'Non-canonical', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED:
				$title = _x( 'Redirected', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED:
				$title = _x( 'Manually excluded', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE:
			case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL:
			case Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING:
				$title = _x( 'Out of scope', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED:
				$title = _x( 'Newly published', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING:
				$title = _x( 'Error analyzing', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING:
				$title = _x( 'Do nothing', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW:
			case Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE:
				$title = _x( 'Update', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_MERGE:
				$title = _x( 'Merge', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE:
				$title = _x( 'Exclude', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_REWRITE:
				$title = _x( 'Rewrite', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
			case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING:
			case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL:
			case Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL:
				$title = _x( 'Analyzing...', 'Reason or suggestion title', 'ahrefs-seo' );
				break;
		}
		return $title;
	}
	/**
	 * Display error message for total, organic traffic, backlinks column when it has value < 0
	 *
	 * @return void
	 */
	protected static function some_error_message() {
		?>
		<span class="some-error" title="<?php esc_attr_e( 'There was an error retrieving data.', 'ahrefs-seo' ); ?>">?</span>
		<?php
	}
	/**
	 * Display empty value message like '-'.
	 *
	 * @return void
	 */
	protected static function some_empty_message() {
		?>
		—
		<?php
	}
	/**
	 * Print position value.
	 *
	 * @since 0.9.8
	 *
	 * @param float|null $position Position value.
	 * @return void
	 */
	public static function print_position( $position = null ) {
		if ( ! is_null( $position ) ) {
			if ( $position >= 0 ) {
				if ( $position < Ahrefs_Seo_Data_Content::POSITION_MAX - 1 ) {
					$value = round( 10 * $position ) / 10;
					echo esc_html( sprintf( '%.1f', $value ) );
				} else { // position not found.
					self::some_empty_message();
				}
			} else {
				self::some_error_message();
			}
		} else {
			self::some_empty_message();
		}
	}
	/**
	 * Fill current table page with items
	 *
	 * @return array<stdClass>
	 */
	protected function fill_items() {
		if ( count( $this->ids ) ) {
			return $this->content->data_get_by_ids( $this->ids );
		}
		$page    = $this->get_pagenum();
		$start   = ( $page - 1 ) * (int) $this->per_page;
		$filters = [
			'cat'       => $this->category,
			'taxonomy'  => $this->taxonomy,
			'post_type' => $this->post_type,
			'page_id'   => $this->page_id,
			's'         => $this->search_string,
			'ids'       => $this->ids,
		];
		if ( ! empty( $this->author ) ) {
			$filters['author'] = $this->author;
		}
		if ( '' !== $this->keywords ) {
			$filters['keywords'] = $this->keywords;
		}
		if ( '' !== $this->reason ) {
			$filters['reason'] = $this->reason;
		}
		return $this->content->data_get_clear( $this->tab, $this->date, $filters, $start, $this->per_page, $this->orderby, $this->order );
	}
	/**
	 * Return count of items using current filters
	 *
	 * @return int
	 */
	private function count_data() {
		if ( count( $this->ids ) ) {
			return count( $this->ids );
		}
		$filters = [
			'cat'       => $this->category,
			'taxonomy'  => $this->taxonomy,
			'post_type' => $this->post_type,
			'page_id'   => $this->page_id,
			's'         => $this->search_string,
		];
		if ( ! empty( $this->author ) ) {
			$filters['author'] = $this->author;
		}
		if ( '' !== $this->keywords ) {
			$filters['keywords'] = $this->keywords;
		}
		if ( '' !== $this->reason ) {
			$filters['reason'] = $this->reason;
		}
		return $this->content->data_get_clear_count( $this->tab, $this->date, $filters );
	}
	/**
	 * Prepares the list of items for displaying
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = get_hidden_columns( ! empty( get_current_screen() ) ? get_current_screen() : $this->screen );
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->fill_items();
		$total_items           = $this->count_data();
		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => intval( ceil( $total_items / $this->per_page ) ),
				'orderby'     => $this->orderby,
				'order'       => $this->order,
			]
		);
	}
	/**
	 * Display a table.
	 *
	 * Note: we will not have there any query vars from page url until we added it as parameter at the content.display() JS function.
	 */
	public function display() {
		// Adds field order and orderby.
		// Note: nonce field already added before.
		?>
		<input type="hidden" class="table-query" name="tab" data-name="tab" value="<?php echo esc_attr( $this->tab ); ?>" />
		<input type="hidden" class="table-query" name="keywords" data-name="keywords" value="<?php echo esc_attr( $this->keywords ); ?>" />
		<input type="hidden" class="table-query" name="reason" data-name="reason" value="<?php echo esc_attr( $this->reason ); ?>" />
		<input type="hidden" class="table-query" name="m" data-name="m" value="<?php echo esc_attr( $this->date ); ?>" />
		<input type="hidden" class="table-query" name="cat" data-name="cat" value="<?php echo esc_attr( is_null( $this->taxonomy ) ? $this->category : "tax:{$this->taxonomy}" ); ?>" />
		<input type="hidden" class="table-query" name="author" data-name="author" value="<?php echo ! empty( $this->author ) ? esc_attr( (string) $this->author ) : ''; ?>" />
		<input type="hidden" class="table-query" name="page" data-name="paged" value="<?php echo esc_attr( (string) $this->get_pagenum() ); ?>" />
		<input type="hidden" class="table-query" name="orderby" data-name="orderby" value="<?php echo esc_attr( $this->_pagination_args['orderby'] ); ?>" />
		<input type="hidden" class="table-query" name="order" data-name="order" value="<?php echo esc_attr( $this->_pagination_args['order'] ); ?>" />
		<input type="hidden" class="table-query" name="last_search" data-name="s" value="<?php echo esc_attr( $this->search_string ); ?>" />
		<input type="hidden" id="has_unprocessed_items" value="<?php echo esc_attr( ( new Content_Audit() )->require_update() ? '1' : '' ); ?>" />
		<?php
		$tip_expand_suggestion = new Tip_Expand_Suggestion();
		if ( '' === $this->tab && $tip_expand_suggestion->need_to_show() ) {
			$tip_expand_suggestion->hide(); // show only once at main tab after each Content audit.
			?>
			<input type="hidden" id="please_expand_suggestion" value="1" />
			<?php
		}
		parent::display();
	}
	/**
	 * Get bulk actions list
	 *
	 * @return array<string, string> Associative array ( action id => title )
	 */
	protected function get_bulk_actions() {
		return array(
			'stop'  => __( 'Exclude from analysis', 'ahrefs-seo' ),
			'start' => __( 'Include in analysis', 'ahrefs-seo' ),
		);
	}
	/**
	 * Generates and displays row action links.
	 *
	 * @param stdClass $item        Item being acted upon.
	 * @param string   $column_name Current column name.
	 * @param string   $primary     Primary column name.
	 * @return string Row actions output for posts.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		// Note: can not define type of parameters, because not defined in parent class.
		if ( $primary === $column_name ) {
			$url_edit = '';
			$post_id  = absint( $item->post_id );
			$title    = $item->title ?: __( '(no title)', 'ahrefs-seo' );
			$actions  = [];
			// Note: we are working only with posts & pages already filtered by post_status 'publish' here.
			// So we can skip related checks in row handlers logic.
			$post_tax = isset( $item->post_tax ) ? $item->post_tax : Post_Tax::create_from_string( (string) $item->id );
			if ( $post_tax->user_can_edit() ) {
				$url_edit = $post_tax->get_url_edit(); // post edit link, may be empty.
			}
			$url_view = $post_tax->get_url();
			if ( '' !== $url_edit ) {
				$actions['edit'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					$url_edit,
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'ahrefs-seo' ), $title ) ),
					__( 'Edit', 'ahrefs-seo' )
				);
			}
			if ( '' !== $url_view ) {
				$actions['view'] = sprintf(
					'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
					$url_view,
					/* translators: %s: post title */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'ahrefs-seo' ), $title ) ),
					__( 'View', 'ahrefs-seo' )
				);
			}
			if ( $post_tax->user_can_manage() ) {
				// Exclude from audit / Include to audit row action.
				$action = isset( $item->action ) ? $item->action : Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST;
				if ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST ], true ) ) {
					$actions['include'] = sprintf( '<a href="%s" data-id="%s" class="submit-include" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Run audit', 'ahrefs-seo' ), __( 'Run audit', 'ahrefs-seo' ) );
				} elseif ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING ], true ) ) {
					$actions['include'] = sprintf( '<a href="%s" data-id="%s" class="submit-include" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Analyze page again', 'ahrefs-seo' ), __( 'Analyze page again', 'ahrefs-seo' ) );
				} elseif ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE, Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL, Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED ], true ) ) {
					$actions['recheck'] = sprintf( '<a href="%s" data-id="%s" class="submit-recheck" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Recheck status', 'ahrefs-seo' ), __( 'Recheck status', 'ahrefs-seo' ) );
					$actions['include'] = sprintf( '<a href="%s" data-id="%s" class="submit-include" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Include to audit', 'ahrefs-seo' ), __( 'Include to audit', 'ahrefs-seo' ) );
				} elseif ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED ], true ) ) {
					$actions['include'] = sprintf( '<a href="%s" data-id="%s" class="submit-include" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Include to audit', 'ahrefs-seo' ), __( 'Include to audit', 'ahrefs-seo' ) );
				} elseif ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW, Ahrefs_Seo_Data_Content::ACTION4_MERGE, Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE, Ahrefs_Seo_Data_Content::ACTION4_REWRITE, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ], true ) ) {
					$actions['exclude'] = sprintf( '<a href="%s" data-id="%s" class="submit-exclude" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Exclude from audit', 'ahrefs-seo' ), __( 'Exclude from audit', 'ahrefs-seo' ) );
				}
				// additional "Exclude" row action.
				if ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST ], true ) ) {
					$actions['exclude'] = sprintf( '<a href="%s" data-id="%s" class="submit-exclude" aria-label="%s">%s</a>', '#', $item->id, esc_attr__( 'Exclude from audit', 'ahrefs-seo' ), __( 'Exclude from audit', 'ahrefs-seo' ) );
				}
			}
			return $this->row_actions( $actions );
		} elseif ( 'keyword' === $column_name ) {
			$post_tax = isset( $item->post_tax ) ? $item->post_tax : Post_Tax::create_from_string( (string) $item->id );
			if ( $post_tax->user_can_manage() ) {
				$actions = [];
				$title   = $item->title ?: __( '(no title)', 'ahrefs-seo' );
				if ( $post_tax->user_can_manage() ) { // can user manage this item in a plugin?
					if ( ! is_null( $item->keyword ) && '' !== $item->keyword && ! (bool) $item->is_approved_keyword && ! Sources::is_source_imported( $item->kw_source ) ) {
						$actions['approve-keyword'] = sprintf(
							'<a href="#" class="approve-keywords" data-post="%s" aria-label="%s">✓ %s</a>',
							$item->id,
							/* translators: %s: post title */
							esc_attr( sprintf( __( 'Approve keyword for &#8220;%s&#8221;', 'ahrefs-seo' ), $title ) ),
							__( 'Approve', 'ahrefs-seo' )
						);
					}
					$actions['change-keyword'] = sprintf(
						'<a href="#" class="change-keywords" data-post="%s" aria-label="%s">%s</a>',
						$item->id,
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'Change keyword for &#8220;%s&#8221;', 'ahrefs-seo' ), $title ) ),
						__( 'Change', 'ahrefs-seo' )
					);
				}
				return $this->row_actions( $actions );
			}
		} elseif ( 'backlinks' === $column_name ) {
			$actions = [];
			$post_id = absint( $item->post_id );
			$url     = Post_Tax::create_from_string( (string) $item->id )->get_url();
			if ( '' !== $url ) {
				$link = 'https://app.ahrefs.com/v2-site-explorer/backlinks/exact?target=' . rawurlencode( apply_filters( 'ahrefs_seo_search_traffic_url', $url ) );
				if ( ! is_null( $item->backlinks ) ) {
					if ( intval( $item->backlinks ) > 0 ) {
						$actions['ahrefs-open-link'] = sprintf(
							'<a href="%s" target="_blank" class="ahrefs-open-content-backlinks" data-post="%d" data-url="%s" aria-label="%s">%s<img src="%s" class="icon"></a>',
							esc_attr( $link ),
							esc_attr( "{$post_id}" ),
							esc_attr( $url ),
							/* translators: view info for post in Ahrefs */
							esc_attr__( 'View in Ahrefs', 'ahrefs-seo' ),
							__( 'View in Ahrefs', 'ahrefs-seo' ),
							esc_attr( AHREFS_SEO_IMAGES_URL . 'link-open.svg' )
						);
					}
				}
			}
			return $this->row_actions( $actions );
		} elseif ( 'refdomains' === $column_name ) {
			$actions = [];
			$post_id = absint( $item->post_id );
			$url     = Post_Tax::create_from_string( (string) $item->id )->get_url();
			if ( '' !== $url ) {
				$link = 'https://app.ahrefs.com/v2-site-explorer/refdomains/exact?target=' . rawurlencode( apply_filters( 'ahrefs_seo_search_traffic_url', $url ) );
				if ( ! is_null( $item->refdomains ) ) {
					if ( intval( $item->refdomains ) > 0 ) {
						$actions['ahrefs-open-link'] = sprintf(
							'<a href="%s" target="_blank" class="ahrefs-open-content-refdomain" data-post="%d" data-url="%s" aria-label="%s">%s<img src="%s" class="icon"></a>',
							esc_attr( $link ),
							esc_attr( "{$post_id}" ),
							esc_attr( $url ),
							/* translators: view info for post in Ahrefs */
							esc_attr__( 'View in Ahrefs', 'ahrefs-seo' ),
							__( 'View in Ahrefs', 'ahrefs-seo' ),
							esc_attr( AHREFS_SEO_IMAGES_URL . 'link-open.svg' )
						);
					}
				}
			}
			return $this->row_actions( $actions );
		}
		return '';
	}
	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @global \WP_Locale $wp_locale
	 * @return void
	 */
	protected function months_dropdown_content() {
		global $wp_locale;
		$months      = Ahrefs_Seo_Db_Helper::content_data_get_clear_months( $this->content->snapshot_context_get(), $this->search_string );
		$month_count = count( $months );
		if ( ! $month_count || 1 === $month_count && 0 === $months[0]->month ) {
			return;
		}
		$m = $this->date;
		?>
		<label for="filter-by-date" class="screen-reader-text">
		<?php
		esc_html_e( 'Filter by date', 'ahrefs-seo' );
		?>
		</label>
		<select name="m" id="filter-by-date">
			<option
			<?php
			selected( $m, 0 );
			?>
		value="0">
		<?php
		esc_html_e( 'All dates', 'ahrefs-seo' );
		?>
		</option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( ! $arc_row->year ) {
					continue;
				}
				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;
				printf(
					"<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					esc_html( sprintf( __( '%1$s %2$d', 'ahrefs-seo' ), $wp_locale->get_month( $month ), $year ) )
				);
			}
			?>
		</select>
		<?php
	}
	/**
	 * Displays a categories drop-down for filtering on the Posts list table.
	 */
	protected function categories_dropdown_content() {
		Helper_Content::get()->categories_dropdown( $this->post_type, $this->category, $this->cat_value_raw, $this->is_lang_used );
	}
	/**
	 * Displays an authors drop-down for filtering on the Posts list table.
	 */
	protected function authors_dropdown_content() {
		$list = ( new Content_Db() )->get_all_authors();
		?>
		<label class="screen-reader-text" for="author">
		<?php
		esc_html_e( 'Filter by author', 'ahrefs-seo' );
		?>
		</label>
		<select name="author" id="author" class="postform">
			<option value="0" class="current"
			<?php
			selected( $this->author, 0 );
			?>
		>
		<?php
		esc_html_e( 'All authors', 'ahrefs-seo' );
		?>
		</option>
			<?php
			if ( $list ) {
				foreach ( $list as $item ) {
					?>
					<option value="<?php echo esc_attr( $item['id'] ); ?>"
												<?php
												selected( $this->author, $item['id'] );
												?>
				>
					<?php
					echo esc_html( $item['name'] );
					?>
				</option>
					<?php
				}
			}
			?>
		</select>
		<?php
	}
	/**
	 * Displays a keywords approved drop-down for filtering on the Posts list table.
	 */
	protected function keywords_dropdown_content() {
		?>
		<label class="screen-reader-text" for="keywords">
		<?php
		esc_html_e( 'Filter by keywords type', 'ahrefs-seo' );
		?>
		</label>
		<select name="keywords" id="keywords" class="postform">
			<option value="<?php echo esc_attr( self::FILTER_KEYWORDS_ALL ); ?>" class="current"
										<?php
										selected( $this->keywords, self::FILTER_KEYWORDS_ALL );
										?>
		>
		<?php
		esc_html_e( 'All keywords', 'ahrefs-seo' );
		?>
		</option>
			<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_APPROVED ); ?>" class="current"
										<?php
										selected( $this->keywords, self::FILTER_KEYWORDS_APPROVED );
										?>
		>
		<?php
		esc_html_e( 'Approved by you', 'ahrefs-seo' );
		?>
		</option>
			<?php
			if ( Sources::get()->has_active_source( Sources::SOURCE_YOASTSEO ) ) {
				?>
					<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_YOAST ); ?>" class="current"
												<?php
												selected( $this->keywords, self::FILTER_KEYWORDS_YOAST );
												?>
			>
				<?php
				esc_html_e( 'Pulled from Yoast', 'ahrefs-seo' );
				?>
			</option>
					<?php
			}
			if ( Sources::get()->has_active_source( Sources::SOURCE_AIOSEO ) ) {
				?>
					<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_AIOSEO ); ?>" class="current"
												<?php
												selected( $this->keywords, self::FILTER_KEYWORDS_AIOSEO );
												?>
			>
				<?php
				esc_html_e( 'Pulled from AIOSEO', 'ahrefs-seo' );
				?>
			</option>
					<?php
			}
			if ( Sources::get()->has_active_source( Sources::SOURCE_RANKMATH ) ) {
				?>
					<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_RANKMATH ); ?>" class="current"
												<?php
												selected( $this->keywords, self::FILTER_KEYWORDS_RANKMATH );
												?>
			>
				<?php
				esc_html_e( 'Pulled from RankMath', 'ahrefs-seo' );
				?>
			</option>
					<?php
			}
			?>
			<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_SUGGESTED ); ?>" class="current"
										<?php
										selected( $this->keywords, self::FILTER_KEYWORDS_SUGGESTED );
										?>
		>
		<?php
		esc_html_e( 'Suggested by the plugin', 'ahrefs-seo' );
		?>
		</option>
			<?php
			if ( 'excluded' !== $this->tab ) { // do not show "Duplicated keywords" at the Excluded tab. We do not analyze these content, so it make no sense.
				?>
			<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_DUPLICATED ); ?>" class="current"
										<?php
										selected( $this->keywords, self::FILTER_KEYWORDS_DUPLICATED );
										?>
			>
				<?php
				esc_html_e( 'Duplicated keywords', 'ahrefs-seo' );
				?>
			</option>
				<?php
			}
			?>
			<option value="<?php echo esc_attr( (string) self::FILTER_KEYWORDS_NO_DETECTED ); ?>" class="current"
										<?php
										selected( $this->keywords, self::FILTER_KEYWORDS_NO_DETECTED );
										?>
		>
		<?php
		esc_html_e( 'No keyword detected', 'ahrefs-seo' );
		?>
		</option>
		</select>
		<?php
	}
	/**
	 * Displays an exclusion reason drop-down for filtering on the Excluded tab
	 *
	 * @since 0.8.6
	 */
	protected function reasons_dropdown_content() {
		$reasons = [ Ahrefs_Seo_Data_Content::ACTION4_NOINDEX_PAGE, Ahrefs_Seo_Data_Content::ACTION4_NONCANONICAL, Ahrefs_Seo_Data_Content::ACTION4_REDIRECTED, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_NEWLY_PUBLISHED, Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST, Ahrefs_Seo_Data_Content::ACTION4_ERROR_ANALYZING, Ahrefs_Seo_Data_Content::ACTION4_MANUALLY_EXCLUDED ];
		?>
		<label class="screen-reader-text" for="reason">
		<?php
		esc_html_e( 'Filter by exclusion reason', 'ahrefs-seo' );
		?>
		</label>
		<select name="reason" id="reason" class="postform">
			<option value="<?php echo esc_attr( self::FILTER_KEYWORDS_ALL ); ?>" class="current"
										<?php
										selected( $this->reason, '' );
										?>
		>
		<?php
		esc_html_e( 'All exclusion reasons', 'ahrefs-seo' );
		?>
		</option>
			<?php
			foreach ( $reasons as $reason ) {
				?>
				<option value="<?php echo esc_attr( $reason ); ?>" class="current"
											<?php
											selected( $this->reason, $reason );
											?>
			>
				<?php
				echo esc_html( $this::get_action_title( $reason ) );
				?>
			</option>
				<?php
			}
			?>
		</select>
		<?php
	}
	/**
	 * Add follow filter and group by choice
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		// Note: can not define type of parameters, because not defined in parent class.
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which ) {
				$this->months_dropdown_content();
				$this->categories_dropdown_content();
				$this->authors_dropdown_content();
				if ( 'excluded' !== $this->tab ) {
					$this->keywords_dropdown_content();
				} else {
					$this->reasons_dropdown_content();
				}
				submit_button( __( 'Filter', 'ahrefs-seo' ), '', 'filter_action', false, array( 'id' => 'group-filter-submit' ) );
			}
			?>
		</div>
		<?php
	}
	/**
	 * Tabs. Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @return array
	 */
	protected function get_views() {
		$result = [];
		$counts = $this->content->data_get_count_by_status();
		// Called from admin-ajax.php content, need to use correct base link.
		$base_url = Links::content_audit();
		foreach ( $this->tabs as $id => $title ) {
			$url      = '' === $id ? remove_query_arg( 'tab', $base_url ) : add_query_arg( 'tab', $id, $base_url );
			$url      = Helper_Content::get()->update_link( $url );
			$count    = isset( $counts[ $id ] ) ? $counts[ $id ] : 0;
			$tab_hint = isset( $this->tab_hints[ $id ] ) ? $this->tab_hints[ $id ] : '';
			// html code of hint.
			$result[ $title ] = sprintf( '<a href="%s" data-tab="%s" class="tab-content-item %s">%s %s <span class="count">(%d)</span></a>', esc_attr( $url ), esc_attr( $id ), $id === $this->tab ? 'current' : '', esc_html( $title ), $tab_hint, $count );
		}
		return $result;
	}
	/**
	 * Replace top navigations by search box and Analysis setting button
	 *
	 * @param string $which Top or bottom.
	 * @phpstan-param 'bottom'|'top' $which Top or bottom.
	 * @return void
	 */
	protected function pagination( $which ) {
		// Note: can not define type of parameters, because not defined in parent class.
		if ( 'top' === $which ) {
			?>
			<div class="tablenav-pages">
				<?php
				$this->search_box( __( 'Search', 'ahrefs-seo' ), 'search_to_url' );
				?>
				<div class="clear"></div>
			</div>
			<?php
			return;
		}
		parent::pagination( $which );
	}
	/**
	 * Return updated items as json answer and terminate.
	 * Check current version of items and return all updated items (rows) at the 'data.updated' field (as raw html code) of json answer.
	 *
	 * @see Ahrefs_Seo_Data_Content class: data_get_by_ids(), data_get_clear_count().
	 *
	 * @param Post_Tax[]           $ids List of posts.
	 * @param array<string, mixed> $additional_fields Additional fields.
	 * @return void
	 */
	public function ajax_response_updated( array $ids, array $additional_fields ) {
		if ( ! empty( $ids ) ) {
			$this->ids = $ids;
			$this->prepare_items();
			ob_start();
			$this->display_rows();
			$rows = ob_get_clean();
		}
		ob_start();
		$this->views();
		$tabs     = ob_get_clean();
		$response = [ 'tabs' => $tabs ];
		if ( ! empty( $rows ) ) {
			$response['updated'] = $rows;
		}
		if ( ! empty( $additional_fields ) ) {
			$response = array_merge( $response, $additional_fields );
		}
		wp_send_json_success( $response );
	}
	/**
	 * Get default orderby
	 *
	 * @since 0.8.4
	 * @return string
	 */
	protected function get_default_orderby() {
		return Ahrefs_Seo_Data_Content::STATUS4_DROPPED === $this->tab ? 'last_well_date' : $this->default_orderby;
	}
	/**
	 * Get current tab
	 *
	 * @since 0.9.4
	 *
	 * @return string Tab ID.
	 */
	public function get_current_tab() {
		return $this->tab;
	}
}